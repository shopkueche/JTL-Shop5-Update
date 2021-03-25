<?php

namespace JTL\Cart;

use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\EigenschaftWert;
use JTL\Catalog\Product\Preise;
use JTL\Checkout\Eigenschaft;
use JTL\Extensions\Config\Item;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class CartItem
 * @package JTL\Cart
 */
class CartItem
{
    /**
     * @var int
     */
    public $kWarenkorbPos;

    /**
     * @var int
     */
    public $kWarenkorb;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var int
     */
    public $kSteuerklasse;

    /**
     * @var int
     */
    public $kVersandklasse = 0;

    /**
     * @var int
     */
    public $nAnzahl;

    /**
     * @var int
     */
    public $nPosTyp;

    /**
     * @var float
     */
    public $fPreisEinzelNetto;

    /**
     * @var float
     */
    public $fPreis;

    /**
     * @var float
     */
    public $fMwSt;

    /**
     * @var float
     */
    public $fGesamtgewicht;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cEinheit = '';

    /**
     * @var array
     */
    public $cGesamtpreisLocalized;

    /**
     * @var string
     */
    public $cHinweis = '';

    /**
     * @var string
     */
    public $cUnique = '';

    /**
     * @var string
     */
    public $cResponsibility = '';

    /**
     * @var int
     */
    public $kKonfigitem;

    /**
     * @var array
     */
    public $cKonfigpreisLocalized;

    /**
     * @var Artikel
     */
    public $Artikel;

    /**
     * @var array
     */
    public $WarenkorbPosEigenschaftArr = [];

    /**
     * @var object[]
     */
    public $variationPicturesArr = [];

    /**
     * @var int
     */
    public $nZeitLetzteAenderung = 0;

    /**
     * @var float
     */
    public $fLagerbestandVorAbschluss = 0.0;

    /**
     * @var int
     */
    public $kBestellpos = 0;

    /**
     * @var string
     */
    public $cLieferstatus = '';

    /**
     * @var string
     */
    public $cArtNr = '';

    /**
     * @var int
     */
    public $nAnzahlEinzel;

    /**
     * @var array
     */
    public $cEinzelpreisLocalized;

    /**
     * @var array
     */
    public $cKonfigeinzelpreisLocalized;

    /**
     * @var string
     */
    public $cEstimatedDelivery = '';

    /**
     * @var string
     */
    public $cArticleNameAffix;

    /**
     * @var string
     */
    public $discountForArticle;

    /**
     * @var object {
     *      localized: string,
     *      longestMin: int,
     *      longestMax: int,
     * }
     */
    public $oEstimatedDelivery;

    /**
     * CartItem constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * Setzt in dieser Position einen Eigenschaftswert der angegebenen Eigenschaft.
     * Existiert ein EigenschaftsWert für die Eigenschaft, so wir er überschrieben, ansonsten neu angelegt
     *
     * @param int    $propertyID
     * @param int    $valueID
     * @param string $freeText
     * @return bool
     */
    public function setzeVariationsWert(int $propertyID, int $valueID, $freeText = ''): bool
    {
        $db                                = Shop::Container()->getDB();
        $attributeValue                    = new EigenschaftWert($valueID);
        $attribute                         = new Eigenschaft($propertyID);
        $newAttributes                     = new CartItemProperty();
        $newAttributes->kEigenschaft       = $propertyID;
        $newAttributes->kEigenschaftWert   = $valueID;
        $newAttributes->fGewichtsdifferenz = $attributeValue->fGewichtDiff;
        $newAttributes->fAufpreis          = $attributeValue->fAufpreisNetto;
        $Aufpreis_obj                      = $db->select(
            'teigenschaftwertaufpreis',
            'kEigenschaftWert',
            (int)$newAttributes->kEigenschaftWert,
            'kKundengruppe',
            Frontend::getCustomerGroup()->getID()
        );
        if (!empty($Aufpreis_obj->fAufpreisNetto)) {
            if ($this->Artikel->Preise->rabatt > 0) {
                $newAttributes->fAufpreis     = $Aufpreis_obj->fAufpreisNetto -
                    (($this->Artikel->Preise->rabatt / 100) * $Aufpreis_obj->fAufpreisNetto);
                $Aufpreis_obj->fAufpreisNetto = $newAttributes->fAufpreis;
            } else {
                $newAttributes->fAufpreis = $Aufpreis_obj->fAufpreisNetto;
            }
        }
        $newAttributes->cTyp               = $attribute->cTyp;
        $newAttributes->cAufpreisLocalized = Preise::getLocalizedPriceString($newAttributes->fAufpreis);
        //posname lokalisiert ablegen
        $newAttributes->cEigenschaftName     = [];
        $newAttributes->cEigenschaftWertName = [];
        foreach ($_SESSION['Sprachen'] as $language) {
            $newAttributes->cEigenschaftName[$language->cISO]     = $attribute->cName;
            $newAttributes->cEigenschaftWertName[$language->cISO] = $attributeValue->cName;

            if ($language->cStandard !== 'Y') {
                $eigenschaft_spr = $db->select(
                    'teigenschaftsprache',
                    'kEigenschaft',
                    (int)$newAttributes->kEigenschaft,
                    'kSprache',
                    (int)$language->kSprache
                );
                if (!empty($eigenschaft_spr->cName)) {
                    $newAttributes->cEigenschaftName[$language->cISO] = $eigenschaft_spr->cName;
                }
                $eigenschaftwert_spr = $db->select(
                    'teigenschaftwertsprache',
                    'kEigenschaftWert',
                    (int)$newAttributes->kEigenschaftWert,
                    'kSprache',
                    (int)$language->kSprache
                );
                if (!empty($eigenschaftwert_spr->cName)) {
                    $newAttributes->cEigenschaftWertName[$language->cISO] = $eigenschaftwert_spr->cName;
                }
            }

            if ($freeText || \mb_strlen(\trim($freeText)) > 0) {
                $newAttributes->cEigenschaftWertName[$language->cISO] = $db->escape($freeText);
            }
        }
        $this->WarenkorbPosEigenschaftArr[] = $newAttributes;
        $this->fGesamtgewicht               = $this->gibGesamtgewicht();

        return true;
    }

