<?php

use JTL\DB\ReturnType;
use JTL\Shop;

/**
 * @param int   $customerGroupID
 * @param int   $languageID
 * @param array $post
 * @param int   $textID
 * @return bool
 */
function speicherAGBWRB(int $customerGroupID, int $languageID, array $post, int $textID = 0)
{
    if ($customerGroupID > 0 && $languageID > 0) {
        $item = new stdClass();
        if ($textID > 0) {
            Shop::Container()->getDB()->delete('ttext', 'kText', $textID);
            $item->kText = $textID;
        }
        // Soll Standard sein?
        if (isset($post['nStandard']) && (int)$post['nStandard'] > 0) {
            // Standard umsetzen
            Shop::Container()->getDB()->query('UPDATE ttext SET nStandard = 0', ReturnType::AFFECTED_ROWS);
        }
        $item->kSprache            = $languageID;
        $item->kKundengruppe       = $customerGroupID;
        $item->cAGBContentText     = $post['cAGBContentText'];
        $item->cAGBContentHtml     = $post['cAGBContentHtml'];
        $item->cWRBContentText     = $post['cWRBContentText'];
        $item->cWRBContentHtml     = $post['cWRBContentHtml'];
        $item->cDSEContentText     = $post['cDSEContentText'];
        $item->cDSEContentHtml     = $post['cDSEContentHtml'];
        $item->cWRBFormContentText = $post['cWRBFormContentText'];
        $item->cWRBFormContentHtml = $post['cWRBFormContentHtml'];
        $item->nStandard           = $post['nStandard'] ?? 0;

        Shop::Container()->getDB()->insert('ttext', $item);

        return true;
    }

    return false;
}
