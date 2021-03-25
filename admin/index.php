<?php

use JTL\Alert\Alert;
use JTL\Backend\AdminLoginStatus;
use JTL\Backend\Status;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Profiler;
use JTL\Session\Backend;
use JTL\Shop;
use JTL\Update\Updater;
use JTLShop\SemVer\Version;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Smarty\JTLSmarty     $smarty */
/** @global \JTL\Backend\AdminAccount $oAccount */
$db          = Shop::Container()->getDB();
$alertHelper = Shop::Container()->getAlertService();
$cache       = Shop::Container()->getCache();
$oUpdater    = new Updater($db);
if (Request::postInt('adminlogin') === 1) {
    $csrfOK = true;
    // Check if shop version is new enough for csrf validation
    if (Shop::getShopDatabaseVersion()->equals(Version::parse('4.0.0'))
        || Shop::getShopDatabaseVersion()->greaterThan(Version::parse('4.0.0'))
    ) {
        $csrfOK = Form::validateToken();
    }
    $loginName = isset($_POST['benutzer'])
        ? Text::filterXSS($db->escape($_POST['benutzer']))
        : '---';
    if ($csrfOK === true) {
        switch ($oAccount->login($_POST['benutzer'], $_POST['passwort'])) {
            case AdminLoginStatus::ERROR_LOCKED:
            case AdminLoginStatus::ERROR_INVALID_PASSWORD_LOCKED:
                $lockTime = $oAccount->getLockedMinutes();
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    sprintf(__('lockForMinutes'), $lockTime),
                    'errorFillRequired'
                );
                break;

            case AdminLoginStatus::ERROR_USER_NOT_FOUND:
            case AdminLoginStatus::ERROR_INVALID_PASSWORD:
                if (empty(Request::verifyGPDataString('TwoFA_code'))) {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorWrongPasswordUser'), 'errorWrongPasswordUser');
                }
                break;

            case AdminLoginStatus::ERROR_USER_DISABLED:
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorLoginTemporaryNotPossible'),
                    'errorLoginTemporaryNotPossible'
                );
                break;

            case AdminLoginStatus::ERROR_LOGIN_EXPIRED:
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLoginDataExpired'), 'errorLoginDataExpired');
                break;

            case AdminLoginStatus::ERROR_TWO_FACTOR_AUTH_EXPIRED:
                if (isset($_SESSION['AdminAccount']->TwoFA_expired)
                    && $_SESSION['AdminAccount']->TwoFA_expired === true
                ) {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorTwoFactorExpired'), 'errorTwoFactorExpired');
                }
                break;

            case AdminLoginStatus::ERROR_NOT_AUTHORIZED:
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorNoPermission'), 'errorNoPermission');
                break;

            case AdminLoginStatus::LOGIN_OK:
                Status::getInstance($db, $cache, true);
                Backend::getInstance()->reHash();
                $safeMode                 = isset($GLOBALS['plgSafeMode'])
                    ? '?safemode=' . ($GLOBALS['plgSafeMode'] ? 'on' : 'off')
                    : '';
                $_SESSION['loginIsValid'] = true; // "enable" the "header.tpl"-navigation again
                if (($conf['global']['global_wizard_done'] ?? 'Y') === 'N') {
                    header('Location: ' . Shop::getAdminURL(true) . '/wizard.php' . $safeMode);
                    exit;
                }
                if ($oAccount->permission('SHOP_UPDATE_VIEW') && $oUpdater->hasPendingUpdates()) {
                    header('Location: ' . Shop::getAdminURL(true) . '/dbupdater.php' . $safeMode);
                    exit;
                }
                if (isset($_REQUEST['uri']) && mb_strlen(trim($_REQUEST['uri'])) > 0) {
                    redirectToURI($_REQUEST['uri']);
                }
                header('Location: ' . Shop::getAdminURL(true) . '/index.php' . $safeMode);
                exit;

                break;
        }
    } elseif (isset($_COOKIE['eSIdAdm'])) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCSRF'), 'errorCSRF');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCookieSettings'), 'errorCookieSettings');
    }
}
$type          = '';
$profilerState = Profiler::getIsActive();
switch ($profilerState) {
    case 0:
    default:
        $type = '';
        break;
    case 1:
        $type = 'Datenbank';
        break;
    case 2:
        $type = 'XHProf';
        break;
    case 3:
        $type = 'Plugin';
        break;
    case 4:
        $type = 'Plugin- und XHProf';
        break;
    case 5:
        $type = 'Datenbank- und Plugin';
        break;
    case 6:
        $type = 'Datenbank- und XHProf';
        break;
    case 7:
        $type = 'Datenbank-, XHProf und Plugin';
        break;
}

