<?php declare(strict_types=1);

namespace JTL\Cron\Admin;

use DateTime;
use InvalidArgumentException;
use JTL\Cache\JTLCacheInterface;
use JTL\Cron\Job\Statusmail;
use JTL\Cron\JobHydrator;
use JTL\Cron\JobInterface;
use JTL\Cron\Type;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Events\Dispatcher;
use JTL\Events\Event;
use JTL\Mapper\JobTypeToJob;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class Controller
 * @package JTL\Cron\Admin
 */
final class Controller
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
     * @var JobHydrator
     */
    private $hydrator;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * Controller constructor.
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
    ) {
        $this->db       = $db;
        $this->logger   = $logger;
        $this->hydrator = $hydrator;
        $this->cache    = $cache;
    }

    /**
     * @param int $jobQueueId
     * @return int
     */
    public function resetQueueEntry(int $jobQueueId): int
    {
        return $this->db->update('tjobqueue', 'jobQueueID', $jobQueueId, (object)['isRunning' => 0]);
    }

    /**
     * @param int $cronId
     * @return int
     */
    public function deleteQueueEntry(int $cronId): int
    {
        $affected = (int)$this->db->queryPrepared(
            'DELETE FROM tjobqueue WHERE cronID = :id',
            ['id' => $cronId],
            ReturnType::AFFECTED_ROWS
        );

        return $affected + (int)$this->db->queryPrepared(
            'DELETE FROM tcron WHERE cronID = :id',
            ['id' => $cronId],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param array $post
     * @return int
     */
    public function addQueueEntry(array $post): int
    {
        $mapper = new JobTypeToJob();
        try {
            $class = $mapper->map($post['type']);
        } catch (InvalidArgumentException $e) {
            return -1;
        }
        if ($class === Statusmail::class) {
            $jobs  = $this->db->selectAll('tstatusemail', 'nAktiv', 1);
            $count = 0;
            foreach ($jobs as $job) {
                $ins               = new stdClass();
                $ins->frequency    = (int)$job->nInterval * 24;
                $ins->jobType      = $post['type'];
                $ins->name         = 'statusemail';
                $ins->tableName    = 'tstatusemail';
                $ins->foreignKey   = 'id';
                $ins->foreignKeyID = (int)$job->id;
                $ins->startTime    = \mb_strlen($post['time']) === 5 ? $post['time'] . ':00' : $post['time'];
                $ins->startDate    = (new DateTime($post['date']))->format('Y-m-d H:i:s');
                $this->db->insert('tcron', $ins);
                ++$count;
            }

            return $count;
        }
        $ins            = new stdClass();
        $ins->frequency = (int)$post['frequency'];
        $ins->jobType   = $post['type'];
        $ins->name      = 'manuell@' . \date('Y-m-d H:i:s');
        $ins->startTime = \mb_strlen($post['time']) === 5 ? $post['time'] . ':00' : $post['time'];
        $ins->startDate = (new DateTime($post['date']))->format('Y-m-d H:i:s');

        return $this->db->insert('tcron', $ins);
    }

    /**
     * @return string[]
     */
    public function getAvailableCronJobs(): array
    {
        $available = [
            Type::IMAGECACHE,
            Type::STATUSMAIL,
            Type::DATAPROTECTION,
        ];
        Dispatcher::getInstance()->fire(Event::GET_AVAILABLE_CRONJOBS, ['jobs' => &$available]);

        return $available;
    }

    /**
     * @return JobInterface[]
     */
    public function getJobs(): array
    {
        $jobs = [];
        $all  = $this->db->query(
            'SELECT tcron.*, tjobqueue.isRunning, tjobqueue.jobQueueID, texportformat.cName AS exportName
                FROM tcron
                LEFT JOIN tjobqueue
                    ON tcron.cronID = tjobqueue.cronID
                LEFT JOIN texportformat
                    ON texportformat.kExportformat = tcron.foreignKeyID
                    AND tcron.tableName = \'texportformat\'',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($all as $cron) {
            $cron->jobQueueID = (int)($cron->jobQueueID ?? 0);
            $cron->cronID     = (int)$cron->cronID;
            if ($cron->foreignKeyID !== null) {
                $cron->foreignKeyID = (int)$cron->foreignKeyID;
            }
            $cron->frequency = (int)$cron->frequency;
            $cron->isRunning = (bool)$cron->isRunning;
            $mapper          = new JobTypeToJob();
            try {
                $class = $mapper->map($cron->jobType);
                $job   = new $class($this->db, $this->logger, $this->hydrator, $this->cache);
                /** @var JobInterface $job */
                $jobs[] = $job->hydrate($cron);
            } catch (InvalidArgumentException $e) {
                $this->logger->info('Invalid cron job found: ' . $cron->jobType);
            }
        }

        return $jobs;
    }
}
