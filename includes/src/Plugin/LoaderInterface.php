<?php declare(strict_types=1);

namespace JTL\Plugin;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;

/**
 * Interface LoaderInterface
 * @package JTL\Plugin
 */
interface LoaderInterface
{
    /**
     * LoaderInterface constructor.
     *
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache);

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
     * @param int      $id
     * @param bool     $invalidateCache
     * @param int|null $languageID
     * @return PluginInterface
     * @throws \InvalidArgumentException
     */
    public function init(int $id, bool $invalidateCache = false, int $languageID = null): PluginInterface;

    /**
     * @param object $obj
     * @param string $currentLanguageCode
     * @return PluginInterface
     */
    public function loadFromObject($obj, string $currentLanguageCode): PluginInterface;

    /**
     * @return PluginInterface|null
     */
    public function loadFromCache(): ?PluginInterface;

    /**
     * @param PluginInterface $plugin
     * @return bool
     */
    public function saveToCache(PluginInterface $plugin): bool;
}
