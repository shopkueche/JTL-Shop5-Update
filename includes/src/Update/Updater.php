<?php

namespace JTL\Update;

use Exception;
use Ifsnop\Mysqldump\Mysqldump;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Minify\MinifyService;
use JTL\Network\JTLApi;
use JTL\Shop;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use JTLShop\SemVer\Version;
use JTLShop\SemVer\VersionCollection;
use PDOException;
use stdClass;

/**
 * Class Updater
 * @package JTL\Update
 */
class Updater
{
    /**
     * @var bool
     */
    protected static $isVerified = false;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * Updater constructor.
     * @param DbInterface $db
     * @throws Exception
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
        $this->verify();
    }

    /**
     * Check database integrity
     *
     * @throws Exception
     */
    public function verify(): void
    {
        if (static::$isVerified === true) {
            return;
        }
        MigrationHelper::verifyIntegrity();
        $dbVersion      = $this->getCurrentDatabaseVersion();
        $dbVersionShort = (int)\sprintf('%d%02d', $dbVersion->getMajor(), $dbVersion->getMinor());
        // While updating from 3.xx to 4.xx provide a default admin-template row
        if ($dbVersionShort < 400) {
            $count = (int)$this->db->query(
                "SELECT * FROM `ttemplate` WHERE `eTyp` = 'admin'",
                ReturnType::AFFECTED_ROWS
            );
            if ($count === 0) {
                $this->db->query(
                    "ALTER TABLE `ttemplate` 
                        CHANGE `eTyp` `eTyp` ENUM('standard','mobil','admin') NOT NULL",
                    ReturnType::AFFECTED_ROWS
                );
                $this->db->query(
                    "INSERT INTO `ttemplate` (`cTemplate`, `eTyp`) VALUES ('bootstrap', 'admin')",
                    ReturnType::AFFECTED_ROWS
                );
            }
        }

        if ($dbVersionShort < 404) {
            $this->db->query(
                'ALTER TABLE `tversion` CHANGE `nTyp` `nTyp` INT(4) UNSIGNED NOT NULL',
                ReturnType::AFFECTED_ROWS
            );
        }

        static::$isVerified = true;
    }

    /**
     * Has pending updates to execute
     *
     * @param bool $force
     * @return bool
     * @throws Exception
     */
    public function hasPendingUpdates(bool $force = false): bool
    {
        static $pending = null;

        if ($force || $pending === null) {
            $fileVersion = $this->getCurrentFileVersion();
            $dbVersion   = $this->getCurrentDatabaseVersion();

            if (Version::parse($fileVersion)->greaterThan($dbVersion)
                || ($dbVersion->smallerThan(Version::parse('2.19'))
                    || $dbVersion->equals(Version::parse('2.19')))
            ) {
                return true;
            }

            $manager = new MigrationManager($this->db);
            $pending = \count($manager->getPendingMigrations($force)) > 0;
        }

        return $pending;
    }

    /**
     * Create a database backup file including structure and data
     *
     * @param string $file
     * @param bool   $compress
     * @throws Exception
     */
    public function createSqlDump(string $file, bool $compress = true): void
    {
        if ($compress) {
            $info = \pathinfo($file);
            if ($info['extension'] !== 'gz') {
                $file .= '.gz';
            }
        }

        if (\file_exists($file)) {
            @\unlink($file);
        }

        $connectionStr = \sprintf('mysql:host=%s;dbname=%s', \DB_HOST, \DB_NAME);
        $sql           = new Mysqldump($connectionStr, \DB_USER, \DB_PASS, [
            'skip-comments'  => true,
            'skip-dump-date' => true,
            'compress'       => $compress === true
                ? Mysqldump::GZIP
                : Mysqldump::NONE
        ]);

        $sql->start($file);
    }

    /**
     * @param bool $compress
     * @return string
     */
    public function createSqlDumpFile(bool $compress = true): string
    {
        $file = \PFAD_ROOT . \PFAD_EXPORT_BACKUP . \date('YmdHis') . '_backup.sql';
        if ($compress) {
            $file .= '.gz';
        }

        return $file;
    }

    /**
     * @return stdClass
     * @throws Exception
     */
    public function getVersion(): stdClass
    {
        $v = $this->db->query('SELECT * FROM tversion', ReturnType::SINGLE_OBJECT);

        if ($v === null) {
            throw new Exception('Unable to identify application version');
        }

        return $v;
    }

    /**
     * @return string
     */
    public function getCurrentFileVersion(): string
    {
        return \APPLICATION_VERSION;
    }

    /**
     * @return Version
     * @throws Exception
     */
    public function getCurrentDatabaseVersion(): Version
    {
        $version = $this->getVersion()->nVersion;

        if ($version === '5' || $version === 5) {
            $version = '5.0.0';
        }

        return Version::parse($version);
    }

    /**
     * @param Version $version
     * @return Version
     */
    public function getTargetVersion(Version $version): Version
    {
        $majors        = ['2.19' => Version::parse('3.00.0'), '3.20' => Version::parse('4.00.0')];
        $targetVersion = null;

        foreach ($majors as $preMajor => $major) {
            if ($version->equals(Version::parse($preMajor))) {
                $targetVersion = $major;
            }
        }

        if (empty($targetVersion)) {
            $api               = Shop::Container()->get(JTLApi::class);
            $availableUpdates  = $api->getAvailableVersions() ?? [];
            $versionCollection = new VersionCollection();

            foreach ($availableUpdates as $availableUpdate) {
                $versionCollection->append($availableUpdate->reference);
            }

            $targetVersion = $version->smallerThan(Version::parse($this->getCurrentFileVersion()))
                ? $versionCollection->getNextVersion($version)
                : $version;
        }

        return $targetVersion ?? Version::parse(\APPLICATION_VERSION);
    }

    /**
     * getPreviousVersion
     *
     * @param int $version
     * @return int|mixed
     */
    public function getPreviousVersion(int $version)
    {
        $majors = [300 => 219, 400 => 320];
        if (\array_key_exists($version, $majors)) {
            $previousVersion = $majors[$version];
        } else {
            $previousVersion = --$version;
        }

        return $previousVersion;
    }

    /**
     * @param int $targetVersion
     * @return string
     */
    protected function getUpdateDir(int $targetVersion): string
    {
        return \sprintf('%s%d', \PFAD_ROOT . \PFAD_UPDATE, $targetVersion);
    }

    /**
     * @param int $targetVersion
     * @return string
     */
    protected function getSqlUpdatePath(int $targetVersion): string
    {
        return \sprintf('%s/update1.sql', $this->getUpdateDir($targetVersion));
    }

    /**
     * @param Version $targetVersion
     * @return array|bool
     * @throws Exception
     */
    protected function getSqlUpdates(Version $targetVersion)
    {
        $sqlFilePathVersion = \sprintf('%d%02d', $targetVersion->getMajor(), $targetVersion->getMinor());
        $sqlFile            = $this->getSqlUpdatePath((int)$sqlFilePathVersion);

        if (!\file_exists($sqlFile)) {
            throw new Exception('SQL file in path "' . $sqlFile . '" not found');
        }

        $lines = \file($sqlFile);
        foreach ($lines as $i => $line) {
            $line = \trim($line);
            if (\mb_strpos($line, '--') === 0 || \mb_strpos($line, '#') === 0) {
                unset($lines[$i]);
            }
        }

        return $lines;
    }

    /**
     * @return IMigration|Version
     * @throws Exception
     */
    public function update()
    {
        return $this->hasPendingUpdates()
            ? $this->updateToNextVersion()
            : Version::parse(\APPLICATION_VERSION);
    }

    public function finalize(): void
    {
        $smarty = new JTLSmarty(true, ContextType::FRONTEND);
        $smarty->clearCompiledTemplate();
        Shop::Container()->getCache()->flushAll();
        $ms = new MinifyService();
        $ms->flushCache();
    }

    /**
     * @return IMigration|Version
     * @throws Exception
     */
    protected function updateToNextVersion()
    {
        $currentVersion = $this->getCurrentDatabaseVersion();
        $targetVersion  = $this->getTargetVersion($currentVersion);

        if ($targetVersion->smallerThan(Version::parse('4.03.0'))) {
            return $targetVersion <= $currentVersion
                ? $currentVersion
                : $this->updateBySqlFile($currentVersion, $targetVersion);
        }

        return $this->updateByMigration($targetVersion);
    }

    /**
     * @param Version $currentVersion
     * @param Version $targetVersion
     * @return Version
     * @throws Exception
     */
    protected function updateBySqlFile(Version $currentVersion, Version $targetVersion): Version
    {
        $currentLine = 0;
        $sqls        = $this->getSqlUpdates($currentVersion);
        try {
            $this->db->beginTransaction();
            foreach ($sqls as $i => $sql) {
                $currentLine = $i;
                $this->db->query($sql, ReturnType::AFFECTED_ROWS);
            }
        } catch (PDOException $e) {
            $code  = (int)$e->errorInfo[1];
            $error = $this->db->escape($e->errorInfo[2]);

            if (!\in_array($code, [1062, 1060, 1267], true)) {
                $this->db->rollback();

                $errorCountForLine = 1;
                $version           = $this->getVersion();

                if ((int)$version->nZeileBis === $currentLine) {
                    $errorCountForLine = $version->nFehler + 1;
                }

                $this->db->queryPrepared(
                    'UPDATE tversion SET
                         nZeileVon = 1, 
                         nZeileBis = :rw, 
                         nFehler = :errcnt,
                         nTyp = :type, 
                         cFehlerSQL = :err, 
                         dAktualisiert = NOW()',
                    [
                        'rw'     => $currentLine,
                        'errcnt' => $errorCountForLine,
                        'type'   => $code,
                        'err'    => $error

                    ],
                    ReturnType::AFFECTED_ROWS
                );

                throw $e;
            }
        }

        $this->setVersion($targetVersion);

        return $targetVersion;
    }

    /**
     * @param Version $targetVersion
     * @return IMigration|Version
     * @throws Exception
     */
    protected function updateByMigration(Version $targetVersion)
    {
        $manager           = new MigrationManager($this->db);
        $pendingMigrations = $manager->getPendingMigrations();
        if (\count($pendingMigrations) === 0) {
            $this->setVersion($targetVersion);

            return $targetVersion;
        }
        $id        = \reset($pendingMigrations);
        $migration = $manager->getMigrationById($id);

        $manager->executeMigration($migration);

        return $migration;
    }

    /**
     * @throws Exception
     */
    protected function executeMigrations(): void
    {
        foreach ((new MigrationManager($this->db))->migrate() as $migration) {
            if ($migration->error !== null) {
                throw new Exception($migration->error);
            }
        }
    }

    /**
     * @param Version $targetVersion
     * @throws Exception
     */
    protected function setVersion(Version $targetVersion): void
    {
        $tVersionColumns = $this->db->executeQuery('SHOW COLUMNS FROM `tversion`', ReturnType::ARRAY_OF_OBJECTS);
        foreach ($tVersionColumns as $column) {
            if ($column->Field === 'nVersion') {
                if ($column->Type !== 'varchar(20)') {
                    $newVersion = \sprintf('%d%02d', $targetVersion->getMajor(), $targetVersion->getMinor());
                } else {
                    $newVersion = $targetVersion->getOriginalVersion();
                }
            }
        }

        if (empty($newVersion)) {
            throw new Exception('New database version can\'t be set.');
        }

        $this->db->queryPrepared(
            "UPDATE tversion SET 
                nVersion = :ver, 
                nZeileVon = 1, 
                nZeileBis = 0, 
                nFehler = 0, 
                nTyp = 1, 
                cFehlerSQL = '', 
                dAktualisiert = NOW()",
            ['ver' => $newVersion],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @return null|stdClass
     * @throws Exception
     */
    public function error(): ?stdClass
    {
        $version = $this->getVersion();

        return (int)$version->nFehler > 0
            ? (object)[
                'code'  => $version->nTyp,
                'error' => $version->cFehlerSQL,
                'sql'   => $version->nVersion < 402
                    ? $this->getErrorSqlByFile()
                    : null
            ]
            : null;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function getErrorSqlByFile(): ?string
    {
        $version = $this->getVersion();
        $sqls    = $this->getSqlUpdates($version->nVersion);

        return ((int)$version->nFehler > 0 && \array_key_exists($version->nZeileBis, $sqls))
            ? \trim($sqls[$version->nZeileBis])
            : null;
    }

    /**
     * @return array
     */
    public function getUpdateDirs(): array
    {
        $directories = [];
        $dir         = \PFAD_ROOT . \PFAD_UPDATE;
        foreach (\scandir($dir, \SCANDIR_SORT_ASCENDING) as $key => $value) {
            if (\is_numeric($value)
                && (int)$value > 300
                && (int)$value < 500
                && !\in_array($value, ['.', '..'], true)
                && \is_dir($dir . \DIRECTORY_SEPARATOR . $value)
            ) {
                $directories[] = $value;
            }
        }

        return $directories;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function hasMinUpdateVersion(): bool
    {
        return !Version::parse(\JTL_MIN_SHOP_UPDATE_VERSION)->greaterThan($this->getCurrentDatabaseVersion());
    }

    /**
     * @return string
     */
    public function getMinUpdateVersionError(): string
    {
        return \sprintf(
            __('errorMinShopVersionRequired'),
            \APPLICATION_VERSION,
            \JTL_MIN_SHOP_UPDATE_VERSION,
            \APPLICATION_VERSION,
            __('dbupdaterURL')
        );
    }
}
