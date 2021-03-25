<?php

namespace JTL\Pagination;

use Illuminate\Support\Collection;

/**
 * Class Pagination
 * @package JTL\Pagination
 */
class Pagination
{
    /**
     * @var string
     */
    private $id = 'pagi';

    /**
     * @var int
     */
    private $dispPagesRadius = 2;

    /**
     * @var array
     */
    private $itemsPerPageOptions = [10, 20, 50, 100];

    /**
     * @var array
     */
    private $sortByOptions = [];

    /**
     * @var int
     */
    private $itemCount = 0;

    /**
     * @var int
     */
    private $itemsPerPage = 10;

    /**
     * @var bool
     */
    private $itemsPerPageExplicit = false;

    /**
     * @var int
     */
    private $sortBy = 0;

    /**
     * @var int
     */
    private $sortDir = 0;

    /**
     * @var int
     */
    private $sortByDir = 0;

    /**
     * @var int
     */
    private $page = 0;

    /**
     * @var int
     */
    private $pageCount = 0;

    /**
     * @var int
     */
    private $prevPage = 0;

    /**
     * @var int
     */
    private $nextPage = 0;

    /**
     * @var int
     */
    private $leftRangePage = 0;

    /**
     * @var int
     */
    private $rightRangePage = 0;

    /**
     * @var int
     */
    private $firstPageItem = 0;

    /**
     * @var int
     */
    private $pageItemCount = 0;

    /**
     * @var string
     */
    private $sortBySQL = '';

    /**
     * @var string
     */
    private $sortDirSQL = '';

    /**
     * @var string
     */
    private $limitSQL = '';

    /**
     * @var string
     */
    private $orderSQL = '';

    /**
     * @var array
     */
    private $items;

    /**
     * @var array|Collection
     */
    private $pageItems;

    /**
     * @var int
     */
    private $defaultItemsPerPage = 0;

    /**
     * @var int
     */
    private $defaultSortByDir = 0;

    /**
     * Pagination constructor.
     * @param string|null $id
     */
    public function __construct(string $id = null)
    {
        if ($id !== null) {
            $this->id = $id;
        }
    }

    /**
     * @param string $id - page-unique name for this pagination
     * @return $this
     */
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param int $nRange - number of page buttons to be displayed before and after the active page button
     * @return $this
     */
    public function setRange(int $nRange): self
    {
        $this->dispPagesRadius = $nRange;

        return $this;
    }

    /**
     * @param int[] $itemsPerPageOptions - to be offered as items per page count options (non-empty)
     * @return $this
     */
    public function setItemsPerPageOptions(array $itemsPerPageOptions): self
    {
        $this->itemsPerPageOptions = $itemsPerPageOptions;

        return $this;
    }

    /**
     * @param array $sortByOptions - array of [$cColumnName, $cDisplayTitle] pairs to be offered as sorting options
     * @return $this
     */
    public function setSortByOptions(array $sortByOptions): self
    {
        $this->sortByOptions = $sortByOptions;

        return $this;
    }

    /**
     * @param int $n - number of items to be paginated
     * @return $this
     */
    public function setItemCount(int $n): self
    {
        $this->itemCount = $n;

        return $this;
    }

    /**
     * @param array|Collection $items - item array to be paginated and sorted
     * @return $this
     */
    public function setItemArray($items): self
    {
        $this->items = $items;
        $this->setItemCount(\count($items));

        return $this;
    }

    /**
     * @param int $n - -1 means: all items / 0 means: use first option of $nItemsPerPageOption_arr
     * @return $this
     */
    public function setDefaultItemsPerPage(int $n): self
    {
        $this->defaultItemsPerPage = $n;

        return $this;
    }

    /**
     * @param int $n
     * @return $this
     */
    public function setDefaultSortByDir(int $n): self
    {
        $this->defaultSortByDir = $n;

        return $this;
    }

    /**
     * Explicitly set the number of items per page. This overrides any custom selection.
     *
     * @param int $nItemsPerPage
     * @return $this
     */
    public function setItemsPerPage(int $nItemsPerPage): self
    {
        $this->itemsPerPageExplicit = true;
        $this->itemsPerPage         = $nItemsPerPage;

        return $this;
    }

