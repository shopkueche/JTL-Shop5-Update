<?php

namespace JTL\Catalog\Product;

use JTL\DB\ReturnType;
use JTL\Helpers\URL;
use JTL\Language\LanguageHelper;
use JTL\Media\Image;
use JTL\Media\MultiSizeImage;
use JTL\Shop;

/**
 * Class MerkmalWert
 * @package JTL\Catalog\Product
 */
class MerkmalWert
{
    use MultiSizeImage;

    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var int
     */
    public $kMerkmalWert;

    /**
     * @var int
     */
    public $kMerkmal;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var string
     */
    public $cWert;

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
    public $cMetaTitle;

    /**
     * @var string
     */
    public $cBeschreibung;

    /**
     * @var string
     */
    public $cSeo;

    /**
     * @var string
     */
    public $cURL;

    /**
     * @var string
     */
    public $cURLFull;

    /**
     * @var string
     */
    public $cBildpfad;

    /**
     * @var string
     */
    public $cBildpfadKlein;

    /**
     * @var string
     */
    public $nBildKleinVorhanden;

    /**
     * @var string
     */
    public $cBildpfadNormal;

    /**
     * @var string
     */
    public $nBildNormalVorhanden;

    /**
     * @var string
     */
    public $cBildURLKlein;

    /**
     * @var string
     */
    public $cBildURLNormal;

    /**
     * MerkmalWert constructor.
     * @param int $id
     * @param int $languageID
     */
    public function __construct(int $id = 0, int $languageID = 0)
    {
        $this->setImageType(Image::TYPE_CHARACTERISTIC_VALUE);
        if ($id > 0) {
            $this->loadFromDB($id, $languageID);
        }
    }

    /**
     * @param int $id
     * @param int $languageID
     * @return $this
     */
    public function loadFromDB(int $id, int $languageID = 0): self
    {
        $languageID = $languageID === 0 ? Shop::getLanguageID() : $languageID;
        $cacheID    = 'mmw_' . $id . '_' . $languageID;
        if (Shop::has($cacheID)) {
            foreach (\get_object_vars(Shop::get($cacheID)) as $k => $v) {
                $this->$k = $v;
            }

            return $this;
        }
        $defaultLanguageID = LanguageHelper::getDefaultLanguage()->kSprache;
        if ($languageID !== $defaultLanguageID) {
            $selectSQL = 'COALESCE(fremdSprache.kSprache, standardSprache.kSprache) AS kSprache, 
                        COALESCE(fremdSprache.cWert, standardSprache.cWert) AS cWert,
                        COALESCE(fremdSprache.cMetaTitle, standardSprache.cMetaTitle) AS cMetaTitle, 
                        COALESCE(fremdSprache.cMetaKeywords, standardSprache.cMetaKeywords) AS cMetaKeywords,
                        COALESCE(fremdSprache.cMetaDescription, standardSprache.cMetaDescription) AS cMetaDescription, 
                        COALESCE(fremdSprache.cBeschreibung, standardSprache.cBeschreibung) AS cBeschreibung,
                        COALESCE(fremdSprache.cSeo, standardSprache.cSeo) AS cSeo';
            $joinSQL   = 'INNER JOIN tmerkmalwertsprache AS standardSprache 
                            ON standardSprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND standardSprache.kSprache = ' . $defaultLanguageID . '
                        LEFT JOIN tmerkmalwertsprache AS fremdSprache 
                            ON fremdSprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND fremdSprache.kSprache = ' . $languageID;
        } else {
            $selectSQL = 'tmerkmalwertsprache.kSprache, tmerkmalwertsprache.cWert, tmerkmalwertsprache.cMetaTitle,
                        tmerkmalwertsprache.cMetaKeywords, tmerkmalwertsprache.cMetaDescription,
                        tmerkmalwertsprache.cBeschreibung, tmerkmalwertsprache.cSeo';
            $joinSQL   = 'INNER JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND tmerkmalwertsprache.kSprache = ' . $languageID;
        }
        $data = Shop::Container()->getDB()->query(
            'SELECT tmerkmalwert.*, ' . $selectSQL . '
                FROM tmerkmalwert ' .  $joinSQL . '
                WHERE tmerkmalwert.kMerkmalWert = ' . $id,
            ReturnType::SINGLE_OBJECT
        );
        if (isset($data->kMerkmalWert) && $data->kMerkmalWert > 0) {
            foreach (\array_keys(\get_object_vars($data)) as $member) {
                $this->$member = $data->$member;
            }
            $this->cURL     = URL::buildURL($this, \URLART_MERKMAL);
            $this->cURLFull = URL::buildURL($this, \URLART_MERKMAL, true);
            \executeHook(\HOOK_MERKMALWERT_CLASS_LOADFROMDB, ['oMerkmalWert' => &$this]);
        }
        $imageBaseURL = Shop::getImageBaseURL();

        $this->cBildpfadKlein       = \BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
        $this->nBildKleinVorhanden  = 0;
        $this->cBildpfadNormal      = \BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
        $this->nBildNormalVorhanden = 0;
        $this->nSort                = (int)$this->nSort;
        $this->kSprache             = (int)$this->kSprache;
        $this->kMerkmal             = (int)$this->kMerkmal;
        $this->kMerkmalWert         = (int)$this->kMerkmalWert;
        if ($this->cBildpfad !== null && \mb_strlen($this->cBildpfad) > 0) {
            if (\file_exists(\PFAD_MERKMALWERTBILDER_KLEIN . $this->cBildpfad)) {
                $this->cBildpfadKlein      = \PFAD_MERKMALWERTBILDER_KLEIN . $this->cBildpfad;
                $this->nBildKleinVorhanden = 1;
            }
            if (\file_exists(\PFAD_MERKMALWERTBILDER_NORMAL . $this->cBildpfad)) {
                $this->cBildpfadNormal      = \PFAD_MERKMALWERTBILDER_NORMAL . $this->cBildpfad;
                $this->nBildNormalVorhanden = 1;
            }
            $this->generateAllImageSizes(true, 1, $this->cBildpfad);
        }
        $this->cBildURLKlein  = $imageBaseURL . $this->cBildpfadKlein;
        $this->cBildURLNormal = $imageBaseURL . $this->cBildpfadNormal;
        Shop::set($cacheID, $this);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getID()
    {
        return $this->kMerkmalWert;
    }
}
