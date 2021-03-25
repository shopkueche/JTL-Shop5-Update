<?php declare(strict_types=1);

namespace JTL\Cache\Methods;

use JTL\Cache\ICachingMethod;
use JTL\Cache\JTLCacheTrait;
use Memcached;

/**
 * Class CacheMemcached
 * Implements the Memcached memory object caching system - notice the "d" at the end
 *
 * @package JTL\Cache\Methods
 * @warning Untested
 * @package JTL\Cache\Methods
 */
class CacheMemcached implements ICachingMethod
{
    use JTLCacheTrait;

    /**
     * @var CacheMemcached
     */
    public static $instance;

    /**
     * @var Memcached
     */
    private $memcached;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        if (!empty($options['memcache_host']) && !empty($options['memcache_port']) && $this->isAvailable()) {
            $this->setMemcached($options['memcache_host'], $options['memcache_port']);
            $this->memcached->setOption(Memcached::OPT_PREFIX_KEY, $options['prefix']);
            $this->isInitialized = true;
            $test                = $this->test();
            $this->setError($test === true ? '' : $this->memcached->getResultMessage());
            $this->journalID = 'memcached_journal';
            // @see http://php.net/manual/de/memcached.expiration.php
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
    private function setMemcached($host, $port): ICachingMethod
    {
        if ($this->memcached !== null) {
            $this->memcached->quit();
        }
        $this->memcached = new Memcached();
        $this->memcached->addServer($host, (int)$port);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function store($cacheID, $content, $expiration = null): bool
    {
        return $this->memcached->set(
            $cacheID,
            $content,
            $expiration ?? $this->options['lifetime']
        );
    }

    /**
     * @inheritdoc
     */
    public function storeMulti($idContent, $expiration = null): bool
    {
        return $this->memcached->setMulti($idContent, $expiration ?? $this->options['lifetime']);
    }

    /**
     * @inheritdoc
     */
    public function load($cacheID)
    {
        return $this->memcached->get($cacheID);
    }

    /**
     * @inheritdoc
     */
    public function loadMulti(array $cacheIDs): array
    {
        if (!\is_array($cacheIDs)) {
            return [];
        }

        return \array_merge(\array_fill_keys($cacheIDs, false), $this->memcached->getMulti($cacheIDs));
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return \class_exists('Memcached');
    }

    /**
     * @inheritdoc
     */
    public function flush($cacheID): bool
    {
        return $this->memcached->delete($cacheID);
    }

    /**
     * @inheritdoc
     */
    public function flushAll(): bool
    {
        return $this->memcached->flush();
    }

    /**
     * @inheritdoc
     */
    public function keyExists($key): bool
    {
        $res = $this->memcached->get($key);

        return ($res !== false || $this->memcached->getResultCode() === Memcached::RES_SUCCESS);
    }

    /**
     * @todo: get the right array index, not just the first one
     * @inheritdoc
     */
    public function getStats(): array
    {
        if (\method_exists($this->memcached, 'getStats')) {
            $stats = $this->memcached->getStats();
            if (\is_array($stats)) {
                foreach ($stats as $key => $_stat) {
                    return [
                        'entries' => $_stat['curr_items'],
                        'hits'    => $_stat['get_hits'],
                        'misses'  => $_stat['get_misses'],
                        'inserts' => $_stat['cmd_set'],
                        'mem'     => $_stat['bytes']
                    ];
                }
            }
        }

        return [];
    }
}
