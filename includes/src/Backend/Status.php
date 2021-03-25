<?php declare(strict_types=1);

namespace JTL\Backend;

use Exception;
use JTL\Cache\JTLCacheInterface;
use JTL\Checkout\ZahlungsLog;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Exportformat;
use JTL\License\Manager;
use JTL\License\Mapper;
use JTL\Mail\Template\Model as MailTplModel;
use JTL\Mail\Template\TemplateFactory;
use JTL\Media\Image\Product;
use JTL\Media\Image\StatsItem;
use JTL\Nice;
use JTL\Plugin\Helper;
use JTL\Plugin\State;
use JTL\Profiler;
use JTL\Shop;
use JTL\Update\Updater;
use stdClass;
use Systemcheck\Environment;
use Systemcheck\Platform\Filesystem;
use Systemcheck\Platform\Hosting;
use function Functional\some;

/**
 * Class Status
 * @package JTL\Backend
 */
class Status
{
    /**
     * @var JTLCacheInterface
     */
    protected $cache;

    /**
     * @var DbInterface
     */
    protected $db;


    private static $instance;

    public const CACHE_ID_FOLDER_PERMISSIONS   = 'validFolderPermissions';
    public const CACHE_ID_DATABASE_STRUCT      = 'validDatabaseStruct';
    public const CACHE_ID_MODIFIED_FILE_STRUCT = 'validModifiedFileStruct';
    public const CACHE_ID_ORPHANED_FILE_STRUCT = 'validOrphanedFilesStruct';
    public const CACHE_ID_EMAIL_SYNTAX_CHECK   = 'validEMailSyntaxCheck';
    public const CACHE_ID_EXPORT_SYNTAX_CHECK  = 'validExportSyntaxCheck';

    /**
     * Status constructor.
     * @param DbInterface $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->db    = $db;
        $this->cache = $cache;

        self::$instance = $this;
    }

    /**
     * @param DbInterface $db
     * @param JTLCacheInterface|null $cache
     * @param bool $flushCache
     * @return Status
     */
    public static function getInstance(
        DbInterface $db,
        ?JTLCacheInterface $cache = null,
        bool $flushCache = false
    ): self {
        $instance = static::$instance ?? new self($db, $cache ?? Shop::Container()->getCache());

        if ($flushCache) {
            $instance->cache->flushTags([\CACHING_GROUP_STATUS]);
        }

        return $instance;
    }

    /**
     * @return JTLCacheInterface
     */
    public function getObjectCache(): JTLCacheInterface
    {
        return $this->cache->setJtlCacheConfig(
            $this->db->selectAll('teinstellungen', 'kEinstellungenSektion', \CONF_CACHING)
        );
    }

    /**
     * @return StatsItem
     * @throws Exception
     */
    public function getImageCache(): StatsItem
    {
        return (new Product($this->db))->getStats();
    }

    /**
     * @return stdClass
     */
    public function getSystemLogInfo(): stdClass
    {
        $conf = Shop::getConfigValue(\CONF_GLOBAL, 'systemlog_flag');

        return (object)[
            'error'  => $conf >= \JTLLOG_LEVEL_ERROR,
            'notice' => $conf >= \JTLLOG_LEVEL_NOTICE,
            'debug'  => $conf >= \JTLLOG_LEVEL_NOTICE
        ];
    }

    /**
     * checks the db-structure against 'admin/includes/shopmd5files/dbstruct_[shop-version].json'
     *
     * @return bool  true='no errors', false='something is wrong'
     */
    public function validDatabaseStruct(): bool
    {
        require_once \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . 'dbcheck_inc.php';

        if (($dbStruct = $this->cache->get(self::CACHE_ID_DATABASE_STRUCT)) === false) {
            $dbStruct             = [];
            $dbStruct['current']  = \getDBStruct(true);
            $dbStruct['original'] = \getDBFileStruct();

            $this->cache->set(
                self::CACHE_ID_DATABASE_STRUCT,
                $dbStruct,
                [\CACHING_GROUP_STATUS]
            );
        }

        return \is_array($dbStruct['current'])
            && \is_array($dbStruct['original'])
            && \count(\compareDBStruct($dbStruct['original'], $dbStruct['current'])) === 0;
    }

    /**
     * checks the shop-filesystem-structure against 'admin/includes/shopmd5files/[shop-version].csv'
     *
     * @param string|null $hash
     * @return bool  true='no errors', false='something is wrong'
     */
    public function validModifiedFileStruct(?string &$hash = null): bool
    {
        if (($validModifiedFileStruct = $this->cache->get(self::CACHE_ID_MODIFIED_FILE_STRUCT)) === false) {
            $check   = new FileCheck();
            $files   = [];
            $stats   = 0;
            $md5file = \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . \PFAD_SHOPMD5 . $check->getVersionString() . '.csv';

            $validModifiedFileStruct = $check->validateCsvFile($md5file, $files, $stats) === FileCheck::OK
                ? $stats
                : 1;

            $this->cache->set(
                self::CACHE_ID_MODIFIED_FILE_STRUCT,
                $validModifiedFileStruct,
                [\CACHING_GROUP_STATUS]
            );
        }
        $hash = \md5(($hash ?? 'validModifiedFileStruct') . '_' . $validModifiedFileStruct);

        return $validModifiedFileStruct === 0;
    }

