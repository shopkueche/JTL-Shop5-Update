<?php declare(strict_types=1);

use JTL\Backend\Status;
use JTL\Network\JTLApi;
use JTL\Shop;

/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('DIAGNOSTIC_VIEW', true, true);

$smarty->assign('status', Status::getInstance(Shop::Container()->getDB(), Shop::Container()->getCache(), true))
    ->assign('sub', Shop::Container()->get(JTLApi::class)->getSubscription())
    ->display('status.tpl');
