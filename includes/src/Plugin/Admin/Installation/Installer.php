<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Helpers\Text;
use JTL\Plugin\Admin\Validation\ValidatorInterface;
use JTL\Plugin\Helper;
use JTL\Plugin\InstallCode;
use JTL\Plugin\LegacyPluginLoader;
use JTL\Plugin\PluginInterface;
use JTL\Plugin\PluginLoader;
use JTL\Plugin\State;
use JTL\Shop;
use JTL\XMLParser;
use JTLShop\SemVer\Version;
use stdClass;
use function Functional\map;
use function Functional\select;

/**
 * Class Installer
 * @package JTL\Plugin\Admin\Installation
 */
final class Installer
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var string
     */
    private $dir;

    /**
     * @var Uninstaller
     */
    private $uninstaller;

    /**
     * @var ValidatorInterface
     */
    private $legacyValidator;

    /**
     * @var ValidatorInterface
     */
    private $pluginValidator;

    /**
     * @var PluginInterface|null
     */
    private $plugin;

    /**
     * Installer constructor.
     * @param DbInterface        $db
     * @param Uninstaller        $uninstaller
     * @param ValidatorInterface $legacyValidator
     * @param ValidatorInterface $pluginValidator
     */
    public function __construct(
        DbInterface $db,
        Uninstaller $uninstaller,
        ValidatorInterface $legacyValidator,
        ValidatorInterface $pluginValidator
    ) {
        $this->db              = $db;
        $this->uninstaller     = $uninstaller;
        $this->legacyValidator = $legacyValidator;
        $this->pluginValidator = $pluginValidator;
    }

    /**
     * @return string
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * @param string $dir
     */
    public function setDir(string $dir): void
    {
        $this->dir = $dir;
    }

    /**
     * @return PluginInterface|null
     */
    public function getPlugin(): ?PluginInterface
    {
        return $this->plugin;
    }

    /**
     * @param PluginInterface|null $plugin
     */
    public function setPlugin(PluginInterface $plugin): void
    {
        $this->plugin = $plugin;
    }

    /**
     * @param string|null $dir
     * @return int
     * @former installierePluginVorbereitung()
     */
    public function prepare(string $dir = null): int
    {
        if (empty($this->dir)) {
            return InstallCode::WRONG_PARAM;
        }
        $validator  = $this->pluginValidator;
        $pluginPath = \PFAD_ROOT . \PLUGIN_DIR . \basename($this->dir);
        if (!\file_exists($pluginPath . '/' . \PLUGIN_INFO_FILE)) {
            $pluginPath = \PFAD_ROOT . \PFAD_PLUGIN . \basename($this->dir);
            $validator  = $this->legacyValidator;
            if (!\file_exists($pluginPath . '/' . \PLUGIN_INFO_FILE)) {
                if ($dir !== null && $dir !== $this->dir) {
                    // special case for EXS api
                    $this->dir = $dir;

                    return $this->prepare();
                }

                return InstallCode::INFO_XML_MISSING;
            }
        }
        $validator->setDir($pluginPath);
        $parser = new XMLParser();
        $xml    = $parser->parse($pluginPath . '/' . \PLUGIN_INFO_FILE);
        $code   = $validator->pluginPlausiIntern($xml, $this->plugin !== null);
        if ($code === InstallCode::DUPLICATE_PLUGIN_ID && $this->plugin !== null && $this->plugin->getID() > 0) {
            $code = InstallCode::OK;
        }
        if ($code === InstallCode::OK || $code === InstallCode::OK_LEGACY) {
            $code = $this->install($xml);
        }

        return $code;
    }

    /**
     * Installiert ein Plugin
     *
     * @param array $xml
     * @return int
     * @former installierePlugin()
     */
    public function install(array $xml): int
    {
        $baseNode           = $this->getBaseNode($xml);
        $baseDir            = \basename($this->dir);
        $versionNode        = $baseNode['Install'][0]['Version'] ?? null;
        $xmlVersion         = (int)$baseNode['XMLVersion'];
        $basePath           = \PFAD_ROOT . \PFAD_PLUGIN . $baseDir . \DIRECTORY_SEPARATOR;
        $lastVersionKey     = null;
        $plugin             = new stdClass();
        $cache              = Shop::Container()->getCache();
        $plugin->nStatus    = $this->plugin === null ? State::ACTIVATED : $this->plugin->getState();
        $plugin->bExtension = 0;
        if (\is_array($versionNode)) {
            $lastVersionKey = \count($versionNode) / 2 - 1;
            $version        = (int)$versionNode[$lastVersionKey . ' attr']['nr'];
            $versionedDir   = $basePath . \PFAD_PLUGIN_VERSION . $version . \DIRECTORY_SEPARATOR;
            $bootstrapper   = $versionedDir . \OLD_BOOTSTRAPPER;
        } else {
            $version            = $baseNode['Version'];
            $basePath           = \PFAD_ROOT . \PLUGIN_DIR . $baseDir . \DIRECTORY_SEPARATOR;
            $versionedDir       = $basePath;
            $versionNode        = [];
            $bootstrapper       = $versionedDir . \PLUGIN_BOOTSTRAPPER;
            $plugin->bExtension = 1;
        }
        if ($this->plugin !== null) {
            $loader = $this->plugin->isExtension() === true
                ? new PluginLoader($this->db, $cache)
                : new LegacyPluginLoader($this->db, $cache);
            if (($p = Helper::bootstrap($this->plugin->getID(), $loader)) !== null) {
                $p->preUpdate($this->plugin->getMeta()->getVersion(), $version);
            }
        }
        $plugin                       = $this->addLicenseData($baseNode, $plugin);
        $plugin->cName                = $baseNode['Name'];
        $plugin->cBeschreibung        = $baseNode['Description'];
        $plugin->cAutor               = $baseNode['Author'];
        $plugin->cURL                 = $baseNode['URL'];
        $plugin->cIcon                = $baseNode['Icon'] ?? null;
        $plugin->cVerzeichnis         = $baseDir;
        $plugin->cPluginID            = $baseNode['PluginID'];
        $plugin->exsID                = $baseNode['ExsID'] ?? '_DBNULL_';
        $plugin->cStoreID             = $baseNode['StoreID'] ?? null;
        $plugin->cFehler              = '';
        $plugin->nVersion             = $version;
        $plugin->nXMLVersion          = $xmlVersion;
        $plugin->nPrio                = 0;
        $plugin->dZuletztAktualisiert = 'NOW()';
        $plugin->dErstellt            = $lastVersionKey !== null
            ? $versionNode[$lastVersionKey]['CreateDate']
            : $baseNode['CreateDate'];
        $plugin->bBootstrap           = (int)\is_file($bootstrapper);
        $plugin                       = $this->checkLicense($versionedDir, $plugin);
        $plugin->dInstalliert         = ($this->plugin !== null && $this->plugin->getID() > 0)
            ? $this->plugin->getMeta()->getDateInstalled()->format('Y-m-d H:i:s')
            : 'NOW()';
        $plugin->kPlugin              = $this->db->insert('tplugin', $plugin);
        $this->flushCache($baseNode);
        if ($plugin->kPlugin <= 0) {
            return InstallCode::WRONG_PARAM;
        }
        $factory = $plugin->bExtension === 0
            ? new LegacyPluginInstallerFactory($this->db, $xml, $plugin, $this->plugin)
            : new PluginInstallerFactory($this->db, $xml, $plugin, $this->plugin);
        $res     = $factory->install();
        if ($res !== InstallCode::OK) {
            $this->uninstaller->uninstall($plugin->kPlugin);

            return $res;
        }
        $res = $this->installSQL($plugin, $versionNode, $version, $versionedDir);
        $cache->flushTags([
            \CACHING_GROUP_CORE,
            \CACHING_GROUP_LICENSES,
            \CACHING_GROUP_LANGUAGE,
            \CACHING_GROUP_PLUGIN
        ]);

        return $res;
    }

    /**
     * @param stdClass $plugin
     * @param array    $versionNode
     * @param mixed    $version
     * @param string   $versionedDir
     * @return int
     */
    private function installSQL(stdClass $plugin, array $versionNode, $version, string $versionedDir): int
    {
        $loader      = $plugin->bExtension === 1
            ? new PluginLoader($this->db, Shop::Container()->getCache())
            : new LegacyPluginLoader($this->db, Shop::Container()->getCache());
        $hasSQLError = false;
        $code        = InstallCode::OK;
        foreach ($versionNode as $i => $versionData) {
            if ($version > 0
                && $this->plugin !== null
                && isset($versionData['nr'])
                && $this->plugin->getMeta()->getVersion() >= (int)$versionData['nr']
            ) {
                continue;
            }
            $i = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);

            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($i)) {
                continue;
            }
            $xy      = \trim(\str_replace('attr', '', $i));
            $sqlFile = $versionNode[$xy]['SQL'] ?? '';
            if ($sqlFile === '') {
                continue;
            }
            $code = $this->validateSQL($sqlFile, (int)$versionData['nr'], $plugin);
            if ($code !== InstallCode::OK) {
                $hasSQLError = true;
                break;
            }
        }
        if ($plugin->bExtension === 1) {
            $this->updateByMigration($plugin, $versionedDir, Version::parse($version));
        }
        // Ist ein SQL Fehler aufgetreten? Wenn ja, deinstalliere wieder alles
        if ($hasSQLError) {
            $this->uninstaller->uninstall($plugin->kPlugin);
        }
        if ($code === InstallCode::OK
            && $this->plugin === null
            && ($p = Helper::bootstrap($plugin->kPlugin, $loader)) !== null
        ) {
            $p->installed();
        }
        if ($this->plugin !== null && ($code === InstallCode::OK_LEGACY || $code === InstallCode::OK)) {
            $code = $this->syncPluginUpdate($plugin->kPlugin);
            if (($p = Helper::bootstrap($this->plugin->getID(), $loader)) !== null) {
                $p->updated($this->plugin->getMeta()->getVersion(), $version);
            }
        }

        return $code;
    }

    /**
     * @param array    $baseNode
     * @param stdClass $plugin
     * @return stdClass
     */
    private function addLicenseData(array $baseNode, stdClass $plugin): stdClass
    {
        $plugin->cLizenz           = '';
        $plugin->cLizenzKlasse     = '';
        $plugin->cLizenzKlasseName = '';
        if (isset($baseNode['LicenceClass'], $baseNode['LicenceClassFile'])
            && \mb_strlen($baseNode['LicenceClass']) > 0
            && \mb_strlen($baseNode['LicenceClassFile']) > 0
        ) {
            $plugin->cLizenzKlasse     = $baseNode['LicenceClass'];
            $plugin->cLizenzKlasseName = $baseNode['LicenceClassFile'];
            $plugin->nStatus           = State::LICENSE_KEY_MISSING;
        }

        return $plugin;
    }

    /**
     * @param array $baseNode
     */
    private function flushCache(array $baseNode): void
    {
        $tags        = empty($baseNode['Install'][0]['FlushTags'])
            ? []
            : \explode(',', $baseNode['Install'][0]['FlushTags']);
        $tagsToFlush = map(select($tags, static function ($e) {
            return \defined(\trim($e));
        }), static function ($e) {
            return \constant(\trim($e));
        });
        if (\count($tagsToFlush) > 0) {
            Shop::Container()->getCache()->flushTags($tagsToFlush);
        }
    }

    /**
     * @param string   $versionedDir
     * @param stdClass $plugin
     * @return stdClass
     */
    private function checkLicense(string $versionedDir, stdClass $plugin): stdClass
    {
        $licenceClassFile = $versionedDir . \PFAD_PLUGIN_LICENCE . $plugin->cLizenzKlasseName;
        if ($this->plugin !== null
            && \is_file($licenceClassFile)
            && $this->plugin->getState() > 0
            && $this->plugin->getLicense()->hasLicense()
        ) {
            require_once $licenceClassFile;
            $licenceClass  = $this->plugin->getLicense()->getClass();
            $pluginLicence = new $licenceClass();
            $licenceMethod = \PLUGIN_LICENCE_METHODE;
            if ($pluginLicence->$licenceMethod($this->plugin->getLicense()->getKey())) {
                $plugin->cLizenz = $this->plugin->getLicense()->getKey();
                $plugin->nStatus = $this->plugin->getState();
            }
        }

        return $plugin;
    }

    /**
     * @param array $xml
     * @return array
     */
    private function getBaseNode(array $xml): array
    {
        return $xml['jtlshopplugin'][0] ?? $xml['jtlshop3plugin'][0];
    }

    /**
     * Geht die angegebene SQL durch und formatiert diese. Immer 1 SQL pro Zeile.
     *
     * @param string $sqlFile
     * @param string $pluginName
     * @param int    $pluginVersion
     * @return array
     */
    private function parseSQLFile(string $sqlFile, string $pluginName, int $pluginVersion): array
    {
        $file = \PFAD_ROOT . \PFAD_PLUGIN . $pluginName . '/' .
            \PFAD_PLUGIN_VERSION . $pluginVersion . '/' .
            \PFAD_PLUGIN_SQL . $sqlFile;

        if (!\file_exists($file)) {
            return [];// SQL Datei existiert nicht
        }
        $handle   = \fopen($file, 'r');
        $sqlLines = [];
        $line     = '';
        while (($data = \fgets($handle)) !== false) {
            $data = \trim($data);
            if ($data !== '' && \mb_strpos($data, '--') !== 0) {
                if (\mb_strpos($data, 'CREATE TABLE') !== false) {
                    $line .= \trim($data);
                } elseif (\mb_strpos($data, 'INSERT') !== false) {
                    $line .= \trim($data);
                } else {
                    $line .= \trim($data);
                }

                if (\mb_substr($data, \mb_strlen($data) - 1, 1) === ';') {
                    $sqlLines[] = $line;
                    $line       = '';
                }
            }
        }
        \fclose($handle);

        return $sqlLines;
    }

    /**
     * @param stdClass $plugin
     * @param string    $pluginPath
     * @param Version   $targetVersion
     * @return array|Version
     * @throws \Exception
     */
    private function updateByMigration(stdClass $plugin, string $pluginPath, Version $targetVersion)
    {
        $path              = $pluginPath . \DIRECTORY_SEPARATOR . \PFAD_PLUGIN_MIGRATIONS;
        $manager           = new MigrationManager($this->db, $path, $plugin->cPluginID, $targetVersion);
        $pendingMigrations = $manager->getPendingMigrations();
        if (\count($pendingMigrations) === 0) {
            return $targetVersion;
        }

        return $manager->migrate();
    }

    /**
     * @param string    $sqlFile
     * @param int       $version
     * @param stdClass $plugin
     * @return int
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     * @former logikSQLDatei()
     */
    private function validateSQL(string $sqlFile, int $version, stdClass $plugin): int
    {
        if (empty($sqlFile)
            || $version < 100
            || (int)$plugin->kPlugin <= 0
            || empty($plugin->cPluginID)
        ) {
            return InstallCode::SQL_MISSING_DATA;
        }
        $lines = $this->parseSQLFile($sqlFile, $plugin->cVerzeichnis, $version);
        if (\count($lines) === 0) {
            return InstallCode::SQL_INVALID_FILE_CONTENT;
        }
        foreach ($lines as $sql) {
            $sql = Text::removeNumerousWhitespaces($sql);
            if (\mb_stripos($sql, 'create table') !== false) {
                $table = $this->getTableName($sql);
                if ($table === false) {
                    return InstallCode::SQL_ERROR;
                }
                $exists = $this->db->select('tplugincustomtabelle', 'cTabelle', $table);
                if ($exists === null) {
                    $customTable           = new stdClass();
                    $customTable->kPlugin  = $plugin->kPlugin;
                    $customTable->cTabelle = $table;
                    $this->db->insert('tplugincustomtabelle', $customTable);
                }
            }
            $this->db->query($sql, ReturnType::DEFAULT);
            $errNo = $this->db->getErrorCode();
            if ($errNo) {
                Shop::Container()->getLogService()->withName('kPlugin')->error(
                    'SQL Fehler beim Installieren des Plugins (' . $plugin->cName . '): ' .
                    \str_replace("'", '', $this->db->getErrorMessage()),
                    [$plugin->kPlugin]
                );

                return InstallCode::SQL_ERROR;
            }
        }

        return InstallCode::OK;
    }

    /**
     * extract table name from sql
     *
     * @param string $sql
     * @param string $action
     * @return string|bool
     */
    private function getTableName(string $sql, string $action = 'create table( if not exists)')
    {
        \preg_match('/' . $action . "? ([`']?)([a-z0-9_]+)\\2/i", $sql, $matches);

        return \end($matches);
    }


    /**
     * Wenn ein Update erfolgreich mit neuer kPlugin in der Datenbank ist
     * wird der alte kPlugin auf die neue Version übertragen und
     * die alte Plugin-Version deinstalliert.
     *
     * @param int $pluginID
     * @return int
     * 1 = Alles O.K.
     * 2 = Übergabeparameter nicht korrekt
     * 3 = Update konnte nicht installiert werden
     */
    public function syncPluginUpdate(int $pluginID): int
    {
        $newPluginID = $this->plugin->getID();
        $res         = $this->uninstaller->uninstall($newPluginID, true, $pluginID);
        if ($res !== InstallCode::OK) {
            $this->uninstaller->uninstall($pluginID);

            return InstallCode::SQL_ERROR;
        }
        $upd = (object)['kPlugin' => $newPluginID];
        $this->db->update('tplugin', 'kPlugin', $pluginID, $upd);
        $this->db->update('tpluginhook', 'kPlugin', $pluginID, $upd);
        $this->db->update('tpluginadminmenu', 'kPlugin', $pluginID, $upd);
        $this->db->update('tpluginsprachvariable', 'kPlugin', $pluginID, $upd);
        $this->db->update('tadminwidgets', 'kPlugin', $pluginID, $upd);
        $this->db->update('tplugin_resources', 'kPlugin', $pluginID, $upd);
        $this->db->update('tplugincustomtabelle', 'kPlugin', $pluginID, $upd);
        $this->db->update('tplugintemplate', 'kPlugin', $pluginID, $upd);
        $this->db->update('tpluginlinkdatei', 'kPlugin', $pluginID, $upd);
        $this->db->update('texportformat', 'kPlugin', $pluginID, $upd);
        $this->db->update('topcportlet', 'kPlugin', $pluginID, $upd);
        $this->db->update('topcblueprint', 'kPlugin', $pluginID, $upd);
        $this->updateLangVars($newPluginID, $pluginID);
        $this->updateConfig($newPluginID, $pluginID);

        $this->updateMailTemplates($newPluginID, $pluginID);
        $this->cleanUpMailTemplates();
        $this->db->update('tlink', 'kPlugin', $pluginID, (object)['kPlugin' => $newPluginID]);
        // Ausnahme: Gibt es noch eine Boxenvorlage in der Pluginversion?
        // Falls nein -> lösche tboxen mit dem entsprechenden kPlugin
        $this->updateBoxes($newPluginID, $pluginID);

        $this->db->update('tcheckboxfunktion', 'kPlugin', $pluginID, $upd);
        $this->db->update('tspezialseite', 'kPlugin', $pluginID, $upd);
        $this->updatePaymentMethods($newPluginID, $pluginID);

        return InstallCode::OK;
    }

    /**
     * @param int $oldPluginID
     * @param int $pluginID
     */
    private function updateBoxes(int $oldPluginID, int $pluginID): void
    {
        $newBoxTemplates = $this->db->queryPrepared(
            "SELECT *
                FROM tboxvorlage
                WHERE kCustomID = :pid
                AND (eTyp = 'plugin' OR eTyp = 'extension')",
            ['pid' => $oldPluginID],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $oldBoxTemplates = $this->db->queryPrepared(
            "SELECT *
                FROM tboxvorlage
                WHERE kCustomID = :pid
                AND (eTyp = 'plugin' OR eTyp = 'extension')",
            ['pid' => $pluginID],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($newBoxTemplates as $template) {
            foreach ($oldBoxTemplates as $newBoxTemplate) {
                if ($template->cTemplate === $newBoxTemplate->cTemplate) {
                    $this->db->queryPrepared(
                        'UPDATE tboxen
                            SET kBoxvorlage = :bid, kCustomID = :pid
                            WHERE kBoxvorlage = :oid',
                        [
                            'bid' => $newBoxTemplate->kBoxvorlage,
                            'pid' => $oldPluginID,
                            'oid' => $template->kBoxvorlage
                        ],
                        ReturnType::DEFAULT
                    );
                    break;
                }
            }
        }
        $this->db->delete('tboxvorlage', ['kCustomID', 'eTyp'], [$oldPluginID, 'plugin']);
        $this->db->delete('tboxvorlage', ['kCustomID', 'eTyp'], [$oldPluginID, 'extension']);
        $this->db->update(
            'tboxvorlage',
            ['kCustomID', 'eTyp'],
            [$pluginID, 'plugin'],
            (object)['kCustomID' => $oldPluginID]
        );
        $this->db->update(
            'tboxvorlage',
            ['kCustomID', 'eTyp'],
            [$pluginID, 'extension'],
            (object)['kCustomID' => $oldPluginID]
        );
        $this->db->queryPrepared(
            'DELETE FROM tboxen
                WHERE kCustomID = :pid 
                AND kBoxvorlage NOT IN (SELECT kBoxvorlage FROM tboxvorlage WHERE kCustomID = :pid)',
            ['pid' => $oldPluginID],
            ReturnType::DEFAULT
        );
    }

    /**
     * @param int $oldPluginID
     * @param int $pluginID
     */
    private function updateLangVars(int $oldPluginID, int $pluginID): void
    {
        $this->db->update(
            'tpluginsprachvariablecustomsprache',
            'kPlugin',
            $pluginID,
            (object)['kPlugin' => $oldPluginID]
        );
        $customLangVars = $this->db->queryPrepared(
            'SELECT DISTINCT tpluginsprachvariable.kPluginSprachvariable AS newID,
                tpluginsprachvariablecustomsprache.kPluginSprachvariable AS oldID, tpluginsprachvariable.cName
                FROM tpluginsprachvariablecustomsprache
                JOIN tpluginsprachvariable
                    ON tpluginsprachvariable.cName =  tpluginsprachvariablecustomsprache.cSprachvariable
                WHERE tpluginsprachvariablecustomsprache.kPlugin = :pid',
            ['pid' => $oldPluginID],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($customLangVars as $langVar) {
            $this->db->update(
                'tpluginsprachvariablecustomsprache',
                ['kPlugin', 'kPluginSprachvariable'],
                [$oldPluginID, $langVar->oldID],
                (object)['kPluginSprachvariable' => $langVar->newID]
            );
        }
    }

    /**
     * @param int $oldPluginID
     * @param int $pluginID
     */
    private function updateConfig(int $oldPluginID, int $pluginID): void
    {
        $pluginConf = $this->db->query(
            'SELECT *
                FROM tplugineinstellungen
                WHERE kPlugin IN (' . $oldPluginID . ', ' . $pluginID . ')
                ORDER BY kPlugin',
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($pluginConf) > 0) {
            $confData = [];
            foreach ($pluginConf as $conf) {
                $name = \str_replace(
                    ['kPlugin_' . $oldPluginID . '_', 'kPlugin_' . $pluginID . '_'],
                    '',
                    $conf->cName
                );
                if (!isset($confData[$name])) {
                    $confData[$name]          = new stdClass();
                    $confData[$name]->kPlugin = $oldPluginID;
                    $confData[$name]->cName   = \str_replace(
                        'kPlugin_' . $pluginID . '_',
                        'kPlugin_' . $oldPluginID . '_',
                        $conf->cName
                    );
                    $confData[$name]->cWert   = $conf->cWert;
                }
            }
            $this->db->query(
                'DELETE FROM tplugineinstellungen
                    WHERE kPlugin IN (' . $oldPluginID . ', ' . $pluginID . ')',
                ReturnType::AFFECTED_ROWS
            );

            foreach ($confData as $value) {
                $this->db->insert('tplugineinstellungen', $value);
            }
        }
        $this->db->query(
            'UPDATE tplugineinstellungen
                SET kPlugin = ' . $oldPluginID . ",
                    cName = REPLACE(cName, 'kPlugin_" . $pluginID . "_', 'kPlugin_" . $oldPluginID . "_')
                WHERE kPlugin = " . $pluginID,
            ReturnType::AFFECTED_ROWS
        );
        $this->db->query(
            'UPDATE tplugineinstellungenconf
                SET kPlugin = ' . $oldPluginID . ",
                    cWertName = REPLACE(cWertName, 'kPlugin_" . $pluginID . "_', 'kPlugin_" . $oldPluginID . "_')
                WHERE kPlugin = " . $pluginID,
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param int $oldPluginID
     * @param int $pluginID
     */
    private function updateMailTemplates(int $oldPluginID, int $pluginID): void
    {
        $this->db->update('temailvorlage', 'kPlugin', $pluginID, (object)['kPlugin' => $oldPluginID]);
        $oldMailTpl = $this->db->select('temailvorlage', 'kPlugin', $oldPluginID);
        $newMailTpl = $this->db->select('temailvorlage', 'kPlugin', $pluginID);
        if (isset($newMailTpl->kEmailvorlage, $oldMailTpl->kEmailvorlage)) {
            $this->db->update(
                'tpluginemailvorlageeinstellungen',
                'kEmailvorlage',
                $oldMailTpl->kEmailvorlage,
                (object)['kEmailvorlage' => $newMailTpl->kEmailvorlage]
            );
        }
        foreach ($this->plugin->getMailTemplates()->getTemplatesAssoc() as $moduleID => $oldTpl) {
            $newTpl = $this->db->select(
                'temailvorlage',
                'kPlugin',
                $oldPluginID,
                'cModulId',
                $moduleID,
                null,
                null,
                false,
                'kEmailvorlage'
            );
            if (isset($newTpl->kEmailvorlage) && $newTpl->kEmailvorlage > 0) {
                $newTplID = (int)$newTpl->kEmailvorlage;
                $oldTplID = (int)$oldTpl->kEmailvorlage;
                $this->db->delete('temailvorlagesprache', 'kEmailvorlage', $newTplID);
                $this->db->update(
                    'temailvorlagesprache',
                    'kEmailvorlage',
                    $oldTplID,
                    (object)['kEmailvorlage' => $newTplID]
                );
                $this->db->update(
                    'tpluginemailvorlageeinstellungen',
                    'kEmailvorlage',
                    $oldTplID,
                    (object)['kEmailvorlage' => $newTplID]
                );
            }
        }
    }

    private function cleanUpMailTemplates(): void
    {
        $this->db->query(
            'DELETE FROM tpluginemailvorlageeinstellungen
                WHERE NOT EXISTS (
                    SELECT 1 FROM temailvorlage
                    WHERE temailvorlage.kEmailvorlage = tpluginemailvorlageeinstellungen.kEmailvorlage
                )',
            ReturnType::DEFAULT
        );
    }

    /**
     * @param int $oldPluginID
     * @param int $pluginID
     */
    private function updatePaymentMethods(int $oldPluginID, int $pluginID): void
    {
        $this->db->query(
            'UPDATE tpluginzahlungsartklasse
                SET kPlugin = ' . $oldPluginID . ",
                    cModulId = REPLACE(cModulId, 'kPlugin_" . $pluginID . "_', 'kPlugin_" . $oldPluginID . "_')
                WHERE kPlugin = " . $pluginID,
            ReturnType::AFFECTED_ROWS
        );
        $oldPaymentMethods = $this->db->queryPrepared(
            'SELECT kZahlungsart, cModulId
                FROM tzahlungsart
                WHERE cModulId LIKE :newID',
            ['newID' => 'kPlugin\_' . $oldPluginID . '\_%'],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $newPaymentMethods = $this->db->queryPrepared(
            'SELECT kZahlungsart, cModulId, cName
                FROM tzahlungsart
                WHERE cModulId LIKE :newID',
            ['newID' => 'kPlugin\_' . $pluginID . '\_%'],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $updatedMethods    = [];
        foreach ($oldPaymentMethods as $method) {
            $oldModuleID      = \str_replace(
                'kPlugin_' . $oldPluginID . '_',
                'kPlugin\_' . $pluginID . '\_',
                $method->cModulId
            );
            $newPaymentMethod = $this->db->queryPrepared(
                'SELECT kZahlungsart
                    FROM tzahlungsart
                    WHERE cModulId LIKE :oldID',
                ['oldID' => $oldModuleID],
                ReturnType::SINGLE_OBJECT
            );
            $setSQL           = '';
            if (isset($method->kZahlungsart, $newPaymentMethod->kZahlungsart)) {
                $this->db->query(
                    'DELETE tzahlungsart, tzahlungsartsprache
                        FROM tzahlungsart
                        JOIN tzahlungsartsprache
                            ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                        WHERE tzahlungsart.kZahlungsart = ' . $method->kZahlungsart,
                    ReturnType::AFFECTED_ROWS
                );
                $setSQL = ' , kZahlungsart = ' . $method->kZahlungsart;
                $upd    = (object)['kZahlungsart' => $method->kZahlungsart];
                $this->db->update('tzahlungsartsprache', 'kZahlungsart', $newPaymentMethod->kZahlungsart, $upd);
            }
            $this->db->queryPrepared(
                'UPDATE tzahlungsart
                    SET cModulId = :newID ' . $setSQL . '
                    WHERE cModulId LIKE :oldID',
                ['oldID' => $oldModuleID, 'newID' => $method->cModulId],
                ReturnType::AFFECTED_ROWS
            );
        }
        foreach ($newPaymentMethods as $method) {
            $newModuleID      = Helper::getModuleIDByPluginID($oldPluginID, $method->cName);
            $updatedMethods[] = $newModuleID;
            $this->db->queryPrepared(
                'UPDATE tzahlungsart
                    SET cModulId = :newID
                    WHERE kZahlungsart = :pmid',
                ['pmid' => $method->kZahlungsart, 'newID' => $newModuleID],
                ReturnType::AFFECTED_ROWS
            );
        }
        foreach ($oldPaymentMethods as $method) {
            if (!\in_array($method->cModulId, $updatedMethods, true)) {
                $this->db->delete('tzahlungsart', 'kZahlungsart', $method->kZahlungsart);
                $this->db->queryPrepared(
                    'DELETE FROM tplugineinstellungen
                        WHERE kPlugin = :pid AND cName LIKE :nm',
                    ['pid' => $oldPluginID, 'nm' => \str_replace('_', '\_', $method->cModulId) . '\_%'],
                    ReturnType::DEFAULT
                );
            }
        }
        $this->db->query(
            'DELETE FROM tzahlungsartsprache
                WHERE kZahlungsart NOT IN (
                    SELECT kZahlungsart FROM tzahlungsart
                )',
            ReturnType::DEFAULT
        );
        $this->db->query(
            'DELETE FROM tversandartzahlungsart
                WHERE kZahlungsart NOT IN (
                    SELECT kZahlungsart FROM tzahlungsart
                )',
            ReturnType::DEFAULT
        );
    }
}
