<?php

use JTL\Cart\CartItem;
use JTL\Catalog\Currency;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\EigenschaftWert;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\CheckBox;
use JTL\Checkout\Bestellung;
use JTL\Checkout\Kupon;
use JTL\Checkout\KuponBestellung;
use JTL\Checkout\Lieferadresse;
use JTL\Checkout\Nummern;
use JTL\Checkout\Rechnungsadresse;
use JTL\Checkout\ZahlungsInfo;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Extensions\Upload\Upload;
use JTL\Helpers\Date;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Campaign;
use JTL\Language\LanguageHelper;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Plugin\Helper;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * @return int
 */
function bestellungKomplett(): int
{
    $checkbox                = new CheckBox();
    $_SESSION['cPlausi_arr'] = $checkbox->validateCheckBox(
        CHECKBOX_ORT_BESTELLABSCHLUSS,
        Frontend::getCustomerGroup()->getID(),
        $_POST,
        true
    );
    $_SESSION['cPost_arr']   = $_POST;

    return (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart'])
        && $_SESSION['Kunde']
        && $_SESSION['Lieferadresse']
        && (int)$_SESSION['Versandart']->kVersandart > 0
        && (int)$_SESSION['Zahlungsart']->kZahlungsart > 0
        && Request::verifyGPCDataInt('abschluss') === 1
        && count($_SESSION['cPlausi_arr']) === 0
    ) ? 1 : 0;
}

/**
 * @return int
 */
function gibFehlendeEingabe(): int
{
    if (!isset($_SESSION['Kunde']) || !$_SESSION['Kunde']) {
        return 1;
    }
    if (!isset($_SESSION['Lieferadresse']) || !$_SESSION['Lieferadresse']) {
        return 2;
    }
    if (!isset($_SESSION['Versandart'])
        || !$_SESSION['Versandart']
        || (int)$_SESSION['Versandart']->kVersandart === 0
    ) {
        return 3;
    }
    if (!isset($_SESSION['Zahlungsart'])
        || !$_SESSION['Zahlungsart']
        || (int)$_SESSION['Zahlungsart']->kZahlungsart === 0
    ) {
        return 4;
    }
    if (count($_SESSION['cPlausi_arr']) > 0) {
        return 6;
    }

    return -1;
}

/**
 * @param int    $cleared
 * @param string $orderNo
 */
