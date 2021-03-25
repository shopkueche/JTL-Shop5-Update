<?php

namespace JTL\Helpers;

use Illuminate\Support\Collection;
use JTL\Alert\Alert;
use JTL\Campaign;
use JTL\Cart\CartHelper;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Catalog\UnitsOfMeasure;
use JTL\CheckBox;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Extensions\Config\Configurator;
use JTL\Extensions\Config\Group;
use JTL\Extensions\Config\Item;
use JTL\Language\LanguageHelper;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Optin\Optin;
use JTL\Optin\OptinAvailAgain;
use JTL\Optin\OptinRefData;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\SimpleMail;
use JTL\Smarty\JTLSmarty;
use stdClass;
use function Functional\group;

/**
 * Class Product
 * @package JTL\Helpers
 */
class Product
{
    /**
     * @param int $productID
     * @return bool
     */
    public static function isVariChild(int $productID): bool
    {
        $product = Shop::Container()->getDB()->select(
            'tartikel',
            'kArtikel',
            $productID,
            null,
            null,
            null,
            null,
            false,
            'kEigenschaftKombi'
        );

        return isset($product->kEigenschaftKombi) && (int)$product->kEigenschaftKombi > 0;
    }

    /**
     * @param int $productID
     * @return int
     */
    public static function getParent(int $productID): int
    {
        $product = Shop::Container()->getDB()->select(
            'tartikel',
            'kArtikel',
            $productID,
            null,
            null,
            null,
            null,
            false,
            'kVaterArtikel'
        );

        return (int)($product->kVaterArtikel ?? 0);
    }

    /**
     * @param int $productID
     * @return bool
     */
    public static function isVariCombiChild(int $productID): bool
    {
        return self::getParent($productID) > 0;
    }

    /**
     * Holt fuer einen kVaterArtikel + gesetzte Eigenschaften, den kArtikel vom Variationskombikind
     *
     * @param int $productID
     * @return int
     */
    public static function getArticleForParent(int $productID): int
    {
        $customerGroupID = Frontend::getCustomerGroup()->getID();
        $properties      = self::getChildPropertiesForParent($productID, $customerGroupID);
        $combinations    = [];
        $valid           = true;
        foreach ($properties as $i => $kAlleEigenschaftWerteProEigenschaft) {
            if (!self::hasSelectedVariationValue($i)) {
                $valid = false;
                break;
            }
            $combinations[$i] = self::getSelectedVariationValue($i);
        }
        if ($valid) {
            $attributes      = [];
            $attributeValues = [];
            if (\count($combinations) > 0) {
                foreach ($combinations as $i => $kVariationKombi) {
                    $attributes[]      = $i;
                    $attributeValues[] = (int)$kVariationKombi;
                }
                $product = Shop::Container()->getDB()->query(
                    'SELECT tartikel.kArtikel
                        FROM teigenschaftkombiwert
                        JOIN tartikel
                            ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                        LEFT JOIN tartikelsichtbarkeit
                            ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                        WHERE teigenschaftkombiwert.kEigenschaft IN (' . \implode(',', $attributes) . ')
                            AND teigenschaftkombiwert.kEigenschaftWert IN (' . \implode(',', $attributeValues) . ')
                            AND tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikel.kVaterArtikel = ' . $productID . '
                        GROUP BY tartikel.kArtikel
                        HAVING COUNT(*) = ' . \count($combinations),
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($product->kArtikel) && $product->kArtikel > 0) {
                    return (int)$product->kArtikel;
                }
            }
            if (!isset($_SESSION['variBoxAnzahl_arr'])) {
                \header('Location: ' . Shop::getURL() .
                    '/?a=' . $productID .
                    '&n=' . $_POST['anzahl'] .
                    '&r=' . \R_VARWAEHLEN, true, 302);
                exit();
            }
        }

        return 0;
    }

    /**
     * Holt fuer einen kVaterArtikel alle Eigenschaften und Eigenschaftswert Assoc als Array
     * z.b. $properties[kEigenschaft] = EigenschaftWert
     *
     * @former: gibAlleKindEigenschaftenZuVater()
     * @param int $productID
     * @param int $customerGroupID
     * @return array
     */
    public static function getChildPropertiesForParent(int $productID, int $customerGroupID): array
    {
        $varCombinations = self::getPossibleVariationCombinations($productID, $customerGroupID);
        $properties      = [];
        foreach ($varCombinations as $comb) {
            if (!isset($properties[$comb->kEigenschaft]) || !\is_array($properties[$comb->kEigenschaft])) {
                $properties[$comb->kEigenschaft] = [];
            }
            if (!isset($comb->kEigenschaftWert, $properties[$comb->kEigenschaft])
                || !\in_array($comb->kEigenschaftWert, $properties[$comb->kEigenschaft], true)
            ) {
                $properties[$comb->kEigenschaft][] = $comb->kEigenschaftWert;
            }
        }

        return $properties;
    }

    /**
     * @param int  $parentID
     * @param int  $customerGroupID
     * @param bool $group
     * @return array
     */
    public static function getPossibleVariationCombinations(
        int $parentID,
        int $customerGroupID = 0,
        bool $group = false
    ): array {
        if (!$customerGroupID) {
            $customerGroupID = CustomerGroup::getDefaultGroupID();
        }
        $cGroupBy = $group ? 'GROUP BY teigenschaftkombiwert.kEigenschaftWert ' : '';

        return \array_map(
            static function ($e) {
                $e->kEigenschaft      = (int)$e->kEigenschaft;
                $e->kEigenschaftKombi = (int)$e->kEigenschaftKombi;
                $e->kEigenschaftWert  = (int)$e->kEigenschaftWert;

                return $e;
            },
            Shop::Container()->getDB()->query(
                'SELECT teigenschaftkombiwert.*
                    FROM teigenschaftkombiwert
                    JOIN tartikel
                        ON tartikel.kVaterArtikel = ' . $parentID . '
                        AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL ' . $cGroupBy .
                'ORDER BY teigenschaftkombiwert.kEigenschaftWert',
                ReturnType::ARRAY_OF_OBJECTS
            )
        );
    }

