<?php

use JTL\Helpers\Seo;

/**
 * @param string $cSeo
 * @return mixed
 * @deprecated since 5.0.0
 */
function getSeo($cSeo)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Seo::getSeo($cSeo);
}

/**
 * @param string $cSeo
 * @return string
 * @deprecated since 5.0.0
 */
function checkSeo($cSeo)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Seo::checkSeo($cSeo);
}

/**
 * @param string $str
 * @return mixed
 * @deprecated since 5.0.0
 */
function iso2ascii($str)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Seo::sanitizeSeoSlug($str);
}

/**
 * Get flat SEO-URL path (removes all slashes from seo-url-path, including leading and trailing slashes)
 *
 * @param string $cSeoPath - the seo path e.g. "My/Product/Name"
 * @return string - flat SEO-URL Path e.g. "My-Product-Name"
 * @deprecated since 5.0.0
 */
function getFlatSeoPath($cSeoPath)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Seo::getFlatSeoPath($cSeoPath);
}
