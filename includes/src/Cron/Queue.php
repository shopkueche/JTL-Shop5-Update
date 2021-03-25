<?php declare(strict_types=1);

namespace JTL\Cron;

use DateTime;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class Queue
 * @package JTL\Cron
 */
class Queue
{
    /**
     * @var QueueEntry[]
     */
    private $queueEntries = [];

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JobFactory
     */
    private $factory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Queue constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     * @param JobFactory      $factory
     */
    public function __construct(DbInterface $db, LoggerInterface $logger, JobFactory $factory)
    {
        $this->db      = $db;
        $this->logger  = $logger;
        $this->factory = $factory;
    }

    /**
     * @return QueueEntry[]
     */
    public function loadQueueFromDB(): array
    {
        $queueData = $this->db->query(
            'SELECT *
                FROM tjobqueue
                WHERE isRunning = 0
                    AND startTime <= NOW()',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($queueData as $entry) {
            $this->queueEntries[] = new QueueEntry($entry);
        }
        $this->logger->debug(\sprintf('Loaded %d existing job(s).', \count($this->queueEntries)));

        return $this->queueEntries;
    }

    /**
     * @return int
     */
    public function unStuckQueues(): int
    {
        return $this->db->query(
            'UPDATE tjobqueue
                SET isRunning = 0
                WHERE isRunning = 1
                    AND startTime <= NOW()
                    AND lastStart IS NOT NULL
                    AND DATE_SUB(CURTIME(), INTERVAL ' . \QUEUE_MAX_STUCK_HOURS . ' Hour) > lastStart',
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param stdClass[] $jobs
     */
    public function enqueueCronJobs(array $jobs): void
    {
        foreach ($jobs as $job) {
            $queueEntry                = new stdClass();
            $queueEntry->cronID        = $job->cronID;
            $queueEntry->foreignKeyID  = $job->foreignKeyID ?? '_DBNULL_';
            $queueEntry->foreignKey    = $job->foreignKey ?? '_DBNULL_';
            $queueEntry->tableName     = $job->tableName;
            $queueEntry->jobType       = $job->jobType;
            $queueEntry->startTime     = 'NOW()';
            $queueEntry->taskLimit     = 0;
            $queueEntry->tasksExecuted = 0;
            $queueEntry->isRunning     = 0;

            $this->db->insert('tjobqueue', $queueEntry);
        }
    }

    /**
     * @param Checker $checker
     * @throws \Exception
     */
    public function run(Checker $checker): void
    {
        if ($checker->isLocked()) {
            $this->logger->debug('Cron currently locked');
            exit;
        }
        $checker->lock();
        $this->enqueueCronJobs($checker->check());
        $affected = $this->unStuckQueues();
        if ($affected > 0) {
            $this->logger->debug(\sprintf('Unstuck %d job(s).', $affected));
        }
        $this->loadQueueFromDB();
        foreach ($this->queueEntries as $i => $queueEntry) {
            if ($i >= \JOBQUEUE_LIMIT_JOBS) {
                $this->logger->debug(\sprintf('Job limit reached after %d jobs.', \JOBQUEUE_LIMIT_JOBS));
                break;
            }
            $job                       = $this->factory->create($queueEntry);
            $queueEntry->tasksExecuted = $job->getExecuted();
            $queueEntry->taskLimit     = $job->getLimit();
            $queueEntry->isRunning     = 1;
            $this->logger->notice('Got job ' . \get_class($job)
                . ' (ID = ' . $job->getCronID()
                . ', type = ' . $job->getType() . ')');
            $job->start($queueEntry);
            $queueEntry->isRunning = 0;
            $queueEntry->lastStart = new DateTime();
            $this->db->update(
                'tcron',
                'cronID',
                $job->getCronID(),
                (object)['lastFinish' => $queueEntry->lastFinish->format('Y-m-d H:i')]
            );
            \executeHook(\HOOK_JOBQUEUE_INC_BEHIND_SWITCH, [
                'oJobQueue' => $queueEntry,
                'job'       => $job,
                'logger'    => $this->logger
            ]);
            $job->saveProgress($queueEntry);
            if ($job->isFinished()) {
                $this->logger->notice('Job ' . $job->getID() . ' successfully finished.');
                $job->delete();
            }
        }
        $checker->unlock();
    }
}
