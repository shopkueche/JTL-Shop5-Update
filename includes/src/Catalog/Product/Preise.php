<?php

namespace JTL\Catalog\Product;

use JTL\Catalog\Currency;
use JTL\DB\ReturnType;
use JTL\Helpers\Tax;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class Preise
 * @package JTL\Catalog\Product
 */
class Preise
{
    /**
     * @var int
     */
    public $kKundengruppe;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var array
     */
    public $cVKLocalized;

    /**
     * @var float
     */
    public $fVKNetto = 0.0;

    /**
     * @var float
     */
    public $fVKBrutto = 0.0;

    /**
     * @var float
     */
    public $fPreis1;

    /**
     * @var float
     */
    public $fPreis2;

    /**
     * @var float
     */
    public $fPreis3;

    /**
     * @var float
     */
    public $fPreis4;

    /**
     * @var float
     */
    public $fPreis5;

    /**
     * @var float
     */
    public $fUst;

    /**
     * @var float
     */
    public $alterVKNetto;

    /**
     * @var int
     */
    public $nAnzahl1;

    /**
     * @var int
     */
    public $nAnzahl2;

    /**
     * @var int
     */
    public $nAnzahl3;

    /**
     * @var int
     */
    public $nAnzahl4;

    /**
     * @var int
     */
    public $nAnzahl5;

    /**
     * @var array
     */
    public $alterVK;

    /**
     * @var float
     */
    public $rabatt;

    /**
     * @var array
     */
    public $alterVKLocalized;

    /**
     * @var array
     */
    public $fVK;

    /**
     * @var array
     */
    public $nAnzahl_arr = [];

    /**
     * @var array
     */
    public $fPreis_arr = [];

    /**
     * @var array
     */
    public $fStaffelpreis_arr = [];

    /**
     * @var array
     */
    public $cPreisLocalized_arr = [];

    /**
     * @var bool|int
     */
    public $Sonderpreis_aktiv = false;

    /**
     * @var bool
     */
    public $Kundenpreis_aktiv = false;

    /**
     * @var PriceRange
     */
    public $oPriceRange;

    /**
     * @var string
     */
    public $SonderpreisBis_en;

    /**
     * @var string
     */
    public $SonderpreisBis_de;

    /**
     * @var int
     */
    public $discountPercentage = 0;

    /**
     * Preise constructor.
     * @param int $customerGroupID
     * @param int $productID
     * @param int $customerID
     * @param int $taxClassID
     */
    public function __construct(int $customerGroupID, int $productID, int $customerID = 0, int $taxClassID = 0)
    {
        $db             = Shop::Container()->getDB();
        $customerFilter = 'AND p.kKundengruppe = ' . $customerGroupID;
        if ($customerID > 0 && $this->hasCustomPrice($customerID)) {
            $customerFilter = 'AND (p.kKundengruppe, COALESCE(p.kKunde, 0)) = (
                            SELECT min(IFNULL(p1.kKundengruppe, ' . $customerGroupID . ')), max(IFNULL(p1.kKunde, 0))
                            FROM tpreis AS p1
                            WHERE p1.kArtikel = ' . $productID . '
                                AND (p1.kKundengruppe = 0 OR p1.kKundengruppe = ' . $customerGroupID . ')
                                AND (p1.kKunde = 0 OR p1.kKunde = ' . $customerID . '))';
        }
        $this->kArtikel      = $productID;
        $this->kKundengruppe = $customerGroupID;
        $this->kKunde        = $customerID;

