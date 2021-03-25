<?php

use Illuminate\Support\Collection;
use JTL\Backend\AdminTemplate;
use JTL\Backend\Notification;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\License\Checker;
use JTL\License\Manager;
use JTL\License\Mapper;
use JTL\Plugin\Admin\StateChanger;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\State;
use JTL\Shop;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use JTL\Update\Updater;

/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global array $adminMenu */

require_once __DIR__ . '/admin_menu.php';

$smarty             = JTLSmarty::getInstance(false, ContextType::BACKEND);
$template           = AdminTemplate::getInstance();
$config             = Shop::getSettings([CONF_GLOBAL]);
$shopURL            = Shop::getURL();
$adminURL           = Shop::getAdminURL();
$db                 = Shop::Container()->getDB();
$currentTemplateDir = $smarty->getTemplateUrlPath();
$updates            = new Collection();
$updater            = new Updater($db);
$hasPendingUpdates  = $updater->hasPendingUpdates();
$resourcePaths      = $template->getResources(isset($config['template']['general']['use_minify'])
    && $config['template']['general']['use_minify'] !== 'N');
$adminLoginGruppe   = !empty($oAccount->account()->oGroup->kAdminlogingruppe)
    ? (int)$oAccount->account()->oGroup->kAdminlogingruppe
    : -1;
