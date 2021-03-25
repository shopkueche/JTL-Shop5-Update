<?php

use JTL\Backend\AdminLoginStatus;
use JTL\Backend\Revision;
use JTL\Events\Event;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Profiler;
use JTL\Services\JTL\CaptchaServiceInterface;
use JTL\Services\JTL\SimpleCaptchaService;
use JTL\Session\Backend;
use JTL\Shop;
use JTL\Update\Updater;
use JTLShop\SemVer\Version;

if (!isset($bExtern) || !$bExtern) {
    if (isset($_REQUEST['safemode'])) {
        $GLOBALS['plgSafeMode'] = in_array(strtolower($_REQUEST['safemode']), ['1', 'on', 'ein', 'true', 'wahr']);
    }
    define('DEFINES_PFAD', __DIR__ . '/../../includes/');
    require DEFINES_PFAD . 'config.JTL-Shop.ini.php';
    require DEFINES_PFAD . 'defines.php';
    require PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admindefines.php';
    defined('DB_HOST') || die('Kein MySQL-Datenbank Host angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    defined('DB_NAME') || die('Kein MySQL Datenbanknamen angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    defined('DB_USER') || die('Kein MySQL-Datenbank Benutzer angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    defined('DB_PASS') || die('Kein MySQL-Datenbank Passwort angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
}

require PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';
require PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require PFAD_ROOT . PFAD_INCLUDES . 'error_handler.php';
require PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
require PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';
require PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'benutzerverwaltung_inc.php';
require PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_tools.php';

define(
    'JTL_VERSION',
    (int)sprintf(
        '%d%02d',
        Version::parse(APPLICATION_VERSION)->getMajor(),
        Version::parse(APPLICATION_VERSION)->getMinor()
    )
); // DEPRECATED since 5.0.0
define('JTL_MINOR_VERSION', (int)Version::parse(APPLICATION_VERSION)->getPatch()); // DEPRECATED since 5.0.0

if (!function_exists('Shop')) {
    /**
     * @return Shop
     */
    function Shop()
    {
        return Shop::getInstance();
    }
}
Profiler::start();
$db    = Shop::Container()->getDB();
$cache = Shop::Container()->getCache()->setJtlCacheConfig(
    $db->selectAll('teinstellungen', 'kEinstellungenSektion', CONF_CACHING)
);
Shop::setIsFrontend(false);
$session    = Backend::getInstance();
$lang       = LanguageHelper::getInstance($db, $cache);
$oAccount   = Shop::Container()->getAdminAccount();
$loggedIn   = $oAccount->logged();
$updater    = new Updater($db);
$hasUpdates = $updater->hasPendingUpdates();
$conf       = Shop::getSettings([CONF_GLOBAL]);

if ($loggedIn && isset($GLOBALS['plgSafeMode'])) {
    if ($GLOBALS['plgSafeMode']) {
        touch(PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR . 'safemode.lck');
    } elseif (file_exists(PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR . 'safemode.lck')) {
        unlink(PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR . 'safemode.lck');
    }
}

if (!empty($_COOKIE['JTLSHOP']) && empty($_SESSION['frontendUpToDate'])) {
    $adminToken   = $_SESSION['jtl_token'];
    $adminLangTag = $_SESSION['AdminAccount']->language;
    $eSIdAdm      = session_id();
    session_write_close();
    session_name('JTLSHOP');
    session_id($_COOKIE['JTLSHOP']);
    session_start();
    $_SESSION['loggedAsAdmin'] = $loggedIn;
    $_SESSION['adminToken']    = $adminToken;
    $_SESSION['adminLangTag']  = $adminLangTag;
    session_write_close();
    session_name('eSIdAdm');
    session_id($eSIdAdm);
    $session = new Backend();
    $session::set('frontendUpToDate', true);
}

if ($loggedIn
    && $_SERVER['REQUEST_METHOD'] === 'GET'
    && Request::verifyGPDataString('action') !== 'quick_change_language'
    && strpos($_SERVER['SCRIPT_FILENAME'], 'logout') === false
    && strpos($_SERVER['SCRIPT_FILENAME'], 'dbupdater') === false
    && strpos($_SERVER['SCRIPT_FILENAME'], 'io.php') === false
    && $hasUpdates
) {
    header('Location: ' . Shop::getAdminURL(true) . '/dbupdater.php');
    exit;
}
if ($loggedIn
    && ($conf['global']['global_wizard_done'] ?? 'Y') === 'N'
    && strpos($_SERVER['SCRIPT_FILENAME'], 'wizard') === false
    && !$hasUpdates
    && !Backend::get('redirectedToWizard')
) {
    header('Location: ' . Shop::getAdminURL(true) . '/wizard.php');
    exit;
}

require PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'smartyinclude.php';

Shop::Container()->singleton(CaptchaServiceInterface::class, static function () {
    return new SimpleCaptchaService(true);
});
if (!$hasUpdates) {
    Shop::bootstrap(false);
}
if ($loggedIn) {
    if (!$session->isValid()) {
        $oAccount->logout();
        $oAccount->redirectOnFailure(AdminLoginStatus::ERROR_SESSION_INVALID);
    }

    if (isset($_POST['revision-action'], $_POST['revision-type'], $_POST['revision-id']) && Form::validateToken()) {
        $revision = new Revision($db);
        Shop::fire(Event::REVISION_RESTORE_DELETE, ['revision' => $revision]);
        if ($_POST['revision-action'] === 'restore') {
            $revision->restoreRevision(
                $_POST['revision-type'],
                $_POST['revision-id'],
                Request::postInt('revision-secondary') === 1
            );
        } elseif ($_POST['revision-action'] === 'delete') {
            $revision->deleteRevision($_POST['revision-id']);
        }
    }
}

Shop::Container()->getGetText()->loadAdminLocale('pages/' . basename($_SERVER['PHP_SELF'], '.php'));
