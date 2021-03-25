<?php

use JTL\IO\IOResponse;
use JTL\Shop;

/**
 * @return IOResponse
 * @throws Exception
 */
function getRandomPasswordIO(): IOResponse
{
    $response = new IOResponse();
    $password = Shop::Container()->getPasswordService()->generate(PASSWORD_DEFAULT_LENGTH);
    $response->assignDom('cPass', 'value', $password);

    return $response;
}
