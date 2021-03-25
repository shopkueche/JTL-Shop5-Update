<?php

/**
 * @param array $post
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeEingabe(array $post)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param array $post
 * @param array $conf
 * @return bool
 * @deprecated since 5.0.0
 */
function setzeKwKinDB(array $post, array $conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param int   $customerID
 * @param float $fGuthaben
 * @return bool
 * @deprecated since 5.0.0 - not use in core anymore
 */
function gibBestandskundeGutbaben(int $customerID, $fGuthaben)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}