    /**
     * checks the shop-filesystem-structure against 'admin/includes/shopmd5files/deleted_files_[shop-version].csv'
     *
     * @param string|null $hash
     * @return bool  true='no errors', false='something is wrong'
     */
    public function validOrphanedFilesStruct(?string &$hash = null): bool
    {
        if (($validOrphanedFilesStruct = $this->cache->get(self::CACHE_ID_ORPHANED_FILE_STRUCT)) === false) {
            $check             = new FileCheck();
            $files             = [];
            $stats             = 0;
            $orphanedFilesFile = \PFAD_ROOT . \PFAD_ADMIN .
                \PFAD_INCLUDES . \PFAD_SHOPMD5
                . 'deleted_files_' . $check->getVersionString() . '.csv';

            $validOrphanedFilesStruct = $check->validateCsvFile($orphanedFilesFile, $files, $stats) === FileCheck::OK
                ? $stats
                : 1;

            $this->cache->set(
                self::CACHE_ID_ORPHANED_FILE_STRUCT,
                $validOrphanedFilesStruct,
                [\CACHING_GROUP_STATUS]
            );
        }
        $hash = \md5(($hash ?? 'validOrphanedFilesStruct') . '_' . $validOrphanedFilesStruct);

        return $validOrphanedFilesStruct === 0;
    }

    /**
     * @param string|null $hash
     * @return bool
     */
    public function validFolderPermissions(?string &$hash = null): bool
    {
        if (($filesystemFolders = $this->cache->get(self::CACHE_ID_FOLDER_PERMISSIONS)) === false) {
            $filesystem = new Filesystem(\PFAD_ROOT);
            $filesystem->getFoldersChecked();
            $filesystemFolders = $filesystem->getFolderStats();
            $this->cache->set(
                self::CACHE_ID_FOLDER_PERMISSIONS,
                $filesystemFolders,
                [\CACHING_GROUP_STATUS]
            );
        }
        $hash = \md5(($hash ?? 'validFolderPermissions') . '_' . $filesystemFolders->nCountInValid);

        return $filesystemFolders->nCountInValid === 0;
    }

