<?php

use JTL\Alert\Alert;
use JTL\Catalog\Separator;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\PlausiTrennzeichen;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('SETTINGS_SEPARATOR_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'trennzeichen_inc.php';
setzeSprache();

$step        = 'trennzeichen_uebersicht';
$alertHelper = Shop::Container()->getAlertService();
if (Request::verifyGPCDataInt('save') === 1 && Form::validateToken()) {
    $checks = new PlausiTrennzeichen();
    $checks->setPostVar($_POST);
    $checks->doPlausi();
    $checkItems = $checks->getPlausiVar();
    if (count($checkItems) === 0) {
        if (speicherTrennzeichen($_POST)) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successConfigSave'), 'successConfigSave');
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_CORE]);
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorConfigSave'), 'errorConfigSave');
            $smarty->assign('xPostVar_arr', $checks->getPostVar());
        }
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
        $idx = 'nDezimal_' . JTL_SEPARATOR_WEIGHT;
        if (isset($checkItems[$idx]) && $checkItems[$idx] === 2) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorWeightDecimals'), 'errorWeightDecimals');
        }
        $idx = 'nDezimal_' . JTL_SEPARATOR_AMOUNT;
        if (isset($checkItems[$idx]) && $checkItems[$idx] === 2) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAmountDecimals'), 'errorAmountDecimals');
        }
        $smarty->assign('xPlausiVar_arr', $checks->getPlausiVar())
            ->assign('xPostVar_arr', $checks->getPostVar());
    }
}

$smarty->assign('step', $step)
    ->assign('oTrennzeichenAssoc_arr', Separator::getAll($_SESSION['editLanguageID']))
    ->display('trennzeichen.tpl');
