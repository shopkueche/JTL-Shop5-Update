<?php

namespace JTL\Cron;

use JTL\Shop;
use stdClass;

/**
 * Class LegacyCron
 * @package JTL\Cron
 * @todo: finalize refactoring and remove this class
 */
class LegacyCron
{
    /**
     * @var int
     */
    public $kCron;

    /**
     * @var int
     */
    public $kKey;

    /**
     * @var int
     */
    public $nAlleXStd;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cTabelle;

    /**
     * @var string
     */
    public $cKey;

    /**
     * @var string
     */
    public $cJobArt;

    /**
     * @var string
     */
    public $dStart;

    /**
     * @var string
     */
    public $dStartZeit;

    /**
     * @var string
     */
    public $dLetzterStart;

    /**
     * @param int         $cronID
     * @param int         $key
     * @param int         $frequency
     * @param string      $name
     * @param string      $jobType
     * @param string      $table
     * @param string      $keyName
     * @param string|null $start
     * @param string|null $startTime
     * @param string|null $lastStart
     */
    public function __construct(
        int $cronID = 0,
        int $key = 0,
        int $frequency = 0,
        string $name = '',
        string $jobType = '',
        string $table = '',
        string $keyName = '',
        string $start = null,
        string $startTime = null,
        string $lastStart = null
    ) {
        $this->kCron         = $cronID;
        $this->kKey          = $key;
        $this->cKey          = $keyName;
        $this->cTabelle      = $table;
        $this->cName         = $name;
        $this->cJobArt       = $jobType;
        $this->nAlleXStd     = $frequency;
        $this->dStart        = $start;
        $this->dStartZeit    = $startTime;
        $this->dLetzterStart = $lastStart;
    }

    /**
     * @return array|bool
     */
    public function holeCronArt()
    {
        return ($this->kKey > 0 && \mb_strlen($this->cTabelle) > 0)
            ? Shop::Container()->getDB()->selectAll($this->cTabelle, $this->cKey, (int)$this->kKey)
            : false;
    }

    /**
     * @return int|bool
     */
    public function speicherInDB()
    {
        if ($this->kKey > 0 && $this->cKey && $this->cTabelle && $this->cName && $this->nAlleXStd && $this->dStart) {
            $ins               = new stdClass();
            $ins->foreignKeyID = $this->kKey;
            $ins->foreignKey   = $this->cKey;
            $ins->tableName    = $this->cTabelle;
            $ins->name         = $this->cName;
            $ins->jobType      = $this->cJobArt;
            $ins->frequency    = $this->nAlleXStd;
            $ins->startDate    = $this->dStart;
            $ins->startTime    = $this->dStartZeit;
            $ins->lastStart    = $this->dLetzterStart ?? '_DBNULL_';

            return Shop::Container()->getDB()->insert('tcron', $ins);
        }

        return false;
    }

    /**
     * @param string $cJobArt
     * @param string $dStart
     * @param int    $nLimitM
     * @return int|bool
     */
    public function speicherInJobQueue($cJobArt, $dStart, $nLimitM)
    {
        if ($dStart && $nLimitM > 0 && \mb_strlen($cJobArt) > 0) {
            $ins                = new stdClass();
            $ins->cronID        = $this->kCron;
            $ins->foreignKeyID  = $this->kKey;
            $ins->foreignKey    = $this->cKey;
            $ins->tableName     = $this->cTabelle;
            $ins->jobType       = $cJobArt;
            $ins->startTime     = $dStart;
            $ins->tasksExecuted = 0;
            $ins->taskLimit     = $nLimitM;
            $ins->isRunning     = 0;

            return Shop::Container()->getDB()->insert('tjobqueue', $ins);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function updateCronDB(): bool
    {
        if ($this->kCron > 0) {
            $upd               = new stdClass();
            $upd->foreignKeyID = (int)$this->kKey;
            $upd->foreignKey   = $this->cKey;
            $upd->tableName    = $this->cTabelle;
            $upd->name         = $this->cName;
            $upd->jobType      = $this->cJobArt;
            $upd->frequency    = (int)$this->nAlleXStd;
            $upd->startDate    = $this->dStart;
            $upd->lastStart    = $this->dLetzterStart ?? '_DBNULL';

            return Shop::Container()->getDB()->update('tcron', 'cronID', $this->kCron, $upd) >= 0;
        }

        return false;
    }
}
