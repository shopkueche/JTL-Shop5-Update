<?php declare(strict_types=1);

namespace JTL\Update;

use Exception;
use JTL\DB\DbInterface;
use JTL\IO\IOError;
use JTL\IO\IOFile;
use JTL\L10n\GetText;
use JTL\Plugin\Admin\Installation\MigrationManager as PluginMigrationManager;
use JTL\Plugin\PluginLoader;
use JTL\Shop;
use JTL\Smarty\ContextType;
use JTLShop\SemVer\Version;
use JTLSmarty;
use SmartyException;

/**
 * Class UpdateIO
 * @package JTL\Update
 */
class UpdateIO
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * UpdateIO constructor.
     * @param DbInterface $db
     * @param GetText     $getText
     */
    public function __construct(DbInterface $db, GetText $getText)
    {
        $this->db = $db;
        $getText->loadAdminLocale('pages/dbupdater');
    }

    /**
     * @return array|IOError
     * @throws Exception
     */
    public function update()
    {
        $updater = new Updater($this->db);
        try {
            $dbVersion       = $updater->getCurrentDatabaseVersion();
            $updateResult    = $updater->update();
            $availableUpdate = $updater->hasPendingUpdates();
            if ($updateResult instanceof IMigration) {
                $updateResult = \sprintf('Migration: %s', $updateResult->getDescription());
            } elseif ($updateResult instanceof Version) {
                $updateResult = \sprintf('Version: %s', $updateResult->__toString());
            } else {
                $updateResult = \sprintf('Version: %.2f', $updateResult / 100);
            }
            if ($availableUpdate === false) {
                $updater->finalize();
            }

            return [
                'result'          => $updateResult,
                'currentVersion'  => $dbVersion,
                'updatedVersion'  => $dbVersion,
                'availableUpdate' => $availableUpdate,
                'action'          => 'update'
            ];
        } catch (Exception $e) {
            return new IOError($e->getMessage());
        }
    }

    /**
     * @return array|IOError
     * @throws Exception
     */
    public function backup()
    {
        $updater = new Updater($this->db);

        try {
            $file = $updater->createSqlDumpFile();
            $updater->createSqlDump($file);
            $file   = \basename($file);
            $params = \http_build_query(['action' => 'download', 'file' => $file], '', '&');
            $url    = Shop::getAdminURL() . '/dbupdater.php?' . $params;

            return [
                'url'  => $url,
                'file' => $file,
                'type' => 'backup'
            ];
        } catch (Exception $e) {
            return new IOError($e->getMessage());
        }
    }

    /**
     * @param string $file
     * @return IOFile|IOError
     */
    public function download($file)
    {
        if (!\preg_match('/^([0-9_a-z]+).sql.gz$/', $file, $m)) {
            return new IOError('Wrong download request');
        }
        $filePath = \PFAD_ROOT . \PFAD_EXPORT_BACKUP . $file;

        return \file_exists($filePath)
            ? new IOFile($filePath, 'application/x-gzip')
            : new IOError('Download file does not exist');
    }

    /**
     * @param int|null $pluginID
     * @return array
     * @throws SmartyException
     * @throws Exception
     */
    public function getStatus($pluginID = null): array
    {
        $smarty                 = JTLSmarty::getInstance(false, ContextType::BACKEND);
        $updater                = new Updater($this->db);
        $template               = Shop::Container()->getTemplateService()->getActiveTemplate();
        $manager                = null;
        $currentFileVersion     = $updater->getCurrentFileVersion();
        $currentDatabaseVersion = $updater->getCurrentDatabaseVersion();
        $version                = $updater->getVersion();
        $updatesAvailable       = $updater->hasPendingUpdates();
        $updateError            = $updater->error();
        if (ADMIN_MIGRATION === true) {
            if ($pluginID !== null && \is_numeric($pluginID)) {
                $loader           = new PluginLoader($this->db, Shop::Container()->getCache());
                $plugin           = $loader->init($pluginID);
                $manager          = new PluginMigrationManager(
                    $this->db,
                    $plugin->getPaths()->getBasePath() . \PFAD_PLUGIN_MIGRATIONS,
                    $plugin->getPluginID(),
                    $plugin->getMeta()->getSemVer()
                );
                $updatesAvailable = \count($manager->getPendingMigrations()) > 0;
                $smarty->assign('migrationURL', 'plugin.php')
                    ->assign('pluginID', $pluginID);
            } else {
                $manager = new MigrationManager($this->db);
            }
        }

        $smarty->assign('updatesAvailable', $updatesAvailable)
            ->assign('currentFileVersion', $currentFileVersion)
            ->assign('currentDatabaseVersion', $currentDatabaseVersion)
            ->assign('manager', $manager)
            ->assign('hasDifferentVersions', !Version::parse($currentDatabaseVersion)
                ->equals(Version::parse($currentFileVersion)))
            ->assign('version', $version)
            ->assign('updateError', $updateError)
            ->assign('currentTemplateFileVersion', $template->getFileVersion() ?? '1.0.0')
            ->assign('currentTemplateDatabaseVersion', $template->getVersion());

        return [
            'tpl'  => $smarty->fetch('tpl_inc/dbupdater_status.tpl'),
            'type' => 'status_tpl'
        ];
    }

    /**
     * @param null|int    $id
     * @param null|int    $version
     * @param null|string $dir
     * @param null|int    $pluginID
     * @return array|IOError
     */
    public function executeMigration($id = null, $version = null, $dir = null, $pluginID = null)
    {
        try {
            $updater    = new Updater($this->db);
            if (!$updater->hasMinUpdateVersion()) {
                throw new Exception($updater->getMinUpdateVersionError());
            }
            $hasAlready = $updater->hasPendingUpdates();
            if ($pluginID !== null && \is_numeric($pluginID)) {
                $loader  = new PluginLoader($this->db, Shop::Container()->getCache());
                $plugin  = $loader->init($pluginID);
                $manager = new PluginMigrationManager(
                    $this->db,
                    $plugin->getPaths()->getBasePath() . \PFAD_PLUGIN_MIGRATIONS,
                    $plugin->getPluginID(),
                    $plugin->getMeta()->getSemVer()
                );
            } else {
                $manager = new MigrationManager($this->db);
            }
            if ($id !== null && \in_array($dir, [IMigration::UP, IMigration::DOWN], true)) {
                $manager->executeMigrationById($id, $dir);
            }

            $migration    = $manager->getMigrationById($id);
            $updateResult = \sprintf('Migration: %s', $migration->getDescription());
            $hasMore      = $updater->hasPendingUpdates(true);
            $result       = [
                'id'          => $id,
                'type'        => 'migration',
                'result'      => $updateResult,
                'hasMore'     => $hasMore,
                'forceReload' => $hasMore === false || ($hasMore !== $hasAlready),
            ];
        } catch (Exception $e) {
            $result = new IOError($e->getMessage());
        }

        return $result;
    }
}
