<?php

namespace JTL\Catalog;

use JTL\DB\ReturnType;
use JTL\Language\LanguageHelper;
use JTL\Media\Image;
use JTL\Media\MultiSizeImage;
use JTL\Session;
use JTL\Shop;
use stdClass;

/**
 * Class Hersteller
 * @package JTL\Catalog
 */
class Hersteller
{
    use MultiSizeImage;

    /**
     * @var int
     */
    public $kHersteller;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cSeo;

    /**
     * @var string
     */
    public $originalSeo;

    /**
     * @var string
     */
    public $cMetaTitle;

    /**
     * @var string
     */
    public $cMetaKeywords;

    /**
     * @var string
     */
    public $cMetaDescription;

    /**
     * @var string
     */
    public $cBeschreibung;

    /**
     * @var string
     */
    public $cBildpfad;

    /**
     * @var int
     */
    public $nSortNr;

    /**
     * @var string
     */
    public $cURL;

    /**
     * @var string
     */
    public $cBildpfadKlein;

    /**
     * @var string
     */
    public $cBildpfadNormal;

    /**
     * @var string
     */
    public $cBildURLKlein;

    /**
     * @var string
     */
    public $cBildURLNormal;

    /**
     * Hersteller constructor.
     *
     * @param int  $id
     * @param int  $languageID
     * @param bool $noCache     - set to true to avoid caching
     */
    public function __construct(int $id = 0, int $languageID = 0, bool $noCache = false)
    {
        $this->setImageType(Image::TYPE_MANUFACTURER);
        if ($id > 0) {
            $this->loadFromDB($id, $languageID, $noCache);
        }
    }

    /**
     * @param stdClass $obj
     * @return $this
     */
    public function loadFromObject(stdClass $obj): self
    {
        $members = \array_keys(\get_object_vars($obj));
        if (\is_array($members) && \count($members) > 0) {
            foreach ($members as $member) {
                $this->{$member} = $obj->{$member};
            }
            $this->kHersteller = (int)$this->kHersteller;
            $this->nSortNr     = (int)$this->nSortNr;
        }
        $this->loadImages($obj);

        return $this;
    }

    /**
     * @param int  $id
     * @param int  $languageID
     * @param bool $noCache
     * @return $this
     */
    public function loadFromDB(int $id, int $languageID = 0, bool $noCache = false)
    {
        // noCache param to avoid problem with de-serialization of class properties with jtl search
        $languageID = $languageID > 0 ? $languageID : Shop::getLanguageID();
        if ($languageID === 0) {
            $language   = LanguageHelper::getDefaultLanguage();
            $languageID = (int)$language->kSprache;
        }
        $cacheID   = 'manuf_' . $id . '_' . $languageID . Shop::Container()->getCache()->getBaseID();
        $cacheTags = [\CACHING_GROUP_MANUFACTURER];
        $cached    = true;
        if ($noCache === true || ($manufacturer = Shop::Container()->getCache()->get($cacheID)) === false) {
            $manufacturer = Shop::Container()->getDB()->queryPrepared(
                "SELECT thersteller.kHersteller, thersteller.cName, thersteller.cHomepage, thersteller.nSortNr, 
                    thersteller.cBildpfad, therstellersprache.cMetaTitle, therstellersprache.cMetaKeywords, 
                    therstellersprache.cMetaDescription, therstellersprache.cBeschreibung,
                    tseo.cSeo, thersteller.cSeo AS originalSeo
                    FROM thersteller
                    LEFT JOIN therstellersprache 
                        ON therstellersprache.kHersteller = thersteller.kHersteller
                        AND therstellersprache.kSprache = :langID
                    LEFT JOIN tseo 
                        ON tseo.kKey = thersteller.kHersteller
                        AND tseo.cKey = 'kHersteller'
                        AND tseo.kSprache = :langID
                    WHERE thersteller.kHersteller = :manfID",
                [
                    'langID' => $languageID,
                    'manfID' => $id
                ],
                ReturnType::SINGLE_OBJECT
            );
            $cached       = false;
            \executeHook(\HOOK_HERSTELLER_CLASS_LOADFROMDB, [
                'oHersteller' => &$manufacturer,
                'cached'      => false,
                'cacheTags'   => &$cacheTags
            ]);
            Shop::Container()->getCache()->set($cacheID, $manufacturer, $cacheTags);
        }
        if ($cached === true) {
            \executeHook(\HOOK_HERSTELLER_CLASS_LOADFROMDB, [
                'oHersteller' => &$manufacturer,
                'cached'      => true,
                'cacheTags'   => &$cacheTags
            ]);
        }
        if ($manufacturer !== false) {
            $this->loadFromObject($manufacturer);
        }

        return $this;
    }

