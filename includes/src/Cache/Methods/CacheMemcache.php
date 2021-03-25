<?php declare(strict_types=1);

namespace JTL\Cache\Methods;

use JTL\Cache\ICachingMethod;
use JTL\Cache\JTLCacheTrait;

/**
 * Class CacheMemcache
 *
 * Implements the Memcache memory object caching system - no "d" at the end
 * @package JTL\Cache\Methods
 */
class CacheMemcache implements ICachingMethod
{
    use JTLCacheTrait;

    /**
     * @var CacheMemcache
     */
    public static $instance;

    /**
     * @var \Memcache
     */
    private $memcache;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        if (!empty($options['memcache_host']) && !empty($options['memcache_port']) && $this->isAvailable()) {
            $this->setMemcache($options['memcache_host'], $options['memcache_port']);
            $this->isInitialized = true;
            $this->journalID     = 'memcache_journal';
            //@see http://php.net/manual/de/memcached.expiration.php
            $options['lifetime'] = \min(60 * 60 * 24 * 30, $options['lifetime']);
            $this->options       = $options;
            self::$instance      = $this;
        }
    }

    /**
     * @param string $host
     * @param int    $port
     * @return $this
     */
    private function setMemcache($host, $port): ICachingMethod
    {
        if ($this->memcache !== null) {
            $this->memcache->close();
        }
        $this->memcache = new \Memcache();
        $this->memcache->addServer($host, (int)$port);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function store($cacheID, $content, $expiration = null): bool
    {
        return $this->memcache->set(
            $this->options['prefix'] . $cacheID,
            $content,
            0,
            $expiration ?? $this->options['lifetime']
        );
    }

    /**
     * @inheritdoc
     */
    public function storeMulti($idContent, $expiration = null): bool
    {
        return $this->memcache->set($this->prefixArray($idContent), $expiration ?? $this->options['lifetime']);
    }

    /**
     * @inheritdoc
     */
    public function load($cacheID)
    {
        return $this->memcache->get($this->options['prefix'] . $cacheID);
    }

    /**
     * @inheritdoc
     */
    public function loadMulti(array $cacheIDs): array
    {
        if (!\is_array($cacheIDs)) {
            return [];
        }
        $prefixedKeys = [];
        foreach ($cacheIDs as $_cid) {
            $prefixedKeys[] = $this->options['prefix'] . $_cid;
        }
        $res = $this->dePrefixArray($this->memcache->get($prefixedKeys));

        // fill up result
        return \array_merge(\array_fill_keys($cacheIDs, false), $res);
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return \class_exists('Memcache');
    }

    /**
     * @inheritdoc
     */
    public function flush($cacheID): bool
    {
        return $this->memcache->delete($this->options['prefix'] . $cacheID);
    }

    /**
     * @inheritdoc
     */
    public function flushAll(): bool
    {
        return $this->memcache->flush();
    }

    /**
     * @inheritdoc
     */
    public function getStats(): array
    {
        $stats = $this->memcache->getStats();

        return [
            'entries' => $stats['curr_items'],
            'hits'    => $stats['get_hits'],
            'misses'  => $stats['get_misses'],
            'inserts' => $stats['cmd_set'],
            'mem'     => $stats['bytes']
        ];
    }
}
