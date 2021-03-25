<?php

use JTL\Backend\AdminIO;
use JTL\Backend\JSONAPI;
use JTL\Backend\Notification;
use JTL\Backend\TwoFA;
use JTL\Backend\Wizard\WizardIO;
use JTL\Exportformat;
use JTL\Helpers\Form;
use JTL\IO\IOError;
use JTL\Jtllog;
use JTL\Link\Admin\LinkAdmin;
use JTL\Mail\Validator\SyntaxChecker;
use JTL\Media\Manager;
use JTL\Plugin\Helper;
use JTL\Shop;
use JTL\Update\UpdateIO;

/** @global \JTL\Backend\AdminAccount $oAccount */

ob_start();
require_once __DIR__ . '/includes/admininclude.php';

if (!$oAccount->getIsAuthenticated()) {
    AdminIO::getInstance()->respondAndExit(new IOError('Not authenticated as admin.', 401));
}
if (!Form::validateToken()) {
    AdminIO::getInstance()->respondAndExit(new IOError('CSRF validation failed.', 403));
}

$db           = Shop::Container()->getDB();
$gettext      = Shop::Container()->getGetText();
$cache        = Shop::Container()->getCache();
$alertService = Shop::Container()->getAlertService();
$jsonApi      = JSONAPI::getInstance();
$io           = AdminIO::getInstance()->setAccount($oAccount);
$images       = new Manager($db, $gettext);
$updateIO     = new UpdateIO($db, $gettext);
$wizardIO     = new WizardIO($db, $cache, $alertService, $gettext);

try {
    Shop::Container()->getOPC()->registerAdminIOFunctions($io);
    Shop::Container()->getOPCPageService()->registerAdminIOFunctions($io);
} catch (Exception $e) {
    $io->respondAndExit(new IOError($e->getMessage(), $e->getCode()));
}

$dashboardInc       = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dashboard_inc.php';
$accountInc         = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'benutzerverwaltung_inc.php';
$bannerInc          = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'banner_inc.php';
$sucheInc           = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'suche_inc.php';
$sucheinstellungInc = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'sucheinstellungen_inc.php';
$plzimportInc       = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'plz_ort_import_inc.php';
$redirectInc        = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'redirect_inc.php';
$dbcheckInc         = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbcheck_inc.php';
$versandartenInc    = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'versandarten_inc.php';