    /**
     * @return array
     */
    public function getPluginSharedHooks(): array
    {
        $sharedPlugins = [];
        $sharedHookIds = $this->db->query(
            'SELECT nHook
                FROM tpluginhook
                GROUP BY nHook
                HAVING COUNT(DISTINCT kPlugin) > 1',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($sharedHookIds as $hookData) {
            $hookID                 = (int)$hookData->nHook;
            $sharedPlugins[$hookID] = [];
            $plugins                = $this->db->queryPrepared(
                'SELECT DISTINCT tpluginhook.kPlugin, tplugin.cName, tplugin.cPluginID
                    FROM tpluginhook
                    INNER JOIN tplugin
                        ON tpluginhook.kPlugin = tplugin.kPlugin
                    WHERE tpluginhook.nHook = :hook
                        AND tplugin.nStatus = :state',
                [
                    'hook'  => $hookID,
                    'state' => State::ACTIVATED
                ],
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($plugins as $plugin) {
                $sharedPlugins[$hookID][$plugin->cPluginID] = $plugin;
            }
        }

        return $sharedPlugins;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function hasPendingUpdates(): bool
    {
        return (new Updater($this->db))->hasPendingUpdates();
    }

    /**
     * @return bool
     */
    public function hasActiveProfiler(): bool
    {
        return Profiler::getIsActive() !== 0;
    }

    /**
     * @return bool
     */
    public function hasInstallDir(): bool
    {
        return \is_dir(\PFAD_ROOT . \PFAD_INSTALL);
    }

    /**
     * @return bool
     */
    public function hasDifferentTemplateVersion(): bool
    {
        try {
            $template = Shop::Container()->getTemplateService()->getActiveTemplate();
        } catch (Exception $e) {
            return false;
        }
        return $template->getVersion() !== \APPLICATION_VERSION;
    }

    /**
     * @return bool
     */
    public function hasMobileTemplateIssue(): bool
    {
        try {
            $template = Shop::Container()->getTemplateService()->getActiveTemplate();
        } catch (Exception $e) {
            return false;
        }
        if ($template->isResponsive()) {
            $mobileTpl = $this->db->select('ttemplate', 'eTyp', 'mobil');
            if ($mobileTpl !== null) {
                $xmlFile = \PFAD_ROOT . \PFAD_TEMPLATES . $mobileTpl->cTemplate .
                    \DIRECTORY_SEPARATOR . \TEMPLATE_XML;
                if (\file_exists($xmlFile)) {
                    return true;
                }
                // Wenn ein Template aktiviert aber physisch nicht vorhanden ist,
                // ist der DB-Eintrag falsch und wird gelöscht
                $this->db->delete('ttemplate', 'eTyp', 'mobil');
            }
        }

        return false;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function hasStandardTemplateIssue(): bool
    {
        return $this->db->select('ttemplate', 'eTyp', 'standard') === null
            || Shop::Container()->getTemplateService()->getActiveTemplate()->getTemplate() === null;
    }

    /**
     * @return bool
     */
    public function hasValidEnvironment(): bool
    {
        $systemcheck = new Environment();
        $systemcheck->executeTestGroup('Shop5');

        return $systemcheck->getIsPassed();
    }

    /**
     * @return array
     */
    public function getEnvironmentTests(): array
    {
        return (new Environment())->executeTestGroup('Shop5');
    }

    /**
     * @return Hosting
     */
    public function getPlatform(): Hosting
    {
        return new Hosting();
    }

    /**
     * @return array
     */
    public function getMySQLStats(): array
    {
        $stats = $this->db->stats();
        $info  = $this->db->info();
        $lines = \explode('  ', $stats);
        $lines = \array_map(static function ($v) {
            [$key, $value] = \explode(':', $v, 2);

            return ['key' => \trim($key), 'value' => \trim($value)];
        }, $lines);

        return \array_merge([['key' => 'Version', 'value' => $info]], $lines);
    }

    /**
     * @return array
     */
    public function getPaymentMethodsWithError(): array
    {
        $incorrectPaymentMethods = [];
        $paymentMethods          = $this->db->selectAll(
            'tzahlungsart',
            'nActive',
            1,
            '*',
            'cAnbieter, cName, nSort, kZahlungsart'
        );
        foreach ($paymentMethods as $method) {
            if (($logCount = ZahlungsLog::count($method->cModulId, \JTLLOG_LEVEL_ERROR)) > 0) {
                $method->logCount          = $logCount;
                $incorrectPaymentMethods[] = $method;
            }
        }

        return $incorrectPaymentMethods;
    }

    /**
     * @param bool $has
     * @return array|bool
     */
    public function getOrphanedCategories(bool $has = true)
    {
        $categories = $this->db->query(
            'SELECT kKategorie, cName
                FROM tkategorie
                WHERE kOberkategorie > 0
                    AND kOberkategorie NOT IN (SELECT DISTINCT kKategorie FROM tkategorie)',
            ReturnType::ARRAY_OF_OBJECTS
        );

        return $has === true
            ? \count($categories) === 0
            : $categories;
    }

    /**
     * @return bool
     */
    public function hasFullTextIndexError(): bool
    {
        $conf = Shop::getSettings([\CONF_ARTIKELUEBERSICHT])['artikeluebersicht'];

        return isset($conf['suche_fulltext'])
            && $conf['suche_fulltext'] !== 'N'
            && (!$this->db->query(
                "SHOW INDEX
                    FROM tartikel
                    WHERE KEY_NAME = 'idx_tartikel_fulltext'",
                ReturnType::SINGLE_OBJECT
            )
                || !$this->db->query(
                    "SHOW INDEX
                    FROM tartikelsprache
                    WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'",
                    ReturnType::SINGLE_OBJECT
                ));
    }

    /**
     * @param string|null $hash
     * @return bool
     */
    public function hasLicenseExpirations(?string &$hash = null): bool
    {
        $manager = new Manager($this->db, $this->cache);
        $mapper  = new Mapper($manager);

        $toBeExpired  = $mapper->getCollection()->getAboutToBeExpired(28)->count();
        $boundExpired = $mapper->getCollection()->getBoundExpired()->count();
        $hash         = \md5(($hash ?? 'hasLicenseExpirations') . '_' . $toBeExpired . '_' . $boundExpired);

        return $toBeExpired > 0 || $boundExpired > 0;
    }

    /**
     * @return bool
     */
    public function hasNewPluginVersions(): bool
    {
        if (\SAFE_MODE === true) {
            return false;
        }

        $data = $this->db->query(
            'SELECT `kPlugin`, `nVersion`, `bExtension`
                FROM `tplugin`',
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($data) || 1 > \count($data)) {
            return false; // there are no plugins installed
        }

        foreach ($data as $item) {
            try {
                $plugin = Helper::getLoader((int)$item->bExtension === 1)->init((int)$item->kPlugin);
            } catch (Exception $e) {
                continue;
            }
            if ($plugin->getCurrentVersion()->greaterThan($item->nVersion)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks, whether the password reset mail template contains the old variable $neues_passwort.
     *
     * @return bool
     */
    public function hasInvalidPasswordResetMailTemplate(): bool
    {
        $translations = $this->db->query(
            "SELECT lang.cContentText, lang.cContentHtml
                FROM temailvorlagesprache lang
                JOIN temailvorlage
                ON lang.kEmailvorlage = temailvorlage.kEmailvorlage
                WHERE temailvorlage.cName = 'Passwort vergessen'",
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($translations as $t) {
            $old = '{$neues_passwort}';
            if (\mb_strpos($t->cContentHtml, $old) !== false || \mb_strpos($t->cContentText, $old) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks, whether SMTP is configured for sending mails but no encryption method is chosen for E-Mail-Server
     * communication
     *
     * @param string|null $hash
     * @return bool
     */
    public function hasInsecureMailConfig(?string &$hash = null): bool
    {
        $conf = Shop::getConfig([\CONF_EMAILS])['emails'];
        $hash = \md5(($hash ?? 'hasInsecureMailConfig') . '_' . $conf['email_methode']);

        return $conf['email_methode'] === 'smtp' && empty(\trim($conf['email_smtp_verschluesselung']));
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function needPasswordRehash2FA(): bool
    {
        $passwordService = Shop::Container()->getPasswordService();
        $hashes          = $this->db->query(
            'SELECT *
                FROM tadmin2facodes
                GROUP BY kAdminlogin',
            ReturnType::ARRAY_OF_OBJECTS
        );

        return some($hashes, static function ($hash) use ($passwordService) {
            return $passwordService->needsRehash($hash->cEmergencyCode);
        });
    }

    /**
     * @return array
     */
    public function getDuplicateLinkGroupTemplateNames(): array
    {
        return $this->db->query(
            'SELECT * FROM tlinkgruppe
                GROUP BY cTemplatename
                HAVING COUNT(*) > 1',
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param int         $type
     * @param string|null $hash
     * @return int
     */
    public function getExportFormatErrorCount(int $type = Exportformat::SYNTAX_FAIL, ?string &$hash = null): int
    {
        $cacheKey = self::CACHE_ID_EXPORT_SYNTAX_CHECK . $type;
        if (($syntaxErrCnt = $this->cache->get($cacheKey)) === false) {
            $syntaxErrCnt = (int)$this->db->queryPrepared(
                'SELECT COUNT(*) AS cnt FROM texportformat WHERE nFehlerhaft = :type',
                ['type' => $type],
                ReturnType::SINGLE_OBJECT
            )->cnt;

            $this->cache->set($cacheKey, $syntaxErrCnt, [\CACHING_GROUP_STATUS, self::CACHE_ID_EXPORT_SYNTAX_CHECK]);
        }

        $hash = \md5($hash . $syntaxErrCnt);

        return $syntaxErrCnt;
    }

    /**
     * @param int         $type
     * @param string|null $hash
     * @return int
     */
    public function getEmailTemplateSyntaxErrorCount(int $type = MailTplModel::SYNTAX_FAIL, ?string &$hash = null): int
    {
        $cacheKey = self::CACHE_ID_EMAIL_SYNTAX_CHECK . $type;
        if (($syntaxErrCnt = $this->cache->get($cacheKey)) === false) {
            $syntaxErrCnt = 0;
            /** @var array $templates */
            $templates = $this->db->queryPrepared(
                'SELECT cModulId, kPlugin FROM temailvorlage WHERE nFehlerhaft = :type',
                ['type' => $type],
                ReturnType::ARRAY_OF_OBJECTS
            );
            $factory   = new TemplateFactory($this->db);
            foreach ($templates as $template) {
                $module = $template->cModulId;
                if ($template->kPlugin > 0) {
                    $module = 'kPlugin_' . $template->kPlugin . '_' . $template->cModulId;
                }
                $syntaxErrCnt += $factory->getTemplate($module) !== null ? 1 : 0;
            }

            $this->cache->set($cacheKey, $syntaxErrCnt, [\CACHING_GROUP_STATUS, self::CACHE_ID_EMAIL_SYNTAX_CHECK]);
        }

        $hash = \md5($hash . $syntaxErrCnt);

        return $syntaxErrCnt;
    }

    /**
     * @return bool
     */
    public function hasExtensionSOAP(): bool
    {
        return \extension_loaded('soap');
    }

    /**
     * @return array
     */
    public function getExtensions(): array
    {
        $nice       = Nice::getInstance();
        $extensions = $nice->gibAlleMoeglichenModule();
        foreach ($extensions as $extension) {
            $extension->bActive = $nice->checkErweiterung($extension->kModulId);
        }

        return $extensions;
    }
}
