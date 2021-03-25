<?php

namespace JTL\Checkout;

use JTL\Shop;
use stdClass;

/**
 * Class Lieferscheinposinfo
 * @package JTL\Checkout
 */
class Lieferscheinposinfo
{
    /**
     * @var int
     */
    protected $kLieferscheinPosInfo;

    /**
     * @var int
     */
    protected $kLieferscheinPos;

    /**
     * @var string
     */
    protected $cSeriennummer;

    /**
     * @var string
     */
    protected $cChargeNr;

    /**
     * @var string
     */
    protected $dMHD;

    /**
     * Lieferscheinposinfo constructor.
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
        $item = Shop::Container()->getDB()->select('tlieferscheinposinfo', 'kLieferscheinPosInfo', $id);
        if ($item !== null && $item->kLieferscheinPosInfo > 0) {
            foreach (\array_keys(\get_object_vars($item)) as $member) {
                $this->$member = $item->$member;
            }
            $this->kLieferscheinPos     = (int)$this->kLieferscheinPos;
            $this->kLieferscheinPosInfo = (int)$this->kLieferscheinPosInfo;
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

        unset($ins->kLieferscheinPosInfo);

        $kPrim = Shop::Container()->getDB()->insert('tlieferscheinposinfo', $ins);

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
        $upd                   = new stdClass();
        $upd->kLieferscheinPos = $this->getLieferscheinPos();
        $upd->cSeriennummer    = $this->getSeriennummer();
        $upd->cChargeNr        = $this->getChargeNr();
        $upd->dMHD             = $this->getMHD();

        return Shop::Container()->getDB()->update(
            'tlieferscheinposinfo',
            'kLieferscheinPosInfo',
            $this->getLieferscheinPosInfo(),
            $upd
        );
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete(
            'tlieferscheinposinfo',
            'kLieferscheinPosInfo',
            $this->getLieferscheinPosInfo()
        );
    }

    /**
     * @param int $kLieferscheinPosInfo
     * @return $this
     */
    public function setLieferscheinPosInfo(int $kLieferscheinPosInfo): self
    {
        $this->kLieferscheinPosInfo = $kLieferscheinPosInfo;

        return $this;
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
     * @param string $cSeriennummer
     * @return $this
     */
    public function setSeriennummer($cSeriennummer): self
    {
        $this->cSeriennummer = Shop::Container()->getDB()->escape($cSeriennummer);

        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function setChargeNr($cChargeNr): self
    {
        $this->cChargeNr = Shop::Container()->getDB()->escape($cChargeNr);

        return $this;
    }

    /**
     * @param string $dMHD
     * @return $this
     */
    public function setMHD($dMHD): self
    {
        $this->dMHD = Shop::Container()->getDB()->escape($dMHD);

        return $this;
    }

    /**
     * @return int
     */
    public function getLieferscheinPosInfo(): int
    {
        return (int)$this->kLieferscheinPosInfo;
    }

    /**
     * @return int
     */
    public function getLieferscheinPos(): int
    {
        return (int)$this->kLieferscheinPos;
    }

    /**
     * @return string|null
     */
    public function getSeriennummer(): ?string
    {
        return $this->cSeriennummer;
    }

    /**
     * @return string|null
     */
    public function getChargeNr(): ?string
    {
        return $this->cChargeNr;
    }

    /**
     * @return string|null
     */
    public function getMHD(): ?string
    {
        return $this->dMHD;
    }
}
