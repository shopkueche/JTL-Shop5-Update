<?php

use JTL\Helpers\SearchSpecial;

/**
 * @return string
 * @deprecated since 5.0.0
 */
function gibVaterSQL()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return SearchSpecial::getParentSQL();
}

/**
 * @param int $limit
 * @param int $customerGroupID
 * @return array
 * @deprecated since 5.0.0
 */
function gibTopAngebote(int $limit = 20, int $customerGroupID = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $searchSpecial = new SearchSpecial(Shop::Container()->getDB(), Shop::Container()->getCache());

    return $searchSpecial->getTopOffers($limit, $customerGroupID);
}

/**
 * @param array $arr
 * @param int   $limit
 * @return array
 * @deprecated since 5.0.0
 */
function randomizeAndLimit(array $arr, int $limit = 1)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return SearchSpecial::randomizeAndLimit($arr, $limit);
}

/**
 * @param int $limit
 * @param int $customerGroupID
 * @return array
 * @deprecated since 5.0.0
 */
function gibBestseller(int $limit = 20, int $customerGroupID = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $searchSpecial = new SearchSpecial(Shop::Container()->getDB(), Shop::Container()->getCache());

    return $searchSpecial->getBestsellers($limit, $customerGroupID);
}

/**
 * @param int $limit
 * @param int $customerGroupID
 * @return array
 * @deprecated since 5.0.0
 */
function gibSonderangebote(int $limit = 20, int $customerGroupID = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $searchSpecial = new SearchSpecial(Shop::Container()->getDB(), Shop::Container()->getCache());

    return $searchSpecial->getSpecialOffers($limit, $customerGroupID);
}

/**
 * @param int $limit
 * @param int $customerGroupID
 * @return array
 * @deprecated since 5.0.0
 */
function gibNeuImSortiment(int $limit, int $customerGroupID = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $searchSpecial = new SearchSpecial(Shop::Container()->getDB(), Shop::Container()->getCache());

    return $searchSpecial->getNewProducts($limit, $customerGroupID);
}
