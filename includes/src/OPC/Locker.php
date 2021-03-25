<?php declare(strict_types=1);

namespace JTL\OPC;

/**
 * Class Locker
 * @package JTL\OPC
 */
class Locker
{
    /**
     * @var PageDB
     */
    protected $pageDB;

    /**
     * Locker constructor.
     * @param PageDB $pageDB
     */
    public function __construct(PageDB $pageDB)
    {
        $this->pageDB = $pageDB;
    }

    /**
     * Try to lock draft to only be manipulated by this one user
     *
     * @param string $userName
     * @param Page   $page
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function lock(string $userName, Page $page): bool
    {
        if ($userName === '') {
            throw new \InvalidArgumentException('Name of the user that locks this page is empty.');
        }

        $lockedBy = $page->getLockedBy();
        $lockedAt = $page->getLockedAt();

        if ($lockedBy !== '' && $lockedBy !== $userName && \strtotime($lockedAt) + 60 > \time()) {
            return false;
        }

        $page
            ->setLockedBy($userName)
            ->setLockedAt(\date('Y-m-d H:i:s'));

        $this->pageDB->saveDraftLockStatus($page);

        return true;
    }

    /**
     * Unlock this draft if it was locked
     *
     * @param Page $page
     * @throws \Exception
     */
    public function unlock(Page $page): void
    {
        $page->setLockedBy('');
        $this->pageDB->saveDraftLockStatus($page);
    }
}
