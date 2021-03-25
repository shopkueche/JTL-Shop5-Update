<?php declare(strict_types=1);

namespace JTL\Cache\Methods;

use JTL\Cache\ICachingMethod;
use JTL\Cache\JTLCacheTrait;

/**
 * Class CacheApc
 *
 * implements the APC Opcode Cache
 * @package JTL\Cache\Methods
 */
class CacheApc implements ICachingMethod
{
    use JTLCacheTrait;

    /**
     * @var CacheApc
     */
    public static $instance;

    /**
     * check whether apc_ or apcu_ functions should be used
     *
     * @var bool
     */
    private $u;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        $this->isInitialized = true;
        $this->journalID     = 'apc_journal';
        $this->options       = $options;
        $this->u             = \function_exists('apcu_store');
        self::$instance      = $this;
    }

    /**
     * @inheritdoc
     */
    public function store($cacheID, $content, $expiration = null): bool
    {
        $func = $this->u ? 'apcu_store' : 'apc_store';

        return $func($this->options['prefix'] . $cacheID, $content, $expiration ?? $this->options['lifetime']);
    }

    /**
     * @inheritdoc
     */
    public function storeMulti($idContent, $expiration = null): bool
    {
        $func = $this->u ? 'apcu_store' : 'apc_store';

        return $func($this->prefixArray($idContent), null, $expiration ?? $this->options['lifetime']);
    }

    /**
     * @inheritdoc
     */
    public function load($cacheID)
    {
        $func = $this->u ? 'apcu_fetch' : 'apc_fetch';

        return $func($this->options['prefix'] . $cacheID);
    }

    /**
     * @inheritdoc
     */
    public function loadMulti(array $cacheIDs): array
    {
        if (!\is_array($cacheIDs)) {
            return [];
        }
        $func         = $this->u ? 'apcu_fetch' : 'apc_fetch';
        $prefixedKeys = [];
        foreach ($cacheIDs as $_cid) {
            $prefixedKeys[] = $this->options['prefix'] . $_cid;
        }
        $res = $this->dePrefixArray($func($prefixedKeys));

        // fill up with false values
        return \array_merge(\array_fill_keys($cacheIDs, false), $res);
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return ((\function_exists('apc_store') && \function_exists('apc_exists'))
            || (\function_exists('apcu_store') && \function_exists('apcu_exists')));
    }

    /**
     * @inheritdoc
     */
    public function flush($cacheID): bool
    {
        $func = $this->u ? 'apcu_delete' : 'apc_delete';

        return $func($this->options['prefix'] . $cacheID);
    }

    /**
     * @inheritdoc
     */
    public function flushAll(): bool
    {
        return $this->u ? \apcu_clear_cache() : \apc_clear_cache('user');
    }

    /**
     * @inheritdoc
     */
    public function keyExists($key): bool
    {
        $func = $this->u ? 'apcu_exists' : 'apc_exists';

        return $func($this->options['prefix'] . $key);
    }

    /**
     * @inheritdoc
     */
    public function getStats(): array
    {
        try {
            $tmp   = $this->u ? \apcu_cache_info() : \apc_cache_info('user');
            $stats = [
                'entries' => $tmp['num_entries'] ?? 0,
                'hits'    => $tmp['num_hits'] ?? 0,
                'misses'  => $tmp['num_misses'] ?? 0,
                'inserts' => $tmp['num_inserts'] ?? 0,
                'mem'     => $tmp['mem_size'] ?? 0
            ];
        } catch (\Exception $e) {
            $stats = [];
        }

        return $stats;
    }
}