    /**
     * @param int $productID
     * @param int $parentID
     * @return array
     */
    public static function getPropertiesForVarCombiArticle(int $productID, &$parentID): array
    {
        $result   = [];
        $parentID = 0;
        $db       = Shop::Container()->getDB();

        // Hole EigenschaftWerte zur gewaehlten VariationKombi
        $children = $db->queryPrepared(
            'SELECT teigenschaftkombiwert.kEigenschaftWert, teigenschaftkombiwert.kEigenschaft, tartikel.kVaterArtikel
                FROM teigenschaftkombiwert
                JOIN tartikel
                    ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                    AND tartikel.kArtikel = :productID
                LEFT JOIN tartikelsichtbarkeit
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = :customerGroup
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                ORDER BY tartikel.kArtikel',
            [
                'productID'     => $productID,
                'customerGroup' => Frontend::getCustomerGroup()->getID(),
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($children) === 0) {
            return [];
        }

        foreach ($children as $child) {
            if (!isset($result[$child->kEigenschaft])
                || !\is_array($result[$child->kEigenschaft])
            ) {
                $result[(int)$child->kEigenschaft] = (int)$child->kEigenschaftWert;
            }
        }

        $parentID = (int)$children[0]->kVaterArtikel;

        return $result;
    }

    /**
     * @former gibGewaehlteEigenschaftenZuVariKombiArtikel()
     * @param int $productID
     * @param int $getVariations
     * @return array
     */
    public static function getSelectedPropertiesForVarCombiArticle(int $productID, int $getVariations = 0): array
    {
        if ($productID <= 0) {
            return [];
        }
        $customerGroup  = Frontend::getCustomerGroup()->getID();
        $db             = Shop::Container()->getDB();
        $properties     = [];
        $propertyValues = self::getPropertiesForVarCombiArticle($productID, $parentID);
        $exists         = true;

        if (\count($propertyValues) === 0) {
            return [];
        }

        $attributes       = [];
        $attributeValues  = [];
        $langID           = Shop::getLanguageID();
        $attr             = new stdClass();
        $attr->cSELECT    = '';
        $attr->cJOIN      = '';
        $attrVal          = new stdClass();
        $attrVal->cSELECT = '';
        $attrVal->cJOIN   = '';
        foreach ($propertyValues as $i => $_u) {
            $attributes[]      = $i;
            $attributeValues[] = $propertyValues[$i];
        }
        if ($langID > 0 && !LanguageHelper::isDefaultLanguageActive()) {
            $attr->cSELECT = 'teigenschaftsprache.cName AS cName_teigenschaftsprache, ';
            $attr->cJOIN   = 'LEFT JOIN teigenschaftsprache
                                        ON teigenschaftsprache.kEigenschaft = teigenschaft.kEigenschaft
                                        AND teigenschaftsprache.kSprache = ' . $langID;

            $attrVal->cSELECT = 'teigenschaftwertsprache.cName AS cName_teigenschaftwertsprache, ';
            $attrVal->cJOIN   = 'LEFT JOIN teigenschaftwertsprache
                                    ON teigenschaftwertsprache.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                                    AND teigenschaftwertsprache.kSprache = ' . $langID;
        }

        $oEigenschaft_arr = $db->query(
            'SELECT teigenschaftwert.kEigenschaftWert, teigenschaftwert.cName, ' . $attrVal->cSELECT . '
                teigenschaftwertsichtbarkeit.kKundengruppe, teigenschaftwert.kEigenschaft, teigenschaft.cTyp, ' .
            $attr->cSELECT . ' teigenschaft.cName AS cNameEigenschaft, teigenschaft.kArtikel
                FROM teigenschaftwert
                LEFT JOIN teigenschaftwertsichtbarkeit
                    ON teigenschaftwertsichtbarkeit.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                    AND teigenschaftwertsichtbarkeit.kKundengruppe = ' . $customerGroup . '
                JOIN teigenschaft ON teigenschaft.kEigenschaft = teigenschaftwert.kEigenschaft
                LEFT JOIN teigenschaftsichtbarkeit ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                    AND teigenschaftsichtbarkeit.kKundengruppe = ' . $customerGroup . '
                ' . $attr->cJOIN . '
                ' . $attrVal->cJOIN . '
                WHERE teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                    AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                    AND teigenschaftwert.kEigenschaft IN (' . \implode(',', $attributes) . ')
                    AND teigenschaftwert.kEigenschaftWert IN (' . \implode(',', $attributeValues) . ')',
            ReturnType::ARRAY_OF_OBJECTS
        );


        $oEigenschaftTMP_arr = $db->query(
            'SELECT teigenschaft.kEigenschaft, teigenschaft.cName, teigenschaft.cTyp, 
                teigenschaft.kArtikel, 0 AS kEigenschaftWert
                FROM teigenschaft
                LEFT JOIN teigenschaftsichtbarkeit
                    ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                    AND teigenschaftsichtbarkeit.kKundengruppe = ' . $customerGroup . '
                WHERE (teigenschaft.kArtikel = ' . $parentID . '
                    OR teigenschaft.kArtikel = ' . $productID . ")
                    AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                    AND (teigenschaft.cTyp = 'FREIFELD'
                    OR teigenschaft.cTyp = 'PFLICHT-FREIFELD')",
            ReturnType::ARRAY_OF_OBJECTS
        );

        if (\is_array($oEigenschaftTMP_arr)) {
            $oEigenschaft_arr = \array_merge($oEigenschaft_arr, $oEigenschaftTMP_arr);
        }

        foreach ($oEigenschaft_arr as $oEigenschaft) {
            $oEigenschaft->kEigenschaftWert = (int)$oEigenschaft->kEigenschaftWert;
            $oEigenschaft->kEigenschaft     = (int)$oEigenschaft->kEigenschaft;
            $oEigenschaft->kArtikel         = (int)$oEigenschaft->kArtikel;
            if ($oEigenschaft->cTyp !== 'FREIFELD' && $oEigenschaft->cTyp !== 'PFLICHT-FREIFELD') {
                // Ist kEigenschaft zu eigenschaftwert vorhanden
                if (self::hasSelectedVariationValue($oEigenschaft->kEigenschaft)) {
                    $oEigenschaftWertVorhanden = $db->query(
                        'SELECT teigenschaftwert.kEigenschaftWert
                            FROM teigenschaftwert
                            LEFT JOIN teigenschaftwertsichtbarkeit
                                ON teigenschaftwertsichtbarkeit.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                                AND teigenschaftwertsichtbarkeit.kKundengruppe = ' . $customerGroup . '
                            WHERE teigenschaftwert.kEigenschaftWert = ' . $oEigenschaft->kEigenschaftWert . '
                                AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                                AND teigenschaftwert.kEigenschaft = ' . $oEigenschaft->kEigenschaft,
                        ReturnType::SINGLE_OBJECT
                    );

                    if ($oEigenschaftWertVorhanden->kEigenschaftWert) {
                        unset($propValue);
                        $propValue                   = new stdClass();
                        $propValue->kEigenschaftWert = $oEigenschaft->kEigenschaftWert;
                        $propValue->kEigenschaft     = $oEigenschaft->kEigenschaft;
                        $propValue->cTyp             = $oEigenschaft->cTyp;

                        if ($langID > 0 && !LanguageHelper::isDefaultLanguageActive()) {
                            $propValue->cEigenschaftName     = $oEigenschaft->cName_teigenschaftsprache;
                            $propValue->cEigenschaftWertName = $oEigenschaft->cName_teigenschaftwertsprache;
                        } else {
                            $propValue->cEigenschaftName     = $oEigenschaft->cNameEigenschaft;
                            $propValue->cEigenschaftWertName = $oEigenschaft->cName;
                        }
                        $properties[] = $propValue;
                    } else {
                        $exists = false;
                        break;
                    }
                } elseif (!isset($_SESSION['variBoxAnzahl_arr'])) {
                    \header('Location: ' . Shop::getURL() .
                        '/?a=' . $productID .
                        '&n=' . Request::postInt('anzahl') .
                        '&r=' . \R_VARWAEHLEN, true, 302);
                    exit();
                }
            } else {
                unset($propValue);
                if ($oEigenschaft->cTyp === 'PFLICHT-FREIFELD'
                    && self::hasSelectedVariationValue($oEigenschaft->kEigenschaft)
                    && \mb_strlen(self::getSelectedVariationValue($oEigenschaft->kEigenschaft)) === 0
                ) {
                    \header('Location: ' . Shop::getURL() .
                        '/?a=' . $productID .
                        '&n=' . Request::postInt('anzahl') .
                        '&r=' . \R_VARWAEHLEN, true, 302);
                    exit();
                }
                $propValue                = new stdClass();
                $propValue->cFreifeldWert = Text::filterXSS(
                    self::getSelectedVariationValue($oEigenschaft->kEigenschaft)
                );
                $propValue->kEigenschaft  = $oEigenschaft->kEigenschaft;
                $propValue->cTyp          = $oEigenschaft->cTyp;
                $properties[]             = $propValue;
            }
        }

        if (!$exists && !isset($_SESSION['variBoxAnzahl_arr'])) {
            \header('Location: ' . Shop::getURL() .
                '/?a=' . $productID .
                '&n=' . Request::postInt('anzahl') .
                '&r=' . \R_VARWAEHLEN, true, 301);
            exit();
        }
        if ($getVariations > 0) {
            $variations = [];
            foreach ($properties as $i => $propValue) {
                $oEigenschaftWert                   = new stdClass();
                $oEigenschaftWert->kEigenschaftWert = $propValue->kEigenschaftWert;
                $oEigenschaftWert->kEigenschaft     = $propValue->kEigenschaft;
                $oEigenschaftWert->cName            = $propValue->cEigenschaftWertName;
                if ($propValue->cTyp === 'PFLICHT-FREIFELD' || $propValue->cTyp === 'FREIFELD') {
                    $oEigenschaftWert->cFreifeldWert    = $propValue->cFreifeldWert;
                    $oEigenschaftWert->kEigenschaftWert = 0;
                }
                $variations[$i]               = new stdClass();
                $variations[$i]->kEigenschaft = $propValue->kEigenschaft;
                $variations[$i]->kArtikel     = $productID;
                $variations[$i]->cWaehlbar    = 'Y';
                $variations[$i]->cTyp         = $propValue->cTyp;
                $variations[$i]->cName        = $propValue->cEigenschaftName ?? null;
                $variations[$i]->Werte        = [];
                $variations[$i]->Werte[]      = $oEigenschaftWert;
            }

            return $variations;
        }

        return $properties;
    }

    /**
     * @param int  $productID
     * @param bool $redirect
     * @return array
     * @former gibGewaehlteEigenschaftenZuArtikel()
     * @since 5.0.0
     */
    public static function getSelectedPropertiesForArticle(int $productID, bool $redirect = true): array
    {
        $db              = Shop::Container()->getDB();
        $customerGroupID = Frontend::getCustomerGroup()->getID();
        $propData        = $db->queryPrepared(
            'SELECT teigenschaft.kEigenschaft,teigenschaft.cName,teigenschaft.cTyp
                FROM teigenschaft
                LEFT JOIN teigenschaftsichtbarkeit
                    ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                    AND teigenschaftsichtbarkeit.kKundengruppe = :cgroupid
                WHERE teigenschaft.kArtikel = :articleid
                    AND teigenschaftsichtbarkeit.kEigenschaft IS NULL',
            ['cgroupid' => $customerGroupID, 'articleid' => $productID],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $properties      = [];
        $exists          = true;
        if (!\is_array($propData) || \count($propData) === 0) {
            return [];
        }
        foreach ($propData as $prop) {
            $prop->kEigenschaft = (int)$prop->kEigenschaft;
            if ($prop->cTyp !== 'FREIFELD' && $prop->cTyp !== 'PFLICHT-FREIFELD') {
                if (self::hasSelectedVariationValue($prop->kEigenschaft)) {
                    $propExists = $db->queryPrepared(
                        'SELECT teigenschaftwert.kEigenschaftWert, teigenschaftwert.cName,
                            teigenschaftwertsichtbarkeit.kKundengruppe
                            FROM teigenschaftwert
                            LEFT JOIN teigenschaftwertsichtbarkeit
                                ON teigenschaftwertsichtbarkeit.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                                AND teigenschaftwertsichtbarkeit.kKundengruppe = :cgroupid
                            WHERE teigenschaftwert.kEigenschaftWert = :attribvalueid
                                AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                                AND teigenschaftwert.kEigenschaft = :attribid',
                        [
                            'cgroupid'      => $customerGroupID,
                            'attribvalueid' => self::getSelectedVariationValue($prop->kEigenschaft),
                            'attribid'      => $prop->kEigenschaft
                        ],
                        ReturnType::SINGLE_OBJECT
                    );

                    if (isset($propExists->kEigenschaftWert)) {
                        $val                       = new stdClass();
                        $val->kEigenschaftWert     = (int)self::getSelectedVariationValue($prop->kEigenschaft);
                        $val->kEigenschaft         = $prop->kEigenschaft;
                        $val->cEigenschaftName     = $prop->cName;
                        $val->cEigenschaftWertName = $propExists->cName;
                        $val->cTyp                 = $prop->cTyp;
                        $properties[]              = $val;
                    } else {
                        $exists = false;
                        break;
                    }
                } elseif (!isset($_SESSION['variBoxAnzahl_arr']) && $redirect) {
                    \header('Location: ' . Shop::getURL() .
                        '/?a=' . $productID .
                        '&n=' . Request::postInt('anzahl') .
                        '&r=' . \R_VARWAEHLEN, true, 302);
                    exit();
                }
            } else {
                if ($prop->cTyp === 'PFLICHT-FREIFELD'
                    && $redirect
                    && self::hasSelectedVariationValue($prop->kEigenschaft)
                    && \mb_strlen(self::getSelectedVariationValue($prop->kEigenschaft)) === 0
                ) {
                    \header('Location: ' . Shop::getURL() .
                        '/?a=' . $productID .
                        '&n=' . Request::postInt('anzahl') .
                        '&r=' . \R_VARWAEHLEN, true, 302);
                    exit();
                }
                $val                   = new stdClass();
                $val->cFreifeldWert    = $db->escape(
                    Text::filterXSS(self::getSelectedVariationValue($prop->kEigenschaft))
                );
                $val->kEigenschaft     = $prop->kEigenschaft;
                $val->kEigenschaftWert = null;
                $val->cTyp             = $prop->cTyp;
                $properties[]          = $val;
            }
        }

        if (!$exists && $redirect && !isset($_SESSION['variBoxAnzahl_arr'])) {
            \header('Location: ' . Shop::getURL() .
                '/?a=' . $productID .
                '&n=' . Request::postInt('anzahl') .
                '&r=' . \R_VARWAEHLEN, true, 302);
            exit();
        }

        return $properties;
    }

    /**
     * @former holeKinderzuVater()
     * @param int $parentID
     * @return array
     */
    public static function getChildren(int $parentID): array
    {
        return $parentID > 0
            ? Shop::Container()->getDB()->selectAll(
                'tartikel',
                'kVaterArtikel',
                $parentID,
                'kArtikel, kEigenschaftKombi'
            )
            : [];
    }

    /**
     * @former pruefeIstVaterArtikel()
     * @param int $productID
     * @return bool
     */
    public static function isParent(int $productID): bool
    {
        $data = Shop::Container()->getDB()->select(
            'tartikel',
            'kArtikel',
            $productID,
            null,
            null,
            null,
            null,
            false,
            'nIstVater'
        );

        return isset($data->nIstVater) && $data->nIstVater > 0;
    }

    /**
     * @param int  $productID
     * @param bool $info
     * @return bool|stdClass
     */
    public static function isStuecklisteKomponente(int $productID, bool $info = false)
    {
        if ($productID > 0) {
            $data = Shop::Container()->getDB()->select('tstueckliste', 'kArtikel', $productID);
            if (isset($data->kStueckliste) && $data->kStueckliste > 0) {
                return $info ? $data : true;
            }
        }

        return false;
    }

    /**
     * Fallback für alte Formular-Struktur
     *
     * alt: eigenschaftwert_{kEigenschaft}
     * neu: eigenschaftwert[{kEigenschaft}]
     *
     * @param int $groupID
     * @return string|bool
     */
    protected static function getSelectedVariationValue(int $groupID)
    {
        $idx = 'eigenschaftwert_' . $groupID;

        return $_POST[$idx] ?? $_POST['eigenschaftwert'][$groupID] ?? false;
    }

    /**
     * @param int $groupID
     * @return bool
     */
    protected static function hasSelectedVariationValue(int $groupID): bool
    {
        return self::getSelectedVariationValue($groupID) !== false;
    }

    /**
     * @param Artikel  $product
     * @param object[] $variationPicturesArr
     */
    public static function addVariationPictures(Artikel $product, $variationPicturesArr): void
    {
        if (\is_array($variationPicturesArr) && \count($variationPicturesArr) > 0) {
            $product->Bilder = \array_filter($product->Bilder, static function ($item) {
                return !(isset($item->isVariation) && $item->isVariation);
            });
            if (\count($variationPicturesArr) === 1) {
                \array_unshift($product->Bilder, $variationPicturesArr[0]);
            } else {
                $product->Bilder = \array_merge($product->Bilder, $variationPicturesArr);
            }

            $nNr = 1;
            foreach (\array_keys($product->Bilder) as $key) {
                $product->Bilder[$key]->nNr = $nNr++;
            }

            $product->cVorschaubild = $product->Bilder[0]->cURLKlein;
        }
    }

    /**
     * @param Artikel    $product
     * @param float|null $price
     * @param int|null   $amount
     * @return stdClass
     */
    public static function getBasePriceUnit(Artikel $product, $price, $amount): stdClass
    {
        $unitMappings = [
            'mg'  => 'kg',
            'g'   => 'kg',
            'mL'  => 'L',
            'cm3' => 'L',
            'cL'  => 'L',
            'dL'  => 'L',
        ];

        $result = (object)[
            'fGrundpreisMenge' => $product->fGrundpreisMenge,
            'fMassMenge'       => $product->fMassMenge * $amount,
            'fBasePreis'       => $price / $product->fVPEWert,
            'fVPEWert'         => (float)$product->fVPEWert,
            'cVPEEinheit'      => $product->cVPEEinheit,
        ];

        $gpUnit   = UnitsOfMeasure::getUnit($product->kGrundpreisEinheit);
        $massUnit = UnitsOfMeasure::getUnit($product->kMassEinheit);

        if (isset($gpUnit, $massUnit, $unitMappings[$gpUnit->cCode], $unitMappings[$massUnit->cCode])) {
            $fFactor    = UnitsOfMeasure::getConversionFaktor($unitMappings[$massUnit->cCode], $massUnit->cCode);
            $threshold  = 250 * $fFactor / 1000;
            $nAmount    = 1;
            $mappedCode = $unitMappings[$massUnit->cCode];

            if ($threshold > 0 && $result->fMassMenge > $threshold) {
                $result->fGrundpreisMenge = $nAmount;
                $result->fMassMenge      /= $fFactor;
                $result->fVPEWert         = $result->fMassMenge / $amount / $result->fGrundpreisMenge;
                $result->fBasePreis       = $price / $result->fVPEWert;
                $result->cVPEEinheit      = $result->fGrundpreisMenge . ' ' .
                    UnitsOfMeasure::getPrintAbbreviation($mappedCode);
            }
        }

        return $result;
    }

    /**
     * @param string        $attribute
     * @param string|int    $value
     * @param callable|null $callback
     * @return mixed
     * @since 5.0
     */
    public static function getDataByAttribute(string $attribute, $value, callable $callback = null)
    {
        $res = Shop::Container()->getDB()->select('tartikel', $attribute, $value);

        return \is_callable($callback)
            ? $callback($res)
            : $res;
    }

    /**
     * @param string        $attribute
     * @param string        $value
     * @param callable|null $callback
     * @return mixed
     * @since 5.0.0
     */
    public static function getProductByAttribute($attribute, $value, callable $callback = null)
    {
        $art = ($res = self::getDataByAttribute($attribute, $value)) !== null
            ? (new Artikel())->fuelleArtikel($res->kArtikel, Artikel::getDefaultOptions())
            : null;

        return \is_callable($callback)
            ? $callback($art)
            : $art;
    }

    /**
     * Gibt den kArtikel von einem Varikombi Kind zurück und braucht dafür Eigenschaften und EigenschaftsWerte
     * Klappt nur bei max. 2 Dimensionen
     *
     * @param int $productID
     * @param int $es0
     * @param int $esWert0
     * @param int $es1
     * @param int $esWert1
     * @return int
     * @since 5.0.0
     * @former findeKindArtikelZuEigenschaft()
     */
    public static function getChildProductIDByAttribute(
        int $productID,
        int $es0,
        int $esWert0,
        int $es1 = 0,
        int $esWert1 = 0
    ): int {
        if ($es0 > 0 && $esWert0 > 0) {
            $join   = ' JOIN teigenschaftkombiwert
                          ON teigenschaftkombiwert.kEigenschaftKombi = tartikel.kEigenschaftKombi
                          AND teigenschaftkombiwert.kEigenschaft = ' . $es0 . '
                          AND teigenschaftkombiwert.kEigenschaftWert = ' . $esWert0;
            $having = '';
            if ($es1 > 0 && $esWert1 > 0) {
                $join = ' JOIN teigenschaftkombiwert
                              ON teigenschaftkombiwert.kEigenschaftKombi = tartikel.kEigenschaftKombi
                              AND teigenschaftkombiwert.kEigenschaft IN(' . $es0 . ', ' . $es1 . ')
                              AND teigenschaftkombiwert.kEigenschaftWert IN(' . $esWert0 . ', ' . $esWert1 . ')';

                $having = ' HAVING COUNT(*) = 2';
            }
            $product = Shop::Container()->getDB()->query(
                'SELECT kArtikel
                    FROM tartikel' . $join . '
                    WHERE tartikel.kVaterArtikel = ' . $productID . '
                    GROUP BY teigenschaftkombiwert.kEigenschaftKombi' . $having,
                ReturnType::SINGLE_OBJECT
            );
            if (isset($product->kArtikel) && \count($product->kArtikel) > 0) {
                return (int)$product->kArtikel;
            }
        }

        return 0;
    }

    /**
     * @param int  $productID
     * @param bool $visibility
     * @return array
     * @since 5.0.0
     * @former gibVarKombiEigenschaftsWerte()
     */
    public static function getVarCombiAttributeValues(int $productID, bool $visibility = true): array
    {
        $attributeValues = [];
        if ($productID <= 0 || !self::isVariChild($productID)) {
            return $attributeValues;
        }
        $product        = new Artikel();
        $productOptions = new stdClass();
        if (!$visibility) {
            $productOptions->nKeineSichtbarkeitBeachten = 1;
        }

        $product->fuelleArtikel($productID, $productOptions);

        if (GeneralObject::hasCount('oVariationenNurKind_arr', $product)) {
            foreach ($product->oVariationenNurKind_arr as $child) {
                $attributeValue                       = new stdClass();
                $attributeValue->kEigenschaftWert     = $child->Werte[0]->kEigenschaftWert;
                $attributeValue->kEigenschaft         = $child->kEigenschaft;
                $attributeValue->cEigenschaftName     = $child->cName;
                $attributeValue->cEigenschaftWertName = $child->Werte[0]->cName;

                $attributeValues[] = $attributeValue;
            }
        }

        return $attributeValues;
    }

    /**
     * @param array $variations
     * @param int   $propertyID
     * @param int   $propertyValueID
     * @return bool|object
     * @former findeVariation()
     * @since 5.0.0
     */
    public static function findVariation(array $variations, int $propertyID, int $propertyValueID): bool
    {
        foreach ($variations as $variation) {
            $variation->kEigenschaft = (int)$variation->kEigenschaft;
            if ($variation->kEigenschaft === $propertyID
                && isset($variation->Werte)
                && \is_array($variation->Werte)
                && \count($variation->Werte) > 0
            ) {
                foreach ($variation->Werte as $oWert) {
                    $oWert->kEigenschaftWert = (int)$oWert->kEigenschaftWert;
                    if ($oWert->kEigenschaftWert === $propertyValueID) {
                        return $oWert;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param Artikel $product
     * @param string  $config
     * @return int
     * @former gibVerfuegbarkeitsformularAnzeigen()
     * @since 5.0.0
     */
    public static function showAvailabilityForm(Artikel $product, string $config): int
    {
        if ($config !== 'N'
            && $product->cLagerBeachten === 'Y'
            && ((int)$product->inWarenkorbLegbar === \INWKNICHTLEGBAR_LAGER
                || (int)$product->inWarenkorbLegbar === \INWKNICHTLEGBAR_LAGERVAR
                || ($product->fLagerbestand <= 0 && $product->cLagerKleinerNull !== 'Y'))
        ) {
            switch ($config) {
                case 'Y':
                    return 1;
                case 'P':
                    return 2;
                case 'L':
                default:
                    return 3;
            }
        }

        return 0;
    }

    /**
     * @param int       $productID
     * @param bool|null $isParent
     * @return stdClass|null
     * @former gibArtikelXSelling()
     * @since 5.0.0
     */
    public static function getXSelling(int $productID, $isParent = null): ?stdClass
    {
        if ($productID <= 0) {
            return null;
        }
        $defaultOptions                   = Artikel::getDefaultOptions();
        $xSelling                         = new stdClass();
        $xSelling->Standard               = new stdClass();
        $xSelling->Kauf                   = new stdClass();
        $xSelling->Standard->XSellGruppen = [];
        $xSelling->Kauf->Artikel          = [];
        $config                           = Shop::getSettings([\CONF_ARTIKELDETAILS])['artikeldetails'];
        if ($config['artikeldetails_xselling_standard_anzeigen'] === 'Y') {
            $stockFilterSQL = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $xsell          = Shop::Container()->getDB()->queryPrepared(
                'SELECT txsell.*, txsellgruppe.cName, txsellgruppe.cBeschreibung
                    FROM txsell
                    JOIN tartikel
                        ON txsell.kXSellArtikel = tartikel.kArtikel
                    LEFT JOIN txsellgruppe
                        ON txsellgruppe.kXSellGruppe = txsell.kXSellGruppe
                        AND txsellgruppe.kSprache = :lid
                    WHERE txsell.kArtikel = :aid' . $stockFilterSQL . '
                    ORDER BY tartikel.cName',
                ['lid' => Shop::getLanguageID(), 'aid' => $productID],
                ReturnType::ARRAY_OF_OBJECTS
            );
            if (\count($xsell) > 0) {
                $xsellgruppen   = group($xsell, static function ($e) {
                    return $e->kXSellGruppe;
                });
                $defaultOptions = Artikel::getDefaultOptions();
                foreach ($xsellgruppen as $groupID => $products) {
                    $group          = new stdClass();
                    $group->Artikel = [];
                    foreach ($products as $xs) {
                        $group->Name         = $xs->cName;
                        $group->Beschreibung = $xs->cBeschreibung;
                        $product             = (new Artikel())->fuelleArtikel((int)$xs->kXSellArtikel, $defaultOptions);
                        if ($product !== null && (int)$product->kArtikel > 0 && $product->aufLagerSichtbarkeit()) {
                            $group->Artikel[] = $product;
                        }
                    }
                    $xSelling->Standard->XSellGruppen[] = $group;
                }
            }
        }

        if ($config['artikeldetails_xselling_kauf_anzeigen'] === 'Y') {
            $limit = (int)$config['artikeldetails_xselling_kauf_anzahl'];
            if ($isParent === null) {
                $isParent = self::isParent($productID);
            }
            if ($isParent === true) {
                $selectSQL = 'txsellkauf.kXSellArtikel';
                $filterSQL = 'tartikel.kVaterArtikel';
                if ($config['artikeldetails_xselling_kauf_parent'] === 'Y') {
                    $selectSQL = 'IF(tartikel.kVaterArtikel = 0, txsellkauf.kXSellArtikel, tartikel.kVaterArtikel)';
                    $filterSQL = 'IF(tartikel.kVaterArtikel = 0, txsellkauf.kXSellArtikel, tartikel.kVaterArtikel)';
                }
                $xsell = Shop::Container()->getDB()->query(
                    'SELECT ' . $productID . ' AS kArtikel, ' . $selectSQL . ' AS kXSellArtikel,
                        SUM(txsellkauf.nAnzahl) nAnzahl
                        FROM txsellkauf
                        JOIN tartikel ON tartikel.kArtikel = txsellkauf.kXSellArtikel
                        WHERE (txsellkauf.kArtikel IN (
                                SELECT tartikel.kArtikel
                                FROM tartikel
                                WHERE tartikel.kVaterArtikel = ' . $productID . '
                            ) OR txsellkauf.kArtikel = ' . $productID . ')
                            AND ' . $filterSQL . ' != ' . $productID . '
                        GROUP BY 1, 2
                        ORDER BY SUM(txsellkauf.nAnzahl) DESC
                        LIMIT ' . $limit,
                    ReturnType::ARRAY_OF_OBJECTS
                );
            } elseif ($config['artikeldetails_xselling_kauf_parent'] === 'Y') {
                $xsell = Shop::Container()->getDB()->queryPrepared(
                    'SELECT txsellkauf.kArtikel,
                    IF(tartikel.kVaterArtikel = 0, txsellkauf.kXSellArtikel, tartikel.kVaterArtikel) AS kXSellArtikel,
                    SUM(txsellkauf.nAnzahl) nAnzahl
                        FROM txsellkauf
                        JOIN tartikel
                            ON tartikel.kArtikel = txsellkauf.kXSellArtikel
                        WHERE txsellkauf.kArtikel = :pid
                            AND (tartikel.kVaterArtikel != (
                                SELECT tartikel.kVaterArtikel
                                FROM tartikel
                                WHERE tartikel.kArtikel = :pid
                            ) OR tartikel.kVaterArtikel = 0)
                        GROUP BY 1, 2
                        ORDER BY SUM(txsellkauf.nAnzahl) DESC
                        LIMIT :lmt',
                    ['pid' => $productID, 'lmt' => $limit],
                    ReturnType::ARRAY_OF_OBJECTS
                );
            } else {
                $xsell = Shop::Container()->getDB()->selectAll(
                    'txsellkauf',
                    'kArtikel',
                    $productID,
                    'kXSellArtikel',
                    'nAnzahl DESC',
                    $limit
                );
            }
            $xsellCount2 = \is_array($xsell) ? \count($xsell) : 0;
            if ($xsellCount2 > 0) {
                foreach ($xsell as $xs) {
                    $product = new Artikel();
                    $product->fuelleArtikel((int)$xs->kXSellArtikel, $defaultOptions);
                    if ($product->kArtikel > 0 && $product->aufLagerSichtbarkeit()) {
                        $xSelling->Kauf->Artikel[] = $product;
                    }
                }
            }
        }
        \executeHook(\HOOK_ARTIKEL_INC_XSELLING, [
            'kArtikel' => $productID,
            'xSelling' => &$xSelling
        ]);

        return $xSelling;
    }

    /**
     * @param array $notices
     * @param array $conf
     * @return array
     * @former bearbeiteFrageZumProdukt()
     * @since 5.0.0
     */
    public static function checkProductQuestion(array $notices, array $conf): array
    {
        if ($conf['artikeldetails']['artikeldetails_fragezumprodukt_anzeigen'] !== 'N') {
            $missingData = self::getMissingProductQuestionFormData($conf);
            Shop::Smarty()->assign('fehlendeAngaben_fragezumprodukt', $missingData);
            $resultCode = Form::eingabenKorrekt($missingData);

            \executeHook(\HOOK_ARTIKEL_INC_FRAGEZUMPRODUKT_PLAUSI);
            if ($resultCode) {
                if (!self::checkProductQuestionFloodProtection(
                    (int)$conf['artikeldetails']['produktfrage_sperre_minuten']
                )) {
                    $checkbox        = new CheckBox();
                    $customerGroupID = Frontend::getCustomerGroup()->getID();
                    $inquiry         = self::getProductQuestionFormDefaults();

                    \executeHook(\HOOK_ARTIKEL_INC_FRAGEZUMPRODUKT);
                    if (empty($inquiry->cNachname)) {
                        $inquiry->cNachname = '';
                    }
                    if (empty($inquiry->cVorname)) {
                        $inquiry->cVorname = '';
                    }
                    $checkbox->triggerSpecialFunction(
                        \CHECKBOX_ORT_FRAGE_ZUM_PRODUKT,
                        $customerGroupID,
                        true,
                        $_POST,
                        ['oKunde' => $inquiry, 'oNachricht' => $inquiry]
                    )->checkLogging(\CHECKBOX_ORT_FRAGE_ZUM_PRODUKT, $customerGroupID, $_POST, true);
                    Shop::Smarty()->assign('PositiveFeedback', self::sendProductQuestion());
                } else {
                    $notices[] = Shop::Lang()->get('questionNotPossible', 'messages');
                }
            } elseif (isset($missingData['email']) && $missingData['email'] === 3) {
                $notices[] = Shop::Lang()->get('blockedEmail');
            } else {
                $notices[] = Shop::Lang()->get('mandatoryFieldNotification', 'errorMessages');
            }
        } else {
            $notices[] = Shop::Lang()->get('productquestionPleaseLogin', 'errorMessages');
        }

        return $notices;
    }

    /**
     * @param array $conf
     * @return array
     * @former gibFehlendeEingabenProduktanfrageformular()
     * @since 5.0.0
     */
    public static function getMissingProductQuestionFormData(array $conf): array
    {
        $ret = [];
        if (!$_POST['nachricht']) {
            $ret['nachricht'] = 1;
        }
        if (SimpleMail::checkBlacklist($_POST['email'])) {
            $ret['email'] = 3;
        }
        if (Text::filterEmailAddress($_POST['email']) === false) {
            $ret['email'] = 2;
        }
        if (!$_POST['email']) {
            $ret['email'] = 1;
        }
        if ($conf['artikeldetails']['produktfrage_abfragen_vorname'] === 'Y' && !$_POST['vorname']) {
            $ret['vorname'] = 1;
        }
        if ($conf['artikeldetails']['produktfrage_abfragen_nachname'] === 'Y' && !$_POST['nachname']) {
            $ret['nachname'] = 1;
        }
        if ($conf['artikeldetails']['produktfrage_abfragen_firma'] === 'Y' && !$_POST['firma']) {
            $ret['firma'] = 1;
        }
        if ($conf['artikeldetails']['produktfrage_abfragen_fax'] === 'Y' && !$_POST['fax']) {
            $ret['fax'] = 1;
        }
        if ($conf['artikeldetails']['produktfrage_abfragen_tel'] === 'Y' && !$_POST['tel']) {
            $ret['tel'] = 1;
        }
        if ($conf['artikeldetails']['produktfrage_abfragen_mobil'] === 'Y' && !$_POST['mobil']) {
            $ret['mobil'] = 1;
        }
        if ($conf['artikeldetails']['produktfrage_abfragen_captcha'] !== 'N' && !Form::validateCaptcha($_POST)) {
            $ret['captcha'] = 2;
        }
        $checkBox = new CheckBox();
        $ret      = \array_merge(
            $ret,
            $checkBox->validateCheckBox(
                \CHECKBOX_ORT_FRAGE_ZUM_PRODUKT,
                Frontend::getCustomerGroup()->getID(),
                $_POST,
                true
            )
        );

        return $ret;
    }

    /**
     * @return stdClass
     * @former baueProduktanfrageFormularVorgaben()
     * @since 5.0.0
     */
    public static function getProductQuestionFormDefaults(): stdClass
    {
        $msg             = Form::getDefaultCustomerFormInputs();
        $msg->cNachricht = isset($_POST['nachricht']) ? Text::filterXSS($_POST['nachricht']) : null;

        return $msg;
    }

    /**
     * @return string
     * @former sendeProduktanfrage()
     * @since 5.0.0
     */
    public static function sendProductQuestion(): string
    {
        $conf             = Shop::getSettings([\CONF_EMAILS, \CONF_ARTIKELDETAILS, \CONF_GLOBAL]);
        $data             = new stdClass();
        $data->tartikel   = $GLOBALS['AktuellerArtikel'];
        $data->tnachricht = self::getProductQuestionFormDefaults();
        $replyToName      = '';
        if ($data->tnachricht->cVorname) {
            $replyToName = $data->tnachricht->cVorname . ' ';
        }
        if ($data->tnachricht->cNachname) {
            $replyToName .= $data->tnachricht->cNachname;
        }
        if ($data->tnachricht->cFirma) {
            if ($data->tnachricht->cNachname || $data->tnachricht->cVorname) {
                $replyToName .= ' - ';
            }
            $replyToName .= $data->tnachricht->cFirma;
        }
        $mail = new stdClass();
        if (isset($conf['artikeldetails']['artikeldetails_fragezumprodukt_email'])) {
            $mail->toEmail = $conf['artikeldetails']['artikeldetails_fragezumprodukt_email'];
        }
        if (empty($mail->toEmail)) {
            $mail->toEmail = $conf['emails']['email_master_absender'];
        }
        $mail->toName       = $conf['global']['global_shopname'];
        $mail->replyToEmail = $data->tnachricht->cMail;
        $mail->replyToName  = $replyToName;
        $data->mail         = $mail;

        $mailer = Shop::Container()->get(Mailer::class);
        $mail   = new Mail();
        $mail   = $mail->createFromTemplateID(\MAILTEMPLATE_PRODUKTANFRAGE, $data);
        if ($conf['artikeldetails']['produktfrage_kopiekunde'] === 'Y') {
            $mail->addCopyRecipient($data->tnachricht->cMail);
        }
        $mailer->send($mail);

        $history             = new stdClass();
        $history->kSprache   = Shop::getLanguageID();
        $history->kArtikel   = Shop::$kArtikel;
        $history->cAnrede    = $data->tnachricht->cAnrede;
        $history->cVorname   = $data->tnachricht->cVorname;
        $history->cNachname  = $data->tnachricht->cNachname;
        $history->cFirma     = $data->tnachricht->cFirma;
        $history->cTel       = $data->tnachricht->cTel;
        $history->cMobil     = $data->tnachricht->cMobil;
        $history->cFax       = $data->tnachricht->cFax;
        $history->cMail      = $data->tnachricht->cMail;
        $history->cNachricht = $data->tnachricht->cNachricht;
        $history->cIP        = Request::getRealIP();
        $history->dErstellt  = 'NOW()';

        $inquiryID = Shop::Container()->getDB()->insert('tproduktanfragehistory', $history);
        Shop::Container()->getAlertService()->addAlert(
            Alert::TYPE_SUCCESS,
            Shop::Lang()->get('thankYouForQuestion', 'messages'),
            'thankYouForQuestion'
        );
        if (isset($_SESSION['Kampagnenbesucher'])) {
            Campaign::setCampaignAction(\KAMPAGNE_DEF_FRAGEZUMPRODUKT, $inquiryID, 1.0);
        }

        return Shop::Lang()->get('thankYouForQuestion', 'messages');
    }

    /**
     * @param int $min
     * @return bool
     * @former floodSchutzProduktanfrage()
     * @since 5.0.0
     */
    public static function checkProductQuestionFloodProtection(int $min = 0): bool
    {
        if ($min <= 0) {
            return false;
        }
        $history = Shop::Container()->getDB()->queryPrepared(
            'SELECT kProduktanfrageHistory
                FROM tproduktanfragehistory
                WHERE cIP = :ip
                    AND DATE_SUB(NOW(), INTERVAL :min MINUTE) < dErstellt',
            ['ip' => Request::getRealIP(), 'min' => $min],
            ReturnType::SINGLE_OBJECT
        );

        return isset($history->kProduktanfrageHistory) && $history->kProduktanfrageHistory > 0;
    }

    /**
     * @param array $notices
     * @return array
     * @former bearbeiteBenachrichtigung()
     * @since 5.0.0
     */
    public static function checkAvailabilityMessage(array $notices): array
    {
        $conf = Shop::getSettings([\CONF_ARTIKELDETAILS]);
        if (!isset($_POST['a'], $conf['artikeldetails']['benachrichtigung_nutzen'])
            || (int)$_POST['a'] <= 0
            || $conf['artikeldetails']['benachrichtigung_nutzen'] === 'N'
        ) {
            return $notices;
        }
        $missingData = self::getMissingAvailibilityFormData();
        Shop::Smarty()->assign('fehlendeAngaben_benachrichtigung', $missingData);
        $resultCode = Form::eingabenKorrekt($missingData);

        \executeHook(\HOOK_ARTIKEL_INC_BENACHRICHTIGUNG_PLAUSI);
        if ($resultCode) {
            if (!self::checkAvailibityFormFloodProtection($conf['artikeldetails']['benachrichtigung_sperre_minuten'])) {
                $dbHandler = Shop::Container()->getDB();
                $refData   = (new OptinRefData())
                    ->setSalutation('')
                    ->setFirstName(Text::filterXSS($dbHandler->escape(\strip_tags($_POST['vorname']))) ?: '')
                    ->setLastName(Text::filterXSS($dbHandler->escape(\strip_tags($_POST['nachname']))) ?: '')
                    ->setProductId(Request::postInt('a'))
                    ->setEmail(Text::filterXSS($dbHandler->escape(\strip_tags($_POST['email']))) ?: '')
                    ->setLanguageID(Shop::getLanguageID())
                    ->setRealIP(Request::getRealIP());
                try {
                    (new Optin(OptinAvailAgain::class))
                        ->getOptinInstance()
                        ->createOptin($refData)
                        ->sendActivationMail();
                } catch (\Exception $e) {
                }
            } else {
                $notices[] = Shop::Lang()->get('notificationNotPossible', 'messages');
            }
        } elseif (isset($missingData['email']) && $missingData['email'] === 3) {
            $notices[] = Shop::Lang()->get('blockedEmail');
        } else {
            $notices[] = Shop::Lang()->get('mandatoryFieldNotification', 'errorMessages');
        }

        return $notices;
    }

    /**
     * @return array
     * @former gibFehlendeEingabenBenachrichtigungsformular()
     * @since 5.0.0
     */
    public static function getMissingAvailibilityFormData(): array
    {
        $ret  = [];
        $conf = Shop::getSettings([\CONF_ARTIKELDETAILS, \CONF_GLOBAL]);
        if (!$_POST['email']) {
            $ret['email'] = 1;
        } elseif (Text::filterEmailAddress($_POST['email']) === false) {
            $ret['email'] = 2;
        }
        if (SimpleMail::checkBlacklist($_POST['email'])) {
            $ret['email'] = 3;
        }
        if (empty($_POST['vorname']) && $conf['artikeldetails']['benachrichtigung_abfragen_vorname'] === 'Y') {
            $ret['vorname'] = 1;
        }
        if (empty($_POST['nachname']) && $conf['artikeldetails']['benachrichtigung_abfragen_nachname'] === 'Y') {
            $ret['nachname'] = 1;
        }
        if ($conf['artikeldetails']['benachrichtigung_abfragen_captcha'] !== 'N'
            && !Form::validateCaptcha($_POST)
        ) {
            $ret['captcha'] = 2;
        }
        // CheckBox Plausi
        $checkbox        = new CheckBox();
        $customerGroupID = Frontend::getCustomerGroup()->getID();
        $ret             = \array_merge(
            $ret,
            $checkbox->validateCheckBox(\CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT, $customerGroupID, $_POST, true)
        );

        return $ret;
    }

    /**
     * @return stdClass
     * @former baueFormularVorgabenBenachrichtigung()
     * @since 5.0.0
     */
    public static function getAvailabilityFormDefaults(): stdClass
    {
        return Form::getDefaultCustomerFormInputs();
    }

    /**
     * @param int $min
     * @return bool
     * @former floodSchutzBenachrichtigung()
     * @since 5.0.0
     */
    public static function checkAvailibityFormFloodProtection(int $min): bool
    {
        if (!$min) {
            return false;
        }
        $history = Shop::Container()->getDB()->executeQueryPrepared(
            'SELECT kVerfuegbarkeitsbenachrichtigung
                FROM tverfuegbarkeitsbenachrichtigung
                WHERE cIP = :ip
                AND DATE_SUB(NOW(), INTERVAL :min MINUTE) < dErstellt',
            ['ip' => Request::getRealIP(), 'min' => $min],
            ReturnType::SINGLE_OBJECT
        );

        return isset($history->kVerfuegbarkeitsbenachrichtigung) && $history->kVerfuegbarkeitsbenachrichtigung > 0;
    }

    /**
     * @param int $productID
     * @param int $categoryID
     * @return stdClass
     * @former gibNaviBlaettern()
     * @since 5.0.0
     */
    public static function getProductNavigation(int $productID, int $categoryID): stdClass
    {
        $nav             = new stdClass();
        $customerGroupID = Frontend::getCustomerGroup()->getID();
        // Wurde der Artikel von der Artikelübersicht aus angeklickt?
        if ($productID > 0
            && isset($_SESSION['oArtikelUebersichtKey_arr'])
            && \count($_SESSION['oArtikelUebersichtKey_arr']) > 0
        ) {
            $collection = $_SESSION['oArtikelUebersichtKey_arr'];
            if (!($collection instanceof Collection)) {
                \collect($collection);
            }
            // Such die Position des aktuellen Artikels im Array der Artikelübersicht
            $prevID = 0;
            $nextID = 0;
            $index  = $collection->search($productID, true);
            if ($index === 0) {
                // Artikel ist an der ersten Position => es gibt nur einen nächsten Artikel (oder keinen :))
                $nextID = $collection[$index + 1] ?? null;
            } elseif ($index === ($collection->count() - 1)) {
                // Artikel ist an der letzten Position => es gibt nur einen voherigen Artikel
                $prevID = $collection[$index - 1];
            } elseif ($index !== false) {
                $nextID = $collection[$index + 1];
                $prevID = $collection[$index - 1];
            }
            if ($nextID > 0) {
                $nav->naechsterArtikel = (new Artikel())->fuelleArtikel($nextID, Artikel::getDefaultOptions());
                if ($nav->naechsterArtikel === null) {
                    unset($nav->naechsterArtikel);
                }
            }
            if ($prevID > 0) {
                $nav->vorherigerArtikel = (new Artikel())->fuelleArtikel($prevID, Artikel::getDefaultOptions());
                if ($nav->vorherigerArtikel->kArtikel === null) {
                    unset($nav->vorherigerArtikel);
                }
            }
        }
        // Ist der Besucher nicht von der Artikelübersicht gekommen?
        if ($categoryID > 0 && (!isset($nav->vorherigerArtikel) && !isset($nav->naechsterArtikel))) {
            $stockFilter = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $prev        = Shop::Container()->getDB()->query(
                'SELECT tartikel.kArtikel
                    FROM tkategorieartikel, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        AND tartikel.kVaterArtikel = 0
                        AND tkategorieartikel.kKategorie = ' . $categoryID . '
                        AND tartikel.kArtikel < ' . $productID . ' ' . $stockFilter . '
                    ORDER BY tartikel.kArtikel DESC
                    LIMIT 1',
                ReturnType::SINGLE_OBJECT
            );
            $next        = Shop::Container()->getDB()->query(
                'SELECT tartikel.kArtikel
                    FROM tkategorieartikel, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        AND tartikel.kVaterArtikel = 0
                        AND tkategorieartikel.kKategorie = ' . $categoryID . '
                        AND tartikel.kArtikel > ' . $productID . ' ' . $stockFilter . '
                    ORDER BY tartikel.kArtikel
                    LIMIT 1',
                ReturnType::SINGLE_OBJECT
            );

            if (!empty($prev->kArtikel)) {
                $nav->vorherigerArtikel = (new Artikel())
                    ->fuelleArtikel((int)$prev->kArtikel, Artikel::getDefaultOptions());
            }
            if (!empty($next->kArtikel)) {
                $nav->naechsterArtikel = (new Artikel())
                    ->fuelleArtikel((int)$next->kArtikel, Artikel::getDefaultOptions());
            }
        }

        return $nav;
    }

    /**
     * @param int $attributeValue
     * @return array
     * @former gibNichtErlaubteEigenschaftswerte()
     * @since 5.0.0
     */
    public static function getNonAllowedAttributeValues(int $attributeValue): array
    {
        $nonAllowed  = Shop::Container()->getDB()->selectAll(
            'teigenschaftwertabhaengigkeit',
            'kEigenschaftWert',
            $attributeValue,
            'kEigenschaftWertZiel AS EigenschaftWert'
        );
        $nonAllowed2 = Shop::Container()->getDB()->selectAll(
            'teigenschaftwertabhaengigkeit',
            'kEigenschaftWertZiel',
            $attributeValue,
            'kEigenschaftWert AS EigenschaftWert'
        );

        return \array_merge($nonAllowed, $nonAllowed2);
    }

    /**
     * @param null|string|array $redirectParam
     * @param bool              $renew
     * @param null|Artikel      $product
     * @param null|float        $amount
     * @param int               $configItemID
     * @param array             $notices
     * @return array
     * @former baueArtikelhinweise()
     * @since 5.0.0
     */
    public static function getProductMessages(
        $redirectParam = null,
        $renew = false,
        $product = null,
        $amount = null,
        $configItemID = 0,
        array $notices = []
    ): array {
        if ($redirectParam === null && isset($_GET['r'])) {
            $redirectParam = $_GET['r'];
        }
        if ($renew) {
            $notices = [];
        }
        if ($redirectParam) {
            $messages = \is_array($redirectParam) ? $redirectParam : \explode(',', $redirectParam);
            foreach (\array_unique($messages) as $message) {
                switch ($message) {
                    case \R_LAGERVAR:
                        $notices[] = Shop::Lang()->get('quantityNotAvailableVar', 'messages');
                        break;
                    case \R_VARWAEHLEN:
                        $notices[] = Shop::Lang()->get('chooseVariations', 'messages');
                        break;
                    case \R_VORBESTELLUNG:
                        $notices[] = Shop::Lang()->get('preorderNotPossible', 'messages');
                        break;
                    case \R_LOGIN:
                        $notices[] = Shop::Lang()->get('pleaseLogin', 'messages');
                        break;
                    case \R_LAGER:
                        $notices[] = Shop::Lang()->get('quantityNotAvailable', 'messages');
                        break;
                    case \R_MINDESTMENGE:
                        $notices[] = \lang_mindestbestellmenge(
                            $product ?? $GLOBALS['AktuellerArtikel'],
                            $amount ?? $_GET['n'],
                            $configItemID
                        );
                        break;
                    case \R_LOGIN_WUNSCHLISTE:
                        $notices[] = Shop::Lang()->get('loginWishlist', 'messages');
                        break;
                    case \R_MAXBESTELLMENGE:
                        $notices[] = Shop::Lang()->get('wkMaxorderlimit', 'messages');
                        break;
                    case \R_ARTIKELABNAHMEINTERVALL:
                        $notices[] = Shop::Lang()->get('wkPurchaseintervall', 'messages');
                        break;
                    case \R_UNVERKAEUFLICH:
                        $notices[] = Shop::Lang()->get('wkUnsalable', 'messages');
                        break;
                    case \R_AUFANFRAGE:
                        $notices[] = Shop::Lang()->get('wkOnrequest', 'messages');
                        break;
                    case \R_EMPTY_TAG:
                        $notices[] = Shop::Lang()->get('tagArtikelEmpty', 'messages');
                        break;
                    case \R_EMPTY_VARIBOX:
                        $notices[] = Shop::Lang()->get('artikelVariBoxEmpty', 'messages');
                        break;
                    case \R_MISSING_TOKEN:
                        $notices[] = Shop::Lang()->get('missingToken', 'messages');
                        break;
                    default:
                        $notices[] = Shop::Lang()->get('unknownError', 'messages');
                        break;
                }
                \executeHook(\HOOK_ARTIKEL_INC_ARTIKELHINWEISSWITCH);
            }
        }

        return $notices;
    }

    /**
     * Baue Blätter Navi - Dient für die Blätternavigation unter Bewertungen in der Artikelübersicht
     *
     * @param int $ratingPage
     * @param int $ratingStars
     * @param int $ratingCount
     * @param int $pageCount
     * @return stdClass
     * @former baueBewertungNavi()
     * @since 5.0.0
     */
    public static function getRatingNavigation(
        int $ratingPage,
        int $ratingStars,
        int $ratingCount,
        int $pageCount = 0
    ): stdClass {
        $navigation         = new stdClass();
        $navigation->nAktiv = 0;
        if (!$pageCount) {
            $pageCount = 10;
        }
        // Ist die Anzahl der Bewertungen für einen bestimmten Artikel, in einer bestimmten Sprache größer als
        // die im Backend eingestellte maximale Anzahl an Bewertungen für eine Seite?
        if ($ratingCount > $pageCount) {
            $counts = [];
            // Anzahl an Seiten
            $pages = \ceil($ratingCount / $pageCount);
            $max   = 5; // Zeige in der Navigation nur maximal X Seiten an
            $start = 0; // Wenn die aktuelle Seite - $nMaxAnzeige größer 0 ist, wird nAnfang gesetzt
            $end   = 0; // Wenn die aktuelle Seite + $nMaxAnzeige <= $nSeitenist, wird nEnde gesetzt
            $prev  = $ratingPage - 1; // Zum zurück blättern in der Navigation
            if ($prev === 0) {
                $prev = 1;
            }
            $next = $ratingPage + 1; // Zum vorwärts blättern in der Navigation
            if ($next >= $pages) {
                $next = $pages;
            }
            // Ist die maximale Anzahl an Seiten > als die Anzahl erlaubter Seiten in der Navigation?
            if ($pages > $max) {
                // Diese Variablen ermitteln die aktuellen Seiten in der Navigation, die angezeigt werden sollen.
                // Begrenzt durch $nMaxAnzeige.
                // Ist die aktuelle Seite nach dem abzug der Begrenzung größer oder gleich 1?
                if (($ratingPage - $max) >= 1) {
                    $start = 1;
                    $nVon  = ($ratingPage - $max) + 1;
                } else {
                    $start = 0;
                    $nVon  = 1;
                }
                // Ist die aktuelle Seite nach dem addieren der Begrenzung kleiner als die maximale Anzahl der Seiten
                if (($ratingPage + $max) < $pages) {
                    $end  = $pages;
                    $nBis = ($ratingPage + $max) - 1;
                } else {
                    $end  = 0;
                    $nBis = $pages;
                }
                // Baue die Seiten für die Navigation
                for ($i = $nVon; $i <= $nBis; $i++) {
                    $counts[] = $i;
                }
            } else {
                // Baue die Seiten für die Navigation
                for ($i = 1; $i <= $pages; $i++) {
                    $counts[] = $i;
                }
            }
            // Blaetter Objekt um später in Smarty damit zu arbeiten
            $navigation->nSeiten             = $pages;
            $navigation->nVoherige           = $prev;
            $navigation->nNaechste           = $next;
            $navigation->nAnfang             = $start;
            $navigation->nEnde               = $end;
            $navigation->nBlaetterAnzahl_arr = $counts;
            $navigation->nAktiv              = 1;
        }

        $navigation->nSterne        = $ratingStars;
        $navigation->nAktuelleSeite = $ratingPage;
        $navigation->nVon           = (($navigation->nAktuelleSeite - 1) * $pageCount) + 1;
        $navigation->nBis           = $navigation->nAktuelleSeite * $pageCount;

        if ($navigation->nBis > $ratingCount) {
            --$navigation->nBis;
        }

        return $navigation;
    }

    /**
     * Mappt den Fehlercode für Bewertungen
     *
     * @param string $code
     * @param float  $fGuthaben
     * @return string
     * @former mappingFehlerCode()
     * @since 5.0.0
     */
    public static function mapErrorCode($code, $fGuthaben = 0.0): string
    {
        switch ($code) {
            case 'f01':
                $error = Shop::Lang()->get('mandatoryFieldNotification', 'errorMessages');
                break;
            case 'f02':
                $error = Shop::Lang()->get('bewertungBewexist', 'errorMessages');
                break;
            case 'f03':
                $error = Shop::Lang()->get('bewertungBewnotbought', 'errorMessages');
                break;
            case 'f04':
                $error = Shop::Lang()->get('loginFirst', 'product rating');
                break;
            case 'f05':
                $error = Shop::Lang()->get('ratingRange', 'errorMessages');
                break;
            case 'h01':
                $error = Shop::Lang()->get('bewertungBewadd', 'messages');
                break;
            case 'h02':
                $error = Shop::Lang()->get('bewertungHilfadd', 'messages');
                break;
            case 'h03':
                $error = Shop::Lang()->get('bewertungHilfchange', 'messages');
                break;
            case 'h04':
                $error = \sprintf(Shop::Lang()->get('bewertungBewaddCredits', 'messages'), (string)$fGuthaben);
                break;
            case 'h05':
                $error = Shop::Lang()->get('bewertungBewaddacitvate', 'messages');
                break;
            default:
                $error = '';
        }
        \executeHook(\HOOK_ARTIKEL_INC_BEWERTUNGHINWEISSWITCH, ['error' => $error]);

        return $error;
    }

    /**
     * @param Artikel $parent
     * @param Artikel $child
     * @return mixed
     * @former fasseVariVaterUndKindZusammen()
     * @since 5.0.0
     */
    public static function combineParentAndChild($parent, $child)
    {
        $product                              = $child;
        $kVariKindArtikel                     = (int)$child->kArtikel;
        $product->kArtikel                    = (int)$parent->kArtikel;
        $product->kVariKindArtikel            = $kVariKindArtikel;
        $product->nIstVater                   = 1;
        $product->kVaterArtikel               = (int)$parent->kArtikel;
        $product->kEigenschaftKombi           = $parent->kEigenschaftKombi;
        $product->kEigenschaftKombi_arr       = $parent->kEigenschaftKombi_arr;
        $product->fDurchschnittsBewertung     = $parent->fDurchschnittsBewertung;
        $product->Bewertungen                 = $parent->Bewertungen ?? null;
        $product->HilfreichsteBewertung       = $parent->HilfreichsteBewertung ?? null;
        $product->oVariationKombiVorschau_arr = $parent->oVariationKombiVorschau_arr ?? [];
        $product->oVariationDetailPreis_arr   = $parent->oVariationDetailPreis_arr;
        $product->oVariationKombiVorschauText = $parent->oVariationKombiVorschauText ?? null;
        $product->cVaterURL                   = $parent->cURL;
        $product->VaterFunktionsAttribute     = $parent->FunktionsAttribute;

        \executeHook(\HOOK_ARTIKEL_INC_FASSEVARIVATERUNDKINDZUSAMMEN, ['article' => $product]);

        return $product;
    }

    /**
     * @param int $productID
     * @return array
     * @former holeAehnlicheArtikel()
     * @since 5.0.0
     */
    public static function getSimilarProductsByID(int $productID): array
    {
        $products        = [];
        $limit           = ' LIMIT 3';
        $conf            = Shop::getSettings([\CONF_ARTIKELDETAILS]);
        $xSeller         = self::getXSelling($productID);
        $xsellProductIDs = [];
        if ($xSeller !== null && GeneralObject::hasCount('XSellGruppen', $xSeller->Standard)) {
            foreach ($xSeller->Standard->XSellGruppen as $xSeller) {
                if (GeneralObject::hasCount('Artikel', $xSeller)) {
                    foreach ($xSeller->Artikel as $product) {
                        $product->kArtikel = (int)$product->kArtikel;
                        if (!\in_array($product->kArtikel, $xsellProductIDs, true)) {
                            $xsellProductIDs[] = $product->kArtikel;
                        }
                    }
                }
            }
        }
        if (isset($xSeller->Kauf) && GeneralObject::hasCount('XSellGruppen', $xSeller->Kauf)) {
            foreach ($xSeller->Kauf->XSellGruppen as $xSeller) {
                if (GeneralObject::hasCount('Artikel', $xSeller)) {
                    foreach ($xSeller->Artikel as $product) {
                        $product->kArtikel = (int)$product->kArtikel;
                        if (!\in_array($product->kArtikel, $xsellProductIDs, true)) {
                            $xsellProductIDs[] = $product->kArtikel;
                        }
                    }
                }
            }
        }

        $xsellSQL = \count($xsellProductIDs) > 0
            ? ' AND tartikel.kArtikel NOT IN (' . \implode(',', $xsellProductIDs) . ') '
            : '';

        if ($productID > 0) {
            if ((int)$conf['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'] > 0) {
                $limit = ' LIMIT ' . (int)$conf['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'];
            }
            $stockFilterSQL    = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $customerGroupID   = Frontend::getCustomerGroup()->getID();
            $productAttributes = Shop::Container()->getDB()->queryPrepared(
                'SELECT tartikelmerkmal.kArtikel, tartikel.kVaterArtikel
                    FROM tartikelmerkmal
                        JOIN tartikel ON tartikel.kArtikel = tartikelmerkmal.kArtikel
                            AND tartikel.kVaterArtikel != :kArtikel
                            AND (tartikel.nIstVater = 1 OR tartikel.kEigenschaftKombi = 0)
                        JOIN tartikelmerkmal similarMerkmal ON similarMerkmal.kArtikel = :kArtikel
                            AND similarMerkmal.kMerkmal = tartikelmerkmal.kMerkmal
                            AND similarMerkmal.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                        LEFT JOIN tartikelsichtbarkeit ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = :customerGroupID
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikelmerkmal.kArtikel != :kArtikel ' . $stockFilterSQL . ' ' . $xsellSQL . '
                    GROUP BY tartikelmerkmal.kArtikel
                    ORDER BY COUNT(tartikelmerkmal.kMerkmal) DESC
                    ' . $limit,
                [
                    'kArtikel'        => $productID,
                    'customerGroupID' => $customerGroupID
                ],
                ReturnType::ARRAY_OF_OBJECTS
            );
            if (\is_array($productAttributes) && \count($productAttributes) > 0) {
                $defaultOptions = Artikel::getDefaultOptions();
                foreach ($productAttributes as $productAttribute) {
                    $product = new Artikel();
                    $id      = $productAttribute->kVaterArtikel > 0
                        ? $productAttribute->kVaterArtikel
                        : $productAttribute->kArtikel;
                    $product->fuelleArtikel($id, $defaultOptions);
                    if ($product->kArtikel > 0) {
                        $products[] = $product;
                    }
                }
            } else { // Falls es keine Merkmale gibt, in tsuchcachetreffer suchen
                $searchCacheHits = Shop::Container()->getDB()->query(
                    'SELECT tsuchcachetreffer.kArtikel, tartikel.kVaterArtikel
                        FROM
                        (
                            SELECT kSuchCache
                            FROM tsuchcachetreffer
                            WHERE kArtikel = ' . $productID . '
                            AND nSort <= 10
                        ) AS ssSuchCache
                        JOIN tsuchcachetreffer
                            ON tsuchcachetreffer.kSuchCache = ssSuchCache.kSuchCache
                            AND tsuchcachetreffer.kArtikel != ' . $productID . '
                        LEFT JOIN tartikelsichtbarkeit
                            ON tsuchcachetreffer.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                        JOIN tartikel
                            ON tartikel.kArtikel = tsuchcachetreffer.kArtikel
                            AND tartikel.kVaterArtikel != ' . $productID . '
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL ' . $stockFilterSQL . ' ' . $xsellSQL . '
                        GROUP BY tsuchcachetreffer.kArtikel
                        ORDER BY COUNT(*) DESC' . $limit,
                    ReturnType::ARRAY_OF_OBJECTS
                );
                if (\count($searchCacheHits) > 0) {
                    $defaultOptions = Artikel::getDefaultOptions();
                    foreach ($searchCacheHits as $hit) {
                        $product = new Artikel();
                        $id      = ($hit->kVaterArtikel > 0)
                            ? $hit->kVaterArtikel
                            : $hit->kArtikel;
                        $product->fuelleArtikel($id, $defaultOptions);
                        if ($product->kArtikel > 0) {
                            $products[] = $product;
                        }
                    }
                }
            }
        }
        \executeHook(\HOOK_ARTIKEL_INC_AEHNLICHEARTIKEL, ['oArtikel_arr' => &$products]);

        foreach ($products as $i => $product) {
            foreach ($xsellProductIDs as $xsellProductID) {
                if ($product->kArtikel === $xsellProductID) {
                    unset($products[$i]);
                }
            }
        }

        return $products;
    }

    /**
     * @param int $productID
     * @return bool
     * @former ProductBundleWK()
     * @since 5.0.0
     */
    public static function addProductBundleToCart(int $productID): bool
    {
        if ($productID <= 0) {
            return false;
        }
        $options                             = Artikel::getDefaultOptions();
        $options->nKeineSichtbarkeitBeachten = 1;

        return CartHelper::addProductIDToCart($productID, 1, [], 0, false, 0, $options);
    }

    /**
     * @param int       $productID
     * @param float|int $amount
     * @param array     $variations
     * @param array     $configGroups
     * @param array     $configGroupAmounts
     * @param array     $configItemAmounts
     * @param bool      $singleProductOutput
     * @return stdClass|null
     * @since 5.0.0
     */
    public static function buildConfig(
        int $productID,
        $amount,
        $variations,
        $configGroups,
        $configGroupAmounts,
        $configItemAmounts,
        $singleProductOutput = false
    ): ?stdClass {
        $config                  = new stdClass;
        $config->fAnzahl         = $amount;
        $config->fGesamtpreis    = [0.0, 0.0];
        $config->cPreisLocalized = [];
        $config->cPreisString    = Shop::Lang()->get('priceAsConfigured', 'productDetails');

        if (!Configurator::checkLicense() || !Configurator::validateKonfig($productID)) {
            return null;
        }
        foreach ($variations as $i => $nVariation) {
            $_POST['eigenschaftwert_' . $i] = $nVariation;
        }
        if (self::isParent($productID)) {
            $productID          = self::getArticleForParent($productID);
            $selectedProperties = self::getSelectedPropertiesForVarCombiArticle($productID);
        } else {
            $selectedProperties = self::getSelectedPropertiesForArticle($productID, false);
        }
        $product = new Artikel();
        $product->fuelleArtikel($productID, Artikel::getDefaultOptions());

        $config->nMinDeliveryDays      = $product->nMinDeliveryDays;
        $config->nMaxDeliveryDays      = $product->nMaxDeliveryDays;
        $config->cEstimatedDelivery    = $product->cEstimatedDelivery;
        $config->Lageranzeige          = new stdClass();
        $config->Lageranzeige->nStatus = $product->Lageranzeige->nStatus;

        $amount = \max($amount, 1);
        if ($product->cTeilbar !== 'Y' && (int)$amount != $amount) {
            $amount = (int)$amount;
        }

        $_amount              = $singleProductOutput ? 1 : $amount;
        $config->fGesamtpreis = [
            Tax::getGross(
                $product->gibPreis($amount, $selectedProperties),
                Tax::getSalesTax($product->kSteuerklasse)
            ) * $_amount,
            $product->gibPreis($amount, $selectedProperties) * $_amount
        ];

        $config->oKonfig_arr = $product->oKonfig_arr;

        foreach ($configGroups as $i => $data) {
            $configGroups[$i] = (array)$data;
        }
        /** @var Group $configGroup */
        foreach ($config->oKonfig_arr as $i => $configGroup) {
            $configGroup->bAktiv = false;
            $configGroupID       = $configGroup->getKonfiggruppe();
            $configItems         = $configGroups[$configGroupID] ?? [];
            foreach ($configGroup->oItem_arr as $j => $configItem) {
                $configItemID        = $configItem->getKonfigitem();
                $configItem->fAnzahl = (float)(
                    $configGroupAmounts[$configItem->getKonfiggruppe()] ?? $configItem->getInitial()
                );
                if ($configItem->fAnzahl > $configItem->getMax() || $configItem->fAnzahl < $configItem->getMin()) {
                    $configItem->fAnzahl = $configItem->getInitial();
                }
                if ($configItemAmounts && isset($configItemAmounts[$configItem->getKonfigitem()])) {
                    $configItem->fAnzahl = (float)$configItemAmounts[$configItem->getKonfigitem()];
                }
                if ($configItem->fAnzahl <= 0) {
                    $configItem->fAnzahl = 1;
                }
                $configItem->fAnzahlWK = $configItem->fAnzahl;
                if (!$configItem->ignoreMultiplier() && !$singleProductOutput) {
                    $configItem->fAnzahlWK *= $amount;
                }
                $configItem->bAktiv = \in_array($configItemID, $configItems);

                if ($configItem->bAktiv) {
                    $config->fGesamtpreis[0] += $configItem->getPreis() * $configItem->fAnzahlWK;
                    $config->fGesamtpreis[1] += $configItem->getPreis(true) * $configItem->fAnzahlWK;
                    $configGroup->bAktiv      = true;
                    if ($configItem->getArtikel() !== null
                        && $configItem->getArtikel()->cLagerBeachten === 'Y'
                        && $config->nMinDeliveryDays < $configItem->getArtikel()->nMinDeliveryDays
                    ) {
                        $config->nMinDeliveryDays      = $configItem->getArtikel()->nMinDeliveryDays;
                        $config->nMaxDeliveryDays      = $configItem->getArtikel()->nMaxDeliveryDays;
                        $config->cEstimatedDelivery    = $configItem->getArtikel()->cEstimatedDelivery;
                        $config->Lageranzeige->nStatus = $configItem->getArtikel()->Lageranzeige->nStatus;
                    }
                }
            }
            $configGroup->oItem_arr = \array_values($configGroup->oItem_arr);
        }
        if (Frontend::getCustomerGroup()->mayViewPrices()) {
            $config->cPreisLocalized = [
                Preise::getLocalizedPriceString($config->fGesamtpreis[0]),
                Preise::getLocalizedPriceString($config->fGesamtpreis[1])
            ];
        } else {
            $config->cPreisLocalized = [Shop::Lang()->get('priceHidden')];
        }
        $config->nNettoPreise = Frontend::getCustomerGroup()->getIsMerchant();

        return $config;
    }

    /**
     * @param int       $configID
     * @param JTLSmarty $smarty
     * @former holeKonfigBearbeitenModus()
     * @since  5.0.0
     */
    public static function getEditConfigMode($configID, $smarty): void
    {
        $cart = Frontend::getCart();
        if (!isset($cart->PositionenArr[$configID]) || !Item::checkLicense()) {
            return;
        }
        $baseItem = $cart->PositionenArr[$configID];
        if ($baseItem->istKonfigVater()) {
            $configItems        = [];
            $configItemAmounts  = [];
            $configGroupAmounts = [];
            foreach ($cart->PositionenArr as $item) {
                if ($item->cUnique !== $baseItem->cUnique || !$item->istKonfigKind()) {
                    continue;
                }
                $configItem                                      = new Item($item->kKonfigitem);
                $configItems[]                                   = $configItem->getKonfigitem();
                $configItemAmounts[$configItem->getKonfigitem()] = $item->nAnzahl / $baseItem->nAnzahl;
                if ($configItem->ignoreMultiplier()) {
                    $configGroupAmounts[$configItem->getKonfiggruppe()] = $item->nAnzahl;
                } else {
                    $configGroupAmounts[$configItem->getKonfiggruppe()] = $item->nAnzahl / $baseItem->nAnzahl;
                }
            }
            $smarty->assign('fAnzahl', $baseItem->nAnzahl)
                   ->assign('kEditKonfig', $configID)
                   ->assign('nKonfigitem_arr', $configItems)
                   ->assign('nKonfigitemAnzahl_arr', $configItemAmounts)
                   ->assign('nKonfiggruppeAnzahl_arr', $configGroupAmounts);
        }
        if (isset($baseItem->WarenkorbPosEigenschaftArr)) {
            $attrValues = [];
            foreach ($baseItem->WarenkorbPosEigenschaftArr as $attr) {
                $attrValues[$attr->kEigenschaft] = (object)[
                    'kEigenschaft'                  => $attr->kEigenschaft,
                    'kEigenschaftWert'              => $attr->kEigenschaftWert,
                    'cEigenschaftWertNameLocalized' => $attr->cEigenschaftWertName[$_SESSION['cISOSprache']],
                ];
            }

            if (\count($attrValues) > 0) {
                $smarty->assign('oEigenschaftWertEdit_arr', $attrValues);
            }
        }
    }

    /**
     * @param int $productID
     * @param int $parentProductID
     * @return bool
     */
    public static function getRatedByCurrentCustomer(int $productID, int $parentProductID = 0): bool
    {
        $customerID = Frontend::getCustomer()->getID();
        $productID  = !empty($parentProductID) ? $parentProductID : $productID;
        if ($customerID <= 0) {
            return false;
        }
        $ratings = Shop::Container()->getDB()->select(
            'tbewertung',
            ['kKunde', 'kArtikel', 'kSprache'],
            [$customerID, $productID, Shop::getLanguageID()]
        );

        return !empty($ratings->kBewertung);
    }
}
