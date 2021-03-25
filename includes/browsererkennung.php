<?php

use JTL\Visitor;

/**
 * @param null|string $userAgent
 * @return stdClass
 * @deprecated since 5.0.0
 */
function getBrowser($userAgent = null)
{
    trigger_error(__METHOD__ . ' is deprecated. Use Visitor::getBrowserForUserAgent() instead.', E_USER_DEPRECATED);
    return Visitor::getBrowserForUserAgent($userAgent);
}
