<?php

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Statusmail;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('EMAIL_REPORTS_VIEW', true, true);
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statusemail_inc.php';

$alertHelper = Shop::Container()->getAlertService();
$step        = 'statusemail_uebersicht';
$statusMail  = new Statusmail(Shop::Container()->getDB());

if (Form::validateToken()) {
    if (Request::postVar('action') === 'sendnow') {
        $statusMail->sendAllActiveStatusMails();
    } elseif (Request::postInt('einstellungen') === 1) {
        if ($statusMail->updateConfig()) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successChangesSave'), 'successChangesSave');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorConfigSave'), 'errorConfigSave');
        }
        $step = 'statusemail_uebersicht';
    }
}
if ($step === 'statusemail_uebersicht') {
    $smarty->assign('oStatusemailEinstellungen', $statusMail->loadConfig());
}

$smarty->assign('step', $step)
       ->display('statusemail.tpl');
