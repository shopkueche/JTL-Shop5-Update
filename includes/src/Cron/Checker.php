<?php declare(strict_types=1);

namespace JTL\Cron;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use Psr\Log\LoggerInterface;

/**
 * Class Checker
 * @package JTL\Cron
 */
class Checker
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var resource|bool
     */
    private $filePointer;

    /**
     * Checker constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     */
    public function __construct(DbInterface $db, LoggerInterface $logger)
    {
        if (!\file_exists(\JOBQUEUE_LOCKFILE)) {
            \touch(\JOBQUEUE_LOCKFILE);
        }
        $this->db          = $db;
        $this->logger      = $logger;
        $this->filePointer = \fopen(\JOBQUEUE_LOCKFILE, 'rb');
    }

    public function __destruct()
    {
        \fclose($this->filePointer);
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        if ($this->filePointer === false || $this->lock()) {
            return false;
        }
        $this->unlock();

        return true;
    }

    /**
     * @return bool
     */
    public function lock(): bool
    {
        return \flock($this->filePointer, \LOCK_EX | \LOCK_NB);
    }

    /**
     * @return bool
     */
    public function unlock(): bool
    {
        return \flock($this->filePointer, \LOCK_UN);
    }

    /**
     * @return \stdClass[]
     */
    public function check(): array
    {
        $jobs = $this->db->query(
            "SELECT tcron.*
                FROM tcron
                LEFT JOIN tjobqueue 
                    ON tjobqueue.cronID = tcron.cronID
                WHERE (tcron.lastStart IS NULL 
                    OR IF(tcron.jobType = 'statusemail' AND tcron.frequency = 720,
                          MONTH(tcron.lastStart) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH),
                          (NOW() > ADDDATE(tcron.lastStart, INTERVAL tcron.frequency HOUR))
                        )
                    )
                    AND tcron.startDate < NOW()
                    AND tjobqueue.jobQueueID IS NULL",
            ReturnType::ARRAY_OF_OBJECTS
        );
        $this->logger->debug(\sprintf('Found %d new cron jobs.', \count($jobs)));

        return $jobs;
    }
}
