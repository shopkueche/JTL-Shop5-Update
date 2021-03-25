<?php

use JTL\Alert\Alert;
use JTL\Backend\DirManager;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Minify\MinifyService;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
/** @global \JTL\Backend\AdminAccount $oAccount */
$oAccount->permission('OBJECTCACHE_VIEW', true, true);
$notice       = '';
$error        = '';
$step         = 'uebersicht';
$tab          = 'uebersicht';
$cache        = null;
$opcacheStats = null;
$options      = null;
$action       = Form::validateToken() ? Request::postVar('a') : null;
$cacheAction  = Request::postVar('cache-action', '');
$db           = Shop::Container()->getDB();
$getText      = Shop::Container()->getGetText();
$alertHelper  = Shop::Container()->getAlertService();
$getText->loadConfigLocales();

if (0 < mb_strlen(Request::verifyGPDataString('tab'))) {
    $smarty->assign('tab', Request::verifyGPDataString('tab'));
}
try {
    $cache = Shop::Container()->getCache();
    $cache->setJtlCacheConfig($db->selectAll('teinstellungen', 'kEinstellungenSektion', CONF_CACHING));
} catch (Exception $exc) {
    $alertHelper->addAlert(Alert::TYPE_ERROR, __('exception') . ': ' . $exc->getMessage(), 'errorException');
}
// get disabled cache types
$deactivated       = $db->select(
    'teinstellungen',
    ['kEinstellungenSektion', 'cName'],
    [CONF_CACHING, 'caching_types_disabled']
);
$currentlyDisabled = [];
if (is_object($deactivated) && isset($deactivated->cWert)) {
    $currentlyDisabled = ($deactivated->cWert !== '')
        ? unserialize($deactivated->cWert)
        : [];
}
switch ($action) {
    case 'cacheMassAction':
        //mass action cache flush
        $tab = 'massaction';
        switch ($cacheAction) {
            case 'flush':
                if (GeneralObject::isCountable('cache-types', $_POST)) {
                    $okCount = 0;
                    foreach ($_POST['cache-types'] as $cacheType) {
                        $hookInfo = ['type' => $cacheType, 'key' => null, 'isTag' => true];
                        $flush    = $cache->flushTags([$cacheType], $hookInfo);
                        if ($flush === false) {
                            $alertHelper->addAlert(
                                Alert::TYPE_ERROR,
                                sprintf(__('errorCacheTypeDelete'), $cacheType),
                                'errorCacheTypeDelete'
                            );
                        } else {
                            $okCount++;
                        }
                    }
                    if ($okCount > 0) {
                        $alertHelper->addAlert(
                            Alert::TYPE_SUCCESS,
                            $okCount . __('successCacheEmptied'),
                            'successCacheEmptied'
                        );
                    }
                } else {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorNoCacheType'), 'errorNoCacheType');
                }
                break;
            case 'activate':
                if (is_array(Request::postVar('cache-types'))) {
                    foreach ($_POST['cache-types'] as $cacheType) {
                        $index = array_search($cacheType, $currentlyDisabled, true);
                        if (is_int($index)) {
                            unset($currentlyDisabled[$index]);
                        }
                    }
                    $upd        = new stdClass();
                    $upd->cWert = serialize($currentlyDisabled);
                    $res        = $db->update(
                        'teinstellungen',
                        ['kEinstellungenSektion', 'cName'],
                        [CONF_CACHING, 'caching_types_disabled'],
                        $upd
                    );
                    if ($res > 0) {
                        $alertHelper->addAlert(
                            Alert::TYPE_SUCCESS,
                            __('successCacheTypeActivate'),
                            'successCacheTypeActivate'
                        );
                    }
                } else {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorNoCacheType'), 'errorNoCacheType');
                }
                break;
            case 'deactivate':
                if (GeneralObject::isCountable('cache-types', $_POST)) {
                    foreach ($_POST['cache-types'] as $cacheType) {
                        $cache->flushTags([$cacheType]);
                        $currentlyDisabled[] = $cacheType;
                    }
                    $currentlyDisabled = array_unique($currentlyDisabled);
                    $upd               = new stdClass();
                    $upd->cWert        = serialize($currentlyDisabled);
                    $res               = $db->update(
                        'teinstellungen',
                        ['kEinstellungenSektion', 'cName'],
                        [CONF_CACHING, 'caching_types_disabled'],
                        $upd
                    );
                    if ($res > 0) {
                        $alertHelper->addAlert(
                            Alert::TYPE_SUCCESS,
                            __('successCacheTypeDeactivate'),
                            'successCacheTypeDeactivate'
                        );
                    }
                } else {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorNoCacheType'), 'errorNoCacheType');
                }
                break;
            default:
                break;
        }
        break;
    case 'flush_object_cache':
        $tab = 'massaction';
        if ($cache !== null && $cache->flushAll() !== false) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successCacheDelete'), 'successCacheDelete');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCacheDelete'), 'errorCacheDelete');
        }
        break;
    case 'settings':
        $settings      = $db->selectAll(
            'teinstellungenconf',
            ['kEinstellungenSektion', 'cConf'],
            [CONF_CACHING, 'Y'],
            '*',
            'nSort'
        );
        $i             = 0;
        $settingsCount = count($settings);

        while ($i < $settingsCount) {
            if (isset($_POST[$settings[$i]->cWertName])) {
                $value                        = new stdClass();
                $value->cWert                 = $_POST[$settings[$i]->cWertName];
                $value->cName                 = $settings[$i]->cWertName;
                $value->kEinstellungenSektion = CONF_CACHING;
                switch ($settings[$i]->cInputTyp) {
                    case 'kommazahl':
                        $value->cWert = (float)$value->cWert;
                        break;
                    case 'zahl':
                    case 'number':
                        $value->cWert = (int)$value->cWert;
                        break;
                    case 'text':
                        $value->cWert = mb_strlen($value->cWert) > 0 ? mb_substr($value->cWert, 0, 255) : $value->cWert;
                        break;
                    case 'listbox':
                        bearbeiteListBox($value->cWert, $settings[$i]->cWertName, CONF_CACHING);
                        break;
                }
                if ($value->cName === 'caching_method' && $value->cWert === 'auto') {
                    $availableMethods = [];
                    $allMethods       = $cache->checkAvailability();
                    foreach ($allMethods as $name => $state) {
                        if (isset($state['available'], $state['functional'])
                            && $state['available'] === true
                            && $state['functional'] === true
                        ) {
                            $availableMethods[] = $name;
                        }
                    }
                    if (count($availableMethods) > 0) {
                        $value->cWert = 'null';
                        if (in_array('redis', $availableMethods, true)) {
                            $value->cWert = 'redis';
                        } elseif (in_array('memcache', $availableMethods, true)) {
                            $value->cWert = 'memcache';
                        } elseif (in_array('memcached', $availableMethods, true)) {
                            $value->cWert = 'memcached';
                        } elseif (in_array('apc', $availableMethods, true)) {
                            $value->cWert = 'apc';
                        } elseif (in_array('xcache', $availableMethods, true)) {
                            $value->cWert = 'xcache';
                        } elseif (in_array('advancedfile', $availableMethods, true)) {
                            $value->cWert = 'advancedfile';
                        } elseif (in_array('file', $availableMethods, true)) {
                            $value->cWert = 'file';
                        }
                    } else {
                        $value->cWert = 'null';
                    }
                    if ($value->cWert !== 'null') {
                        $alertHelper->addAlert(
                            Alert::TYPE_SUCCESS,
                            '<strong>' . $value->cWert . '</strong>' . __('successCacheMethodSave'),
                            'successCacheDelete'
                        );
                    } else {
                        $alertHelper->addAlert(
                            Alert::TYPE_ERROR,
                            __('errorCacheMethodSelect'),
                            'errorCacheMethodSelect'
                        );
                    }
                }
                $db->delete(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [CONF_CACHING, $settings[$i]->cWertName]
                );
                $db->insert('teinstellungen', $value);
            }
            ++$i;
        }
        $cache->flushAll();
        $cache->setJtlCacheConfig($db->selectAll('teinstellungen', 'kEinstellungenSektion', CONF_CACHING));
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successConfigSave'), 'successConfigSave');
        $cache->flushTags([CACHING_GROUP_OPTION]);
        $tab = 'settings';
        break;
    case 'benchmark':
        //do benchmarks
        $tab      = 'benchmark';
        $testData = 'simple short string';
        $methods  = 'all';
        $repeat   = Request::postInt('repeat', 1);
        $runCount = Request::postInt($_POST['runcount'], 1000);
        if (isset($_POST['testdata'])) {
            switch ($_POST['testdata']) {
                case 'array':
                    $testData = ['test1' => 'string number one', 'test2' => 'string number two', 'test3' => 333];
                    break;
                case 'object':
                    $testData        = new stdClass();
                    $testData->test1 = 'string number one';
                    $testData->test2 = 'string number two';
                    $testData->test3 = 333;
                    break;
                case 'string':
                default:
                    $testData = 'simple short string';
                    break;
            }
        }
        if (is_array(Request::postVar('methods'))) {
            $methods = $_POST['methods'];
        }
        if ($cache !== null) {
            $benchResults = $cache->benchmark($methods, $testData, $runCount, $repeat, false, true);
            $smarty->assign('bench_results', $benchResults);
        }
        break;
    case 'flush_template_cache':
        // delete all template cachefiles
        $callback     = static function (array $pParameters) {
            if (!$pParameters['isdir']) {
                if (@unlink($pParameters['path'] . $pParameters['filename'])) {
                    $pParameters['count']++;
                } else {
                    $pParameters['error'] .= sprintf(
                        __('errorFileDelete'),
                        '<strong>' . $pParameters['path'] . $pParameters['filename'] . '</strong>'
                    ) . '<br/>';
                }
            } elseif (!@rmdir($pParameters['path'] . $pParameters['filename'])) {
                $pParameters['error'] .= sprintf(
                    __('errorDirDelete'),
                    '<strong>' . $pParameters['path'] . $pParameters['filename'] . '</strong>'
                ) . '<br/>';
            }
        };
        $deleteCount  = 0;
        $cbParameters = [
            'count'  => &$deleteCount,
            'notice' => &$notice,
            'error'  => &$error
        ];
        $template     = Shop::Container()->getTemplateService()->getActiveTemplate();
        $dirMan       = new DirManager();
        $dirMan->getData(PFAD_ROOT . PFAD_COMPILEDIR . $template->getDir(), $callback, $cbParameters);
        $dirMan->getData(PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR, $callback, $cbParameters);
        $ms = new MinifyService();
        $ms->flushCache();
        $alertHelper->addAlert(Alert::TYPE_ERROR, $error, 'errorCache');
        $alertHelper->addAlert(Alert::TYPE_NOTE, $notice, 'noticeCache');
        $alertHelper->addAlert(
            Alert::TYPE_SUCCESS,
            sprintf(
                __('successTemplateCacheDelete'),
                number_format($cbParameters['count'])
            ),
            'successTemplateCacheDelete'
        );
        break;
    default:
        break;
}
if ($cache !== null) {
    $options = $cache->getOptions();
    $smarty->assign('method', ucfirst($options['method']))
        ->assign('all_methods', $cache->getAllMethods())
        ->assign('stats', $cache->getStats());
}
$settings = $db->selectAll(
    'teinstellungenconf',
    ['nStandardAnzeigen', 'kEinstellungenSektion'],
    [1, CONF_CACHING],
    '*',
    'nSort'
);

