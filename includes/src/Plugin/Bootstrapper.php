<?php

namespace JTL\Plugin;

use JTL\Backend\Notification;
use JTL\Backend\NotificationEntry;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Events\Dispatcher;
use JTL\License\Struct\ExsLicense;
use JTL\Link\LinkInterface;
use JTL\Plugin\Admin\StateChanger;
use JTL\Plugin\Admin\Validation\LegacyPluginValidator;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Smarty\JTLSmarty;
use JTL\XMLParser;

/**
 * Class Bootstrapper
 * @package JTL\Plugin
 */
abstract class Bootstrapper implements BootstrapperInterface
{
    /**
     * @var string
     */
    private $pluginId;

    /**
     * @var array
     */
    private $notifications = [];

    /**
     * @var LegacyPlugin
     */
    private $plugin;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * Bootstrapper constructor.
     * @param PluginInterface   $plugin
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    final public function __construct($plugin, DbInterface $db, JTLCacheInterface $cache)
    {
        $this->plugin   = $plugin;
        $this->pluginId = $plugin->getPluginID();
        $this->db       = $db;
        $this->cache    = $cache;
    }

    /**
     * @inheritdoc
     */
    public function boot(Dispatcher $dispatcher)
    {
        $dispatcher->listen('backend.notification', function (Notification $notify) use (&$dispatcher) {
            $dispatcher->forget('backend.notification');
            foreach ($this->notifications as $n) {
                $notify->addNotify($n);
            }
        });
    }

    /**
     * @inheritdoc
     */
    final public function addNotify($type, $title, $description = null): void
    {
        $this->notifications[] = (new NotificationEntry($type, $title, $description))->setPluginId($this->pluginId);
    }

    /**
     * @inheritdoc
     */
    public function installed()
    {
    }

    /**
     * @inheritdoc
     */
    public function uninstalled(bool $deleteData = true)
    {
    }

    /**
     * @inheritdoc
     */
    public function enabled()
    {
    }

    /**
     * @inheritdoc
     */
    public function disabled()
    {
    }

    /**
     * @inheritdoc
     */
    public function preUpdate($oldVersion, $newVersion): void
    {
    }

    /**
     * @inheritdoc
     */
    public function updated($oldVersion, $newVersion)
    {
    }

    /**
     * @inheritdoc
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @inheritdoc
     */
    public function setPlugin(PluginInterface $plugin): void
    {
        $this->plugin = $plugin;
    }

    /**
     * @inheritdoc
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @inheritdoc
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function getCache(): JTLCacheInterface
    {
        return $this->cache;
    }

    /**
     * @inheritdoc
     */
    public function setCache(JTLCacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function prepareFrontend(LinkInterface $link, JTLSmarty $smarty): bool
    {
        $smarty->assign(
            'cPluginTemplate',
            $this->getPlugin()->getPaths()->getFrontendPath() . \PFAD_PLUGIN_TEMPLATE . $link->getTemplate()
        );

        return false;
    }

    /**
     * @inheritdoc
     */
    public function loaded(): int
    {
        if (\PLUGIN_DEV_MODE !== true || $this->plugin === null) {
            return -1;
        }
        $parser       = $parser = new XMLParser();
        $stateChanger = new StateChanger(
            $this->db,
            $this->cache,
            new LegacyPluginValidator($this->db, $parser),
            new PluginValidator($this->db, $parser)
        );

        return $stateChanger->reload($this->plugin);
    }

    /**
     * @inheritDoc
     */
    public function licenseExpired(ExsLicense $license): void
    {
    }
}