function bestellungInDB($cleared = 0, $orderNo = '')
{
    unhtmlSession();
    $order             = new Bestellung();
    $customer          = Frontend::getCustomer();
    $deliveryAddress   = Frontend::getDeliveryAddress();
    $db                = Shop::Container()->getDB();
    $cart              = Frontend::getCart();
    $order->cBestellNr = empty($orderNo) ? baueBestellnummer() : $orderNo;
    $cartItems         = [];
    if (Frontend::getCustomer()->getID() <= 0) {
        $customerAttributes      = $customer->getCustomerAttributes();
        $customer->kKundengruppe = Frontend::getCustomerGroup()->getID();
        $customer->kSprache      = Shop::getLanguageID();
        $customer->cAbgeholt     = 'N';
        $customer->cAktiv        = 'Y';
        $customer->cSperre       = 'N';
        $customer->dErstellt     = 'NOW()';
        $customer->nRegistriert  = 0;
        $cPasswortKlartext       = '';
        if ($customer->cPasswort) {
            $customer->nRegistriert = 1;
            $cPasswortKlartext      = $customer->cPasswort;
            $customer->cPasswort    = md5($customer->cPasswort);
        }
        $cart->kKunde = $customer->insertInDB();
        if (Frontend::get('customerAttributes') !== null) {
            $customerAttributes->assign(Frontend::get('customerAttributes'));
        }
        $customer->kKunde = $cart->kKunde;
        $customer->cLand  = $customer->pruefeLandISO($customer->cLand);
        $customerAttributes->setCustomerID($customer->kKunde);
        $customerAttributes->save();
        Frontend::set('customerAttributes', null);

        if (!empty($customer->cPasswort)) {
            $customer->cPasswortKlartext = $cPasswortKlartext;

            $obj         = new stdClass();
            $obj->tkunde = $customer;

            executeHook(HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_NEUKUNDENREGISTRIERUNG);

            $mailer = Shop::Container()->get(Mailer::class);
            $mail   = new Mail();
            $mailer->send($mail->createFromTemplateID(MAILTEMPLATE_NEUKUNDENREGISTRIERUNG, $obj));
        }
    } else {
        $cart->kKunde = $customer->kKunde;
        $db->update(
            'tkunde',
            'kKunde',
            $customer->kKunde,
            (object)['cAbgeholt' => 'N']
        );
    }
    $cart->kLieferadresse = 0; //=rechnungsadresse
    if (isset($_SESSION['Bestellung']->kLieferadresse)
        && $_SESSION['Bestellung']->kLieferadresse == -1
        && !$deliveryAddress->kLieferadresse
    ) {
        $deliveryAddress->kKunde = $cart->kKunde;
        executeHook(
            HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_LIEFERADRESSE_NEU,
            ['deliveryAddress' => $deliveryAddress]
        );
        $cart->kLieferadresse = $deliveryAddress->insertInDB();
    } elseif (isset($_SESSION['Bestellung']->kLieferadresse) && $_SESSION['Bestellung']->kLieferadresse > 0) {
        executeHook(
            HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_LIEFERADRESSE_ALT,
            ['deliveryAddressID' => (int)$_SESSION['Bestellung']->kLieferadresse]
        );
        $cart->kLieferadresse = $_SESSION['Bestellung']->kLieferadresse;
    }
    $conf = Shop::getSettings([CONF_GLOBAL]);
    //füge Warenkorb ein
    executeHook(HOOK_BESTELLABSCHLUSS_INC_WARENKORBINDB, ['oWarenkorb' => &$cart]);
    $cart->kWarenkorb = $cart->insertInDB();
    //füge alle Warenkorbpositionen ein
    if (is_array($cart->PositionenArr) && count($cart->PositionenArr) > 0) {
        $productFilter = (int)$conf['global']['artikel_artikelanzeigefilter'];
        /** @var CartItem $item */
        foreach ($cart->PositionenArr as $item) {
            if ($item->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
                $item->fLagerbestandVorAbschluss = $item->Artikel->fLagerbestand !== null
                    ? (double)$item->Artikel->fLagerbestand
                    : 0;
            }
            $item->cName         = Text::unhtmlentities(is_array($item->cName)
                ? $item->cName[$_SESSION['cISOSprache']]
                : $item->cName);
            $item->cLieferstatus = isset($item->cLieferstatus[$_SESSION['cISOSprache']])
                ? Text::unhtmlentities($item->cLieferstatus[$_SESSION['cISOSprache']])
                : '';
            $item->kWarenkorb    = $cart->kWarenkorb;
            $item->fMwSt         = Tax::getSalesTax($item->kSteuerklasse);
            $item->kWarenkorbPos = $item->insertInDB();
            if (is_array($item->WarenkorbPosEigenschaftArr) && count($item->WarenkorbPosEigenschaftArr) > 0) {
                $idx = Shop::getLanguageCode();
                // Bei einem Varkombikind dürfen nur FREIFELD oder PFLICHT-FREIFELD gespeichert werden,
                // da sonst eventuelle Aufpreise in der Wawi doppelt berechnet werden
                if (isset($item->Artikel->kVaterArtikel) && $item->Artikel->kVaterArtikel > 0) {
                    foreach ($item->WarenkorbPosEigenschaftArr as $o => $WKPosEigenschaft) {
                        if ($WKPosEigenschaft->cTyp === 'FREIFELD' || $WKPosEigenschaft->cTyp === 'PFLICHT-FREIFELD') {
                            $WKPosEigenschaft->kWarenkorbPos        = $item->kWarenkorbPos;
                            $WKPosEigenschaft->cEigenschaftName     = $WKPosEigenschaft->cEigenschaftName[$idx];
                            $WKPosEigenschaft->cEigenschaftWertName = $WKPosEigenschaft->cEigenschaftWertName[$idx];
                            $WKPosEigenschaft->cFreifeldWert        = $WKPosEigenschaft->cEigenschaftWertName;
                            $WKPosEigenschaft->insertInDB();
                        }
                    }
                } else {
                    foreach ($item->WarenkorbPosEigenschaftArr as $o => $WKPosEigenschaft) {
                        $WKPosEigenschaft->kWarenkorbPos        = $item->kWarenkorbPos;
                        $WKPosEigenschaft->cEigenschaftName     = $WKPosEigenschaft->cEigenschaftName[$idx];
                        $WKPosEigenschaft->cEigenschaftWertName = $WKPosEigenschaft->cEigenschaftWertName[$idx];
                        if ($WKPosEigenschaft->cTyp === 'FREIFELD' || $WKPosEigenschaft->cTyp === 'PFLICHT-FREIFELD') {
                            $WKPosEigenschaft->cFreifeldWert = $WKPosEigenschaft->cEigenschaftWertName;
                        }
                        $WKPosEigenschaft->insertInDB();
                    }
                }
            }
            //bestseller tabelle füllen
            if ($item->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL && is_object($item->Artikel)) {
                //Lagerbestand verringern
                aktualisiereLagerbestand(
                    $item->Artikel,
                    $item->nAnzahl,
                    $item->WarenkorbPosEigenschaftArr,
                    $productFilter
                );
                aktualisiereBestseller($item->kArtikel, $item->nAnzahl);
                //xsellkauf füllen
                foreach ($cart->PositionenArr as $cartItem) {
                    if ($cartItem->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL && $cartItem->kArtikel != $item->kArtikel) {
                        aktualisiereXselling($item->kArtikel, $cartItem->kArtikel);
                    }
                }
                $cartItems[] = $item;
                Shop::Container()->getCache()->flushTags([CACHING_GROUP_ARTICLE . '_' . $item->kArtikel]);
            } elseif ($item->nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                aktualisiereLagerbestand(
                    $item->Artikel,
                    $item->nAnzahl,
                    $item->WarenkorbPosEigenschaftArr,
                    $productFilter
                );
                $cartItems[] = $item;
                Shop::Container()->getCache()->flushTags([CACHING_GROUP_ARTICLE . '_' . $item->kArtikel]);
            }

            $order->Positionen[] = $item;
        }
        // Falls die Einstellung global_wunschliste_artikel_loeschen_nach_kauf auf Y (Ja) steht und
        // Artikel vom aktuellen Wunschzettel gekauft wurden, sollen diese vom Wunschzettel geloescht werden
        if (isset($_SESSION['Wunschliste']->kWunschliste) && $_SESSION['Wunschliste']->kWunschliste > 0) {
            Wishlist::pruefeArtikelnachBestellungLoeschen(
                $_SESSION['Wunschliste']->kWunschliste,
                $cartItems
            );
        }
    }
    $billingAddress                = new Rechnungsadresse();
    $billingAddress->kKunde        = $customer->kKunde;
    $billingAddress->cAnrede       = $customer->cAnrede;
    $billingAddress->cTitel        = $customer->cTitel;
    $billingAddress->cVorname      = $customer->cVorname;
    $billingAddress->cNachname     = $customer->cNachname;
    $billingAddress->cFirma        = $customer->cFirma;
    $billingAddress->cZusatz       = $customer->cZusatz;
    $billingAddress->cStrasse      = $customer->cStrasse;
    $billingAddress->cHausnummer   = $customer->cHausnummer;
    $billingAddress->cAdressZusatz = $customer->cAdressZusatz;
    $billingAddress->cPLZ          = $customer->cPLZ;
    $billingAddress->cOrt          = $customer->cOrt;
    $billingAddress->cBundesland   = $customer->cBundesland;
    $billingAddress->cLand         = $customer->cLand;
    $billingAddress->cTel          = $customer->cTel;
    $billingAddress->cMobil        = $customer->cMobil;
    $billingAddress->cFax          = $customer->cFax;
    $billingAddress->cUSTID        = $customer->cUSTID;
    $billingAddress->cWWW          = $customer->cWWW;
    $billingAddress->cMail         = $customer->cMail;

    executeHook(HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_RECHNUNGSADRESSE, ['billingAddress' => $billingAddress]);

    $billingAddressID = $billingAddress->insertInDB();
    if (isset($_POST['kommentar'])) {
        $_SESSION['kommentar'] = mb_substr(strip_tags($_POST['kommentar']), 0, 1000);
    } elseif (!isset($_SESSION['kommentar'])) {
        $_SESSION['kommentar'] = '';
    }

    $order->kKunde            = $cart->kKunde;
    $order->kWarenkorb        = $cart->kWarenkorb;
    $order->kLieferadresse    = $cart->kLieferadresse;
    $order->kRechnungsadresse = $billingAddressID;
    $order->kZahlungsart      = $_SESSION['Zahlungsart']->kZahlungsart;
    $order->kVersandart       = $_SESSION['Versandart']->kVersandart;
    $order->kSprache          = Shop::getLanguageID();
    $order->kWaehrung         = Frontend::getCurrency()->getID();
    $order->fGesamtsumme      = Frontend::getCart()->gibGesamtsummeWaren(true);
    $order->cVersandartName   = $_SESSION['Versandart']->angezeigterName[$_SESSION['cISOSprache']];
    $order->cZahlungsartName  = $_SESSION['Zahlungsart']->angezeigterName[$_SESSION['cISOSprache']];
    $order->cSession          = session_id();
    $order->cKommentar        = $_SESSION['kommentar'];
    $order->cAbgeholt         = 'N';
    $order->cStatus           = BESTELLUNG_STATUS_OFFEN;
    $order->dErstellt         = 'NOW()';
    $order->berechneEstimatedDelivery();
    if (isset($_SESSION['Bestellung']->GuthabenNutzen) && (int)$_SESSION['Bestellung']->GuthabenNutzen === 1) {
        $order->fGuthaben = -$_SESSION['Bestellung']->fGuthabenGenutzt;
        $db->queryPrepared(
            'UPDATE tkunde
                SET fGuthaben = fGuthaben - :cred
                WHERE kKunde = :cid',
            [
                'cred' => (float)$_SESSION['Bestellung']->fGuthabenGenutzt,
                'cid'  => (int)$order->kKunde
            ],
            ReturnType::DEFAULT
        );
        $customer->fGuthaben -= $_SESSION['Bestellung']->fGuthabenGenutzt;
    }
    // Gesamtsumme entspricht 0
    if ($order->fGesamtsumme == 0) {
        $order->cStatus          = BESTELLUNG_STATUS_BEZAHLT;
        $order->dBezahltDatum    = 'NOW()';
        $order->cZahlungsartName = Shop::Lang()->get('paymentNotNecessary', 'checkout');
    }
    // no anonymization is done here anymore, cause we got a contract
    $order->cIP = $_SESSION['IP']->cIP ?? Request::getRealIP();
    //#8544
    $order->fWaehrungsFaktor = Frontend::getCurrency()->getConversionFactor();

    executeHook(HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB, ['oBestellung' => &$order]);

    $orderID = $order->insertInDB();

    // OrderAttributes
    if (!empty($_SESSION['Warenkorb']->OrderAttributes)) {
        foreach ($_SESSION['Warenkorb']->OrderAttributes as $orderAttr) {
            $obj              = new stdClass();
            $obj->kBestellung = $orderID;
            $obj->cName       = $orderAttr->cName;
            $obj->cValue      = $orderAttr->cName === 'Finanzierungskosten'
                ? (float)str_replace(',', '.', $orderAttr->cValue)
                : $orderAttr->cValue;
            Shop::Container()->getDB()->insert('tbestellattribut', $obj);
        }
    }

    $logger = Shop::Container()->getLogService();
    if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
        $logger->withName('kBestellung')->debug('Bestellung gespeichert: ' . print_r($order, true), [$orderID]);
    }
    //BestellID füllen
    $bestellid              = new stdClass();
    $bestellid->cId         = uniqid('', true);
    $bestellid->kBestellung = $order->kBestellung;
    $bestellid->dDatum      = 'NOW()';
    $db->insert('tbestellid', $bestellid);
    //bestellstatus füllen
    $bestellstatus              = new stdClass();
    $bestellstatus->kBestellung = $order->kBestellung;
    $bestellstatus->dDatum      = 'NOW()';
    $bestellstatus->cUID        = uniqid('', true);
    $db->insert('tbestellstatus', $bestellstatus);
    //füge ZahlungsInfo ein, falls es die Versandart erfordert
    if (isset($_SESSION['Zahlungsart']->ZahlungsInfo) && $_SESSION['Zahlungsart']->ZahlungsInfo) {
        saveZahlungsInfo($order->kKunde, $order->kBestellung);
    }

    $_SESSION['BestellNr']   = $order->cBestellNr;
    $_SESSION['kBestellung'] = $order->kBestellung;
    //evtl. Kupon  Verwendungen hochzählen
    KuponVerwendungen($order);
    // Kampagne
    if (isset($_SESSION['Kampagnenbesucher'])) {
        Campaign::setCampaignAction(KAMPAGNE_DEF_VERKAUF, $order->kBestellung, 1.0);
        Campaign::setCampaignAction(KAMPAGNE_DEF_VERKAUFSSUMME, $order->kBestellung, $order->fGesamtsumme);
    }

    executeHook(HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_ENDE, [
        'oBestellung'   => &$order,
        'bestellID'     => &$bestellid,
        'bestellstatus' => &$bestellstatus,
    ]);
}

