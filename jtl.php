<?php declare(strict_types=1);

use JTL\Customer\AccountController;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'wunschliste_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'kundenwerbenkeunden_inc.php';

$linkService   = Shop::Container()->getLinkService();
$smarty        = Shop::Smarty();
$controller    = new AccountController(
    Shop::Container()->getDB(),
    Shop::Container()->getAlertService(),
    $linkService,
    $smarty
);
$cCanonicalURL = $linkService->getStaticRoute('jtl.php');
$controller->handleRequest();
require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
executeHook(HOOK_JTL_PAGE);

$smarty->display('account/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
