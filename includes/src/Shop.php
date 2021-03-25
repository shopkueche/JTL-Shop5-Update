<?php

namespace JTL;

use Exception;
use JTL\Alert\Alert;
use JTL\Backend\AdminAccount;
use JTL\Backend\AdminLoginConfig;
use JTL\Boxes\Factory as BoxFactory;
use JTL\Boxes\FactoryInterface as BoxFactoryInterface;
use JTL\Boxes\Renderer\DefaultRenderer;
use JTL\Cache\JTLCache;
use JTL\Cache\JTLCacheInterface;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\Consent\Manager;
use JTL\Consent\ManagerInterface;
use JTL\Cron\Admin\Controller as CronController;
use JTL\Cron\Starter\StarterFactory;
use JTL\DB\DbInterface;
use JTL\DB\NiceDB;
use JTL\DB\ReturnType;
use JTL\DB\Services\GcService;
use JTL\DB\Services\GcServiceInterface;
use JTL\Debug\JTLDebugBar;
use JTL\Events\Dispatcher;
use JTL\Events\Event;
use JTL\Filesystem\AdapterFactory;
use JTL\Filesystem\Filesystem;
use JTL\Filter\Config;
use JTL\Filter\FilterInterface;
use JTL\Filter\ProductFilter;
use JTL\Helpers\Form;
use JTL\Helpers\PHPSettings;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\L10n\GetText;
use JTL\Language\LanguageHelper;
use JTL\Mail\Hydrator\DefaultsHydrator;
use JTL\Mail\Mailer;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Validator\MailValidator;
use JTL\Mapper\AdminLoginStatusMessageMapper;
use JTL\Mapper\AdminLoginStatusToLogLevel;
use JTL\Mapper\PageTypeToPageName;
use JTL\Media\Media;
use JTL\Network\JTLApi;
use JTL\OPC\DB;
use JTL\OPC\Locker;
use JTL\OPC\PageDB;
use JTL\OPC\PageService;
use JTL\OPC\Service as OPCService;
use JTL\Optin\Optin;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\LegacyPluginLoader;
use JTL\Plugin\PluginLoader;
use JTL\Plugin\State;
use JTL\ProcessingHandler\NiceDBHandler;
use JTL\Services\Container;
use JTL\Services\DefaultServicesInterface;
use JTL\Services\JTL\AlertService;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Services\JTL\BoxService;
use JTL\Services\JTL\BoxServiceInterface;
use JTL\Services\JTL\CaptchaService;
use JTL\Services\JTL\CaptchaServiceInterface;
use JTL\Services\JTL\CountryService;
use JTL\Services\JTL\CountryServiceInterface;
use JTL\Services\JTL\CryptoService;
use JTL\Services\JTL\CryptoServiceInterface;
use JTL\Services\JTL\LinkService;
use JTL\Services\JTL\LinkServiceInterface;
use JTL\Services\JTL\NewsService;
use JTL\Services\JTL\NewsServiceInterface;
use JTL\Services\JTL\PasswordService;
use JTL\Services\JTL\PasswordServiceInterface;
use JTL\Services\JTL\SimpleCaptchaService;
use JTL\Services\JTL\Validation\RuleSet;
use JTL\Services\JTL\Validation\ValidationService;
use JTL\Services\JTL\Validation\ValidationServiceInterface;
use JTL\Session\Frontend;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use JTL\Smarty\MailSmarty;
use JTL\Template\TemplateService;
use JTL\Template\TemplateServiceInterface;
use JTLShop\SemVer\Version;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;
use stdClass;
use function Functional\first;
use function Functional\map;
use function Functional\tail;

/**
 * Class Shop
 * @package JTL
 * @method static JTLCacheInterface Cache()
 * @method static LanguageHelper Lang()
 * @method static Smarty\JTLSmarty Smarty(bool $fast_init = false, string $context = ContextType::FRONTEND)
 * @method static Media Media()
 * @method static Events\Dispatcher Event()
 * @method static bool has(string $key)
 * @method static Shop set(string $key, mixed $value)
 * @method static null|mixed get($key)
 */
final class Shop
{
    /**
     * @var int
     */
    public static $kSprache;

    /**
     * @var string
     */
    public static $cISO;

    /**
     * @var int
     */
    public static $kKonfigPos;

    /**
     * @var int
     */
    public static $kKategorie;

    /**
     * @var int
     */
    public static $kArtikel;

    /**
     * @var int
     */
    public static $kVariKindArtikel;

    /**
     * @var int
     */
    public static $kSeite;

    /**
     * @var int
     */
    public static $kLink;

    /**
     * @var int
     */
    public static $nLinkart;

    /**
     * @var int
     */
    public static $kHersteller;

    /**
     * @var int
     */
    public static $kSuchanfrage;

    /**
     * @var int
     */
    public static $kMerkmalWert;

    /**
     * @var int
     */
    public static $kSuchspecial;

    /**
     * @var int
     */
    public static $kNews;

    /**
     * @var int
     */
    public static $kNewsMonatsUebersicht;

    /**
     * @var int
     */
    public static $kNewsKategorie;

    /**
     * @var int
     */
    public static $nBewertungSterneFilter;

    /**
     * @var string
     */
    public static $cPreisspannenFilter;

    /**
     * @var int
     */
    public static $kHerstellerFilter;

    /**
     * @var array
     */
    public static $manufacturerFilterIDs;

    /**
     * @var array
     */
    public static $categoryFilterIDs;

    /**
     * @var int
     */
    public static $kKategorieFilter;

    /**
     * @var int
     */
    public static $kSuchspecialFilter;

    /**
     * @var array
     */
    public static $searchSpecialFilterIDs;

    /**
     * @var int
     */
    public static $kSuchFilter;

    /**
     * @var int
     */
    public static $nDarstellung;

    /**
     * @var int
     */
    public static $nSortierung;

    /**
     * @var int
     */
    public static $nSort;

    /**
     * @var int
     */
    public static $show;

    /**
     * @var int
     */
    public static $vergleichsliste;

    /**
     * @var bool
     */
    public static $bFileNotFound;

    /**
     * @var string
     */
    public static $cCanonicalURL;

    /**
     * @var bool
     */
    public static $is404;

    /**
     * @var array
     */
    public static $MerkmalFilter;

    /**
     * @var array
     */
    public static $SuchFilter;

    /**
     * @var int
     */
    public static $kWunschliste;

    /**
     * @var bool
     */
    public static $bSEOMerkmalNotFound;

    /**
     * @var bool
     */
    public static $bKatFilterNotFound;

    /**
     * @var bool
     */
    public static $bHerstellerFilterNotFound;

    /**
     * @var bool
     * @deprecated since 5.0.0
     */
    public static $isSeoMainword = false;

    /**
     * @var null|Shop
     */
    private static $instance;

    /**
     * @var ProductFilter
     */
    public static $productFilter;

    /**
     * @var string
     */
    public static $fileName;

    /**
     * @var string
     */
    public static $AktuelleSeite;

    /**
     * @var int
     */
    public static $pageType;

    /**
     * @var bool
     */
    public static $directEntry = true;

    /**
     * @var bool
     */
    public static $bSeo = false;

    /**
     * @var bool
     */
    public static $isInitialized = false;

    /**
     * @var int
     */
    public static $nArtikelProSeite;

    /**
     * @var string
     */
    public static $cSuche;

    /**
     * @var
     */
    public static $seite;

    /**
     * @var int
     */
    public static $nSterne;

    /**
     * @var int
     */
    public static $nNewsKat;

    /**
     * @var string
     */
    public static $cDatum;

    /**
     * @var int
     */
    public static $nAnzahl;

    /**
     * @var string
     */
    public static $uri;

    /**
     * @var array
     */
    private $registry = [];

    /**
     * @var null|bool
     */
    private static $logged;

    /**
     * @var null|bool
     */
    private static $adminToken;

    /**
     * @var null|string
     */
    private static $adminLangTag;

    /**
     * @var array
     */
    private static $url = [];

    /**
     * @var Shopsetting
     */
    private static $settings;

    /**
     * @var FilterInterface[]
     */
    public static $customFilters = [];

    /**
     * @var DefaultServicesInterface
     */
    private static $container;

    /**
     * @var string
     */
    private static $imageBaseURL;

    /**
     * @var string
     */
    private static $optinCode;

    /**
     * @var bool
     */
    private static $isFrontend = true;

    /**
     * @var array
     */
    private static $mapping = [
        'DB'     => '_DB',
        'Cache'  => '_Cache',
        'Lang'   => '_Language',
        'Smarty' => '_Smarty',
        'Media'  => '_Media',
        'Event'  => '_Event',
        'has'    => '_has',
        'set'    => '_set',
        'get'    => '_get'
    ];

