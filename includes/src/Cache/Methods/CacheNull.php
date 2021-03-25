<?php declare(strict_types=1);

namespace JTL\Cache\Methods;

use JTL\Cache\ICachingMethod;
use JTL\Cache\JTLCacheTrait;

/**
 * Class CacheNull
 *
 * emergency fallback caching method
 * @package JTL\Cache\Methods
 */
class CacheNull implements ICachingMethod
{
    use JTLCacheTrait;

    /**
     * @var CacheNull|null
     */
    public static $instance;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        $this->isInitialized = true;
        $this->options       = $options;
        $this->journalID     = 'null_journal';
        self::$instance      = $this;
    }

    /**
     * @inheritdoc
     */
    public function store($cacheID, $content, $expiration = null): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function storeMulti($idContent, $expiration = null): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function load($cacheID)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function loadMulti(array $cacheIDs): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function flush($cacheID): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function flushAll(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getStats(): array
    {
        return [];
    }
}