        $prices = $db->query(
            'SELECT *
                FROM tpreis AS p
                JOIN tpreisdetail AS d ON d.kPreis = p.kPreis
                WHERE p.kArtikel = ' . $productID . ' ' . $customerFilter . '
                ORDER BY d.nAnzahlAb',
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($prices) > 0) {
            if ($taxClassID === 0) {
                $tax        = $db->select(
                    'tartikel',
                    'kArtikel',
                    $productID,
                    null,
                    null,
                    null,
                    null,
                    false,
                    'kSteuerklasse'
                );
                $taxClassID = (int)$tax->kSteuerklasse;
            }
            $this->fUst        = Tax::getSalesTax($taxClassID);
            $tmp               = $db->select(
                'tartikel',
                'kArtikel',
                $productID,
                null,
                null,
                null,
                null,
                false,
                'fMwSt'
            );
            $defaultTax        = $tmp->fMwSt;
            $currentTax        = $this->fUst;
            $specialPriceValue = null;
            foreach ($prices as $i => $price) {
                // Kundenpreis?
                if ((int)$price->kKunde > 0) {
                    $this->Kundenpreis_aktiv = true;
                }
                // Standardpreis
                if ($price->nAnzahlAb < 1) {
                    $this->fVKNetto = $this->getRecalculatedNetPrice($price->fVKNetto, $defaultTax, $currentTax);
                    $specialPrice   = $db->queryPrepared(
                        "SELECT tsonderpreise.fNettoPreis, tartikelsonderpreis.dEnde AS dEnde_en,
                            DATE_FORMAT(tartikelsonderpreis.dEnde, '%d.%m.%Y') AS dEnde_de
                            FROM tsonderpreise
                            JOIN tartikel 
                                ON tartikel.kArtikel = :productID
                            JOIN tartikelsonderpreis 
                                ON tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis
                                AND tartikelsonderpreis.kArtikel = :productID
                                AND tartikelsonderpreis.cAktiv = 'Y'
                                AND tartikelsonderpreis.dStart <= CURDATE()
                                AND (tartikelsonderpreis.dEnde IS NULL OR tartikelsonderpreis.dEnde >= CURDATE()) 
                                AND (tartikelsonderpreis.nAnzahl <= tartikel.fLagerbestand 
                                    OR tartikelsonderpreis.nIstAnzahl = 0)
                            WHERE tsonderpreise.kKundengruppe = :customerGroup",
                        [
                            'productID'     => $productID,
                            'customerGroup' => $customerGroupID,
                        ],
                        ReturnType::SINGLE_OBJECT
                    );

                    if (isset($specialPrice->fNettoPreis)) {
                        $specialPrice->fNettoPreis = $this->getRecalculatedNetPrice(
                            $specialPrice->fNettoPreis,
                            $defaultTax,
                            $currentTax
                        );
                        if ((double)$specialPrice->fNettoPreis < $this->fVKNetto) {
                            $specialPriceValue       = $specialPrice->fNettoPreis;
                            $this->alterVKNetto      = $this->fVKNetto;
                            $this->fVKNetto          = $specialPriceValue;
                            $this->Sonderpreis_aktiv = 1;
                            $this->SonderpreisBis_de = $specialPrice->dEnde_de;
                            $this->SonderpreisBis_en = $specialPrice->dEnde_en;
                        }
                    }
                } else {
                    // Alte Preisstaffeln
                    if ($i <= 5) {
                        $scaleGetter = 'nAnzahl' . $i;
                        $priceGetter = 'fPreis' . $i;

                        $this->{$scaleGetter} = (int)$price->nAnzahlAb;
                        $this->{$priceGetter} = $specialPriceValue ?? $this->getRecalculatedNetPrice(
                            $price->fVKNetto,
                            $defaultTax,
                            $currentTax
                        );
                    }

                    $this->nAnzahl_arr[] = (int)$price->nAnzahlAb;
                    $this->fPreis_arr[]  =
                        ($specialPriceValue !== null && $specialPriceValue < (double)$price->fVKNetto)
                            ? $specialPriceValue
                            : $this->getRecalculatedNetPrice($price->fVKNetto, $defaultTax, $currentTax);
                }
            }
        }

