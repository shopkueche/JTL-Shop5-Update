<?php

namespace JTL\Catalog\Wishlist;

use JTL\Catalog\Product\Artikel;
use JTL\Shop;
use stdClass;
use function Functional\some;

/**
 * Class WishlistItem
 * @package JTL\Catalog\Wishlist
 */
class WishlistItem
{
    /**
     * @var int
     */
    public $kWunschlistePos;

    /**
     * @var int
     */
    public $kWunschliste;

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
    public $cArtikelName = '';

    /**
     * @var string
     */
    public $cKommentar = '';

    /**
     * @var string
     */
    public $dHinzugefuegt;

    /**
     * @var string
     */
    public $dHinzugefuegt_de;

    /**
     * @var array
     */
    public $CWunschlistePosEigenschaft_arr = [];

    /**
     * @var Artikel
     */
    public $Artikel;

    /**
     * WishlistItem constructor.
     * @param int          $productID
     * @param string       $productName
     * @param float|string $qty
     * @param int          $wihlistID
     */
    public function __construct(int $productID, string $productName, $qty, int $wihlistID)
    {
        $this->kArtikel     = $productID;
        $this->cArtikelName = $productName;
        $this->fAnzahl      = $qty;
        $this->kWunschliste = $wihlistID;
    }

    /**
     * @param array $values
     * @return $this
     */
    public function erstellePosEigenschaften(array $values): self
    {
        foreach ($values as $value) {
            $wlItemProp = new WishlistItemProperty(
                $value->kEigenschaft,
                !empty($value->kEigenschaftWert) ? $value->kEigenschaftWert : null,
                !empty($value->cFreifeldWert) ? $value->cFreifeldWert : null,
                !empty($value->cEigenschaftName) ? $value->cEigenschaftName : null,
                !empty($value->cEigenschaftWertName) ? $value->cEigenschaftWertName : null,
                $this->kWunschlistePos
            );
            $wlItemProp->schreibeDB();
            $this->CWunschlistePosEigenschaft_arr[] = $wlItemProp;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function schreibeDB(): self
    {
        $ins                = new stdClass();
        $ins->kWunschliste  = $this->kWunschliste;
        $ins->kArtikel      = $this->kArtikel;
        $ins->fAnzahl       = $this->fAnzahl;
        $ins->cArtikelName  = $this->cArtikelName;
        $ins->cKommentar    = $this->cKommentar;
        $ins->dHinzugefuegt = $this->dHinzugefuegt;

        $this->kWunschlistePos = Shop::Container()->getDB()->insert('twunschlistepos', $ins);

        return $this;
    }

    /**
     * @return $this
     */
    public function updateDB(): self
    {
        $upd                  = new stdClass();
        $upd->kWunschlistePos = $this->kWunschlistePos;
        $upd->kWunschliste    = $this->kWunschliste;
        $upd->kArtikel        = $this->kArtikel;
        $upd->fAnzahl         = $this->fAnzahl;
        $upd->cArtikelName    = $this->cArtikelName;
        $upd->cKommentar      = $this->cKommentar;
        $upd->dHinzugefuegt   = $this->dHinzugefuegt;

        Shop::Container()->getDB()->update('twunschlistepos', 'kWunschlistePos', $this->kWunschlistePos, $upd);

        return $this;
    }

    /**
     * @param int $propertyID
     * @param null|int $propertyValueID
     * @return bool
     */
    public function istEigenschaftEnthalten(int $propertyID, ?int $propertyValueID): bool
    {
        return some(
            $this->CWunschlistePosEigenschaft_arr,
            static function ($e) use ($propertyID, $propertyValueID) {
                return (int)$e->kEigenschaft === $propertyID && (int)$e->kEigenschaftWert === $propertyValueID;
            }
        );
    }
}
