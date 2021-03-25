<?php

use JTL\Debug\DataCollector\Smarty;
use JTL\Filter\Metadata;
use JTL\Helpers\PHPSettings;
use JTL\Language\LanguageHelper;
use JTL\Plugin\Helper;
use JTL\Profiler;
use JTL\Session\Frontend;
use JTL\Shop;
use JTLShop\SemVer\Version;

$nStartzeit = microtime(true);

if (file_exists(__DIR__ . '/config.JTL-Shop.ini.php')) {
    require_once __DIR__ . '/config.JTL-Shop.ini.php';
}

/**
 * @param string $message
 */
function handleFatal(string $message): void
{
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache', true, 500);
    die($message);
}

if (defined('PFAD_ROOT')) {
    require_once PFAD_ROOT . 'includes/defines.php';
} else {
    handleFatal('Could not get configuration from config file. ' .
        'For shop installation <a href="install/">click here</a>.');
}

require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';

defined('DB_HOST') || handleFatal('Kein MySql-Datenbank Host angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
defined('DB_NAME') || handleFatal('Kein MySql Datenbanknamen angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
defined('DB_USER') || handleFatal('Kein MySql-Datenbank Benutzer angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
defined('DB_PASS') || handleFatal('Kein MySql-Datenbank Passwort angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');

define(
    'JTL_VERSION',
    (int)sprintf(
        '%d%02d',
        Version::parse(APPLICATION_VERSION)->getMajor(),
        Version::parse(APPLICATION_VERSION)->getMinor()
    )
); // DEPRECATED since 5.0.0
define('JTL_MINOR_VERSION', (int)Version::parse(APPLICATION_VERSION)->getPatch()); // DEPRECATED since 5.0.0

Profiler::start();

$shop = Shop::getInstance();

if (!function_exists('Shop')) {
    /**
     * @return Shop
     */
    function Shop(): Shop
    {
        return Shop::getInstance();
    }
}
// PHP memory_limit work around
if (!PHPSettings::getInstance()->hasMinLimit(64 * 1024 * 1024)) {
    ini_set('memory_limit', '64M');
}

require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';

try {
    $db = Shop::Container()->getDB();
} catch (Exception $exc) {
    handleFatal($exc->getMessage());
}
require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
if (!defined('CLI_BATCHRUN')) {
    $cache = Shop::Container()->getCache();
    $cache->setJtlCacheConfig($db->selectAll('teinstellungen', 'kEinstellungenSektion', CONF_CACHING));
    $config = Shop::getSettings([CONF_GLOBAL])['global'];
    $lang   = LanguageHelper::getInstance($db, $cache);
}
if (PHP_SAPI !== 'cli'
    && $config['kaufabwicklung_ssl_nutzen'] === 'P'
    && (!isset($_SERVER['HTTPS'])
        || (mb_convert_case($_SERVER['HTTPS'], MB_CASE_LOWER) !== 'on' && (int)$_SERVER['HTTPS'] !== 1))
) {
    $https = ((isset($_SERVER['HTTP_X_FORWARDED_HOST']) && $_SERVER['HTTP_X_FORWARDED_HOST'] === 'ssl.webpack.de')
        || (isset($_SERVER['SCRIPT_URI']) && preg_match('/^ssl-id/', $_SERVER['SCRIPT_URI']))
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && preg_match('/^ssl/', $_SERVER['HTTP_X_FORWARDED_HOST']))
    );
    if (!$https && isset($_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI'])) {
        $lang = '';
        if (!LanguageHelper::isDefaultLanguageActive(true)) {
            $lang = mb_strpos($_SERVER['REQUEST_URI'], '?')
                ? '&lang=' . $_SESSION['cISOSprache']
                : '?lang=' . $_SESSION['cISOSprache'];
        }
        header('Location: https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . $lang, true, 301);
        exit();
    }
}
if (!JTL_INCLUDE_ONLY_DB && !defined('CLI_BATCHRUN')) {
    $debugbar = Shop::Container()->getDebugBar();

    require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'parameterhandler.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'artikelsuchspecial_inc.php';
    $pluginHooks    = Helper::getHookList();
    $globalMetaData = Metadata::getGlobalMetaData();
    executeHook(HOOK_GLOBALINCLUDE_INC);
    $session             = (defined('JTLCRON') && JTLCRON === true)
        ? Frontend::getInstance(true, true, 'JTLCRON')
        : Frontend::getInstance();
    $bAdminWartungsmodus = false;
    if ((SAFE_MODE === true || $config['wartungsmodus_aktiviert'] === 'Y')
        && basename($_SERVER['SCRIPT_FILENAME']) !== 'wartung.php'
    ) {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'benutzerverwaltung_inc.php';
        if (!Shop::isAdmin(true)) {
            http_response_code(503);
            require_once PFAD_ROOT . 'wartung.php';
            exit;
        }
        $bAdminWartungsmodus = true;
    }
    Shop::bootstrap();
    require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
    $debugbar->addCollector(new Smarty(Shop::Smarty()));
}