$getText->localizeConfigs($settings);
foreach ($settings as $i => $setting) {
    if ($setting->cName === 'caching_types_disabled') {
        unset($settings[$i]);
        continue;
    }
    if ($setting->cInputTyp === 'selectbox') {
        $setting->ConfWerte = $db->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$setting->kEinstellungenConf,
            '*',
            'nSort'
        );
        $getText->localizeConfigValues($setting, $setting->ConfWerte);
    }
    $setValue               = $db->select(
        'teinstellungen',
        ['kEinstellungenSektion', 'cName'],
        [CONF_CACHING, $setting->cWertName]
    );
    $setting->gesetzterWert = $setValue->cWert ?? null;
}
$advancedSettings = $db->query(
    'SELECT * 
        FROM teinstellungenconf 
        WHERE (nStandardAnzeigen = 0 OR nStandardAnzeigen = 2)
            AND kEinstellungenSektion = ' . CONF_CACHING . '
        ORDER BY nSort',
    ReturnType::ARRAY_OF_OBJECTS
);
$getText->localizeConfigs($advancedSettings);
$settingsCount = count($advancedSettings);
for ($i = 0; $i < $settingsCount; ++$i) {
    if ($advancedSettings[$i]->cInputTyp === 'selectbox') {
        $advancedSettings[$i]->ConfWerte = $db->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$advancedSettings[$i]->kEinstellungenConf,
            '*',
            'nSort'
        );
        $getText->localizeConfigValues($advancedSettings[$i], $advancedSettings[$i]->ConfWerte);
    }
    $setValue                            = $db->select(
        'teinstellungen',
        ['kEinstellungenSektion', 'cName'],
        [CONF_CACHING, $advancedSettings[$i]->cWertName]
    );
    $advancedSettings[$i]->gesetzterWert = $setValue->cWert ?? null;
}
if (function_exists('opcache_get_status')) {
    $data                       = opcache_get_status();
    $opcacheStats               = new stdClass();
    $opcacheStats->enabled      = isset($data['opcache_enabled'])
        && $data['opcache_enabled'] === true;
    $opcacheStats->memoryFree   = isset($data['memory_usage']['free_memory'])
        ? round($data['memory_usage']['free_memory'] / 1024 / 1024, 2)
        : -1;
    $opcacheStats->memoryUsed   = isset($data['memory_usage']['used_memory'])
        ? round($data['memory_usage']['used_memory'] / 1024 / 1024, 2)
        : -1;
    $opcacheStats->numberScrips = $data['opcache_statistics']['num_cached_scripts'] ?? -1;
    $opcacheStats->numberKeys   = $data['opcache_statistics']['num_cached_keys'] ?? -1;
    $opcacheStats->hits         = $data['opcache_statistics']['hits'] ?? -1;
    $opcacheStats->misses       = $data['opcache_statistics']['misses'] ?? -1;
    $opcacheStats->hitRate      = isset($data['opcache_statistics']['opcache_hit_rate'])
        ? round($data['opcache_statistics']['opcache_hit_rate'], 2)
        : -1;
    $opcacheStats->scripts      = GeneralObject::isCountable('scripts', $data)
        ? $data['scripts']
        : [];
}

