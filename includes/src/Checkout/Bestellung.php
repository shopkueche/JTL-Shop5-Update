<?php

namespace JTL\Checkout;

use JTL\Cart\CartHelper;
use JTL\Cart\CartItem;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Currency;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Extensions\Download\Download;
use JTL\Extensions\Upload\Upload;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Language\LanguageHelper;
use JTL\Plugin\Payment\LegacyMethod;
use JTL\Plugin\Payment\Method as PaymentMethod;
use JTL\Shop;
use stdClass;

/**
 * Class Bestellung
 * @package JTL
 */
class Bestellung
{
    /**
     * @var int
     */
    public $kBestellung;

    /**
     * @var int
     */
    public $kRechnungsadresse;

    /**
     * @var int
     */
    public $kWarenkorb;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var int
     */
    public $kLieferadresse;

    /**
     * @var int
     */
    public $kZahlungsart;

    /**
     * @var int
     */
    public $kVersandart;

    /**
     * @var int
     */
    public $kWaehrung;

    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var float
     */
    public $fGuthaben = 0.0;

    /**
     * @var int
     */
    public $fGesamtsumme;

    /**
     * @var string
     */
    public $cSession;

    /**
     * @var string
     */
    public $cBestellNr;

    /**
     * @var string
     */
    public $cVersandInfo;

    /**
     * @var string
     */
    public $cTracking;

    /**
     * @var string
     */
    public $cKommentar;

    /**
     * @var string
     */
    public $cVersandartName;

    /**
     * @var string
     */
    public $cZahlungsartName;

    /**
     * @var string - 'Y'/'N'
     */
    public $cAbgeholt;

    /**
     * @var string 'Y'/'N'
     */
    public $cStatus;

    /**
     * @var string - datetime [yyyy.mm.dd hh:ii:ss]
     */
    public $dVersandDatum;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var string
     */
    public $dBezahltDatum;

    /**
     * @var string
     */
    public $cEstimatedDelivery = '';

    /**
     * @var object {
     *      localized: string,
     *      longestMin: int,
     *      longestMax: int,
     * }
     */
    public $oEstimatedDelivery;

    /**
     * @var CartItem[]
     */
    public $Positionen;

    /**
     * @var PaymentMethod
     */
    public $Zahlungsart;

    /**
     * @var Lieferadresse
     */
    public $Lieferadresse;

    /**
     * @var Rechnungsadresse
     */
    public $oRechnungsadresse;

    /**
     * @var Versandart
     */
    public $oVersandart;

    /**
     * @var null|string
     */
    public $dBewertungErinnerung;

    /**
     * @var string
     */
    public $cLogistiker = '';

    /**
     * @var string
     */
    public $cTrackingURL = '';

    /**
     * @var string
     */
    public $cIP = '';

    /**
     * @var Customer
     */
    public $oKunde;

    /**
     * @var string
     */
    public $BestellstatusURL;

    /**
     * @var string
     */
    public $dVersanddatum_de;

    /**
     * @var string
     */
    public $dBezahldatum_de;

    /**
     * @var string
     */
    public $dErstelldatum_de;

    /**
     * @var string
     */
    public $dVersanddatum_en;

    /**
     * @var string
     */
    public $dBezahldatum_en;

    /**
     * @var string
     */
    public $dErstelldatum_en;

    /**
     * @var
     */
    public $cBestellwertLocalized;

    /**
     * @var
     */
    public $Waehrung;

    /**
     * @var
     */
    public $Steuerpositionen;

    /**
     * @var
     */
    public $Status;

    /**
     * @var array
     */
    public $oLieferschein_arr = [];

    /**
     * @var ZahlungsInfo
     */
    public $Zahlungsinfo;

    /**
     * @var int
     */
    public $GuthabenNutzen;

    /**
     * @var string
     */
    public $GutscheinLocalized;

    /**
     * @var float
     */
    public $fWarensumme;

    /**
     * @var float
     */
    public $fVersand = 0.0;

    /**
     * @var float
     */
    public $fWarensummeNetto = 0.0;

    /**
     * @var float
     */
    public $fVersandNetto = 0.0;

    /**
     * @var array
     */
    public $oUpload_arr;

    /**
     * @var array
     */
    public $oDownload_arr;

    /**
     * @var float
     */
    public $fGesamtsummeNetto;

    /**
     * @var float
     */
    public $fWarensummeKundenwaehrung;

    /**
     * @var float
     */
    public $fVersandKundenwaehrung;

    /**
     * @var float
     */
    public $fSteuern;

    /**
     * @var float
     */
    public $fGesamtsummeKundenwaehrung;

    /**
     * @var array
     */
    public $WarensummeLocalized = [];