    /**
     *
     */
    private function __construct()
    {
        self::$instance = $this;
        self::$settings = Shopsetting::getInstance();
    }

    /**
     * @return Shop
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self();
    }

    /**
     * object wrapper - this allows to call NiceDB->query() etc.
     *
     * @param string $method
     * @param mixed  $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return ($mapping = self::map($method)) !== null
            ? \call_user_func_array([$this, $mapping], $arguments)
            : null;
    }

    /**
     * static wrapper - this allows to call Shop::Container()->getDB()->query() etc.
     *
     * @param string $method
     * @param mixed  $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return ($mapping = self::map($method)) !== null
            ? \call_user_func_array([self::getInstance(), $mapping], $arguments)
            : null;
    }

    /**
     * @param string $key
     * @return null|mixed
     */
    public function _get($key)
    {
        return $this->registry[$key] ?? null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function _set($key, $value): self
    {
        $this->registry[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function _has($key): bool
    {
        return isset($this->registry[$key]);
    }

    /**
     * map function calls to real functions
     *
     * @param string $method
     * @return string|null
     */
    private static function map($method): ?string
    {
        return self::$mapping[$method] ?? null;
    }

    /**
     * @param string $url
     */
    public static function setImageBaseURL(string $url): void
    {
        self::$imageBaseURL = \rtrim($url, '/') . '/';
    }

    /**
     * @return string
     */
    public static function getImageBaseURL(): string
    {
        if (self::$imageBaseURL === null) {
            self::setImageBaseURL(\defined('IMAGE_BASE_URL') ? \IMAGE_BASE_URL : self::getURL());
        }

        return self::$imageBaseURL;
    }

    /**
     * get remote service instance
     *
     * @return JTLApi
     * @deprecated since 5.0.0 use Shop::Container()->get(JTLApi::class) instead
     */
    public function RS(): JTLApi
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return self::Container()->get(JTLApi::class);
    }

    /**
     * get session instance
     *
     * @return Frontend
     * @throws Exception
     * @deprecated since 5.0.0
     */
    public function Session(): Frontend
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Frontend::getInstance();
    }

    /**
     * get db adapter instance
     *
     * @return DbInterface
     * @deprecated since 5.0.0 - use Shop::Container()->getDB() instead
     */
    public function _DB(): DbInterface
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return self::Container()->getDB();
    }

