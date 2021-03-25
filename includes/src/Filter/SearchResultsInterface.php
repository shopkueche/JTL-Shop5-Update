<?php declare(strict_types=1);

namespace JTL\Filter;

use Illuminate\Support\Collection;
use JTL\Catalog\Category\Kategorie;
use JTL\Filter\Pagination\Info;
use stdClass;

/**
 * Interface SearchResultsInterface
 * @package JTL\Filter
 */
interface SearchResultsInterface
{
    /**
     * @param stdClass|SearchResultsInterface $legacy
     * @return $this
     */
    public function convert($legacy): SearchResultsInterface;

    /**
     * @return stdClass
     */
    public function getProductsCompat(): stdClass;

    /**
     * @return $this
     */
    public function setProductsCompat(): SearchResultsInterface;

    /**
     * @return Collection
     */
    public function getProductKeys(): Collection;

    /**
     * @param Collection $keys
     * @return $this
     */
    public function setProductKeys(Collection $keys): SearchResultsInterface;

    /**
     * @return Collection
     */
    public function getProducts(): Collection;

    /**
     * @param Collection $products
     * @return $this
     */
    public function setProducts($products): SearchResultsInterface;

    /**
     * @return int
     */
    public function getProductCount(): int;

    /**
     * @param int $productCount
     * @return $this
     */
    public function setProductCount($productCount): SearchResultsInterface;

    /**
     * @return int
     */
    public function getVisibleProductCount(): int;

    /**
     * @param int $count
     * @return $this
     */
    public function setVisibleProductCount(int $count): SearchResultsInterface;

    /**
     * @return int
     */
    public function getOffsetStart(): int;

    /**
     * @param int $offsetStart
     * @return $this
     */
    public function setOffsetStart($offsetStart): SearchResultsInterface;

    /**
     * @return int
     */
    public function getOffsetEnd(): int;

    /**
     * @param int $offsetEnd
     * @return $this
     */
    public function setOffsetEnd($offsetEnd): SearchResultsInterface;

    /**
     * @return Info
     */
    public function getPages(): Info;

    /**
     * @param Info $pages
     * @return $this
     */
    public function setPages(Info $pages): SearchResultsInterface;

    /**
     * @return string|null
     */
    public function getSearchTerm(): ?string;

    /**
     * @param string $searchTerm
     * @return $this
     */
    public function setSearchTerm($searchTerm): SearchResultsInterface;

    /**
     * @return string|null
     */
    public function getSearchTermWrite(): ?string;

    /**
     * @param string $searchTerm
     * @return $this
     */
    public function setSearchTermWrite($searchTerm): SearchResultsInterface;

    /**
     * @return bool
     */
    public function getSearchUnsuccessful(): bool;

    /**
     * @param bool $searchUnsuccessful
     * @return $this
     */
    public function setSearchUnsuccessful($searchUnsuccessful): SearchResultsInterface;

    /**
     * @return Option[]
     */
    public function getManufacturerFilterOptions(): array;

    /**
     * @param Option[] $options
     * @return $this
     */
    public function setManufacturerFilterOptions($options): SearchResultsInterface;

    /**
     * @return Option[]
     */
    public function getRatingFilterOptions(): array;

    /**
     * @param Option[] $options
     * @return $this
     */
    public function setRatingFilterOptions($options): SearchResultsInterface;

    /**
     * @return Option[]
     */
    public function getCharacteristicFilterOptions(): array;

    /**
     * @param Option[] $options
     * @return $this
     */
    public function setCharacteristicFilterOptions($options): SearchResultsInterface;

    /**
     * @return Option[]
     */
    public function getPriceRangeFilterOptions(): array;

    /**
     * @param Option[] $options
     * @return $this
     */
    public function setPriceRangeFilterOptions($options): SearchResultsInterface;

    /**
     * @return Option[]
     */
    public function getCategoryFilterOptions(): array;

    /**
     * @param Option[] $options
     * @return $this
     */
    public function setCategoryFilterOptions($options): SearchResultsInterface;

    /**
     * @return Option[]
     */
    public function getSearchFilterOptions(): array;

    /**
     * @param Option[] $options
     * @return $this
     */
    public function setSearchFilterOptions($options): SearchResultsInterface;

    /**
     * @return Option[]
     */
    public function getSearchSpecialFilterOptions(): array;

    /**
     * @param Option[] $options
     * @return $this
     */
    public function setSearchSpecialFilterOptions($options): SearchResultsInterface;

    /**
     * @return Option[]
     */
    public function getAvailabilityFilterOptions(): array;

    /**
     * @param Option[] $options
     * @return $this
     */
    public function setAvailabilityFilterOptions($options): SearchResultsInterface;

    /**
     * @return Option[]
     */
    public function getCustomFilterOptions(): array;

    /**
     * @param Option[] $options
     * @return $this
     */
    public function setCustomFilterOptions($options): SearchResultsInterface;

    /**
     * @return string|null
     */
    public function getSearchFilterJSON(): ?string;

    /**
     * @param string $json
     * @return $this
     */
    public function setSearchFilterJSON($json): SearchResultsInterface;

    /**
     * @return string|null
     */
    public function getError(): ?string;

    /**
     * @param string $error
     * @return $this
     */
    public function setError($error): SearchResultsInterface;

    /**
     * @return array
     */
    public function getSortingOptions(): array;

    /**
     * @param array $options
     * @return $this
     */
    public function setSortingOptions($options): SearchResultsInterface;

    /**
     * @return array
     */
    public function getLimitOptions(): array;

    /**
     * @param array $options
     * @return $this
     */
    public function setLimitOptions($options): SearchResultsInterface;

    /**
     * @return array
     */
    public function getAllFilterOptions(): array;

    /**
     * @param ProductFilter  $productFilter
     * @param null|Kategorie $currentCategory
     * @param bool           $selectionWizard
     * @return $this
     */
    public function setFilterOptions(
        ProductFilter $productFilter,
        $currentCategory = null,
        $selectionWizard = false
    ): SearchResultsInterface;
}
