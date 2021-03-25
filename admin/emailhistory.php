<?php

use JTL\Alert\Alert;
use JTL\Emailhistory;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('EMAILHISTORY_VIEW', true, true);
$step        = 'uebersicht';
$history     = new Emailhistory();
$action      = (isset($_POST['a']) && Form::validateToken()) ? $_POST['a'] : '';
$alertHelper = Shop::Container()->getAlertService();

if ($action === 'delete') {
    if (isset($_POST['remove_all'])) {
        if ($history->deleteAll() !== true) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorHistoryDelete'), 'errorHistoryDelete');
        }
    } elseif (GeneralObject::hasCount('kEmailhistory', $_POST)) {
        $history->deletePack($_POST['kEmailhistory']);
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successHistoryDelete'), 'successHistoryDelete');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSelectEntry'), 'errorSelectEntry');
    }
}

if ($step === 'uebersicht') {
    $pagination = (new Pagination('emailhist'))
        ->setItemCount($history->getCount())
        ->assemble();
    $smarty->assign('pagination', $pagination)
        ->assign('oEmailhistory_arr', $history->getAll(' LIMIT ' . $pagination->getLimitSQL()));
}

$smarty->assign('step', $step)
    ->display('emailhistory.tpl');
