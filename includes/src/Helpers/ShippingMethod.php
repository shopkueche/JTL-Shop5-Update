<?php

namespace JTL\Helpers;

use JTL\Cart\Cart;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Checkout\Versandart;
use JTL\Country\Country;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Language\LanguageHelper;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;
use function Functional\first;
use function Functional\map;
use function Functional\some;

/**
 * Class ShippingMethod
 * @package JTL\Helpers
 */
class ShippingMethod
{
    /**
     * @var ShippingMethod
     */
    private static $instance;

    /**
     * @var string
     */
    public $cacheID;

    /**
     * @var array
     */
    public $shippingMethods;

    /**
     * @var array
     */
    public $countries = [];

    /**
     *
     */
    public function __construct()
    {
        $this->cacheID         = 'smeth_' . Shop::Container()->getCache()->getBaseID();
        $this->shippingMethods = $this->getShippingMethods();
        self::$instance        = $this;
    }

    /**
     * @return ShippingMethod
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self();
    }

    /**
     * @return array
     */
    public function getShippingMethods(): array
    {
        return $this->shippingMethods ?? Shop::Container()->getDB()->query(
            'SELECT * FROM tversandart',
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param float|int $freeFromX
     * @return array
     */
    public function filter($freeFromX): array
    {
        $freeFromX = (float)$freeFromX;

        return \array_filter(
            $this->shippingMethods,
            static function ($s) use ($freeFromX) {
                return $s->fVersandkostenfreiAbX !== '0.00'
                    && (float)$s->fVersandkostenfreiAbX > 0
                    && (float)$s->fVersandkostenfreiAbX <= $freeFromX;
            }
        );
    }

    /**
     * @param array $prices
     * @param int $cgroupID
     * @param int $shippingClassID
     * @return string
     */
    public function getFreeShippingCountries(array $prices, int $cgroupID, int $shippingClassID = 0): string
    {
        if (!isset($this->countries[$cgroupID][$shippingClassID])) {
            if (!isset($this->countries[$cgroupID])) {
                $this->countries[$cgroupID] = [];
            }
            $this->countries[$cgroupID][$shippingClassID] = Shop::Container()->getDB()->queryPrepared(
                "SELECT *
                    FROM tversandart
                    WHERE fVersandkostenfreiAbX > 0
                        AND (cVersandklassen = '-1'
                        OR cVersandklassen RLIKE :sClasses)
                        AND (cKundengruppen = '-1' OR FIND_IN_SET(:cGroupID, REPLACE(cKundengruppen, ';', ',')) > 0)",
                [
                    'sClasses' => '^([0-9 -]* )?' . $shippingClassID . ' ',
                    'cGroupID' => $cgroupID
                ],
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
        $shippingFreeCountries = [];
        foreach ($this->countries[$cgroupID][$shippingClassID] as $_method) {
            $price = $_method->eSteuer === 'brutto' ? $prices[0] : $prices[1];
            if ((float)$_method->fVersandkostenfreiAbX >= $price) {
                continue;
            }
            foreach (\explode(' ', $_method->cLaender) as $_country) {
                $shippingFreeCountries[] = $_country;
            }
        }

        return \implode(', ', \array_unique($shippingFreeCountries));
    }

    /**
     * @param string $country
     * @return bool
     */
    public static function normalerArtikelversand($country): bool
    {
        return some(Frontend::getCart()->PositionenArr, static function ($item) use ($country) {
            return (int)$item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                && !self::gibArtikelabhaengigeVersandkosten($country, $item->Artikel, $item->nAnzahl);
        });
    }

    /**
     * @param string $country
     * @return bool
     */
    public static function hasSpecificShippingcosts($country): bool
    {
        return !empty(self::gibArtikelabhaengigeVersandkostenImWK($country, Frontend::getCart()->PositionenArr));
    }

    /**
     * @param int $shippingMethodID
     * @param int $cgroupID
     * @param int $filterPaymentID
     * @return array
     */
    public static function getPaymentMethods(int $shippingMethodID, int $cgroupID, int $filterPaymentID = 0): ?array
    {
        $filterSQL = '';
        $params    = [
            'methodID' => $shippingMethodID,
            'cGroupID' => $cgroupID,
        ];
        if ($filterPaymentID > 0) {
            $filterSQL           = ' AND tzahlungsart.kZahlungsart = :paymentID ';
            $params['paymentID'] = $filterPaymentID;
        }
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT tversandartzahlungsart.*, tzahlungsart.*
                     FROM tversandartzahlungsart, tzahlungsart
                     WHERE tversandartzahlungsart.kVersandart = :methodID
                         ' . $filterSQL . "
                         AND tversandartzahlungsart.kZahlungsart = tzahlungsart.kZahlungsart
                         AND (tzahlungsart.cKundengruppen IS NULL OR tzahlungsart.cKundengruppen = ''
                            OR FIND_IN_SET(:cGroupID, REPLACE(tzahlungsart.cKundengruppen, ';', ',')) > 0)
                         AND tzahlungsart.nActive = 1
                         AND tzahlungsart.nNutzbar = 1
                     ORDER BY tzahlungsart.nSort",
            $params,
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @former gibMoeglicheVersandarten()
     * @param string $countryCode
     * @param string $zip
     * @param string $shippingClasses
     * @param int    $cgroupID
     * @return array
     */
    public static function getPossibleShippingMethods($countryCode, $zip, $shippingClasses, int $cgroupID): array
    {
        $db                       = Shop::Container()->getDB();
        $cart                     = Frontend::getCart();
        $taxClassID               = $cart->gibVersandkostenSteuerklasse();
        $minSum                   = 10000;
        $hasSpecificShippingcosts = self::hasSpecificShippingcosts($countryCode);
        $vatNote                  = null;
        $depending                = self::normalerArtikelversand($countryCode) === false
            ? 'Y'
            : 'N';
        $methods                  = $db->queryPrepared(
            "SELECT * FROM tversandart
                WHERE cNurAbhaengigeVersandart = :depOnly
                    AND cLaender LIKE :iso
                    AND (cVersandklassen = '-1'
                    OR cVersandklassen RLIKE :sClasses)
                    AND (cKundengruppen = '-1'
                    OR FIND_IN_SET(:cGroupID, REPLACE(cKundengruppen, ';', ',')) > 0)
                ORDER BY nSort",
            [
                'iso'      => '%' . $countryCode . '%',
                'cGroupID' => $cgroupID,
                'sClasses' => '^([0-9 -]* )?' . $shippingClasses . ' ',
                'depOnly'  => $depending
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (empty($methods)) {
            return [];
        }
        $netPricesActive = Frontend::getCustomerGroup()->isMerchant();

        foreach ($methods as $i => $shippingMethod) {
            $gross = $shippingMethod->eSteuer !== 'netto';

            $shippingMethod->kVersandart        = (int)$shippingMethod->kVersandart;
            $shippingMethod->kVersandberechnung = (int)$shippingMethod->kVersandberechnung;
            $shippingMethod->nSort              = (int)$shippingMethod->nSort;
            $shippingMethod->nMinLiefertage     = (int)$shippingMethod->nMinLiefertage;
            $shippingMethod->nMaxLiefertage     = (int)$shippingMethod->nMaxLiefertage;
            $shippingMethod->Zuschlag           = self::getAdditionalFees($shippingMethod, $countryCode, $zip);
            $shippingMethod->fEndpreis          = self::calculateShippingFees(
                $shippingMethod,
                $countryCode,
                null
            );
            if ($shippingMethod->fEndpreis === -1) {
                unset($methods[$i]);
                continue;
            }
            if ($netPricesActive === true) {
                $shippingCosts = $gross
                    ? $shippingMethod->fEndpreis / (100 + Tax::getSalesTax($taxClassID)) * 100.0
                    : \round($shippingMethod->fEndpreis, 2);
                $vatNote       = ' ' . Shop::Lang()->get('plus', 'productDetails') . ' ' .
                    Shop::Lang()->get('vat', 'productDetails');
            } else {
                $shippingCosts = $gross
                    ? $shippingMethod->fEndpreis
                    : \round($shippingMethod->fEndpreis * (100 + Tax::getSalesTax($taxClassID)) / 100, 2);
            }
            $shippingMethod->angezeigterName           = [];
            $shippingMethod->angezeigterHinweistext    = [];
            $shippingMethod->cLieferdauer              = [];
            $shippingMethod->specificShippingcosts_arr = null;
            foreach ($_SESSION['Sprachen'] as $language) {
                $localized = $db->select(
                    'tversandartsprache',
                    'kVersandart',
                    (int)$shippingMethod->kVersandart,
                    'cISOSprache',
                    $language->cISO
                );
                if (isset($localized->cName)) {
                    $shippingMethod->angezeigterName[$language->cISO]        = $localized->cName;
                    $shippingMethod->angezeigterHinweistext[$language->cISO] = $localized->cHinweistextShop;
                    $shippingMethod->cLieferdauer[$language->cISO]           = $localized->cLieferdauer;
                }
            }
            if ($shippingMethod->fEndpreis < $minSum) {
                $minSum = $shippingMethod->fEndpreis;
            }
            if ($shippingMethod->fEndpreis == 0) {
                // Abfrage ob ein Artikel Artikelabhängige Versandkosten besitzt
                $shippingMethod->cPreisLocalized = Shop::Lang()->get('freeshipping');
                if ($hasSpecificShippingcosts === true) {
                    $shippingMethod->cPreisLocalized           = Preise::getLocalizedPriceString($shippingCosts);
                    $shippingMethod->specificShippingcosts_arr = self::gibArtikelabhaengigeVersandkostenImWK(
                        $countryCode,
                        $cart->PositionenArr
                    );
                }
            } else {
                // Abfrage ob ein Artikel Artikelabhängige Versandkosten besitzt
                $shippingMethod->cPreisLocalized = Preise::getLocalizedPriceString($shippingCosts) . ($vatNote ?? '');
                if ($hasSpecificShippingcosts === true) {
                    $shippingMethod->specificShippingcosts_arr = self::gibArtikelabhaengigeVersandkostenImWK(
                        $countryCode,
                        $cart->PositionenArr
                    );
                }
            }
            // Abfrage ob die Zahlungsart/en zur Versandart gesetzt ist/sind
            $paymentMethods        = self::getPaymentMethods((int)$shippingMethod->kVersandart, $cgroupID);
            $shippingMethod->valid = some($paymentMethods, static function ($pmm) {
                return PaymentMethod::shippingMethodWithValidPaymentMethod($pmm);
            });
        }
        // auf anzeige filtern
        $possibleMethods = \array_filter(
            \array_merge($methods),
            static function ($p) use ($minSum) {
                return $p->valid
                    && ($p->cAnzeigen === 'immer' || ($p->cAnzeigen === 'guenstigste' && $p->fEndpreis <= $minSum));
            }
        );
        // evtl. Versandkupon anwenden
        if (!empty($_SESSION['VersandKupon'])) {
            foreach ($possibleMethods as $method) {
                $method->fEndpreis = 0;
                // lokalisieren
                $method->cPreisLocalized = Preise::getLocalizedPriceString($method->fEndpreis);
            }
        }

        return $possibleMethods;
    }

    /**
     * @former ermittleVersandkosten()
     * @param string $country
     * @param string $zip
     * @param string $errorMsg
     * @return bool
     */
    public static function getShippingCosts($country, $zip, &$errorMsg = ''): bool
    {
        if ($country !== null && $zip !== null && \mb_strlen($country) > 0 && \mb_strlen($zip) > 0) {
            $cgroupID = Frontend::getCustomerGroup()->getID();
            if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
                $cgroupID = $_SESSION['Kunde']->kKundengruppe;
            }

            $shippingMethods = self::getPossibleShippingMethods(
                Text::filterXSS($country),
                Text::filterXSS($zip),
                self::getShippingClasses(Frontend::getCart()),
                $cgroupID
            );
            if (\count($shippingMethods) > 0) {
                Frontend::set('cLieferlandISO', $country);

                Shop::Smarty()
                    ->assign('ArtikelabhaengigeVersandarten', self::gibArtikelabhaengigeVersandkostenImWK(
                        $country,
                        Frontend::getCart()->PositionenArr
                    ))
                    ->assign('Versandarten', $shippingMethods)
                    ->assign('Versandland', LanguageHelper::getCountryCodeByCountryName($country))
                    ->assign('VersandPLZ', Text::filterXSS($zip));
            } else {
                $errorMsg = Shop::Lang()->get('noDispatchAvailable');
            }
            \executeHook(\HOOK_WARENKORB_PAGE_ERMITTLEVERSANDKOSTEN);

            return true;
        }

        return !(isset($_POST['versandrechnerBTN']) && (\mb_strlen($country) === 0 || \mb_strlen($zip) === 0));
    }

    /**
     * @former ermittleVersandkostenExt()
     * @param array $products
     * @return string
     */
    public static function getShippingCostsExt(array $products): string
    {
        if (!isset($_SESSION['shipping_count'])) {
            $_SESSION['shipping_count'] = 0;
        }
        if (!\is_array($products) || \count($products) === 0) {
            return '';
        }
        $iso      = $_SESSION['cLieferlandISO'] ?? false;
        $cart     = Frontend::getCart();
        $cgroupID = Frontend::getCustomerGroup()->getID();
        if (!$iso) {
            // Falls kein Land in tfirma da
            $iso = 'DE';
        }
        // Baue ZusatzArtikel
        $additionalProduct                  = new stdClass();
        $additionalProduct->fAnzahl         = 0;
        $additionalProduct->fWarenwertNetto = 0;
        $additionalProduct->fGewicht        = 0;

        $shippingClasses        = self::getShippingClasses($cart);
        $conf                   = Shop::getSettings([\CONF_KAUFABWICKLUNG]);
        $defaultOptions         = Artikel::getDefaultOptions();
        $additionalShippingFees = 0;
        $perTaxClass            = [];
        $taxClassID             = 0;
        // Vorkonditionieren -- Gleiche kartikel aufsummieren
        // aber nur, wenn artikelabhaengiger Versand bei dem jeweiligen kArtikel
        $productIDs = [];
        foreach ($products as $product) {
            $productID              = (int)$product['kArtikel'];
            $productIDs[$productID] = isset($productIDs[$productID]) ? 1 : 0;
        }
        $merge = false;
        foreach ($productIDs as $productID => $nArtikelAssoc) {
            if ($nArtikelAssoc !== 1) {
                continue;
            }
            $tmpProduct = (new Artikel())->fuelleArtikel($productID, $defaultOptions);
            // Normaler Variationsartikel
            if ($tmpProduct !== null
                && $tmpProduct->nIstVater === 0
                && $tmpProduct->kVaterArtikel === 0
                && \count($tmpProduct->Variationen) > 0
                && self::pruefeArtikelabhaengigeVersandkosten($tmpProduct) === 2
            ) {
                // Nur wenn artikelabhaengiger Versand gestaffelt als Funktionsattribut gesetzt ist
                $fAnzahl = 0;
                foreach ($products as $i => $prod) {
                    if ($prod['kArtikel'] === $productID) {
                        $fAnzahl += $prod['fAnzahl'];
                        unset($products[$i]);
                    }
                }

                $merged             = [];
                $merged['kArtikel'] = $productID;
                $merged['fAnzahl']  = $fAnzahl;
                $products[]         = $merged;
                $merge              = true;
            }
        }
        if ($merge) {
            $products = \array_merge($products);
        }
        foreach ($products as $i => $product) {
            $tmpProduct = (new Artikel())->fuelleArtikel($product['kArtikel'], $defaultOptions);
            if ($tmpProduct === null || $tmpProduct->kArtikel <= 0) {
                continue;
            }
            $taxClassID = $tmpProduct->kSteuerklasse;
            // Artikelabhaengige Versandkosten?
            if ($tmpProduct->nIstVater === 0) {
                // Summen pro Steuerklasse summieren
                if ($tmpProduct->kSteuerklasse === null) {
                    $perTaxClass[$tmpProduct->kSteuerklasse] = 0;
                }

                $perTaxClass[$tmpProduct->kSteuerklasse] += $tmpProduct->Preise->fVKNetto * $product['fAnzahl'];

                $oVersandPos = self::gibHinzukommendeArtikelAbhaengigeVersandkosten(
                    $tmpProduct,
                    $iso,
                    $product['fAnzahl']
                );
                if ($oVersandPos !== false) {
                    $additionalShippingFees += $oVersandPos->fKosten;
                    continue;
                }
            }
            // Normaler Artikel oder Kind Artikel
            if ($tmpProduct->kVaterArtikel > 0 || \count($tmpProduct->Variationen) === 0) {
                $additionalProduct->fAnzahl         += $product['fAnzahl'];
                $additionalProduct->fWarenwertNetto += $product['fAnzahl'] * $tmpProduct->Preise->fVKNetto;
                $additionalProduct->fGewicht        += $product['fAnzahl'] * $tmpProduct->fGewicht;

                if (\mb_strlen($shippingClasses) > 0
                    && \mb_strpos($shippingClasses, $tmpProduct->kVersandklasse) === false
                ) {
                    $shippingClasses = '-' . $tmpProduct->kVersandklasse;
                } elseif (\mb_strlen($shippingClasses) === 0) {
                    $shippingClasses = $tmpProduct->kVersandklasse;
                }
            } elseif ($tmpProduct->nIstVater === 0
                && $tmpProduct->kVaterArtikel === 0
                && \count($tmpProduct->Variationen) > 0
            ) { // Normale Variation
                if (\mb_strpos($product['cInputData'], '_') === 0) {
                    // 1D
                    [$property0, $propertyValue0] = \explode(':', \mb_substr($product['cInputData'], 1));

                    $variation = Product::findVariation(
                        $tmpProduct->Variationen,
                        $property0,
                        $propertyValue0
                    );

                    $additionalProduct->fAnzahl         += $product['fAnzahl'];
                    $additionalProduct->fWarenwertNetto += $product['fAnzahl'] *
                        ($tmpProduct->Preise->fVKNetto + $variation->fAufpreisNetto);
                    $additionalProduct->fGewicht        += $product['fAnzahl'] *
                        ($tmpProduct->fGewicht + $variation->fGewichtDiff);
                } else {
                    // 2D
                    [$cVariation0, $cVariation1]  = \explode('_', $product['cInputData']);
                    [$property0, $propertyValue0] = \explode(':', $cVariation0);
                    [$property1, $propertyValue1] = \explode(':', $cVariation1);

                    $variation0 = Product::findVariation(
                        $tmpProduct->Variationen,
                        $property0,
                        $propertyValue0
                    );
                    $variation1 = Product::findVariation(
                        $tmpProduct->Variationen,
                        $property1,
                        $propertyValue1
                    );

                    $additionalProduct->fAnzahl         += $product['fAnzahl'];
                    $additionalProduct->fWarenwertNetto += $product['fAnzahl'] *
                        ($tmpProduct->Preise->fVKNetto + $variation0->fAufpreisNetto + $variation1->fAufpreisNetto);
                    $additionalProduct->fGewicht        += $product['fAnzahl'] *
                        ($tmpProduct->fGewicht + $variation0->fGewichtDiff + $variation1->fGewichtDiff);
                }
                if (\mb_strlen($shippingClasses) > 0
                    && \mb_strpos($shippingClasses, $tmpProduct->kVersandklasse) === false
                ) {
                    $shippingClasses = '-' . $tmpProduct->kVersandklasse;
                } elseif (\mb_strlen($shippingClasses) === 0) {
                    $shippingClasses = $tmpProduct->kVersandklasse;
                }
            } elseif ($tmpProduct->nIstVater > 0) { // Variationskombination (Vater)
                $child = new Artikel();
                if (\mb_strpos($product['cInputData'], '_') === 0) {
                    // 1D
                    $cVariation0                  = \mb_substr($product['cInputData'], 1);
                    [$property0, $propertyValue0] = \explode(':', $cVariation0);
                    $childProductID               = Product::getChildProductIDByAttribute(
                        $tmpProduct->kArtikel,
                        $property0,
                        $propertyValue0
                    );
                    $child->fuelleArtikel($childProductID, $defaultOptions);
                    // Summen pro Steuerklasse summieren
                    if (!\array_key_exists($child->kSteuerklasse, $perTaxClass)) {
                        $perTaxClass[$child->kSteuerklasse] = 0;
                    }
                    $perTaxClass[$child->kSteuerklasse] += $child->Preise->fVKNetto * $product['fAnzahl'];

                    $sum = self::gibHinzukommendeArtikelAbhaengigeVersandkosten(
                        $child,
                        $iso,
                        $product['fAnzahl']
                    );
                    if ($sum !== false) {
                        $additionalShippingFees += $sum;
                        continue;
                    }

                    $additionalProduct->fAnzahl         += $product['fAnzahl'];
                    $additionalProduct->fWarenwertNetto += $product['fAnzahl'] * $child->Preise->fVKNetto;
                    $additionalProduct->fGewicht        += $product['fAnzahl'] * $child->fGewicht;
                } else {
                    // 2D
                    [$cVariation0, $cVariation1]  = \explode('_', $product['cInputData']);
                    [$property0, $propertyValue0] = \explode(':', $cVariation0);
                    [$property1, $propertyValue1] = \explode(':', $cVariation1);

                    $childProductID = Product::getChildProductIDByAttribute(
                        $tmpProduct->kArtikel,
                        $property0,
                        $propertyValue0,
                        $property1,
                        $propertyValue1
                    );
                    $child->fuelleArtikel($childProductID, $defaultOptions);
                    // Summen pro Steuerklasse summieren
                    if (!\array_key_exists($child->kSteuerklasse, $perTaxClass)) {
                        $perTaxClass[$child->kSteuerklasse] = 0;
                    }

                    $perTaxClass[$child->kSteuerklasse] += $child->Preise->fVKNetto * $product['fAnzahl'];

                    $sum = self::gibHinzukommendeArtikelAbhaengigeVersandkosten(
                        $child,
                        $iso,
                        $product['fAnzahl']
                    );
                    if ($sum !== false) {
                        $additionalShippingFees += $sum;
                        continue;
                    }

                    $additionalProduct->fAnzahl         += $product['fAnzahl'];
                    $additionalProduct->fWarenwertNetto += $product['fAnzahl'] * $child->Preise->fVKNetto;
                    $additionalProduct->fGewicht        += $product['fAnzahl'] * $child->fGewicht;
                }
                if (\mb_strlen($shippingClasses) > 0
                    && \mb_strpos($shippingClasses, $child->kVersandklasse) === false
                ) {
                    $shippingClasses = '-' . $child->kVersandklasse;
                } elseif (\mb_strlen($shippingClasses) === 0) {
                    $shippingClasses = $child->kVersandklasse;
                }
            }
        }

        if (GeneralObject::hasCount('PositionenArr', $cart)) {
            // Wenn etwas im Warenkorb ist, dann Vesandart vom Warenkorb rausfinden
            $currentShippingMethod = self::getFavourableShippingMethod(
                $iso,
                $shippingClasses,
                $cgroupID,
                null
            );
            $depending             = self::gibArtikelabhaengigeVersandkostenImWK(
                $iso,
                $cart->PositionenArr
            );

            $sum = 0;
            foreach ($depending as $costs) {
                $sum += $costs->fKosten;
            }

            $currentShippingMethod->fEndpreis += $sum;
            $shippingMethod                    = self::getFavourableShippingMethod(
                $iso,
                $shippingClasses,
                $cgroupID,
                $additionalProduct
            );
            $shippingMethod->fEndpreis        += ($sum + $additionalShippingFees);
        } else {
            $currentShippingMethod            = new stdClass();
            $shippingMethod                   = new stdClass();
            $currentShippingMethod->fEndpreis = 0;
            $shippingMethod->fEndpreis        = $additionalShippingFees;
        }

        if (\abs($shippingMethod->fEndpreis - $currentShippingMethod->fEndpreis) > 0.01) {
            // Versand mit neuen Artikeln > als Versand ohne Steuerklasse bestimmen
            foreach ($cart->PositionenArr as $item) {
                if ((int)$item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
                    //Summen pro Steuerklasse summieren
                    if (!\array_key_exists($item->Artikel->kSteuerklasse, $perTaxClass)) {
                        $perTaxClass[$item->Artikel->kSteuerklasse] = 0;
                    }
                    $perTaxClass[$item->Artikel->kSteuerklasse] += $item->Artikel->Preise->fVKNetto * $item->nAnzahl;
                }
            }

            if ($conf['kaufabwicklung']['bestellvorgang_versand_steuersatz'] === 'US') {
                $maxSum = 0;
                foreach ($perTaxClass as $j => $fWarensummeProSteuerklasse) {
                    if ($fWarensummeProSteuerklasse > $maxSum) {
                        $maxSum     = $fWarensummeProSteuerklasse;
                        $taxClassID = $j;
                    }
                }
            } else {
                $maxTaxRate = 0;
                foreach ($perTaxClass as $j => $fWarensummeProSteuerklasse) {
                    if (Tax::getSalesTax($j) > $maxTaxRate) {
                        $maxTaxRate = Tax::getSalesTax($j);
                        $taxClassID = $j;
                    }
                }
            }

            return \sprintf(
                Shop::Lang()->get('productExtraShippingNotice'),
                Preise::getLocalizedPriceString(
                    Tax::getGross($shippingMethod->fEndpreis, Tax::getSalesTax($taxClassID), 4)
                )
            );
        }

        return Shop::Lang()->get('productNoExtraShippingNotice');
    }

    /**
     * @param string         $deliveryCountry
     * @param string         $shippingClasses
     * @param int            $customerGroupID
     * @param Artikel|object $product
     * @param bool           $checkProductDepedency
     * @return mixed
     * @former gibGuenstigsteVersandart()
     */
    public static function getFavourableShippingMethod(
        $deliveryCountry,
        $shippingClasses,
        $customerGroupID,
        $product,
        $checkProductDepedency = true
    ) {
        $favourableIDX   = 0;
        $minVersand      = 10000;
        $depOnly         = ($checkProductDepedency && self::normalerArtikelversand($deliveryCountry) === false)
            ? 'Y'
            : 'N';
        $shippingMethods = Shop::Container()->getDB()->queryPrepared(
            "SELECT *
            FROM tversandart
            WHERE cIgnoreShippingProposal != 'Y'
                AND cNurAbhaengigeVersandart = :depOnly
                AND cLaender LIKE :iso
                AND (cVersandklassen = '-1'
                    OR cVersandklassen RLIKE :sClasses)
                AND (cKundengruppen = '-1'
                    OR FIND_IN_SET(:cGroupID, REPLACE(cKundengruppen, ';', ',')) > 0)
            ORDER BY nSort",
            [
                'depOnly'  => $depOnly,
                'iso'      => '%' . $deliveryCountry . '%',
                'cGroupID' => $customerGroupID,
                'sClasses' => '^([0-9 -]* )?' . $shippingClasses . ' '
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($shippingMethods as $i => $shippingMethod) {
            $shippingMethod->fEndpreis = self::calculateShippingFees($shippingMethod, $deliveryCountry, $product);
            if ($shippingMethod->fEndpreis === -1) {
                unset($shippingMethods[$i]);
                continue;
            }
            if ($shippingMethod->fEndpreis < $minVersand) {
                $minVersand    = $shippingMethod->fEndpreis;
                $favourableIDX = $i;
            }
        }

        return $shippingMethods[$favourableIDX];
    }

    /**
     * Prueft, ob es artikelabhaengige Versandkosten gibt und falls ja,
     * wird die hinzukommende Versandsumme fuer den Artikel
     * der hinzugefuegt werden soll errechnet und zurueckgegeben.
     *
     * @param Artikel $product
     * @param string  $iso
     * @param float   $productAmount
     * @return bool|stdClass
     */
    public static function gibHinzukommendeArtikelAbhaengigeVersandkosten($product, $iso, $productAmount)
    {
        $product->kArtikel = (int)$product->kArtikel;
        $dep               = self::pruefeArtikelabhaengigeVersandkosten($product);
        if ($dep === 1) {
            return self::gibArtikelabhaengigeVersandkosten($iso, $product, $productAmount, false);
        }
        if ($dep === 2) {
            // Gib alle Artikel im Warenkorb, die Artikel abhaengige Versandkosten beinhalten
            $depending = self::gibArtikelabhaengigeVersandkostenImWK(
                $iso,
                Frontend::getCart()->PositionenArr,
                false
            );

            if (\count($depending) > 0) {
                $amount = $productAmount;
                $total  = 0;
                foreach ($depending as $shipping) {
                    $shipping->kArtikel = (int)$shipping->kArtikel;
                    // Wenn es bereits den hinzukommenden Artikel im Warenkorb gibt
                    // zaehle die Anzahl vom Warenkorb hinzu und gib die Kosten fuer den Artikel im Warenkorb
                    if ($shipping->kArtikel === $product->kArtikel) {
                        $amount += $shipping->nAnzahl;
                        $total   = $shipping->fKosten;
                        break;
                    }
                }

                return self::gibArtikelabhaengigeVersandkosten($iso, $product, $amount, false) - $total;
            }
        }

        return false;
    }

    /**
     * @param Artikel $product
     * @return int
     */
    public static function pruefeArtikelabhaengigeVersandkosten(Artikel $product): int
    {
        $hookReturn = false;
        \executeHook(\HOOK_TOOLS_GLOBAL_PRUEFEARTIKELABHAENGIGEVERSANDKOSTEN, [
            'oArtikel'    => &$product,
            'bHookReturn' => &$hookReturn
        ]);

        if ($hookReturn) {
            return -1;
        }
        if ($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN]) {
            // Artikelabhaengige Versandkosten
            return 1;
        }
        if ($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]) {
            // Artikelabhaengige Versandkosten gestaffelt
            return 2;
        }

        return -1;  // Keine artikelabhaengigen Versandkosten
    }

    /**
     * @param string    $country
     * @param Artikel   $product
     * @param int|float $amount
     * @param bool      $checkDeliveryAddress
     * @return bool|stdClass
     */
    public static function gibArtikelabhaengigeVersandkosten(
        $country,
        Artikel $product,
        $amount,
        bool $checkDeliveryAddress = true
    ) {
        $taxRate    = null;
        $hookReturn = false;
        \executeHook(\HOOK_TOOLS_GLOBAL_GIBARTIKELABHAENGIGEVERSANDKOSTEN, [
            'oArtikel'    => &$product,
            'cLand'       => &$country,
            'nAnzahl'     => &$amount,
            'bHookReturn' => &$hookReturn
        ]);

        if ($hookReturn) {
            return false;
        }
        $netPricesActive = Frontend::getCustomerGroup()->isMerchant();
        // Steuersatz nur benötigt, wenn Nettokunde
        if ($netPricesActive === true) {
            $taxRate = Shop::Container()->getDB()->select(
                'tsteuersatz',
                'kSteuerklasse',
                Frontend::getCart()->gibVersandkostenSteuerklasse()
            )->fSteuersatz;
        }
        // gestaffelte
        if (!empty($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT])) {
            $shippingData = \array_filter(\explode(
                ';',
                $product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]
            ));
            foreach ($shippingData as $shipping) {
                // DE 1-45,00:2-60,00:3-80;AT 1-90,00:2-120,00:3-150,00
                [$countries, $costs] = \explode(' ', $shipping);
                if ($countries && ($country === $countries || $checkDeliveryAddress === false)) {
                    foreach (\explode(':', $costs) as $staffel) {
                        [$limit, $price] = \explode('-', $staffel);
                        $price           = (float)\str_replace(',', '.', $price);
                        if ($price >= 0 && $limit > 0 && $amount <= $limit) {
                            $item        = new stdClass();
                            $item->cName = [];
                            foreach ($_SESSION['Sprachen'] as $language) {
                                $item->cName[$language->cISO] = Shop::Lang()->get('shippingFor', 'checkout') .
                                    ' ' . $product->cName . ' (' . $countries . ')';
                            }
                            $item->fKosten = $price;
                            if ($netPricesActive === true) {
                                $item->cPreisLocalized = Preise::getLocalizedPriceString(
                                    Tax::getNet((float)$item->fKosten, $taxRate)
                                ) . ' ' . Shop::Lang()->get('plus', 'productDetails') . ' ' .
                                Shop::Lang()->get('vat', 'productDetails');
                            } else {
                                $item->cPreisLocalized = Preise::getLocalizedPriceString($item->fKosten);
                            }

                            return $item;
                        }
                    }
                }
            }
        }
        // flache
        if (!empty($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN])) {
            $shippingData = \array_filter(\explode(';', $product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN]));
            foreach ($shippingData as $shipping) {
                [$countries, $fKosten] = \explode(' ', $shipping);
                if ($countries && ($country === $countries || $checkDeliveryAddress === false)) {
                    $item = new stdClass();
                    //posname lokalisiert ablegen
                    $item->cName = [];
                    foreach ($_SESSION['Sprachen'] as $language) {
                        $item->cName[$language->cISO] = Shop::Lang()->get('shippingFor', 'checkout') . ' ' .
                            $product->cName . ' (' . $countries . ')';
                    }
                    $item->fKosten = (float)\str_replace(',', '.', $fKosten) * $amount;
                    if ($netPricesActive === true) {
                        $item->cPreisLocalized = Preise::getLocalizedPriceString(Tax::getNet(
                            (float)$item->fKosten,
                            $taxRate
                        )) . ' ' . Shop::Lang()->get('plus', 'productDetails') . ' ' .
                        Shop::Lang()->get('vat', 'productDetails');
                    } else {
                        $item->cPreisLocalized = Preise::getLocalizedPriceString($item->fKosten);
                    }

                    return $item;
                }
            }
        }

        return false;
    }

    /**
     * @param string $country
     * @param array  $items
     * @param bool   $checkDelivery
     * @return array
     */
    public static function gibArtikelabhaengigeVersandkostenImWK($country, $items, $checkDelivery = true): array
    {
        $shippingItems = [];
        if (!\is_array($items)) {
            return $shippingItems;
        }
        $items = \array_filter($items, static function ($item) {
            return (int)$item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL && \is_object($item->Artikel);
        });
        foreach ($items as $item) {
            $shippingItem = self::gibArtikelabhaengigeVersandkosten(
                $country,
                $item->Artikel,
                $item->nAnzahl,
                $checkDelivery
            );
            if (!empty($shippingItem->cName)) {
                $shippingItem->kArtikel = (int)$item->Artikel->kArtikel;
                $shippingItems[]        = $shippingItem;
            }
        }

        return $shippingItems;
    }

    /**
     * @param Cart $cart
     * @return string
     */
    public static function getShippingClasses(Cart $cart): string
    {
        $classes = [];
        foreach ($cart->PositionenArr as $item) {
            $item->kVersandklasse = (int)$item->kVersandklasse;
            if ((int)$item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                && $item->kVersandklasse > 0
                && !\in_array($item->kVersandklasse, $classes, true)
            ) {
                $classes[] = $item->kVersandklasse;
            }
        }
        \sort($classes);

        return \implode('-', $classes);
    }

    /**
     * @param Versandart|object $shippingMethod
     * @param string            $iso
     * @param string            $zip
     * @return stdClass|null
     * @former gibVersandZuschlag()
     */
    public static function getAdditionalFees($shippingMethod, $iso, $zip): ?stdClass
    {
        $db   = Shop::Container()->getDB();
        $fees = $db->selectAll(
            'tversandzuschlag',
            ['kVersandart', 'cISO'],
            [(int)$shippingMethod->kVersandart, $iso]
        );
        foreach ($fees as $fee) {
            $zipData = $db->queryPrepared(
                'SELECT * FROM tversandzuschlagplz
                    WHERE ((cPLZAb <= :plz
                        AND cPLZBis >= :plz)
                        OR cPLZ = :plz)
                        AND kVersandzuschlag = :sid',
                ['plz' => $zip, 'sid' => (int)$fee->kVersandzuschlag],
                ReturnType::SINGLE_OBJECT
            );
            if (isset($zipData->kVersandzuschlagPlz) && $zipData->kVersandzuschlagPlz > 0) {
                $fee->angezeigterName = [];
                foreach (Frontend::getLanguages() as $Sprache) {
                    $localized = $db->select(
                        'tversandzuschlagsprache',
                        'kVersandzuschlag',
                        (int)$fee->kVersandzuschlag,
                        'cISOSprache',
                        $Sprache->cISO
                    );

                    $fee->angezeigterName[$Sprache->cISO] = $localized->cName;
                }
                $fee->cPreisLocalized = Preise::getLocalizedPriceString($fee->fZuschlag);

                return $fee;
            }
        }

        return null;
    }

    /**
     * @todo Hier gilt noch zu beachten, dass fWarenwertNetto vom Zusatzartikel
     *       darf kein Netto sein, sondern der Preis muss in Brutto angegeben werden.
     * @param Versandart|object $shippingMethod
     * @param String            $iso
     * @param Artikel|stdClass  $additionalProduct
     * @param Artikel|null      $product
     * @return int|string
     * @former berechneVersandpreis()
     */
    public static function calculateShippingFees($shippingMethod, $iso, $additionalProduct, $product = null)
    {
        $db                            = Shop::Container()->getDB();
        $excludeShippingCostAttributes = self::normalerArtikelversand($iso) === true;
        if (!isset($additionalProduct->fAnzahl)) {
            if ($additionalProduct === null) {
                $additionalProduct = new stdClass();
            }
            $additionalProduct->fAnzahl         = 0;
            $additionalProduct->fWarenwertNetto = 0;
            $additionalProduct->fGewicht        = 0;
        }
        $calculation = $db->select(
            'tversandberechnung',
            'kVersandberechnung',
            $shippingMethod->kVersandberechnung
        );
        $price       = 0;
        switch ($calculation->cModulId) {
            case 'vm_versandkosten_pauschale_jtl':
                $price = $shippingMethod->fPreis;
                break;

            case 'vm_versandberechnung_gewicht_jtl':
                $totalWeight  = $product->fGewicht
                    ?? Frontend::getCart()->getWeight($excludeShippingCostAttributes, $iso);
                $totalWeight += $additionalProduct->fGewicht;
                $shipping     = $db->queryPrepared(
                    'SELECT *
                        FROM tversandartstaffel
                        WHERE kVersandart = :sid
                            AND fBis >= :wght
                        ORDER BY fBis ASC',
                    ['sid' => (int)$shippingMethod->kVersandart, 'wght' => $totalWeight],
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($shipping->kVersandartStaffel)) {
                    $price = $shipping->fPreis;
                } else {
                    return -1;
                }
                break;

            case 'vm_versandberechnung_warenwert_jtl':
                $total    = $product
                    ? $product->Preise->fVKNetto
                    : Frontend::getCart()->gibGesamtsummeWarenExt(
                        [\C_WARENKORBPOS_TYP_ARTIKEL],
                        true,
                        $excludeShippingCostAttributes,
                        $iso
                    );
                $total   += $additionalProduct->fWarenwertNetto;
                $shipping = $db->queryPrepared(
                    'SELECT *
                        FROM tversandartstaffel
                        WHERE kVersandart = :sid
                            AND fBis >= :val
                        ORDER BY fBis ASC',
                    ['sid' => (int)$shippingMethod->kVersandart, 'val' => $total],
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($shipping->kVersandartStaffel)) {
                    $price = $shipping->fPreis;
                } else {
                    return -1;
                }
                break;

            case 'vm_versandberechnung_artikelanzahl_jtl':
                $productCount = 1;
                if (!$product) {
                    $productCount = isset($_SESSION['Warenkorb'])
                        ? Frontend::getCart()->gibAnzahlArtikelExt(
                            [\C_WARENKORBPOS_TYP_ARTIKEL],
                            $excludeShippingCostAttributes,
                            $iso
                        )
                        : 0;
                }
                $productCount += $additionalProduct->fAnzahl;
                $shipping      = $db->queryPrepared(
                    'SELECT *
                        FROM tversandartstaffel
                        WHERE kVersandart = :sid
                            AND fBis >= :cnt
                        ORDER BY fBis ASC',
                    ['sid' => (int)$shippingMethod->kVersandart, 'cnt' => $productCount],
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($shipping->kVersandartStaffel)) {
                    $price = $shipping->fPreis;
                } else {
                    return -1;
                }
                break;

            default:
                // bearbeite fremdmodule
                break;
        }
        \executeHook(\HOOK_CALCULATESHIPPINGFEES, [
            'price'             => &$price,
            'shippingMethod'    => $shippingMethod,
            'iso'               => $iso,
            'additionalProduct' => $additionalProduct,
            'product'           => $product,
        ]);
        if ($shippingMethod->cNurAbhaengigeVersandart === 'Y'
            && (!empty($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN])
                || !empty($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]))
        ) {
            $productSpecific = self::gibArtikelabhaengigeVersandkosten($iso, $product, 1);
            $price          += $productSpecific->fKosten ?? 0;
        }
        if ($price >= $shippingMethod->fDeckelung && $shippingMethod->fDeckelung > 0) {
            $price = $shippingMethod->fDeckelung;
        }
        if (isset($shippingMethod->Zuschlag->fZuschlag) && $shippingMethod->Zuschlag->fZuschlag != 0) {
            $price += $shippingMethod->Zuschlag->fZuschlag;
        }
        $productPrice         = 0;
        $totalForShippingFree = 0;
        if ($shippingMethod->eSteuer === 'netto') {
            if ($product) {
                $productPrice = $product->Preise->fVKNetto;
            }
            if (isset($_SESSION['Warenkorb'])) {
                $totalForShippingFree = Tax::getNet(
                    Frontend::getCart()->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true, true, $iso),
                    Tax::getSalesTax(Frontend::getCart()->gibVersandkostenSteuerklasse())
                );
            }
        } elseif ($shippingMethod->eSteuer === 'brutto') {
            if ($product) {
                $productPrice = Tax::getGross(
                    $product->Preise->fVKNetto,
                    Tax::getSalesTax($product->kSteuerklasse)
                );
            }
            if (isset($_SESSION['Warenkorb'])) {
                $totalForShippingFree = Frontend::getCart()->gibGesamtsummeWarenExt(
                    [\C_WARENKORBPOS_TYP_ARTIKEL],
                    true,
                    true,
                    $iso
                );
            }
        }

