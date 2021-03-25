<?php

use JTL\Backend\DirManager;
use JTL\DB\ReturnType;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Helpers\Text;
use JTL\Session\Backend;
use JTL\Shop;
use JTL\Update\DBMigrationHelper;
use JTLShop\SemVer\Parser;

/**
 * @param bool $extended
 * @param bool $clearCache
 * @return array
 */
function getDBStruct(bool $extended = false, bool $clearCache = false)
{
    static $dbStruct = [
        'normal'   => null,
        'extended' => null,
    ];

    $db           = Shop::Container()->getDB();
    $cache        = Shop::Container()->getCache();
    $dbLocked     = [];
    $database     = $db->getConfig()['database'];
    $mysqlVersion = DBMigrationHelper::getMySQLVersion();

    if ($clearCache) {
        if ($cache->isActive()) {
            $cache->flushTags([CACHING_GROUP_CORE . '_getDBStruct']);
        } else {
            Backend::set('getDBStruct_extended', false);
            Backend::set('getDBStruct_normal', false);
        }
        $dbStruct['extended'] = null;
        $dbStruct['normal']   = null;
    }

    if ($extended) {
        $cacheID = 'getDBStruct_extended';
        if ($dbStruct['extended'] === null) {
            $dbStruct['extended'] = $cache->isActive()
                ? $cache->get($cacheID)
                : Backend::get($cacheID, false);
        }
        $dbStructure =& $dbStruct['extended'];

        if (version_compare($mysqlVersion->innodb->version, '5.6', '>=')) {
            $dbStatus = $db->queryPrepared(
                'SHOW OPEN TABLES
                    WHERE `Database` LIKE :schema',
                ['schema' => $database],
                ReturnType::ARRAY_OF_OBJECTS
            );
            if ($dbStatus) {
                foreach ($dbStatus as $oStatus) {
                    if ((int)$oStatus->In_use > 0) {
                        $dbLocked[$oStatus->Table] = 1;
                    }
                }
            }
        }
    } else {
        $cacheID = 'getDBStruct_normal';
        if ($dbStruct['normal'] === null) {
            $dbStruct['normal'] = $cache->isActive()
                ? $cache->get($cacheID)
                : Backend::get($cacheID);
        }
        $dbStructure =& $dbStruct['normal'];
    }

    if ($dbStructure === false) {
        $dbData = $db->queryPrepared(
            "SELECT t.`TABLE_NAME`, t.`ENGINE`, `TABLE_COLLATION`, t.`TABLE_ROWS`, t.`TABLE_COMMENT`,
                    t.`DATA_LENGTH` + t.`INDEX_LENGTH` AS DATA_SIZE,
                    COUNT(IF(c.DATA_TYPE = 'text', c.COLUMN_NAME, NULL)) TEXT_FIELDS,
                    COUNT(IF(c.DATA_TYPE = 'tinyint', c.COLUMN_NAME, NULL)) TINY_FIELDS,
                    COUNT(IF(c.COLLATION_NAME = 'utf8_unicode_ci', NULL, c.COLLATION_NAME)) FIELD_COLLATIONS
                FROM information_schema.TABLES t
                LEFT JOIN information_schema.COLUMNS c ON c.TABLE_NAME = t.TABLE_NAME
                    AND c.TABLE_SCHEMA = t.TABLE_SCHEMA
                    AND (c.DATA_TYPE = 'text'
                        OR (c.DATA_TYPE = 'tinyint' AND SUBSTRING(c.COLUMN_NAME, 1, 1) = 'k')
                        OR c.COLLATION_NAME != 'utf8_unicode_ci')
                WHERE t.`TABLE_SCHEMA` = :schema
                    AND t.`TABLE_NAME` NOT LIKE 'xplugin_%'
                GROUP BY t.`TABLE_NAME`, t.`ENGINE`, `TABLE_COLLATION`, t.`TABLE_ROWS`, t.`TABLE_COMMENT`,
                    t.`DATA_LENGTH` + t.`INDEX_LENGTH`
                ORDER BY t.`TABLE_NAME`",
            ['schema' => $database],
            ReturnType::ARRAY_OF_OBJECTS
        );

        foreach ($dbData as $data) {
            $table = $data->TABLE_NAME;
            if ($extended) {
                $dbStructure[$table]            = $data;
                $dbStructure[$table]->Columns   = [];
                $dbStructure[$table]->Migration = DBMigrationHelper::MIGRATE_NONE;

                if (version_compare($mysqlVersion->innodb->version, '5.6', '<')) {
                    $dbStructure[$table]->Locked = mb_strpos($data->TABLE_COMMENT, ':Migrating') !== false ? 1 : 0;
                } else {
                    $dbStructure[$table]->Locked = $dbLocked[$table] ?? 0;
                }
            } else {
                $dbStructure[$table] = [];
            }

            $columns = $db->queryPrepared(
                'SELECT `COLUMN_NAME`, `DATA_TYPE`, `COLUMN_TYPE`, `CHARACTER_SET_NAME`, `COLLATION_NAME`
                    FROM information_schema.COLUMNS
                    WHERE `TABLE_SCHEMA` = :schema
                        AND `TABLE_NAME` = :table
                    ORDER BY `ORDINAL_POSITION`',
                [
                    'schema' => $database,
                    'table'  => $table
                ],
                ReturnType::ARRAY_OF_OBJECTS
            );
            if ($columns !== false) {
                foreach ($columns as $column) {
                    if ($extended) {
                        $dbStructure[$table]->Columns[$column->COLUMN_NAME] = $column;
                    } else {
                        $dbStructure[$table][] = $column->COLUMN_NAME;
                    }
                }
            }
            if ($extended) {
                $dbStructure[$table]->Migration = DBMigrationHelper::isTableNeedMigration($data);
            }
        }
        if ($cache->isActive()) {
            $cache->set(
                $cacheID,
                $dbStructure,
                [CACHING_GROUP_CORE, CACHING_GROUP_CORE . '_getDBStruct']
            );
        } else {
            Backend::set($cacheID, $dbStructure);
        }
    } elseif ($extended) {
        foreach (array_keys($dbStructure) as $table) {
            $dbStructure[$table]->Locked = $dbLocked[$table] ?? 0;
        }
    }

    return $dbStructure;
}