    /**
     * @var float
     */
    public $fWaehrungsFaktor = 1.0;

    /**
     * @var string
     */
    public $cPUIZahlungsdaten;

    /**
     * @var object
     */
    public $oKampagne;

    /**
     * @var array
     */
    public $OrderAttributes;

    /**
     * Bestellung constructor.
     * @param int  $id
     * @param bool $init
     */
    public function __construct(int $id = 0, bool $init = false)
    {
        if ($id > 0) {
            $this->loadFromDB($id);
            if ($init) {
                $this->fuelleBestellung();
            }
        }
    }

    /**
     * @param int $id
     * @return $this
     */
    public function loadFromDB(int $id): self
    {
        $obj = Shop::Container()->getDB()->select('tbestellung', 'kBestellung', $id);
        if ($obj !== null && $obj->kBestellung > 0) {
            foreach (\get_object_vars($obj) as $k => $v) {
                $this->$k = $v;
            }
            $this->kSprache          = (int)$this->kSprache;
            $this->kWarenkorb        = (int)$this->kWarenkorb;
            $this->kBestellung       = (int)$this->kBestellung;
            $this->kWaehrung         = (int)$this->kWaehrung;
            $this->kKunde            = (int)$this->kKunde;
            $this->kRechnungsadresse = (int)$this->kRechnungsadresse;
            $this->kZahlungsart      = (int)$this->kZahlungsart;
            $this->kVersandart       = (int)$this->kVersandart;
        }

        if (isset($this->nLongestMinDelivery, $this->nLongestMaxDelivery)) {
            $this->setEstimatedDelivery((int)$this->nLongestMinDelivery, (int)$this->nLongestMaxDelivery);
            unset($this->nLongestMinDelivery, $this->nLongestMaxDelivery);
        } else {
            $this->setEstimatedDelivery();
        }

        return $this;
    }

