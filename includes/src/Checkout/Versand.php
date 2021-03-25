<?php

namespace JTL\Checkout;

use JTL\Shop;
use stdClass;

/**
 * Class Versand
 * @package JTL\Checkout
 */
class Versand
{
    /**
     * @var int
     */
    protected $kVersand;

    /**
     * @var int
     */
    protected $kLieferschein;

    /**
     * @var string
     */
    protected $cLogistik;

    /**
     * @var string
     */
    protected $cLogistikURL;

    /**
     * @var string
     */
    protected $cIdentCode;

    /**
     * @var string
     */
    protected $cHinweis;

    /**
     * @var string
     */
    protected $dErstellt;

    /**
     * @var object
     */
    protected $oData;

    /**
     * Constructor
     *
     * @param int         $id
     * @param null|object $oData
     */
    public function __construct(int $id = 0, $oData = null)
    {
        if ($id > 0) {
            $this->loadFromDB($id, $oData);
        }
    }

    /**
     * @param int         $id
     * @param null|object $oData
     */
    private function loadFromDB(int $id = 0, $oData = null): void
    {
        $item = Shop::Container()->getDB()->select('tversand', 'kVersand', $id);

        $this->oData = $oData;

        if (!empty($item->kVersand)) {
            foreach (\array_keys(\get_object_vars($item)) as $member) {
                $this->$member = $item->$member;
            }
        }
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
        unset($ins->kVersand);

        $kPrim = Shop::Container()->getDB()->insert('tversand', $ins);

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
        $upd->kLieferschein = (int)$this->kLieferschein;
        $upd->cLogistik     = $this->cLogistik;
        $upd->cLogistikURL  = $this->cLogistikURL;
        $upd->cIdentCode    = $this->cIdentCode;
        $upd->cHinweis      = $this->cHinweis;
        $upd->dErstellt     = $this->dErstellt;

        return Shop::Container()->getDB()->update('tversand', 'kVersand', (int)$this->kVersand, $upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tversand', 'kVersand', (int)$this->kVersand);
    }

    /**
     * @param int $kVersand
     * @return $this
     */
    public function setVersand(int $kVersand): self
    {
        $this->kVersand = $kVersand;

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
     * @param string $cLogistik
     * @return $this
     */
    public function setLogistik($cLogistik): self
    {
        $this->cLogistik = $cLogistik;

        return $this;
    }

    /**
     * @param string $cLogistikURL
     * @return $this
     */
    public function setLogistikURL($cLogistikURL): self
    {
        $this->cLogistikURL = $cLogistikURL;

        return $this;
    }

    /**
     * @param string $cIdentCode
     * @return $this
     */
    public function setIdentCode($cIdentCode): self
    {
        $this->cIdentCode = $cIdentCode;

        return $this;
    }

    /**
     * @param string $cHinweis
     * @return $this
     */
    public function setHinweis($cHinweis): self
    {
        $this->cHinweis = $cHinweis;

        return $this;
    }

    /**
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt): self
    {
        $this->dErstellt = $dErstellt;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getVersand(): ?int
    {
        return $this->kVersand;
    }

    /**
     * @return int|null
     */
    public function getLieferschein(): ?int
    {
        return $this->kLieferschein;
    }

    /**
     * @return string|null
     */
    public function getLogistik(): ?string
    {
        return $this->cLogistik;
    }

    /**
     * @return string|null
     */
    public function getLogistikURL(): ?string
    {
        return $this->cLogistikURL;
    }

    /**
     * @return string|null
     */
    public function getIdentCode(): ?string
    {
        return $this->cIdentCode;
    }

    /**
     * @return string|null
     */
    public function getHinweis(): ?string
    {
        return $this->cHinweis;
    }

    /**
     * @return string|null
     */
    public function getErstellt(): ?string
    {
        return $this->dErstellt;
    }

    /**
     * @return string|null
     */
    public function getLogistikVarUrl(): ?string
    {
        $cVarUrl = $this->cLogistikURL;

        if (isset($this->oData->cPLZ)) {
            $cVarUrl = \str_replace(
                ['#PLZ#', '#IdentCode#'],
                [$this->oData->cPLZ, $this->cIdentCode],
                $this->cLogistikURL
            );
        }

        return $cVarUrl;
    }
}
