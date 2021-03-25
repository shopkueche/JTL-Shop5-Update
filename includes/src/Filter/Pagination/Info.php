<?php declare(strict_types=1);

namespace JTL\Filter\Pagination;

use JTL\MagicCompatibilityTrait;

/**
 * Class Info
 * @package JTL\Filter\Pagination
 */
class Info
{
    use MagicCompatibilityTrait;

    /**
     * @var int
     */
    private $currentPage = 0;

    /**
     * @var int
     */
    private $totalPages = 0;

    /**
     * @var int
     */
    private $minPage = 0;

    /**
     * @var int
     */
    private $maxPage = 0;

    /**
     * @var array
     */
    public static $mapping = [
        'AktuelleSeite' => 'CurrentPage',
        'MaxSeiten'     => 'TotalPages',
        'minSeite'      => 'MinPage',
        'maxSeite'      => 'MaxPage',
    ];

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @param int $currentPage
     */
    public function setCurrentPage(int $currentPage): void
    {
        $this->currentPage = $currentPage;
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * @param int $totalPages
     */
    public function setTotalPages(int $totalPages): void
    {
        $this->totalPages = $totalPages;
    }

    /**
     * @return int
     */
    public function getMinPage(): int
    {
        return $this->minPage;
    }

    /**
     * @param int $minPage
     */
    public function setMinPage(int $minPage): void
    {
        $this->minPage = $minPage;
    }

    /**
     * @return int
     */
    public function getMaxPage(): int
    {
        return $this->maxPage;
    }

    /**
     * @param int $maxPage
     */
    public function setMaxPage(int $maxPage): void
    {
        $this->maxPage = $maxPage;
    }
}
