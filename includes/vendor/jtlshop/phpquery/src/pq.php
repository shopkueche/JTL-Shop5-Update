<?php

use JTL\phpQuery\phpQueryObject;
use JTL\phpQuery\phpQuery;

/**
 * Shortcut to phpQuery::pq($arg1, $context)
 *
 * @see phpQuery::pq()
 * @param mixed $arg1
 * @param mixed $context
 * @return phpQueryObject
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @package phpQuery
 */
function pq($arg1, $context = null)
{
    return \call_user_func_array([phpQuery::class, 'pq'], \func_get_args());
}