/**
 * @param int  $customerID
 * @param int  $orderID
 * @param bool $payAgain
 * @return bool
 */
function saveZahlungsInfo(int $customerID, int $orderID, bool $payAgain = false): bool
{
    /** @var array('Warenkorb' => Warenkorb) $_SESSION */

    if (!$customerID || !$orderID) {
        return false;
    }
    $_SESSION['ZahlungsInfo']               = new ZahlungsInfo();
    $_SESSION['ZahlungsInfo']->kBestellung  = $orderID;
    $_SESSION['ZahlungsInfo']->kKunde       = $customerID;
    $_SESSION['ZahlungsInfo']->cKartenTyp   = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cKartenTyp)
        ? Text::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cKartenTyp)
        : null;
    $_SESSION['ZahlungsInfo']->cGueltigkeit = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cGueltigkeit)
        ? Text::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cGueltigkeit)
        : null;
    $_SESSION['ZahlungsInfo']->cBankName    = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cBankName)
        ? Text::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cBankName)
        : null;
    $_SESSION['ZahlungsInfo']->cKartenNr    = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cKartenNr)
        ? Text::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cKartenNr)
        : null;
    $_SESSION['ZahlungsInfo']->cCVV         = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cCVV)
        ? Text::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cCVV)
        : null;
    $_SESSION['ZahlungsInfo']->cKontoNr     = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr)
        ? Text::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr)
        : null;
    $_SESSION['ZahlungsInfo']->cBLZ         = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cBLZ)
        ? Text::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cBLZ)
        : null;
    $_SESSION['ZahlungsInfo']->cIBAN        = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN)
        ? Text::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN)
        : null;
    $_SESSION['ZahlungsInfo']->cBIC         = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cBIC)
        ? Text::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cBIC)
        : null;
    $_SESSION['ZahlungsInfo']->cInhaber     = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber)
        ? Text::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber)
        : null;

    if (!$payAgain) {
        $cart                = Frontend::getCart();
        $cart->kZahlungsInfo = $_SESSION['ZahlungsInfo']->insertInDB();
        $cart->updateInDB();
    } else {
        $_SESSION['ZahlungsInfo']->insertInDB();
    }
    if (isset($_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr)
        || isset($_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN)
    ) {
        Shop::Container()->getDB()->delete('tkundenkontodaten', 'kKunde', $customerID);
        speicherKundenKontodaten($_SESSION['Zahlungsart']->ZahlungsInfo);
    }

    return true;
}

/**
 * @param object $paymentInfo
 */
function speicherKundenKontodaten($paymentInfo): void
{
    $cryptoService   = Shop::Container()->getCryptoService();
    $data            = new stdClass();
    $data->kKunde    = Frontend::getCart()->kKunde;
    $data->cBLZ      = $cryptoService->encryptXTEA($paymentInfo->cBLZ ?? '');
    $data->nKonto    = $cryptoService->encryptXTEA($paymentInfo->cKontoNr ?? '');
    $data->cInhaber  = $cryptoService->encryptXTEA($paymentInfo->cInhaber ?? '');
    $data->cBankName = $cryptoService->encryptXTEA($paymentInfo->cBankName ?? '');
    $data->cIBAN     = $cryptoService->encryptXTEA($paymentInfo->cIBAN ?? '');
    $data->cBIC      = $cryptoService->encryptXTEA($paymentInfo->cBIC ?? '');

    Shop::Container()->getDB()->insert('tkundenkontodaten', $data);
}