    /**
     * @param stdClass $obj
     * @return $this
     */
    private function loadImages(stdClass $obj): self
    {
        $shopURL               = Shop::getURL() . '/';
        $imageBaseURL          = Shop::getImageBaseURL();
        $this->cBildpfadKlein  = \BILD_KEIN_HERSTELLERBILD_VORHANDEN;
        $this->cBildpfadNormal = \BILD_KEIN_HERSTELLERBILD_VORHANDEN;
        if (isset($obj->kHersteller) && $obj->kHersteller > 0) {
            // URL bauen
            $this->cURL = (isset($obj->cSeo) && \mb_strlen($obj->cSeo) > 0)
                ? $shopURL . $obj->cSeo
                : $shopURL . '?h=' . $obj->kHersteller;
        }
        if (\mb_strlen($this->cBildpfad) > 0) {
            $this->cBildpfadKlein  = \PFAD_HERSTELLERBILDER_KLEIN . $this->cBildpfad;
            $this->cBildpfadNormal = \PFAD_HERSTELLERBILDER_NORMAL . $this->cBildpfad;
            $this->generateAllImageSizes(true, 1, $this->cBildpfad);
        }
        $this->cBildURLKlein  = $imageBaseURL . $this->cBildpfadKlein;
        $this->cBildURLNormal = $imageBaseURL . $this->cBildpfadNormal;

        return $this;
    }

    /**
     * @param bool $productLookup
     * @return array
     */
    public static function getAll(bool $productLookup = true): array
    {
        $sqlWhere   = '';
        $languageID = Shop::getLanguageID();
        if ($productLookup) {
            $sqlWhere = ' WHERE EXISTS (
                            SELECT 1
                            FROM tartikel
                            WHERE tartikel.kHersteller = thersteller.kHersteller
                                ' . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL() . '
                                AND NOT EXISTS (
                                SELECT 1 FROM tartikelsichtbarkeit
                                WHERE tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                                    AND tartikelsichtbarkeit.kKundengruppe = ' .
                Session\Frontend::getCustomerGroup()->getID() .
                ')
                        )';
        }
        $items   = Shop::Container()->getDB()->query(
            'SELECT thersteller.kHersteller, thersteller.cName, thersteller.cHomepage, thersteller.nSortNr, 
                thersteller.cBildpfad, therstellersprache.cMetaTitle, therstellersprache.cMetaKeywords, 
                therstellersprache.cMetaDescription, therstellersprache.cBeschreibung,
                tseo.cSeo, thersteller.cSeo AS originalSeo
                FROM thersteller
                LEFT JOIN therstellersprache 
                    ON therstellersprache.kHersteller = thersteller.kHersteller
                    AND therstellersprache.kSprache = ' . $languageID . "
                LEFT JOIN tseo 
                    ON tseo.kKey = thersteller.kHersteller
                    AND tseo.cKey = 'kHersteller'
                    AND tseo.kSprache = " . $languageID . $sqlWhere . '
                ORDER BY thersteller.nSortNr, thersteller.cName',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $results = [];
        foreach ($items as $item) {
            $manufacturer = new self(0, 0, true);
            $manufacturer->loadFromObject($item);
            $results[] = $manufacturer;
        }

        return $results;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->cName;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return (int)$this->kHersteller;
    }
}