$currentToplevel    = 0;
$currentSecondLevel = 0;
$currentThirdLevel  = 0;
$mainGroups         = [];
$rootKey            = 0;
$expired            = collect([]);
if (!$hasPendingUpdates) {
    $jtlSearch                    = $db->query(
        "SELECT kPlugin, cName
            FROM tplugin
            WHERE cPluginID = 'jtl_search'",
        ReturnType::SINGLE_OBJECT
    );
    $curScriptFileNameWithRequest = basename($_SERVER['REQUEST_URI'] ?? 'index.php');
    foreach ($adminMenu as $rootName => $rootEntry) {
        $rootKey   = (string)$rootKey;
        $mainGroup = (object)[
            'cName'           => $rootName,
            'icon'            => $rootEntry->icon,
            'oLink_arr'       => [],
            'oLinkGruppe_arr' => [],
            'key'             => $rootKey,
        ];

        $secondKey = 0;

        foreach ($rootEntry->items as $secondName => $secondEntry) {
            $linkGruppe = (object)[
                'cName'     => $secondName,
                'oLink_arr' => [],
                'key'       => $rootKey . $secondKey,
            ];

            if ($secondEntry === 'DYNAMIC_PLUGINS') {
                if (!$oAccount->permission('PLUGIN_ADMIN_VIEW') || SAFE_MODE === true) {
                    continue;
                }
                $pluginLinks = $db->queryPrepared(
                    'SELECT DISTINCT p.kPlugin, p.cName, p.nPrio
                        FROM tplugin AS p INNER JOIN tpluginadminmenu AS pam
                            ON p.kPlugin = pam.kPlugin
                        WHERE p.nStatus = :state
                        ORDER BY p.nPrio, p.cName',
                    ['state' => State::ACTIVATED],
                    ReturnType::ARRAY_OF_OBJECTS
                );

                foreach ($pluginLinks as $pluginLink) {
                    $pluginID = (int)$pluginLink->kPlugin;
                    Shop::Container()->getGetText()->loadPluginLocale(
                        'base',
                        PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID)
                    );

                    $link = (object)[
                        'cLinkname' => __($pluginLink->cName),
                        'cURL'      => $adminURL . '/plugin.php?kPlugin=' . $pluginID,
                        'cRecht'    => 'PLUGIN_ADMIN_VIEW',
                        'key'       => $rootKey . $secondKey . $pluginID,
                    ];

                    $linkGruppe->oLink_arr[] = $link;
                    if (Request::getInt('kPlugin') === $pluginID) {
                        $currentToplevel    = $mainGroup->key;
                        $currentSecondLevel = $linkGruppe->key;
                        $currentThirdLevel  = $link->key;
                    }
                }
            } else {
                $thirdKey = 0;

                if (is_object($secondEntry)) {
                    if (!$oAccount->permission($secondEntry->permissions)) {
                        continue;
                    }
                    $linkGruppe->oLink_arr = (object)[
                        'cLinkname' => $secondName,
                        'cURL'      => $secondEntry->link,
                        'cRecht'    => $secondEntry->permissions,
                    ];
                    if (Request::urlHasEqualRequestParameter($linkGruppe->oLink_arr->cURL, 'kSektion')
                        && strpos($curScriptFileNameWithRequest, $linkGruppe->oLink_arr->cURL) === 0
                    ) {
                        $currentToplevel    = $mainGroup->key;
                        $currentSecondLevel = $linkGruppe->key;
                    }
                } else {
                    foreach ($secondEntry as $thirdName => $thirdEntry) {
                        if ($thirdEntry === 'DYNAMIC_JTL_SEARCH' && ($jtlSearch->kPlugin ?? 0) > 0) {
                            $link = (object)[
                                'cLinkname' => 'JTL Search',
                                'cURL'      => $adminURL . '/plugin.php?kPlugin=' . $jtlSearch->kPlugin,
                                'cRecht'    => 'PLUGIN_ADMIN_VIEW',
                                'key'       => $rootKey . $secondKey . $thirdKey,
                            ];
                        } elseif (is_object($thirdEntry)) {
                            $link = (object)[
                                'cLinkname' => $thirdName,
                                'cURL'      => $thirdEntry->link,
                                'cRecht'    => $thirdEntry->permissions,
                                'key'       => $rootKey . $secondKey . $thirdKey,
                            ];
                        } else {
                            continue;
                        }
                        if (!$oAccount->permission($link->cRecht)) {
                            continue;
                        }
                        $urlParts             = parse_url($link->cURL);
                        $urlParts['basename'] = basename($urlParts['path']);

                        if (empty($urlParts['query'])) {
                            $urlParts['query'] = [];
                        } else {
                            mb_parse_str($urlParts['query'], $urlParts['query']);
                        }

                        if (Request::urlHasEqualRequestParameter($link->cURL, 'kSektion')
                            && strpos($curScriptFileNameWithRequest, explode('#', $link->cURL)[0]) === 0
                        ) {
                            $currentToplevel    = $mainGroup->key;
                            $currentSecondLevel = $linkGruppe->key;
                            $currentThirdLevel  = $link->key;
                        }

                        $linkGruppe->oLink_arr[] = $link;
                        $thirdKey++;
                    }
                }
            }

            if (is_object($linkGruppe->oLink_arr) || count($linkGruppe->oLink_arr) > 0) {
                $mainGroup->oLinkGruppe_arr[] = $linkGruppe;
            }
            $secondKey++;
        }

        if (count($mainGroup->oLinkGruppe_arr) > 0) {
            $mainGroups[] = $mainGroup;
        }
        $rootKey++;
    }
    if (Request::getVar('licensenoticeaccepted') === 'true') {
        $_SESSION['licensenoticeaccepted'] = 0;
    }
    if (Request::postVar('action') === 'disable-expired-plugins' && Form::validateToken()) {
        $sc = new StateChanger($db, Shop::Container()->getCache());
        foreach ($_POST['pluginID'] as $pluginID) {
            $sc->deactivate((int)$pluginID);
        }
    }
    $cache                 = Shop::Container()->getCache();
    $mapper                = new Mapper(new Manager($db, $cache));
    $checker               = new Checker(Shop::Container()->getBackendLogService(), $db, $cache);
    $updates               = $checker->getUpdates($mapper);
    $licenseNoticeAccepted = (int)($_SESSION['licensenoticeaccepted'] ?? -1);
    if ($licenseNoticeAccepted === -1 && SAFE_MODE === false) {
        $expired = $checker->getLicenseViolations($mapper);
    } else {
        $licenseNoticeAccepted++;
    }
    if ($licenseNoticeAccepted > 5) {
        $licenseNoticeAccepted = -1;
    }
    $_SESSION['licensenoticeaccepted'] = $licenseNoticeAccepted;
}
if (empty($template->version)) {
    $adminTplVersion = '1.0.0';
} else {
    $adminTplVersion = $template->version;
}
$langTag = $_SESSION['AdminAccount']->language ?? Shop::Container()->getGetText()->getLanguage();
$smarty->assign('URL_SHOP', $shopURL)
    ->assign('expiredLicenses', $expired)
    ->assign('jtl_token', Form::getTokenInput())
    ->assign('shopURL', $shopURL)
    ->assign('adminURL', $adminURL)
    ->assign('adminTplVersion', $adminTplVersion)
    ->assign('PFAD_ADMIN', PFAD_ADMIN)
    ->assign('JTL_CHARSET', JTL_CHARSET)
    ->assign('session_name', session_name())
    ->assign('session_id', session_id())
    ->assign('currentTemplateDir', $currentTemplateDir)
    ->assign('templateBaseURL', $adminURL . '/' . $currentTemplateDir)
    ->assign('lang', 'german')
    ->assign('admin_css', $resourcePaths['css'])
    ->assign('admin_js', $resourcePaths['js'])
    ->assign('account', $oAccount->account())
    ->assign('PFAD_CKEDITOR', $shopURL . '/' . PFAD_CKEDITOR)
    ->assign('PFAD_CODEMIRROR', $shopURL . '/' . PFAD_CODEMIRROR)
    ->assign('Einstellungen', $config)
    ->assign('oLinkOberGruppe_arr', $mainGroups)
    ->assign('currentMenuPath', [$currentToplevel, $currentSecondLevel, $currentThirdLevel])
    ->assign('notifications', Notification::getInstance($db))
    ->assign('licenseItemUpdates', $updates)
    ->assign('alertList', Shop::Container()->getAlertService())
    ->assign('favorites', $oAccount->favorites())
    ->assign('language', $langTag)
    ->assign('hasPendingUpdates', $hasPendingUpdates)
    ->assign('sprachen', LanguageHelper::getInstance()->gibInstallierteSprachen())
    ->assign('availableLanguages', LanguageHelper::getInstance()->gibInstallierteSprachen())
    ->assign('languageName', Locale::getDisplayLanguage($langTag, $langTag))
    ->assign('languages', Shop::Container()->getGetText()->getAdminLanguages())
    ->assign('faviconAdminURL', Shop::getFaviconURL(true))
    ->assign('cTab', Text::filterXSS(Request::verifyGPDataString('tab')))
    ->assign(
        'wizardDone',
        (($conf['global']['global_wizard_done'] ?? 'Y') === 'Y'
            || strpos($_SERVER['SCRIPT_NAME'], 'wizard.php') === false)
        && !Request::getVar('fromWizard')
    );