/**
 * @return array
 */
function getDBFileStruct()
{
    $version    = Parser::parse(APPLICATION_VERSION);
    $versionStr = $version->getMajor() . '-' . $version->getMinor() . '-' . $version->getPatch();
    if ($version->hasPreRelease()) {
        $preRelease  = $version->getPreRelease();
        $versionStr .= '-' . $preRelease->getGreek();
        if ($preRelease->getReleaseNumber() > 0) {
            $versionStr .= '-' . $preRelease->getReleaseNumber();
        }
    }

    $fileList = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_SHOPMD5 . 'dbstruct_' . $versionStr . '.json';
    if (!file_exists($fileList)) {
        return [];
    }
    $struct = json_decode(file_get_contents($fileList), false);

    return is_object($struct) ? get_object_vars($struct) : $struct;
}

/**
 * @param string $msg
 * @param bool   $engineError
 * @return stdClass
 */
function createDBStructError(string $msg, bool $engineError = false): stdClass
{
    return (object)[
        'errMsg'        => $msg,
        'isEngineError' => $engineError,
    ];
}

/**
 * @param array $dbFileStruct
 * @param array $dbStruct
 * @return object[]
 */
function compareDBStruct(array $dbFileStruct, array $dbStruct): array
{
    $errors = [];
    foreach ($dbFileStruct as $table => $columns) {
        if (!array_key_exists($table, $dbStruct)) {
            $errors[$table] = createDBStructError(__('errorNoTable'));
            continue;
        }
        if (($dbStruct[$table]->Migration & DBMigrationHelper::MIGRATE_INNODB) === DBMigrationHelper::MIGRATE_INNODB) {
            $errors[$table] = createDBStructError($table . __('errorNoInnoTable'), true);
            continue;
        }
        if (($dbStruct[$table]->Migration & DBMigrationHelper::MIGRATE_UTF8) === DBMigrationHelper::MIGRATE_UTF8) {
            $errors[$table] = createDBStructError($table . __('errorWrongCollation'), true);
            continue;
        }

        foreach ($columns as $cColumn) {
            if (!in_array($cColumn, isset($dbStruct[$table]->Columns)
                ? array_keys($dbStruct[$table]->Columns)
                : $dbStruct[$table], true)
            ) {
                $errors[$table] = createDBStructError(sprintf(__('errorRowMissing'), $cColumn, $table));
                break;
            }

            if (isset($dbStruct[$table]->Columns[$cColumn])) {
                if (!empty($dbStruct[$table]->Columns[$cColumn]->COLLATION_NAME)
                    && $dbStruct[$table]->Columns[$cColumn]->COLLATION_NAME !== 'utf8_unicode_ci'
                ) {
                    $errors[$table] = createDBStructError(sprintf(__('errorWrongCollationRow'), $cColumn));
                    break;
                }
                if ($dbStruct[$table]->Columns[$cColumn]->DATA_TYPE === 'text') {
                    $errors[$table] = createDBStructError(sprintf(__('errorDataTypeTextInRow'), $cColumn), true);
                    break;
                }
                if ($dbStruct[$table]->Columns[$cColumn]->DATA_TYPE === 'tinyint'
                    && strpos($dbStruct[$table]->Columns[$cColumn]->COLUMN_NAME, 'k') === 0
                ) {
                    $errors[$table] = createDBStructError(sprintf(__('errorDataTypeTinyInRow'), $cColumn), true);
                    break;
                }
            }
        }
    }

    return $errors;
}

