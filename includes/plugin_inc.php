<?php

use JTL\DB\ReturnType;
use JTL\Plugin\Helper;
use JTL\Plugin\HookManager;
use JTL\Plugin\LegacyPlugin;
use JTL\Plugin\State;
use JTL\Shop;

/**
 * @param int   $hookID
 * @param array $args_arr
 */
function executeHook(int $hookID, array $args_arr = [])
{
    HookManager::getInstance()->executeHook($hookID, $args_arr);
}

/**
 * @param LegacyPlugin $plugin
 * @param array        $params
 * @return bool
 * @deprecated since 5.0.0
 */
function pluginLizenzpruefung($plugin, array $params = []): bool
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Helper::licenseCheck($plugin, $params);
}

/**
 * @param LegacyPlugin $plugin
 * @param int          $state
 * @deprecated since 5.0.0
 */
function aenderPluginZahlungsartStatus($plugin, int $state)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    Helper::updatePaymentMethodState($plugin, $state);
}

/**
 * @param int $pluginID
 * @return array
 * @deprecated since 5.0.0
 */
function gibPluginEinstellungen(int $pluginID)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Helper::getConfigByID($pluginID);
}

/**
 * @param int    $id
 * @param string $iso
 * @return array
 * @deprecated since 5.0.0
 */
function gibPluginSprachvariablen(int $id, $iso = ''): array
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Helper::getLanguageVariablesByID($id, $iso);
}

/**
 * @param int $state
 * @param int $pluginID
 * @return bool
 * @deprecated since 5.0.0
 */
function aenderPluginStatus(int $state, int $pluginID): bool
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Helper::updateStatusByID($state, $pluginID);
}

/**
 * @param int    $pluginID
 * @param string $paymentMethodName
 * @return string
 * @deprecated since 5.0.0
 */
function gibPlugincModulId(int $pluginID, string $paymentMethodName): string
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Helper::getModuleIDByPluginID($pluginID, $paymentMethodName);
}

/**
 * @param string $moduleID
 * @return int
 * @deprecated since 5.0.0
 */
function gibkPluginAuscModulId(string $moduleID): int
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Helper::getIDByModuleID($moduleID);
}

/**
 * @param string $pluginID
 * @return int
 * @deprecated since 5.0.0
 */
function gibkPluginAuscPluginID(string $pluginID): int
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Helper::getIDByPluginID($pluginID);
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibPluginExtendedTemplates(): array
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    $templates = [];
    $data      = Shop::Container()->getDB()->queryPrepared(
        'SELECT tplugintemplate.cTemplate, tplugin.cVerzeichnis, tplugin.nVersion
            FROM tplugintemplate
            JOIN tplugin 
                ON tplugintemplate.kPlugin = tplugin.kPlugin
                WHERE tplugin.nStatus = :state 
            ORDER BY tplugin.nPrio DESC',
        ['state' => State::ACTIVATED],
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($data as $tpl) {
        $path = PFAD_ROOT . PFAD_PLUGIN . $tpl->cVerzeichnis . '/' .
            PFAD_PLUGIN_VERSION . $tpl->nVersion . '/' .
            PFAD_PLUGIN_FRONTEND . PFAD_PLUGIN_TEMPLATE . $tpl->cTemplate;
        if (file_exists($path)) {
            $templates[] = $path;
        }
    }

    return $templates;
}
