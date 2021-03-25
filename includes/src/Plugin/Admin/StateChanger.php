<?php declare(strict_types=1);

namespace JTL\Plugin\Admin;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Plugin\Admin\Installation\Installer;
use JTL\Plugin\Admin\Installation\Uninstaller;
use JTL\Plugin\Admin\Validation\LegacyPluginValidator;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Plugin\Admin\Validation\ValidatorInterface;
use JTL\Plugin\Helper;
use JTL\Plugin\InstallCode;
use JTL\Plugin\LegacyPluginLoader;
use JTL\Plugin\PluginInterface;
use JTL\Plugin\PluginLoader;
use JTL\Plugin\State;

/**
 * Class StateChanger
 * @package JTL\Plugin\Admin
 */
class StateChanger
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var ValidatorInterface|LegacyPluginValidator
     */
    private $legacyValidator;

    /**
     * @var ValidatorInterface|PluginValidator
     */
    protected $pluginValidator;

    /**
     * StateChanger constructor.
     * @param DbInterface             $db
     * @param JTLCacheInterface       $cache
     * @param ValidatorInterface|null $legacyValidator
     * @param ValidatorInterface|null $pluginValidator
     */
    public function __construct(
        DbInterface $db,
        JTLCacheInterface $cache,
        ValidatorInterface $legacyValidator = null,
        ValidatorInterface $pluginValidator = null
    ) {
        $this->db              = $db;
        $this->cache           = $cache;
        $this->legacyValidator = $legacyValidator;
        $this->pluginValidator = $pluginValidator;
    }

    /**
     * @param int $pluginID
     * @return int
     * @former aktivierePlugin()
     */
    public function activate(int $pluginID): int
    {
        if ($pluginID <= 0) {
            return InstallCode::WRONG_PARAM;
        }
        $pluginData = $this->db->select('tplugin', 'kPlugin', $pluginID);
        if (empty($pluginData->kPlugin)) {
            return InstallCode::NO_PLUGIN_FOUND;
        }
        if ((int)$pluginData->bExtension === 1) {
            $path      = \PFAD_ROOT . \PLUGIN_DIR;
            $validator = $this->pluginValidator;
            $loader    = new PluginLoader($this->db, $this->cache);
        } else {
            $path      = \PFAD_ROOT . \PFAD_PLUGIN;
            $validator = $this->legacyValidator;
            $loader    = new LegacyPluginLoader($this->db, $this->cache);
        }
        $valid = $validator->validateByPath($path . $pluginData->cVerzeichnis);
        if (!\in_array($valid, [InstallCode::OK, InstallCode::OK_LEGACY, InstallCode::DUPLICATE_PLUGIN_ID], true)) {
            return $valid;
        }
        $affectedRow = $this->db->update(
            'tplugin',
            'kPlugin',
            $pluginID,
            (object)['nStatus' => State::ACTIVATED]
        );
        $this->db->update('tadminwidgets', 'kPlugin', $pluginID, (object)['bActive' => 1]);
        $this->db->update('tlink', 'kPlugin', $pluginID, (object)['bIsActive' => 1]);
        $this->db->update('topcportlet', 'kPlugin', $pluginID, (object)['bActive' => 1]);
        $this->db->update('topcblueprint', 'kPlugin', $pluginID, (object)['bActive' => 1]);
        $this->cache->flushTags([\CACHING_GROUP_PLUGIN . '_' . $pluginID, \CACHING_GROUP_LICENSES]);
        if (($p = Helper::bootstrap($pluginID, $loader)) !== null) {
            $p->enabled();
        }

        return $affectedRow > 0 ? InstallCode::OK : InstallCode::NO_PLUGIN_FOUND;
    }

    /**
     * @param int $pluginID
     * @param int $newState
     * @return int
     * @former deaktivierePlugin()
     */
    public function deactivate(int $pluginID, int $newState = State::DISABLED): int
    {
        if ($pluginID <= 0) {
            return InstallCode::WRONG_PARAM;
        }
        $pluginData = $this->db->select('tplugin', 'kPlugin', $pluginID);
        if (!\SAFE_MODE) {
            $loader = (int)$pluginData->bExtension === 1
                ? new PluginLoader($this->db, $this->cache)
                : new LegacyPluginLoader($this->db, $this->cache);
            if (($p = Helper::bootstrap($pluginID, $loader)) !== null) {
                $p->disabled();
            }
        }
        $this->db->update('tplugin', 'kPlugin', $pluginID, (object)['nStatus' => $newState]);
        $this->db->update('tlink', 'kPlugin', $pluginID, (object)['bIsActive' => 0]);
        $this->db->update('tadminwidgets', 'kPlugin', $pluginID, (object)['bActive' => 0]);
        $this->db->update('topcportlet', 'kPlugin', $pluginID, (object)['bActive' => 0]);
        $this->db->update('topcblueprint', 'kPlugin', $pluginID, (object)['bActive' => 0]);
        $this->cache->flushTags([\CACHING_GROUP_PLUGIN . '_' . $pluginID, \CACHING_GROUP_LICENSES]);

        return InstallCode::OK;
    }


    /**
     * @param PluginInterface $plugin
     * @param bool            $forceReload
     * @throws \Exception
     * @return int
     * @former reloadPlugin()
     * @throws \Exception
     */
    public function reload(PluginInterface $plugin, bool $forceReload = false): int
    {
        $info = $plugin->getPaths()->getBasePath() . \PLUGIN_INFO_FILE;
        if (!\file_exists($info)) {
            return -1;
        }
        $lastUpdate    = $plugin->getMeta()->getDateLastUpdate();
        $lastXMLChange = \filemtime($info);
        if ($forceReload === true || $lastXMLChange > $lastUpdate->getTimestamp()) {
            $uninstaller = new Uninstaller($this->db, $this->cache);
            $installer   = new Installer($this->db, $uninstaller, $this->legacyValidator, $this->pluginValidator);
            $installer->setDir($plugin->getPaths()->getBaseDir());
            $installer->setPlugin($plugin);

            return $installer->prepare();
        }

        return 200;
    }
}
