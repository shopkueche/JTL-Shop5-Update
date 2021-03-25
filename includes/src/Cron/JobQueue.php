<?php

namespace JTL\Cron;

use JTL\Shop;
use stdClass;

/**
 * Class JobQueue
 * @package JTL\Cron
 * @todo: finalize refactoring and remove this class
 */
class JobQueue
{
    /**
     * @var int
     */
    public $kJobQueue;

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
    public $nLimitN;

    /**
     * @var int
     */
    public $nLimitM;

    /**
     * @var int
     */
    public $nLastArticleID;

    /**
     * @var int
     */
    public $nInArbeit;

    /**
     * @var string
     */
    public $cJobArt;

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
    public $dStartZeit;

    /**
     * @var string
     */
    public $dZuletztGelaufen;

    /**
     * @return int|null
     */
    public function getKJobQueue(): ?int
    {
        return $this->kJobQueue;
    }

    /**
     * @param int|null $kJobQueue
     * @return $this
     */
    public function setKJobQueue($kJobQueue): self
    {
        $this->kJobQueue = $kJobQueue;

        return $this;
    }

    /**
     * @return int
     */
    public function getKCron(): int
    {
        return $this->kCron ?? 0;
    }

    /**
     * @param int $kCron
     * @return $this
     */
    public function setKCron(int $kCron): self
    {
        $this->kCron = $kCron;

        return $this;
    }

    /**
     * @return int
     */
    public function getKKey(): int
    {
        return $this->kKey ?? 0;
    }

    /**
     * @param int $kKey
     * @return $this
     */
    public function setKKey(int $kKey): self
    {
        $this->kKey = $kKey;

        return $this;
    }

    /**
     * @return int
     */
    public function getNLimitN(): int
    {
        return $this->nLimitN ?? 0;
    }

    /**
     * @param int $nLimitN
     * @return $this
     */
    public function setNLimitN(int $nLimitN): self
    {
        $this->nLimitN = $nLimitN;

        return $this;
    }

    /**
     * @return int
     */
    public function getNLimitM(): int
    {
        return $this->nLimitM ?? 0;
    }

    /**
     * @param int $nLimitM
     * @return $this
     */
    public function setNLimitM(int $nLimitM): self
    {
        $this->nLimitM = $nLimitM;

        return $this;
    }

    /**
     * @return int
     */
    public function getNLastArticleID(): int
    {
        return $this->nLastArticleID ?? 0;
    }

    /**
     * @param int $nLastArticleID
     * @return $this
     */
    public function setNLastArticleID(int $nLastArticleID): self
    {
        $this->nLastArticleID = $nLastArticleID;

        return $this;
    }

    /**
     * @return int
     */
    public function getNInArbeit(): int
    {
        return $this->nInArbeit ?? 0;
    }

    /**
     * @param int $nInArbeit
     * @return $this
     */
    public function setNInArbeit(int $nInArbeit): self
    {
        $this->nInArbeit = $nInArbeit;

        return $this;
    }

    /**
     * @return string
     */
    public function getCJobArt(): string
    {
        return $this->cJobArt ?? '';
    }

    /**
     * @param string $cJobArt
     * @return $this
     */
    public function setCJobArt(string $cJobArt): self
    {
        $this->cJobArt = $cJobArt;

        return $this;
    }

    /**
     * @return string
     */
    public function getCTabelle(): string
    {
        return $this->cTabelle ?? '';
    }

    /**
     * @param string $cTabelle
     * @return $this
     */
    public function setCTabelle($cTabelle): self
    {
        $this->cTabelle = $cTabelle;

        return $this;
    }

    /**
     * @return string
     */
    public function getCKey(): string
    {
        return $this->cKey ?? '';
    }

    /**
     * @param string $cKey
     */
    public function setCKey($cKey): void
    {
        $this->cKey = $cKey;
    }

    /**
     * @return string
     */
    public function getDStartZeit(): string
    {
        return $this->dStartZeit ?? 'NOW()';
    }

    /**
     * @param string $dStartZeit
     * @return $this
     */
    public function setDStartZeit($dStartZeit): self
    {
        $this->dStartZeit = $dStartZeit;

        return $this;
    }

    /**
     * @return string
     */
    public function getDZuletztGelaufen(): string
    {
        return $this->dZuletztGelaufen ?? '_DBNULL_';
    }

