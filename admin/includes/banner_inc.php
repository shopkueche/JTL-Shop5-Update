<?php

use JTL\ImageMap;
use JTL\IO\IOResponse;
use JTL\Shop;

/**
 * @return mixed
 */
function holeAlleBanner()
{
    $banner = new ImageMap(Shop::Container()->getDB());

    return $banner->fetchAll();
}

/**
 * @param int  $kImageMap
 * @param bool $fill
 * @return mixed
 */
function holeBanner(int $kImageMap, bool $fill = true)
{
    $banner = new ImageMap(Shop::Container()->getDB());

    return $banner->fetch($kImageMap, true, $fill);
}

/**
 * @param int $kImageMap
 * @return mixed
 */
function holeExtension(int $kImageMap)
{
    return Shop::Container()->getDB()->select('textensionpoint', 'cClass', 'ImageMap', 'kInitial', $kImageMap);
}

/**
 * @param int $kImageMap
 * @return mixed
 */
function entferneBanner(int $kImageMap)
{
    $db     = Shop::Container()->getDB();
    $banner = new ImageMap($db);
    $db->delete('textensionpoint', ['cClass', 'kInitial'], ['ImageMap', $kImageMap]);

    return $banner->delete($kImageMap);
}

/**
 * @return array
 */
function holeBannerDateien()
{
    $files = [];
    if (($handle = opendir(PFAD_ROOT . PFAD_BILDER_BANNER)) !== false) {
        while (($file = readdir($handle)) !== false) {
            if ($file !== '.' && $file !== '..' && $file[0] !== '.') {
                $files[] = $file;
            }
        }
        closedir($handle);
    }

    return $files;
}

/**
 * @param mixed $data
 * @return IOResponse
 */
function saveBannerAreasIO($data)
{
    $banner   = new ImageMap(Shop::Container()->getDB());
    $response = new IOResponse();
    $data     = json_decode($data);
    foreach ($data->oArea_arr as $area) {
        $area->kArtikel      = (int)$area->kArtikel;
        $area->kImageMap     = (int)$area->kImageMap;
        $area->kImageMapArea = (int)$area->kImageMapArea;
    }
    $banner->saveAreas($data);

    return $response;
}
