<?php

use JTL\Shop;

/**
 * @param int $sliderID
 * @return mixed
 */
function holeExtension(int $sliderID)
{
    return Shop::Container()->getDB()->select('textensionpoint', 'cClass', 'Slider', 'kInitial', $sliderID);
}