/**
 * @param string $action
 * @param array  $tables
 * @return array|bool
 */
function doDBMaintenance(string $action, array $tables)
{
    switch ($action) {
        case 'optimize':
            $cmd = 'OPTIMIZE TABLE ';
            break;
        case 'analyze':
            $cmd = 'ANALYZE TABLE ';
            break;
        case 'repair':
            $cmd = 'REPAIR TABLE ';
            break;
        case 'check':
            $cmd = 'CHECK TABLE ';
            break;
        default:
            return false;
    }

    return count($tables) > 0
        ? Shop::Container()->getDB()->query($cmd . implode(', ', $tables), ReturnType::ARRAY_OF_OBJECTS)
        : false;
}

/**
 * @param array $dbStruct
 * @return stdClass
 */
function determineEngineUpdate(array $dbStruct)
{
    $result             = new stdClass();
    $result->tableCount = 0;
    $result->dataSize   = 0;
    $result->estimated  = [];

    foreach ($dbStruct as $table => $meta) {
        if (isset($dbStruct[$table]->Migration)
            && $dbStruct[$table]->Migration !== DBMigrationHelper::MIGRATE_NONE
        ) {
            $result->tableCount++;
            $result->dataSize += $dbStruct[$table]->DATA_SIZE;
        }
    }

    $result->estimated = [
        $result->tableCount * 1.60 + $result->dataSize / 1048576 * 1.15,
        $result->tableCount * 2.40 + $result->dataSize / 1048576 * 2.50,
    ];

    return $result;
}

/**
 * @param string   $fileName
 * @param string[] $shopTables
 * @return string
 */
