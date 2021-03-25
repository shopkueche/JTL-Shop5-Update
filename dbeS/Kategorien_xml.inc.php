<?php

use JTL\Shop;

/**
 * update lft/rght values for categories in the nested set model
 *
 * @param int $parentID
 * @param int $left
 * @param int $level
 * @return int
 */
function rebuildCategoryTree(int $parentID, int $left, int $level = 0): int
{
    // the right value of this node is the left value + 1
    $right = $left + 1;
    // get all children of this node
    $result = Shop::Container()->getDB()->selectAll(
        'tkategorie',
        'kOberKategorie',
        $parentID,
        'kKategorie',
        'nSort, cName'
    );
    foreach ($result as $_res) {
        $right = rebuildCategoryTree($_res->kKategorie, $right, $level + 1);
    }
    // we've got the left value, and now that we've processed the children of this node we also know the right value
    Shop::Container()->getDB()->update('tkategorie', 'kKategorie', $parentID, (object)[
        'lft'    => $left,
        'rght'   => $right,
        'nLevel' => $level,
    ]);

    // return the right value of this node + 1
    return $right + 1;
}

/**
 * @return void
 */
function Kategorien_xml_Finish(): void
{
    rebuildCategoryTree(0, 1);
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_CATEGORY]);
}
