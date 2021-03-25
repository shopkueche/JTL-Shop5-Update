<?php

namespace JTL\dbeS\Sync;

use JTL\Checkout\Adresse;
use JTL\Checkout\Bestellung;
use JTL\Checkout\Lieferadresse;
use JTL\Checkout\Lieferschein;
use JTL\Checkout\Rechnungsadresse;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\dbeS\Starter;
use JTL\Language\LanguageHelper;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Plugin\Payment\LegacyMethod;
use JTL\Shop;
use stdClass;

/**
 * Class Orders
 * @package JTL\dbeS\Sync
 */
final class Orders extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        foreach ($starter->getXML() as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            if (\strpos($file, 'ack_bestellung.xml') !== false) {
                $this->handleACK($xml);
            } elseif (\strpos($file, 'del_bestellung.xml') !== false) {
                $this->handleDeletes($xml);
            } elseif (\strpos($file, 'delonly_bestellung.xml') !== false) {
                $this->handleDeleteOnly($xml);
            } elseif (\strpos($file, 'storno_bestellung.xml') !== false) {
                $this->handleCancelation($xml);
            } elseif (\strpos($file, 'reaktiviere_bestellung.xml') !== false) {
                $this->handleReactivation($xml);
            } elseif (\strpos($file, 'ack_zahlungseingang.xml') !== false) {
                $this->handlePaymentACK($xml);
            } elseif (\strpos($file, 'set_bestellung.xml') !== false) {
                $this->handleSet($xml);
            } elseif (\strpos($file, 'upd_bestellung.xml') !== false) {
                $this->handleUpdate($xml);
            }
        }

        return null;
    }

    /**
     * @param array $xml
     */
    private function handleACK($xml): void
    {
        $source = $xml['ack_bestellungen']['kBestellung'] ?? [];
        if (\is_numeric($source)) {
            $source = [$source];
        }
        foreach (\array_filter(\array_map('\intval', $source)) as $orderID) {
            $this->db->update('tbestellung', 'kBestellung', $orderID, (object)['cAbgeholt' => 'Y']);
            $this->db->update(
                'tbestellung',
                ['kBestellung', 'cStatus'],
                [$orderID, \BESTELLUNG_STATUS_OFFEN],
                (object)['cStatus' => \BESTELLUNG_STATUS_IN_BEARBEITUNG]
            );
            $this->db->update('tzahlungsinfo', 'kBestellung', $orderID, (object)['cAbgeholt' => 'Y']);
        }
    }

    /**
     * @param int $orderID
     * @return bool|\PaymentMethod
     */
    private function getPaymentMethod(int $orderID)
    {
        $order = $this->db->queryPrepared(
            'SELECT tbestellung.kBestellung, tzahlungsart.cModulId
            FROM tbestellung
            LEFT JOIN tzahlungsart 
                ON tbestellung.kZahlungsart = tzahlungsart.kZahlungsart
            WHERE tbestellung.kBestellung = :oid
            LIMIT 1',
            ['oid' => $orderID],
            ReturnType::SINGLE_OBJECT
        );

        return empty($order->cModulId) ? false : LegacyMethod::create($order->cModulId);
    }

    /**
     * @param array $xml
     */
    private function handleDeletes(array $xml): void
    {
        $source = $xml['del_bestellungen']['kBestellung'] ?? [];
        if (\is_numeric($source)) {
            $source = [$source];
        }
        foreach (\array_filter(\array_map('\intval', $source)) as $orderID) {
            $orderID = (int)$orderID;
            if ($orderID <= 0) {
                continue;
            }
            $module = $this->getPaymentMethod($orderID);
            if ($module) {
                $module->cancelOrder($orderID, true);
            }
            $this->deleteOrder($orderID);
            // uploads (bestellungen)
            $this->db->delete('tuploadschema', ['kCustomID', 'nTyp'], [$orderID, 2]);
            $this->db->delete('tuploaddatei', ['kCustomID', 'nTyp'], [$orderID, 2]);
            // uploads (artikel der bestellung)
            // todo...
            // wenn unreg kunde, dann kunden auch löschen
            $this->db->query(
                'SELECT kKunde FROM tbestellung WHERE kBestellung = ' . $orderID,
                ReturnType::SINGLE_OBJECT
            );
        }
    }

    /**
     * @param array $xml
     */
    private function handleDeleteOnly(array $xml): void
    {
        $orderIDs = \is_array($xml['del_bestellungen']['kBestellung'])
            ? $xml['del_bestellungen']['kBestellung']
            : [$xml['del_bestellungen']['kBestellung']];
        foreach (\array_filter(\array_map('\intval', $orderIDs)) as $orderID) {
            $module = $this->getPaymentMethod($orderID);
            if ($module) {
                $module->cancelOrder($orderID, true);
            }
            $this->deleteOrder($orderID);
        }
    }

    /**
     * @param array $xml
     */
    private function handleCancelation(array $xml): void
    {
        $source = $xml['storno_bestellungen']['kBestellung'] ?? [];
        if (\is_numeric($source)) {
            $source = [$source];
        }
        foreach (\array_filter(\array_map('\intval', $source)) as $orderID) {
            $module   = $this->getPaymentMethod($orderID);
            $tmpOrder = new Bestellung($orderID);
            $customer = new Customer($tmpOrder->kKunde);
            $tmpOrder->fuelleBestellung();
            if ($module) {
                $module->cancelOrder($orderID);
            } else {
                if (!empty($customer->cMail) && ($tmpOrder->Zahlungsart->nMailSenden & \ZAHLUNGSART_MAIL_STORNO)) {
                    $data              = new stdClass;
                    $data->tkunde      = $customer;
                    $data->tbestellung = $tmpOrder;

                    $mailer = Shop::Container()->get(Mailer::class);
                    $mail   = new Mail();
                    $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_BESTELLUNG_STORNO, $data));
                }
                $this->db->update(
                    'tbestellung',
                    'kBestellung',
                    $orderID,
                    (object)['cStatus' => \BESTELLUNG_STATUS_STORNO]
                );
            }
            \executeHook(\HOOK_BESTELLUNGEN_XML_BEARBEITESTORNO, [
                'oBestellung' => &$tmpOrder,
                'oKunde'      => &$customer,
                'oModule'     => $module
            ]);
        }
    }

    /**
     * @param array $xml
     */
    private function handleReactivation(array $xml): void
    {
        $source = $xml['reaktiviere_bestellungen']['kBestellung'] ?? [];
        if (\is_numeric($source)) {
            $source = [$source];
        }
        foreach (\array_filter(\array_map('\intval', $source)) as $orderID) {
            $module = $this->getPaymentMethod($orderID);
            if ($module) {
                $module->reactivateOrder($orderID);
            } else {
                $tmpOrder = new Bestellung($orderID);
                $customer = new Customer($tmpOrder->kKunde);
                $tmpOrder->fuelleBestellung();
                if (($tmpOrder->Zahlungsart->nMailSenden & \ZAHLUNGSART_MAIL_STORNO) && \strlen($customer->cMail) > 0) {
                    $data              = new stdClass;
                    $data->tkunde      = $customer;
                    $data->tbestellung = $tmpOrder;

                    $mailer = Shop::Container()->get(Mailer::class);
                    $mail   = new Mail();
                    $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_BESTELLUNG_RESTORNO, $data));
                }
                $this->db->update(
                    'tbestellung',
                    'kBestellung',
                    $orderID,
                    (object)['cStatus' => \BESTELLUNG_STATUS_IN_BEARBEITUNG]
                );
            }
        }
    }

    /**
     * @param array $xml
     */
    private function handlePaymentACK(array $xml): void
    {
        $source = $xml['ack_zahlungseingang']['kZahlungseingang'] ?? [];
        if (\is_numeric($source)) {
            $source = [$source];
        }
        foreach (\array_filter(\array_map('\intval', $source)) as $id) {
            $this->db->update(
                'tzahlungseingang',
                'kZahlungseingang',
                $id,
                (object)['cAbgeholt' => 'Y']
            );
        }
    }

    /**
     * @param array $xml
     */
    private function handleUpdate(array $xml): void
    {
        $order  = new stdClass;
        $orders = $this->mapper->mapArray($xml, 'tbestellung', 'mBestellung');
        if (\count($orders) === 1) {
            $order = $orders[0];
        }
        if (empty($order->kBestellung)) {
            \syncException(
                'Keine kBestellung in tbestellung! XML:' . \print_r($xml, true),
                \FREIDEFINIERBARER_FEHLER
            );
        }
        $order->kBestellung = (int)$order->kBestellung;
        $oldOrder           = $this->getShopOrder($order->kBestellung);
        if ($oldOrder === null) {
            \syncException(
                'Keine Bestellung in Shop gefunden:' . \print_r($xml, true),
                \FREIDEFINIERBARER_FEHLER
            );
        }
        $billingAddress = $this->getBillingAddress($oldOrder, $xml);
        if (!$oldOrder->kBestellung || \trim($order->cBestellNr) !== \trim($oldOrder->cBestellNr)) {
            \syncException(
                'Fehler: Zur Bestellung ' . $order->cBestellNr .
                ' gibt es keine Bestellung im Shop! Bestellung wurde nicht aktualisiert!',
                \FREIDEFINIERBARER_FEHLER
            );
        }
        $paymentMethod    = $this->getPaymentMethodFromXML($order, $xml);
        $correctionFactor = $this->applyCorrectionFactor($order);
        // Die Wawi schickt in fGesamtsumme die Rechnungssumme (Summe aller Positionen), der Shop erwartet hier
        // aber tatsächlich eine Gesamtsumme oder auch den Zahlungsbetrag (Rechnungssumme abzgl. evtl. Guthaben)
        $order->fGesamtsumme -= $order->fGuthaben;

        $this->updateOrderData($oldOrder, $order, $paymentMethod);
        $this->updateAddresses($oldOrder, $billingAddress, $xml);
        $this->updateCartItems($oldOrder, $correctionFactor, $xml);
        if (isset($xml['tbestellung']['tbestellattribut'])) {
            $this->editAttributes(
                $order->kBestellung,
                $this->mapper->isAssoc($xml['tbestellung']['tbestellattribut'])
                    ? [$xml['tbestellung']['tbestellattribut']]
                    : $xml['tbestellung']['tbestellattribut']
            );
        }
        $customer = new Customer((int)$oldOrder->kKunde);
        $this->sendMail($oldOrder, $order, $customer);

        \executeHook(\HOOK_BESTELLUNGEN_XML_BEARBEITEUPDATE, [
            'oBestellung'    => &$order,
            'oBestellungAlt' => &$oldOrder,
            'oKunde'         => &$customer
        ]);
    }

    /**
     * @param stdClass         $oldOrder
     * @param Rechnungsadresse $billingAddress
     * @param array            $xml
     */
    private function updateAddresses($oldOrder, $billingAddress, array $xml)
    {
        $deliveryAddress = new Lieferadresse($oldOrder->kLieferadresse);
        $this->mapper->mapObject($deliveryAddress, $xml['tbestellung']['tlieferadresse'], 'mLieferadresse');
        if (isset($deliveryAddress->cAnrede)) {
            $deliveryAddress->cAnrede = $this->mapSalutation($deliveryAddress->cAnrede);
        }
        // Hausnummer extrahieren
        $this->extractStreet($deliveryAddress);
        // Workaround for WAWI-39370
        $deliveryAddress->cLand = Adresse::checkISOCountryCode($deliveryAddress->cLand);
        // lieferadresse ungleich rechungsadresse?
        if ($deliveryAddress->cVorname !== $billingAddress->cVorname
            || $deliveryAddress->cNachname !== $billingAddress->cNachname
            || $deliveryAddress->cStrasse !== $billingAddress->cStrasse
            || $deliveryAddress->cHausnummer !== $billingAddress->cHausnummer
            || $deliveryAddress->cPLZ !== $billingAddress->cPLZ
            || $deliveryAddress->cOrt !== $billingAddress->cOrt
            || $deliveryAddress->cLand !== $billingAddress->cLand
        ) {
            if ($deliveryAddress->kLieferadresse > 0) {
                $deliveryAddress->updateInDB();
            } else {
                $deliveryAddress->kKunde         = $oldOrder->kKunde;
                $deliveryAddress->kLieferadresse = $deliveryAddress->insertInDB();
                $this->db->query(
                    'UPDATE tbestellung
                    SET kLieferadresse = ' . (int)$deliveryAddress->kLieferadresse . '
                    WHERE kBestellung = ' . (int)$oldOrder->kBestellung,
                    ReturnType::DEFAULT
                );
            }
        } elseif ($oldOrder->kLieferadresse > 0) {
            $this->db->update(
                'tbestellung',
                'kBestellung',
                (int)$oldOrder->kBestellung,
                (object)['kLieferadresse' => 0]
            );
        }
        $billingAddress->updateInDB();
    }

    /**
     * @param stdClass $order
     * @return float
     */
    private function applyCorrectionFactor($order): float
    {
        $correctionFactor = 1.0;
        if (isset($order->kWaehrung)) {
            $currentCurrency = $this->db->select('twaehrung', 'kWaehrung', $order->kWaehrung);
            $defaultCurrency = $this->db->select('twaehrung', 'cStandard', 'Y');
            if (isset($currentCurrency->kWaehrung, $defaultCurrency->kWaehrung)) {
                $correctionFactor     = (float)$currentCurrency->fFaktor;
                $order->fGesamtsumme /= $correctionFactor;
                $order->fGuthaben    /= $correctionFactor;
            }
        }

        return $correctionFactor;
    }

    /**
     * @param stdClass $order
     * @param array    $xml
     * @return stdClass|null
     */
    private function getPaymentMethodFromXML($order, array $xml): ?stdClass
    {
        if (empty($xml['tbestellung']['cZahlungsartName'])) {
            return null;
        }
        // Von Wawi kommt in $xml['tbestellung']['cZahlungsartName'] nur der deutsche Wert,
        // deshalb immer Abfrage auf tzahlungsart.cName
        $paymentMethodName = $xml['tbestellung']['cZahlungsartName'];
        $res               = $this->db->executeQueryPrepared(
            'SELECT tzahlungsart.kZahlungsart, IFNULL(tzahlungsartsprache.cName, tzahlungsart.cName) AS cName
            FROM tzahlungsart
            LEFT JOIN tzahlungsartsprache
                ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                AND tzahlungsartsprache.cISOSprache = :iso
            WHERE tzahlungsart.cName LIKE :search
            ORDER BY CASE
                WHEN tzahlungsart.cName = :name1 THEN 1
                WHEN tzahlungsart.cName LIKE :name2 THEN 2
                WHEN tzahlungsart.cName LIKE :name3 THEN 3
                END, kZahlungsart',
            [
                'iso'    => LanguageHelper::getLanguageDataByType('', (int)$order->kSprache),
                'search' => '%' . $paymentMethodName . '%',
                'name1'  => $paymentMethodName,
                'name2'  => $paymentMethodName . '%',
                'name3'  => '%' . $paymentMethodName . '%',
            ],
            ReturnType::SINGLE_OBJECT
        );

        return isset($res->kZahlungsart) ? $res : null;
    }

    /**
     * @param stdClass $oldOrder
     * @param array    $xml
     * @return Rechnungsadresse
     */
    private function getBillingAddress($oldOrder, array $xml): Rechnungsadresse
    {
        $billingAddress = new Rechnungsadresse($oldOrder->kRechnungsadresse);
        $this->mapper->mapObject($billingAddress, $xml['tbestellung']['trechnungsadresse'], 'mRechnungsadresse');
        if (!empty($billingAddress->cAnrede)) {
            $billingAddress->cAnrede = $this->mapSalutation($billingAddress->cAnrede);
        }
        $this->extractStreet($billingAddress);
        // Workaround for WAWI-39370
        $billingAddress->cLand = Adresse::checkISOCountryCode($billingAddress->cLand);
        if (!$billingAddress->cNachname && !$billingAddress->cFirma && !$billingAddress->cStrasse) {
            \syncException(
                'Error Bestellung Update. Rechnungsadresse enthält keinen Nachnamen, Firma und Strasse! XML:' .
                \print_r($xml, true),
                \FREIDEFINIERBARER_FEHLER
            );
        }

        return $billingAddress;
    }

    /**
     * @param stdClass $oldOrder
     * @param stdClass $order
     * @param stdClass $paymentMethod
     */
    private function updateOrderData($oldOrder, $order, $paymentMethod): void
    {
        $updateSql = '';
        if (isset($paymentMethod->kZahlungsart) && $paymentMethod->kZahlungsart > 0) {
            $updateSql = ' , kZahlungsart = ' . (int)$paymentMethod->kZahlungsart .
                ", cZahlungsartName = '" . $paymentMethod->cName . "' ";
        }
        $this->db->queryPrepared(
            'UPDATE tbestellung SET
            fGuthaben = :fg,
            fGesamtsumme = :total,
            cKommentar = :cmt ' . $updateSql . '
            WHERE kBestellung = :oid',
            [
                'fg'    => $order->fGuthaben,
                'total' => $order->fGesamtsumme,
                'cmt'   => $order->cKommentar,
                'oid'   => $oldOrder->kBestellung
            ],
            ReturnType::DEFAULT
        );
    }

    /**
     * @param stdClass $oldOrder
     * @param stdClass $order
     * @param Customer $customer
     */
    private function sendMail($oldOrder, $order, $customer): void
    {
        $module = $this->getPaymentMethod($oldOrder->kBestellung);
        $mail   = new Mail();
        $test   = $mail->createFromTemplateID(\MAILTEMPLATE_BESTELLUNG_AKTUALISIERT);
        $tpl    = $test->getTemplate();
        if ($tpl !== null
            && $tpl->getModel() !== null
            && $tpl->getModel()->getActive() === true
            && ($order->cSendeEMail === 'Y' || !isset($order->cSendeEMail))
        ) {
            if ($module) {
                $module->sendMail($oldOrder->kBestellung, \MAILTEMPLATE_BESTELLUNG_AKTUALISIERT);
            } else {
                $data              = new stdClass;
                $data->tkunde      = $customer;
                $data->tbestellung = new Bestellung((int)$oldOrder->kBestellung, true);

                $mailer = Shop::Container()->get(Mailer::class);
                $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_BESTELLUNG_AKTUALISIERT, $data));
            }
        }
    }

    /**
     * @param stdClass $oldOrder
     * @param float    $correctionFactor
     * @param array    $xml
     */
    private function updateCartItems($oldOrder, $correctionFactor, array $xml): void
    {
        $oldItems = $this->db->selectAll(
            'twarenkorbpos',
            'kWarenkorb',
            $oldOrder->kWarenkorb
        );
        $map      = [];
        foreach ($oldItems as $key => $oldItem) {
            $this->db->delete(
                'twarenkorbposeigenschaft',
                'kWarenkorbPos',
                (int)$oldItem->kWarenkorbPos
            );
            if ($oldItem->kArtikel > 0) {
                $map[$oldItem->kArtikel] = $key;
            }
        }
        $this->db->delete('twarenkorbpos', 'kWarenkorb', $oldOrder->kWarenkorb);
        $cartItems = $this->mapper->mapArray($xml['tbestellung'], 'twarenkorbpos', 'mWarenkorbpos');
        $itemCount = \count($cartItems);
        for ($i = 0; $i < $itemCount; $i++) {
            $oldItem = \array_key_exists($cartItems[$i]->kArtikel, $map)
                ? $oldItems[$map[$cartItems[$i]->kArtikel]]
                : null;
            unset($cartItems[$i]->kWarenkorbPos);
            $cartItems[$i]->kWarenkorb         = $oldOrder->kWarenkorb;
            $cartItems[$i]->fPreis            /= $correctionFactor;
            $cartItems[$i]->fPreisEinzelNetto /= $correctionFactor;
            // persistiere nLongestMin/MaxDelivery wenn nicht von Wawi übetragen
            if (!isset($cartItems[$i]->nLongestMinDelivery)) {
                $cartItems[$i]->nLongestMinDelivery = $oldItem->nLongestMinDelivery ?? 0;
            }
            if (!isset($cartItems[$i]->nLongestMaxDelivery)) {
                $cartItems[$i]->nLongestMaxDelivery = $oldItem->nLongestMaxDelivery ?? 0;
            }
            $cartItems[$i]->kWarenkorbPos = $this->db->insert(
                'twarenkorbpos',
                $cartItems[$i]
            );

            if (\count($cartItems) < 2) {
                $cartItemAttributes = $this->mapper->mapArray(
                    $xml['tbestellung']['twarenkorbpos'],
                    'twarenkorbposeigenschaft',
                    'mWarenkorbposeigenschaft'
                );
            } else {
                $cartItemAttributes = $this->mapper->mapArray(
                    $xml['tbestellung']['twarenkorbpos'][$i],
                    'twarenkorbposeigenschaft',
                    'mWarenkorbposeigenschaft'
                );
            }
            foreach ($cartItemAttributes as $posAttribute) {
                unset($posAttribute->kWarenkorbPosEigenschaft);
                $posAttribute->kWarenkorbPos = $cartItems[$i]->kWarenkorbPos;
                $this->db->insert('twarenkorbposeigenschaft', $posAttribute);
            }
        }
    }

    /**
     * @param int $orderID
     * @return stdClass|null
     */
    private function getShopOrder(int $orderID): ?stdClass
    {
        $order = $this->db->select('tbestellung', 'kBestellung', $orderID);
        if (!isset($order->kBestellung) || $order->kBestellung <= 0) {
            return null;
        }
        $order->kBestellung       = (int)$order->kBestellung;
        $order->kWarenkorb        = (int)$order->kWarenkorb;
        $order->kKunde            = (int)$order->kKunde;
        $order->kRechnungsadresse = (int)$order->kRechnungsadresse;
        $order->kLieferadresse    = (int)$order->kLieferadresse;
        $order->kZahlungsart      = (int)$order->kZahlungsart;
        $order->kVersandart       = (int)$order->kVersandart;
        $order->kSprache          = (int)$order->kSprache;
        $order->kWaehrung         = (int)$order->kWaehrung;
        $order->cStatus           = (int)$order->cStatus;

        return $order;
    }

    /**
     * @param stdClass $shopOrder
     * @param stdClass $order
     * @return int
     */
    private function getOrderState(stdClass $shopOrder, stdClass $order): int
    {
        if ($shopOrder->cStatus === \BESTELLUNG_STATUS_STORNO) {
            return \BESTELLUNG_STATUS_STORNO;
        }
        $state = \BESTELLUNG_STATUS_IN_BEARBEITUNG;
        if (isset($order->cBezahlt) && $order->cBezahlt === 'Y') {
            $state = \BESTELLUNG_STATUS_BEZAHLT;
        }
        if (isset($order->dVersandt) && \strlen($order->dVersandt) > 0) {
            $state = \BESTELLUNG_STATUS_VERSANDT;
        }
        $updatedOrder = new Bestellung($shopOrder->kBestellung, true);
        if ((\count($updatedOrder->oLieferschein_arr) > 0)
            && (isset($order->nKomplettAusgeliefert) && (int)$order->nKomplettAusgeliefert === 0)
        ) {
            $state = \BESTELLUNG_STATUS_TEILVERSANDT;
        }

        return $state;
    }

    /**
     * @param stdClass $shopOrder
     * @param stdClass $order
     * @return string
     */
    private function getTrackingURL(stdClass $shopOrder, stdClass $order): string
    {
        $trackingURL = '';
        if ($order->cIdentCode !== null && \strlen($order->cIdentCode) > 0) {
            $trackingURL = $order->cLogistikURL;
            if ($shopOrder->kLieferadresse > 0) {
                $deliveryAddress = $this->db->queryPrepared(
                    'SELECT cPLZ
                        FROM tlieferadresse 
                        WHERE kLieferadresse = :dai',
                    ['dai' => $shopOrder->kLieferadresse],
                    ReturnType::SINGLE_OBJECT
                );
                if ($deliveryAddress->cPLZ) {
                    $trackingURL = \str_replace('#PLZ#', $deliveryAddress->cPLZ, $trackingURL);
                }
            } else {
                $customer    = new Customer($shopOrder->kKunde);
                $trackingURL = \str_replace('#PLZ#', $customer->cPLZ, $trackingURL);
            }
            $trackingURL = \str_replace('#IdentCode#', $order->cIdentCode, $trackingURL);
        }

        return $trackingURL;
    }

    /**
     * @param stdClass $shopOrder
     * @param stdClass $order
     * @param int      $state
     * @return Bestellung
     */
    private function updateOrder(stdClass $shopOrder, stdClass $order, int $state): Bestellung
    {
        $trackingURL = $this->getTrackingURL($shopOrder, $order);
        $methodName  = $this->db->escape($order->cZahlungsartName);
        $clearedDate = $this->db->escape($order->dBezahltDatum);
        $shippedDate = $this->db->escape($order->dVersandt);
        if ($shippedDate === null || $shippedDate === '') {
            $shippedDate = '_DBNULL_';
        }

        $upd                = new stdClass;
        $upd->dVersandDatum = $shippedDate;
        $upd->cTracking     = $this->db->escape($order->cIdentCode);
        $upd->cLogistiker   = $this->db->escape($order->cLogistik);
        $upd->cTrackingURL  = $this->db->escape($trackingURL);
        $upd->cStatus       = $state;
        $upd->cVersandInfo  = $this->db->escape($order->cVersandInfo);
        if (\strlen($methodName) > 0) {
            $upd->cZahlungsartName = $methodName;
        }
        $upd->dBezahltDatum = empty($clearedDate)
            ? '_DBNULL_'
            : $clearedDate;

        $this->db->update('tbestellung', 'kBestellung', $order->kBestellung, $upd);

        return new Bestellung($shopOrder->kBestellung, true);
    }

    /**
     * @param Bestellung $updatedOrder
     * @param stdClass   $shopOrder
     * @param int        $state
     * @param Customer   $customer
     */
    private function sendStatusMail(Bestellung $updatedOrder, stdClass $shopOrder, int $state, $customer): void
    {
        $doSend = false;
        foreach ($updatedOrder->oLieferschein_arr as $note) {
            /** @var Lieferschein $note */
            if ($note->getEmailVerschickt() === false) {
                $doSend = true;
                break;
            }
        }
        if (($state === \BESTELLUNG_STATUS_VERSANDT && $shopOrder->cStatus !== \BESTELLUNG_STATUS_VERSANDT)
            || ($state === \BESTELLUNG_STATUS_TEILVERSANDT && $doSend === true)
        ) {
            $mailType = $state === \BESTELLUNG_STATUS_VERSANDT
                ? \MAILTEMPLATE_BESTELLUNG_VERSANDT
                : \MAILTEMPLATE_BESTELLUNG_TEILVERSANDT;
            $module   = $this->getPaymentMethod($shopOrder->kBestellung);
            if (!isset($updatedOrder->oVersandart->cSendConfirmationMail)
                || $updatedOrder->oVersandart->cSendConfirmationMail !== 'N'
            ) {
                if ($module) {
                    $module->sendMail($shopOrder->kBestellung, $mailType);
                } else {
                    $data              = new stdClass;
                    $data->tkunde      = $customer;
                    $data->tbestellung = $updatedOrder;

                    $mailer = Shop::Container()->get(Mailer::class);
                    $mail   = new Mail();
                    $mailer->send($mail->createFromTemplateID($mailType, $data));
                }
            }
            /** @var Lieferschein $note */
            foreach ($updatedOrder->oLieferschein_arr as $note) {
                $note->setEmailVerschickt(true)->update();
            }
        }
    }

    /**
     * @param stdClass $shopOrder
     * @param stdClass $order
     * @param Customer $customer
     */
    private function sendPaymentMail(stdClass $shopOrder, stdClass $order, $customer): void
    {
        if (!$shopOrder->dBezahltDatum && $order->dBezahltDatum && $customer->kKunde > 0) {
            $module = $this->getPaymentMethod($order->kBestellung);
            if ($module) {
                $module->sendMail($order->kBestellung, \MAILTEMPLATE_BESTELLUNG_BEZAHLT);
            } else {
                $updatedOrder = new Bestellung((int)$shopOrder->kBestellung, true);
                if (($updatedOrder->Zahlungsart->nMailSenden & \ZAHLUNGSART_MAIL_EINGANG)
                    && \strlen($customer->cMail) > 0
                ) {
                    $data              = new stdClass;
                    $data->tkunde      = $customer;
                    $data->tbestellung = $updatedOrder;

                    $mailer = Shop::Container()->get(Mailer::class);
                    $mail   = new Mail();
                    $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_BESTELLUNG_BEZAHLT, $data));
                }
            }
        }
    }

    /**
     * @param array $xml
     */
    private function handleSet(array $xml): void
    {
        $orders = $this->mapper->mapArray($xml['tbestellungen'], 'tbestellung', 'mBestellung');
        foreach ($orders as $order) {
            $order->kBestellung = (int)$order->kBestellung;
            $shopOrder          = $this->getShopOrder($order->kBestellung);
            if ($shopOrder === null) {
                continue;
            }

            $state = $this->getOrderState($shopOrder, $order);
            \executeHook(\HOOK_BESTELLUNGEN_XML_BESTELLSTATUS, [
                'status'      => &$state,
                'oBestellung' => &$shopOrder
            ]);
            $updatedOrder = $this->updateOrder($shopOrder, $order, $state);
            $customer     = null;
            if ((!$shopOrder->dVersandDatum && $order->dVersandt)
                || (!$shopOrder->dBezahltDatum && $order->dBezahltDatum)
            ) {
                $tmp      = $this->db->query(
                    'SELECT kKunde FROM tbestellung WHERE kBestellung = ' . $order->kBestellung,
                    ReturnType::SINGLE_OBJECT
                );
                $customer = new Customer((int)$tmp->kKunde);
            }
            if ($customer === null) {
                $customer = new Customer($shopOrder->kKunde);
            }
            $this->sendStatusMail($updatedOrder, $shopOrder, $state, $customer);
            $this->sendPaymentMail($shopOrder, $order, $customer);

            \executeHook(\HOOK_BESTELLUNGEN_XML_BEARBEITESET, [
                'oBestellung'     => &$shopOrder,
                'oKunde'          => &$customer,
                'oBestellungWawi' => &$order
            ]);
        }
    }

    /**
     * @param int $orderID
     */
    private function deleteOrder(int $orderID): void
    {
        $cartID = (int)($this->db->select(
            'tbestellung',
            'kBestellung',
            $orderID,
            null,
            null,
            null,
            null,
            false,
            'kWarenkorb'
        )->kWarenkob ?? 0);
        $this->db->delete('tbestellung', 'kBestellung', $orderID);
        $this->db->delete('tbestellid', 'kBestellung', $orderID);
        $this->db->delete('tbestellstatus', 'kBestellung', $orderID);
        $this->db->delete('tkuponbestellung', 'kBestellung', $orderID);
        $this->db->delete('tuploaddatei', ['kCustomID', 'nTyp'], [$orderID, \UPLOAD_TYP_BESTELLUNG]);
        $this->db->delete('tuploadqueue', 'kBestellung', $orderID);
        if ($cartID > 0) {
            $this->db->delete('twarenkorb', 'kWarenkorb', $cartID);
            $this->db->delete('twarenkorbpos', 'kWarenkorb', $cartID);
            foreach ($this->db->selectAll(
                'twarenkorbpos',
                'kWarenkorb',
                $cartID,
                'kWarenkorbPos'
            ) as $item) {
                $this->db->delete(
                    'twarenkorbposeigenschaft',
                    'kWarenkorbPos',
                    (int)$item->kWarenkorbPos
                );
            }
        }
    }

    /**
     * @param int        $orderID
     * @param stdClass[] $orderAttributes
     */
    private function editAttributes(int $orderID, $orderAttributes): void
    {
        $updated = [];
        if (\is_array($orderAttributes)) {
            foreach ($orderAttributes as $orderAttributeData) {
                $orderAttribute    = (object)$orderAttributeData;
                $orderAttributeOld = $this->db->select(
                    'tbestellattribut',
                    ['kBestellung', 'cName'],
                    [$orderID, $orderAttribute->key]
                );
                if (isset($orderAttributeOld->kBestellattribut)) {
                    $this->db->update(
                        'tbestellattribut',
                        'kBestellattribut',
                        $orderAttributeOld->kBestellattribut,
                        (object)['cValue' => $orderAttribute->value]
                    );
                    $updated[] = $orderAttributeOld->kBestellattribut;
                } else {
                    $updated[] = $this->db->insert('tbestellattribut', (object)[
                        'kBestellung' => $orderID,
                        'cName'       => $orderAttribute->key,
                        'cValue'      => $orderAttribute->value,
                    ]);
                }
            }
        }

        if (\count($updated) > 0) {
            $this->db->query(
                'DELETE FROM tbestellattribut
                WHERE kBestellung = ' . $orderID . '
                    AND kBestellattribut NOT IN (' . \implode(', ', $updated) . ')',
                ReturnType::DEFAULT
            );
        } else {
            $this->db->delete('tbestellattribut', 'kBestellung', $orderID);
        }
    }
}