        $this->berechneVKs();
        $this->oPriceRange = new PriceRange($productID, $customerGroupID, $customerID);
        \executeHook(\HOOK_PRICES_CONSTRUCT, [
            'customerGroupID' => $customerGroupID,
            'customerID'      => $customerID,
            'productID'       => $productID,
            'taxClassID'      => $taxClassID,
            'prices'          => $this
        ]);
    }

    /**
     * Return recalculated new net price based on the rounded default gross price.
     * This is necessary for having consistent gross prices in case of
     * threshold delivery (Tax rate != default tax rate).
     *
     * @param float|string $netPrice      the product net price
     * @param float|string $defaultTax    the default tax factor of the product e.g. 19 for 19% vat
     * @param float|string $conversionTax the taxFactor of the delivery country / delivery threshold
     * @return double - calculated net price based on a rounded(!!!) DEFAULT gross price.
     */
    private function getRecalculatedNetPrice($netPrice, $defaultTax, $conversionTax)
    {
        $newNetPrice = $netPrice;
        if (\CONSISTENT_GROSS_PRICES === true
            && $defaultTax > 0
            && $conversionTax > 0 &&
            $defaultTax != $conversionTax
        ) {
            $newNetPrice = \round($netPrice * ($defaultTax + 100) / 100, 2) / ($conversionTax + 100) * 100;
        }

        return (double)$newNetPrice;
    }

    /**
     * @param int $customerID
     * @return bool
     */
    protected function hasCustomPrice(int $customerID): bool
    {
        if ($customerID <= 0) {
            return false;
        }
        $cacheID = 'custprice_' . $customerID;
        if (($data = Shop::Container()->getCache()->get($cacheID)) === false) {
            $data = Shop::Container()->getDB()->query(
                'SELECT COUNT(kPreis) AS nAnzahl 
                    FROM tpreis
                    WHERE kKunde = ' . $customerID,
                ReturnType::SINGLE_OBJECT
            );
            if (\is_object($data)) {
                $cacheTags = [\CACHING_GROUP_ARTICLE];
                Shop::Container()->getCache()->set($cacheID, $data, $cacheTags);
            }
        }

        return \is_object($data) && $data->nAnzahl > 0;
    }

    /**
     * @return bool
     */
    public function isDiscountable(): bool
    {
        return !($this->Kundenpreis_aktiv || $this->Sonderpreis_aktiv);
    }

    /**
     * @return $this
     * @deprecated since 5.0.0 - removed tpreise
     */
    public function loadFromDB(): self
    {
        \trigger_error(__FUNCTION__ . ' is deprecated.', \E_USER_DEPRECATED);

        return $this;
    }

    /**
     * @param float $discount
     * @param float $offset
     * @return $this
     */
    public function rabbatierePreise($discount, $offset = 0.0): self
    {
        if ($discount != 0 && $this->isDiscountable()) {
            $this->rabatt       = $discount;
            $this->alterVKNetto = $this->fVKNetto;

            $this->fVKNetto = ($this->fVKNetto - $this->fVKNetto * $discount / 100.0) + $offset;
            $this->fPreis1  = ($this->fPreis1 - $this->fPreis1 * $discount / 100.0) + $offset;
            $this->fPreis2  = ($this->fPreis2 - $this->fPreis2 * $discount / 100.0) + $offset;
            $this->fPreis3  = ($this->fPreis3 - $this->fPreis3 * $discount / 100.0) + $offset;
            $this->fPreis4  = ($this->fPreis4 - $this->fPreis4 * $discount / 100.0) + $offset;
            $this->fPreis5  = ($this->fPreis5 - $this->fPreis5 * $discount / 100.0) + $offset;

            foreach ($this->fPreis_arr as $i => $fPreis) {
                $this->fPreis_arr[$i] = ($fPreis - $fPreis * $discount / 100.0) + $offset;
            }
            $this->berechneVKs();
            $this->oPriceRange->setDiscount($discount);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function localizePreise(): self
    {
        $currency                  = Frontend::getCurrency();
        $this->cPreisLocalized_arr = [];
        foreach ($this->fPreis_arr as $price) {
            $this->cPreisLocalized_arr[] = [
                self::getLocalizedPriceString(Tax::getGross($price, $this->fUst, 4), $currency),
                self::getLocalizedPriceString($price, $currency)
            ];
        }
        $this->cVKLocalized[0] = self::getLocalizedPriceString(
            Tax::getGross($this->fVKNetto, $this->fUst, 4),
            $currency
        );
        $this->cVKLocalized[1] = self::getLocalizedPriceString($this->fVKNetto, $currency);
        $this->fVKBrutto       = Tax::getGross($this->fVKNetto, $this->fUst);
        if ($this->alterVKNetto) {
            $this->alterVKLocalized[0] = self::getLocalizedPriceString(
                Tax::getGross($this->alterVKNetto, $this->fUst, 4),
                $currency
            );
            $this->alterVKLocalized[1] = self::getLocalizedPriceString($this->alterVKNetto, $currency);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function berechneVKs(): self
    {
        $factor = Frontend::getCurrency()->getConversionFactor();

        $this->fVKBrutto = Tax::getGross($this->fVKNetto, $this->fUst);

        $this->fVK[0] = Tax::getGross($this->fVKNetto * $factor, $this->fUst);
        $this->fVK[1] = $this->fVKNetto * $factor;

        $this->alterVK[0] = Tax::getGross($this->alterVKNetto * $factor, $this->fUst);
        $this->alterVK[1] = $this->alterVKNetto * $factor;

        $this->fStaffelpreis_arr = [];
        foreach ($this->fPreis_arr as $price) {
            $this->fStaffelpreis_arr[] = [
                Tax::getGross($price * $factor, $this->fUst),
                $price * $factor
            ];
        }
        if (!empty($this->alterVKNetto)) {
            $this->discountPercentage = (int)((($this->alterVKNetto - $this->fVKNetto) * 100) / $this->alterVKNetto);
        }

        return $this;
    }

    /**
     * @retun int
     * @deprecated since 5.0.0 - removed tpreise
     */
    public function insertInDB(): int
    {
        \trigger_error(__FUNCTION__ . ' is deprecated.', \E_USER_DEPRECATED);

        return 0;
    }

    /**
     * setzt Daten aus Sync POST request.
     *
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setzePostDaten(): bool
    {
        return false;
    }

    /**
     * @param int    $customerGroupID
     * @param string $priceAlias
     * @param string $detailAlias
     * @param string $productAlias
     * @return string
     */
    public static function getPriceJoinSql(
        int $customerGroupID,
        $priceAlias = 'tpreis',
        $detailAlias = 'tpreisdetail',
        $productAlias = 'tartikel'
    ): string {
        return 'JOIN tpreis AS ' . $priceAlias . ' ON ' . $priceAlias . '.kArtikel = ' . $productAlias . '.kArtikel
                    AND ' . $priceAlias . '.kKundengruppe = ' . $customerGroupID . '
                JOIN tpreisdetail AS ' . $detailAlias . ' ON ' . $detailAlias . '.kPreis = ' . $priceAlias . '.kPreis
                    AND ' . $detailAlias . '.nAnzahlAb = 0';
    }

    /**
     * Set all fvk prices to zero.
     */
    public function setPricesToZero(): void
    {
        $this->fVKNetto  = 0;
        $this->fVKBrutto = 0;
        foreach ($this->fVK as $key => $fVK) {
            $this->fVK[$key] = 0;
        }
        foreach ($this->alterVK as $key => $alterVK) {
            $this->alterVK[$key] = 0;
        }
        $this->fPreis1 = 0;
        $this->fPreis2 = 0;
        $this->fPreis3 = 0;
        $this->fPreis4 = 0;
        $this->fPreis5 = 0;
        foreach ($this->fPreis_arr as $key => $price) {
            $this->fPreis_arr[$key] = 0;
        }
        foreach ($this->fStaffelpreis_arr as $fStaffelpreisKey => $fStaffelpreis) {
            foreach ($fStaffelpreis as $pKey => $price) {
                $this->fStaffelpreis_arr[$fStaffelpreisKey][$pKey] = 0;
            }
        }
    }

    /**
     * @param float         $preis
     * @param Currency|null $waehrung
     * @param bool          $html
     * @return string
     * @former gibPreisLocalizedOhneFaktor()
     */
    public static function getLocalizedPriceWithoutFactor($preis, $waehrung = null, bool $html = true): string
    {
        $currency = !$waehrung ? Frontend::getCurrency() : $waehrung;
        if ($currency !== null && \get_class($currency) === 'stdClass') {
            $currency = new Currency($currency->kWaehrung);
        }
        $localized = \number_format($preis, 2, $currency->getDecimalSeparator(), $currency->getThousandsSeparator());
        $name      = $html ? $currency->getHtmlEntity() : $currency->getName();

        return $currency->getForcePlacementBeforeNumber()
            ? $name . ' ' . $localized
            : $localized . ' ' . $name;
    }

    /**
     * @param float|string $price
     * @param object|null  $currency
     * @param bool         $html
     * @param int          $decimals
     * @return string
     * @former self::getLocalizedPriceString()
     */
    public static function getLocalizedPriceString(
        $price,
        $currency = null,
        bool $html = true,
        int $decimals = 2
    ): string {
        if ($currency === null || \is_numeric($currency) || \is_bool($currency)) {
            $currency = Frontend::getCurrency();
        } elseif (\is_object($currency) && ($currency instanceof stdClass)) {
            $currency = new Currency((int)$currency->kWaehrung);
        } elseif (\is_string($currency)) {
            $currency = Currency::fromISO($currency);
        }
        $localized    = \number_format(
            $price * $currency->getConversionFactor(),
            $decimals,
            $currency->getDecimalSeparator(),
            $currency->getThousandsSeparator()
        );
        $currencyName = $html ? $currency->getHtmlEntity() : $currency->getName();

        return $currency->getForcePlacementBeforeNumber()
            ? ($currencyName . ' ' . $localized)
            : ($localized . ' ' . $currencyName);
    }
}