    /**
     * @param bool $htmlCurrency
     * @param int  $external
     * @param bool $initProduct
     * @param bool $disableFactor - @see #8544, hack to avoid applying currency factor twice
     * @return $this
     */
    public function fuelleBestellung(
        bool $htmlCurrency = true,
        $external = 0,
        $initProduct = true,
        $disableFactor = false
    ): self {
        if (!($this->kWarenkorb > 0 || $external > 0)) {
            return $this;
        }
        $db               = Shop::Container()->getDB();
        $sum              = null;
        $date             = null;
        $this->Positionen = $db->selectAll(
            'twarenkorbpos',
            'kWarenkorb',
            (int)$this->kWarenkorb,
            '*',
            'kWarenkorbPos'
        );
        if ($this->kLieferadresse !== null && $this->kLieferadresse > 0) {
            $this->Lieferadresse = new Lieferadresse($this->kLieferadresse);
        }
        // Rechnungsadresse holen
        if ($this->kRechnungsadresse !== null && $this->kRechnungsadresse > 0) {
            $billingAddress = new Rechnungsadresse($this->kRechnungsadresse);
            if ($billingAddress->kRechnungsadresse > 0) {
                $this->oRechnungsadresse = $billingAddress;
            }
        }
        // Versandart holen
        if ($this->kVersandart !== null && $this->kVersandart > 0) {
            $shippingMethod = new Versandart($this->kVersandart);
            if ($shippingMethod->kVersandart !== null && $shippingMethod->kVersandart > 0) {
                $this->oVersandart = $shippingMethod;
            }
        }
        // Kunde holen
        if ($this->kKunde !== null && $this->kKunde > 0) {
            $customer = new Customer($this->kKunde);
            if ($customer->kKunde !== null && $customer->kKunde > 0) {
                $customer->cPasswort = null;
                $customer->fRabatt   = null;
                $customer->fGuthaben = null;
                $customer->cUSTID    = null;
                $this->oKunde        = $customer;
            }
        }

        $orderState             = $db->select(
            'tbestellstatus',
            'kBestellung',
            (int)$this->kBestellung
        );
        $this->BestellstatusURL = Shop::getURL() . '/status.php?uid=' . $orderState->cUID;
        $sum                    = $db->query(
            'SELECT SUM(((fPreis * fMwSt)/100 + fPreis) * nAnzahl) AS wert
                FROM twarenkorbpos
                WHERE kWarenkorb = ' . (int)$this->kWarenkorb,
            ReturnType::SINGLE_OBJECT
        );
        $date                   = $db->query(
            "SELECT date_format(dVersandDatum,'%d.%m.%Y') AS dVersanddatum_de,
                date_format(dBezahltDatum,'%d.%m.%Y') AS dBezahldatum_de,
                date_format(dErstellt,'%d.%m.%Y %H:%i:%s') AS dErstelldatum_de,
                date_format(dVersandDatum,'%D %M %Y') AS dVersanddatum_en,
                date_format(dBezahltDatum,'%D %M %Y') AS dBezahldatum_en,
                date_format(dErstellt,'%D %M %Y') AS dErstelldatum_en
                FROM tbestellung WHERE kBestellung = " . (int)$this->kBestellung,
            ReturnType::SINGLE_OBJECT
        );
        if ($date !== null && \is_object($date)) {
            $this->dVersanddatum_de = $date->dVersanddatum_de;
            $this->dBezahldatum_de  = $date->dBezahldatum_de;
            $this->dErstelldatum_de = $date->dErstelldatum_de;
            $this->dVersanddatum_en = $date->dVersanddatum_en;
            $this->dBezahldatum_en  = $date->dBezahldatum_en;
            $this->dErstelldatum_en = $date->dErstelldatum_en;
        }
        // Hole Netto- oder Bruttoeinstellung der Kundengruppe
        $nNettoPreis = 0;
        if ($this->kBestellung > 0) {
            $netOrderData = $db->query(
                'SELECT tkundengruppe.nNettoPreise
                    FROM tkundengruppe
                    JOIN tbestellung 
                        ON tbestellung.kBestellung = ' . (int)$this->kBestellung . '
                    JOIN tkunde 
                        ON tkunde.kKunde = tbestellung.kKunde
                    WHERE tkunde.kKundengruppe = tkundengruppe.kKundengruppe',
                ReturnType::SINGLE_OBJECT
            );
            if (isset($netOrderData->nNettoPreise) && $netOrderData->nNettoPreise > 0) {
                $nNettoPreis = 1;
            }
        }
        $this->cBestellwertLocalized = Preise::getLocalizedPriceString($sum->wert ?? 0, $htmlCurrency);
        $this->Status                = \lang_bestellstatus((int)$this->cStatus);
        if ($this->kWaehrung > 0) {
            $this->Waehrung = new Currency((int)$this->kWaehrung);
            if ($this->fWaehrungsFaktor !== null && $this->fWaehrungsFaktor != 1 && isset($this->Waehrung->fFaktor)) {
                $this->Waehrung->fFaktor = $this->fWaehrungsFaktor;
            }
            if ($disableFactor === true) {
                $this->Waehrung->fFaktor = 1;
            }
            $this->Steuerpositionen = Tax::getOldTaxItems(
                $this->Positionen,
                $nNettoPreis,
                $htmlCurrency,
                $this->Waehrung
            );
            if ($this->kZahlungsart > 0) {
                $this->Zahlungsart = $db->select(
                    'tzahlungsart',
                    'kZahlungsart',
                    (int)$this->kZahlungsart
                );
                if ($this->Zahlungsart !== null) {
                    $oZahlungsart = LegacyMethod::create($this->Zahlungsart->cModulId, 1);
                    if ($oZahlungsart !== null) {
                        $this->Zahlungsart->bPayAgain = $oZahlungsart->canPayAgain();
                    }
                }
            }
        }
        if ($this->kBestellung > 0) {
            $this->Zahlungsinfo = new ZahlungsInfo(0, $this->kBestellung);
        }
        if ((float)$this->fGuthaben) {
            $this->GuthabenNutzen = 1;
        }
        $this->GutscheinLocalized = Preise::getLocalizedPriceString($this->fGuthaben, $htmlCurrency);
        $summe                    = 0;
        $this->fWarensumme        = 0;
        $this->fVersand           = 0;
        $this->fWarensummeNetto   = 0;
        $this->fVersandNetto      = 0;
        $defaultOptions           = Artikel::getDefaultOptions();
        $languageID               = Shop::getLanguageID();
        if (!$languageID) {
            $language             = LanguageHelper::getDefaultLanguage();
            $languageID           = (int)$language->kSprache;
            $_SESSION['kSprache'] = $languageID;
        }
        foreach ($this->Positionen as $i => $item) {
            $item->kArtikel            = (int)$item->kArtikel;
            $item->nPosTyp             = (int)$item->nPosTyp;
            $item->kWarenkorbPos       = (int)$item->kWarenkorbPos;
            $item->kVersandklasse      = (int)$item->kVersandklasse;
            $item->kKonfigitem         = (int)$item->kKonfigitem;
            $item->kBestellpos         = (int)$item->kBestellpos;
            $item->nLongestMinDelivery = (int)$item->nLongestMinDelivery;
            $item->nLongestMaxDelivery = (int)$item->nLongestMaxDelivery;
            if ($item->nAnzahl == (int)$item->nAnzahl) {
                $item->nAnzahl = (int)$item->nAnzahl;
            }
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_VERSANDPOS
                || $item->nPosTyp === \C_WARENKORBPOS_TYP_VERSANDZUSCHLAG
                || $item->nPosTyp === \C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR
                || $item->nPosTyp === \C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG
                || $item->nPosTyp === \C_WARENKORBPOS_TYP_VERPACKUNG
            ) {
                $this->fVersandNetto += $item->fPreis;
                $this->fVersand      += $item->fPreis + ($item->fPreis * $item->fMwSt) / 100;
            } else {
                $this->fWarensummeNetto += $item->fPreis * $item->nAnzahl;
                $this->fWarensumme      += ($item->fPreis + ($item->fPreis * $item->fMwSt) / 100)
                    * $item->nAnzahl;
            }

            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
                if ($initProduct) {
                    $item->Artikel = (new Artikel())->fuelleArtikel($item->kArtikel, $defaultOptions);
                }
                if ($this->kBestellung > 0) {
                    $this->oDownload_arr = Download::getDownloads(['kBestellung' => $this->kBestellung], $languageID);
                    $this->oUpload_arr   = Upload::gibBestellungUploads($this->kBestellung);
                }
                if ($item->kWarenkorbPos > 0) {
                    $item->WarenkorbPosEigenschaftArr = $db->selectAll(
                        'twarenkorbposeigenschaft',
                        'kWarenkorbPos',
                        (int)$item->kWarenkorbPos
                    );
                    foreach ($item->WarenkorbPosEigenschaftArr as $attribute) {
                        if ($attribute->fAufpreis) {
                            $attribute->cAufpreisLocalized[0] = Preise::getLocalizedPriceString(
                                Tax::getGross(
                                    $attribute->fAufpreis,
                                    $item->fMwSt
                                ),
                                $this->Waehrung,
                                $htmlCurrency
                            );
                            $attribute->cAufpreisLocalized[1] = Preise::getLocalizedPriceString(
                                $attribute->fAufpreis,
                                $this->Waehrung,
                                $htmlCurrency
                            );
                        }
                    }
                }

                CartItem::setEstimatedDelivery(
                    $item,
                    $item->nLongestMinDelivery,
                    $item->nLongestMaxDelivery
                );
            }
            if (!isset($item->kSteuerklasse)) {
                $taxClass = $db->select('tsteuersatz', 'fSteuersatz', $item->fMwSt);
                if ($taxClass !== null) {
                    $item->kSteuerklasse = $taxClass->kSteuerklasse;
                }
            }
            $summe += $item->fPreis * $item->nAnzahl;
            if ($this->kWarenkorb > 0) {
                $item->cGesamtpreisLocalized[0] = Preise::getLocalizedPriceString(
                    Tax::getGross(
                        $item->fPreis * $item->nAnzahl,
                        $item->fMwSt
                    ),
                    $this->Waehrung,
                    $htmlCurrency
                );
                $item->cGesamtpreisLocalized[1] = Preise::getLocalizedPriceString(
                    $item->fPreis * $item->nAnzahl,
                    $this->Waehrung,
                    $htmlCurrency
                );
                $item->cEinzelpreisLocalized[0] = Preise::getLocalizedPriceString(
                    Tax::getGross($item->fPreis, $item->fMwSt),
                    $this->Waehrung,
                    $htmlCurrency
                );
                $item->cEinzelpreisLocalized[1] = Preise::getLocalizedPriceString(
                    $item->fPreis,
                    $this->Waehrung,
                    $htmlCurrency
                );

                if ((int)$item->kKonfigitem > 0 && \is_string($item->cUnique) && !empty($item->cUnique)) {
                    $net       = 0;
                    $gross     = 0;
                    $parentIdx = null;
                    foreach ($this->Positionen as $idx => $_item) {
                        if ($item->cUnique === $_item->cUnique) {
                            $net   += $_item->fPreis * $_item->nAnzahl;
                            $ust    = Tax::getSalesTax($_item->kSteuerklasse ?? 0);
                            $gross += Tax::getGross($_item->fPreis * $_item->nAnzahl, $ust);
                            if ((int)$_item->kKonfigitem === 0
                                && \is_string($_item->cUnique)
                                && !empty($_item->cUnique)
                            ) {
                                $parentIdx = $idx;
                            }
                        }
                    }
                    if ($parentIdx !== null) {
                        $parent = $this->Positionen[$parentIdx];
                        if (\is_object($parent)) {
                            $item->nAnzahlEinzel                    = $item->nAnzahl / $parent->nAnzahl;
                            $parent->cKonfigpreisLocalized[0]       = Preise::getLocalizedPriceString(
                                $gross,
                                $this->Waehrung
                            );
                            $parent->cKonfigpreisLocalized[1]       = Preise::getLocalizedPriceString(
                                $net,
                                $this->Waehrung
                            );
                            $parent->cKonfigeinzelpreisLocalized[0] = Preise::getLocalizedPriceString(
                                $gross / $parent->nAnzahl,
                                $this->Waehrung
                            );
                            $parent->cKonfigeinzelpreisLocalized[1] = Preise::getLocalizedPriceString(
                                $net / $parent->nAnzahl,
                                $this->Waehrung
                            );
                        }
                    }
                }
            }
            $item->kLieferschein_arr   = [];
            $item->nAusgeliefert       = 0;
            $item->nAusgeliefertGesamt = 0;
            $item->bAusgeliefert       = false;
            $item->nOffenGesamt        = $item->nAnzahl;
        }

        $this->WarensummeLocalized[0]     = Preise::getLocalizedPriceString(
            $this->fGesamtsumme,
            $this->Waehrung,
            $htmlCurrency
        );
        $this->WarensummeLocalized[1]     = Preise::getLocalizedPriceString(
            $summe + $this->fGuthaben,
            $this->Waehrung,
            $htmlCurrency
        );
        $this->fGesamtsummeNetto          = $summe + $this->fGuthaben;
        $this->fWarensummeKundenwaehrung  = ($this->fWarensumme + $this->fGuthaben) * $this->fWaehrungsFaktor;
        $this->fVersandKundenwaehrung     = $this->fVersand * $this->fWaehrungsFaktor;
        $this->fSteuern                   = $this->fGesamtsumme - $this->fGesamtsummeNetto;
        $this->fGesamtsummeKundenwaehrung = CartHelper::roundOptional(
            $this->fWarensummeKundenwaehrung + $this->fVersandKundenwaehrung
        );

        $sData                   = new stdClass();
        $sData->cPLZ             = $this->oRechnungsadresse->cPLZ ?? ($this->Lieferadresse->cPLZ ?? '');
        $this->oLieferschein_arr = [];
        if ((int)$this->kBestellung > 0) {
            $deliveryNotes = $db->selectAll(
                'tlieferschein',
                'kInetBestellung',
                (int)$this->kBestellung,
                'kLieferschein'
            );
            foreach ($deliveryNotes as $note) {
                $note                = new Lieferschein((int)$note->kLieferschein, $sData);
                $note->oPosition_arr = [];
                /** @var Lieferscheinpos $lineItem */
                foreach ($note->oLieferscheinPos_arr as $lineItem) {
                    foreach ($this->Positionen as &$orderItem) {
                        $orderItem->nPosTyp     = (int)$orderItem->nPosTyp;
                        $orderItem->kBestellpos = (int)$orderItem->kBestellpos;
                        if (\in_array(
                            $orderItem->nPosTyp,
                            [\C_WARENKORBPOS_TYP_ARTIKEL, \C_WARENKORBPOS_TYP_GRATISGESCHENK],
                            true
                        )
                            && $lineItem->getBestellPos() === $orderItem->kBestellpos
                        ) {
                            $orderItem->kLieferschein_arr[]  = $note->getLieferschein();
                            $orderItem->nAusgeliefert        = $lineItem->getAnzahl();
                            $orderItem->nAusgeliefertGesamt += $orderItem->nAusgeliefert;
                            $orderItem->nOffenGesamt        -= $orderItem->nAusgeliefert;
                            $note->oPosition_arr[]           = &$orderItem;
                            if (!isset($lineItem->oPosition) || !\is_object($lineItem->oPosition)) {
                                $lineItem->oPosition = &$orderItem;
                            }
                            if ((int)$orderItem->nOffenGesamt === 0) {
                                $orderItem->bAusgeliefert = true;
                            }
                        }
                    }
                    unset($orderItem);
                    // Charge, MDH & Seriennummern
                    if (isset($lineItem->oPosition) && \is_object($lineItem->oPosition)) {
                        /** @var Lieferscheinposinfo $_lieferscheinPosInfo */
                        foreach ($lineItem->oLieferscheinPosInfo_arr as $_lieferscheinPosInfo) {
                            $mhd    = $_lieferscheinPosInfo->getMHD();
                            $serial = $_lieferscheinPosInfo->getSeriennummer();
                            $charge = $_lieferscheinPosInfo->getChargeNr();
                            if (\mb_strlen($charge) > 0) {
                                $lineItem->oPosition->cChargeNr = $charge;
                            }
                            if ($mhd !== null && \mb_strlen($mhd) > 0) {
                                $lineItem->oPosition->dMHD    = $mhd;
                                $lineItem->oPosition->dMHD_de = \date_format(\date_create($mhd), 'd.m.Y');
                            }
                            if (\mb_strlen($serial) > 0) {
                                $lineItem->oPosition->cSeriennummer = $serial;
                            }
                        }
                    }
                }
                $this->oLieferschein_arr[] = $note;
            }
            // Wenn Konfig-Vater, alle Kinder ueberpruefen
            foreach ($this->oLieferschein_arr as $deliveryNote) {
                foreach ($deliveryNote->oPosition_arr as $deliveryItem) {
                    if ($deliveryItem->kKonfigitem == 0 && !empty($deliveryItem->cUnique)) {
                        $bAlleAusgeliefert = true;
                        foreach ($this->Positionen as $child) {
                            if ($child->cUnique === $deliveryItem->cUnique
                                && $child->kKonfigitem > 0
                                && !$child->bAusgeliefert
                            ) {
                                $bAlleAusgeliefert = false;
                            }
                        }
                        $deliveryItem->bAusgeliefert = $bAlleAusgeliefert;
                    }
                }
            }
        }
        // Fallback for Non-Beta
        if ((int)$this->cStatus === \BESTELLUNG_STATUS_VERSANDT) {
            foreach ($this->Positionen as $item) {
                $item->nAusgeliefertGesamt = $item->nAnzahl;
                $item->bAusgeliefert       = true;
                $item->nOffenGesamt        = 0;
            }
        }

        if (empty($this->oEstimatedDelivery->localized)) {
            $this->berechneEstimatedDelivery();
        }

        $this->OrderAttributes = [];
        if ((int)$this->kBestellung > 0) {
            $OrderAttributes = $db->selectAll(
                'tbestellattribut',
                'kbestellung',
                (int)$this->kBestellung
            );
            foreach ($OrderAttributes as $attribute) {
                $attr                   = new stdClass();
                $attr->kBestellattribut = (int)$attribute->kBestellattribut;
                $attr->kBestellung      = (int)$attribute->kBestellung;
                $attr->cName            = $attribute->cName;
                $attr->cValue           = $attribute->cValue;
                if ($attribute->cName === 'Finanzierungskosten') {
                    $attr->cValue = Preise::getLocalizedPriceString(
                        \str_replace(',', '.', $attribute->cValue),
                        $this->Waehrung,
                        $htmlCurrency
                    );
                }
                $this->OrderAttributes[] = $attr;
            }
        }

        $this->setKampagne();

        \executeHook(\HOOK_BESTELLUNG_CLASS_FUELLEBESTELLUNG, [
            'oBestellung' => $this
        ]);

        return $this;
    }

    /**
     * @return $this
     * @deprecated since 5.0.0
     */
    public function machGoogleAnalyticsReady(): self
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        foreach ($this->Positionen as $item) {
            $item->nPosTyp = (int)$item->nPosTyp;
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL && $item->kArtikel > 0) {
                $product            = new Artikel();
                $product->kArtikel  = $item->kArtikel;
                $expandedCategories = new KategorieListe();
                $category           = new Kategorie($product->gibKategorie());
                $expandedCategories->getOpenCategories($category);
                $item->Category = '';
                $elemCount      = \count($expandedCategories->elemente) - 1;
                for ($o = $elemCount; $o >= 0; $o--) {
                    $item->Category = $expandedCategories->elemente[$o]->cName;
                    if ($o > 0) {
                        $item->Category .= ' / ';
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        $obj                       = new stdClass();
        $obj->kWarenkorb           = $this->kWarenkorb;
        $obj->kKunde               = $this->kKunde;
        $obj->kLieferadresse       = $this->kLieferadresse;
        $obj->kRechnungsadresse    = $this->kRechnungsadresse;
        $obj->kZahlungsart         = $this->kZahlungsart;
        $obj->kVersandart          = $this->kVersandart;
        $obj->kSprache             = $this->kSprache;
        $obj->kWaehrung            = $this->kWaehrung;
        $obj->fGuthaben            = $this->fGuthaben;
        $obj->fGesamtsumme         = $this->fGesamtsumme;
        $obj->cSession             = $this->cSession;
        $obj->cVersandartName      = $this->cVersandartName;
        $obj->cZahlungsartName     = $this->cZahlungsartName;
        $obj->cBestellNr           = $this->cBestellNr;
        $obj->cVersandInfo         = $this->cVersandInfo;
        $obj->nLongestMinDelivery  = $this->oEstimatedDelivery->longestMin;
        $obj->nLongestMaxDelivery  = $this->oEstimatedDelivery->longestMax;
        $obj->dVersandDatum        = empty($this->dVersandDatum) ? '_DBNULL_' : $this->dVersandDatum;
        $obj->dBezahltDatum        = empty($this->dBezahltDatum) ? '_DBNULL_' : $this->dBezahltDatum;
        $obj->dBewertungErinnerung = empty($this->dBewertungErinnerung) ? '_DBNULL_' : $this->dBewertungErinnerung;
        $obj->cTracking            = $this->cTracking;
        $obj->cKommentar           = $this->cKommentar;
        $obj->cLogistiker          = $this->cLogistiker;
        $obj->cTrackingURL         = $this->cTrackingURL;
        $obj->cIP                  = $this->cIP;
        $obj->cAbgeholt            = $this->cAbgeholt;
        $obj->cStatus              = $this->cStatus;
        $obj->dErstellt            = $this->dErstellt;
        $obj->fWaehrungsFaktor     = $this->fWaehrungsFaktor;
        $obj->cPUIZahlungsdaten    = $this->cPUIZahlungsdaten;

        $this->kBestellung = Shop::Container()->getDB()->insert('tbestellung', $obj);

        return $this->kBestellung;
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj                       = new stdClass();
        $obj->kBestellung          = $this->kBestellung;
        $obj->kWarenkorb           = $this->kWarenkorb;
        $obj->kKunde               = $this->kKunde;
        $obj->kLieferadresse       = $this->kLieferadresse;
        $obj->kRechnungsadresse    = $this->kRechnungsadresse;
        $obj->kZahlungsart         = $this->kZahlungsart;
        $obj->kVersandart          = $this->kVersandart;
        $obj->kSprache             = $this->kSprache;
        $obj->kWaehrung            = $this->kWaehrung;
        $obj->fGuthaben            = $this->fGuthaben;
        $obj->fGesamtsumme         = $this->fGesamtsumme;
        $obj->cSession             = $this->cSession;
        $obj->cVersandartName      = $this->cVersandartName;
        $obj->cZahlungsartName     = $this->cZahlungsartName;
        $obj->cBestellNr           = $this->cBestellNr;
        $obj->cVersandInfo         = $this->cVersandInfo;
        $obj->nLongestMinDelivery  = $this->oEstimatedDelivery->longestMin;
        $obj->nLongestMaxDelivery  = $this->oEstimatedDelivery->longestMax;
        $obj->dVersandDatum        = empty($this->dVersandDatum) ? '_DBNULL_' : $this->dVersandDatum;
        $obj->dBezahltDatum        = empty($this->dBezahltDatum) ? '_DBNULL_' : $this->dBezahltDatum;
        $obj->dBewertungErinnerung = empty($this->dBewertungErinnerung) ? '_DBNULL_' : $this->dBewertungErinnerung;
        $obj->cTracking            = $this->cTracking;
        $obj->cKommentar           = $this->cKommentar;
        $obj->cLogistiker          = $this->cLogistiker;
        $obj->cTrackingURL         = $this->cTrackingURL;
        $obj->cIP                  = $this->cIP;
        $obj->cAbgeholt            = $this->cAbgeholt;
        $obj->cStatus              = $this->cStatus;
        $obj->dErstellt            = $this->dErstellt;
        $obj->cPUIZahlungsdaten    = $this->cPUIZahlungsdaten;

        return Shop::Container()->getDB()->update('tbestellung', 'kBestellung', $obj->kBestellung, $obj);
    }

    /**
     * @param int  $orderID
     * @param bool $assoc
     * @param int  $posType
     * @return array
     */
    public static function getOrderPositions(
        int $orderID,
        bool $assoc = true,
        int $posType = \C_WARENKORBPOS_TYP_ARTIKEL
    ): array {
        $items = [];
        if ($orderID > 0) {
            $data = Shop::Container()->getDB()->query(
                'SELECT twarenkorbpos.kWarenkorbPos, twarenkorbpos.kArtikel
                      FROM tbestellung
                      JOIN twarenkorbpos
                        ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                          AND nPosTyp = ' . $posType . '
                      WHERE tbestellung.kBestellung = ' . $orderID,
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($data as $item) {
                if (isset($item->kWarenkorbPos) && $item->kWarenkorbPos > 0) {
                    if ($assoc) {
                        $items[$item->kArtikel] = new CartItem($item->kWarenkorbPos);
                    } else {
                        $items[] = new CartItem($item->kWarenkorbPos);
                    }
                }
            }
        }

        return $items;
    }

    /**
     * @param int $orderID
     * @return int|bool
     */
    public static function getOrderNumber(int $orderID)
    {
        $data = Shop::Container()->getDB()->select(
            'tbestellung',
            'kBestellung',
            $orderID,
            null,
            null,
            null,
            null,
            false,
            'cBestellNr'
        );

        return isset($data->cBestellNr) && \mb_strlen($data->cBestellNr) > 0 ? $data->cBestellNr : false;
    }

    /**
     * @param int $orderID
     * @param int $productID
     * @return int
     */
    public static function getProductAmount(int $orderID, int $productID): int
    {
        $data = Shop::Container()->getDB()->queryPrepared(
            'SELECT twarenkorbpos.nAnzahl
                FROM tbestellung
                JOIN twarenkorbpos
                    ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                WHERE tbestellung.kBestellung = :oid
                    AND twarenkorbpos.kArtikel = :pid',
            ['oid' => $orderID, 'pid' => $productID],
            ReturnType::SINGLE_OBJECT
        );

        return (int)($data->nAnzahl ?? 0);
    }

    /**
     * @param int|null $minDelivery
     * @param int|null $maxDelivery
     */
    public function setEstimatedDelivery(int $minDelivery = null, int $maxDelivery = null): void
    {
        $this->oEstimatedDelivery = (object)[
            'localized'  => '',
            'longestMin' => 0,
            'longestMax' => 0,
        ];
        if ($minDelivery !== null && $maxDelivery !== null) {
            $this->oEstimatedDelivery->longestMin = $minDelivery;
            $this->oEstimatedDelivery->longestMax = $maxDelivery;
            $this->oEstimatedDelivery->localized  = (!empty($this->oEstimatedDelivery->longestMin)
                && !empty($this->oEstimatedDelivery->longestMax))
                ? ShippingMethod::getDeliverytimeEstimationText(
                    $this->oEstimatedDelivery->longestMin,
                    $this->oEstimatedDelivery->longestMax
                )
                : '';
        }
        $this->cEstimatedDelivery = &$this->oEstimatedDelivery->localized;
    }

    /**
     * @return $this
     */
    public function berechneEstimatedDelivery(): self
    {
        $minDeliveryDays = null;
        $maxDeliveryDays = null;
        if (\is_array($this->Positionen) && \count($this->Positionen) > 0) {
            $minDeliveryDays = 0;
            $maxDeliveryDays = 0;
            $lang            = LanguageHelper::getIsoFromLangID((int)$this->kSprache);
            foreach ($this->Positionen as $item) {
                $item->nPosTyp = (int)$item->nPosTyp;
                if ($item->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL
                    || !isset($item->Artikel)
                    || !$item->Artikel instanceof Artikel
                ) {
                    continue;
                }
                $item->Artikel->getDeliveryTime(
                    $this->Lieferadresse->cLand ?? null,
                    $item->nAnzahl,
                    $item->fLagerbestandVorAbschluss,
                    $lang->cISO ?? null,
                    $this->kVersandart
                );
                CartItem::setEstimatedDelivery(
                    $item,
                    $item->Artikel->nMinDeliveryDays,
                    $item->Artikel->nMaxDeliveryDays
                );
                if (isset($item->Artikel->nMinDeliveryDays) && $item->Artikel->nMinDeliveryDays > $minDeliveryDays) {
                    $minDeliveryDays = $item->Artikel->nMinDeliveryDays;
                }
                if (isset($item->Artikel->nMaxDeliveryDays) && $item->Artikel->nMaxDeliveryDays > $maxDeliveryDays) {
                    $maxDeliveryDays = $item->Artikel->nMaxDeliveryDays;
                }
            }
        }
        $this->setEstimatedDelivery($minDeliveryDays, $maxDeliveryDays);

        return $this;
    }

    /**
     * @return string
     * @deprecated since 4.06
     */
    public function getEstimatedDeliveryTime(): string
    {
        if (empty($this->oEstimatedDelivery->localized)) {
            $this->berechneEstimatedDelivery();
        }

        return $this->oEstimatedDelivery->localized;
    }

    /**
     * set Kampagne
     */
    public function setKampagne(): void
    {
        $this->oKampagne = Shop::Container()->getDB()->queryPrepared(
            'SELECT tkampagne.kKampagne, tkampagne.cName, tkampagne.cParameter, tkampagnevorgang.dErstellt,
                    tkampagnevorgang.kKey AS kBestellung, tkampagnevorgang.cParamWert AS cWert
                FROM tkampagnevorgang
                    LEFT JOIN tkampagne 
                    ON tkampagne.kKampagne = tkampagnevorgang.kKampagne
                WHERE tkampagnevorgang.kKampagneDef = :kampagneDef
                    AND tkampagnevorgang.kKey = :orderID',
            [
                'orderID'     => $this->kBestellung,
                'kampagneDef' => \KAMPAGNE_DEF_VERKAUF
            ],
            ReturnType::SINGLE_OBJECT
        );
    }
}
