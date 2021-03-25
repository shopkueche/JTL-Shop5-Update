<?php

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('DISPLAY_ARTICLEOVERLAYS_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'suchspecialoverlay_inc.php';
$alertHelper = Shop::Container()->getAlertService();
$step        = 'suchspecialoverlay_uebersicht';
setzeSprache();
$overlay = gibSuchspecialOverlay(1);
if (Request::verifyGPCDataInt('suchspecialoverlay') === 1) {
    $step = 'suchspecialoverlay_detail';
    $oID  = Request::verifyGPCDataInt('kSuchspecialOverlay');
    if (Request::postInt('speicher_einstellung') === 1
        && Form::validateToken()
        && speicherEinstellung($oID, $_POST, $_FILES['cSuchspecialOverlayBild'])
    ) {
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successConfigSave'), 'successConfigSave');
    }
    if ($oID > 0) {
        $overlay = gibSuchspecialOverlay($oID);
    }
}
$overlays = gibAlleSuchspecialOverlays();
$template = Shop::Container()->getTemplateService()->getActiveTemplate();
if ($template->getName() === 'Evo'
    && $template->getAuthor() === 'JTL-Software-GmbH'
    && (int)$template->getVersion() >= 4
) {
    $smarty->assign('isDeprecated', true);
}

$smarty->assign('cRnd', time())
    ->assign('oSuchspecialOverlay', $overlay)
    ->assign('nMaxFileSize', getMaxFileSize(ini_get('upload_max_filesize')))
    ->assign('oSuchspecialOverlay_arr', $overlays)
    ->assign('nSuchspecialOverlayAnzahl', count($overlays) + 1)
    ->assign('step', $step)
    ->display('suchspecialoverlay.tpl');
