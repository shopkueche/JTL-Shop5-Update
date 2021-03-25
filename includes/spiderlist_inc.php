<?php

use JTL\Visitor;

/**
 * @return array
 * @deprecated since 5.0.0
 */
function getSpiderArr(): array
{
    trigger_error(__METHOD__ . ' is deprecated. Use Visitor::getSpiders() instead.', E_USER_DEPRECATED);
    return Visitor::getSpiders();
}