$smarty->assign('bProfilerActive', $profilerState !== 0)
       ->assign('profilerType', $type)
       ->assign('pw_updated', Request::getVar('pw_updated') === 'true')
       ->assign('alertError', $alertHelper->alertTypeExists(Alert::TYPE_ERROR))
       ->assign('alertList', $alertHelper)
       ->assign('updateMessage', $updateMessage ?? null)
       ->assign('plgSafeMode', $GLOBALS['plgSafeMode'] ?? false);

/**
 * opens the dashboard
 * (prevents code duplication)
 */
function openDashboard()
{
    global $oAccount;

    $smarty = Shop::Smarty();
    if (isset($_REQUEST['uri']) && mb_strlen(trim($_REQUEST['uri'])) > 0) {
        redirectToURI($_REQUEST['uri']);
    }
    $_SESSION['loginIsValid'] = true;
    if ($oAccount->permission('DASHBOARD_VIEW')) {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dashboard_inc.php';

        $smarty->assign('bDashboard', true)
               ->assign('bUpdateError', (Request::postInt('shopupdate') === 1 ? '1' : false))
               ->assign('oActiveWidget_arr', getWidgets())
               ->assign('oAvailableWidget_arr', getWidgets(false))
               ->assign('bInstallExists', is_dir(PFAD_ROOT . 'install'));
    }
    $smarty->display('dashboard.tpl');
    exit();
}

/**
 * redirects to a given (base64-encoded) URI
 * (prevents code duplication)
 * @param string $uri
 */
function redirectToURI($uri)
{
    header('Location: ' . Shop::getAdminURL(true) . '/' . base64_decode($uri));
    exit;
}

unset($_SESSION['AdminAccount']->TwoFA_active);
if ($oAccount->getIsAuthenticated()) {
    Shop::Container()->getGetText()->loadAdminLocale('widgets');
    if (!$oAccount->getIsTwoFaAuthenticated()) {
        $_SESSION['AdminAccount']->TwoFA_active = true;
        // restore first generated token from POST
        $_SESSION['jtl_token'] = $_POST['jtl_token'] ?? '';
        if (Request::postVar('TwoFA_code', '') !== '') {
            if ($oAccount->doTwoFA()) {
                Backend::getInstance()->reHash();
                $_SESSION['AdminAccount']->TwoFA_expired = false;
                $_SESSION['AdminAccount']->TwoFA_valid   = true;
                $_SESSION['loginIsValid']                = true;
                openDashboard();
            } else {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorTwoFactorFaultyExpired'),
                    'errorTwoFactorFaultyExpired'
                );
                $smarty->assign('alertError', true);
            }
        } else {
            $_SESSION['AdminAccount']->TwoFA_expired = true;
        }
        Shop::Container()->getGetText()->loadAdminLocale('pages/login');
        $oAccount->redirectOnUrl();
        $smarty->assign('uri', isset($_REQUEST['uri']) && mb_strlen(trim($_REQUEST['uri'])) > 0
            ? trim($_REQUEST['uri'])
            : '')
               ->display('login.tpl');
        exit();
    }
    openDashboard();
} else {
    $oAccount->redirectOnUrl();
    if (Request::getInt('errCode', null) === AdminLoginStatus::ERROR_SESSION_INVALID) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSessionExpired'), 'errorSessionExpired');
    }
    Shop::Container()->getGetText()->loadAdminLocale('pages/login');
    $smarty->assign('uri', isset($_REQUEST['uri']) && mb_strlen(trim($_REQUEST['uri'])) > 0
        ? trim($_REQUEST['uri'])
        : '')
           ->assign('alertError', $alertHelper->alertTypeExists(Alert::TYPE_ERROR))
           ->assign('alertList', $alertHelper)
           ->display('login.tpl');
}
