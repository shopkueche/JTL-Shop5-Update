<?php declare(strict_types=1);

namespace JTL\Cron;

use InvalidArgumentException;
use JTL\Cache\JTLCacheInterface;
use JTL\Cron\Job\Dummy;
use JTL\DB\DbInterface;
use JTL\Mapper\JobTypeToJob;
use Psr\Log\LoggerInterface;

/**
 * Class JobFactory
 * @package JTL\Cron
 */
class JobFactory
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var JTLCacheInterface
     */
    protected $cache;

    /**
     * JobFactory constructor.
     * @param DbInterface       $db
     * @param LoggerInterface   $logger
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, LoggerInterface $logger, JTLCacheInterface $cache)
    {
        $this->db     = $db;
        $this->logger = $logger;
        $this->cache  = $cache;
    }

    /**
     * @param QueueEntry $data
     * @return JobInterface
     */
    public function create(QueueEntry $data): JobInterface
    {
        $mapper = new JobTypeToJob();
        try {
            $class = $mapper->map($data->jobType);
        } catch (InvalidArgumentException $e) {
            $class = Dummy::class;
        }
        $job = new $class($this->db, $this->logger, new JobHydrator(), $this->cache);
        /** @var JobInterface $job */
        $job->hydrate($data);

        return $job;
    }
}
