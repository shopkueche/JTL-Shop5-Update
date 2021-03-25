<?php

use JTL\Catalog\Separator;
use JTL\Shop;

/**
 * @param array $post
 * @return bool
 */
function speicherTrennzeichen(array $post): bool
{
    foreach ([JTL_SEPARATOR_WEIGHT, JTL_SEPARATOR_AMOUNT, JTL_SEPARATOR_LENGTH] as $nEinheit) {
        if (isset(
            $post['nDezimal_' . $nEinheit],
            $post['cDezZeichen_' . $nEinheit],
            $post['cTausenderZeichen_' . $nEinheit]
        )) {
            $trennzeichen = new Separator();
            $trennzeichen->setSprache($_SESSION['editLanguageID'])
                          ->setEinheit($nEinheit)
                          ->setDezimalstellen($post['nDezimal_' . $nEinheit])
                          ->setDezimalZeichen($post['cDezZeichen_' . $nEinheit])
                          ->setTausenderZeichen($post['cTausenderZeichen_' . $nEinheit]);
            $idx = 'kTrennzeichen_' . $nEinheit;
            if (isset($post[$idx])) {
                $trennzeichen->setTrennzeichen($post[$idx])
                              ->update();
            } elseif (!$trennzeichen->save()) {
                return false;
            }
        }
    }

    Shop::Container()->getCache()->flushTags(
        [CACHING_GROUP_CORE, CACHING_GROUP_CATEGORY, CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]
    );

    return true;
}