    /**
     * @return DbInterface
     * @deprecated since 5.0.0 - use Shop::Container()->getDB() instead
     */
    public static function DB(): DbInterface
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return self::Container()->getDB();
    }

    /**
     * get language instance
     *
     * @return LanguageHelper
     */
    public function _Language(): LanguageHelper
    {
        return LanguageHelper::getInstance();
    }

    /**
     * get config
     *
     * @return Shopsetting
     * @deprecated since 5.0.0
     */
    public function Config(): Shopsetting
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return self::$settings;
    }

    /**
     * get garbage collector
     *
     * @return GcServiceInterface
     * @deprecated since 5.0.0 -> use Shop::Container()->getGc() instead
     */
    public function Gc(): GcServiceInterface
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return static::Container()->getDBServiceGC();
    }

    /**
     * get logger
     *
     * @return Jtllog
     * @deprecated since 5.0.0
     */
    public function Logger(): Jtllog
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return new Jtllog();
    }

    /**
     * @return PHPSettings
     * @deprecated since 5.0.0
     */
    public function PHPSettingsHelper(): PHPSettings
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return PHPSettings::getInstance();
    }

    /**
     * get cache instance
     *
     * @return JTLCacheInterface
     * @deprecated since 5.0.0
     */
    public function _Cache(): JTLCacheInterface
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return self::Container()->getCache();
    }

    /**
     * @param bool        $fast
     * @param string|null $context
     * @return JTLSmarty
     */
    public function _Smarty(bool $fast = false, string $context = null): JTLSmarty
    {
        if ($context === null) {
            $context = self::isFrontend() ? ContextType::FRONTEND : ContextType::BACKEND;
        }
        return JTLSmarty::getInstance($fast, $context);
    }

    /**
     * get media instance
     *
     * @return Media
     * @deprecated since 5.0.0
     */
    public function _Media(): Media
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Media::getInstance();
    }

    /**
     * get event instance
     *
     * @return Dispatcher
     * @deprecated since 5.0.0
     */
    public function _Event(): Dispatcher
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Dispatcher::getInstance();
    }

    /**
     * @param string       $eventName
     * @param array|object $arguments
     */
    public static function fire(string $eventName, $arguments = []): void
    {
        Dispatcher::getInstance()->fire($eventName, $arguments);
    }

    /**
     * quick&dirty debugging
     *
     * @param mixed       $var          - the variable to debug
     * @param bool        $die          - set true to die() afterwards
     * @param null|string $beforeString - a prefix string
     * @param int         $backtrace    - backtrace depth
     */
    public static function dbg($var, bool $die = false, $beforeString = null, int $backtrace = 0): void
    {
        $nl     = \PHP_SAPI === 'cli' ? \PHP_EOL : '<br>';
        $trace  = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, $backtrace);
        $callee = first($trace);
        $info   = \pathinfo($callee['file']);
        echo $info['basename'] . ':' . $callee['line'] . ' ';
        if ($beforeString !== null) {
            echo $beforeString . $nl;
        }
        if (\PHP_SAPI !== 'cli') {
            echo '<pre>';
        }
        \var_dump($var);
        if ($backtrace > 0) {
            echo $nl . 'Backtrace:' . $nl;
            \var_dump(tail($trace));
        }
        if (\PHP_SAPI !== 'cli') {
            echo '</pre>';
        }
        if ($die === true) {
            die();
        }
    }

    /**
     * get current language/language ISO
     *
     * @var bool $iso
     * @return int|string
     */
    public static function getLanguage(bool $iso = false)
    {
        return $iso === false ? (int)self::$kSprache : self::$cISO;
    }

    /**
     * get current language/language ISO
     *
     * @var bool $iso
     * @return int
     */
    public static function getLanguageID(): int
    {
        return (int)self::$kSprache;
    }

    /**
     * get current language/language ISO
     *
     * @var bool $iso
     * @return string|null
     */
    public static function getLanguageCode(): ?string
    {
        return self::$cISO;
    }

    /**
     * set language/language ISO
     *
     * @param int         $languageID
     * @param string|null $iso
     */
    public static function setLanguage(int $languageID, string $iso = null): void
    {
        self::$kSprache = $languageID;
        if ($iso !== null) {
            self::$cISO = $iso;
        }
    }

    /**
     * @param array $config
     * @return array
     */
    public static function getConfig($config): array
    {
        return self::getSettings($config);
    }

    /**
     * @param array|int $config
     * @return array
     */
    public static function getSettings($config): array
    {
        return (self::$settings ?? Shopsetting::getInstance())->getSettings($config);
    }

    /**
     * @param int    $section
     * @param string $option
     * @return string|array|int|null
     */
    public static function getSettingValue(int $section, $option)
    {
        return self::getConfigValue($section, $option);
    }

    /**
     * @param int    $section
     * @param string $option
     * @return string|array|int|null
     */
    public static function getConfigValue(int $section, $option)
    {
        return (self::$settings ?? Shopsetting::getInstance())->getValue($section, $option);
    }

    /**
     * Load plugin event driven system
     * @param bool $isFrontend
     */
    public static function bootstrap(bool $isFrontend = true): void
    {
        self::$isFrontend = $isFrontend;
        if (\SAFE_MODE === true) {
            return;
        }
        $db      = self::Container()->getDB();
        $cache   = self::Container()->getCache();
        $cacheID = 'plgnbtsrp';
        if (($plugins = $cache->get($cacheID)) === false) {
            $plugins = map($db->queryPrepared(
                'SELECT kPlugin, bBootstrap, bExtension
                    FROM tplugin
                    WHERE nStatus = :state
                      AND bBootstrap = 1
                    ORDER BY nPrio ASC',
                ['state' => State::ACTIVATED],
                ReturnType::ARRAY_OF_OBJECTS
            ) ?: [], static function ($e) {
                $e->kPlugin    = (int)$e->kPlugin;
                $e->bBootstrap = (int)$e->bBootstrap;
                $e->bExtension = (int)$e->bExtension;

                return $e;
            });
            $cache->set($cacheID, $plugins, [\CACHING_GROUP_PLUGIN]);
        }
        $dispatcher      = Dispatcher::getInstance();
        $extensionLoader = new PluginLoader($db, $cache);
        $pluginLoader    = new LegacyPluginLoader($db, $cache);
        foreach ($plugins as $plugin) {
            $loader = $plugin->bExtension === 1 ? $extensionLoader : $pluginLoader;
            if (($p = PluginHelper::bootstrap($plugin->kPlugin, $loader)) !== null) {
                $p->boot($dispatcher);
                $p->loaded();
            }
        }
    }

    /**
     * @return bool
     */
    public static function isFrontend(): bool
    {
        return self::$isFrontend === true;
    }

    /**
     * @param bool $isFrontend
     */
    public static function setIsFrontend(bool $isFrontend): void
    {
        self::$isFrontend = $isFrontend;
    }

    /**
     * @return ProductFilter
     */
    public static function run(): ProductFilter
    {
        if (Request::postVar('action') === 'updateconsent' && Form::validateToken()) {
            $manager = new Manager(self::Container()->getDB());
            die(\json_encode((object)['status' => 'OK', 'data' => $manager->save(Request::postVar('data'))]));
        }
        self::$kKonfigPos             = Request::verifyGPCDataInt('ek');
        self::$kKategorie             = Request::verifyGPCDataInt('k');
        self::$kArtikel               = Request::verifyGPCDataInt('a');
        self::$kVariKindArtikel       = Request::verifyGPCDataInt('a2');
        self::$kSeite                 = Request::verifyGPCDataInt('s');
        self::$kLink                  = Request::verifyGPCDataInt('s');
        self::$kHersteller            = Request::verifyGPCDataInt('h');
        self::$kSuchanfrage           = Request::verifyGPCDataInt('l');
        self::$kMerkmalWert           = Request::verifyGPCDataInt('m');
        self::$kSuchspecial           = Request::verifyGPCDataInt('q');
        self::$kNews                  = Request::verifyGPCDataInt('n');
        self::$kNewsMonatsUebersicht  = Request::verifyGPCDataInt('nm');
        self::$kNewsKategorie         = Request::verifyGPCDataInt('nk');
        self::$nBewertungSterneFilter = Request::verifyGPCDataInt('bf');
        self::$cPreisspannenFilter    = Request::verifyGPDataString('pf');
        self::$manufacturerFilterIDs  = Request::verifyGPDataIntegerArray('hf');
        self::$kHerstellerFilter      = \count(self::$manufacturerFilterIDs) > 0
            ? self::$manufacturerFilterIDs[0]
            : 0;
        self::$categoryFilterIDs      = Request::verifyGPDataIntegerArray('kf');
        self::$kKategorieFilter       = \count(self::$categoryFilterIDs) > 0
            ? self::$categoryFilterIDs[0]
            : 0;
        self::$searchSpecialFilterIDs = Request::verifyGPDataIntegerArray('qf');
        self::$kSuchFilter            = Request::verifyGPCDataInt('sf');
        self::$kSuchspecialFilter     = \count(self::$searchSpecialFilterIDs) > 0
            ? self::$searchSpecialFilterIDs[0]
            : 0;

        self::$nDarstellung = Request::verifyGPCDataInt('ed');
        self::$nSortierung  = Request::verifyGPCDataInt('sortierreihenfolge');
        self::$nSort        = Request::verifyGPCDataInt('Sortierung');

        self::$show            = Request::verifyGPCDataInt('show');
        self::$vergleichsliste = Request::verifyGPCDataInt('vla');
        self::$bFileNotFound   = false;
        self::$cCanonicalURL   = '';
        self::$is404           = false;
        self::$nLinkart        = 0;

        self::$nSterne = Request::verifyGPCDataInt('nSterne');

        self::$kWunschliste = Wishlist::checkeParameters();

        self::$nNewsKat = Request::verifyGPCDataInt('nNewsKat');
        self::$cDatum   = Request::verifyGPDataString('cDatum');
        self::$nAnzahl  = Request::verifyGPCDataInt('nAnzahl');

        self::$optinCode = Request::verifyGPDataString('oc');

        if (Request::verifyGPDataString('qs') !== '') {
            self::$cSuche = Text::xssClean(Request::verifyGPDataString('qs'));
        } elseif (Request::verifyGPDataString('suchausdruck') !== '') {
            self::$cSuche = Text::xssClean(Request::verifyGPDataString('suchausdruck'));
        } else {
            self::$cSuche = Text::xssClean(Request::verifyGPDataString('suche'));
        }

        self::$nArtikelProSeite = Request::verifyGPCDataInt('af');
        if (self::$nArtikelProSeite !== 0) {
            $_SESSION['ArtikelProSeite'] = self::$nArtikelProSeite;
        }

        self::$isInitialized = true;
        $redirect            = Request::verifyGPDataString('r');
        if (self::$kArtikel > 0) {
            if (!empty($redirect)
                && (self::$kNews > 0 // get param "n" was used a article amount
                    || (isset($_GET['n']) && (float)$_GET['n'] > 0)) // article amount was a float >0 and <1
            ) {
                // GET param "n" is often misused as "amount of article"
                self::$kNews = 0;
                if ((int)$redirect === \R_LOGIN_WUNSCHLISTE) {
                    // login redirect on wishlist add when not logged in uses get param "n" as amount
                    // and "a" for the article ID - but we want to go to the login page, not to the article page
                    self::$kArtikel = 0;
                }
            } elseif (((int)$redirect === \R_LOGIN_BEWERTUNG || (int)$redirect === \R_LOGIN_TAG)
                && empty($_SESSION['Kunde']->kKunde)
            ) {
                // avoid redirect to article page for ratings that require logged in customers
                self::$kArtikel = 0;
            }
        }
        if (self::$kWunschliste === 0
            && Request::verifyGPDataString('error') === ''
            && \mb_strlen(Request::verifyGPDataString('wlid')) > 0
        ) {
            \header(
                'Location: ' . LinkService::getInstance()->getStaticRoute('wunschliste.php') .
                '?wlid=' . Text::filterXSS(Request::verifyGPDataString('wlid')) . '&error=1',
                true,
                303
            );
            exit();
        }
        self::Container()->get(ManagerInterface::class)->initActiveItems(self::$kSprache);
        $conf = new Config();
        $conf->setLanguageID(self::$kSprache);
        $conf->setLanguages(self::Lang()->getLangArray());
        $conf->setCustomerGroupID(Frontend::getCustomerGroup()->getID());
        $conf->setConfig(self::$settings->getAll());
        $conf->setBaseURL(self::getURL() . '/');
        self::$productFilter = new ProductFilter($conf, self::Container()->getDB(), self::Container()->getCache());
        self::seoCheck();

        if ((self::$kArtikel > 0 || self::$kKategorie > 0)
            && !Frontend::getCustomerGroup()->mayViewCategories()
        ) {
            // falls Artikel/Kategorien nicht gesehen werden duerfen -> login
            \header('Location: ' . LinkService::getInstance()->getStaticRoute('jtl.php') . '?li=1', true, 303);
            exit;
        }

        self::setImageBaseURL(\defined('IMAGE_BASE_URL') ? \IMAGE_BASE_URL : self::getURL());
        Dispatcher::getInstance()->fire(Event::RUN);

        self::$productFilter->initStates(self::getParameters());
        $starterFactory = new StarterFactory(self::getConfig([\CONF_CRON])['cron'] ?? []);
        $starterFactory->getStarter()->start();

        return self::$productFilter;
    }

    /**
     * get page parameters
     *
     * @return array
     */
    public static function getParameters(): array
    {
        if (self::$kKategorie > 0
            && !Kategorie::isVisible(self::$kKategorie, Frontend::getCustomerGroup()->getID())
        ) {
            self::$kKategorie = 0;
        }
        if (Product::isVariChild(self::$kArtikel)) {
            self::$kVariKindArtikel = self::$kArtikel;
            self::$kArtikel         = Product::getParent(self::$kArtikel);
        }

        return [
            'kKategorie'             => self::$kKategorie,
            'kKonfigPos'             => self::$kKonfigPos,
            'kHersteller'            => self::$kHersteller,
            'kArtikel'               => self::$kArtikel,
            'kVariKindArtikel'       => self::$kVariKindArtikel,
            'kSeite'                 => self::$kSeite,
            'kLink'                  => self::$kLink,
            'nLinkart'               => self::$nLinkart,
            'kSuchanfrage'           => self::$kSuchanfrage,
            'kMerkmalWert'           => self::$kMerkmalWert,
            'kSuchspecial'           => self::$kSuchspecial,
            'kNews'                  => self::$kNews,
            'kNewsMonatsUebersicht'  => self::$kNewsMonatsUebersicht,
            'kNewsKategorie'         => self::$kNewsKategorie,
            'kKategorieFilter'       => self::$kKategorieFilter,
            'kHerstellerFilter'      => self::$kHerstellerFilter,
            'nBewertungSterneFilter' => self::$nBewertungSterneFilter,
            'cPreisspannenFilter'    => self::$cPreisspannenFilter,
            'kSuchspecialFilter'     => self::$kSuchspecialFilter,
            'nSortierung'            => self::$nSortierung,
            'nSort'                  => self::$nSort,
            'MerkmalFilter_arr'      => self::$MerkmalFilter,
            'SuchFilter_arr'         => self::$SuchFilter ?? [],
            'nArtikelProSeite'       => self::$nArtikelProSeite,
            'cSuche'                 => self::$cSuche,
            'seite'                  => self::$seite,
            'show'                   => self::$show,
            'is404'                  => self::$is404,
            'kSuchFilter'            => self::$kSuchFilter,
            'kWunschliste'           => self::$kWunschliste,
            'MerkmalFilter'          => self::$MerkmalFilter,
            'SuchFilter'             => self::$SuchFilter,
            'vergleichsliste'        => self::$vergleichsliste,
            'nDarstellung'           => self::$nDarstellung,
            'isSeoMainword'          => false,
            'nNewsKat'               => self::$nNewsKat,
            'cDatum'                 => self::$cDatum,
            'nAnzahl'                => self::$nAnzahl,
            'nSterne'                => self::$nSterne,
            'customFilters'          => self::$customFilters,
            'searchSpecialFilters'   => self::$searchSpecialFilterIDs,
            'manufacturerFilters'    => self::$manufacturerFilterIDs,
            'categoryFilters'        => self::$categoryFilterIDs
        ];
    }

    private static function getLanguageFromServerName(): void
    {
        if (\EXPERIMENTAL_MULTILANG_SHOP !== true) {
            return;
        }
        foreach ($_SESSION['Sprachen'] ?? [] as $language) {
            $code    = \mb_convert_case($language->getCode(), \MB_CASE_UPPER);
            $shopURL = \defined('URL_SHOP_' . $code) ? \constant('URL_SHOP_' . $code) : \URL_SHOP;
            if ($_SERVER['HTTP_HOST'] === \parse_url($shopURL)['host']) {
                self::setLanguage($language->getId(), $language->getCode());
                break;
            }
        }
    }

    /**
     * check for seo url
     */
    public static function seoCheck(): void
    {
        self::getLanguageFromServerName();
        $uri                             = $_SERVER['HTTP_X_REWRITE_URL'] ?? $_SERVER['REQUEST_URI'] ?? '';
        self::$uri                       = $uri;
        self::$bSEOMerkmalNotFound       = false;
        self::$bKatFilterNotFound        = false;
        self::$bHerstellerFilterNotFound = false;
        \executeHook(\HOOK_SEOCHECK_ANFANG, ['uri' => &$uri]);
        $seite       = 0;
        $manufSeo    = [];
        $categorySeo = '';
        $customSeo   = [];
        $shopURLdata = \parse_url(self::getURL());
        $baseURLdata = \parse_url($uri);
        $seo         = isset($baseURLdata['path'])
            ? \mb_substr($baseURLdata['path'], isset($shopURLdata['path'])
                ? (\mb_strlen($shopURLdata['path']) + 1)
                : 1)
            : false;
        $seo         = Request::extractExternalParams($seo);
        if ($seo) {
            foreach (self::$productFilter->getCustomFilters() as $customFilter) {
                $seoParam = $customFilter->getUrlParamSEO();
                if (empty($seoParam)) {
                    continue;
                }
                $customFilterArr = \explode($seoParam, $seo);
                if (\count($customFilterArr) > 1) {
                    [$seo, $customFilterSeo] = $customFilterArr;
                    if (\mb_strpos($customFilterSeo, \SEP_HST) !== false) {
                        $arr             = \explode(\SEP_HST, $customFilterSeo);
                        $customFilterSeo = $arr[0];
                        $seo            .= \SEP_HST . $arr[1];
                    }
                    if (($idx = \mb_strpos($customFilterSeo, \SEP_KAT)) !== false
                        && $idx !== \mb_strpos($customFilterSeo, \SEP_HST)
                    ) {
                        $manufacturers   = \explode(\SEP_KAT, $customFilterSeo);
                        $customFilterSeo = $manufacturers[0];
                        $seo            .= \SEP_KAT . $manufacturers[1];
                    }
                    if (\mb_strpos($customFilterSeo, \SEP_MERKMAL) !== false) {
                        $arr             = \explode(\SEP_MERKMAL, $customFilterSeo);
                        $customFilterSeo = $arr[0];
                        $seo            .= \SEP_MERKMAL . $arr[1];
                    }
                    if (\mb_strpos($customFilterSeo, \SEP_MM_MMW) !== false) {
                        $arr             = \explode(\SEP_MM_MMW, $customFilterSeo);
                        $customFilterSeo = $arr[0];
                        $seo            .= \SEP_MM_MMW . $arr[1];
                    }
                    if (\mb_strpos($customFilterSeo, \SEP_SEITE) !== false) {
                        $arr             = \explode(\SEP_SEITE, $customFilterSeo);
                        $customFilterSeo = $arr[0];
                        $seo            .= \SEP_SEITE . $arr[1];
                    }

                    $customSeo[$customFilter->getClassName()] = [
                        'cSeo'  => $customFilterSeo,
                        'table' => $customFilter->getTableName()
                    ];
                }
            }
            // change Opera Fix
            if (\mb_substr($seo, \mb_strlen($seo) - 1, 1) === '?') {
                $seo = \mb_substr($seo, 0, -1);
            }
            $nMatch = \preg_match('/[^_](' . \SEP_SEITE . '([0-9]+))/', $seo, $matches, \PREG_OFFSET_CAPTURE);
            if ($nMatch === 1) {
                $seite = (int)$matches[2][0];
                $seo   = \mb_substr($seo, 0, $matches[1][1]);
            }
            // duplicate content work around
            if ($seite === 1 && \mb_strlen($seo) > 0) {
                \http_response_code(301);
                \header('Location: ' . self::getURL() . '/' . $seo);
                exit();
            }
            $seoAttributes = \explode(\SEP_MERKMAL, $seo);
            $seo           = $seoAttributes[0];
            foreach ($seoAttributes as $i => &$merkmal) {
                if ($i === 0) {
                    continue;
                }
                if (($idx = \mb_strpos($merkmal, \SEP_KAT)) !== false && $idx !== \mb_strpos($merkmal, \SEP_HST)) {
                    $arr     = \explode(\SEP_KAT, $merkmal);
                    $merkmal = $arr[0];
                    $seo    .= \SEP_KAT . $arr[1];
                }
                if (\mb_strpos($merkmal, \SEP_HST) !== false) {
                    $arr     = \explode(\SEP_HST, $merkmal);
                    $merkmal = $arr[0];
                    $seo    .= \SEP_HST . $arr[1];
                }
                if (\mb_strpos($merkmal, \SEP_MM_MMW) !== false) {
                    $arr     = \explode(\SEP_MM_MMW, $merkmal);
                    $merkmal = $arr[0];
                    $seo    .= \SEP_MM_MMW . $arr[1];
                }
                if (\mb_strpos($merkmal, \SEP_SEITE) !== false) {
                    $arr     = \explode(\SEP_SEITE, $merkmal);
                    $merkmal = $arr[0];
                    $seo    .= \SEP_SEITE . $arr[1];
                }
            }
            unset($merkmal);
            $manufacturers = \explode(\SEP_HST, $seo);
            if (\is_array($manufacturers) && \count($manufacturers) > 1) {
                foreach ($manufacturers as $i => $manufacturer) {
                    if ($i === 0) {
                        $seo = $manufacturer;
                    } else {
                        $manufSeo[] = $manufacturer;
                    }
                }
                foreach ($manufSeo as $i => $hstseo) {
                    if (($idx = \mb_strpos($hstseo, \SEP_KAT)) !== false && $idx !== \mb_strpos($hstseo, \SEP_HST)) {
                        $manufacturers[] = \explode(\SEP_KAT, $hstseo);
                        $manufSeo[$i]    = $manufacturers[0];
                        $seo            .= \SEP_KAT . $manufacturers[1];
                    }
                    if (\mb_strpos($hstseo, \SEP_MERKMAL) !== false) {
                        $arr          = \explode(\SEP_MERKMAL, $hstseo);
                        $manufSeo[$i] = $arr[0];
                        $seo         .= \SEP_MERKMAL . $arr[1];
                    }
                    if (\mb_strpos($hstseo, \SEP_MM_MMW) !== false) {
                        $arr          = \explode(\SEP_MM_MMW, $hstseo);
                        $manufSeo[$i] = $arr[0];
                        $seo         .= \SEP_MM_MMW . $arr[1];
                    }
                    if (\mb_strpos($hstseo, \SEP_SEITE) !== false) {
                        $arr          = \explode(\SEP_SEITE, $hstseo);
                        $manufSeo[$i] = $arr[0];
                        $seo         .= \SEP_SEITE . $arr[1];
                    }
                }
            } else {
                $seo = $manufacturers[0];
            }
            $categories = \explode(\SEP_KAT, $seo);
            if (\is_array($categories) && \count($categories) > 1) {
                [$seo, $categorySeo] = $categories;
                if (\mb_strpos($categorySeo, \SEP_HST) !== false) {
                    $arr         = \explode(\SEP_HST, $categorySeo);
                    $categorySeo = $arr[0];
                    $seo        .= \SEP_HST . $arr[1];
                }
                if (\mb_strpos($categorySeo, \SEP_MERKMAL) !== false) {
                    $arr         = \explode(\SEP_MERKMAL, $categorySeo);
                    $categorySeo = $arr[0];
                    $seo        .= \SEP_MERKMAL . $arr[1];
                }
                if (\mb_strpos($categorySeo, \SEP_MM_MMW) !== false) {
                    $arr         = \explode(\SEP_MM_MMW, $categorySeo);
                    $categorySeo = $arr[0];
                    $seo        .= \SEP_MM_MMW . $arr[1];
                }
                if (\mb_strpos($categorySeo, \SEP_SEITE) !== false) {
                    $arr         = \explode(\SEP_SEITE, $categorySeo);
                    $categorySeo = $arr[0];
                    $seo        .= \SEP_SEITE . $arr[1];
                }
            } else {
                $seo = $categories[0];
            }
            if ($seite > 0) {
                $_GET['seite'] = $seite;
                self::$kSeite  = $seite;
            }
            // split attribute/attribute value
            $attributes = \explode(\SEP_MM_MMW, $seo);
            if (\is_array($attributes) && \count($attributes) > 1) {
                $seo = $attributes[1];
                //$mmseo = $oMerkmal_arr[0];
            }
            // custom filter
            foreach ($customSeo as $className => $data) {
                $seoData = self::Container()->getDB()->select($data['table'], 'cSeo', $data['cSeo']);
                if (isset($seoData->filterval)) {
                    self::$customFilters[$className] = (int)$seoData->filterval;
                } else {
                    self::$bKatFilterNotFound = true;
                }
                if (isset($seoData->kSprache) && $seoData->kSprache > 0) {
                    self::updateLanguage((int)$seoData->kSprache);
                }
            }
            // category filter
            if (\mb_strlen($categorySeo) > 0) {
                $seoData = self::Container()->getDB()->select('tseo', 'cKey', 'kKategorie', 'cSeo', $categorySeo);
                if (isset($seoData->kKey) && \strcasecmp($seoData->cSeo, $categorySeo) === 0) {
                    self::$kKategorieFilter = (int)$seoData->kKey;
                } else {
                    self::$bKatFilterNotFound = true;
                }
            }
            // manufacturer filter
            if (($seoCount = \count($manufSeo)) > 0) {
                if ($seoCount === 1) {
                    $oSeo = self::Container()->getDB()->selectAll(
                        'tseo',
                        ['cKey', 'cSeo'],
                        ['kHersteller', $manufSeo[0]],
                        'kKey'
                    );
                } else {
                    $bindValues = [];
                    // PDO::bindValue() is 1-based
                    foreach ($manufSeo as $i => $t) {
                        $bindValues[$i + 1] = $t;
                    }
                    $oSeo = self::Container()->getDB()->queryPrepared(
                        "SELECT kKey
                            FROM tseo
                            WHERE cKey = 'kHersteller'
                            AND cSeo IN (" . \implode(',', \array_fill(0, $seoCount, '?')) . ')',
                        $bindValues,
                        ReturnType::ARRAY_OF_OBJECTS
                    );
                }
                $results = \count($oSeo);
                if ($results === 1) {
                    self::$kHerstellerFilter = (int)$oSeo[0]->kKey;
                } elseif ($results === 0) {
                    self::$bHerstellerFilterNotFound = true;
                } else {
                    self::$kHerstellerFilter = \array_map(static function ($e) {
                        return (int)$e->kKey;
                    }, $oSeo);
                }
            }
            // attribute filter
            if (\count($seoAttributes) > 1) {
                if (!isset($_GET['mf'])) {
                    $_GET['mf'] = [];
                } elseif (!\is_array($_GET['mf'])) {
                    $_GET['mf'] = [(int)$_GET['mf']];
                }
                self::$bSEOMerkmalNotFound = false;
                foreach ($seoAttributes as $i => $seoString) {
                    if ($i > 0 && \mb_strlen($seoString) > 0) {
                        $seoData = self::Container()->getDB()->select(
                            'tseo',
                            'cKey',
                            'kMerkmalWert',
                            'cSeo',
                            $seoString
                        );
                        if (isset($seoData->kKey) && \strcasecmp($seoData->cSeo, $seoString) === 0) {
                            // haenge an GET, damit baueMerkmalFilter die Merkmalfilter setzen kann - @todo?
                            $_GET['mf'][] = (int)$seoData->kKey;
                        } else {
                            self::$bSEOMerkmalNotFound = true;
                        }
                    }
                }
            }
            if (\count($categories) > 1) {
                if (!isset($_GET['kf'])) {
                    $_GET['kf'] = [];
                } elseif (!\is_array($_GET['kf'])) {
                    $_GET['kf'] = [(int)$_GET['kf']];
                }
                self::$bSEOMerkmalNotFound = false;
                foreach ($categories as $i => $seoString) {
                    if ($i > 0 && \mb_strlen($seoString) > 0) {
                        $seoData = self::Container()->getDB()->select(
                            'tseo',
                            'cKey',
                            'kKategorie',
                            'cSeo',
                            $seoString
                        );
                        if (isset($seoData->kKey) && \strcasecmp($seoData->cSeo, $seoString) === 0) {
                            $_GET['kf'][] = (int)$seoData->kKey;
                        } else {
                            self::$bSEOMerkmalNotFound = true;
                        }
                    }
                }
            }
            $oSeo = self::Container()->getDB()->select('tseo', 'cSeo', $seo);
            // EXPERIMENTAL_MULTILANG_SHOP
            if (isset($oSeo->kSprache)
                && self::$kSprache !== (int)$oSeo->kSprache
                && \EXPERIMENTAL_MULTILANG_SHOP === true
            ) {
                if (\MULTILANG_URL_FALLBACK === true) {
                    $oSeo->kSprache = self::$kSprache;
                } else {
                    // slug language id and shop language id have to match - 404 otherwise
                    $oSeo = null;
                }
            }
            // EXPERIMENTAL_MULTILANG_SHOP END
            // Link active?
            if (isset($oSeo->cKey) && $oSeo->cKey === 'kLink') {
                $bIsActive = self::Container()->getDB()->select('tlink', 'kLink', (int)$oSeo->kKey);
                if ($bIsActive !== null && (int)$bIsActive->bIsActive === 0) {
                    $oSeo = false;
                }
            }
            // mainwords
            if (isset($oSeo->kKey) && \strcasecmp($oSeo->cSeo, $seo) === 0) {
                if ($seo !== $oSeo->cSeo) {
                    \http_response_code(301);
                    \header('Location: ' . self::getURL() . '/' . $oSeo->cSeo);
                    exit;
                }
                self::$cCanonicalURL = self::getURL() . '/' . $oSeo->cSeo;
                $oSeo->kKey          = (int)$oSeo->kKey;
                switch ($oSeo->cKey) {
                    case 'kKategorie':
                        self::$kKategorie = $oSeo->kKey;
                        break;

                    case 'kHersteller':
                        self::$kHersteller = $oSeo->kKey;
                        break;

                    case 'kArtikel':
                        self::$kArtikel = $oSeo->kKey;
                        break;

                    case 'kLink':
                        self::$kLink = $oSeo->kKey;
                        break;

                    case 'kSuchanfrage':
                        self::$kSuchanfrage = $oSeo->kKey;
                        break;

                    case 'kMerkmalWert':
                        self::$kMerkmalWert = $oSeo->kKey;
                        break;

                    case 'suchspecial':
                        self::$kSuchspecial = $oSeo->kKey;
                        break;

                    case 'kNews':
                        self::$kNews = $oSeo->kKey;
                        break;

                    case 'kNewsMonatsUebersicht':
                        self::$kNewsMonatsUebersicht = $oSeo->kKey;
                        break;

                    case 'kNewsKategorie':
                        self::$kNewsKategorie = $oSeo->kKey;
                        break;
                }
            }
            if (isset($oSeo->kSprache) && $oSeo->kSprache > 0) {
                self::updateLanguage((int)$oSeo->kSprache);
            }
        }
        self::$MerkmalFilter     = ProductFilter::initCharacteristicFilter();
        self::$SuchFilter        = ProductFilter::initSearchFilter();
        self::$categoryFilterIDs = ProductFilter::initCategoryFilter();

        \executeHook(\HOOK_SEOCHECK_ENDE);
    }

    /**
     * @param int $languageID
     */
    private static function updateLanguage(int $languageID): void
    {
        $iso = self::Lang()->getIsoFromLangID($languageID)->cISO ?? null;
        if ($iso !== $_SESSION['cISOSprache']) {
            Frontend::checkReset($iso);
            Tax::setTaxRates();
        }
        if (self::$productFilter->getFilterConfig()->getLanguageID() !== $languageID) {
            self::$productFilter->getFilterConfig()->setLanguageID($languageID);
            self::$productFilter->initBaseStates();
        }
        $customer     = Frontend::getCustomer();
        $customerLang = $customer->getLanguageID();
        if ($customerLang > 0 && $customerLang !== $languageID) {
            $customer->setLanguageID($languageID);
            $customer->updateInDB();
        }
    }

    /**
     * decide which page to load
     * @return string|null
     */
    public static function getEntryPoint(): ?string
    {
        self::setPageType(\PAGE_UNBEKANNT);
        if ((self::$kArtikel > 0 && !self::$kKategorie)
            || (self::$kArtikel > 0 && self::$kKategorie > 0 && self::$show === 1)
        ) {
            $parentID = Product::getParent(self::$kArtikel);
            if ($parentID > 0) {
                $productID = $parentID;
                // save data from child article POST and add to redirect
                $cRP = '';
                if (\is_array($_POST) && \count($_POST) > 0) {
                    foreach (\array_keys($_POST) as $key) {
                        $cRP .= '&' . $key . '=' . $_POST[$key];
                    }
                    // Redirect POST
                    $cRP = '&cRP=' . \base64_encode($cRP);
                }
                \http_response_code(301);
                \header('Location: ' . self::getURL() . '/?a=' . $productID . $cRP);
                exit();
            }

            self::setPageType(\PAGE_ARTIKEL);
            self::$fileName = 'artikel.php';
        } elseif ((self::$bSEOMerkmalNotFound === null || self::$bSEOMerkmalNotFound === false)
            && (self::$bKatFilterNotFound === null || self::$bKatFilterNotFound === false)
            && (self::$bHerstellerFilterNotFound === null || self::$bHerstellerFilterNotFound === false)
            && ((self::$kHersteller > 0
                    || self::$kSuchanfrage > 0
                    || self::$kMerkmalWert > 0
                    || self::$kKategorie > 0
                    || self::$nBewertungSterneFilter > 0
                    || self::$kHerstellerFilter > 0
                    || self::$kKategorieFilter > 0
                    || self::$kSuchspecial > 0
                    || self::$kSuchFilter > 0)
                || (self::$cPreisspannenFilter !== null && self::$cPreisspannenFilter > 0))
            && (self::$productFilter->getFilterCount() === 0 || !self::$bSeo)
        ) {
            self::$fileName = 'filter.php';
            self::setPageType(\PAGE_ARTIKELLISTE);
        } elseif (self::$kWunschliste > 0) {
            self::$fileName = 'wunschliste.php';
            self::setPageType(\PAGE_WUNSCHLISTE);
        } elseif (self::$vergleichsliste > 0) {
            self::$fileName = 'vergleichsliste.php';
            self::setPageType(\PAGE_VERGLEICHSLISTE);
        } elseif (self::$kNews > 0 || self::$kNewsMonatsUebersicht > 0 || self::$kNewsKategorie > 0) {
            self::$fileName = 'news.php';
            self::setPageType(\PAGE_NEWS);
        } elseif (!empty(self::$cSuche)) {
            self::$fileName = 'filter.php';
            self::setPageType(\PAGE_ARTIKELLISTE);
        } elseif (!self::$kLink) {
            //check path
            $path        = self::getRequestUri(true);
            $requestFile = '/' . \ltrim($path, '/');
            if ($requestFile === '/index.php') {
                // special case: /index.php shall be redirected to Shop-URL
                \header('Location: ' . self::getURL(), true, 301);
                exit;
            }
            if ($requestFile === '/') {
                // special case: home page is accessible without seo url
                $link = null;
                self::setPageType(\PAGE_STARTSEITE);
                self::$fileName = 'seite.php';
                if (Frontend::getCustomerGroup()->getID() > 0) {
                    $cKundengruppenSQL = " AND (FIND_IN_SET('" . Frontend::getCustomerGroup()->getID()
                        . "', REPLACE(cKundengruppen, ';', ',')) > 0
                        OR cKundengruppen IS NULL
                        OR cKundengruppen = 'NULL'
                        OR tlink.cKundengruppen = '')";
                    $link              = self::Container()->getDB()->query(
                        'SELECT kLink
                            FROM tlink
                            WHERE nLinkart = ' . \LINKTYP_STARTSEITE . $cKundengruppenSQL,
                        ReturnType::SINGLE_OBJECT
                    );
                }
                self::$kLink = isset($link->kLink)
                    ? (int)$link->kLink
                    : self::Container()->getLinkService()->getSpecialPageID(\LINKTYP_STARTSEITE);
            } elseif (Media::getInstance()->isValidRequest($path)) {
                Media::getInstance()->handleRequest($path);
            } else {
                self::$is404    = true;
                self::$fileName = null;
                self::setPageType(\PAGE_404);
            }
        } elseif (!empty(self::$kLink)) {
            $link = self::Container()->getLinkService()->getLinkByID(self::$kLink);
            if ($link !== null && ($linkType = $link->getLinkType()) > 0) {
                self::$nLinkart = $linkType;

                if ($linkType === \LINKTYP_EXTERNE_URL) {
                    \header('Location: ' . $link->getURL(), true, 303);
                    exit;
                }

                self::$fileName = 'seite.php';
                self::setPageType(\PAGE_EIGENE);

                if ($linkType === \LINKTYP_STARTSEITE) {
                    self::setPageType(\PAGE_STARTSEITE);
                } elseif ($linkType === \LINKTYP_DATENSCHUTZ) {
                    self::setPageType(\PAGE_DATENSCHUTZ);
                } elseif ($linkType === \LINKTYP_AGB) {
                    self::setPageType(\PAGE_AGB);
                } elseif ($linkType === \LINKTYP_WRB) {
                    self::setPageType(\PAGE_WRB);
                } elseif ($linkType === \LINKTYP_VERSAND) {
                    self::setPageType(\PAGE_VERSAND);
                } elseif ($linkType === \LINKTYP_LIVESUCHE) {
                    self::setPageType(\PAGE_LIVESUCHE);
                } elseif ($linkType === \LINKTYP_HERSTELLER) {
                    self::setPageType(\PAGE_HERSTELLER);
                } elseif ($linkType === \LINKTYP_NEWSLETTERARCHIV) {
                    self::setPageType(\PAGE_NEWSLETTERARCHIV);
                } elseif ($linkType === \LINKTYP_SITEMAP) {
                    self::setPageType(\PAGE_SITEMAP);
                } elseif ($linkType === \LINKTYP_GRATISGESCHENK) {
                    self::setPageType(\PAGE_GRATISGESCHENK);
                } elseif ($linkType === \LINKTYP_AUSWAHLASSISTENT) {
                    self::setPageType(\PAGE_AUSWAHLASSISTENT);
                } elseif ($linkType === \LINKTYP_404) {
                    self::setPageType(\PAGE_404);
                }
            }
            if ($link !== null && !empty($link->getFileName())) {
                self::$fileName = $link->getFileName();
                switch (self::$fileName) {
                    case 'news.php':
                        self::setPageType(\PAGE_NEWS);
                        break;
                    case 'jtl.php':
                        self::setPageType(\PAGE_MEINKONTO);
                        break;
                    case 'kontakt.php':
                        self::setPageType(\PAGE_KONTAKT);
                        break;
                    case 'newsletter.php':
                        self::setPageType(\PAGE_NEWSLETTER);
                        break;
                    case 'pass.php':
                        self::setPageType(\PAGE_PASSWORTVERGESSEN);
                        break;
                    case 'registrieren.php':
                        self::setPageType(\PAGE_REGISTRIERUNG);
                        break;
                    case 'warenkorb.php':
                        self::setPageType(\PAGE_WARENKORB);
                        break;
                    case 'wunschliste.php':
                        self::setPageType(\PAGE_WUNSCHLISTE);
                        break;
                    default:
                        break;
                }
            }
        } elseif (self::$fileName === null) {
            self::$fileName = 'seite.php';
            self::setPageType(\PAGE_EIGENE);
        }
        self::check404();

        if (\mb_strlen(self::$optinCode) > 8) {
            try {
                $successMsg = (new Optin())
                    ->setCode(self::$optinCode)
                    ->handleOptin();
                self::Container()->getAlertService()->addAlert(
                    Alert::TYPE_INFO,
                    self::Lang()->get($successMsg, 'messages'),
                    'optinSucceeded'
                );
            } catch (Exceptions\EmptyResultSetException $e) {
                self::Container()->getLogService()->notice($e->getMessage());
                self::Container()->getAlertService()->addAlert(
                    Alert::TYPE_ERROR,
                    self::Lang()->get('optinCodeUnknown', 'errorMessages'),
                    'optinCodeUnknown'
                );
            } catch (Exceptions\InvalidInputException $e) {
                self::Container()->getAlertService()->addAlert(
                    Alert::TYPE_ERROR,
                    self::Lang()->get('optinActionUnknown', 'errorMessages'),
                    'optinUnknownAction'
                );
            }
        }

        return self::$fileName;
    }

    /**
     * @return bool
     */
    public static function check404(): bool
    {
        if (self::$is404 !== true) {
            return false;
        }
        \executeHook(\HOOK_INDEX_SEO_404, ['seo' => self::getRequestUri()]);
        if (!self::$kLink) {
            $hookInfos     = Redirect::urlNotFoundRedirect([
                'key'   => 'kLink',
                'value' => self::$kLink
            ]);
            $kLink         = $hookInfos['value'];
            $bFileNotFound = $hookInfos['isFileNotFound'];
            if (!$kLink) {
                self::$kLink = self::Container()->getLinkService()->getSpecialPageID(\LINKTYP_404);
            }
        }

        return true;
    }

    /**
     * build navigation filter object from parameters
     *
     * @param array                     $params
     * @param object|null|ProductFilter $productFilter
     * @return ProductFilter
     * @deprecated since 5.0.0
     */
    public static function buildNaviFilter(array $params, $productFilter = null): ProductFilter
    {
        \trigger_error(
            __METHOD__ . ' is deprecated. Use ' . __CLASS__ . '::buildProductFilter() instead',
            \E_USER_DEPRECATED
        );

        return self::buildProductFilter($params, $productFilter);
    }

    /**
     * build product filter object from parameters
     *
     * @param array                       $params
     * @param stdClass|null|ProductFilter $productFilter
     * @param bool                        $validate
     * @return ProductFilter
     */
    public static function buildProductFilter(
        array $params,
        $productFilter = null,
        bool $validate = true
    ): ProductFilter {
        $pf = new ProductFilter(
            Config::getDefault(),
            self::Container()->getDB(),
            self::Container()->getCache()
        );
        if ($productFilter !== null) {
            foreach (\get_object_vars($productFilter) as $k => $v) {
                $pf->$k = $v;
            }
        }

        return $pf->initStates($params, $validate);
    }

    /**
     * @return ProductFilter
     * @deprecated since 5.0.0
     */
    public static function getNaviFilter(): ProductFilter
    {
        \trigger_error(
            __METHOD__ . 'is deprecated. Use ' . __CLASS__ . '::getProductFilter() instead',
            \E_USER_DEPRECATED
        );

        return self::getProductFilter();
    }

    /**
     * @return ProductFilter
     */
    public static function getProductFilter(): ProductFilter
    {
        if (self::$productFilter === null) {
            self::$productFilter = self::buildProductFilter([]);
        }

        return self::$productFilter;
    }

    /**
     * @param ProductFilter $productFilter
     */
    public static function setProductFilter(ProductFilter $productFilter): void
    {
        self::$productFilter = $productFilter;
    }

    /**
     * @param null|ProductFilter $productFilter
     * @deprecated since 5.0.0 - this is done in ProductFilter:validate()
     */
    public static function checkNaviFilter($productFilter = null): void
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
    }

    /**
     * @return Version
     */
    public static function getShopDatabaseVersion(): Version
    {
        $version = self::Container()->getDB()->query(
            'SELECT nVersion FROM tversion',
            ReturnType::SINGLE_OBJECT
        )->nVersion;

        if ($version === '5' || $version === 5) {
            $version = '5.0.0';
        }

        return Version::parse($version);
    }

    /**
     * Return version of files
     *
     * @deprecated since 5.0.0
     * @return string
     */
    public static function getVersion(): string
    {
        \trigger_error(
            __METHOD__ . ' is deprecated. Use ' . __CLASS__ . '::getApplicationVersion() instead',
            \E_USER_DEPRECATED
        );

        return self::getApplicationVersion();
    }

    /**
     * Return version of files
     *
     * @return string
     */
    public static function getApplicationVersion(): string
    {
        return \APPLICATION_VERSION;
    }

    /**
     * get logo from db, fallback to first file in logo dir
     *
     * @var bool $fullURL - prepend shop url if set to true
     * @return string|null - image path/null if no logo was found
     */
    public static function getLogo(bool $fullUrl = false): ?string
    {
        $ret  = null;
        $conf = self::getSettings([\CONF_LOGO]);
        $logo = $conf['logo']['shop_logo'] ?? null;
        if ($logo !== null && $logo !== '') {
            $ret = \PFAD_SHOPLOGO . $logo;
        } elseif (\is_dir(\PFAD_ROOT . \PFAD_SHOPLOGO)) {
            $dir = \opendir(\PFAD_ROOT . \PFAD_SHOPLOGO);
            if (!$dir) {
                return '';
            }
            while (($file = \readdir($dir)) !== false) {
                if ($file !== '.' && $file !== '..' && \mb_strpos($file, \SHOPLOGO_NAME) !== false) {
                    $ret = \PFAD_SHOPLOGO . $file;
                    break;
                }
            }
        }

        return $ret === null
            ? null
            : ($fullUrl === true
                ? self::getImageBaseURL()
                : '') . $ret;
    }

    /**
     * @param array $urls
     */
    public static function setURLs(array $urls): void
    {
        self::$url = $urls;
    }

    /**
     * @param bool     $forceSSL
     * @param int|null $langID
     * @return string - the shop URL without trailing slash
     */
    public static function getURL(bool $forceSSL = false, int $langID = null): string
    {
        $langID = $langID ?? self::$kSprache;
        $idx    = (int)$forceSSL;
        if (isset(self::$url[$langID][$idx])) {
            return self::$url[$langID][$idx];
        }
        $url                      = self::buildBaseURL($forceSSL);
        self::$url[$langID][$idx] = $url;

        return $url;
    }

    /**
     * @param bool $forceSSL
     * @return string - the shop Admin URL without trailing slash
     */
    public static function getAdminURL(bool $forceSSL = false): string
    {
        return \rtrim(self::buildBaseURL($forceSSL) . '/' . \PFAD_ADMIN, '/');
    }

    /**
     * @param bool $forceSSL
     * @return string
     */
    private static function buildBaseURL(bool $forceSSL): string
    {
        $url = \URL_SHOP;
        if (\mb_strpos($url, 'http://') === 0) {
            $sslStatus = Request::checkSSL();
            if ($sslStatus === 2) {
                $url = \str_replace('http://', 'https://', $url);
            } elseif ($sslStatus === 4 || ($sslStatus === 3 && $forceSSL)) {
                $url = \str_replace('http://', 'https://', $url);
            }
        }

        return \rtrim($url, '/');
    }

    /**
     * @param int $pageType
     */
    public static function setPageType(int $pageType): void
    {
        $mapper              = new PageTypeToPageName();
        self::$pageType      = $pageType;
        self::$AktuelleSeite = $mapper->map($pageType);
        \executeHook(\HOOK_SHOP_SET_PAGE_TYPE, [
            'pageType' => self::$pageType,
            'pageName' => self::$AktuelleSeite
        ]);
    }

    /**
     * @return int
     */
    public static function getPageType(): int
    {
        return self::$pageType ?? \PAGE_UNBEKANNT;
    }

    /**
     * @param bool $decoded - true to decode %-sequences in the URI, false to leave them unchanged
     * @return string
     */
    public static function getRequestUri(bool $decoded = false): string
    {
        $uri         = $_SERVER['HTTP_X_REWRITE_URL'] ?? $_SERVER['REQUEST_URI'];
        $shopURLdata = \parse_url(self::getURL());
        $baseURLdata = \parse_url($uri);

        if (empty($shopURLdata['path'])) {
            $shopURLdata['path'] = '/';
        }

        $uri = isset($baseURLdata['path'])
            ? \mb_substr($baseURLdata['path'], \mb_strlen($shopURLdata['path']))
            : '';

        if ($decoded) {
            $uri = \rawurldecode($uri);
        }

        return $uri;
    }

    /**
     * @param bool $sessionSwitchAllowed
     * @return bool
     */
    public static function isAdmin(bool $sessionSwitchAllowed = false): bool
    {
        if (\is_bool(self::$logged)) {
            return self::$logged;
        }

        if (\session_name() === 'eSIdAdm') {
            // admin session already active
            self::$logged       = self::Container()->getAdminAccount()->logged();
            self::$adminToken   = $_SESSION['jtl_token'];
            self::$adminLangTag = $_SESSION['AdminAccount']->language;
        } elseif (!empty($_SESSION['loggedAsAdmin']) && $_SESSION['loggedAsAdmin'] === true) {
            // frontend session has been notified by admin session
            self::$logged       = true;
            self::$adminToken   = $_SESSION['adminToken'];
            self::$adminLangTag = $_SESSION['adminLangTag'];
            self::Container()->getGetText();
        } elseif ($sessionSwitchAllowed === true
            && isset($_COOKIE['eSIdAdm'])
            && Request::verifyGPDataString('fromAdmin') === 'yes'
        ) {
            // frontend session has not been notified yet
            // try to fetch information autonomously
            $frontendId = \session_id();
            \session_write_close();
            \session_name('eSIdAdm');
            \session_id($_COOKIE['eSIdAdm']);
            \session_start();
            $adminToken                   = $_SESSION['jtl_token'];
            $adminLangTag                 = $_SESSION['AdminAccount']->language;
            $_SESSION['frontendUpToDate'] = true;
            \session_write_close();
            \session_name('JTLSHOP');
            \session_id($frontendId);
            \session_start();
            self::$logged       = $_SESSION['loggedAsAdmin'] = true;
            self::$adminToken   = $_SESSION['adminToken']    = $adminToken;
            self::$adminLangTag = $_SESSION['adminLangTag']  = $adminLangTag;
        } else {
            // no information about admin session available
            self::$logged       = null;
            self::$adminToken   = null;
            self::$adminLangTag = null;
        }

        return self::$logged ?? false;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public static function getAdminSessionToken(): ?string
    {
        if (self::isAdmin()) {
            return self::$adminToken;
        }

        return null;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public static function getCurAdminLangTag(): ?string
    {
        if (self::isAdmin()) {
            return self::$adminLangTag;
        }

        return null;
    }

    /**
     * @return bool
     */
    public static function isBrandfree(): bool
    {
        return Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_BRANDFREE);
    }

    /**
     * Get the default container of the jtl shop
     *
     * @return DefaultServicesInterface
     */
    public static function Container(): DefaultServicesInterface
    {
        if (!static::$container) {
            static::createContainer();
        }

        return static::$container;
    }

    /**
     * Get the default container of the jtl shop
     *
     * @return DefaultServicesInterface
     */
    public function _Container(): DefaultServicesInterface
    {
        return self::Container();
    }

    /**
     * Create the default container of the jtl shop
     */
    private static function createContainer(): void
    {
        $container         = new Services\Container();
        static::$container = $container;

        $container->singleton(DbInterface::class, static function () {
            return new NiceDB(\DB_HOST, \DB_USER, \DB_PASS, \DB_NAME);
        });

        $container->singleton(JTLCacheInterface::class, JTLCache::class);

        $container->singleton(LinkServiceInterface::class, LinkService::class);

        $container->singleton(AlertServiceInterface::class, AlertService::class);

        $container->singleton(NewsServiceInterface::class, NewsService::class);

        $container->singleton(CryptoServiceInterface::class, CryptoService::class);

        $container->singleton(PasswordServiceInterface::class, PasswordService::class);

        $container->singleton(CountryServiceInterface::class, CountryService::class);

        $container->singleton(JTLDebugBar::class, static function (Container $container) {
            return new JTLDebugBar($container->getDB()->getPDO(), Shopsetting::getInstance()->getAll());
        });

        $container->singleton('BackendAuthLogger', static function (Container $container) {
            $loggingConf = self::getConfig([\CONF_GLOBAL])['global']['admin_login_logger_mode'] ?? [];
            $handlers    = [];
            foreach ($loggingConf as $value) {
                if ($value === AdminLoginConfig::CONFIG_DB) {
                    $handlers[] = (new NiceDBHandler($container->getDB(), Logger::INFO))
                        ->setFormatter(new LineFormatter('%message%', null, true, true));
                } elseif ($value === AdminLoginConfig::CONFIG_FILE) {
                    $handlers[] = (new StreamHandler(\PFAD_LOGFILES . 'auth.log', Logger::INFO))
                        ->setFormatter(new LineFormatter(null, null, true, true));
                }
            }

            return new Logger('auth', $handlers, [new PsrLogMessageProcessor()]);
        });

        $container->singleton(LoggerInterface::class, static function (Container $container) {
            $handler = (new NiceDBHandler($container->getDB(), self::getConfigValue(\CONF_GLOBAL, 'systemlog_flag')))
                ->setFormatter(new LineFormatter('%message%', null, true, true));

            return new Logger('jtllog', [$handler], [new PsrLogMessageProcessor()]);
        });

        $container->alias(LoggerInterface::class, 'Logger');

        $container->singleton(ValidationServiceInterface::class, static function () {
            $vs = new ValidationService($_GET, $_POST, $_COOKIE);
            $vs->setRuleSet('identity', (new RuleSet())->integer()->gt(0));

            return $vs;
        });

        $container->bind(JTLApi::class, static function () {
            // return new JTLApi($_SESSION, $container->make(Nice::class));
            return new JTLApi($_SESSION, Nice::getInstance());
        });

        $container->singleton(GcServiceInterface::class, GcService::class);

        $container->singleton(OPCService::class);

        $container->singleton(PageService::class);

        $container->singleton(DB::class);

        $container->singleton(PageDB::class);

        $container->singleton(Locker::class);

        $container->bind(BoxFactoryInterface::class, static function () {
            return new BoxFactory(Shopsetting::getInstance()->getAll());
        });

        $container->singleton(BoxServiceInterface::class, static function (Container $container) {
            $smarty = self::Smarty();

            return new BoxService(
                Shopsetting::getInstance()->getAll(),
                $container->getBoxFactory(),
                $container->getDB(),
                $container->getCache(),
                $smarty,
                new DefaultRenderer($smarty)
            );
        });

        $container->singleton(CaptchaServiceInterface::class, static function () {
            return new CaptchaService(new SimpleCaptchaService(
                !(Frontend::get('bAnti_spam_already_checked', false) || Frontend::getCustomer()->isLoggedIn())
            ));
        });

        $container->singleton(GetText::class);

        $container->singleton(AdminAccount::class, static function (Container $container) {
            return new AdminAccount(
                $container->getDB(),
                $container->getBackendLogService(),
                new AdminLoginStatusMessageMapper(),
                new AdminLoginStatusToLogLevel(),
                $container->getGetText(),
                $container->getAlertService()
            );
        });

        $container->singleton(Filesystem::class, static function () {
            $factory = new AdapterFactory(self::getConfig([\CONF_FS])['fs']);

            return new Filesystem($factory->getAdapter());
        });

        $container->bind(Mailer::class, static function (Container $container) {
            $db        = $container->getDB();
            $settings  = Shopsetting::getInstance();
            $smarty    = new SmartyRenderer(new MailSmarty($db));
            $hydrator  = new DefaultsHydrator($smarty->getSmarty(), $db, $settings);
            $validator = new MailValidator($db, $settings->getAll());

            return new Mailer($hydrator, $smarty, $settings, $validator);
        });

        $container->singleton(ManagerInterface::class, static function (Container $container) {
            return new Manager($container->getDB());
        });

        $container->singleton(TemplateServiceInterface::class, static function (Container $container) {
            return new TemplateService($container->getDB(), $container->getCache());
        });

        $container->bind(CronController::class);
    }

    /**
     * @param bool $admin
     * @return string
     */
    public static function getFaviconURL(bool $admin = false): string
    {
        if ($admin) {
            $faviconUrl = self::getAdminURL();
            if (\file_exists(\PFAD_ROOT . \PFAD_ADMIN . 'favicon.ico')) {
                $faviconUrl .= '/favicon.ico';
            } else {
                $faviconUrl .= '/favicon-default.ico';
            }
        } else {
            $smarty           = JTLSmarty::getInstance();
            $templateDir      = $smarty->getTemplateDir($smarty->context);
            $shopTemplatePath = $smarty->getTemplateUrlPath();
            $faviconUrl       = self::getURL() . '/';

            if (\file_exists($templateDir . 'themes/base/images/favicon.ico')) {
                $faviconUrl .= $shopTemplatePath . 'themes/base/images/favicon.ico';
            } elseif (\file_exists($templateDir . 'favicon.ico')) {
                $faviconUrl .= $shopTemplatePath . 'favicon.ico';
            } elseif (\file_exists(\PFAD_ROOT . 'favicon.ico')) {
                $faviconUrl .= 'favicon.ico';
            } else {
                $faviconUrl .= 'favicon-default.ico';
            }
        }

        return $faviconUrl;
    }
}