    /**
     * Load parameters from GET, POST or SESSION store
     * @return $this
     */
    public function loadParameters(): self
    {
        $idx                = $this->id . '_nItemsPerPage';
        $this->itemsPerPage =
            $this->itemsPerPageExplicit ? $this->itemsPerPage : (
            isset($_GET[$idx]) ? (int)$_GET[$idx] : (
            isset($_POST[$idx]) ? (int)$_POST[$idx] : (
            !empty($_SESSION[$idx]) ? (int)$_SESSION[$idx] : (
            $this->defaultItemsPerPage === -1 ? $this->defaultItemsPerPage :
                $this->itemsPerPageOptions[0]))));
        $idx                = $this->id . '_nSortByDir';
        $this->sortByDir    =
            isset($_GET[$idx]) ? (int)$_GET[$idx] : (
            isset($_POST[$idx]) ? (int)$_POST[$idx] : (
            isset($_SESSION[$idx]) ? (int)$_SESSION[$idx] :
                $this->defaultSortByDir));
        $idx                = $this->id . '_nPage';
        $this->page         =
            isset($_GET[$idx]) ? (int)$_GET[$idx] : (
            isset($_POST[$idx]) ? (int)$_POST[$idx] : (
            isset($_SESSION[$idx]) ? (int)$_SESSION[$idx] : 0));

        return $this;
    }

    /**
     * Assemble the pagination. Create SQL LIMIT and ORDER BY clauses. Sort and slice item array if present
     * @return $this
     */
    public function assemble(): self
    {
        $this->loadParameters()
             ->storeParameters();

        if ($this->itemsPerPage === -1) {
            // Show all entries on a single page
            $this->pageCount      = 1;
            $this->page           = 0;
            $this->prevPage       = 0;
            $this->nextPage       = 0;
            $this->leftRangePage  = 0;
            $this->rightRangePage = 0;
            $this->firstPageItem  = 0;
            $this->pageItemCount  = $this->itemCount;
        } elseif ($this->itemsPerPage === 0) {
            // Set $nItemsPerPage to default if greater 0 or else to the first option in $nItemsPerPageOption_arr
            $nItemsPerPage        = $this->defaultItemsPerPage > 0
                ? $this->defaultItemsPerPage
                : $this->itemsPerPageOptions[0];
            $this->pageCount      = $nItemsPerPage > 0 ? (int)\ceil($this->itemCount / $nItemsPerPage) : 1;
            $this->page           = \max(0, \min($this->pageCount - 1, $this->page));
            $this->prevPage       = \max(0, \min($this->pageCount - 1, $this->page - 1));
            $this->nextPage       = \max(0, \min($this->pageCount - 1, $this->page + 1));
            $this->leftRangePage  = \max(0, $this->page - $this->dispPagesRadius);
            $this->rightRangePage = \min($this->pageCount - 1, $this->page + $this->dispPagesRadius);
            $this->firstPageItem  = $this->page * $nItemsPerPage;
            $this->pageItemCount  = \min($nItemsPerPage, $this->itemCount - $this->firstPageItem);
        } else {
            $this->pageCount      = $this->itemsPerPage > 0 ? (int)\ceil($this->itemCount / $this->itemsPerPage) : 1;
            $this->page           = \max(0, \min($this->pageCount - 1, $this->page));
            $this->prevPage       = \max(0, \min($this->pageCount - 1, $this->page - 1));
            $this->nextPage       = \max(0, \min($this->pageCount - 1, $this->page + 1));
            $this->leftRangePage  = \max(0, $this->page - $this->dispPagesRadius);
            $this->rightRangePage = \min($this->pageCount - 1, $this->page + $this->dispPagesRadius);
            $this->firstPageItem  = $this->page * $this->itemsPerPage;
            $this->pageItemCount  = \min($this->itemsPerPage, $this->itemCount - $this->firstPageItem);
        }

        $this->sortBy  = (int)($this->sortByDir / 2);
        $this->sortDir = $this->sortByDir % 2;

        if (isset($this->sortByOptions[$this->sortBy])) {
            $this->sortBySQL  = $this->sortByOptions[$this->sortBy][0];
            $this->sortDirSQL = $this->sortDir === 0 ? 'ASC' : 'DESC';
            $this->orderSQL   = $this->sortBySQL . ' ' . $this->sortDirSQL;
            $nSortFac         = $this->sortDir === 0 ? +1 : -1;
            $cSortBy          = $this->sortBySQL;
            if (\is_array($this->items)) {
                \usort($this->items, static function ($a, $b) use ($cSortBy, $nSortFac) {
                    $valueA = \is_string($a->$cSortBy) ? \mb_convert_case($a->$cSortBy, \MB_CASE_LOWER) : $a->$cSortBy;
                    $valueB = \is_string($b->$cSortBy) ? \mb_convert_case($b->$cSortBy, \MB_CASE_LOWER) : $b->$cSortBy;

                    return $valueA == $valueB ? 0 : ($valueA < $valueB ? -$nSortFac : +$nSortFac);
                });
            }
        }
        $this->limitSQL = $this->firstPageItem . ',' . $this->pageItemCount;
        if (\is_array($this->items)) {
            $this->pageItems = \array_slice($this->items, $this->firstPageItem, $this->pageItemCount);
        } elseif ($this->items instanceof Collection) {
            $this->pageItems = $this->items->slice($this->firstPageItem, $this->pageItemCount);
        }

        return $this;
    }

