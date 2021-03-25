<?php declare(strict_types=1);

namespace JTL\Cache\Methods;

use JTL\Cache\ICachingMethod;
use JTL\Cache\JTLCacheTrait;
use JTL\Shop;
use Redis;
use RedisCluster;
use RedisClusterException;

/**
 * Class CacheRedisCluster
 * @package JTL\Cache\Methods
 * Implements caching via phpredis in cluster mode
 *
 * @see https://github.com/nicolasff/phpredis
 */
class CacheRedisCluster implements ICachingMethod
{
    use JTLCacheTrait;

    /**
     * @var CacheRedisCluster
     */
    public static $instance;

    /**
     * @var RedisCluster
     */
    private $redis;

    /**
     * @var array
     */
    private $masters = [];

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $res             = false;
        $this->journalID = 'redis_journal';
        $this->options   = $options;
        if (isset($options['rediscluster_hosts']) && $this->isAvailable()) {
            $res = $this->setRedisCluster(
                $options['rediscluster_hosts'],
                $options['redis_persistent'],
                (int)$options['rediscluster_strategy'],
                $options['redis_pass']
            );
        }
        $this->isInitialized = $res;
    }

    /**
     * @param string|null $hosts
     * @param bool        $persist
     * @param int         $strategy
     * @param string|null $pass
     * @return bool
     */
    private function setRedisCluster($hosts = null, $persist = false, $strategy = 0, string $pass = null): bool
    {
        try {
            $pass  = $pass !== null && \strlen($pass) > 0 ? $pass : null;
            $redis = new RedisCluster(null, \explode(',', $hosts), 1.5, 1.5, $persist, $pass);
            $redis->setOption(Redis::OPT_PREFIX, $this->options['prefix']);
            // set php serializer for objects and arrays
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
            switch ($strategy) {
                case 4:
                    $redis->setOption(RedisCluster::OPT_SLAVE_FAILOVER, RedisCluster::FAILOVER_DISTRIBUTE_SLAVES);
                    break;
                case 3:
                    $redis->setOption(RedisCluster::OPT_SLAVE_FAILOVER, RedisCluster::FAILOVER_DISTRIBUTE);
                    break;
                case 2:
                    $redis->setOption(RedisCluster::OPT_SLAVE_FAILOVER, RedisCluster::FAILOVER_ERROR);
                    break;
                case 1:
                default:
                    $redis->setOption(RedisCluster::OPT_SLAVE_FAILOVER, RedisCluster::FAILOVER_NONE);
                    break;
            }
            $this->masters = $redis->_masters();
            $this->redis   = $redis;
        } catch (RedisClusterException $e) {
            $this->setError($e->getMessage());
        }

        return \count($this->masters) > 0;
    }

    /**
     * @inheritdoc
     */
    public function store($cacheID, $content, $expiration = null): bool
    {
        try {
            $exp = $expiration ?? $this->options['lifetime'];

            return $this->redis->set($cacheID, $content, $cacheID !== $this->journalID && $exp > -1 ? $exp : null);
        } catch (RedisClusterException $e) {
            Shop::Container()->getLogService()->error('RedisClusterException: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function storeMulti($idContent, $expiration = null): bool
    {
        try {
            $res = $this->redis->mset($idContent);
            $exp = $expiration ?? $this->options['lifetime'];
            $exp = $exp > -1 ? $exp : null;
            foreach (\array_keys($idContent) as $_cacheID) {
                $this->redis->expire($_cacheID, $exp);
            }

            return $res;
        } catch (RedisClusterException $e) {
            Shop::Container()->getLogService()->error('RedisClusterException: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function load($cacheID)
    {
        try {
            return $this->redis->get($cacheID);
        } catch (RedisClusterException $e) {
            Shop::Container()->getLogService()->error('RedisClusterException: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function loadMulti(array $cacheIDs): array
    {
        $res    = $this->redis->mget($cacheIDs);
        $i      = 0;
        $return = [];
        foreach ($res as $_idx => $_val) {
            $return[$cacheIDs[$i]] = $_val;
            ++$i;
        }

        return $return;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return \class_exists('Redis');
    }

    /**
     * @inheritdoc
     */
    public function flush($cacheID): bool
    {
        return $this->redis->del($cacheID) > 0;
    }

    /**
     * @inheritdoc
     */
    public function setCacheTag($tags, $cacheID): bool
    {
        $res = false;
        if (\is_string($tags)) {
            $tags = [$tags];
        }
        if (\count($tags) > 0) {
            foreach ($tags as $tag) {
                $this->redis->sAdd(self::_keyFromTagName($tag), $cacheID);
            }
            $res = true;
        }

        return $res;
    }

    /**
     * custom prefix for tag IDs
     *
     * @param string $tagName
     * @return string
     */
    private static function _keyFromTagName($tagName): string
    {
        return 'tag_' . $tagName;
    }

    /**
     * @inheritdoc
     */
    public function flushTags($tags): int
    {
        return $this->flush(\array_unique($this->getKeysByTag($tags))) ? \count($tags) : 0;
    }

    /**
     * @inheritdoc
     */
    public function flushAll(): bool
    {
        foreach ($this->masters as $master) {
            $this->redis->flushDB($master);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getKeysByTag($tags = []): array
    {
        $matchTags = \is_string($tags)
            ? [self::_keyFromTagName($tags)]
            : \array_map('JTL\Cache\Methods\CacheRedisCluster::_keyFromTagName', $tags);
        $res       = \count($tags) === 1
            ? $this->redis->sMembers($matchTags[0])
            : $this->redis->sUnion($matchTags);
        if (\PHP_SAPI === 'srv' || \PHP_SAPI === 'cli') { // for some reason, hhvm does not unserialize values
            foreach ($res as &$_cid) {
                // phpredis will throw an exception when unserializing unserialized data
                try {
                    $_cid = $this->redis->_unserialize($_cid);
                } catch (RedisClusterException $e) {
                    // we know we don't have to continue unserializing when there was an exception
                    break;
                }
            }
        }

        return \is_array($res) ? $res : [];
    }

    /**
     * @inheritdoc
     */
    public function keyExists($key): bool
    {
        return (bool)$this->redis->exists($key);
    }

    /**
     * @inheritdoc
     */
    public function getStats(): array
    {
        $numEntries  = [];
        $uptimes     = [];
        $stats       = [];
        $mem         = [];
        $slowLogs    = [];
        $slowLogData = [];
        $hits        = [];
        $misses      = [];
        $hps         = [];
        $mps         = [];
        try {
            foreach ($this->masters as $master) {
                $stats[]    = $this->redis->info($master);
                $slowLogs[] = \method_exists($this->redis, 'slowlog')
                    ? $this->redis->slowlog($master, 'get', 25)
                    : [];
            }
        } catch (RedisClusterException $e) {
            Shop::Container()->getLogService()->error('RedisClusterException: ' . $e->getMessage());

            return [];
        }
        $idx = 'db0';
        foreach ($stats as $stat) {
            $uptimes[] = $stat['uptime_in_seconds'] ?? 0;
            $hits[]    = $stat['keyspace_hits'];
            $misses[]  = $stat['keyspace_misses'];
            $mem[]     = $stat['used_memory'];
            $hps[]     = $stat['uptime_in_seconds'] > 0 ? $stat['keyspace_hits'] / $stat['uptime_in_seconds'] : 0;
            $mps[]     = $stat['uptime_in_seconds'] > 0 ? $stat['keyspace_misses'] / $stat['uptime_in_seconds'] : 0;
            if (isset($stat[$idx])) {
                $dbStats = \explode(',', $stat[$idx]);
                foreach ($dbStats as $dbStat) {
                    if (\mb_strpos($dbStat, 'keys=') !== false) {
                        $numEntries[] = \str_replace('keys=', '', $dbStat);
                    }
                }
            }
        }
        foreach ($slowLogs as $slowLog) {
            foreach ($slowLog as $_slow) {
                $slowLogDataEntry = [];
                if (isset($_slow[1])) {
                    $slowLogDataEntry['date'] = \date('d.m.Y H:i:s', $_slow[1]);
                }
                if (isset($_slow[3][0])) {
                    $slowLogDataEntry['cmd'] = $_slow[3][0];
                }
                if (isset($_slow[2]) && $_slow[2] > 0) {
                    $slowLogDataEntry['exec_time'] = ($_slow[2] / 1000000);
                }
                $slowLogData[] = $slowLogDataEntry;
            }
        }

        return [
            'entries'  => \implode('/', $numEntries),
            'uptime'   => \implode('/', $uptimes), //uptime in seconds
            'uptime_h' => \implode('/', \array_map([$this, 'secondsToTime'], $uptimes)), //human readable
            'hits'     => \implode('/', $hits), //cache hits
            'misses'   => \implode('/', $misses), //cache misses
            'hps'      => \implode('/', $hps), //hits per second
            'mps'      => \implode('/', $mps), //misses per second
            'mem'      => \implode('/', $mem), //used memory in bytes
            'slow'     => $slowLogData //redis slow log
        ];
    }
}
