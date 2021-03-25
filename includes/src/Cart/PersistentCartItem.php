<?php

namespace JTL\Cart;

use JTL\Catalog\Product\Artikel;
use JTL\Shop;
use stdClass;

/**
 * Class PersistentCartItem
 * @package JTL\Cart
 */
class PersistentCartItem
{
    /**
     * @var int
     */
    public $kWarenkorbPersPos;

    /**
     * @var int
     */
    public $kWarenkorbPers;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var float
     */
    public $fAnzahl;

    /**
     * @var string
     */
    public $cArtikelName;

    /**
     * @var string
     */
    public $dHinzugefuegt;

    /**
     * @var string
     */
    public $dHinzugefuegt_de;

    /**
     * @var string
     */
    public $cUnique;

    /**
     * @var string
     */
    public $cResponsibility;

    /**
     * @var int
     */
    public $kKonfigitem;

    /**
     * @var int
     */
    public $nPosTyp;

    /**
     * @var array
     */
    public $oWarenkorbPersPosEigenschaft_arr = [];

    /**
     * @var string
     */
    public $cKommentar;

    /**
     * @var Artikel
     */
    public $Artikel;

    /**
     * PersistentCartItem constructor.
     * @param int          $productID
     * @param string       $productName
     * @param float|string $qty
     * @param int          $cartItemID
     * @param string       $unique
     * @param int          $configItemID
     * @param int|string   $type
     * @param string       $responsibility
     */
    public function __construct(
        int $productID,
        $productName,
        $qty,
        int $cartItemID,
        $unique = '',
        int $configItemID = 0,
        int $type = \C_WARENKORBPOS_TYP_ARTIKEL,
        string $responsibility = 'core'
    ) {
        $this->kArtikel        = $productID;
        $this->cArtikelName    = $productName;
        $this->fAnzahl         = $qty;
        $this->dHinzugefuegt   = 'NOW()';
        $this->kWarenkorbPers  = $cartItemID;
        $this->cUnique         = $unique;
        $this->cResponsibility = !empty($responsibility) ? $responsibility : 'core';
        $this->kKonfigitem     = $configItemID;
        $this->nPosTyp         = $type;
    }

    /**
     * @param array $attrValues
     * @return $this
     */
    public function erstellePosEigenschaften(array $attrValues): self
    {
        foreach ($attrValues as $value) {
            if (isset($value->kEigenschaft)) {
                $attr = new PersistentCartItemProperty(
                    $value->kEigenschaft,
                    $value->kEigenschaftWert ?? 0,
                    $value->cFreifeldWert ?? null,
                    $value->cEigenschaftName ?? null,
                    $value->cEigenschaftWertName ?? null,
                    $this->kWarenkorbPersPos
                );
                $attr->schreibeDB();
                $this->oWarenkorbPersPosEigenschaft_arr[] = $attr;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function schreibeDB(): self
    {
        $ins                     = new stdClass();
        $ins->kWarenkorbPers     = $this->kWarenkorbPers;
        $ins->kArtikel           = $this->kArtikel;
        $ins->cArtikelName       = $this->cArtikelName;
        $ins->fAnzahl            = $this->fAnzahl;
        $ins->dHinzugefuegt      = $this->dHinzugefuegt;
        $ins->cUnique            = $this->cUnique;
        $ins->cResponsibility    = !empty($this->cResponsibility) ? $this->cResponsibility : 'core';
        $ins->kKonfigitem        = $this->kKonfigitem;
        $ins->nPosTyp            = $this->nPosTyp;
        $this->kWarenkorbPersPos = Shop::Container()->getDB()->insert('twarenkorbperspos', $ins);

        return $this;
    }

    /**
     * @return int
     */
    public function updateDB(): int
    {
        $upd                    = new stdClass();
        $upd->kWarenkorbPersPos = $this->kWarenkorbPersPos;
        $upd->kWarenkorbPers    = $this->kWarenkorbPers;
        $upd->kArtikel          = $this->kArtikel;
        $upd->cArtikelName      = $this->cArtikelName;
        $upd->fAnzahl           = $this->fAnzahl;
        $upd->dHinzugefuegt     = $this->dHinzugefuegt;
        $upd->cUnique           = $this->cUnique;
        $upd->cResponsibility   = !empty($this->cResponsibility) ? $this->cResponsibility : 'core';
        $upd->kKonfigitem       = $this->kKonfigitem;
        $upd->nPosTyp           = $this->nPosTyp;

        return Shop::Container()->getDB()->update(
            'twarenkorbperspos',
            'kWarenkorbPersPos',
            $this->kWarenkorbPersPos,
            $upd
        );
    }

    /**
     * @param int      $propertyID
     * @param int|null $propertyValueID
     * @param string   $freeText
     * @return bool
     */
    public function istEigenschaftEnthalten(int $propertyID, ?int $propertyValueID, string $freeText = ''): bool
    {
        foreach ($this->oWarenkorbPersPosEigenschaft_arr as $attr) {
            if ((int)$attr->kEigenschaft === $propertyID
                && ((!empty($attr->kEigenschaftWert) && $attr->kEigenschaftWert === $propertyValueID)
                    || ($attr->kEigenschaftWert === 0 && $attr->cFreifeldWert === $freeText))
            ) {
                return true;
            }
        }

        return false;
    }
}
