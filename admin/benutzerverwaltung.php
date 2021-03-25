<?php declare(strict_types=1);

use JTL\Backend\AdminAccountManager;

/** @global \JTL\Backend\AdminAccount $oAccount */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ACCOUNT_VIEW', true, true);

/** @global \JTL\Smarty\JTLSmarty $smarty */
$adminAccountManager = new AdminAccountManager(
    $smarty,
    Shop::Container()->getDB(),
    Shop::Container()->getAlertService()
);
$adminAccountManager->finalize($adminAccountManager->getNextAction());
