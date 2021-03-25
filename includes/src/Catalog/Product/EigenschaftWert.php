<?php

namespace JTL\Catalog\Product;

use JTL\Helpers\GeneralObject;
use JTL\Shop;

/**
 * Class EigenschaftWert
 * @package JTL\Catalog\Product
 */
class EigenschaftWert
{
    /**
     * @var int
     */
    public $kEigenschaftWert;

    /**
     * @var int
     */
    public $kEigenschaft;

    /**
     * @var float
     */
    public $fAufpreisNetto;

    /**
     * @var float
     */
    public $fGewichtDiff;

    /**
     * @var float
     */
    public $fLagerbestand;

    /**
     * @var float
     */
    public $fPackeinheit;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var float
     */
    public $fAufpreis;

    /**
     * @var int
     */
    public $nSort;

    /**
     * EigenschaftWert constructor.
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
        if ($id > 0) {
            $obj = Shop::Container()->getDB()->select('teigenschaftwert', 'kEigenschaftWert', $id);
            if (isset($obj->kEigenschaftWert) && $obj->kEigenschaftWert > 0) {
                foreach (\get_object_vars($obj) as $k => $v) {
                    $this->$k = $v;
                }
                $this->kEigenschaft     = (int)$this->kEigenschaft;
                $this->kEigenschaftWert = (int)$this->kEigenschaftWert;
                $this->nSort            = (int)$this->nSort;
                if ($this->fPackeinheit == 0) {
                    $this->fPackeinheit = 1;
                }
            }
            \executeHook(\HOOK_EIGENSCHAFTWERT_CLASS_LOADFROMDB);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        $obj = GeneralObject::copyMembers($this);
        unset($obj->fAufpreis);

        return Shop::Container()->getDB()->insert('teigenschaftwert', $obj);
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = GeneralObject::copyMembers($this);
        unset($obj->fAufpreis);

        return Shop::Container()->getDB()->update('teigenschaftwert', 'kEigenschaftWert', $obj->kEigenschaftWert, $obj);
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
}
