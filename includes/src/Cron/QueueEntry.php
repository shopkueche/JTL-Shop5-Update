<?php declare(strict_types=1);

namespace JTL\Cron;

use DateTime;
use stdClass;

/**
 * Class QueueEntry
 * @package JTL\Cron
 */
class QueueEntry
{
    /**
     * @var int
     */
    public $jobQueueID;

    /**
     * @var int
     */
    public $cronID;

    /**
     * @var int
     */
    public $foreignKeyID;

    /**
     * @var int
     */
    public $taskLimit;

    /**
     * @var int
     */
    public $tasksExecuted;

    /**
     * @var int
     */
    public $lastProductID;

    /**
     * @var int
     */
    public $isRunning;

    /**
     * @var string
     */
    public $jobType;

    /**
     * @var string
     */
    public $tableName;

    /**
     * @var string
     */
    public $foreignKey;

    /**
     * @var DateTime
     */
    public $startTime;

    /**
     * @var DateTime
     */
    public $lastStart;

    /**
     * @var DateTime
     */
    public $lastFinish;

    /**
     * compatability only
     *
     * @var int
     */
    public $nLimitN;

    /**
     * compatability only
     *
     * @var int
     */
    public $nLimitM;

    /**
     * QueueEntry constructor.
     * @param stdClass $data
     */
    public function __construct(stdClass $data)
    {
        $this->jobQueueID    = (int)$data->jobQueueID;
        $this->cronID        = (int)$data->cronID;
        $this->foreignKeyID  = (int)$data->foreignKeyID;
        $this->taskLimit     = (int)$data->taskLimit;
        $this->nLimitN       = (int)$data->tasksExecuted;
        $this->tasksExecuted = (int)$data->tasksExecuted;
        $this->nLimitM       = (int)$data->taskLimit;
        $this->lastProductID = (int)$data->lastProductID;
        $this->isRunning     = 0;
        $this->jobType       = $data->jobType;
        $this->tableName     = $data->tableName;
        $this->foreignKey    = $data->foreignKey;
        $this->startTime     = new DateTime($data->startTime ?? '');
        $this->lastStart     = new DateTime($data->lastStart ?? '');
        $this->lastFinish    = new DateTime($data->lastFinish ?? '');
    }
}
