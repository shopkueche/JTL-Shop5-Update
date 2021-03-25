<?php declare(strict_types=1);

namespace JTL\Filter\Pagination;

use JTL\MagicCompatibilityTrait;

/**
 * Class Item
 * @package JTL\Filter\Pagination
 */
class Item
{
    use MagicCompatibilityTrait;

    /**
     * @var int
     */
    private $page = 0;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @var bool
     */
    private $isActive = false;

    /**
     * @var int|null - compatibility only
     */
    public $nBTN;

    /**
     * @var array
     */
    public static $mapping = [
        'cURL'   => 'URL',
        'page'   => 'PageNumber',
        'nSeite' => 'PageNumber',
    ];

    /**
     * @return int
     */
    public function getPageNumber(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPageNumber(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return string|null
     */
    public function getURL(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     */
    public function setURL($url): void
    {
        $this->url = $url;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }
}