function doEngineUpdateScript(string $fileName, array $shopTables)
{
    $nl = "\r\n";

    $database    = Shop::Container()->getDB()->getConfig()['database'];
    $host        = Shop::Container()->getDB()->getConfig()['host'];
    $mysqlVer    = DBMigrationHelper::getMySQLVersion();
    $recreateFKs = '';

    $result  = '-- ' . $fileName . $nl;
    $result .= '-- ' . $nl;
    $result .= '-- @host: ' . $host . $nl;
    $result .= '-- @database: ' . $database . $nl;
    $result .= '-- @created: ' . date(DATE_RFC822) . $nl;
    $result .= '-- ' . $nl;
    $result .= '-- @important: !!! PLEASE MAKE A BACKUP OF STRUCTURE AND DATA FOR `' . $database . '` !!!' . $nl;
    $result .= '-- ' . $nl;
    $result .= $nl;
    $result .= '-- ---------------------------------------------------------' .
        '-------------------------------------------' . $nl;
    $result .= '-- ' . $nl;
    $result .= 'use `' . $database . '`;' . $nl;

    foreach (DBMigrationHelper::getTablesNeedMigration() as $table) {
        $fulltextSQL = [];
        $migration   = DBMigrationHelper::isTableNeedMigration($table);

        if (!in_array($table->TABLE_NAME, $shopTables, true)) {
            continue;
        }

        if (version_compare($mysqlVer->innodb->version, '5.6', '<')) {
            // Fulltext indizes are not supported for innoDB on MySQL < 5.6
            $fulltextIndizes = DBMigrationHelper::getFulltextIndizes($table->TABLE_NAME);

            if ($fulltextIndizes) {
                $result .= $nl . '--' . $nl;
                $result .= '-- remove fulltext indizes because there is no support for innoDB on MySQL < 5.6 ' . $nl;
                foreach ($fulltextIndizes as $fulltextIndex) {
                    $fulltextSQL[] = /** @lang text */
                        'ALTER TABLE `' . $table->TABLE_NAME . '` DROP KEY `' . $fulltextIndex->INDEX_NAME . '`';
                }
            }
        }

        if (($migration & DBMigrationHelper::MIGRATE_TABLE) !== DBMigrationHelper::MIGRATE_NONE) {
            $result .= $nl . '--' . $nl;
            if (($migration & DBMigrationHelper::MIGRATE_TABLE) === DBMigrationHelper::MIGRATE_TABLE) {
                $result .= '-- migrate engine and collation for ' . $table->TABLE_NAME . $nl;
            } elseif (($migration & DBMigrationHelper::MIGRATE_INNODB) === DBMigrationHelper::MIGRATE_INNODB) {
                $result .= '-- migrate engine for ' . $table->TABLE_NAME . $nl;
            } elseif (($migration & DBMigrationHelper::MIGRATE_UTF8) === DBMigrationHelper::MIGRATE_UTF8) {
                $result .= '-- migrate collation for ' . $table->TABLE_NAME . $nl;
            }
        } else {
            $result .= $nl;
        }

        if (count($fulltextSQL) > 0) {
            $result .= implode(';' . $nl, $fulltextSQL) . ';' . $nl;
        }

        $sql    = DBMigrationHelper::sqlMoveToInnoDB($table);
        $fkSQLs = DBMigrationHelper::sqlRecreateFKs($table->TABLE_NAME);
        if (!empty($sql)) {
            $result .= '--' . $nl;
            foreach ($fkSQLs->dropFK as $fkSQL) {
                $result .= $fkSQL . ';' . $nl;
            }
            $result .= $sql . ';' . $nl;
            foreach ($fkSQLs->createFK as $fkSQL) {
                $recreateFKs .= $fkSQL . ';' . $nl;
            }
        }

        $sql = DBMigrationHelper::sqlConvertUTF8($table, $nl);
        if (!empty($sql)) {
            $result .= '--' . $nl;
            $result .= '-- migrate collation and / or datatype for columns in ' . $table->TABLE_NAME . $nl;
            $result .= '--' . $nl;
            $result .= $sql . ';' . $nl;
        }
    }

    $result .= $nl;

    if (version_compare($mysqlVer->innodb->version, '5.6', '<')) {
        // Fulltext search is not available on MySQL < 5.6
        $result .= '--' . $nl;
        $result .= '-- Fulltext search is not available on MySQL < 5.6' . $nl;
        $result .= '--' . $nl;
        $result .= "UPDATE `teinstellungen` SET `cWert` = 'N' WHERE `cName` = 'suche_fulltext';" . $nl;
        $result .= $nl;
    }

    if (!empty($recreateFKs)) {
        $result .= '--' . $nl;
        $result .= '-- Recreate foreign keys' . $nl;
        $result .= '--' . $nl;
        $result .= $recreateFKs;
        $result .= $nl;
    }

    return $result;
}

