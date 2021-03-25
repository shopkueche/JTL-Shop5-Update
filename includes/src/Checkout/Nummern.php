<?php

namespace JTL\Checkout;

use JTL\Shop;
use stdClass;

/**
 * Class Nummern
 * @package JTL\Checkout
 */
class Nummern
{
    /**
     * @var int
     */
    protected $nNummer;

    /**
     * @var int
     */
    protected $nArt;

    /**
     * @var string
     */
    protected $dAktualisiert;

    /**
     * Nummern constructor.
     *
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
    private function loadFromDB(int $id = 0): self
    {
        $item = Shop::Container()->getDB()->select('tnummern', 'nArt', $id);
        if ($item !== null && $item->nArt > 0) {
            $this->nNummer       = (int)$item->nNummer;
            $this->nArt          = (int)$item->nArt;
            $this->dAktualisiert = $item->dAktualisiert;
        }

        return $this;
    }

    /**
     * @param bool $primary
     * @return bool|int
     */
    public function save(bool $primary = true)
    {
        $ins = new stdClass();
        foreach (\array_keys(\get_object_vars($this)) as $member) {
            $ins->$member = $this->$member;
        }
        $kPrim = Shop::Container()->getDB()->insert('tnummern', $ins);
        if ($kPrim > 0) {
            return $primary ? $kPrim : true;
        }

        return false;
    }

    /**
     * @param bool $bDate
     * @return int
     */
    public function update(bool $bDate = true): int
    {
        if ($bDate) {
            $this->setAktualisiert('NOW()');
        }
        $upd                = new stdClass();
        $upd->nNummer       = $this->nNummer;
        $upd->dAktualisiert = $this->dAktualisiert;

        return Shop::Container()->getDB()->update('tnummern', 'nArt', $this->nArt, $upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tnummern', 'nArt', $this->nArt);
    }

    /**
     * @param int $nNummer
     * @return $this
     */
    public function setNummer(int $nNummer): self
    {
        $this->nNummer = $nNummer;

        return $this;
    }

    /**
     * @param int $nArt
     * @return $this
     */
    public function setArt(int $nArt): self
    {
        $this->nArt = $nArt;

        return $this;
    }

    /**
     * @param string $dAktualisiert
     * @return $this
     */
    public function setAktualisiert($dAktualisiert): self
    {
        $this->dAktualisiert = \mb_convert_case($dAktualisiert, \MB_CASE_UPPER) === 'NOW()'
            ? \date('Y-m-d H:i:s')
            : Shop::Container()->getDB()->escape($dAktualisiert);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getNummer(): ?int
    {
        return $this->nNummer;
    }

    /**
     * @return int|null
     */
    public function getArt(): ?int
    {
        return $this->nArt;
    }

    /**
     * @return string|null
     */
    public function getAktualisiert(): ?string
    {
        return $this->dAktualisiert;
    }
}