try {
    $io->register('getPages', [$jsonApi, 'getPages'])
       ->register('getCategories', [$jsonApi, 'getCategories'])
       ->register('getProducts', [$jsonApi, 'getProducts'])
       ->register('getManufacturers', [$jsonApi, 'getManufacturers'])
       ->register('getCustomers', [$jsonApi, 'getCustomers'])
       ->register('getSeos', [$jsonApi, 'getSeos'])
       ->register('getAttributes', [$jsonApi, 'getAttributes'])
       ->register('isDuplicateSpecialLink', [LinkAdmin::class, 'isDuplicateSpecialLink'])
       ->register('getCurrencyConversion', 'getCurrencyConversionIO')
       ->register('setCurrencyConversionTooltip', 'setCurrencyConversionTooltipIO')
       ->register('getNotifyDropIO')
       ->register('getNewTwoFA', [TwoFA::class, 'getNewTwoFA'])
       ->register('genTwoFAEmergencyCodes', [TwoFA::class, 'genTwoFAEmergencyCodes'])
       ->register('setWidgetPosition', 'setWidgetPosition', $dashboardInc, 'DASHBOARD_VIEW')
       ->register('closeWidget', 'closeWidget', $dashboardInc, 'DASHBOARD_VIEW')
       ->register('addWidget', 'addWidget', $dashboardInc, 'DASHBOARD_VIEW')
       ->register('expandWidget', 'expandWidget', $dashboardInc, 'DASHBOARD_VIEW')
       ->register('getAvailableWidgets', 'getAvailableWidgetsIO', $dashboardInc, 'DASHBOARD_VIEW')
       ->register('getRemoteData', 'getRemoteDataIO', $dashboardInc, 'DASHBOARD_VIEW')
       ->register('getShopInfo', 'getShopInfoIO', $dashboardInc, 'DASHBOARD_VIEW')
       ->register('truncateJtllog', [Jtllog::class, 'truncateLog'], null, 'DASHBOARD_VIEW')
       ->register('addFav')
       ->register('reloadFavs')
       ->register('loadStats', [$images, 'loadStats'], null, 'DISPLAY_IMAGES_VIEW')
       ->register('cleanupStorage', [$images, 'cleanupStorage'], null, 'DISPLAY_IMAGES_VIEW')
       ->register('clearImageCache', [$images, 'clearImageCache'], null, 'DISPLAY_IMAGES_VIEW')
       ->register('generateImageCache', [$images, 'generateImageCache'], null, 'DISPLAY_IMAGES_VIEW')
       ->register('plzimportActionLoadAvailableDownloads', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
       ->register('plzimportActionDoImport', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
       ->register('plzimportActionResetImport', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
       ->register('plzimportActionCallStatus', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
       ->register('plzimportActionUpdateIndex', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
       ->register('plzimportActionRestoreBackup', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
       ->register('plzimportActionCheckStatus', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
       ->register('plzimportActionDelTempImport', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
       ->register('dbUpdateIO', [$updateIO, 'update'], null, 'SHOP_UPDATE_VIEW')
       ->register('dbupdaterBackup', [$updateIO, 'backup'], null, 'SHOP_UPDATE_VIEW')
       ->register('dbupdaterDownload', [$updateIO, 'download'], null, 'SHOP_UPDATE_VIEW')
       ->register('dbupdaterStatusTpl', [$updateIO, 'getStatus'], null, 'SHOP_UPDATE_VIEW')
       ->register('dbupdaterMigration', [$updateIO, 'executeMigration'], null, 'SHOP_UPDATE_VIEW')
       ->register('finishWizard', [$wizardIO, 'answerQuestions'], null, 'WIZARD_VIEW')
       ->register('validateStepWizard', [$wizardIO, 'validateStep'], null, 'WIZARD_VIEW')
       ->register('migrateToInnoDB_utf8', 'doMigrateToInnoDB_utf8', $dbcheckInc, 'DBCHECK_VIEW')
       ->register('redirectCheckAvailability', [JTL\Redirect::class, 'checkAvailability'])
       ->register('updateRedirectState', null, $redirectInc, 'REDIRECT_VIEW')
       ->register('getRandomPassword', 'getRandomPasswordIO', $accountInc, 'ACCOUNT_VIEW')
       ->register('saveBannerAreas', 'saveBannerAreasIO', $bannerInc, 'DISPLAY_BANNER_VIEW')
       ->register('createSearchIndex', 'createSearchIndex', $sucheinstellungInc, 'SETTINGS_ARTICLEOVERVIEW_VIEW')
       ->register('clearSearchCache', 'clearSearchCache', $sucheinstellungInc, 'SETTINGS_ARTICLEOVERVIEW_VIEW')
       ->register('adminSearch', 'adminSearch', $sucheInc, 'SETTINGS_SEARCH_VIEW')
       ->register('saveShippingSurcharge', 'saveShippingSurcharge', $versandartenInc, 'ORDER_SHIPMENT_VIEW')
       ->register('deleteShippingSurcharge', 'deleteShippingSurcharge', $versandartenInc, 'ORDER_SHIPMENT_VIEW')
       ->register('deleteShippingSurchargeZIP', 'deleteShippingSurchargeZIP', $versandartenInc, 'ORDER_SHIPMENT_VIEW')
       ->register('createShippingSurchargeZIP', 'createShippingSurchargeZIP', $versandartenInc, 'ORDER_SHIPMENT_VIEW')
       ->register('getShippingSurcharge', 'getShippingSurcharge', $versandartenInc, 'ORDER_SHIPMENT_VIEW')
       ->register('exportformatSyntaxCheck', [Exportformat::class, 'ioCheckSyntax'], null, 'EXPORT_FORMATS_VIEW')
       ->register('mailvorlageSyntaxCheck', [SyntaxChecker::class, 'ioCheckSyntax'], null, 'CONTENT_EMAIL_TEMPLATE_VIEW')
       ->register('notificationAction', [Notification::class, 'ioNotification'])
       ->register('pluginTestLoading', [Helper::class, 'ioTestLoading']);
} catch (Exception $e) {
    $io->respondAndExit(new IOError($e->getMessage(), $e->getCode()));
}

$request = $_REQUEST['io'];

executeHook(HOOK_IO_HANDLE_REQUEST_ADMIN, [
    'io'      => &$io,
    'request' => &$request
]);

ob_end_clean();
$io->respondAndExit($io->handleRequest($request));
