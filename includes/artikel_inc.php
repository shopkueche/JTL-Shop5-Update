<?php

use JTL\Catalog\Product\Artikel;
use JTL\Helpers\Product;
use JTL\Shop;

/**
 * @param int       $productID
 * @param bool|null $isParent
 * @return stdClass|null
 * @deprecated since 5.0.0
 */
function gibArtikelXSelling(int $productID, $isParent = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Product::getXSelling($productID, $isParent);
}

/**
 * @deprecated since 5.0.0
 */
function bearbeiteFrageZumProdukt()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Product::checkProductQuestion([], Shop::getSettings([CONF_ARTIKELDETAILS, CONF_GLOBAL]));
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibFehlendeEingabenProduktanfrageformular()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Product::getMissingProductQuestionFormData(Shop::getSettings([CONF_ARTIKELDETAILS, CONF_GLOBAL]));
}

/**
 * @return stdClass
 * @deprecated since 5.0.0
 */
function baueProduktanfrageFormularVorgaben()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Product::getProductQuestionFormDefaults();
}

/**
 * @deprecated since 5.0.0
 */
function sendeProduktanfrage()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Product::sendProductQuestion();
}

/**
 * @param int $min
 * @return bool
 * @deprecated since 5.0.0
 */
function floodSchutzProduktanfrage(int $min = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Product::checkProductQuestionFloodProtection($min);
}

/**
 * @deprecated since 5.0.0
 */
function bearbeiteBenachrichtigung()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Product::checkAvailabilityMessage([]);
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibFehlendeEingabenBenachrichtigungsformular()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Product::getMissingAvailibilityFormData();
}

/**
 * @return stdClass
 * @deprecated since 5.0.0
 */
function baueFormularVorgabenBenachrichtigung()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Product::getAvailabilityFormDefaults();
}

/**
 * @param int $min
 * @return bool
 * @deprecated since 5.0.0
 */
function floodSchutzBenachrichtigung(int $min)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Product::checkAvailibityFormFloodProtection($min);
}

/**
 * @param int $productID
 * @param int $categoryID
 * @return stdClass
 * @deprecated since 5.0.0
 */
function gibNaviBlaettern(int $productID, int $categoryID)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Product::getProductNavigation($productID, $categoryID);
}

/**
 * @param int $nEigenschaftWert
 * @return array
 * @deprecated since 5.0.0
 */
function gibNichtErlaubteEigenschaftswerte(int $nEigenschaftWert)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Product::getNonAllowedAttributeValues($nEigenschaftWert);
}

/**
 * @param null|string|array $redirectParam
 * @param bool              $renew
 * @param null|Artikel      $product
 * @param null|float        $qty
 * @param int               $configItemID
 * @return array
 * @deprecated since 5.0.0
 */
function baueArtikelhinweise($redirectParam = null, $renew = false, $product = null, $qty = null, $configItemID = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Product::getProductMessages($redirectParam, $renew, $product, $qty, $configItemID);
}

/**
 * @param Artikel $product
 * @return mixed
 * @deprecated since 5.0.0
 */
function bearbeiteProdukttags($product)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return null;
}

/**
 * Baue Blätter Navi - Dient für die Blätternavigation unter Bewertungen in der Artikelübersicht
 *
 * @param int $ratingPage
 * @param int $ratingStars
 * @param int $ratingCount
 * @param int $pageCount
 * @return stdClass
 * @deprecated since 5.0.0
 */
function baueBewertungNavi($ratingPage, $ratingStars, $ratingCount, $pageCount = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Product::getRatingNavigation($ratingPage, $ratingStars, $ratingCount, $pageCount);
}

/**
 * Mappt den Fehlercode für Bewertungen
 *
 * @param string $code
 * @param float  $fGuthaben
 * @return string
 * @deprecated since 5.0.0
 */
function mappingFehlerCode($code, $fGuthaben = 0.0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Product::mapErrorCode($code, $fGuthaben);
}

/**
 * @param Artikel $parent
 * @param Artikel $child
 * @return mixed
 * @deprecated since 5.0.0
 */
function fasseVariVaterUndKindZusammen($parent, $child)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Product::combineParentAndChild($parent, $child);
}

/**
 * @param int $productID
 * @return array
 * @deprecated since 5.0.0
 */
function holeAehnlicheArtikel(int $productID)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);

    return Product::getSimilarProductsByID($productID);
}

/**
 * @param int $productID
 * @return bool
 * @deprecated since 5.0.0
 */
function ProductBundleWK(int $productID)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Product::addProductBundleToCart($productID);
}

/**
 * @param int       $productID
 * @param float|int $qty
 * @param array     $variations
 * @param array     $configGroups
 * @param array     $configGroupAmounts
 * @param array     $configItemAmounts
 * @return stdClass|null
 * @deprecated since 5.0.0
 */
function buildConfig($productID, $qty, $variations, $configGroups, $configGroupAmounts, $configItemAmounts)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Product::buildConfig(
        $productID,
        $qty,
        $variations,
        $configGroups,
        $configGroupAmounts,
        $configItemAmounts
    );
}

/**
 * @param int                   $configID
 * @param \JTL\Smarty\JTLSmarty $smarty
 * @deprecated since 5.0.0
 */
function holeKonfigBearbeitenModus($configID, $smarty)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Product::getEditConfigMode($configID, $smarty);
}


if (!function_exists('baueFormularVorgaben')) {
    /**
     * @return stdClass
     * @deprecated since 5.0.0
     */
    function baueFormularVorgaben()
    {
        trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
        return Product::getProductQuestionFormDefaults();
    }
}
