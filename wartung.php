<?php declare(strict_types=1);

use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';

if (SAFE_MODE === false && Shop::getSettings([CONF_GLOBAL])['global']['wartungsmodus_aktiviert'] === 'N') {
    header('Location: ' . Shop::getURL(), true, 307);
    exit;
}
Shop::setPageType(PAGE_WARTUNG);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

Shop::Smarty()->display('snippets/maintenance.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
