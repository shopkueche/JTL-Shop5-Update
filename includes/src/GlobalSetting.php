<?php

namespace JTL;

use Illuminate\Support\Collection;
use JTL\Cache\JTLCache;

/**
 * Class GlobalSetting
 * @package JTL
 */
final class GlobalSetting
{
    /**
     * @var self
     */
    private static $instance;

    /**
     * @var JTLCache
     */
    private $cache;

    /**
     * @var Collection
     */
    private $settings;

    public const CHILD_ITEM_BULK_PRICING = 'GENERAL_CHILD_ITEM_BULK_PRICING';

    private const CACHE_ID = 'setting_global';

    /**
     *
     */
    private function __construct()
    {
        self::$instance = $this;

        $this->cache = Shop::Container()->getCache();
    }

    /**
     *
     */
    private function __clone()
    {
    }

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self();
    }

    /**
     * @return Collection
     */
    private function loadSettings(): Collection
    {
        //todo: implement this method if global settings are supported in dbeS
        return new Collection();
    }

    /**
     * @return Collection
     */
    private function getSettings(): Collection
    {
        if ($this->settings === null || $this->settings->isEmpty()) {
            $this->settings = $this->cache->get(
                self::CACHE_ID,
                function ($cache, $id, &$content, &$tags) {
                    $content = $this->loadSettings();
                    $tags    = [\CACHING_GROUP_OPTION];

                    return true;
                }
            );
        }

        return $this->settings;
    }

    /**
     * @param string     $valueName
     * @param mixed|null $default
     * @return mixed
     */
    public function getValue(string $valueName, $default = null)
    {
        $value = $this->getSettings()->get($valueName, $default);

        switch (\gettype($default)) {
            case 'boolean':
                return (bool)$value;
            case 'integer':
                return (int)$value;
            case 'double':
                return (float)$value;
            case 'string':
                return (string)$value;
        }

        return $value;
    }
}