        if ($shippingMethod->fVersandkostenfreiAbX > 0
            && (($product && $productPrice >= $shippingMethod->fVersandkostenfreiAbX)
                || ($totalForShippingFree >= $shippingMethod->fVersandkostenfreiAbX))
        ) {
            $price = 0;
        }
        \executeHook(\HOOK_TOOLSGLOBAL_INC_BERECHNEVERSANDPREIS, [
            'fPreis'         => &$price,
            'versandart'     => $shippingMethod,
            'cISO'           => $iso,
            'oZusatzArtikel' => $additionalProduct,
            'Artikel'        => $product,
        ]);

        return $price;
    }

    /**
     * calculate shipping costs for exports
     *
     * @param string  $iso
     * @param Artikel $product
     * @param int     $allowCash
     * @param int     $customerGroupID
     * @return int|float
     * @former gibGuenstigsteVersandkosten()
     */
    public static function getLowestShippingFees($iso, $product, $allowCash, $customerGroupID)
    {
        $dep = '';
        $fee = 99999;
        $db  = Shop::Container()->getDB();
        if (empty($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN])
            && empty($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT])
        ) {
            $dep = " AND cNurAbhaengigeVersandart = 'N'";
        }
        $methods = $db->queryPrepared(
            "SELECT *
                FROM tversandart
                WHERE cIgnoreShippingProposal != 'Y'
                    AND cLaender LIKE :iso
                    AND (cVersandklassen = '-1'
                        OR cVersandklassen RLIKE :scls)
                    AND (cKundengruppen = '-1'
                        OR FIND_IN_SET(:cgid, REPLACE(cKundengruppen, ';', ',')) > 0)" . $dep,
            [
                'iso'  => '%' . $iso . '%',
                'scls' => '^([0-9 -]* )?' . $product->kVersandklasse,
                'cgid' => $customerGroupID
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($methods as $method) {
            if (!$allowCash) {
                $cash = $db->select(
                    'tversandartzahlungsart',
                    'kZahlungsart',
                    6,
                    'kVersandart',
                    (int)$method->kVersandart
                );
                if ($cash !== null && isset($cash->kVersandartZahlungsart) && $cash->kVersandartZahlungsart > 0) {
                    continue;
                }
            }
            $vp = self::calculateShippingFees($method, $iso, null, $product);
            if ($vp !== -1 && $vp < $fee) {
                $fee = $vp;
            }
            if ($vp === 0) {
                break;
            }
        }

        return $fee === 99999 ? -1 : $fee;
    }

    /**
     * @param int $minDeliveryDays
     * @param int $maxDeliveryDays
     * @return string
     */
    public static function getDeliverytimeEstimationText(int $minDeliveryDays, int $maxDeliveryDays): string
    {
        $deliveryText = $minDeliveryDays === $maxDeliveryDays
            ? \str_replace(
                '#DELIVERYDAYS#',
                $minDeliveryDays,
                Shop::Lang()->get('deliverytimeEstimationSimple')
            )
            : \str_replace(
                ['#MINDELIVERYDAYS#', '#MAXDELIVERYDAYS#'],
                [$minDeliveryDays, $maxDeliveryDays],
                Shop::Lang()->get('deliverytimeEstimation')
            );

        \executeHook(\HOOK_GET_DELIVERY_TIME_ESTIMATION_TEXT, [
            'min'  => $minDeliveryDays,
            'max'  => $maxDeliveryDays,
            'text' => &$deliveryText
        ]);

        return $deliveryText;
    }

    /**
     * @param Versandart|object $method
     * @param float             $cartSum
     * @return string
     * @former baueVersandkostenfreiString()
     */
    public static function getShippingFreeString($method, $cartSum): string
    {
        if (isset($_SESSION['oVersandfreiKupon'])) {
            return '';
        }
        if (!\is_object($method)
            || (float)$method->fVersandkostenfreiAbX <= 0
            || !isset($_SESSION['Warenkorb'], $_SESSION['Steuerland'])
        ) {
            return '';
        }

        if (isset($method->cNameLocalized)) {
            $name = $method->cNameLocalized;
        } else {
            $localized = Shop::Container()->getDB()->select(
                'tversandartsprache',
                'kVersandart',
                $method->kVersandart,
                'cISOSprache',
                Shop::getLanguageCode()
            );
            $name      = !empty($localized->cName)
                ? $localized->cName
                : $method->cName;
        }
        $shippingFreeDifference = self::getShippingFreeDifference($method, $cartSum);
        if ($shippingFreeDifference <= 0) {
            return \sprintf(
                Shop::Lang()->get('noShippingCostsReached', 'basket'),
                $name,
                self::getShippingFreeCountriesString($method)
            );
        }

        return \sprintf(
            Shop::Lang()->get('noShippingCostsAt', 'basket'),
            Preise::getLocalizedPriceString($shippingFreeDifference),
            $name,
            self::getShippingFreeCountriesString($method)
        );
    }

    /**
     * @param $method
     * @param $cartSum
     * @return float
     */
    public static function getShippingFreeDifference($method, $cartSum): float
    {
        $db                     = Shop::Container()->getDB();
        $shippingFreeDifference = (float)$method->fVersandkostenfreiAbX - (float)$cartSum;
        // check if vkfreiabx is calculated net or gross
        if ($method->eSteuer === 'netto') {
            // calculate net with default tax class
            $defaultTaxClass = $db->select('tsteuerklasse', 'cStandard', 'Y');
            if ($defaultTaxClass !== null && isset($defaultTaxClass->kSteuerklasse)) {
                $taxClasss  = (int)$defaultTaxClass->kSteuerklasse;
                $defaultTax = $db->select('tsteuersatz', 'kSteuerklasse', $taxClasss);
                if ($defaultTax !== null) {
                    $defaultTaxValue        = $defaultTax->fSteuersatz;
                    $shippingFreeDifference = (float)$method->fVersandkostenfreiAbX -
                        Tax::getNet((float)$cartSum, $defaultTaxValue);
                }
            }
        }

        return $shippingFreeDifference;
    }

    /**
     * @param Versandart|stdClass $shippingMethod
     * @return string
     * @former baueVersandkostenfreiLaenderString()
     */
    public static function getShippingFreeCountriesString($shippingMethod): string
    {
        if (!\is_object($shippingMethod) || (float)$shippingMethod->fVersandkostenfreiAbX <= 0) {
            return '';
        }
        $cacheID = 'bvkfls_' . $shippingMethod->fVersandkostenfreiAbX . \mb_strlen($shippingMethod->cLaender) . '_' .
            Shop::getLanguageID();
        if (($shippingFreeCountries = Shop::Container()->getCache()->get($cacheID)) === false) {
            $shippingFreeCountries = \implode(', ', \array_map(static function (Country $e) {
                return $e->getName();
            }, Shop::Container()->getCountryService()->getFilteredCountryList(
                \array_filter(\explode(' ', $shippingMethod->cLaender))
            )->toArray()));

            Shop::Container()->getCache()->set($cacheID, $shippingFreeCountries, [\CACHING_GROUP_OPTION]);
        }

        return $shippingFreeCountries;
    }

    /**
     * @param int    $customerGroupID
     * @param string $country
     * @return int|mixed
     * @former gibVersandkostenfreiAb()
     */
    public static function getFreeShippingMinimum(int $customerGroupID, $country = '')
    {
        $shippingClasses = self::getShippingClasses(Frontend::getCart());
        $defaultShipping = self::normalerArtikelversand($country);
        $cacheID         = 'vkfrei_' . $customerGroupID . '_' .
            $country . '_' . $shippingClasses . '_' . Shop::getLanguageCode();
        if (($shippingMethod = Shop::Container()->getCache()->get($cacheID)) === false) {
            if (\mb_strlen($country) > 0) {
                $customerSQL = " AND cLaender LIKE '%" . Text::filterXSS($country) . "%'";
            } else {
                $iso         = Shop::Container()->getDB()->query(
                    'SELECT cISO
                        FROM tfirma
                        JOIN tland
                            ON tfirma.cLand = tland.cDeutsch
                        LIMIT 0,1',
                    ReturnType::SINGLE_OBJECT
                );
                $customerSQL = isset($iso->cISO)
                    ? " AND cLaender LIKE '%" . $iso->cISO . "%'"
                    : '';
            }
            $productSpecificCondition = empty($defaultShipping) ? '' : " AND cNurAbhaengigeVersandart = 'N' ";
            $shippingMethod           = Shop::Container()->getDB()->queryPrepared(
                "SELECT tversandart.*, tversandartsprache.cName AS cNameLocalized
                    FROM tversandart
                    LEFT JOIN tversandartsprache
                        ON tversandart.kVersandart = tversandartsprache.kVersandart
                        AND tversandartsprache.cISOSprache = :cLangID
                    WHERE fVersandkostenfreiAbX > 0
                        AND (cVersandklassen = '-1'
                            OR cVersandklassen RLIKE :cShippingClass)
                        AND (cKundengruppen = '-1'
                            OR FIND_IN_SET(:cGroupID, REPLACE(cKundengruppen, ';', ',')) > 0)
                        " . $customerSQL . $productSpecificCondition . '
                    ORDER BY tversandart.fVersandkostenfreiAbX, tversandart.nSort ASC
                    LIMIT 1',
                [
                    'cLangID'        => Shop::getLanguageCode(),
                    'cShippingClass' => '^([0-9 -]* )?' . $shippingClasses . ' ',
                    'cGroupID'       => $customerGroupID
                ],
                ReturnType::SINGLE_OBJECT
            );
            Shop::Container()->getCache()->set($cacheID, $shippingMethod, [\CACHING_GROUP_OPTION]);
        }

        return !empty($shippingMethod) && $shippingMethod->fVersandkostenfreiAbX > 0
            ? $shippingMethod
            : 0;
    }

    /**
     * @param int   $customerGroupID
     * @param bool  $ignoreConf
     * @param bool  $force
     * @param array $filterISO
     * @return array
     * @former gibBelieferbareLaender()
     * @since 5.0.0
     */
    public static function getPossibleShippingCountries(
        int $customerGroupID = 0,
        bool $ignoreConf = false,
        bool $force = false,
        array $filterISO = []
    ): array {
        if (empty($customerGroupID)) {
            $customerGroupID = CustomerGroup::getDefaultGroupID();
        }
        $conf          = Shop::getSettings([\CONF_KUNDEN]);
        $countryHelper = Shop::Container()->getCountryService();

        if (!$force && ($conf['kunden']['kundenregistrierung_nur_lieferlaender'] === 'Y' || $ignoreConf)) {
            $countryISOFilter = Shop::Container()->getDB()->query(
                "SELECT DISTINCT tland.cISO
                    FROM tland
                    INNER JOIN tversandart ON FIND_IN_SET(tland.cISO, REPLACE(tversandart.cLaender, ' ', ','))
                    WHERE (tversandart.cKundengruppen = '-1'
                        OR FIND_IN_SET('" . $customerGroupID . "', REPLACE(cKundengruppen, ';', ',')) > 0)
                        " . (\count($filterISO) > 0
                    ? "AND tland.cISO IN ('" . \implode("','", $filterISO) . "')"
                    : ''),
                ReturnType::ARRAY_OF_OBJECTS
            );
            $countries        = $countryHelper->getFilteredCountryList(
                map($countryISOFilter, static function ($country) {
                    return $country->cISO;
                })
            )->toArray();
        } else {
            $countries = $countryHelper->getFilteredCountryList($filterISO, true)->toArray();
        }
        \executeHook(\HOOK_TOOLSGLOBAL_INC_GIBBELIEFERBARELAENDER, [
            'oLaender_arr' => &$countries
        ]);

        return $countries;
    }

    /**
     * @param int $customerGroupID
     * @return array
     * @former gibMoeglicheVerpackungen()
     * @since 5.0.0
     */
    public static function getPossiblePackagings(int $customerGroupID): array
    {
        $cartSum      = Frontend::getCart()->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true);
        $packagings   = Shop::Container()->getDB()->queryPrepared(
            "SELECT * FROM tverpackung
                JOIN tverpackungsprache
                    ON tverpackung.kVerpackung = tverpackungsprache.kVerpackung
                WHERE tverpackungsprache.cISOSprache = :lcode
                AND (tverpackung.cKundengruppe = '-1'
                    OR FIND_IN_SET(:cid, REPLACE(tverpackung.cKundengruppe, ';', ',')) > 0)
                AND :csum >= tverpackung.fMindestbestellwert
                AND tverpackung.nAktiv = 1
                ORDER BY tverpackung.kVerpackung",
            [
                'lcode' => Shop::getLanguageCode(),
                'cid'   => $customerGroupID,
                'csum'  => $cartSum
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $currencyCode = Frontend::getCurrency()->getID();
        foreach ($packagings as $packaging) {
            $packaging->nKostenfrei      = ($cartSum >= $packaging->fKostenfrei
                && $packaging->fBrutto > 0
                && $packaging->fKostenfrei != 0)
                ? 1
                : 0;
            $packaging->fBruttoLocalized = Preise::getLocalizedPriceString($packaging->fBrutto, $currencyCode);
        }

        return $packagings;
    }

    /**
     * @param object[]|null $shippingMethods
     * @param int           $paymentMethodID
     * @return object|null
     */
    public static function getFirstShippingMethod($shippingMethods = null, int $paymentMethodID = 0): ?object
    {
        $customer = Frontend::getCustomer();

        if (!\is_array($shippingMethods)) {
            $country = $_SESSION['Lieferadresse']->cLand ?? $customer->cLand;
            $zip     = $_SESSION['Lieferadresse']->cPLZ ?? $customer->cPLZ;

            $shippingMethods = self::getPossibleShippingMethods(
                $country,
                $zip,
                self::getShippingClasses(Frontend::getCart()),
                $customer->getGroupID() ?? Frontend::getCustomerGroup()->getID()
            );
        }
        if ($paymentMethodID === 0) {
            $paymentMethodID = $_SESSION['Zahlungsart']->kZahlungsart ?? 0;
        }

        if ($paymentMethodID > 0) {
            $shippingMethods = \array_filter(
                $shippingMethods,
                static function ($method) use ($paymentMethodID, $customer) {
                    $paymentMethods = self::getPaymentMethods(
                        (int)$method->kVersandart,
                        $customer->getGroupID() ?? Frontend::getCustomerGroup()->getID(),
                        $paymentMethodID
                    );

                    return \count($paymentMethods) > 0;
                }
            );
        }

        return first($shippingMethods);
    }
}
