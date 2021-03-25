<?php declare(strict_types=1);

namespace JTL\Cache\Methods;

use JTL\Cache\ICachingMethod;
use JTL\Cache\JTLCacheTrait;

/**
 * Class CacheSession
 * Implements caching via PHP $_SESSION object
 * @package JTL\Cache\Methods
 */
class CacheSession implements ICachingMethod
{
    use JTLCacheTrait;

    /**
     * @var CacheSession
     */
    public static $instance;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        $this->isInitialized = true;
        $this->journalID     = 'session_journal';
        $this->options       = $options;
    }

    /**
     * @inheritdoc
     */
    public function store($cacheID, $content, $expiration = null): bool
    {
        $_SESSION[$this->options['prefix'] . $cacheID] = [
            'value'     => $content,
            'timestamp' => \time(),
            'lifetime'  => $expiration ?? $this->options['lifetime']
        ];

        return true;
    }

    /**
     * @inheritdoc
     */
    public function storeMulti($idContent, $expiration = null): bool
    {
        foreach ($idContent as $_key => $_value) {
            $this->store($_key, $_value, $expiration);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function load($cacheID)
    {
        $originalCacheID = $cacheID;
        $cacheID         = $this->options['prefix'] . $cacheID;
        if (isset($_SESSION[$cacheID])) {
            $cacheValue = $_SESSION[$cacheID];
            if ((\time() - $cacheValue['timestamp']) < $cacheValue['lifetime']) {
                return $cacheValue['value'];
            }
            $this->flush($originalCacheID);

            return false;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function loadMulti(array $cacheIDs): array
    {
        $res = [];
        foreach ($cacheIDs as $_cid) {
            $res[$_cid] = $this->load($_cid);
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return $_SESSION !== null;
    }

    /**
     * @inheritdoc
     */
    public function flush($cacheID): bool
    {
        unset($_SESSION[$this->options['prefix'] . $cacheID]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function flushAll(): bool
    {
        foreach ($_SESSION as $_sessionKey => $_sessionValue) {
            if (\mb_strpos($_sessionKey, $this->options['prefix']) === 0) {
                unset($_SESSION[$_sessionKey]);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function keyExists($key): bool
    {
        return isset($_SESSION[$this->options['prefix'] . $key]);
    }

    /**
     * @inheritdoc
     */
    public function getStats(): array
    {
        $num = 0;
        $tmp = [];
        foreach ($_SESSION as $_sessionKey => $_sessionValue) {
            if (\mb_strpos($_sessionKey, $this->options['prefix']) === 0) {
                $num++;
                $tmp[] = $_sessionKey;
            }
        }
        $startMemory = \memory_get_usage();
        $_tmp2       = \unserialize(\serialize($tmp));
        $total       = \memory_get_usage() - $startMemory;

        return [
            'entries' => $num,
            'hits'    => null,
            'misses'  => null,
            'inserts' => null,
            'mem'     => $total
        ];
    }
}
