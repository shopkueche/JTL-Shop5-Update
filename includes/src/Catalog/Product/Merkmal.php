<?php

namespace JTL\Catalog\Product;

use JTL\DB\ReturnType;
use JTL\Language\LanguageHelper;
use JTL\Media\Image;
use JTL\Media\MultiSizeImage;
use JTL\Shop;

/**
 * Class Merkmal
 * @package JTL\Catalog\Product
 */
class Merkmal
{
    use MultiSizeImage;

    /**
     * @var int
     */
    public $kMerkmal;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cBildpfad;

    /**
     * @var int
     */
    public $nSort;

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
    public $cBildpfadGross;

    /**
     * @var string
     */
    public $nBildGrossVorhanden;

    /**
     * @var string
     */
    public $cBildpfadNormal;

    /**
     * @var array
     */
    public $oMerkmalWert_arr = [];

    /**
     * @var string
     */
    public $cTyp;

    /**
     * @var string
     */
    public $cBildURLKlein;

    /**
     * @var string
     */
    public $cBildURLGross;

    /**
     * @var string
     */
    public $cBildURLNormal;

    /**
     * @var int
     */
    public $kSprache;

    /**
     * Merkmal constructor.
     * @param int  $id
     * @param bool $getValues
     * @param int  $languageID
     */
    public function __construct(int $id = 0, bool $getValues = false, int $languageID = 0)
    {
        $this->setImageType(Image::TYPE_CHARACTERISTIC);
        if ($id > 0) {
            $this->loadFromDB($id, $getValues, $languageID);
        }
    }

    /**
     * @param int  $id
     * @param bool $getValues
     * @param int  $languageID
     * @return Merkmal
     */
    public function loadFromDB(int $id, bool $getValues = false, int $languageID = 0): self
    {
        $languageID     = $languageID === 0 ? Shop::getLanguageID() : $languageID;
        $cacheID        = 'mm_' . $id . '_' . $this->kSprache;
        $this->kSprache = $languageID;
        if ($getValues === false && Shop::has($cacheID)) {
            foreach (\get_object_vars(Shop::get($cacheID)) as $k => $v) {
                $this->$k = $v;
            }

            return $this;
        }
        $defaultLanguageID = LanguageHelper::getDefaultLanguage()->kSprache;
        if ($languageID !== $defaultLanguageID) {
            $selectSQL = 'COALESCE(fremdSprache.cName, standardSprache.cName) AS cName';
            $joinSQL   = 'INNER JOIN tmerkmalsprache AS standardSprache 
                            ON standardSprache.kMerkmal = tmerkmal.kMerkmal
                            AND standardSprache.kSprache = ' . $defaultLanguageID . '
                        LEFT JOIN tmerkmalsprache AS fremdSprache 
                            ON fremdSprache.kMerkmal = tmerkmal.kMerkmal
                            AND fremdSprache.kSprache = ' . $languageID;
        } else {
            $selectSQL = 'tmerkmalsprache.cName';
            $joinSQL   = 'INNER JOIN tmerkmalsprache ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                            AND tmerkmalsprache.kSprache = ' . $languageID;
        }
        $data = Shop::Container()->getDB()->query(
            'SELECT tmerkmal.kMerkmal, tmerkmal.nSort, tmerkmal.cBildpfad, tmerkmal.cTyp, ' .
                $selectSQL . '
                FROM tmerkmal ' .
                $joinSQL . '
                WHERE tmerkmal.kMerkmal = ' . $id . '
                ORDER BY tmerkmal.nSort',
            ReturnType::SINGLE_OBJECT
        );
        if (isset($data->kMerkmal) && $data->kMerkmal > 0) {
            foreach (\array_keys(\get_object_vars($data)) as $cMember) {
                $this->$cMember = $data->$cMember;
            }
            $this->kMerkmal = (int)$this->kMerkmal;
            $this->nSort    = (int)$this->nSort;
        }
        if ($getValues && $this->kMerkmal > 0) {
            if ($languageID !== $defaultLanguageID) {
                $joinValueSQL = 'INNER JOIN tmerkmalwertsprache AS standardSprache 
                                        ON standardSprache.kMerkmalWert = tmw.kMerkmalWert
                                        AND standardSprache.kSprache = ' . $defaultLanguageID . '
                                    LEFT JOIN tmerkmalwertsprache AS fremdSprache 
                                        ON fremdSprache.kMerkmalWert = tmw.kMerkmalWert
                                        AND fremdSprache.kSprache = ' . $languageID;
                $orderSQL     = ' ORDER BY tmw.nSort, COALESCE(fremdSprache.cWert, standardSprache.cWert)';
            } else {
                $joinValueSQL = 'INNER JOIN tmerkmalwertsprache AS standardSprache
                                        ON standardSprache.kMerkmalWert = tmw.kMerkmalWert
                                        AND standardSprache.kSprache = ' . $languageID;
                $orderSQL     = ' ORDER BY tmw.nSort, standardSprache.cWert';
            }
            $tmpAttributes          = Shop::Container()->getDB()->query(
                'SELECT tmw.kMerkmalWert
                    FROM tmerkmalwert tmw ' .  $joinValueSQL . '
                    WHERE kMerkmal = ' . $this->kMerkmal . $orderSQL,
                ReturnType::ARRAY_OF_OBJECTS
            );
            $this->oMerkmalWert_arr = [];
            foreach ($tmpAttributes as $oMerkmalWertTMP) {
                $this->oMerkmalWert_arr[] = new MerkmalWert((int)$oMerkmalWertTMP->kMerkmalWert, $this->kSprache);
            }
        }
        $imageBaseURL = Shop::getImageBaseURL();

        $this->cBildpfadKlein      = \BILD_KEIN_MERKMALBILD_VORHANDEN;
        $this->nBildKleinVorhanden = 0;
        $this->cBildpfadGross      = \BILD_KEIN_MERKMALBILD_VORHANDEN;
        $this->nBildGrossVorhanden = 0;
        if (\mb_strlen($this->cBildpfad) > 0) {
            if (\file_exists(\PFAD_MERKMALBILDER_KLEIN . $this->cBildpfad)) {
                $this->cBildpfadKlein      = \PFAD_MERKMALBILDER_KLEIN . $this->cBildpfad;
                $this->nBildKleinVorhanden = 1;
            }
            if (\file_exists(\PFAD_MERKMALBILDER_NORMAL . $this->cBildpfad)) {
                $this->cBildpfadNormal     = \PFAD_MERKMALBILDER_NORMAL . $this->cBildpfad;
                $this->nBildGrossVorhanden = 1;
            }
            $this->generateAllImageSizes(true, 1, $this->cBildpfad);
        }
        $this->cBildURLGross       = $imageBaseURL . $this->cBildpfadGross;
        $this->cBildURLNormal      = $imageBaseURL . $this->cBildpfadNormal;
        $this->cBildURLKlein       = $imageBaseURL . $this->cBildpfadKlein;
        $this->kMerkmal            = (int)$this->kMerkmal;
        $this->nSort               = (int)$this->nSort;
        $this->nBildKleinVorhanden = (int)$this->nBildKleinVorhanden;
        $this->nBildGrossVorhanden = (int)$this->nBildGrossVorhanden;
        $this->kSprache            = (int)$this->kSprache;

        \executeHook(\HOOK_MERKMAL_CLASS_LOADFROMDB, ['instance' => $this]);
        Shop::set($cacheID, $this);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getID()
    {
        return $this->kMerkmal;
    }
}
