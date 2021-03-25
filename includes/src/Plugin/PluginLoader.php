<?php declare(strict_types=1);

namespace JTL\Plugin;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Shop;

/**
 * Class PluginLoader
 * @package JTL\Plugin
 */
class PluginLoader extends AbstractLoader
{
    /**
     * PluginLoader constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->db    = $db;
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function init(int $id, bool $invalidateCache = false, int $languageID = null): PluginInterface
    {
        if (($languageID = $languageID ?? Shop::getLanguageID()) === 0) {
            $languageID = Shop::Lang()->getDefaultLanguage()->kSprache;
        }
        $getText       = Shop::Container()->getGetText();
        $languageCode  = Shop::Lang()->getIsoFromLangID($languageID)->cISO;
        $languageTag   = $_SESSION['AdminAccount']->language ?? $getText->getLanguage();
        $this->cacheID = \CACHING_GROUP_PLUGIN . '_' . $id . '_' . $languageID . '_' . $languageTag;
        if ($invalidateCache === true) {
            $this->cache->flush('hook_list');
            $this->cache->flushTags([\CACHING_GROUP_PLUGIN, \CACHING_GROUP_PLUGIN . '_' . $id]);
        } elseif (($plugin = $this->loadFromCache()) !== null) {
            $getText->setLanguage($languageTag);
            $getText->loadPluginLocale('base', $plugin);

            return $plugin;
        }
        $obj = $this->db->select('tplugin', 'kPlugin', $id);
        if ($obj === null) {
            throw new \InvalidArgumentException('Cannot find plugin with ID ' . $id);
        }

        return $this->loadFromObject($obj, $languageCode);
    }

    /**
     * @inheritdoc
     */
    public function loadFromCache(): ?PluginInterface
    {
        return ($plugin = $this->cache->get($this->cacheID)) === false ? null : $plugin;
    }

    /**
     * @inheritdoc
     */
    public function saveToCache(PluginInterface $plugin): bool
    {
        return $this->cacheID !== null
            ? $this->cache->set(
                $this->cacheID,
                $plugin,
                [
                    \CACHING_GROUP_PLUGIN . '_' . $plugin->getID(),
                    \CACHING_GROUP_PLUGIN,
                    $plugin->getCache()->getGroup()
                ]
            )
            : false;
    }

    /**
     * @inheritdoc
     */
    public function loadFromObject($obj, string $currentLanguageCode): PluginInterface
    {
        $id      = (int)$obj->kPlugin;
        $paths   = $this->loadPaths($obj->cVerzeichnis);
        $plugin  = new Plugin();
        $getText = Shop::Container()->getGetText();
        $getText->setLanguage();
        $plugin->setID($id);
        $plugin->setPluginID($obj->cPluginID);
        $plugin->setIsExtension(true);
        $plugin->setPaths($paths);
        $getText->loadPluginLocale('base', $plugin);
        $plugin->setMeta($this->loadMetaData($obj));
        $plugin->setLicense($this->loadLicense($obj));
        $this->loadMarkdownFiles($paths->getBasePath(), $plugin->getMeta());
        $this->loadAdminMenu($plugin);
        $plugin->setHooks($this->loadHooks($id));
        $plugin->setState((int)$obj->nStatus);
        $plugin->setBootstrap(true);
        $plugin->setLinks($this->loadLinks($id));
        $plugin->setPluginID($obj->cPluginID);
        $plugin->setPriority((int)$obj->nPrio);
        $plugin->setCache($this->loadCacheData($plugin));
        $plugin->setConfig($this->loadConfig($paths->getAdminPath(), $plugin->getID()));
        $plugin->setLocalization($this->loadLocalization($id, $currentLanguageCode));
        $plugin->setWidgets($this->loadWidgets($plugin));
        $plugin->setMailTemplates($this->loadMailTemplates($plugin));
        $plugin->setPaymentMethods($this->loadPaymentMethods($plugin));

        $this->saveToCache($plugin);

        return $plugin;
    }
}
