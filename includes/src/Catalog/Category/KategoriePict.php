<?php

namespace JTL\Catalog\Category;

use JTL\Helpers\GeneralObject;
use JTL\Shop;

/**
 * Class KategoriePict
 * @package JTL\Catalog\Category
 */
class KategoriePict
{
    /**
     * @var int
     */
    public $kKategoriePict;

    /**
     * @var int
     */
    public $kKategorie;

    /**
     * @var string
     */
    public $cPfad;

    /**
     * @var string
     */
    public $cType;

    /**
     * KategoriePict constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @param int $id
     * @return $this
     */
    public function loadFromDB(int $id): self
    {
        $obj = Shop::Container()->getDB()->select('tkategoriepict', 'kKategoriePict', $id);
        foreach (\get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        return Shop::Container()->getDB()->insert('tkategoriepict', GeneralObject::copyMembers($this));
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = GeneralObject::copyMembers($this);

        return Shop::Container()->getDB()->update('tkategoriepict', 'kKategoriePict', $obj->kKategoriePict, $obj);
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setzePostDaten(): bool
    {
        return false;
    }
}
