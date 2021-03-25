<?php

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Pagination\Pagination;
use JTL\Shop;

/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('OPC_VIEW', true, true);

$action      = Request::verifyGPDataString('action');
$alertHelper = Shop::Container()->getAlertService();

$opc       = Shop::Container()->getOPC();
$opcPage   = Shop::Container()->getOPCPageService();
$opcPageDB = Shop::Container()->getOPCPageDB();

$pagesPagi = (new Pagination('pages'))
    ->setItemCount($opcPageDB->getPageCount())
    ->assemble();

if (Form::validateToken()) {
    if ($action === 'restore') {
        $pageId = Request::verifyGPDataString('pageId');
        $opcPage->deletePage($pageId);
        $alertHelper->addAlert(Alert::TYPE_NOTE, __('opcNoticePageReset'), 'opcNoticePageReset');
    } elseif ($action === 'discard') {
        $pageKey = Request::verifyGPCDataInt('pageKey');
        $opcPage->deleteDraft($pageKey);
        $alertHelper->addAlert(Alert::TYPE_NOTE, __('opcNoticeDraftDelete'), 'opcNoticeDraftDelete');
    }
}

$smarty
    ->assign('opc', $opc)
    ->assign('opcPageDB', $opcPageDB)
    ->assign('pagesPagi', $pagesPagi)
    ->display('opc-controlcenter.tpl');