/**
 * @param string $status
 * @param string $tableName
 * @param int    $step
 * @param array  $exclude
 * @return stdClass
 * @throws CircularReferenceException
 * @throws ServiceNotFoundException
 */
function doMigrateToInnoDB_utf8(string $status = 'start', string $tableName = '', int $step = 1, array $exclude = [])
{
    Shop::Container()->getGetText()->loadAdminLocale('pages/dbcheck');

    $mysqlVersion = DBMigrationHelper::getMySQLVersion();
    $tableName    = (string)Text::filterXSS($tableName);
    $result       = new stdClass();
    $db           = Shop::Container()->getDB();
    $doSingle     = false;

    switch (mb_convert_case($status, MB_CASE_LOWER)) {
        case 'stop':
            $result->nextTable = '';
            $result->status    = 'all done';
            break;
        case 'start':
            $shopTables = array_keys(getDBFileStruct());
            $table      = DBMigrationHelper::getNextTableNeedMigration($exclude);
            if ($table !== null && is_object($table)) {
                if (!in_array($table->TABLE_NAME, $shopTables, true)) {
                    $exclude[] = $table->TABLE_NAME;
                    $result    = doMigrateToInnoDB_utf8('start', '', 1, $exclude);
                } else {
                    $result->nextTable = $table->TABLE_NAME;
                    $result->nextStep  = 1;
                    $result->status    = 'migrate';
                }
            } else {
                $result = doMigrateToInnoDB_utf8('stop');
            }
            break;
        /** @noinspection PhpMissingBreakStatementInspection */
        case 'migrate_single':
            $doSingle = true;
        // no break
        case 'migrate':
            if (!empty($tableName) && $step === 1) {
                // Migration Step 1...
                $table     = DBMigrationHelper::getTable($tableName);
                $migration = DBMigrationHelper::isTableNeedMigration($table);
                if (is_object($table)
                    && $migration !== DBMigrationHelper::MIGRATE_NONE
                    && !in_array($table->TABLE_NAME, $exclude, true)
                ) {
                    if (!DBMigrationHelper::isTableInUse($tableName)) {
                        if (version_compare($mysqlVersion->innodb->version, '5.6', '<')) {
                            // If MySQL version is lower than 5.6 use alternative lock method
                            // and delete all fulltext indexes because these are not supported
                            $db->executeQuery(
                                DBMigrationHelper::sqlAddLockInfo($table),
                                ReturnType::QUERYSINGLE
                            );
                            $fulltextIndizes = DBMigrationHelper::getFulltextIndizes($table->TABLE_NAME);

                            if ($fulltextIndizes) {
                                foreach ($fulltextIndizes as $fulltextIndex) {
                                    $db->executeQuery(
                                        'ALTER TABLE `' . $table->TABLE_NAME . '`
                                            DROP KEY `' . $fulltextIndex->INDEX_NAME . '`',
                                        ReturnType::QUERYSINGLE
                                    );
                                }
                            }
                        }
                        if (($migration & DBMigrationHelper::MIGRATE_TABLE) !== 0) {
                            $fkSQLs = DBMigrationHelper::sqlRecreateFKs($table->TABLE_NAME);
                            foreach ($fkSQLs->dropFK as $fkSQL) {
                                $db->executeQuery($fkSQL, ReturnType::DEFAULT);
                            }
                            $migrate = $db->executeQuery(
                                DBMigrationHelper::sqlMoveToInnoDB($table),
                                ReturnType::DEFAULT
                            );
                            foreach ($fkSQLs->createFK as $fkSQL) {
                                $db->executeQuery($fkSQL, ReturnType::DEFAULT);
                            }
                        } else {
                            $migrate = true;
                        }
                        if ($migrate) {
                            $result->nextTable = $tableName;
                            $result->nextStep  = 2;
                            $result->status    = 'migrate';
                        } else {
                            $result->status = 'failure';
                        }
                        if (version_compare($mysqlVersion->innodb->version, '5.6', '<')) {
                            $db->executeQuery(
                                DBMigrationHelper::sqlClearLockInfo($table),
                                ReturnType::QUERYSINGLE
                            );
                        }
                    } else {
                        $result->status = 'in_use';
                    }
                } else {
                    // Get next table for migration...
                    $exclude[] = $tableName;
                    $result    = $doSingle
                        ? doMigrateToInnoDB_utf8('stop')
                        : doMigrateToInnoDB_utf8('start', '', 1, $exclude);
                }
            } elseif (!empty($tableName) && $step === 2) {
                // Migration Step 2...
                if (!DBMigrationHelper::isTableInUse($tableName)) {
                    $table = DBMigrationHelper::getTable($tableName);
                    $sql   = DBMigrationHelper::sqlConvertUTF8($table);

                    if (!empty($sql)) {
                        if ($db->executeQuery($sql, ReturnType::QUERYSINGLE)) {
                            // Get next table for migration...
                            $result = $doSingle
                                ? doMigrateToInnoDB_utf8('stop')
                                : doMigrateToInnoDB_utf8('start', '', 1, $exclude);
                        } else {
                            $result->status = 'failure';
                        }
                    } else {
                        // Get next table for migration...
                        $result = $doSingle
                            ? doMigrateToInnoDB_utf8('stop')
                            : doMigrateToInnoDB_utf8('start', '', 1, $exclude);
                    }
                } else {
                    $result->status = 'in_use';
                }
            }

            break;
        case 'clear cache':
            // Objektcache leeren
            try {
                $cache = Shop::Container()->getCache();
                if ($cache !== null) {
                    $cache->setJtlCacheConfig($db->selectAll('teinstellungen', 'kEinstellungenSektion', CONF_CACHING));
                    $cache->flushAll();
                }
            } catch (Exception $e) {
                Shop::Container()->getLogService()->error(
                    sprintf(__('errorEmptyCache'), $e->getMessage())
                );
            }
            $callback    = static function (array $pParameters) {
                if (!$pParameters['isdir']) {
                    @unlink($pParameters['path'] . $pParameters['filename']);
                } else {
                    @rmdir($pParameters['path'] . $pParameters['filename']);
                }
            };
            $templateDir = Shop::Container()->getTemplateService()->getActiveTemplate()->getDir();
            $dirMan      = new DirManager();
            $dirMan->getData(PFAD_ROOT . PFAD_COMPILEDIR . $templateDir, $callback);
            $dirMan->getData(PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR, $callback);
            // Clear special category session array
            unset($_SESSION['oKategorie_arr_new']);
            // Reset Fulltext search if version is lower than 5.6
            if (version_compare($mysqlVersion->innodb->version, '5.6', '<')) {
                $db->executeQuery(
                    "UPDATE `teinstellungen` 
                        SET `cWert` = 'N' 
                        WHERE `cName` = 'suche_fulltext'",
                    ReturnType::QUERYSINGLE
                );
            }
            $result->nextTable = '';
            $result->status    = 'finished';
            break;
    }

    return $result;
}
