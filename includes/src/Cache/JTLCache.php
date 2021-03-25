<?php declare(strict_types=1);

namespace JTL\Cache;

use JTL\Cache\Methods\CacheNull;
use JTL\Helpers\Request;
use JTL\Profiler;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;

/**
 * Class JTLCache
 * @package JTL\Cache
 */
final class JTLCache implements JTLCacheInterface
{
    /**
     * default port for redis caching method
     */
    public const DEFAULT_REDIS_PORT = 6379;

    /**
     * default host name for redis caching method
     */
    public const DEFAULT_REDIS_HOST = 'localhost';

    /**
     * default memcache(d) port
     */
    public const DEFAULT_MEMCACHE_PORT = 11211;

    /**
     * default memcache(d) host name
     */
    public const DEFAULT_MEMCACHE_HOST = 'localhost';

    /**
     * default cache life time in seconds (86400 = 1 day)
     */
    public const DEFAULT_LIFETIME = 86400;

    /**
     * result code for successful getting result from cache
     */
    public const RES_SUCCESS = 1;

    /**
     * result code for cache miss
     */
    public const RES_FAIL = 2;

    /**
     * result code when getting multiple values at once
     */
    public const RES_UNDEF = 3;

    /**
     * currently active caching method
     *
     * @var ICachingMethod
     */
    private $method;

    /**
     * caching options
     *
     * @var array
     */
    private $options = [];

    /**
     * plugin instance
     *
     * @var JTLCache
     */
    private static $instance;

    /**
     * get/set result code
     *
     * @var int
     */
    private $resultCode = self::RES_UNDEF;

    /**
     * @var array
     */
    private $cachingGroups = [];

    /**
     * @var string
     */
    private $error = '';

    /**
     * init cache and set default method
     *
     * @param array $options
     * @param bool  $ignoreInstance - used for page cache to not overwrite the instance and delete debug output
     */
    public function __construct(array $options = [], bool $ignoreInstance = false)
    {
        if ($ignoreInstance === false) {
            self::$instance = $this;
        }
        $this->setCachingGroups()
             ->setOptions($options);
    }

