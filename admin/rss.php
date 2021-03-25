<?php

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'rss_inc.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('EXPORT_RSSFEED_VIEW', true, true);
$alertHelper = Shop::Container()->getAlertService();
if (Request::getInt('f') === 1 && Form::validateToken()) {
    if (generiereRSSXML()) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successRSSCreate'), 'successRSSCreate');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorRSSCreate'), 'errorRSSCreate');
    }
}
if (Request::postInt('einstellungen') > 0) {
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(CONF_RSS, $_POST),
        'saveSettings'
    );
}
if (!file_exists(PFAD_ROOT . FILE_RSS_FEED)) {
    @touch(PFAD_ROOT . FILE_RSS_FEED);
}
if (!is_writable(PFAD_ROOT . FILE_RSS_FEED)) {
    $alertHelper->addAlert(
        Alert::TYPE_ERROR,
        sprintf(__('errorRSSCreatePermissions'), PFAD_ROOT . FILE_RSS_FEED),
        'errorRSSCreatePermissions'
    );
}
$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_RSS))
       ->assign('alertError', $alertHelper->alertTypeExists(Alert::TYPE_ERROR))
       ->display('rss.tpl');
