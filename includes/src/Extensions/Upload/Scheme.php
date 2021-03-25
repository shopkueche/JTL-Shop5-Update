<?php

namespace JTL\Extensions\Upload;

use JTL\DB\ReturnType;
use JTL\Nice;
use JTL\Shop;
use stdClass;

/**
 * Class Scheme
 * @package JTL\Extensions\Upload
 */
final class Scheme
{
    /**
     * @var int
     */
    public $kUploadSchema;

    /**
     * @var int
     */
    public $kCustomID;

    /**
     * @var int
     */
    public $nTyp;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cBeschreibung;

    /**
     * @var string
     */
    public $cDateiTyp;

    /**
     * @var int
     */
    public $nPflicht;

    /**
     * @var bool
     */
    private $licenseOK;

    /**
     * Scheme constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        $this->licenseOK = self::checkLicense();
        if ($id > 0 && $this->licenseOK === true) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @return bool
     */
    public static function checkLicense(): bool
    {
        return Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_UPLOADS);
    }

    /**
     * @param int $id
     */
    private function loadFromDB(int $id): void
    {
        $upload = Shop::Container()->getDB()->queryPrepared(
            'SELECT tuploadschema.kUploadSchema, tuploadschema.kCustomID, tuploadschema.nTyp, 
                tuploadschema.cDateiTyp, tuploadschema.nPflicht, tuploadschemasprache.cName, 
                tuploadschemasprache.cBeschreibung
                FROM tuploadschema
                LEFT JOIN tuploadschemasprache
                    ON tuploadschemasprache.kArtikelUpload = tuploadschema.kUploadSchema
                    AND tuploadschemasprache.kSprache = :lid
                WHERE kUploadSchema =  :uid',
            [
                'lid' => Shop::getLanguageID(),
                'uid' => $id
            ],
            ReturnType::SINGLE_OBJECT
        );

        if (isset($upload->kUploadSchema) && (int)$upload->kUploadSchema > 0) {
            self::copyMembers($upload, $this);
        }
    }

    /**
     * @return int
     * @deprecated since 5.0.0
     */
    public function save(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return 0;
    }

    /**
     * @return int
     * @deprecated since 5.0.0
     */
    public function update(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return 0;
    }

    /**
     * @return int
     * @deprecated since 5.0.0
     */
    public function delete(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return 0;
    }

    /**
     * @param int $kCustomID
     * @param int $type
     * @return stdClass[]
     */
    public function fetchAll(int $kCustomID, int $type): array
    {
        if (!$this->licenseOK) {
            return [];
        }
        $sql = $type === \UPLOAD_TYP_WARENKORBPOS
            ? ' AND kCustomID = ' . $kCustomID
            : '';

        return Shop::Container()->getDB()->queryPrepared(
            'SELECT tuploadschema.kUploadSchema, tuploadschema.kCustomID, tuploadschema.nTyp, 
                tuploadschema.cDateiTyp, tuploadschema.nPflicht, 
                IFNULL(tuploadschemasprache.cName,tuploadschema.cName ) cName,
                IFNULL(tuploadschemasprache.cBeschreibung, tuploadschema.cBeschreibung) cBeschreibung
                FROM tuploadschema
                LEFT JOIN tuploadschemasprache
                    ON tuploadschemasprache.kArtikelUpload = tuploadschema.kUploadSchema
                    AND tuploadschemasprache.kSprache = :lid
                WHERE nTyp = :tpe' . $sql,
            ['tpe' => $type, 'lid' => Shop::getLanguageID()],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param object      $objFrom
     * @param object|null $objTo
     * @return null|object
     */
    private static function copyMembers($objFrom, &$objTo = null)
    {
        if (!\is_object($objTo)) {
            $objTo = new stdClass();
        }
        foreach (\array_keys(\get_object_vars($objFrom)) as $member) {
            $objTo->$member = $objFrom->$member;
        }

        return $objTo;
    }
}
