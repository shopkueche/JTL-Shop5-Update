<?php declare(strict_types=1);

namespace JTL\Filter\Pagination;

use JTL\Filter\ProductFilter;

/**
 * Class Pagination
 * @package JTL\Filter\Pagination
 */
class Pagination
{
    /**
     * @var ProductFilter
     */
    private $productFilter;

    /**
     * @var ItemFactory
     */
    private $factory;

    /**
     * @var array
     */
    private $pages = [];

    /**
     * @var Item
     */
    private $prev;

    /**
     * @var Item
     */
    private $next;

    /**
     * @var array
     */
    public static $mapping = [
        'zurueck' => 'Prev',
        'vor'     => 'Next',
    ];

    /**
     * Pagination constructor.
     * @param ProductFilter $productFilter
     * @param ItemFactory   $factory
     */
    public function __construct(ProductFilter $productFilter, ItemFactory $factory)
    {
        $this->productFilter = $productFilter;
        $this->factory       = $factory;
        $this->prev          = $this->factory->create();
        $this->next          = $this->factory->create();
    }

    /**
     * @param Info $pages
     * @return array
     */
    public function create(Info $pages): array
    {
        if ($pages->getTotalPages() < 2 || $pages->getCurrentPage() === 0) {
            return $this->pages;
        }
        $naviURL = $this->productFilter->getFilterURL()->getURL();
        $sep     = \mb_strpos($naviURL, '?') === false
            ? \SEP_SEITE
            : '&amp;seite=';
        $active  = $pages->getCurrentPage();
        $from    = $pages->getMinPage();
        $to      = $pages->getMaxPage();
        $current = $from;
        while ($current <= $to) {
            $page = $this->factory->create();
            $page->setPageNumber($current);
            $page->setURL($naviURL . $sep . $current);
            $page->setIsActive($current === $active);
            $this->pages[] = $page;
            if ($current === $active - 1) {
                $this->prev = clone $page;
            } elseif ($current === $active + 1) {
                $this->next = clone $page;
            }
            ++$current;
        }

        return $this->pages;
    }

    /**
     * for shop4 compatibility only!
     *
     * @return array
     */
    public function getItemsCompat(): array
    {
        $items = [];
        foreach ($this->pages as $page) {
            $items[$page->getPageNumber()] = $page;
        }
        $this->next->nBTN = 1;
        $this->prev->nBTN = 1;
        $items['vor']     = $this->next;
        $items['zurueck'] = $this->prev;

        return $items;
    }

    /**
     * @return array
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    /**
     * @param array $pages
     */
    public function setPages(array $pages): void
    {
        $this->pages = $pages;
    }

    /**
     * @return Item
     */
    public function getPrev(): Item
    {
        return $this->prev;
    }

    /**
     * @param Item $prev
     */
    public function setPrev(Item $prev): void
    {
        $this->prev = $prev;
    }

    /**
     * @return Item
     */
    public function getNext(): Item
    {
        return $this->next;
    }

    /**
     * @param Item $next
     */
    public function setNext(Item $next): void
    {
        $this->next = $next;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res                  = \get_object_vars($this);
        $res['productFilter'] = '*truncated*';
        $res['factory']       = '*truncated*';

        return $res;
    }
}