    /**
     * @param string $dZuletztGelaufen
     * @return $this
     */
    public function setDZuletztGelaufen($dZuletztGelaufen): self
    {
        $this->dZuletztGelaufen = $dZuletztGelaufen;

        return $this;
    }

    /**
     * @param int|null    $kJobQueue
     * @param int         $kCron
     * @param int         $kKey
     * @param int         $nLimitN
     * @param int         $nLimitM
     * @param int         $nInArbeit
     * @param string      $cJobArt
     * @param string      $cTabelle
     * @param string      $cKey
     * @param string      $dStartZeit
     * @param string|null $dZuletztGelaufen
     */
    public function __construct(
        int $kJobQueue = null,
        int $kCron = 0,
        int $kKey = 0,
        int $nLimitN = 0,
        int $nLimitM = 0,
        int $nInArbeit = 0,
        $cJobArt = '',
        $cTabelle = '',
        $cKey = '',
        $dStartZeit = 'NOW()',
        $dZuletztGelaufen = null
    ) {
        $this->kJobQueue        = $kJobQueue;
        $this->kCron            = $kCron;
        $this->kKey             = $kKey;
        $this->nLimitN          = $nLimitN;
        $this->nLimitM          = $nLimitM;
        $this->nLastArticleID   = 0;
        $this->nInArbeit        = $nInArbeit;
        $this->cJobArt          = $cJobArt;
        $this->cTabelle         = $cTabelle;
        $this->cKey             = $cKey;
        $this->dStartZeit       = $dStartZeit;
        $this->dZuletztGelaufen = $dZuletztGelaufen;
    }

    /**
     * @return stdClass|null
     */
    public function holeJobArt(): ?stdClass
    {
        if ($this->kKey > 0 && \mb_strlen($this->cTabelle) > 0) {
            return Shop::Container()->getDB()->select(
                $this->cTabelle,
                $this->cKey,
                (int)$this->kKey
            );
        }

        return null;
    }

    /**
     * @return int
     */
    public function speicherJobInDB(): int
    {
        if ($this->kKey > 0
            && $this->nLimitM > 0
            && \mb_strlen($this->cJobArt) > 0
            && \mb_strlen($this->cKey) > 0
            && \mb_strlen($this->cTabelle) > 0
            && \mb_strlen($this->dStartZeit) > 0
        ) {
            $ins                = new stdClass();
            $ins->cronID        = (int)$this->kCron;
            $ins->foreignKeyID  = (int)$this->kKey;
            $ins->tasksExecuted = (int)$this->nLimitN;
            $ins->taskLimit     = (int)$this->nLimitM;
            $ins->lastProductID = (int)$this->nLastArticleID;
            $ins->isRunning     = (int)$this->nInArbeit;
            $ins->jobType       = $this->cJobArt;
            $ins->tableName     = $this->cTabelle;
            $ins->foreignKey    = $this->cKey;
            $ins->startTime     = $this->dStartZeit;
            $ins->lastStart     = $this->dZuletztGelaufen ?? '_DBNULL_';

            return Shop::Container()->getDB()->insert('tjobqueue', $ins);
        }

        return 0;
    }

    /**
     * @return int
     */
    public function updateJobInDB(): int
    {
        if ($this->kJobQueue > 0) {
            $upd                = new stdClass();
            $upd->cronID        = (int)$this->kCron;
            $upd->foreignKeyID  = (int)$this->kKey;
            $upd->tasksExecuted = (int)$this->nLimitN;
            $upd->taskLimit     = (int)$this->nLimitM;
            $upd->lastProductID = (int)$this->nLastArticleID;
            $upd->isRunning     = (int)$this->nInArbeit;
            $upd->jobType       = $this->cJobArt;
            $upd->tableName     = $this->cTabelle;
            $upd->foreignKey    = $this->cKey;
            $upd->startTime     = $this->dStartZeit;
            $upd->lastStart     = $this->dZuletztGelaufen ?? '_DBNULL_';

            return Shop::Container()->getDB()->update('tjobqueue', 'jobQueueID', (int)$this->kJobQueue, $upd);
        }

        return 0;
    }

    /**
     * @return int
     */
    public function deleteJobInDB(): int
    {
        return $this->kJobQueue > 0
            ? Shop::Container()->getDB()->delete('tjobqueue', 'jobQueueID', (int)$this->kJobQueue)
            : 0;
    }
}
