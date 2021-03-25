<?php declare(strict_types=1);

/** @global \JTL\Smarty\JTLSmarty $smarty */
/** @global \JTL\Backend\AdminAccount $oAccount */

use JTL\Backend\AuthToken;
use JTL\Backend\Wizard\DefaultFactory;
use JTL\Backend\Wizard\Controller;
use JTL\Helpers\Request;
use JTL\License\Admin;
use JTL\License\Checker;
use JTL\License\Manager;
use JTL\Session\Backend;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

$db           = Shop::Container()->getDB();
$cache        = Shop::Container()->getCache();
$getText      = Shop::Container()->getGetText();
$checker      = new Checker(Shop::Container()->getLogService(), $db, $cache);
$manager      = new Manager($db, $cache);
$admin        = new Admin($manager, $db, $cache, $checker);
$factory      = new DefaultFactory(
    $db,
    $getText,
    Shop::Container()->getAlertService(),
    Shop::Container()->getAdminAccount()
);
$controller   = new Controller($factory, $db, $cache, $getText);
$conf         = Shop::getSettings([CONF_GLOBAL]);
$token        = AuthToken::getInstance($db);
$valid        = $token->isValid();
$authRedirect = $valid && Backend::get('wizard-authenticated')
    ? Backend::get('wizard-authenticated')
    : false;

Backend::set('redirectedToWizard', true);

if (Request::postVar('action') === 'code') {
    $admin->handleAuth();
} elseif (Request::getVar('action') === 'auth') {
    Backend::set('wizard-authenticated', Request::getVar('wizard-authenticated'));
    $token->requestToken(
        Backend::get('jtl_token'),
        Shop::getAdminURL() . '/wizard.php?action=code'
    );
}
if (Request::postVar('action') !== 'code') {
    unset($_SESSION['wizard-authenticated']);
    $oAccount->permission('WIZARD_VIEW', true, true);
    $smarty->assign('steps', $controller->getSteps())
        ->assign('authRedirect', $authRedirect)
        ->assign('hasAuth', $valid)
        ->display('wizard.tpl');
}
