<?php

use JTL\Cart\CartHelper;

/**
 * @param array $items
 * @deprecated since 5.0.0
 */
function loescheWarenkorbPositionen($items)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    CartHelper::deleteCartItems($items);
}

/**
 * @param int $item
 * @deprecated since 5.0.0
 */
function loescheWarenkorbPosition($item)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    CartHelper::deleteCartItems([$item]);
}

/**
 * @deprecated since 5.0.0
 */
function uebernehmeWarenkorbAenderungen()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    CartHelper::applyCartChanges();
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function checkeSchnellkauf()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return CartHelper::checkQuickBuy();
}

/**
 * @deprecated since 5.0.0
 */
function loescheAlleSpezialPos()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    CartHelper::deleteAllSpecialItems();
}

/**
 * @return stdClass
 * @deprecated since 5.0.0
 */
function gibXSelling()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return CartHelper::getXSelling();
}

/**
 * @param array $conf
 * @return array
 * @deprecated since 5.0.0
 */
function gibGratisGeschenke(array $conf)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return CartHelper::getFreeGifts($conf);
}

/**
 * Schaut nach ob eine Bestellmenge > Lagersbestand ist und falls dies erlaubt ist, gibt es einen Hinweis
 *
 * @param array $conf
 * @return string
 * @deprecated since 5.0.0
 */
function pruefeBestellMengeUndLagerbestand($conf = [])
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return CartHelper::checkOrderAmountAndStock($conf);
}

/**
 * Nachschauen ob beim Konfigartikel alle Pflichtkomponenten vorhanden sind, andernfalls löschen
 * @deprecated since 5.0.0
 */
function validiereWarenkorbKonfig()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    CartHelper::validateCartConfig();
}