    /**
     * gibt EigenschaftsWert zu einer Eigenschaft bei dieser Position
     *
     * @param int $propertyID - ID der Eigenschaft
     * @return int - gesetzter Wert. Falls nicht gesetzt, wird 0 zurückgegeben
     */
    public function gibGesetztenEigenschaftsWert(int $propertyID): int
    {
        foreach ($this->WarenkorbPosEigenschaftArr as $WKPosEigenschaft) {
            $WKPosEigenschaft->kEigenschaft = (int)$WKPosEigenschaft->kEigenschaft;
            if ($WKPosEigenschaft->kEigenschaft === $propertyID) {
                return (int)$WKPosEigenschaft->kEigenschaftWert;
            }
        }

        return 0;
    }

    /**
     * gibt Summe der Aufpreise der Variationen dieser Position zurück
     *
     * @return float
     */
    public function gibGesamtAufpreis()
    {
        $aufpreis = 0;
        foreach ($this->WarenkorbPosEigenschaftArr as $WKPosEigenschaft) {
            if ($WKPosEigenschaft->fAufpreis != 0) {
                $aufpreis += $WKPosEigenschaft->fAufpreis;
            }
        }

        return $aufpreis;
    }

    /**
     * gibt Gewicht dieser Position zurück. Variationen und PosAnzahl berücksichtigt
     *
     * @return float
     */
    public function gibGesamtgewicht()
    {
        $gewicht = $this->Artikel->fGewicht * $this->nAnzahl;

        if (!$this->Artikel->kVaterArtikel) {
            foreach ($this->WarenkorbPosEigenschaftArr as $WKPosEigenschaft) {
                if ($WKPosEigenschaft->fGewichtsdifferenz != 0) {
                    $gewicht += $WKPosEigenschaft->fGewichtsdifferenz * $this->nAnzahl;
                }
            }
        }

        return $gewicht;
    }

    /**
     * Calculate the total weight of a config item and his components.
     *
     * @return float|int
     */
    public function getTotalConfigWeight()
    {
        $weight = $this->Artikel->fGewicht * $this->nAnzahl;
        if ($this->kKonfigitem === 0 && !empty($this->cUnique)) {
            foreach (Frontend::getCart()->PositionenArr as $item) {
                if ($item->cUnique === $this->cUnique && $item->istKonfigKind()) {
                    $weight += $item->fGesamtgewicht;
                }
            }
        }

        return $weight;
    }

    /**
     * typo in function name - for compatibility reasons only
     *
     * @deprecated since 4.05
     * @return $this
     */
    public function setzeGesamtpreisLoacalized(): self
    {
        return $this->setzeGesamtpreisLocalized();
    }