/**
 *
 */
function unhtmlSession(): void
{
    $customer           = new Customer();
    $sessionCustomer    = Frontend::getCustomer();
    $customerAttributes = Frontend::get('customerAttributes');
    if ($sessionCustomer->kKunde > 0) {
        $customer->kKunde = $sessionCustomer->kKunde;
        $customer->getCustomerAttributes()->load($customer->getID());
    } elseif ($customerAttributes !== null) {
        $customer->getCustomerAttributes()->assign($customerAttributes);
    }
    $customer->kKundengruppe = Frontend::getCustomerGroup()->getID();
    if ($sessionCustomer->kKundengruppe > 0) {
        $customer->kKundengruppe = $sessionCustomer->kKundengruppe;
    }
    $customer->kSprache = Shop::getLanguageID();
    if ($sessionCustomer->kSprache > 0) {
        $customer->kSprache = $sessionCustomer->kSprache;
    }
    if ($sessionCustomer->cKundenNr) {
        $customer->cKundenNr = $sessionCustomer->cKundenNr;
    }
    if ($sessionCustomer->cPasswort) {
        $customer->cPasswort = $sessionCustomer->cPasswort;
    }
    if ($sessionCustomer->fGuthaben) {
        $customer->fGuthaben = $sessionCustomer->fGuthaben;
    }
    if ($sessionCustomer->fRabatt) {
        $customer->fRabatt = $sessionCustomer->fRabatt;
    }
    if ($sessionCustomer->dErstellt) {
        $customer->dErstellt = $sessionCustomer->dErstellt;
    }
    if ($sessionCustomer->cAktiv) {
        $customer->cAktiv = $sessionCustomer->cAktiv;
    }
    if ($sessionCustomer->cAbgeholt) {
        $customer->cAbgeholt = $sessionCustomer->cAbgeholt;
    }
    if (isset($sessionCustomer->nRegistriert)) {
        $customer->nRegistriert = $sessionCustomer->nRegistriert;
    }
    $customer->cAnrede       = Text::unhtmlentities($sessionCustomer->cAnrede);
    $customer->cVorname      = Text::unhtmlentities($sessionCustomer->cVorname);
    $customer->cNachname     = Text::unhtmlentities($sessionCustomer->cNachname);
    $customer->cStrasse      = Text::unhtmlentities($sessionCustomer->cStrasse);
    $customer->cHausnummer   = Text::unhtmlentities($sessionCustomer->cHausnummer);
    $customer->cPLZ          = Text::unhtmlentities($sessionCustomer->cPLZ);
    $customer->cOrt          = Text::unhtmlentities($sessionCustomer->cOrt);
    $customer->cLand         = Text::unhtmlentities($sessionCustomer->cLand);
    $customer->cMail         = Text::unhtmlentities($sessionCustomer->cMail);
    $customer->cTel          = Text::unhtmlentities($sessionCustomer->cTel);
    $customer->cFax          = Text::unhtmlentities($sessionCustomer->cFax);
    $customer->cFirma        = Text::unhtmlentities($sessionCustomer->cFirma);
    $customer->cZusatz       = Text::unhtmlentities($sessionCustomer->cZusatz);
    $customer->cTitel        = Text::unhtmlentities($sessionCustomer->cTitel);
    $customer->cAdressZusatz = Text::unhtmlentities($sessionCustomer->cAdressZusatz);
    $customer->cMobil        = Text::unhtmlentities($sessionCustomer->cMobil);
    $customer->cWWW          = Text::unhtmlentities($sessionCustomer->cWWW);
    $customer->cUSTID        = Text::unhtmlentities($sessionCustomer->cUSTID);
    $customer->dGeburtstag   = Text::unhtmlentities($sessionCustomer->dGeburtstag);
    $customer->cBundesland   = Text::unhtmlentities($sessionCustomer->cBundesland);

    $_SESSION['Kunde'] = $customer;

    $shippingAddress = new Lieferadresse();
    $deliveryAddress = Frontend::getDeliveryAddress();
    if (($cid = $deliveryAddress->kKunde) > 0) {
        $shippingAddress->kKunde = $cid;
    }
    if (($did = $deliveryAddress->kLieferadresse) > 0) {
        $shippingAddress->kLieferadresse = $did;
    }
    $shippingAddress->cVorname      = Text::unhtmlentities($deliveryAddress->cVorname);
    $shippingAddress->cNachname     = Text::unhtmlentities($deliveryAddress->cNachname);
    $shippingAddress->cFirma        = Text::unhtmlentities($deliveryAddress->cFirma);
    $shippingAddress->cZusatz       = Text::unhtmlentities($deliveryAddress->cZusatz);
    $shippingAddress->cStrasse      = Text::unhtmlentities($deliveryAddress->cStrasse);
    $shippingAddress->cHausnummer   = Text::unhtmlentities($deliveryAddress->cHausnummer);
    $shippingAddress->cPLZ          = Text::unhtmlentities($deliveryAddress->cPLZ);
    $shippingAddress->cOrt          = Text::unhtmlentities($deliveryAddress->cOrt);
    $shippingAddress->cLand         = Text::unhtmlentities($deliveryAddress->cLand);
    $shippingAddress->cAnrede       = Text::unhtmlentities($deliveryAddress->cAnrede);
    $shippingAddress->cMail         = Text::unhtmlentities($deliveryAddress->cMail);
    $shippingAddress->cBundesland   = Text::unhtmlentities($deliveryAddress->cBundesland);
    $shippingAddress->cTel          = Text::unhtmlentities($deliveryAddress->cTel);
    $shippingAddress->cFax          = Text::unhtmlentities($deliveryAddress->cFax);
    $shippingAddress->cTitel        = Text::unhtmlentities($deliveryAddress->cTitel);
    $shippingAddress->cAdressZusatz = Text::unhtmlentities($deliveryAddress->cAdressZusatz);
    $shippingAddress->cMobil        = Text::unhtmlentities($deliveryAddress->cMobil);

    $shippingAddress->angezeigtesLand = LanguageHelper::getCountryCodeByCountryName($shippingAddress->cLand);

    $deliveryAddress = $shippingAddress;
}

/**
 * @param int       $productID
 * @param int|float $amount
 */
