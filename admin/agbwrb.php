<?php

use JTL\Alert\Alert;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Recommendation\Manager;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'agbwrb_inc.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
/** @global \JTL\Backend\AdminAccount $oAccount */
$oAccount->permission('ORDER_AGB_WRB_VIEW', true, true);
$step            = 'agbwrb_uebersicht';
$alertHelper     = Shop::Container()->getAlertService();
$recommendations = new Manager($alertHelper, Manager::SCOPE_BACKEND_LEGAL_TEXTS);
setzeSprache();
$languageID = (int)$_SESSION['editLanguageID'];

if (Request::verifyGPCDataInt('agbwrb') === 1 && Form::validateToken()) {
    // Editieren
    if (Request::verifyGPCDataInt('agbwrb_edit') === 1) {
        if (Request::verifyGPCDataInt('kKundengruppe') > 0) {
            $step    = 'agbwrb_editieren';
            $oAGBWRB = Shop::Container()->getDB()->select(
                'ttext',
                'kSprache',
                $languageID,
                'kKundengruppe',
                Request::verifyGPCDataInt('kKundengruppe')
            );
            $smarty->assign('kKundengruppe', Request::verifyGPCDataInt('kKundengruppe'))
                ->assign('oAGBWRB', $oAGBWRB);
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorInvalidCustomerGroup'), 'errorInvalidCustomerGroup');
        }
    } elseif (Request::verifyGPCDataInt('agbwrb_editieren_speichern') === 1) {
        if (speicherAGBWRB(
            Request::verifyGPCDataInt('kKundengruppe'),
            $languageID,
            $_POST,
            Request::verifyGPCDataInt('kText')
        )) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSave'), 'agbWrbSuccessSave');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSave'), 'agbWrbErrorSave');
        }
    }
}

if ($step === 'agbwrb_uebersicht') {
    // AGB fuer jeweilige Sprache holen
    $agbWrb = [];
    $data   = Shop::Container()->getDB()->selectAll('ttext', 'kSprache', $languageID);
    // Assoc Array mit kKundengruppe machen
    foreach ($data as $item) {
        $agbWrb[(int)$item->kKundengruppe] = $item;
    }
    $smarty->assign('customerGroups', CustomerGroup::getGroups())
        ->assign('oAGBWRB_arr', $agbWrb);
}

$smarty->assign('step', $step)
    ->assign('languageID', $languageID)
    ->assign('recommendations', $recommendations)
    ->display('agbwrb.tpl');