    /**
     * build list of all caching groups
     * enriched with description placeholders that can be loaded as smarty variables
     *
     * @return $this
     */
    private function setCachingGroups(): JTLCacheInterface
    {
        $this->cachingGroups = [
            [
                'name'        => 'CACHING_GROUP_ARTICLE',
                'nicename'    => 'cg_article_nicename',
                'value'       => \CACHING_GROUP_ARTICLE,
                'description' => 'cg_article_description'
            ],
            [
                'name'        => 'CACHING_GROUP_CATEGORY',
                'nicename'    => 'cg_category_nicename',
                'value'       => \CACHING_GROUP_CATEGORY,
                'description' => 'cg_category_description'
            ],
            [
                'name'        => 'CACHING_GROUP_LANGUAGE',
                'nicename'    => 'cg_language_nicename',
                'value'       => \CACHING_GROUP_LANGUAGE,
                'description' => 'cg_language_description'
            ],
            [
                'name'        => 'CACHING_GROUP_TEMPLATE',
                'nicename'    => 'cg_template_nicename',
                'value'       => \CACHING_GROUP_TEMPLATE,
                'description' => 'cg_template_description'
            ],
            [
                'name'        => 'CACHING_GROUP_OPTION',
                'nicename'    => 'cg_option_nicename',
                'value'       => \CACHING_GROUP_OPTION,
                'description' => 'cg_option_description'
            ],
            [
                'name'        => 'CACHING_GROUP_PLUGIN',
                'nicename'    => 'cg_plugin_nicename',
                'value'       => \CACHING_GROUP_PLUGIN,
                'description' => 'cg_plugin_description'
            ],
            [
                'name'        => 'CACHING_GROUP_CORE',
                'nicename'    => 'cg_core_nicename',
                'value'       => \CACHING_GROUP_CORE,
                'description' => 'cg_core_description'
            ],
            [
                'name'        => 'CACHING_GROUP_OBJECT',
                'nicename'    => 'cg_object_nicename',
                'value'       => \CACHING_GROUP_OBJECT,
                'description' => 'cg_object_description'
            ],
            [
                'name'        => 'CACHING_GROUP_BOX',
                'nicename'    => 'cg_box_nicename',
                'value'       => \CACHING_GROUP_BOX,
                'description' => 'cg_box_description'
            ],
            [
                'name'        => 'CACHING_GROUP_NEWS',
                'nicename'    => 'cg_news_nicename',
                'value'       => \CACHING_GROUP_NEWS,
                'description' => 'cg_news_description'
            ],
            [
                'name'        => 'CACHING_GROUP_ATTRIBUTE',
                'nicename'    => 'cg_attribute_nicename',
                'value'       => \CACHING_GROUP_ATTRIBUTE,
                'description' => 'cg_attribute_description'
            ],
            [
                'name'        => 'CACHING_GROUP_MANUFACTURER',
                'nicename'    => 'cg_manufacturer_nicename',
                'value'       => \CACHING_GROUP_MANUFACTURER,
                'description' => 'cg_manufacturer_description'
            ],
            [
                'name'        => 'CACHING_GROUP_FILTER',
                'nicename'    => 'cg_filter_nicename',
                'value'       => \CACHING_GROUP_FILTER,
                'description' => 'cg_filter_description'
            ],
            [
                'name'        => 'CACHING_GROUP_STATUS',
                'nicename'    => 'cg_status_nicename',
                'value'       => \CACHING_GROUP_STATUS,
                'description' => 'cg_status_description'
            ],
        ];

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCachingGroups(): array
    {
        return $this->cachingGroups;
    }

    /**
     * @inheritdoc
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @inheritdoc
     */
    public function setError(string $error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setOptions(array $options = []): JTLCacheInterface
    {
        $defaults = [
            'activated'        => false,
            // main switch
            'method'           => 'null',
            // caching method to use - init with null to avoid errors after installation
            'redis_port'       => self::DEFAULT_REDIS_PORT,
            //port of redis server
            'redis_pass'       => null,
            // password for redis server
            'redis_host'       => self::DEFAULT_REDIS_HOST,
            //host of redis server
            'redis_db'         => null,
            // optional redis database id, null or 0 for default
            'redis_persistent' => false,
            // optional redis database id, null or 0 for default
            'memcache_port'    => self::DEFAULT_MEMCACHE_PORT,
            // port for memcache(d) server
            'memcache_host'    => self::DEFAULT_MEMCACHE_HOST,
            // host of memcache(d) server
            'prefix'           => 'j5_' . (\defined('DB_NAME') ? \DB_NAME . '_' : ''),
            // try to make a quite unique prefix if multiple shops are used
            'lifetime'         => self::DEFAULT_LIFETIME,
            // cache lifetime in seconds
            'collect_stats'    => false,
            // used to tell caching methods to collect statistical data or not (if not provided transparently)
            'debug'            => false,
            // enable or disable collecting of debug data
            'debug_method'     => 'echo',
            // 'ssd'/'jtld' for SmarterSmartyDebug/JTLDebug, 'echo' for direct echo
            'cache_dir'        => \OBJECT_CACHE_DIR,
            //file cache directory
            'file_extension'   => '.fc',
            // file extension for file cache
            'page_cache'       => false,
            // smarty page cache switch
            'types_disabled'   => []
            // disabled cache groups
        ];
        // merge defaults with assigned options and set them
        $this->options = \array_merge($defaults, $options);
        // always add trailing slash
        if (\mb_substr($this->options['cache_dir'], \mb_strlen($this->options['cache_dir']) - 1) !== '/') {
            $this->options['cache_dir'] .= '/';
        }
        if ($this->options['method'] !== 'redis' && $this->options['lifetime'] < 0) {
            $this->options['lifetime'] = 0;
        }
        // accept only valid integer lifetime values
        $this->options['lifetime'] = ($this->options['lifetime'] === '' || $this->options['lifetime'] === 0)
            ? self::DEFAULT_LIFETIME
            : $this->options['lifetime'];
        if ($this->options['types_disabled'] === null) {
            $this->options['types_disabled'] = [];
        }
        if ($this->options['debug'] === true && $this->options['debug_method'] === 'echo') {
            echo '<br />Initialized Cache with method ' . $this->options['method'];
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCache(string $methodName): bool
    {
        if (\SAFE_MODE === false) {
            $cache = null;
            /** @var ICachingMethod $className */
            $class = 'JTL\Cache\Methods\Cache' . \ucfirst($methodName);
            $cache = new $class($this->options);
            if (!empty($cache) && $cache instanceof ICachingMethod) {
                $this->setError($cache->getError());
                if ($cache->isInitialized() && $cache->isAvailable()) {
                    $this->setMethod($cache);

                    return true;
                }
            }
        }
        $this->setMethod(CacheNull::getInstance($this->options));

        return false;
    }

    /**
     * set caching method
     *
     * @param ICachingMethod|JTLCacheTrait $method
     * @return $this
     */
    private function setMethod($method): JTLCacheInterface
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getJtlCacheConfig(array $config): array
    {
        $cacheInit = [];
        foreach ($config as $_conf) {
            if ($_conf->cWert === 'Y' || $_conf->cWert === 'y') {
                $value = true;
            } elseif ($_conf->cWert === 'N' || $_conf->cWert === 'n') {
                $value = false;
            } elseif ($_conf->cWert === '') {
                $value = null;
            } elseif (\is_numeric($_conf->cWert)) {
                $value = (int)$_conf->cWert;
            } else {
                $value = $_conf->cWert;
            }
            // naming convention is 'caching_'<var-name> for options saved in database
            $cacheInit[\str_replace('caching_', '', $_conf->cName)] = $value;
        }
        // disabled cache types are saved as serialized string in db
        if (isset($cacheInit['types_disabled'])
            && \is_string($cacheInit['types_disabled'])
            && $cacheInit['types_disabled'] !== ''
        ) {
            $cacheInit['types_disabled'] = \unserialize($cacheInit['types_disabled']);
        }

        return $cacheInit;
    }

    /**
     * @inheritdoc
     */
    public function setJtlCacheConfig(array $config): JTLCacheInterface
    {
        $this->setOptions($this->getJtlCacheConfig($config))->init();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function init(): JTLCacheInterface
    {
        if ($this->options['activated'] === true) {
            // set the configure caching method
            $this->setCache($this->options['method']);
            // preload shop settings and lang vars to avoid single cache/mysql requests
            $settings = Shopsetting::getInstance();
            $settings->preLoad();
        } else {
            // set fallback null method
            $this->setCache('null');
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @inheritdoc
     */
    public function setRedisCredentials($host, $port, $pass = null, $database = null): JTLCacheInterface
    {
        $this->options['redis_host'] = $host;
        $this->options['redis_port'] = $port;
        $this->options['redis_pass'] = $pass;
        $this->options['redis_db']   = $database;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setMemcacheCredentials($host, $port): JTLCacheInterface
    {
        $this->options['memcache_host'] = $host;
        $this->options['memcache_port'] = $port;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setMemcachedCredentials($host, $port): JTLCacheInterface
    {
        return $this->setMemcacheCredentials($host, $port);
    }

    /**
     * @inheritdoc
     */
    public function get($cacheID, $callback = null, $customData = null)
    {
        $res              = $this->options['activated'] === true
            ? $this->method->load($cacheID)
            : false;
        $this->resultCode = ($res !== false || $this->method->keyExists($cacheID))
            ? self::RES_SUCCESS
            : self::RES_FAIL;
        if ($this->options['debug'] === true) {
            if ($this->options['debug_method'] === 'echo') {
                echo '<br />Key ' . $cacheID . (($this->resultCode !== self::RES_SUCCESS)
                        ? ' could not be'
                        : 'successfully') . ' loaded.';
            } else {
                Profiler::setCacheProfile('get', (($res !== false) ? 'success' : 'failure'), $cacheID);
            }
        }
        if ($callback !== null && $this->resultCode !== self::RES_SUCCESS && \is_callable($callback)) {
            $content    = null;
            $tags       = null;
            $expiration = null;
            $res        = \call_user_func_array(
                $callback,
                [$this, $cacheID, &$content, &$tags, &$expiration, $customData]
            );
            if ($res === true) {
                $this->set($cacheID, $content, $tags, $expiration);

                return $content;
            }
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function set($cacheID, $content, $tags = null, $expiration = null): bool
    {
        $res = false;
        if ($this->options['activated'] === true && $this->isCacheGroupActive($tags) === true) {
            $res = $this->method->store($cacheID, $content, $expiration);
            if ($res === true && $tags !== null) {
                $this->setCacheTag($tags, $cacheID);
            }
        }
        if ($this->options['debug'] === true) {
            if ($this->options['debug_method'] === 'echo') {
                echo '<br />Key ' . $cacheID . (($res !== false) ? 'successfully' : 'could not be') . ' set.';
            } else {
                Profiler::setCacheProfile('set', (($res !== false) ? 'success' : 'failure'), $cacheID);
            }
        }
        $this->resultCode = $res === false ? self::RES_FAIL : self::RES_SUCCESS;

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function setMulti($keyValue, $tags = null, $expiration = null): bool
    {
        if ($this->options['activated'] === true && $this->isCacheGroupActive($tags) === true) {
            $res = $this->method->storeMulti($keyValue, $expiration);
            if ($res === true && $tags !== null) {
                foreach (\array_keys($keyValue) as $_cacheID) {
                    $this->setCacheTag($tags, $_cacheID);
                }
            }
            $this->resultCode = self::RES_UNDEF; // for now, let's not check every part of the result

            return $res;
        }
        $this->resultCode = self::RES_FAIL;

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getMulti(array $cacheIDs): array
    {
        $this->resultCode = self::RES_UNDEF; // for now, let's not check every part of the result

        return $this->method->loadMulti($cacheIDs);
    }

    /**
     * @inheritdoc
     */
    public function isCacheGroupActive($groupID): bool
    {
        if ($this->options['activated'] === false) {
            // if the cache is disabled, every tag is inactive
            return false;
        }
        if (\is_string($groupID)
            && \is_array($this->options['types_disabled'])
            && \in_array($groupID, $this->options['types_disabled'], true)
        ) {
            return false;
        }
        if (\is_array($groupID)) {
            foreach ($groupID as $group) {
                if (\in_array($group, $this->options['types_disabled'], true)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getKeysByTag($tags): array
    {
        return $this->method->getKeysByTag($tags);
    }

    /**
     * @inheritdoc
     */
    public function setCacheTag($tags, $cacheID): bool
    {
        return $this->options['activated'] === true
            ? $this->method->setCacheTag($tags, $cacheID)
            : false;
    }

    /**
     * @inheritdoc
     */
    public function setCacheLifetime(int $lifetime): JTLCacheInterface
    {
        $this->options['lifetime'] = $lifetime > 0
            ? $lifetime
            : self::DEFAULT_LIFETIME;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCacheDir(string $dir): JTLCacheInterface
    {
        $this->options['cache_dir'] = $dir;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getActiveMethod(): ICachingMethod
    {
        return $this->method;
    }

    /**
     * @inheritdoc
     */
    public function flush($cacheID = null, $tags = null, $hookInfo = null)
    {
        $res = false;
        if ($cacheID !== null && $tags === null) {
            $res = ($this->options['activated'] === true)
                ? $this->method->flush($cacheID)
                : false;
        } elseif ($tags !== null) {
            $res = $this->flushTags($tags, $hookInfo);
        }
        if ($this->options['debug'] === true) {
            if ($this->options['debug_method'] === 'echo') {
                echo '<br />Key ' . $cacheID . ($res !== false ? ' ' : ' not') . ' flushed';
            } else {
                Profiler::setCacheProfile('flush', ($res !== false ? 'success' : 'failure'), $cacheID);
            }
        }
        if ($hookInfo !== null && \defined('HOOK_CACHE_FLUSH_AFTER') && \function_exists('executeHook')) {
            \executeHook(\HOOK_CACHE_FLUSH_AFTER, $hookInfo);
        }
        $this->resultCode = \is_int($res) ? self::RES_FAIL : self::RES_SUCCESS;

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function flushTags($tags, $hookInfo = null): int
    {
        $deleted = $this->method->flushTags($tags);
        if ($hookInfo !== null && \defined('HOOK_CACHE_FLUSH_AFTER') && \function_exists('executeHook')) {
            \executeHook(\HOOK_CACHE_FLUSH_AFTER, $hookInfo);
        }

        return $deleted;
    }

    /**
     * @inheritdoc
     */
    public function flushAll(): bool
    {
        $this->method->flush($this->method->getJournalID());

        return $this->method->flushAll();
    }

    /**
     * @inheritdoc
     */
    public function getResultCode(): int
    {
        return $this->resultCode;
    }

    /**
     * @inheritdoc
     */
    public function getJournal(): array
    {
        return $this->method->getJournal();
    }

    /**
     * @inheritdoc
     */
    public function getStats(): array
    {
        return $this->method->getStats();
    }

    /**
     * @inheritdoc
     */
    public function testMethod(): bool
    {
        return $this->method->test();
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return $this->method->isAvailable();
    }

    /**
     * @inheritdoc
     */
    public function isActive(): bool
    {
        return (bool)$this->options['activated'];
    }

    /**
     * @inheritdoc
     */
    public function getAllMethods(): array
    {
        return [
            'advancedfile',
            'apc',
            'file',
            'memcache',
            'memcached',
            'null',
            'redis',
            'redisCluster',
            'session',
            'xcache'
        ];
    }

    /**
     * @inheritdoc
     */
    public function checkAvailability(): array
    {
        $available = [];
        foreach ($this->getAllMethods() as $methodName) {
            $class = 'JTL\Cache\Methods\Cache' . \ucfirst($methodName);
            /** @var ICachingMethod $instance */
            $instance               = new $class($this->options);
            $available[$methodName] = [
                'available'  => $instance->isAvailable(),
                'functional' => $instance->test()
            ];
        }

        return $available;
    }

    /**
     * @inheritdoc
     */
    public function getBaseID(
        $hash = false,
        $customerID = false,
        $customerGroup = true,
        $languageID = true,
        $currencyID = true,
        $sslStatus = true
    ): string {
        $baseID = 'b';
        // add customer ID
        if ($customerID === true) {
            $baseID .= '_cid';
            $baseID .= $_SESSION['Kunde']->kKunde ?? '-1';
        }
        // add customer group
        if ($customerGroup === true) {
            $baseID .= '_cgid' . Frontend::getCustomerGroup()->getID();
        } elseif (\is_numeric($customerGroup)) {
            $baseID .= '_cgid' . (int)$customerGroup;
        }
        // add language ID
        if ($languageID === true) {
            $baseID .= '_lid';
            $lang    = Shop::getLanguageID();
            if ($lang > 0) {
                $baseID .= $lang;
            } elseif (Shop::getLanguageID() > 0) {
                $baseID .= Shop::getLanguageID();
            } else {
                $baseID .= '0';
            }
        } elseif (\is_numeric($languageID)) {
            $baseID .= '_lid' . (int)$languageID;
        }
        // add currency ID
        if ($currencyID === true) {
            $baseID .= '_curid' . Frontend::getCurrency()->getID();
        } elseif (\is_numeric($currencyID)) {
            $baseID .= '_curid' . (int)$currencyID;
        }
        // add current SSL status
        if ($sslStatus === true) {
            $baseID .= '_ssl' . Request::checkSSL();
        }

        if ($this->options['debug'] === true && $this->options['debug_method'] === 'echo') {
            echo '<br>generated $baseID ' . $baseID;
        }

        return $hash === true ? \md5($baseID) : $baseID;
    }

    /**
     * @inheritdoc
     */
    public function benchmark(
        $methods = 'all',
        $testData = 'simple string',
        $runCount = 1000,
        $repeat = 1,
        $echo = true,
        $format = false
    ): array {
        $this->options['activated'] = true;
        $this->options['lifetime']  = self::DEFAULT_LIFETIME;
        // sanitize input
        if (!\is_int($runCount) || $runCount < 1) {
            $runCount = 1;
        }
        if (!\is_int($repeat) || $repeat < 1) {
            $repeat = 1;
        }
        $results = [];
        if ($methods === 'all') {
            $methods = $this->getAllMethods();
        }
        if (\is_array($methods)) {
            foreach ($methods as $method) {
                if ($method !== 'null') {
                    $results[] = $this->benchmark($method, $testData, $runCount, $repeat, $echo, $format);
                }
            }
        } else {
            $timesSet     = 0;
            $timesGet     = 0;
            $cacheSetRes  = $this->setCache($methods);
            $validResults = true;
            if ($echo === true) {
                echo '### Testing ' . $methods . ' cache ###';
            }
            $result = [
                'method'  => $methods,
                'status'  => 'ok',
                'timings' => ['get' => 0.0, 'set' => 0.0]
            ];
            if ($cacheSetRes !== false) {
                for ($i = 0; $i < $repeat; ++$i) {
                    // set testing
                    $start = \microtime(true);
                    for ($j = 0; $j < $runCount; ++$j) {
                        $cacheID = 'c_' . $j;
                        $this->set($cacheID, $testData);
                    }
                    $end          = \microtime(true);
                    $runTimingSet = ($end - $start);
                    $timesSet    += $runTimingSet;
                    // get testing
                    $start = \microtime(true);
                    for ($j = 0; $j < $runCount; ++$j) {
                        $cacheID = 'c_' . $j;
                        $res     = $this->get($cacheID);
                        if ($res !== $testData) {
                            $validResults = false;
                        }
                    }
                    $end          = \microtime(true);
                    $runTimingGet = ($end - $start);
                    $timesGet    += $runTimingGet;
                }
            } else {
                if ($echo === true) {
                    echo '<br />Caching method not supported by server<br /><br />';
                }
                $result['status'] = 'failed';

                return $result;
            }
            if ($timesSet > 0.0 && $timesGet > 0.0 && $validResults !== false) {
                // calculate averages
                $rpsGet    = ($runCount * $repeat / $timesGet);
                $rpsSet    = ($runCount * $repeat / $timesSet);
                $timesSet /= $repeat;
                $timesGet /= $repeat;
                if ($format === true) {
                    $timesSet = \number_format($timesSet, 4, ',', '.');
                    $timesGet = \number_format($timesGet, 4, ',', '.');
                    $rpsSet   = \number_format($rpsSet, 2, ',', '.');
                    $rpsGet   = \number_format($rpsGet, 2, ',', '.');
                }
                // output averages
                if ($echo === true) {
                    echo '<br />Avg. time for setting: ' . $timesSet . 's (' . $rpsSet . ' requests per second)';
                    echo '<br />Avg. time for getting: ' . $timesGet . 's (' . $rpsGet . ' requests per second)';
                }
                $result['timings'] = ['get' => $timesGet, 'set' => $timesSet];
                $result['rps']     = ['get' => $rpsGet, 'set' => $rpsSet];
            }
            if ($validResults === false) {
                if ($echo === true) {
                    echo '<br />Got invalid results when loading cached values!';
                }
                $result['status'] = 'invalid';
            }
            if ($echo === true) {
                echo '<br /><br />';
            }

            return $result;
        }

        return $results;
    }
}
