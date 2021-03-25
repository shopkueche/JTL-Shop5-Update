<?php

use JTL\Visitor;

/**
 * @param string $userAgent
 * @param string $ip
 * @return object
 * @deprecated since 5.0.0
 */
function dbLookupVisitor($userAgent, $ip)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Visitor::dbLookup() instead.', E_USER_DEPRECATED);
    return Visitor::dbLookup($userAgent, $ip);
}

/**
 * @param object $visitor
 * @param int    $visitorID
 * @param string $userAgent
 * @param int    $botID
 * @return object
 * @deprecated since 5.0.0
 */
function updateVisitorObject($visitor, int $visitorID, $userAgent, $botID)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::updateVisitorObject($visitor, $visitorID, $userAgent, $botID);
}

/**
 * @param string $userAgent
 * @param int    $botID
 * @return object
 * @deprecated since 5.0.0
 */
function createVisitorObject($userAgent, int $botID)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::createVisitorObject($userAgent, $botID);
}

/**
 * @param object $visitor
 * @return int [LAST_INSERT_ID|0(on fail)]
 * @deprecated since 5.0.0
 */
function dbInsertVisitor($visitor)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::dbInsert($visitor);
}

/**
 * @param object $visitor
 * @param int    $visitorID
 * @return int
 * @deprecated since 5.0.0
 */
function dbUpdateVisitor($visitor, int $visitorID)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::dbUpdate($visitor, $visitorID);
}

/**
 * @param int $customerID
 * @return int [$kBestellung|0]
 * @deprecated since 5.0.0
 */
function refreshCustomerOrderId(int $customerID)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::refreshCustomerOrderId($customerID);
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function gibBrowser()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::getBrowser();
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function gibReferer()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::getReferer();
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function gibBot()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::getBot();
}

/**
 * @param int    $visitorID
 * @param string $referer
 * @deprecated since 5.0.0
 */
function werteRefererAus(int $visitorID, $referer)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Visitor::analyzeReferer($visitorID, $referer);
}

/**
 * @param string $referer
 * @return int
 * @deprecated since 5.0.0
 */
function istSuchmaschine($referer)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::isSearchEngine($referer);
}

/**
 * @param string $userAgent
 * @return int
 * @deprecated since 5.0.0
 */
function istSpider($userAgent)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::isSpider($userAgent);
}
