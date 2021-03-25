<?php declare(strict_types=1);

use JTL\Cart\CartHelper;
use JTL\Shop;

require __DIR__ . '/includes/globalinclude.php';

$NaviFilter = Shop::run();
executeHook(HOOK_INDEX_NAVI_HEAD_POSTGET);
CartHelper::checkAdditions();
$file = Shop::getEntryPoint();
if ($file !== null && !Shop::$is404) {
    require PFAD_ROOT . basename($file);
}
if (Shop::check404() === true) {
    require PFAD_ROOT . 'seite.php';
}
