<?php declare(strict_types=1);

namespace JTL\Cron;

use DateTime;
use JTL\Cache\ICachingMethod;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class Job
 * @package JTL\Cron
 */
abstract class Job implements JobInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var int
     */
    private $limit = 100;

    /**
     * @var int
     */
    private $executed = 0;

    /**
     * @var int
     */
    private $cronID = 0;

    /**
     * @var int
     */
    private $queueID = 0;

    /**
     * @var int|null
     */
    private $foreignKeyID;

    /**
     * @var string
     */
    private $foreignKey = '';

    /**
     * @var DateTime
     */
    private $dateLastStarted;

    /**
     * @var DateTime
     */
    private $dateLastFinished;

    /**
     * @var DateTime
     */
    private $startTime;

    /**
     * @var DateTime
     */
    private $startDate;

    /**
     * @var string
     */
    private $tableName = '';

    /**
     * @var bool
     */
    private $finished = false;

    /**
     * @var bool
     */
    private $running = false;

    /**
     * @var int
     */
    private $frequency = 24;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var JobHydrator
     */
    protected $hydrator;

    /**
     * @var ICachingMethod
     */
    protected $cache;

    /**
     * @inheritdoc
     */
    public function __construct(
        DbInterface $db,
        LoggerInterface $logger,
        JobHydrator $hydrator,
        JTLCacheInterface $cache
    ) {
        $this->db       = $db;
        $this->logger   = $logger;
        $this->hydrator = $hydrator;
        $this->cache    = $cache;
    }

    /**
     * @inheritdoc
     */
    public function insert(): int
    {
        $ins               = new stdClass();
        $ins->foreignKeyID = $this->getForeignKeyID() ?? '_DBNULL_';
        $ins->foreignKey   = $this->getForeignKey() ?? '_DBNULL_';
        $ins->tableName    = $this->getTableName() ?? '_DBNULL_';
        $ins->name         = $this->getName();
        $ins->jobType      = $this->getType();
        $ins->frequency    = $this->getFrequency();
        $ins->startDate    = $this->getStartDate() === null
            ? '_DBNULL_'
            : $this->getStartDate()->format('Y-m-d H:i');
        $ins->startTime    = $this->getStartTime() === null
            ? '_DBNULL_'
            : $this->getStartTime()->format('H:i:s');
        $ins->lastStart    = $this->getDateLastStarted() === null
            ? '_DBNULL_'
            : $this->getDateLastStarted()->format('Y-m-d H:i');
        $ins->lastFinish   = $this->getDateLastFinished() === null
            ? '_DBNULL_'
            : $this->getDateLastFinished()->format('Y-m-d H:i');

        $this->setCronID($this->db->insert('tcron', $ins));

        return $this->getCronID();
    }

    /**
     * @inheritdoc
     */
    public function delete(): bool
    {
        return $this->db->delete('tjobqueue', 'cronID', $this->getCronID()) > 0;
    }

    /**
     * @param QueueEntry $queueEntry
     * @return bool
     */
    public function saveProgress(QueueEntry $queueEntry): bool
    {
        $upd                = new stdClass();
        $upd->taskLimit     = $queueEntry->taskLimit;
        $upd->tasksExecuted = $queueEntry->tasksExecuted;
        $upd->lastProductID = $queueEntry->lastProductID;
        $upd->lastFinish    = 'NOW()';
        $upd->isRunning     = 0;

        return $this->getCronID() > 0
            ? $this->db->update('tjobqueue', 'cronID', $this->getCronID(), $upd) >= 0
            : $this->db->update('tjobqueue', 'jobQueueID', $queueEntry->jobQueueID, $upd) >= 0;
    }

    /**
     * @inheritdoc
     */
    public function hydrate($data)
    {
        return $this->hydrator->hydrate($this, $data);
    }

    /**
     * @return stdClass|null
     */
    protected function getJobData(): ?stdClass
    {
        return $this->getForeignKeyID() > 0 && $this->getForeignKey() !== '' && $this->getTableName() !== ''
            ? $this->db->select(
                $this->getTableName(),
                $this->getForeignKey(),
                $this->getForeignKeyID()
            )
            : null;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @inheritdoc
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @inheritdoc
     */
    public function getID(): int
    {
        return $this->cronID;
    }

    /**
     * @inheritdoc
     */
    public function setID(int $id): void
    {
        $this->cronID = $id;
    }

    /**
     * @inheritdoc
     */
    public function getDateLastStarted(): ?DateTime
    {
        return $this->dateLastStarted;
    }

    /**
     * @inheritdoc
     */
    public function setDateLastStarted($date): void
    {
        $this->dateLastStarted = \is_string($date)
            ? new DateTime($date)
            : $date;
    }

    /**
     * @inheritdoc
     */
    public function getDateLastFinished(): ?DateTime
    {
        return $this->dateLastFinished;
    }

    /**
     * @inheritdoc
     */
    public function setDateLastFinished($date): void
    {
        $this->dateLastFinished = \is_string($date)
            ? new DateTime($date)
            : $date;
    }

    /**
     * @param string|null $date
     */
    public function setLastStarted(?string $date): void
    {
        $this->dateLastStarted = $date === null ? null : new DateTime($date);
    }

    /**
     * @inheritdoc
     */
    public function getStartTime(): ?DateTime
    {
        return $this->startTime;
    }

    /**
     * @inheritdoc
     */
    public function setStartTime($startTime): void
    {
        $this->startTime = \is_string($startTime)
            ? new DateTime($startTime)
            : $startTime;
    }

    /**
     * @inheritdoc
     */
    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    /**
     * @inheritdoc
     */
    public function setStartDate($date): void
    {
        $this->startDate = \is_string($date)
            ? new DateTime($date)
            : $date;
    }

    /**
     * @inheritdoc
     */
    public function getForeignKeyID(): ?int
    {
        return $this->foreignKeyID;
    }

    /**
     * @inheritdoc
     */
    public function setForeignKeyID(?int $foreignKeyID): void
    {
        $this->foreignKeyID = $foreignKeyID;
    }

    /**
     * @inheritdoc
     */
    public function getForeignKey(): ?string
    {
        return $this->foreignKey;
    }

    /**
     * @inheritdoc
     */
    public function setForeignKey(?string $foreignKey): void
    {
        $this->foreignKey = $foreignKey;
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    /**
     * @inheritdoc
     */
    public function setTableName(?string $table): void
    {
        $this->tableName = $table;
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        $this->setDateLastStarted(new DateTime());
        $this->db->update(
            'tjobqueue',
            'jobQueueID',
            $queueEntry->jobQueueID,
            (object)['isRunning' => $queueEntry->isRunning, 'lastStart' => 'NOW()']
        );
        $this->db->update(
            'tcron',
            'cronID',
            $queueEntry->cronID,
            (object)['lastStart' => 'NOW()']
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExecuted(): int
    {
        return $this->executed;
    }

    /**
     * @inheritdoc
     */
    public function setExecuted(int $executed): void
    {
        $this->executed = $executed;
    }

    /**
     * @inheritdoc
     */
    public function getCronID(): int
    {
        return $this->cronID;
    }

    /**
     * @inheritdoc
     */
    public function setCronID(int $cronID): void
    {
        $this->cronID = $cronID;
    }

    /**
     * @inheritdoc
     */
    public function isFinished(): bool
    {
        return $this->finished;
    }

    /**
     * @inheritdoc
     */
    public function setFinished(bool $finished): void
    {
        $this->finished = $finished;
    }

    /**
     * @inheritdoc
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * @inheritdoc
     */
    public function setRunning(bool $running): void
    {
        $this->running = $running;
    }

    /**
     * @inheritdoc
     */
    public function getFrequency(): int
    {
        return $this->frequency;
    }

    /**
     * @inheritdoc
     */
    public function setFrequency(int $frequency): void
    {
        $this->frequency = $frequency;
    }

    /**
     * @inheritdoc
     */
    public function getQueueID(): int
    {
        return $this->queueID;
    }

    /**
     * @inheritdoc
     */
    public function setQueueID(int $queueID): void
    {
        $this->queueID = $queueID;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res = \get_object_vars($this);
        unset($res['db'], $res['logger']);

        return $res;
    }
}
