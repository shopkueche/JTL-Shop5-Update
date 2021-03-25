<?php

use JTL\Plugin\Admin\Installation\Installer;
use JTL\Plugin\Admin\Installation\Uninstaller;
use JTL\Plugin\Admin\StateChanger;
use JTL\Plugin\Admin\Updater;
use JTL\Plugin\Admin\Validation\LegacyPluginValidator;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Plugin\Helper;
use JTL\Plugin\LegacyPlugin;
use JTL\Shop;
use JTL\XMLParser;

/**
 * @param int    $pluginID
 * @param string $dir
 * @return int
 * @deprecated since 5.0.0
 */
function pluginPlausi(int $pluginID, $dir = '')
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $validator = new LegacyPluginValidator(Shop::Container()->getDB(), new XMLParser());
    $validator->setDir($dir);

    return $validator->validateByPluginID($pluginID);
}

/**
 * @param array  $xml
 * @param string $dir
 * @return int
 * @deprecated since 5.0.0
 */
function pluginPlausiIntern($xml, $dir)
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $validator = new LegacyPluginValidator(Shop::Container()->getDB(), new XMLParser());
    $validator->setDir($dir);

    return $validator->pluginPlausiIntern($xml, false);
}

/**
 * Versucht ein ausgewähltes Plugin zu updaten
 *
 * @param int $pluginID
 * @return int
 * @deprecated since 5.0.0
 */
function updatePlugin(int $pluginID)
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $db        = Shop::Container()->getDB();
    $cache     = Shop::Container()->getCache();
    $parser    = new XMLParser();
    $installer = new Installer(
        $db,
        new Uninstaller($db, $cache),
        new LegacyPluginValidator($db, $parser),
        new PluginValidator($db, $parser)
    );
    $updater   = new Updater($db, $installer);

    return $updater->update($pluginID);
}

/**
 * Versucht ein ausgewähltes Plugin vorzubereiten und danach zu installieren
 *
 * @param string     $dir
 * @param int|Plugin $oldPlugin
 * @return int
 * @deprecated since 5.0.0
 */
function installierePluginVorbereitung($dir, $oldPlugin = 0)
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $db        = Shop::Container()->getDB();
    $cache     = Shop::Container()->getCache();
    $parser    = new XMLParser();
    $installer = new Installer(
        $db,
        new Uninstaller($db, $cache),
        new LegacyPluginValidator($db, $parser),
        new PluginValidator($db, $parser)
    );
    $installer->setDir($dir);
    if ($oldPlugin !== 0) {
        $installer->setPlugin($oldPlugin);
        $installer->setDir($dir);
    }

    return $installer->prepare();
}

/**
 * Laedt das Plugin neu, d.h. liest die XML Struktur neu ein, fuehrt neue SQLs aus.
 *
 * @param LegacyPlugin $plugin
 * @param bool         $forceReload
 * @return int
 * @throws Exception
 * @deprecated since 5.0.0
 * 200 = kein Reload nötig, da info file älter als dZuletztAktualisiert
 * siehe return Codes von installierePluginVorbereitung()
 */
function reloadPlugin($plugin, bool $forceReload = false)
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $db           = Shop::Container()->getDB();
    $parser       = new XMLParser();
    $stateChanger = new StateChanger(
        $db,
        Shop::Container()->getCache(),
        new LegacyPluginValidator($db, $parser),
        new PluginValidator($db, $parser)
    );

    return $stateChanger->reload($plugin, $forceReload);
}

/**
 * Versucht ein ausgewähltes Plugin zu aktivieren
 *
 * @param int $pluginID
 * @return int
 * @deprecated since 5.0.0
 */
function aktivierePlugin(int $pluginID): int
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $db           = Shop::Container()->getDB();
    $parser       = new XMLParser();
    $stateChanger = new StateChanger(
        $db,
        Shop::Container()->getCache(),
        new LegacyPluginValidator($db, $parser),
        new PluginValidator($db, $parser)
    );

    return $stateChanger->activate($pluginID);
}

/**
 * Versucht ein ausgewähltes Plugin zu deaktivieren
 *
 * @param int $pluginID
 * @return int
 * @deprecated since 5.0.0
 */
function deaktivierePlugin(int $pluginID): int
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $stateChanger = new StateChanger(Shop::Container()->getDB(), Shop::Container()->getCache());

    return $stateChanger->deactivate($pluginID);
}

/**
 * Holt alle PluginSprachvariablen (falls vorhanden)
 *
 * @param int $pluginID
 * @return array
 * @deprecated since 5.0.0
 */
function gibSprachVariablen(int $pluginID): array
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Helper::getLanguageVariables($pluginID);
}
