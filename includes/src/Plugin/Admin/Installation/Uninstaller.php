<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation;

use Exception;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Language\LanguageHelper;
use JTL\Plugin\Helper;
use JTL\Plugin\InstallCode;
use JTL\Plugin\LegacyPluginLoader;
use JTL\Plugin\PluginInterface;
use JTL\Plugin\PluginLoader;
use JTL\Shop;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;

/**
 * Class Uninstaller
 * @package JTL\Plugin\Admin\Installation
 */
final class Uninstaller
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * Uninstaller constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->db    = $db;
        $this->cache = $cache;
    }

    /**
     * Versucht, ein ausgewähltes Plugin zu deinstallieren
     *
     * @param int      $pluginID
     * @param bool     $update
     * @param int|null $newID
     * @param bool     $deleteData
     * @param bool     $deleteFiles
     * @return int
     * 1 = Alles O.K.
     * 2 = $kPlugin wurde nicht übergeben
     * 3 = SQL-Fehler
     */
    public function uninstall(
        int $pluginID,
        bool $update = false,
        int $newID = null,
        bool $deleteData = true,
        bool $deleteFiles = false
    ): int {
        if ($pluginID <= 0) {
            return InstallCode::WRONG_PARAM;
        }
        $data   = $this->db->select('tplugin', 'kPlugin', $pluginID);
        $loader = (int)$data->bExtension === 1
            ? new PluginLoader($this->db, $this->cache)
            : new LegacyPluginLoader($this->db, $this->cache);
        $plugin = $loader->init($pluginID);
        if ($plugin === null) {
            return InstallCode::NO_PLUGIN_FOUND;
        }
        if ($update) {
            // Plugin wird nur teilweise deinstalliert, weil es danach ein Update gibt
            $this->doSQLDelete($pluginID, $update, $newID);
        } else {
            if (!\SAFE_MODE && ($p = Helper::bootstrap($pluginID, $loader)) !== null) {
                $p->uninstalled($deleteData);
            }
            $this->executeMigrations($plugin, $deleteData);
            $uninstaller = $plugin->getPaths()->getUninstaller();
            if ($uninstaller !== null && \file_exists($uninstaller)) {
                try {
                    include $plugin->getPaths()->getUninstaller();
                } catch (Exception $exc) {
                }
            }
            $this->doSQLDelete($pluginID, $update, $newID, $deleteData);
            if ($deleteFiles === true) {
                $dir     = $plugin->getPaths()->getBaseDir();
                $manager = new MountManager(['root' => new Filesystem(new Local(\PFAD_ROOT))]);
                $manager->mountFilesystem('plgn', Shop::Container()->get(\JTL\Filesystem\Filesystem::class));
                $dirName = (int)$data->bExtension === 1
                    ? (\PLUGIN_DIR . $dir)
                    : (\PFAD_PLUGIN . $dir);
                @$manager->deleteDir('plgn://' . $dirName);
            }
        }
        $this->cache->flushAll();

        return InstallCode::OK;
    }

    /**
     * @param PluginInterface $plugin
     * @param bool            $deleteData
     * @return array
     * @throws Exception
     */
    private function executeMigrations(PluginInterface $plugin, bool $deleteData = true): array
    {
        $manager = new MigrationManager(
            $this->db,
            $plugin->getPaths()->getBasePath() . \PFAD_PLUGIN_MIGRATIONS,
            $plugin->getPluginID()
        );

        return $manager->migrate(0, $deleteData);
    }

    /**
     * @param int $pluginID
     */
    private function fullDelete(int $pluginID): void
    {
        $this->db->query(
            'DELETE tpluginsprachvariablesprache, tpluginsprachvariablecustomsprache, tpluginsprachvariable
                FROM tpluginsprachvariable
                LEFT JOIN tpluginsprachvariablesprache
                    ON tpluginsprachvariablesprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                LEFT JOIN tpluginsprachvariablecustomsprache
                    ON tpluginsprachvariablecustomsprache.cSprachvariable = tpluginsprachvariable.cName
                    AND tpluginsprachvariablecustomsprache.kPlugin = tpluginsprachvariable.kPlugin
                WHERE tpluginsprachvariable.kPlugin = ' . $pluginID,
            ReturnType::DEFAULT
        );
        $this->db->delete('tplugineinstellungen', 'kPlugin', $pluginID);
        $this->db->delete('tconsent', 'pluginID', $pluginID);
        $this->db->delete('tplugincustomtabelle', 'kPlugin', $pluginID);
        $this->db->delete('tpluginlinkdatei', 'kPlugin', $pluginID);
        $this->db->queryPrepared(
            'DELETE tzahlungsartsprache, tzahlungsart, tversandartzahlungsart
                FROM tzahlungsart
                LEFT JOIN tzahlungsartsprache
                    ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                LEFT JOIN tversandartzahlungsart
                    ON tversandartzahlungsart.kZahlungsart = tzahlungsart.kZahlungsart
                WHERE tzahlungsart.cModulId LIKE :pid',
            ['pid' => 'kPlugin_' . $pluginID . '_%'],
            ReturnType::DEFAULT
        );
        $this->db->queryPrepared(
            "DELETE tboxen, tboxvorlage
                FROM tboxvorlage
                LEFT JOIN tboxen 
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE tboxvorlage.kCustomID = :pid
                    AND (tboxvorlage.eTyp = 'plugin' OR tboxvorlage.eTyp = 'extension')",
            ['pid' => $pluginID],
            ReturnType::DEFAULT
        );
        $this->db->query(
            'DELETE tpluginemailvorlageeinstellungen, temailvorlagespracheoriginal,
                temailvorlage, temailvorlagesprache
                FROM temailvorlage
                LEFT JOIN temailvorlagespracheoriginal
                    ON temailvorlagespracheoriginal.kEmailvorlage = temailvorlage.kEmailvorlage
                LEFT JOIN tpluginemailvorlageeinstellungen
                    ON tpluginemailvorlageeinstellungen.kEmailvorlage = temailvorlage.kEmailvorlage
                LEFT JOIN temailvorlagesprache
                    ON temailvorlagesprache.kEmailvorlage = temailvorlage.kEmailvorlage
                WHERE temailvorlage.kPlugin = ' . $pluginID,
            ReturnType::DEFAULT
        );
    }

    /**
     * @param int $pluginID
     */
    private function partialDelete(int $pluginID): void
    {
        $this->db->queryPrepared(
            'DELETE tpluginsprachvariablesprache, tpluginsprachvariable
                FROM tpluginsprachvariable
                LEFT JOIN tpluginsprachvariablesprache
                    ON tpluginsprachvariablesprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                WHERE tpluginsprachvariable.kPlugin = :pid',
            ['pid' => $pluginID],
            ReturnType::DEFAULT
        );
        $this->db->delete('tpluginlinkdatei', 'kPlugin', $pluginID);
        $this->db->queryPrepared(
            'DELETE temailvorlage, temailvorlagespracheoriginal
                FROM temailvorlage
                LEFT JOIN temailvorlagespracheoriginal
                    ON temailvorlagespracheoriginal.kEmailvorlage = temailvorlage.kEmailvorlage
                WHERE temailvorlage.kPlugin = :pid',
            ['pid' => $pluginID],
            ReturnType::DEFAULT
        );
    }

    /**
     * @param int      $pluginID
     * @param bool     $update
     * @param null|int $newPluginID
     * @param bool     $deleteData
     */
    private function doSQLDelete(int $pluginID, bool $update, int $newPluginID = null, bool $deleteData = true): void
    {
        if ($update) {
            $this->partialDelete($pluginID);
        } else {
            if ($deleteData === true) {
                foreach ($this->db->selectAll('tplugincustomtabelle', 'kPlugin', $pluginID) as $table) {
                    $this->db->query('DROP TABLE IF EXISTS ' . $table->cTabelle, ReturnType::DEFAULT);
                }
            }
            $this->fullDelete($pluginID);
        }
        $this->db->queryPrepared(
            'DELETE tpluginsqlfehler, tpluginhook
                FROM tpluginhook
                LEFT JOIN tpluginsqlfehler
                    ON tpluginsqlfehler.kPluginHook = tpluginhook.kPluginHook
                WHERE tpluginhook.kPlugin = :pid',
            ['pid' => $pluginID],
            ReturnType::DEFAULT
        );
        $this->db->delete('tpluginadminmenu', 'kPlugin', $pluginID);
        $this->db->queryPrepared(
            'DELETE tplugineinstellungenconfwerte, tplugineinstellungenconf
                FROM tplugineinstellungenconf
                LEFT JOIN tplugineinstellungenconfwerte
                    ON tplugineinstellungenconfwerte.kPluginEinstellungenConf = 
                    tplugineinstellungenconf.kPluginEinstellungenConf
                WHERE tplugineinstellungenconf.kPlugin = :pid',
            ['pid' => $pluginID],
            ReturnType::DEFAULT
        );

        $this->db->delete('tpluginuninstall', 'kPlugin', $pluginID);
        $this->db->delete('tplugin_resources', 'kPlugin', $pluginID);
        $links = [];
        if ($newPluginID !== null && $newPluginID > 0) {
            $links = $this->db->query(
                'SELECT kLink
                    FROM tlink
                    WHERE kPlugin IN (' . $pluginID . ', ' . $newPluginID . ')
                        ORDER BY kLink',
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
        if (\count($links) === 2) {
            $languages = LanguageHelper::getAllLanguages(2);
            foreach ($this->db->selectAll('tlinksprache', 'kLink', $links[0]->kLink) as $item) {
                $this->db->update(
                    'tlinksprache',
                    ['kLink', 'cISOSprache'],
                    [$links[1]->kLink, $item->cISOSprache],
                    (object)['cSeo' => $item->cSeo]
                );
                $languageID = $languages[$item->cISOSprache]->kSprache;
                $this->db->delete(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kLink', $links[0]->kLink, $languageID]
                );
                $this->db->update(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kLink', $links[1]->kLink, $languageID],
                    (object)['cSeo' => $item->cSeo]
                );
            }
        }
        $this->db->queryPrepared(
            "DELETE tlinksprache, tseo, tlink
                FROM tlink
                LEFT JOIN tlinksprache
                    ON tlinksprache.kLink = tlink.kLink
                LEFT JOIN tseo
                    ON tseo.cKey = 'kLink'
                    AND tseo.kKey = tlink.kLink
                WHERE tlink.kPlugin = :pid",
            ['pid' => $pluginID],
            ReturnType::DEFAULT
        );
        $this->db->delete('tpluginzahlungsartklasse', 'kPlugin', $pluginID);
        $this->db->delete('tplugintemplate', 'kPlugin', $pluginID);
        $this->db->delete('tcheckboxfunktion', 'kPlugin', $pluginID);
        $this->db->delete('tadminwidgets', 'kPlugin', $pluginID);
        $this->db->delete('topcportlet', 'kPlugin', $pluginID);
        $this->db->delete('topcblueprint', 'kPlugin', $pluginID);
        $this->db->queryPrepared(
            'DELETE texportformateinstellungen, texportformatqueuebearbeitet, texportformat
                FROM texportformat
                LEFT JOIN texportformateinstellungen
                    ON texportformateinstellungen.kExportformat = texportformat.kExportformat
                LEFT JOIN texportformatqueuebearbeitet
                    ON texportformatqueuebearbeitet.kExportformat = texportformat.kExportformat
                WHERE texportformat.kPlugin = :pid',
            ['pid' => $pluginID],
            ReturnType::DEFAULT
        );
        $this->db->delete('tplugin', 'kPlugin', $pluginID);
    }
}
