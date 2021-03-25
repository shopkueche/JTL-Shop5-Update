<?php declare(strict_types=1);

namespace JTL\Cron\Job;

use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\DB\ReturnType;
use JTL\Media\IMedia;
use JTL\Media\Media;

/**
 * Class ImageCache
 * @package JTL\Cron\Job
 */
final class ImageCache extends Job
{
    /**
     * @var int
     */
    private $nextIndex = 0;

    /**
     * @inheritdoc
     */
    public function hydrate($data)
    {
        parent::hydrate($data);
        if (\JOBQUEUE_LIMIT_IMAGE_CACHE_IMAGES > 0) {
            $this->setLimit(\JOBQUEUE_LIMIT_IMAGE_CACHE_IMAGES);
        }

        return $this;
    }

    /**
     * @param int    $index
     * @param IMedia $instance
     * @return bool
     * @throws \Exception
     */
    private function generateImageCache(int $index, IMedia $instance): bool
    {
        $rendered = 0;
        $limit    = $this->getLimit();
        $uncached = $instance->getUncachedImageCount();
        $images   = $instance->getImages(true, $index, $limit);
        $totalAll = $instance->getTotalImageCount();
        $this->logger->debug(\sprintf('Uncached %s images: %d/%d', $instance::getType(), $uncached, $totalAll));
        if ($index >= $totalAll) {
            $index  = 0;
            $images = $instance->getImages(true, $index, $limit);
        }
        while (\count($images) === 0 && $index < $totalAll) {
            $index += $limit;
            $images = $instance->getImages(true, $index, $limit);
        }
        $thisRun = \count($images);
        foreach ($images as $image) {
            $instance->cacheImage($image);
            ++$index;
            ++$rendered;
            $this->logger->debug(\sprintf('generated image %d/%d', $rendered, $thisRun));
            if ($index % 10 === 0) {
                // this may be a long running loop without any db interaction - so query something from time to time
                $this->db->query('SELECT 1 AS avoidTimeout', ReturnType::DEFAULT);
            }
        }
        $this->logger->info(\sprintf('Generated cache for %d %s images', $rendered, $instance::getType()));
        $this->nextIndex = $uncached === 0 || ($uncached - $rendered === 0) ? 0 : $index;

        return $this->nextIndex === 0;
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $this->logger->debug(\sprintf('Generating image cache - max. %d', $this->getLimit()));
        $res = true;
        foreach (Media::getInstance()->getRegisteredClasses() as $type) {
            $res = $this->generateImageCache($queueEntry->tasksExecuted, $type) && $res;
        }
        $queueEntry->tasksExecuted = $this->nextIndex;
        $this->setFinished($res);

        return $this;
    }
}
