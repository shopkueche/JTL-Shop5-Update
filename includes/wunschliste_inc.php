<?php

use JTL\Catalog\Wishlist\Wishlist;

/**
 * Holt für einen Kunden die aktive Wunschliste (falls vorhanden) aus der DB und fügt diese in die Session
 * @deprecated since 5.0.0
 */
function setzeWunschlisteInSession()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    Wishlist::persistInSession();
}

/**
 * @param int $id
 * @return string
 * @deprecated since 5.0.0
 */
function wunschlisteLoeschen(int $id)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wishlist::delete($id);
}

/**
 * @param int $id
 * @return string
 * @deprecated since 5.0.0
 */
function wunschlisteAktualisieren(int $id)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wishlist::update($id);
}

/**
 * @param int $id
 * @return string
 * @deprecated since 5.0.0
 */
function wunschlisteStandard(int $id)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wishlist::setDefault($id);
}

/**
 * @param string $name
 * @return string
 * @deprecated since 5.0.0
 */
function wunschlisteSpeichern($name)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wishlist::save($name);
}

/**
 * @param array $recipients
 * @param int   $id
 * @return string
 * @deprecated since 5.0.0
 */
function wunschlisteSenden(array $recipients, int $id)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wishlist::send($recipients, $id);
}

/**
 * @param int $wishListID
 * @param int $itemID
 * @return array|bool
 * @deprecated since 5.0.0
 */
function gibEigenschaftenZuWunschliste(int $wishListID, int $itemID)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wishlist::getAttributesByID($wishListID, $itemID);
}

/**
 * @param int $itemID
 * @return object|bool
 * @deprecated since 5.0.0
 */
function giboWunschlistePos(int $itemID)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wishlist::getWishListPositionDataByID($itemID);
}

/**
 * @param int    $id
 * @param string $cURLID
 * @return bool|stdClass
 * @deprecated since 5.0.0
 */
function giboWunschliste(int $id = 0, string $cURLID = '')
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wishlist::getWishListDataByID($id, $cURLID);
}

/**
 * @param object $wishList
 * @return mixed
 * @deprecated since 5.0.0
 */
function bauecPreis($wishList)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wishlist::buildPrice($wishList);
}

/**
 * @param int $code
 * @return string
 * @deprecated since 5.0.0
 */
function mappeWunschlisteMSG(int $code)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wishlist::mapMessage($code);
}