    /**
     * gibt Gesamtpreis inkl. aller Aufpreise * Positionsanzahl lokalisiert als String zurück
     *
     * @return $this
     */
    public function setzeGesamtpreisLocalized(): self
    {
        /** @var array('Warenkorb' => Warenkorb) $_SESSION */
        if (!\is_array($_SESSION['Waehrungen'])) {
            return $this;
        }
        $tax = Tax::getSalesTax($this->kSteuerklasse);
        foreach (Frontend::getCurrencies() as $currency) {
            $currencyName = $currency->getName();
            // Standardartikel
            $this->cGesamtpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                Tax::getGross($this->fPreis * $this->nAnzahl, $tax, 4),
                $currency
            );
            $this->cGesamtpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                $this->fPreis * $this->nAnzahl,
                $currency
            );
            $this->cEinzelpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                Tax::getGross($this->fPreis, $tax, 4),
                $currency
            );
            $this->cEinzelpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString($this->fPreis, $currency);
            if (!empty($this->Artikel->cVPEEinheit)
                && isset($this->Artikel->cVPE)
                && $this->Artikel->cVPE === 'Y'
                && $this->Artikel->fVPEWert > 0
            ) {
                $this->Artikel->baueVPE($this->fPreis);
            }
            if ($this->istKonfigVater()) {
                $this->cKonfigpreisLocalized[0][$currencyName]       = Preise::getLocalizedPriceString(
                    Tax::getGross($this->fPreis * $this->nAnzahl, $tax, 4),
                    $currency
                );
                $this->cKonfigpreisLocalized[1][$currencyName]       = Preise::getLocalizedPriceString(
                    $this->fPreis * $this->nAnzahl,
                    $currency
                );
                $this->cKonfigeinzelpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                    Tax::getGross($this->fPreis, $tax, 4),
                    $currency
                );
                $this->cKonfigeinzelpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                    $this->fPreis,
                    $currency
                );
            }
            if ($this->istKonfigKind()) {
                $net       = 0;
                $gross     = 0;
                $parentIdx = null;
                if (!empty($this->cUnique)) {
                    foreach (Frontend::getCart()->PositionenArr as $idx => $item) {
                        if ($this->cUnique === $item->cUnique) {
                            $net   += $item->fPreis * $item->nAnzahl;
                            $gross += Tax::getGross(
                                $item->fPreis * $item->nAnzahl,
                                $tax,
                                4
                            );

                            if ($item->istKonfigVater()) {
                                $parentIdx = $idx;
                            }
                        }
                    }
                }
                if ($parentIdx !== null) {
                    $parent = Frontend::getCart()->PositionenArr[$parentIdx];
                    if (\is_object($parent)) {
                        $this->nAnzahlEinzel = $this->isIgnoreMultiplier()
                            ? $this->nAnzahl
                            : $this->nAnzahl / $parent->nAnzahl;

                        $parent->cKonfigpreisLocalized[0][$currencyName]       = Preise::getLocalizedPriceString(
                            $gross,
                            $currency
                        );
                        $parent->cKonfigpreisLocalized[1][$currencyName]       = Preise::getLocalizedPriceString(
                            $net,
                            $currency
                        );
                        $parent->cKonfigeinzelpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                            $gross / $parent->nAnzahl,
                            $currency
                        );
                        $parent->cKonfigeinzelpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                            $net / $parent->nAnzahl,
                            $currency
                        );
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param int $kWarenkorbPos
     * @return $this
     */
    public function loadFromDB(int $kWarenkorbPos): self
    {
        $obj     = Shop::Container()->getDB()->select('twarenkorbpos', 'kWarenkorbPos', $kWarenkorbPos);
        $members = \array_keys(\get_object_vars($obj));
        foreach ($members as $member) {
            $this->$member = $obj->$member;
        }
        if (isset($this->nLongestMinDelivery, $this->nLongestMaxDelivery)) {
            self::setEstimatedDelivery($this, $this->nLongestMinDelivery, $this->nLongestMaxDelivery);
            unset($this->nLongestMinDelivery, $this->nLongestMaxDelivery);
        } else {
            self::setEstimatedDelivery($this);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        $obj                            = new stdClass();
        $obj->kWarenkorb                = $this->kWarenkorb;
        $obj->kArtikel                  = $this->kArtikel;
        $obj->kVersandklasse            = $this->kVersandklasse;
        $obj->cName                     = $this->cName;
        $obj->cLieferstatus             = $this->cLieferstatus;
        $obj->cArtNr                    = $this->cArtNr;
        $obj->cEinheit                  = $this->cEinheit ?? '';
        $obj->fPreisEinzelNetto         = $this->fPreisEinzelNetto;
        $obj->fPreis                    = $this->fPreis;
        $obj->fMwSt                     = $this->fMwSt;
        $obj->nAnzahl                   = $this->nAnzahl;
        $obj->nPosTyp                   = $this->nPosTyp;
        $obj->cHinweis                  = $this->cHinweis ?? '';
        $obj->cUnique                   = $this->cUnique;
        $obj->cResponsibility           = !empty($this->cResponsibility) ? $this->cResponsibility : 'core';
        $obj->kKonfigitem               = $this->kKonfigitem;
        $obj->kBestellpos               = $this->kBestellpos;
        $obj->fLagerbestandVorAbschluss = $this->fLagerbestandVorAbschluss;

        if (isset($this->oEstimatedDelivery->longestMin)) {
            // Lieferzeiten nur speichern, wenn sie gesetzt sind, also z.B. nicht bei Versandkosten etc.
            $obj->nLongestMinDelivery = $this->oEstimatedDelivery->longestMin;
            $obj->nLongestMaxDelivery = $this->oEstimatedDelivery->longestMax;
        }

        $this->kWarenkorbPos = Shop::Container()->getDB()->insert('twarenkorbpos', $obj);

        if ($this->nPosTyp === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
            $oGift               = new stdClass();
            $oGift->kWarenkorb   = $this->kWarenkorb;
            $oGift->kArtikel     = $this->kArtikel;
            $oGift->nAnzahl      = $this->nAnzahl;
            $this->kWarenkorbPos = Shop::Container()->getDB()->insert('tgratisgeschenk', $oGift);
        }

        return $this->kWarenkorbPos;
    }

    /**
     * @return bool
     */
    public function istKonfigVater(): bool
    {
        return \is_string($this->cUnique) && !empty($this->cUnique) && (int)$this->kKonfigitem === 0;
    }

    /**
     * @return bool
     */
    public function istKonfigKind(): bool
    {
        return \is_string($this->cUnique) && !empty($this->cUnique) && (int)$this->kKonfigitem > 0;
    }

    /**
     * @return bool
     */
    public function istKonfig(): bool
    {
        return $this->istKonfigVater() || $this->istKonfigKind();
    }

    /**
     * @param CartItem $cartPos
     * @param int|null $minDelivery
     * @param int|null $maxDelivery
     */
    public static function setEstimatedDelivery($cartPos, int $minDelivery = null, int $maxDelivery = null): void
    {
        $cartPos->oEstimatedDelivery = (object)[
            'localized'  => '',
            'longestMin' => 0,
            'longestMax' => 0,
        ];
        if ($minDelivery !== null && $maxDelivery !== null) {
            $cartPos->oEstimatedDelivery->longestMin = $minDelivery;
            $cartPos->oEstimatedDelivery->longestMax = $maxDelivery;

            $cartPos->oEstimatedDelivery->localized = (!empty($cartPos->oEstimatedDelivery->longestMin)
                && !empty($cartPos->oEstimatedDelivery->longestMax))
                ? ShippingMethod::getDeliverytimeEstimationText(
                    $cartPos->oEstimatedDelivery->longestMin,
                    $cartPos->oEstimatedDelivery->longestMax
                )
                : '';
        }
        $cartPos->cEstimatedDelivery = &$cartPos->oEstimatedDelivery->localized;
    }

    /**
     * Return value of config item property bIgnoreMultiplier
     *
     * @return bool|int
     */
    public function isIgnoreMultiplier()
    {
        static $ignoreMultipliers = null;

        if ($ignoreMultipliers === null || !\array_key_exists($this->kKonfigitem, $ignoreMultipliers)) {
            $konfigItem        = new Item($this->kKonfigitem);
            $ignoreMultipliers = [
                $this->kKonfigitem => $konfigItem->ignoreMultiplier(),
            ];
        }

        return $ignoreMultipliers[$this->kKonfigitem];
    }

    /**
     * @param string $isoCode
     * @param bool   $excludeShippingCostAttributes
     * @return bool
     */
    public function isUsedForShippingCostCalculation(string $isoCode, bool $excludeShippingCostAttributes = false): bool
    {
        return (!$excludeShippingCostAttributes
            || $this->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL
            || ($this->Artikel && $this->Artikel->isUsedForShippingCostCalculation($isoCode))
        );
    }
}
