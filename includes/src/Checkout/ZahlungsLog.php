<?php

namespace JTL\Checkout;

use JTL\DB\ReturnType;
use JTL\Shop;
use stdClass;

/**
 * Class ZahlungsLog
 * @package JTL\Checkout
 */
class ZahlungsLog
{
    /**
     * @var string
     */
    public $cModulId;

    /**
     * @var array
     */
    public $oLog_arr = [];

    /**
     * @var int
     */
    public $nEingangAnzahl = 0;

    /**
     * @var bool
     */
    public $hasError = false;

    /**
     * @param string $moduleID
     */
    public function __construct(string $moduleID)
    {
        $this->cModulId = $moduleID;
    }

    /**
     * @param string $limit
     * @param int    $level
     * @param string $whereSQL
     * @return array
     */
    public function holeLog(string $limit, int $level = -1, string $whereSQL = ''): array
    {
        $condition = $level >= 0 ? ('AND nLevel = ' . $level) : '';

        return Shop::Container()->getDB()->query(
            "SELECT * FROM tzahlungslog
                WHERE cModulId = '" . $this->cModulId . "' " .
            $condition . ($whereSQL !== '' ? ' AND ' . $whereSQL : '') . '
                ORDER BY dDatum DESC, kZahlunglog DESC 
                LIMIT ' . $limit,
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @return int
     */
    public function logCount(): int
    {
        $oCount = Shop::Container()->getDB()->queryPrepared(
            'SELECT COUNT(*) AS nCount 
                FROM tzahlungslog 
                WHERE cModulId = :module',
            ['module' => $this->cModulId],
            ReturnType::SINGLE_OBJECT
        );

        return (int)$oCount->nCount;
    }

    /**
     * @return int
     */
    public function loeschen(): int
    {
        return Shop::Container()->getDB()->delete('tzahlungslog', 'cModulId', $this->cModulId);
    }

    /**
     * @param string $cLog
     * @return int
     */
    public function log($cLog): int
    {
        return self::add($this->cModulId, $cLog);
    }

    /**
     * @param string $cModulId
     * @param string $cLog
     * @param string $cLogData
     * @param int    $nLevel
     * @return int
     */
    public static function add($cModulId, $cLog, $cLogData = '', $nLevel = \LOGLEVEL_ERROR): int
    {
        if (\mb_strlen($cModulId) === 0) {
            return 0;
        }

        $log           = new stdClass();
        $log->cModulId = $cModulId;
        $log->cLog     = $cLog;
        $log->cLogData = $cLogData;
        $log->nLevel   = $nLevel;
        $log->dDatum   = 'NOW()';

        return Shop::Container()->getDB()->insert('tzahlungslog', $log);
    }

    /**
     * @param array $moduleIDs
     * @param int   $offset
     * @param int   $limit
     * @param int   $level
     * @return array
     */
    public static function getLog($moduleIDs, int $offset = 0, int $limit = 100, int $level = -1): array
    {
        if (!\is_array($moduleIDs)) {
            $moduleIDs = (array)$moduleIDs;
        }
        \array_walk($moduleIDs, static function (&$value) {
            $value = \sprintf("'%s'", $value);
        });
        $moduleIDlist = \implode(',', $moduleIDs);
        $where        = ($level >= 0) ? ('AND nLevel = ' . $level) : '';

        return Shop::Container()->getDB()->query(
            'SELECT * FROM tzahlungslog
                WHERE cModulId IN(' . $moduleIDlist . ') ' . $where . '
                ORDER BY dDatum DESC, kZahlunglog DESC 
                LIMIT ' . $offset . ', ' . $limit,
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param string $moduleID
     * @param int    $level
     * @param string $whereSQL
     * @return int
     */
    public static function count(string $moduleID, int $level = -1, string $whereSQL = ''): int
    {
        if ($level === -1) {
            $count = Shop::Container()->getDB()->queryPrepared(
                'SELECT COUNT(*) AS count 
                    FROM tzahlungslog 
                    WHERE cModulId = :cModulId ' . ($whereSQL !== '' ? ' AND ' . $whereSQL : ''),
                ['cModulId' => $moduleID],
                ReturnType::SINGLE_OBJECT
            )->count;
        } else {
            $count = Shop::Container()->getDB()->queryPrepared(
                'SELECT COUNT(*) AS count 
                    FROM tzahlungslog 
                    WHERE cModulId = :cModulId 
                        AND nLevel = :nLevel ' . ($whereSQL !== '' ? ' AND ' . $whereSQL : ''),
                ['nLevel' => $level, 'cModulId' => $moduleID],
                ReturnType::SINGLE_OBJECT
            )->count;
        }

        return (int)$count;
    }
}