    /**
     * Store the custom parameters back into the SESSION store
     * @return $this
     */
    public function storeParameters(): self
    {
        $_SESSION[$this->id . '_nItemsPerPage'] = $this->itemsPerPage;
        $_SESSION[$this->id . '_nSortByDir']    = $this->sortByDir;
        $_SESSION[$this->id . '_nPage']         = $this->page;

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getItemsPerPageOptions(): array
    {
        return $this->itemsPerPageOptions;
    }

    /**
     * @return array
     */
    public function getSortByOptions(): array
    {
        return $this->sortByOptions;
    }

    /**
     * @return string
     */
    public function getLimitSQL(): string
    {
        return $this->limitSQL;
    }

    /**
     * @return string
     */
    public function getOrderSQL(): string
    {
        return $this->orderSQL;
    }

    /**
     * @return int
     */
    public function getItemCount(): int
    {
        return $this->itemCount;
    }

    /**
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * @return int
     */
    public function getSortBy(): int
    {
        return $this->sortBy;
    }

    /**
     * @return int
     */
    public function getSortDirSQL(): int
    {
        return $this->sortDir;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    /**
     * @return int
     */
    public function getPrevPage(): int
    {
        return $this->prevPage;
    }

    /**
     * @return int
     */
    public function getNextPage(): int
    {
        return $this->nextPage;
    }

    /**
     * @return int
     */
    public function getLeftRangePage(): int
    {
        return $this->leftRangePage;
    }

    /**
     * @return int
     */
    public function getRightRangePage(): int
    {
        return $this->rightRangePage;
    }

    /**
     * @return int
     */
    public function getFirstPageItem(): int
    {
        return $this->firstPageItem;
    }

    /**
     * @return int
     */
    public function getPageItemCount(): int
    {
        return $this->pageItemCount;
    }

    /**
     * @return array|Collection|null
     */
    public function getPageItems()
    {
        return $this->pageItems;
    }

    /**
     * @return string - 'ASC' or 'DESC'
     */
    public function getSortDirSpecifier(): string
    {
        return $this->sortDirSQL;
    }

    /**
     * @return string - the column name to sort by
     */
    public function getSortByCol(): string
    {
        return $this->sortBySQL;
    }

    /**
     * @param int $nIndex
     * @return int|null
     */
    public function getItemsPerPageOption(int $nIndex): ?int
    {
        return $this->itemsPerPageOptions[$nIndex];
    }

    /**
     * @return int
     */
    public function getSortByDir(): int
    {
        return $this->sortByDir;
    }
}