function aktualisiereBestseller(int $productID, $amount): void
{
    if (!$productID || !$amount) {
        return;
    }
    $data = Shop::Container()->getDB()->select('tbestseller', 'kArtikel', $productID);
    if (isset($data->kArtikel) && $data->kArtikel > 0) {
        Shop::Container()->getDB()->queryPrepared(
            'UPDATE tbestseller SET fAnzahl = fAnzahl + :mnt WHERE kArtikel = :aid',
            ['mnt' => $amount, 'aid' => $productID],
            ReturnType::DEFAULT
        );
    } else {
        $bestseller           = new stdClass();
        $bestseller->kArtikel = $productID;
        $bestseller->fAnzahl  = $amount;
        Shop::Container()->getDB()->insert('tbestseller', $bestseller);
    }
    if (Product::isVariCombiChild($productID)) {
        aktualisiereBestseller(Product::getParent($productID), $amount);
    }
}

/**
 * @param int $productID
 * @param int $targetID
 */
function aktualisiereXselling(int $productID, int $targetID): void
{
    if (!$productID || !$targetID) {
        return;
    }
    $obj = Shop::Container()->getDB()->select('txsellkauf', 'kArtikel', $productID, 'kXSellArtikel', $targetID);
    if (isset($obj->nAnzahl) && $obj->nAnzahl > 0) {
        Shop::Container()->getDB()->queryPrepared(
            'UPDATE txsellkauf
              SET nAnzahl = nAnzahl + 1
              WHERE kArtikel = :pid
                AND kXSellArtikel = :xs',
            [
                'pid' => $productID,
                'xs'  => $targetID
            ],
            ReturnType::DEFAULT
        );
    } else {
        $xs                = new stdClass();
        $xs->kArtikel      = $productID;
        $xs->kXSellArtikel = $targetID;
        $xs->nAnzahl       = 1;
        Shop::Container()->getDB()->insert('txsellkauf', $xs);
    }
}

/**
 * @param Artikel   $product
 * @param int|float $amount
 * @param array     $attributeValues
 * @param int       $productFilter
 * @return int|float - neuer Lagerbestand
 */
function aktualisiereLagerbestand(Artikel $product, $amount, $attributeValues, int $productFilter = 1)
{
    $inventory = (float)$product->fLagerbestand;
    $db        = Shop::Container()->getDB();
    if ($amount <= 0 || $product->cLagerBeachten !== 'Y') {
        return $inventory;
    }
    if ($product->cLagerVariation === 'Y'
        && is_array($attributeValues)
        && count($attributeValues) > 0
    ) {
        foreach ($attributeValues as $value) {
            $EigenschaftWert = new EigenschaftWert($value->kEigenschaftWert);
            if ($EigenschaftWert->fPackeinheit == 0) {
                $EigenschaftWert->fPackeinheit = 1;
            }
            $db->queryPrepared(
                'UPDATE teigenschaftwert
                    SET fLagerbestand = fLagerbestand - :inv
                    WHERE kEigenschaftWert = :aid',
                [
                    'aid' => (int)$value->kEigenschaftWert,
                    'inv' => $amount * $EigenschaftWert->fPackeinheit
                ],
                ReturnType::DEFAULT
            );
        }
        updateStock($product->kArtikel, $amount, $product->fPackeinheit);
    } elseif ($product->fPackeinheit > 0) {
        if ($product->kStueckliste > 0) {
            $inventory = aktualisiereStuecklistenLagerbestand($product, $amount);
        } else {
            updateStock($product->kArtikel, $amount, $product->fPackeinheit);
            $tmpProduct = $db->select(
                'tartikel',
                'kArtikel',
                (int)$product->kArtikel,
                null,
                null,
                null,
                null,
                false,
                'fLagerbestand'
            );
            if ($tmpProduct !== null) {
                $inventory = (float)$tmpProduct->fLagerbestand;
            }
            // Stücklisten Komponente
            if (Product::isStuecklisteKomponente($product->kArtikel)) {
                aktualisiereKomponenteLagerbestand(
                    $product->kArtikel,
                    $inventory,
                    $product->cLagerKleinerNull === 'Y'
                );
            }
        }
        // Aktualisiere Merkmale in tartikelmerkmal vom Vaterartikel
        if ($product->kVaterArtikel > 0) {
            Artikel::beachteVarikombiMerkmalLagerbestand($product->kVaterArtikel, $productFilter);
            updateStock($product->kVaterArtikel, $amount, $product->fPackeinheit);
        }
    }

    return $inventory;
}

/**
 * @param int $productID
 * @param float|int $amount
 * @param float|int $packeinheit
 */
function updateStock(int $productID, $amount, $packeinheit)
{
    Shop::Container()->getDB()->queryPrepared(
        'UPDATE tartikel
            SET fLagerbestand = GREATEST(fLagerbestand - :amountSubstract, 0)
            WHERE kArtikel = :productID',
        [
            'amountSubstract' => $amount * $packeinheit,
            'productID'       => $productID
        ],
        ReturnType::DEFAULT
    );
}

/**
 * @param Artikel   $bomProduct
 * @param int|float $amount
 * @return int|float - neuer Lagerbestand
 */
function aktualisiereStuecklistenLagerbestand($bomProduct, $amount)
{
    $amount        = (float)$amount;
    $bomID         = (int)$bomProduct->kStueckliste;
    $oldStockLevel = (float)$bomProduct->fLagerbestand;
    $newStockLevel = $oldStockLevel;
    $negStockLevel = $oldStockLevel;
    if ($amount <= 0) {
        return $newStockLevel;
    }
    // Gibt es lagerrelevante Komponenten in der Stückliste?
    $components = Shop::Container()->getDB()->queryPrepared(
        "SELECT tstueckliste.kArtikel, tstueckliste.fAnzahl
            FROM tstueckliste
            JOIN tartikel
              ON tartikel.kArtikel = tstueckliste.kArtikel
            WHERE tstueckliste.kStueckliste = :slid
                AND tartikel.cLagerBeachten = 'Y'",
        ['slid' => $bomID],
        ReturnType::ARRAY_OF_OBJECTS
    );

    if (is_array($components) && count($components) > 0) {
        // wenn ja, dann wird für diese auch der Bestand aktualisiert
        $options                             = Artikel::getDefaultOptions();
        $options->nKeineSichtbarkeitBeachten = 1;
        foreach ($components as $component) {
            $tmpArtikel = new Artikel();
            $tmpArtikel->fuelleArtikel($component->kArtikel, $options);
            $compStockLevel = floor(
                aktualisiereLagerbestand(
                    $tmpArtikel,
                    $amount * $component->fAnzahl,
                    null
                ) / $component->fAnzahl
            );

            if ($compStockLevel < $newStockLevel && $tmpArtikel->cLagerKleinerNull !== 'Y') {
                // Neuer Bestand ist der Kleinste Komponententbestand aller Artikel ohne Überverkauf
                $newStockLevel = $compStockLevel;
            } elseif ($compStockLevel < $negStockLevel) {
                // Für Komponenten mit Überverkauf wird der kleinste Bestand ermittelt.
                $negStockLevel = $compStockLevel;
            }
        }
    }

    // Ist der alte gleich dem neuen Bestand?
    if ($oldStockLevel === $newStockLevel) {
        // Es sind keine lagerrelevanten Komponenten vorhanden, die den Bestand der Stückliste herabsetzen.
        if ($negStockLevel === $newStockLevel) {
            // Es gibt auch keine Komponenten mit Überverkäufen, die den Bestand verringern, deshalb wird
            // der Bestand des Stücklistenartikels anhand des Verkaufs verringert
            $newStockLevel -= $amount * $bomProduct->fPackeinheit;
        } else {
            // Da keine lagerrelevanten Komponenten vorhanden sind, wird der kleinste Bestand der
            // Komponentent mit Überverkauf verwendet.
            $newStockLevel = $negStockLevel;
        }

        Shop::Container()->getDB()->update(
            'tartikel',
            'kArtikel',
            (int)$bomProduct->kArtikel,
            (object)['fLagerbestand' => $newStockLevel]
        );
    }
    // Kein Lagerbestands-Update für die Stückliste notwendig! Dies erfolgte bereits über die Komponentenabfrage und
    // die dortige Lagerbestandsaktualisierung!

    return $newStockLevel;
}

