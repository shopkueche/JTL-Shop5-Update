<?php

namespace JTL\Helpers;

use JTL\Catalog\Hersteller;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Language\LanguageHelper;
use JTL\Shop;

/**
 * Class Manufacturer
 * @package JTL\Helpers
 */
class Manufacturer
{
    /**
     * @var Manufacturer
     */
    private static $instance;

    /**
     * @var string
     */
    public $cacheID;

    /**
     * @var array
     */
    public $manufacturers;

    /**
     * @var int
     */
    private static $langID;

    /**
     * HerstellerHelper constructor.
     */
    public function __construct()
    {
        $lagerfilter   = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
        $this->cacheID = 'manuf_' . Shop::Container()->getCache()->getBaseID() .
            ($lagerfilter !== '' ? \md5($lagerfilter) : '');
        self::$langID  = Shop::getLanguageID();
        if (self::$langID <= 0) {
            if (Shop::getLanguageID() > 0) {
                self::$langID = Shop::getLanguageID();
            } else {
                $_lang        = LanguageHelper::getDefaultLanguage();
                self::$langID = (int)$_lang->kSprache;
            }
        }
        $this->manufacturers = $this->getManufacturers();
        self::$instance      = $this;
    }

    /**
     * @return Manufacturer
     */
    public static function getInstance(): self
    {
        return (self::$instance === null || Shop::getLanguageID() !== self::$langID)
            ? new self()
            : self::$instance;
    }

    /**
     * @return array
     */
    public function getManufacturers(): array
    {
        if ($this->manufacturers !== null) {
            return $this->manufacturers;
        }
        if (($manufacturers = Shop::Container()->getCache()->get($this->cacheID)) === false) {
            $lagerfilter   = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $manufacturers = Shop::Container()->getDB()->queryPrepared(
                'SELECT thersteller.kHersteller, thersteller.cName, thersteller.cHomepage, thersteller.nSortNr, 
                        thersteller.cBildpfad, therstellersprache.cMetaTitle, therstellersprache.cMetaKeywords, 
                        therstellersprache.cMetaDescription, therstellersprache.cBeschreibung,
                        tseo.cSeo, thersteller.cSeo AS originalSeo
                    FROM thersteller
                    LEFT JOIN therstellersprache 
                        ON therstellersprache.kHersteller = thersteller.kHersteller
                        AND therstellersprache.kSprache = :lid
                    LEFT JOIN tseo 
                        ON tseo.kKey = thersteller.kHersteller
                        AND tseo.cKey = :skey
                        AND tseo.kSprache = :lid
                    WHERE EXISTS (
                        SELECT 1
                        FROM tartikel
                        WHERE tartikel.kHersteller = thersteller.kHersteller ' . $lagerfilter . '
                            AND NOT EXISTS (
                                SELECT 1 FROM tartikelsichtbarkeit
                                WHERE tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                                    AND tartikelsichtbarkeit.kKundengruppe = :cgid
                                )
                        )
                    ORDER BY thersteller.nSortNr, thersteller.cName',
                [
                    'skey' => 'kHersteller',
                    'lid'  => self::$langID,
                    'cgid' => CustomerGroup::getDefaultGroupID()
                ],
                ReturnType::ARRAY_OF_OBJECTS
            );
            $shopURL       = Shop::getURL() . '/';
            $imageBaseURL  = Shop::getImageBaseURL();
            foreach ($manufacturers as &$manufacturer) {
                if (!empty($manufacturer->cBildpfad)) {
                    $manufacturer->cBildpfadKlein  = \PFAD_HERSTELLERBILDER_KLEIN . $manufacturer->cBildpfad;
                    $manufacturer->cBildpfadNormal = \PFAD_HERSTELLERBILDER_NORMAL . $manufacturer->cBildpfad;
                } else {
                    $manufacturer->cBildpfadKlein  = \BILD_KEIN_HERSTELLERBILD_VORHANDEN;
                    $manufacturer->cBildpfadNormal = \BILD_KEIN_HERSTELLERBILD_VORHANDEN;
                }
                $manufacturer->cBildURLKlein  = $imageBaseURL . $manufacturer->cBildpfadKlein;
                $manufacturer->cBildURLNormal = $imageBaseURL . $manufacturer->cBildpfadKlein;
                $manufacturer->cURLFull       = $shopURL . $manufacturer->cSeo;
                $instance                     = new Hersteller();
                $manufacturer                 = $instance->loadFromObject($manufacturer);
            }
            unset($manufacturer);
            $cacheTags = [\CACHING_GROUP_MANUFACTURER, \CACHING_GROUP_CORE];
            \executeHook(\HOOK_GET_MANUFACTURERS, [
                'cached'        => false,
                'cacheTags'     => &$cacheTags,
                'manufacturers' => &$manufacturers
            ]);
            Shop::Container()->getCache()->set($this->cacheID, $manufacturers, $cacheTags);
        } else {
            \executeHook(\HOOK_GET_MANUFACTURERS, [
                'cached'        => true,
                'cacheTags'     => [],
                'manufacturers' => &$manufacturers
            ]);
        }
        $this->manufacturers = $manufacturers;

        return $this->manufacturers;
    }

    /**
     * @param string        $attribute
     * @param string|int    $value
     * @param callable|null $callback
     * @return mixed
     * @since 5.0.0
     */
    public static function getDataByAttribute(string $attribute, $value, callable $callback = null)
    {
        $res = Shop::Container()->getDB()->select('thersteller', $attribute, $value);

        return \is_callable($callback)
            ? $callback($res)
            : $res;
    }

    /**
     * @param string        $attribute
     * @param string|int    $value
     * @param callable|null $callback
     * @return mixed
     * @since 5.0.0
     */
    public static function getManufacturerByAttribute(string $attribute, $value, callable $callback = null)
    {
        $mf = ($res = self::getDataByAttribute($attribute, $value)) !== null
            ? new Hersteller($res->kHersteller)
            : null;

        return \is_callable($callback)
            ? $callback($mf)
            : $mf;
    }
}
