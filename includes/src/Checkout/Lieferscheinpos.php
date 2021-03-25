<?php

namespace JTL\Checkout;

use JTL\Shop;
use stdClass;

/**
 * Class Lieferscheinpos
 * @package JTL\Checkout
 */
class Lieferscheinpos
{
    /**
     * @var int
     */
    protected $kLieferscheinPos;

    /**
     * @var int
     */
    protected $kLieferschein;

    /**
     * @var int
     */
    protected $kBestellPos;

    /**
     * @var int
     */
    protected $kWarenlager;

    /**
     * @var float|int
     */
    protected $fAnzahl;

    /**
     * @var array
     */
    public $oLieferscheinPosInfo_arr;

    /**
     * Lieferscheinpos constructor.
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
        $item = Shop::Container()->getDB()->select('tlieferscheinpos', 'kLieferscheinPos', $id);
        if ($item !== null && $item->kLieferscheinPos > 0) {
            foreach (\array_keys(\get_object_vars($item)) as $member) {
                $this->$member = $item->$member;
            }
            $this->kBestellPos      = (int)$this->kBestellPos;
            $this->kLieferschein    = (int)$this->kLieferschein;
            $this->kLieferscheinPos = (int)$this->kLieferscheinPos;
            $this->kWarenlager      = (int)$this->kWarenlager;
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

        unset($ins->kLieferscheinPos, $ins->oLieferscheinPosInfo_arr);
        $kPrim = Shop::Container()->getDB()->insert('tlieferscheinpos', $ins);

        if ($kPrim > 0) {
            return $primary ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $upd                = new stdClass();
        $upd->kLieferschein = $this->getLieferschein();
        $upd->kBestellPos   = $this->getBestellPos();
        $upd->kWarenlager   = $this->getWarenlager();
        $upd->fAnzahl       = $this->getAnzahl();

        return Shop::Container()->getDB()->update(
            'tlieferscheinpos',
            'kLieferscheinPos',
            $this->getLieferscheinPos(),
            $upd
        );
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tlieferscheinpos', 'kLieferscheinPos', $this->getLieferscheinPos());
    }

    /**
     * @param int $kLieferscheinPos
     * @return $this
     */
    public function setLieferscheinPos(int $kLieferscheinPos): self
    {
        $this->kLieferscheinPos = $kLieferscheinPos;

        return $this;
    }

    /**
     * @param int $kLieferschein
     * @return $this
     */
    public function setLieferschein(int $kLieferschein): self
    {
        $this->kLieferschein = $kLieferschein;

        return $this;
    }

    /**
     * @param int $kBestellPos
     * @return $this
     */
    public function setBestellPos(int $kBestellPos): self
    {
        $this->kBestellPos = $kBestellPos;

        return $this;
    }

    /**
     * @param int $kWarenlager
     * @return $this
     */
    public function setWarenlager(int $kWarenlager): self
    {
        $this->kWarenlager = $kWarenlager;

        return $this;
    }

    /**
     * @param float $fAnzahl
     * @return $this
     */
    public function setAnzahl($fAnzahl): self
    {
        $this->fAnzahl = (float)$fAnzahl;

        return $this;
    }

    /**
     * @return int
     */
    public function getLieferscheinPos(): int
    {
        return (int)$this->kLieferscheinPos;
    }

    /**
     * @return int
     */
    public function getLieferschein(): int
    {
        return (int)$this->kLieferschein;
    }

    /**
     * @return int
     */
    public function getBestellPos(): int
    {
        return (int)$this->kBestellPos;
    }

    /**
     * @return int
     */
    public function getWarenlager(): int
    {
        return (int)$this->kWarenlager;
    }

    /**
     * @return float|int
     */
    public function getAnzahl()
    {
        return $this->fAnzahl;
    }
}