/**
 * @param int       $productID
 * @param int|float $stockLevel
 * @param bool      $allowNegativeStock
 */
function aktualisiereKomponenteLagerbestand(int $productID, float $stockLevel, bool $allowNegativeStock): void
{
    $db   = Shop::Container()->getDB();
    $boms = $db->queryPrepared(
        "SELECT tstueckliste.kStueckliste, tstueckliste.fAnzahl,
                tartikel.kArtikel, tartikel.fLagerbestand, tartikel.cLagerKleinerNull
            FROM tstueckliste
            JOIN tartikel
                ON tartikel.kStueckliste = tstueckliste.kStueckliste
            WHERE tstueckliste.kArtikel = :cid
                AND tartikel.cLagerBeachten = 'Y'",
        ['cid' => $productID],
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($boms as $bom) {
        // Ist der aktuelle Bestand der Stückliste größer als dies mit dem Bestand der Komponente möglich wäre?
        $max = floor($stockLevel / $bom->fAnzahl);
        if ($max < (float)$bom->fLagerbestand && (!$allowNegativeStock || $bom->cLagerKleinerNull === 'Y')) {
            // wenn ja, dann den Bestand der Stückliste entsprechend verringern, aber nur wenn die Komponente nicht
            // überberkaufbar ist oder die gesamte Stückliste Überverkäufe zulässt
            $db->update(
                'tartikel',
                'kArtikel',
                (int)$bom->kArtikel,
                (object)['fLagerbestand' => $max]
            );
        }
    }
}

/**
 * @param int       $productID
 * @param int|float $amount
 * @param null|int  $bomID
 * @deprecated since 4.06 - use aktualisiereStuecklistenLagerbestand instead
 */
function AktualisiereAndereStuecklisten(int $productID, $amount, $bomID = null): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if ($productID > 0) {
        $tmpArtikel = new Artikel();
        $tmpArtikel->fuelleArtikel($productID, Artikel::getDefaultOptions());
        aktualisiereKomponenteLagerbestand($productID, $tmpArtikel->fLagerbestand, $tmpArtikel->cLagerKleinerNull);
    }
}

/**
 * @param int       $bomID
 * @param float     $fPackeinheitSt
 * @param float     $stockLevel
 * @param int|float $amount
 * @deprecated since 4.06 - dont use anymore
 */
function AktualisiereStueckliste(int $bomID, $fPackeinheitSt, float $stockLevel, $amount): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Shop::Container()->getDB()->update(
        'tartikel',
        'kStueckliste',
        $bomID,
        (object)['fLagerbestand' => $stockLevel]
    );
}

/**
 * @param Artikel        $product
 * @param null|int|float $amount
 * @param bool           $isBom
 * @deprecated since 4.06 - use aktualisiereStuecklistenLagerbestand instead
 */
function AktualisiereLagerStuecklisten($product, $amount = null, $isBom = false): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (isset($product->kArtikel) && $product->kArtikel > 0) {
        if ($isBom) {
            aktualisiereStuecklistenLagerbestand($product, $amount);
        } else {
            aktualisiereKomponenteLagerbestand(
                $product->kArtikel,
                $product->fLagerbestand,
                $product->cLagerKleinerNull
            );
        }
    }
}

/**
 * @param $order
 */
