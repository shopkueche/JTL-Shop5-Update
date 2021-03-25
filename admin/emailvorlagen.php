<?php

use JTL\Alert\Alert;
use JTL\Backend\Revision;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Mail\Admin\Controller;
use JTL\Mail\Hydrator\TestHydrator;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Template\Model;
use JTL\Mail\Template\TemplateFactory;
use JTL\Mail\Validator\NullValidator;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use JTL\Smarty\MailSmarty;
use function Functional\filter;
use function Functional\first;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global JTLSmarty $smarty */

$oAccount->permission('CONTENT_EMAIL_TEMPLATE_VIEW', true, true);
Shop::Container()->getCache()->flushTags([Status::CACHE_ID_EMAIL_SYNTAX_CHECK]);

$mailTemplate        = null;
$hasError            = false;
$continue            = true;
$emailTemplate       = null;
$attachmentErrors    = [];
$step                = 'uebersicht';
$conf                = Shop::getSettings([CONF_EMAILS]);
$settingsTableName   = 'temailvorlageeinstellungen';
$pluginSettingsTable = 'tpluginemailvorlageeinstellungen';
$db                  = Shop::Container()->getDB();
$alertHelper         = Shop::Container()->getAlertService();
$emailTemplateID     = Request::verifyGPCDataInt('kEmailvorlage');
$pluginID            = Request::verifyGPCDataInt('kPlugin');
$settings            = Shopsetting::getInstance();
$renderer            = new SmartyRenderer(new MailSmarty($db));
$hydrator            = new TestHydrator($renderer->getSmarty(), $db, $settings);
$validator           = new NullValidator();
$mailer              = new Mailer($hydrator, $renderer, $settings, $validator);
$mail                = new Mail();
$factory             = new TemplateFactory($db);
$controller          = new Controller($db, $mailer, $factory);
if ($pluginID > 0) {
    $settingsTableName = $pluginSettingsTable;
}
if (isset($_GET['err'])) {
    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorTemplate'), 'errorTemplate');
    if (is_array($_SESSION['last_error'])) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, $_SESSION['last_error']['message'], 'last_error');
        unset($_SESSION['last_error']);
    }
}
if (Request::postInt('resetConfirm') > 0) {
    $mailTemplate = $controller->getTemplateByID(Request::postInt('resetConfirm'));
    if ($mailTemplate !== null) {
        $step = 'zuruecksetzen';
    }
}
if (isset($_POST['resetConfirmJaSubmit'])
    && Request::postInt('resetEmailvorlage') === 1
    && $emailTemplateID > 0
    && Form::validateToken()
    && $controller->getTemplateByID($emailTemplateID) !== null
) {
    $controller->resetTemplate($emailTemplateID);
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successTemplateReset'), 'successTemplateReset');
}
if (Request::postInt('preview') > 0) {
    $state = $controller->sendPreviewMails(Request::postInt('preview'));
    if ($state === $controller::OK) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successEmailSend'), 'successEmailSend');
    } elseif ($state === $controller::ERROR_CANNOT_SEND) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorEmailSend'), 'errorEmailSend');
    }
    foreach ($controller->getErrorMessages() as $i => $msg) {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            $msg,
            'sentError' . $i
        );
    }
}
if ($emailTemplateID > 0 && Request::verifyGPCDataInt('Aendern') === 1 && Form::validateToken()) {
    $step     = 'uebersicht';
    $revision = new Revision($db);
    $revision->addRevision('mail', $emailTemplateID, true);

    $db->delete($settingsTableName, 'kEmailvorlage', $emailTemplateID);
    if (mb_strlen(Request::verifyGPDataString('cEmailOut')) > 0) {
        saveEmailSetting($settingsTableName, $emailTemplateID, 'cEmailOut', Request::verifyGPDataString('cEmailOut'));
    }
    if (mb_strlen(Request::verifyGPDataString('cEmailSenderName')) > 0) {
        saveEmailSetting(
            $settingsTableName,
            $emailTemplateID,
            'cEmailSenderName',
            Request::verifyGPDataString('cEmailSenderName')
        );
    }
    if (mb_strlen(Request::verifyGPDataString('cEmailCopyTo')) > 0) {
        saveEmailSetting(
            $settingsTableName,
            $emailTemplateID,
            'cEmailCopyTo',
            Request::verifyGPDataString('cEmailCopyTo')
        );
    }

    if ($hasError === false) {
        $res = $controller->updateTemplate($emailTemplateID, $_POST, $_FILES);
        if ($res === $controller::OK) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successTemplateEdit'), 'successTemplateEdit');
            $step     = 'uebersicht';
            $continue = (bool)Request::verifyGPCDataInt('continue');
            $doCheck  = $emailTemplateID;
        } else {
            $mailTemplate = $controller->getModel();
            foreach ($controller->getErrorMessages() as $i => $msg) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    $msg,
                    'errorUpload' . $i
                );
            }
        }
    }
}
if ((($emailTemplateID > 0 && $continue === true)
        || $step === 'prebearbeiten'
        || Request::getVar('a') === 'pdfloeschen'
    ) && Form::validateToken()
) {
    $uploadDir = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;
    if (isset($_GET['kS'], $_GET['token'])
        && $_GET['token'] === $_SESSION['jtl_token']
        && Request::getVar('a') === 'pdfloeschen'
    ) {
        $languageID = Request::verifyGPCDataInt('kS');
        $controller->deleteAttachments($emailTemplateID, $languageID);
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successFileAppendixDelete'), 'successFileAppendixDelete');
    }

    $step        = 'bearbeiten';
    $config      = $db->selectAll($settingsTableName, 'kEmailvorlage', $emailTemplateID);
    $configAssoc = [];
    foreach ($config as $item) {
        $configAssoc[$item->cKey] = $item->cValue;
    }
    $mailTemplate = $mailTemplate ?? $controller->getTemplateByID($emailTemplateID);
    $smarty->assign('availableLanguages', LanguageHelper::getAllLanguages())
           ->assign('mailConfig', $configAssoc)
           ->assign('cUploadVerzeichnis', $uploadDir);
}

if ($step === 'uebersicht') {
    $templates = $controller->getAllTemplates();
    $smarty->assign('mailTemplates', filter($templates, static function (Model $e) {
        return $e->getPluginID() === 0;
    }))
        ->assign('pluginMailTemplates', filter($templates, static function (Model $e) {
            return $e->getPluginID() > 0;
        }));
}
$smarty->assign('kPlugin', $pluginID)
       ->assign('mailTemplate', $mailTemplate)
       ->assign('checkTemplate', $doCheck ?? 0)
       ->assign('cFehlerAnhang_arr', $attachmentErrors)
       ->assign('step', $step)
       ->assign('Einstellungen', $conf)
       ->display('emailvorlagen.tpl');

/**
 * @param string $settingsTable
 * @param int    $emailTemplateID
 * @param string $key
 * @param string $value
 */
function saveEmailSetting(string $settingsTable, int $emailTemplateID, string $key, string $value)
{
    if ($emailTemplateID > 0 && mb_strlen($settingsTable) > 0 && mb_strlen($key) > 0 && mb_strlen($value) > 0) {
        $conf                = new stdClass();
        $conf->kEmailvorlage = $emailTemplateID;
        $conf->cKey          = $key;
        $conf->cValue        = $value;

        Shop::Container()->getDB()->insert($settingsTable, $conf);
    }
}
