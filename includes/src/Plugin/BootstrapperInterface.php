<?php

namespace JTL\Plugin;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Events\Dispatcher;
use JTL\License\Struct\ExsLicense;
use JTL\Link\LinkInterface;
use JTL\Smarty\JTLSmarty;

/**
 * Interface BootstrapperInterface
 * @package JTL\Plugin
 */
interface BootstrapperInterface
{
    /**
     * @param Dispatcher $dispatcher
     */
    public function boot(Dispatcher $dispatcher);

    /**
     * @return mixed
     */
    public function installed();

    /**
     * @param bool $deleteData
     * @return mixed
     */
    public function uninstalled(bool $deleteData = true);

    /**
     * @return mixed
     */
    public function enabled();

    /**
     * @return mixed
     */
    public function disabled();

    /**
     * @param string $oldVersion
     * @param string $newVersion
     */
    public function preUpdate($oldVersion, $newVersion): void;

    /**
     * @param mixed $oldVersion
     * @param mixed $newVersion
     * @return mixed
     */
    public function updated($oldVersion, $newVersion);

    /**
     * @param int         $type
     * @param string      $title
     * @param null|string $description
     */
    public function addNotify($type, $title, $description = null): void;

    /**
     * @param PluginInterface $plugin
     */
    public function setPlugin(PluginInterface $plugin): void;

    /**
     * @return PluginInterface
     */
    public function getPlugin();

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface;

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void;

    /**
     * @return JTLCacheInterface
     */
    public function getCache(): JTLCacheInterface;

    /**
     * @param JTLCacheInterface $cache
     */
    public function setCache(JTLCacheInterface $cache): void;

    /**
     * @param string    $tabName
     * @param int       $menuID
     * @param JTLSmarty $smarty
     * @return string
     */
    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string;

    /**
     * @param LinkInterface $link
     * @param JTLSmarty     $smarty
     * @return bool
     */
    public function prepareFrontend(LinkInterface $link, JTLSmarty $smarty): bool;

    /**
     * @return int
     */
    public function loaded(): int;

    /**
     * @param ExsLicense $license
     */
    public function licenseExpired(ExsLicense $license): void;
}
