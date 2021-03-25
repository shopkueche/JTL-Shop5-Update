<?php declare(strict_types=1);

namespace JTL\Cron;

use DateTime;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use Psr\Log\LoggerInterface;

/**
 * Interface JobInterface
 * @package JTL\Cron
 */
interface JobInterface
{
    /**
     * JobInterface constructor.
     * @param DbInterface       $db
     * @param LoggerInterface   $logger
     * @param JobHydrator       $hydrator
     * @param JTLCacheInterface $cache
     */
    public function __construct(
        DbInterface $db,
        LoggerInterface $logger,
        JobHydrator $hydrator,
        JTLCacheInterface $cache
    );

    /**
     * @return int
     */
    public function insert(): int;

    /**
     * @return bool
     */
    public function delete(): bool;

    /**
     * @param QueueEntry $queueEntry
     * @return bool
     */
    public function saveProgress(QueueEntry $queueEntry): bool;

    /**
     * @param QueueEntry|\stdClass $data
     * @return object
     */
    public function hydrate($data);

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     */
    public function setType(string $type): void;

    /**
     * @return int
     */
    public function getLimit(): int;

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void;

    /**
     * @return int
     */
    public function getID(): int;

    /**
     * @param int $id
     */
    public function setID(int $id): void;

    /**
     * @return DateTime|null
     */
    public function getDateLastStarted(): ?DateTime;

    /**
     * @param DateTime|string|null $date
     */
    public function setDateLastStarted($date): void;

    /**
     * @return DateTime|null
     */
    public function getDateLastFinished(): ?DateTime;

    /**
     * @param DateTime|string $date
     */
    public function setDateLastFinished($date): void;

    /**
     * @param string|null $date
     */
    public function setLastStarted(?string $date): void;

    /**
     * @return DateTime|null
     */
    public function getStartTime(): ?DateTime;

    /**
     * @param DateTime|string|null $startTime
     */
    public function setStartTime($startTime): void;

    /**
     * @return DateTime
     */
    public function getStartDate(): ?DateTime;

    /**
     * @param DateTime|string $date
     */
    public function setStartDate($date): void;

    /**
     * @return int|null
     */
    public function getForeignKeyID(): ?int;

    /**
     * @param int|null $foreignKeyID
     */
    public function setForeignKeyID(?int $foreignKeyID): void;

    /**
     * @return string|null
     */
    public function getForeignKey(): ?string;

    /**
     * @param string|null $foreignKey
     */
    public function setForeignKey(?string $foreignKey): void;

    /**
     * @return string|null
     */
    public function getTableName(): ?string;

    /**
     * @param string|null $table
     */
    public function setTableName(?string $table): void;

    /**
     * @param QueueEntry $queueEntry
     * @return JobInterface
     */
    public function start(QueueEntry $queueEntry): JobInterface;

    /**
     * @return int
     */
    public function getExecuted(): int;

    /**
     * @param int $executed
     */
    public function setExecuted(int $executed): void;

    /**
     * @return int
     */
    public function getCronID(): int;

    /**
     * @param int $cronID
     */
    public function setCronID(int $cronID): void;

    /**
     * @return bool
     */
    public function isFinished(): bool;

    /**
     * @param bool $finished
     */
    public function setFinished(bool $finished): void;

    /**
     * @return bool
     */
    public function isRunning(): bool;

    /**
     * @param bool $running
     */
    public function setRunning(bool $running): void;

    /**
     * @return int
     */
    public function getFrequency(): int;

    /**
     * @param int $frequency
     */
    public function setFrequency(int $frequency): void;

    /**
     * @return int
     */
    public function getQueueID(): int;

    /**
     * @param int $queueID
     */
    public function setQueueID(int $queueID): void;
}
