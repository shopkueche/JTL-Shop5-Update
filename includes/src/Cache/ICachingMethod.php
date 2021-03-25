<?php declare(strict_types=1);

namespace JTL\Cache;

/**
 * Interface ICachingMethod
 * @package JTL\Cache
 */
interface ICachingMethod
{
    /**
     * store value to cache
     *
     * @param string   $cacheID - key to identify the value
     * @param mixed    $content - the content to save
     * @param int|null $expiration - expiration time in seconds
     * @return bool - success
     */
    public function store($cacheID, $content, $expiration): bool;

    /**
     * store multiple values to multiple keys at once to cache
     *
     * @param array    $idContent - array keys are cache IDs, array values are content to save
     * @param int|null $expiration - expiration time in seconds
     * @return bool
     */
    public function storeMulti($idContent, $expiration): bool;

    /**
     * get value from cache
     *
     * @param string $cacheID
     * @return mixed|bool - the loaded data or false if not found
     */
    public function load($cacheID);

    /**
     * check if key exists
     *
     * @param string $key
     * @return bool
     */
    public function keyExists($key): bool;

    /**
     * get multiple values at once from cache
     *
     * @param array $cacheIDs
     * @return array
     */
    public function loadMulti(array $cacheIDs): array;

    /**
     * add cache tags to cached value
     *
     * @param string|array $tags
     * @param string       $cacheID
     * @return bool
     */
    public function setCacheTag($tags, $cacheID): bool;

    /**
     * get cache IDs by cache tag(s)
     *
     * @param array|string $tags
     * @return array
     */
    public function getKeysByTag($tags): array;

    /**
     * removes cache IDs associated with given tags from cache
     *
     * @param array|string $tags
     * @return int
     */
    public function flushTags($tags): int;

    /**
     * load journal
     *
     * @return array
     */
    public function getJournal(): array;

    /**
     * class singleton getter
     *
     * @param array $options
     * @return mixed
     */
    public static function getInstance($options);

    /**
     * check if php functions for using the selected caching method exist
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * check if method was successfully initialized
     *
     * @return bool
     */
    public function isInitialized(): bool;

    /**
     * clear cache by cid or gid
     *
     * @param string|array $cacheID
     * @return bool - success
     */
    public function flush($cacheID): bool;

    /**
     * flushes all values from cache
     *
     * @return bool
     */
    public function flushAll(): bool;

    /**
     * test data integrity and if functions are working properly - default implementation @JTLCacheTrait
     *
     * @return bool - success
     */
    public function test(): bool;

    /**
     * get statistical data for caching method if supported
     *
     * @return array
     */
    public function getStats(): array;

    /**
     * @return string|null
     */
    public function getJournalID(): ?string;

    /**
     * @param string $id
     */
    public function setJournalID($id): void;

    /**
     * @return string
     */
    public function getError(): string;

    /**
     * @param string $error
     * @return JTLCacheInterface
     */
    public function setError(string $error);
}