function KuponVerwendungen($order): void
{
    $db          = Shop::Container()->getDB();
    $cart        = Frontend::getCart();
    $couponID    = 0;
    $couponType  = '';
    $couponGross = 0;
    if (isset($_SESSION['VersandKupon']->kKupon) && $_SESSION['VersandKupon']->kKupon > 0) {
        $couponID    = (int)$_SESSION['VersandKupon']->kKupon;
        $couponType  = Kupon::TYPE_SHIPPING;
        $couponGross = $_SESSION['Versandart']->fPreis;
    }
    if (isset($_SESSION['NeukundenKupon']->kKupon) && $_SESSION['NeukundenKupon']->kKupon > 0) {
        $couponID   = (int)$_SESSION['NeukundenKupon']->kKupon;
        $couponType = Kupon::TYPE_NEWCUSTOMER;
    }
    if (isset($_SESSION['Kupon']->kKupon) && $_SESSION['Kupon']->kKupon > 0) {
        $couponID   = (int)$_SESSION['Kupon']->kKupon;
        $couponType = Kupon::TYPE_STANDARD;
    }
    foreach ($cart->PositionenArr as $item) {
        $item->nPosTyp = (int)$item->nPosTyp;
        if (!isset($_SESSION['VersandKupon'])
            && ($item->nPosTyp === C_WARENKORBPOS_TYP_KUPON || $item->nPosTyp === C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
        ) {
            $couponGross = Tax::getGross(
                $item->fPreisEinzelNetto,
                Tax::getSalesTax($item->kSteuerklasse)
            ) * (-1);
        }
    }
    if ($couponID <= 0) {
        return;
    }
    $db->queryPrepared(
        'UPDATE tkupon
          SET nVerwendungenBisher = nVerwendungenBisher + 1
          WHERE kKupon = :couponID',
        ['couponID' => $couponID],
        ReturnType::DEFAULT
    );

    $db->queryPrepared(
        'INSERT INTO `tkuponkunde` (kKupon, cMail, dErstellt, nVerwendungen)
            VALUES (:couponID, :email, NOW(), :used)
            ON DUPLICATE KEY UPDATE
              nVerwendungen = nVerwendungen + 1',
        [
            'couponID' => $couponID,
            'email'    => Kupon::hash(Frontend::getCustomer()->cMail),
            'used'     => 1
        ],
        ReturnType::DEFAULT
    );

    $db->insert('tkuponflag', (object)[
        'cKuponTyp'  => $couponType,
        'cEmailHash' => Kupon::hash(Frontend::getCustomer()->cMail),
        'dErstellt'  => 'NOW()'
    ]);

    $couponOrder                     = new KuponBestellung();
    $couponOrder->kKupon             = $couponID;
    $couponOrder->kBestellung        = $order->kBestellung;
    $couponOrder->kKunde             = $cart->kKunde;
    $couponOrder->cBestellNr         = $order->cBestellNr;
    $couponOrder->fGesamtsummeBrutto = $order->fGesamtsumme;
    $couponOrder->fKuponwertBrutto   = $couponGross;
    $couponOrder->cKuponTyp          = $couponType;
    $couponOrder->dErstellt          = 'NOW()';

    $couponOrder->save();
}

/**
 * @return string
 */
function baueBestellnummer(): string
{
    $conf      = Shop::getSettings([CONF_KAUFABWICKLUNG]);
    $number    = new Nummern(JTL_GENNUMBER_ORDERNUMBER);
    $orderNo   = 1;
    $increment = isset($conf['kaufabwicklung']['bestellabschluss_bestellnummer_anfangsnummer'])
        ? (int)$conf['kaufabwicklung']['bestellabschluss_bestellnummer_anfangsnummer']
        : 1;
    if ($number) {
        $orderNo = $number->getNummer() + $increment;
        $number->setNummer($number->getNummer() + 1);
        $number->update();
    }

    /*
    *   %Y = -aktuelles Jahr
    *   %m = -aktueller Monat
    *   %d = -aktueller Tag
    *   %W = -aktuelle KW
    */
    $prefix = str_replace(
        ['%Y', '%m', '%d', '%W'],
        [date('Y'), date('m'), date('d'), date('W')],
        $conf['kaufabwicklung']['bestellabschluss_bestellnummer_praefix']
    );
    $suffix = str_replace(
        ['%Y', '%m', '%d', '%W'],
        [date('Y'), date('m'), date('d'), date('W')],
        $conf['kaufabwicklung']['bestellabschluss_bestellnummer_suffix']
    );
    executeHook(HOOK_BESTELLABSCHLUSS_INC_BAUEBESTELLNUMMER, [
        'orderNo' => &$orderNo,
        'prefix'  => &$prefix,
        'suffix'  => &$suffix
    ]);

    return $prefix . $orderNo . $suffix;
}

/**
 * @param Bestellung $order
 */
function speicherUploads($order): void
{
    if (!empty($order->kBestellung) && Upload::checkLicense()) {
        Upload::speicherUploadDateien(Frontend::getCart(), $order->kBestellung);
    }
}

/**
 * @param Bestellung $order
 */
function setzeSmartyWeiterleitung(Bestellung $order): void
{
    $moduleID = $_SESSION['Zahlungsart']->cModulId;
    speicherUploads($order);
    $logger = Shop::Container()->getLogService();
    if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
        $logger->withName('cModulId')->debug(
            'setzeSmartyWeiterleitung wurde mit folgender Zahlungsart ausgefuehrt: ' .
            print_r($_SESSION['Zahlungsart'], true),
            [$moduleID]
        );
    }
    $pluginID = Helper::getIDByModuleID($moduleID);
    if ($pluginID > 0) {
        $loader = Helper::getLoaderByPluginID($pluginID);
        $plugin = $loader->init($pluginID);
        global $oPlugin;
        $oPlugin = $plugin;
        if ($plugin !== null) {
            $pluginPaymentMethod = $plugin->getPaymentMethods()->getMethodByID($moduleID);
            if ($pluginPaymentMethod === null) {
                return;
            }
            $className = $pluginPaymentMethod->getClassName();
            /** @var PaymentMethod $paymentMethod */
            $paymentMethod           = new $className($moduleID);
            $paymentMethod->cModulId = $moduleID;
            $paymentMethod->preparePaymentProcess($order);
            Shop::Smarty()->assign('oPlugin', $plugin)
                ->assign('plugin', $plugin);
        }
    } elseif ($moduleID === 'za_kreditkarte_jtl' || $moduleID === 'za_lastschrift_jtl') {
        Shop::Smarty()->assign('abschlussseite', 1);
    }

    executeHook(HOOK_BESTELLABSCHLUSS_INC_SMARTYWEITERLEITUNG);
}

/**
 * @return Bestellung
 */
function fakeBestellung()
{
    /** @var array('Warenkorb' => Warenkorb) $_SESSION */
    if (isset($_POST['kommentar'])) {
        $_SESSION['kommentar'] = mb_substr(
            strip_tags(Shop::Container()->getDB()->escape($_POST['kommentar'])),
            0,
            1000
        );
    }
    $cart                    = Frontend::getCart();
    $customer                = Frontend::getCustomer();
    $order                   = new Bestellung();
    $order->kKunde           = $cart->kKunde;
    $order->kWarenkorb       = $cart->kWarenkorb;
    $order->kLieferadresse   = $cart->kLieferadresse;
    $order->kZahlungsart     = $_SESSION['Zahlungsart']->kZahlungsart;
    $order->kVersandart      = $_SESSION['Versandart']->kVersandart;
    $order->kSprache         = Shop::getLanguageID();
    $order->kWaehrung        = Frontend::getCurrency()->getID();
    $order->fGesamtsumme     = Frontend::getCart()->gibGesamtsummeWaren(true);
    $order->fWarensumme      = $order->fGesamtsumme;
    $order->cVersandartName  = $_SESSION['Versandart']->angezeigterName[$_SESSION['cISOSprache']];
    $order->cZahlungsartName = $_SESSION['Zahlungsart']->angezeigterName[$_SESSION['cISOSprache']];
    $order->cSession         = session_id();
    $order->cKommentar       = $_SESSION['kommentar'];
    $order->cAbgeholt        = 'N';
    $order->cStatus          = BESTELLUNG_STATUS_OFFEN;
    $order->dErstellt        = 'NOW()';
    $order->Zahlungsart      = $_SESSION['Zahlungsart'];
    $order->Positionen       = [];
    $order->Waehrung         = ($_SESSION['Waehrung'] instanceof Currency)
        ? $_SESSION['Waehrung']
        : new Currency($order->kWaehrung);
    $order->kWaehrung        = Frontend::getCurrency()->getID();
    $order->fWaehrungsFaktor = Frontend::getCurrency()->getConversionFactor();

    $order->oRechnungsadresse              = $order->oRechnungsadresse ?? new Rechnungsadresse();
    $order->oRechnungsadresse->cVorname    = $customer->cVorname;
    $order->oRechnungsadresse->cNachname   = $customer->cNachname;
    $order->oRechnungsadresse->cFirma      = $customer->cFirma;
    $order->oRechnungsadresse->kKunde      = $customer->kKunde;
    $order->oRechnungsadresse->cAnrede     = $customer->cAnrede;
    $order->oRechnungsadresse->cTitel      = $customer->cTitel;
    $order->oRechnungsadresse->cStrasse    = $customer->cStrasse;
    $order->oRechnungsadresse->cHausnummer = $customer->cHausnummer;
    $order->oRechnungsadresse->cPLZ        = $customer->cPLZ;
    $order->oRechnungsadresse->cOrt        = $customer->cOrt;
    $order->oRechnungsadresse->cLand       = $customer->cLand;
    $order->oRechnungsadresse->cTel        = $customer->cTel;
    $order->oRechnungsadresse->cMobil      = $customer->cMobil;
    $order->oRechnungsadresse->cFax        = $customer->cFax;
    $order->oRechnungsadresse->cUSTID      = $customer->cUSTID;
    $order->oRechnungsadresse->cWWW        = $customer->cWWW;
    $order->oRechnungsadresse->cMail       = $customer->cMail;

    if (mb_strlen(Frontend::getDeliveryAddress()->cVorname) > 0) {
        $order->Lieferadresse = gibLieferadresseAusSession();
    }
    $order->cBestellNr = date('dmYHis') . mb_substr($order->cSession, 0, 4);
    if (is_array($cart->PositionenArr) && count($cart->PositionenArr) > 0) {
        $order->Positionen = [];
        foreach ($cart->PositionenArr as $i => $item) {
            $order->Positionen[$i] = new CartItem();
            foreach (array_keys(get_object_vars($item)) as $member) {
                $order->Positionen[$i]->$member = $item->$member;
            }

            if (is_array($order->Positionen[$i]->cName)) {
                $order->Positionen[$i]->cName = $order->Positionen[$i]->cName[$_SESSION['cISOSprache']];
            }
            $order->Positionen[$i]->fMwSt = Tax::getSalesTax($item->kSteuerklasse);
            $order->Positionen[$i]->setzeGesamtpreisLocalized();
        }
    }
    if (isset($_SESSION['Bestellung']->GuthabenNutzen) && (int)$_SESSION['Bestellung']->GuthabenNutzen === 1) {
        $order->fGuthaben = -$_SESSION['Bestellung']->fGuthabenGenutzt;
    }
    $order->cIP = Request::getRealIP();

    return $order->fuelleBestellung(false, true);
}

