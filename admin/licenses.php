<?php declare(strict_types=1);

/**
 * @global \JTL\Backend\AdminAccount $oAccount
 * @global \JTL\Smarty\JTLSmarty $smarty
 */

use JTL\Helpers\Request;
use JTL\License\Admin;
use JTL\License\Checker;
use JTL\License\Manager;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

Shop::Container()->getGetText()->loadAdminLocale('pages/pluginverwaltung');
$db      = Shop::Container()->getDB();
$cache   = Shop::Container()->getCache();
$checker = new Checker(Shop::Container()->getLogService(), $db, $cache);
$manager = new Manager($db, $cache);
$admin   = new Admin($manager, $db, $cache, $checker);
if (Request::postVar('action') === 'code') {
    $admin->handleAuth();
} else {
    $oAccount->permission('LICENSE_MANAGER', true, true);
    $admin->handle($smarty);
    $smarty->display('licenses.tpl');
}
