<?php declare(strict_types=1);

namespace JTL\Cache\Methods;

use JTL\Cache\ICachingMethod;
use JTL\Cache\JTLCacheTrait;
use Redis;
use RedisException;

/**
 * Class CacheRedis
 * Implements caching via phpredis
 *
 * @see https://github.com/nicolasff/phpredis
 * @package JTL\Cache\Methods
 */
class CacheRedis implements ICachingMethod
{
    use JTLCacheTrait;

    /**
     * @var CacheRedis
     */
    public static $instance;

    /**
     * @var Redis
     */
    private $redis;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $res             = false;
        $this->journalID = 'redis_journal';
        $this->options   = $options;
        if ($this->isAvailable()) {
            $res = $this->setRedis(
                $options['redis_host'],
                $options['redis_port'],
                $options['redis_pass'],
                $options['redis_db'],
                $options['redis_persistent']
            );
        }
        if ($res === false) {
            $this->redis         = null;
            $this->isInitialized = false;
        } else {
            $this->isInitialized = true;
        }
    }

    /**
     * @param string|null $host
     * @param int|null    $port
     * @param string|null $pass
     * @param int|null    $database
     * @param bool        $persist
     * @return bool
     */
    private function setRedis($host = null, $port = null, $pass = null, $database = null, $persist = false): bool
    {
        $redis   = new Redis();
        $connect = $persist === false ? 'connect' : 'pconnect';
        if ($host === null) {
            return false;
        }
        try {
            $res = ($port !== null && $host[0] !== '/')
                ? $redis->$connect($host, (int)$port, \REDIS_CONNECT_TIMEOUT)
                : $redis->$connect($host); // for connecting to socket
        } catch (RedisException $e) {
            $this->setError($e->getMessage());
            $res = false;
        }
        if ($pass !== null && $pass !== '') {
            try {
                $res = $redis->auth($pass);
            } catch (RedisException $e) {
                $this->setError($e->getMessage());
                $res = false;
            }
        }
        if ($database !== null && $database !== '') {
            try {
                $res = $redis->select($database);
            } catch (RedisException $e) {
                $this->setError($e->getMessage());
                $res = false;
            }
        }
        if ($res === false) {
            return false;
        }
        $this->setError('');
        // set custom prefix
        $redis->setOption(Redis::OPT_PREFIX, $this->options['prefix']);
        // set php serializer for objects and arrays
        $redis->setOption(Redis::OPT_SERIALIZER, (string)Redis::SERIALIZER_PHP);
        $this->redis = $redis;

        return true;
    }

    /**
     * @inheritdoc
     */
    public function store($cacheID, $content, $expiration = null): bool
    {
        try {
            $res = $this->redis->set($cacheID, $content);
            $exp = $expiration ?? $this->options['lifetime'];
            // the journal and negative expiration values should not cause an expiration
            if ($cacheID !== $this->journalID && $exp > -1) {
                $this->redis->expire($cacheID, $exp);
            }

            return \is_bool($res) ? $res : false;
        } catch (RedisException $e) {
            echo 'Redis exception: ' . $e->getMessage();

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
            foreach (\array_keys($idContent) as $_cacheID) {
                $this->redis->expire($_cacheID, $expiration ?? $this->options['lifetime']);
            }

            return $res;
        } catch (RedisException $e) {
            echo 'Redis exception: ' . $e->getMessage();

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
        } catch (RedisException $e) {
            echo 'Redis exception: ' . $e->getMessage();

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function loadMulti(array $cacheIDs): array
    {
        try {
            $res    = $this->redis->mget($cacheIDs);
            $i      = 0;
            $return = [];
            foreach ($res as $_idx => $_val) {
                $return[$cacheIDs[$i]] = $_val;
                ++$i;
            }

            return $return;
        } catch (RedisException $e) {
            echo 'Redis exception: ' . $e->getMessage();

            return [];
        }
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
        try {
            return $this->redis->del($cacheID) > 0;
        } catch (RedisException $e) {
            echo 'Redis exception: ' . $e->getMessage();

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function setCacheTag($tags, $cacheID): bool
    {
        $res   = false;
        $redis = $this->redis->multi();
        if (\is_string($tags)) {
            $tags = [$tags];
        }
        if (\count($tags) > 0) {
            foreach ($tags as $tag) {
                $redis->sAdd(self::_keyFromTagName($tag), $cacheID);
            }
            $redis->exec();
            $res = true;
        }

        return $res;
    }

    /**
     * @param string|int $tagName
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
        return $this->redis->flushDB();
    }

    /**
     * @inheritdoc
     */
    public function getKeysByTag($tags = []): array
    {
        $matchTags = \is_string($tags)
            ? [self::_keyFromTagName($tags)]
            : \array_map('JTL\Cache\Methods\CacheRedis::_keyFromTagName', $tags);
        $res       = \count($matchTags) === 1
            ? $this->redis->sMembers($matchTags[0])
            : $this->redis->sUnion($matchTags);
        if (\PHP_SAPI === 'srv' || \PHP_SAPI === 'cli') { // for some reason, hhvm does not unserialize values
            foreach ($res as &$_cid) {
                // phpredis will throw an exception when unserializing unserialized data
                try {
                    $_cid = $this->redis->_unserialize($_cid);
                } catch (RedisException $e) {
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
        $numEntries  = null;
        $slowLog     = [];
        $slowLogData = [];
        try {
            $stats = $this->redis->info();
        } catch (RedisException $e) {
            echo 'Redis exception: ' . $e->getMessage();

            return [];
        }
        try {
            $slowLog = \method_exists($this->redis, 'slowlog')
                ? $this->redis->slowlog('get', 25)
                : [];
        } catch (RedisException $e) {
            echo 'Redis exception: ' . $e->getMessage();
        }
        $db  = $this->redis->getDBNum();
        $idx = 'db' . $db;
        if (isset($stats[$idx])) {
            $dbStats = \explode(',', $stats[$idx]);
            foreach ($dbStats as $stat) {
                if (\mb_strpos($stat, 'keys=') !== false) {
                    $numEntries = \str_replace('keys=', '', $stat);
                }
            }
        }
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

        return [
            'entries'  => $numEntries,
            'uptime'   => $stats['uptime_in_seconds'] ?? null, //uptime in seconds
            'uptime_h' => isset($stats['uptime_in_seconds'])
                ? $this->secondsToTime($stats['uptime_in_seconds'])
                : null, //human readable
            'hits'     => $stats['keyspace_hits'], //cache hits
            'misses'   => $stats['keyspace_misses'], //cache misses
            'hps'      => isset($stats['uptime_in_seconds'])
                ? ($stats['keyspace_hits'] / $stats['uptime_in_seconds'])
                : null, //hits per second
            'mps'      => isset($stats['uptime_in_seconds'])
                ? ($stats['keyspace_misses'] / $stats['uptime_in_seconds'])
                : null, //misses per second
            'mem'      => $stats['used_memory'], //used memory in bytes
            'slow'     => $slowLogData //redis slow log
        ];
    }
}