$tplcacheStats           = new stdClass();
$tplcacheStats->frontend = [];
$tplcacheStats->backend  = [];

$callback = static function (array $pParameters) {
    if (!$pParameters['isdir']) {
        $fileObj           = new stdClass();
        $fileObj->filename = $pParameters['filename'];
        $fileObj->path     = $pParameters['path'];
        $fileObj->fullname = $pParameters['path'] . $pParameters['filename'];

        $pParameters['files'][] = $fileObj;
    }
};

$template = Shop::Container()->getTemplateService()->getActiveTemplate();
$dirMan   = new DirManager();
$dirMan->getData(PFAD_ROOT . PFAD_COMPILEDIR . $template->getDir(), $callback, ['files' => &$tplcacheStats->frontend])
    ->getData(PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR, $callback, ['files' => &$tplcacheStats->backend]);
$allMethods           = $cache->checkAvailability();
$availableMethods     = [];
$disFunctionalMethods = [];
$nonAvailableMethods  = [];
foreach ($allMethods as $name => $state) {
    if ($name === 'null') {
        continue;
    }
    if ($state['functional'] === true) {
        $availableMethods[] = $name;
    } elseif ($state['available'] === true) {
        $disFunctionalMethods[] = $name;
    } else {
        $nonAvailableMethods[] = $name;
    }
}
$cachingGroups = [];
if ($cache !== null) {
    $cachingGroups = $cache->getCachingGroups();
    foreach ($cachingGroups as &$cachingGroup) {
        $cachingGroup['key_count'] = count($cache->getKeysByTag([constant($cachingGroup['name'])]));
    }
    unset($cachingGroup);
}
if (!empty($cache->getError())) {
    $alertHelper->addAlert(Alert::TYPE_ERROR, $cache->getError(), 'errorCache');
}
$smarty->assign('settings', $settings)
    ->assign('caching_groups', $cachingGroups)
    ->assign('cache_enabled', isset($options['activated']) && $options['activated'] === true)
    ->assign('show_page_cache', $settings)
    ->assign('options', $options)
    ->assign('opcache_stats', $opcacheStats)
    ->assign('tplcacheStats', $tplcacheStats)
    ->assign('functional_methods', json_encode($availableMethods))
    ->assign('disfunctional_methods', json_encode($disFunctionalMethods))
    ->assign('non_available_methods', json_encode($nonAvailableMethods))
    ->assign('advanced_settings', $advancedSettings)
    ->assign('disabled_caches', $currentlyDisabled)
    ->assign('step', $step)
    ->assign('tab', $tab)
    ->display('cache.tpl');
