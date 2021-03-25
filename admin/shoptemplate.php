<?php declare(strict_types=1);

use JTL\Shop;
use JTL\Template\Admin\Controller;

/**
 * @global \JTL\Smarty\JTLSmarty $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('DISPLAY_TEMPLATE_VIEW', true, true);

$controller = new Controller(
    Shop::Container()->getDB(),
    Shop::Container()->getCache(),
    Shop::Container()->getAlertService(),
    $smarty
);
$controller->handleAction();
