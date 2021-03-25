<?php

namespace JTL\Catalog\Wishlist;

use JTL\Helpers\GeneralObject;
use JTL\Shop;

/**
 * Class WishlistItemProperty
 * @package JTL\Catalog\Wishlist
 */
class WishlistItemProperty
{
    /**
     * @var int
     */
    public $kWunschlistePosEigenschaft;

    /**
     * @var int
     */
    public $kWunschlistePos;

    /**
     * @var int
     */
    public $kEigenschaft;

    /**
     * @var int
     */
    public $kEigenschaftWert;

    /**
     * @var string
     */
    public $cFreifeldWert;

    /**
     * @var string
     */
    public $cEigenschaftName;

    /**
     * @var string
     */
    public $cEigenschaftWertName;

    /**
     * WishlistItemProperty constructor.
     * @param int    $propertyID
     * @param null|int    $propertyValueID
     * @param string $freeText
     * @param string $propertyName
     * @param string $propertyValueName
     * @param int    $wishlistItemID
     */
    public function __construct(
        int $propertyID,
        ?int $propertyValueID,
        $freeText,
        $propertyName,
        $propertyValueName,
        int $wishlistItemID
    ) {
        $this->kEigenschaft         = $propertyID;
        $this->kEigenschaftWert     = $propertyValueID;
        $this->kWunschlistePos      = $wishlistItemID;
        $this->cFreifeldWert        = $freeText;
        $this->cEigenschaftName     = $propertyName;
        $this->cEigenschaftWertName = $propertyValueName;
    }

    /**
     * @return $this
     */
    public function schreibeDB(): self
    {
        $this->kWunschlistePosEigenschaft = Shop::Container()->getDB()->insert(
            'twunschlisteposeigenschaft',
            GeneralObject::copyMembers($this)
        );

        return $this;
    }
}