/**
 * @return null|stdClass
 */
function gibLieferadresseAusSession()
{
    $deliveryAddress = Frontend::getDeliveryAddress();
    if (empty($deliveryAddress->cVorname)) {
        return null;
    }
    $shippingAddress              = new stdClass();
    $shippingAddress->cVorname    = $deliveryAddress->cVorname;
    $shippingAddress->cNachname   = $deliveryAddress->cNachname;
    $shippingAddress->cFirma      = $deliveryAddress->cFirma ?? null;
    $shippingAddress->kKunde      = $deliveryAddress->kKunde;
    $shippingAddress->cAnrede     = $deliveryAddress->cAnrede;
    $shippingAddress->cTitel      = $deliveryAddress->cTitel;
    $shippingAddress->cStrasse    = $deliveryAddress->cStrasse;
    $shippingAddress->cHausnummer = $deliveryAddress->cHausnummer;
    $shippingAddress->cPLZ        = $deliveryAddress->cPLZ;
    $shippingAddress->cOrt        = $deliveryAddress->cOrt;
    $shippingAddress->cLand       = $deliveryAddress->cLand;
    $shippingAddress->cTel        = $deliveryAddress->cTel;
    $shippingAddress->cMobil      = $deliveryAddress->cMobil ?? null;
    $shippingAddress->cFax        = $deliveryAddress->cFax ?? null;
    $shippingAddress->cUSTID      = $deliveryAddress->cUSTID ?? null;
    $shippingAddress->cWWW        = $deliveryAddress->cWWW ?? null;
    $shippingAddress->cMail       = $deliveryAddress->cMail;
    $shippingAddress->cAnrede     = $deliveryAddress->cAnrede;

    return $shippingAddress;
}

/**
 * Schaut nach ob eine Bestellmenge > Lagersbestand ist und falls dies erlaubt ist, gibt es einen Hinweis.
 *
 * @return array
 */
function pruefeVerfuegbarkeit(): array
{
    $res  = ['cArtikelName_arr' => []];
    $conf = Shop::getSettings([CONF_GLOBAL]);
    foreach (Frontend::getCart()->PositionenArr as $item) {
        if ($item->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
            && isset($item->Artikel->cLagerBeachten)
            && $item->Artikel->cLagerBeachten === 'Y'
            && $item->Artikel->cLagerKleinerNull === 'Y'
            && $conf['global']['global_lieferverzoegerung_anzeigen'] === 'Y'
            && $item->nAnzahl > $item->Artikel->fLagerbestand
        ) {
            $res['cArtikelName_arr'][] = $item->Artikel->cName;
        }
    }

    if (count($res['cArtikelName_arr']) > 0) {
        $res['cHinweis'] = str_replace('%s', '', Shop::Lang()->get('orderExpandInventory', 'basket'));
    }

    return $res;
}

/**
 * @param string $orderNo
 * @param bool   $sendMail
 * @return Bestellung
 */
function finalisiereBestellung($orderNo = '', bool $sendMail = true): Bestellung
{
    $obj                      = new stdClass();
    $obj->cVerfuegbarkeit_arr = pruefeVerfuegbarkeit();

    bestellungInDB(0, $orderNo);

    $order = new Bestellung($_SESSION['kBestellung']);
    $order->fuelleBestellung(false);

    $upd              = new stdClass();
    $upd->kKunde      = Frontend::getCart()->kKunde;
    $upd->kBestellung = (int)$order->kBestellung;
    Shop::Container()->getDB()->update('tbesucher', 'kKunde', $upd->kKunde, $upd);
    $obj->tkunde      = Frontend::getCustomer();
    $obj->tbestellung = $order;

    if (isset($order->oEstimatedDelivery->longestMin, $order->oEstimatedDelivery->longestMax)) {
        $obj->tbestellung->cEstimatedDeliveryEx = Date::dateAddWeekday(
            $order->dErstellt,
            $order->oEstimatedDelivery->longestMin
        )->format('d.m.Y') . ' - ' .
        Date::dateAddWeekday($order->dErstellt, $order->oEstimatedDelivery->longestMax)->format('d.m.Y');
    }
    $customer = new Customer();
    $customer->kopiereSession();
    if ($sendMail === true) {
        $mailer = Shop::Container()->get(Mailer::class);
        $mail   = new Mail();
        $mailer->send($mail->createFromTemplateID(MAILTEMPLATE_BESTELLBESTAETIGUNG, $obj));
    }
    $_SESSION['Kunde'] = $customer;
    $customerGroupID   = Frontend::getCustomerGroup()->getID();
    $checkbox          = new CheckBox();
    $checkbox->triggerSpecialFunction(
        CHECKBOX_ORT_BESTELLABSCHLUSS,
        $customerGroupID,
        true,
        $_POST,
        ['oBestellung' => $order, 'oKunde' => $customer]
    );
    $checkbox->checkLogging(CHECKBOX_ORT_BESTELLABSCHLUSS, $customerGroupID, $_POST, true);

    return $order;
}
