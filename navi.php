<?php declare(strict_types=1);

use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';

$NaviFilter = Shop::run();
$params     = Shop::getParameters();
if ($params['kLink'] > 0) {
    require PFAD_ROOT . 'seite.php';
} elseif ($params['kArtikel'] > 0) {
    require PFAD_ROOT . 'artikel.php';
} else {
    Shop::Smarty()->assign('NaviFilter', $NaviFilter);
    require PFAD_ROOT . 'filter.php';
}
if (Shop::check404() === true) {
    require PFAD_ROOT . 'seite.php';
}
