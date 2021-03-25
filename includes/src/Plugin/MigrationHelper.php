<?php declare(strict_types=1);

namespace JTL\Plugin;

use DateTime;
use DirectoryIterator;
use Exception;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Shop;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Class MigrationHelper
 * @package JTL\Plugin
 */
final class MigrationHelper
{
    /**
     * @var string
     */
    private const MIGRATION_FILE_NAME_PATTERN = '/^Migration(\d{14}).php$/';

    /**
     * @var string
     */
    public const MIGRATION_CLASS_NAME_PATTERN = '/Migration(\d{14})$/';

    /**
     * @var string
     */
    private $path;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * MigrationHelper constructor.
     * @param string      $path
     * @param DbInterface $db
     */
    public function __construct(string $path, DbInterface $db)
    {
        $this->path = $path;
        $this->db   = $db;
    }

    /**
     * Gets the migration path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Get the id from a file name.
     *
     * @param string $fileName File Name
     * @return int|null
     */
    public function getIdFromFileName($fileName): ?int
    {
        $matches = [];

        return (\preg_match(self::MIGRATION_FILE_NAME_PATTERN, \basename($fileName), $matches))
            ? (int)$matches[1]
            : null;
    }

    /**
     * Returns names like 'Migration12345678901234'.
     *
     * @param DirectoryIterator $file
     * @param string            $pluginID
     * @return string
     */
    public function mapFileNameToClassName(DirectoryIterator $file, string $pluginID): string
    {
        return \sprintf(
            'Plugin\%s\Migrations\%s',
            $pluginID,
            \str_replace('.' . $file->getExtension(), '', $file->getFilename())
        );
    }

    /**
     * Check if a migration file name is valid.
     *
     * @param string $fileName File Name
     * @return bool|int
     */
    public function isValidMigrationFileName($fileName)
    {
        $matches = [];

        return \preg_match(self::MIGRATION_FILE_NAME_PATTERN, $fileName, $matches);
    }

    /**
     * Check database integrity
     */
    public function verifyIntegrity(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS tpluginmigration 
            (
                kMigration bigint(14) NOT NULL, 
                nVersion int(3) NOT NULL, 
                pluginID varchar(255) NOT NULL, 
                dExecuted datetime NOT NULL,
                PRIMARY KEY (kMigration)
            ) ENGINE=InnoDB CHARACTER SET='utf8' COLLATE='utf8_unicode_ci'",
            ReturnType::DEFAULT
        );
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS tmigrationlog 
            (
                kMigrationlog int(10) NOT NULL AUTO_INCREMENT, 
                kMigration bigint(20) NOT NULL, 
                cDir enum('up','down') NOT NULL, 
                cState varchar(6) NOT NULL, 
                cLog text NOT NULL, 
                dCreated datetime NOT NULL, 
                PRIMARY KEY (kMigrationlog)
            ) ENGINE=InnoDB CHARACTER SET='utf8' COLLATE='utf8_unicode_ci'",
            ReturnType::DEFAULT
        );
    }

    /**
     * @param string $idxTable
     * @param string $idxName
     * @return array
     */
    public function indexColumns(string $idxTable, string $idxName): array
    {
        return $this->db->queryPrepared(
            'SHOW INDEXES FROM `' . $idxTable . '` WHERE Key_name = :idxName',
            ['idxName' => $idxName],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param string      $idxTable
     * @param array       $idxColumns
     * @param string|null $idxName
     * @param bool        $idxUnique
     * @return bool
     */
    public function createIndex(string $idxTable, array $idxColumns, $idxName = null, $idxUnique = false): bool
    {
        if (empty($idxName)) {
            $idxName = \implode('_', $idxColumns) . '_' . ($idxUnique ? 'UQ' : 'IDX');
        }

        if (\count($this->indexColumns($idxTable, $idxName)) === 0 || $this->dropIndex($idxTable, $idxName)) {
            $ddl = 'CREATE' . ($idxUnique ? ' UNIQUE' : '')
                . ' INDEX `' . $idxName . '` ON `' . $idxTable . '` '
                . '(`' . \implode('`, `', $idxColumns) . '`)';

            return !$this->db->executeQuery($ddl, ReturnType::DEFAULT) ? false : true;
        }

        return false;
    }

    /**
     * @param string $idxTable
     * @param string $idxName
     * @return bool
     */
    public function dropIndex(string $idxTable, string $idxName): bool
    {
        if (\count($this->indexColumns($idxTable, $idxName)) > 0) {
            return !$this->db->executeQuery(
                'DROP INDEX `' . $idxName . '` ON `' . $idxTable . '` ',
                ReturnType::DEFAULT
            ) ? false : true;
        }

        return true;
    }

    /**
     * Returns names like '12345678901234'.
     *
     * @param string $className File Name
     * @return int|null
     */
    public static function mapClassNameToId($className): ?int
    {
        $matches = [];

        return \preg_match(self::MIGRATION_CLASS_NAME_PATTERN, $className, $matches)
            ? (int)$matches[1]
            : null;
    }

    /**
     * @param string $pluginDir
     * @param string $description
     * @param string $author
     * @return string
     * @throws \SmartyException
     * @throws Exception
     */
    public static function create(string $pluginDir, string $description, string $author): string
    {
        $plugin = Shop::Container()->getDB()->select('tplugin', 'cVerzeichnis', $pluginDir);
        if (empty($plugin)) {
            throw new Exception('There is no plugin for the given dir name.');
        }

        $datetime      = new DateTime('NOW');
        $timestamp     = $datetime->format('YmdHis');
        $filePath      = 'Migration' . $timestamp;
        $relPath       = 'plugins/' . $pluginDir . '/Migrations';
        $migrationPath = $relPath . '/' . $filePath . '.php';
        $fileSystem    = new Filesystem(new Local(\PFAD_ROOT));

        if (!$fileSystem->has($relPath)) {
            throw new Exception('Migrations path doesn\'t exist!');
        }

        $content = Shop::Smarty()
            ->assign('description', $description)
            ->assign('author', $author)
            ->assign('created', $datetime->format(DateTime::RSS))
            ->assign('pluginDir', $pluginDir)
            ->assign('timestamp', $timestamp)
            ->fetch(\PFAD_ROOT . 'includes/src/Console/Command/Plugin/Template/migration.class.tpl');

        $fileSystem->put($migrationPath, $content);

        return $migrationPath;
    }
}
