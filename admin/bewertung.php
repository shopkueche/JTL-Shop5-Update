<?php

use JTL\Helpers\Request;
use JTL\Review\ReviewAdminController;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */
$oAccount->permission('MODULE_VOTESYSTEM_VIEW', true, true);

require_once PFAD_ROOT . PFAD_INCLUDES . 'bewertung_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'bewertung_inc.php';
setzeSprache();
$cache       = Shop::Container()->getCache();
$alertHelper = Shop::Container()->getAlertService();
$db          = Shop::Container()->getDB();
$controller  = new ReviewAdminController($db, $cache, $alertHelper, $smarty);
$tab         = mb_strlen(Request::verifyGPDataString('tab')) > 0 ? Request::verifyGPDataString('tab') : 'freischalten';
$step        = $controller->handleRequest();
if (Request::getVar('a') === 'editieren' || $step === 'bewertung_editieren') {
    $step = 'bewertung_editieren';
    $smarty->assign('review', $controller->getReview(Request::verifyGPCDataInt('kBewertung')));
    if (Request::verifyGPCDataInt('nFZ') === 1) {
        $smarty->assign('nFZ', 1);
    }
} elseif ($step === 'bewertung_uebersicht') {
    $controller->getOverview();
}
$smarty->assign('step', $step)
    ->assign('cTab', $tab)
    ->display('bewertung.tpl');
