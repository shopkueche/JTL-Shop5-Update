<?php declare(strict_types=1);

namespace JTL\Filter;

/**
 * Interface NavigationURLsInterface
 * @package JTL\Filter
 */
interface NavigationURLsInterface
{
    /**
     * @return string
     */
    public function getPriceRanges(): string;

    /**
     * @param string $priceRanges
     * @return NavigationURLsInterface
     */
    public function setPriceRanges(string $priceRanges): NavigationURLsInterface;

    /**
     * @return string
     */
    public function getRatings(): string;

    /**
     * @param string $ratings
     * @return NavigationURLsInterface
     */
    public function setRatings(string $ratings): NavigationURLsInterface;

    /**
     * @return string
     */
    public function getSearchSpecials(): string;

    /**
     * @param string $searchSpecials
     * @return NavigationURLsInterface
     */
    public function setSearchSpecials(string $searchSpecials): NavigationURLsInterface;

    /**
     * @return string
     */
    public function getCategories(): string;

    /**
     * @param string $categories
     * @return NavigationURLsInterface
     */
    public function setCategories(string $categories): NavigationURLsInterface;

    /**
     * @return string
     */
    public function getManufacturers(): string;

    /**
     * @param string $manufacturers
     * @return NavigationURLsInterface
     */
    public function setManufacturers(string $manufacturers): NavigationURLsInterface;

    /**
     * @param string|int $idx
     * @param string     $manufacturer
     * @return NavigationURLsInterface
     */
    public function addManufacturer($idx, string $manufacturer): NavigationURLsInterface;

    /**
     * @return array
     */
    public function getCharacteristics(): array;

    /**
     * @param string $characteristics
     * @return NavigationURLsInterface
     */
    public function setCharacteristics(string $characteristics): NavigationURLsInterface;

    /**
     * @param string|int $idx
     * @param string     $characteristic
     * @return NavigationURLsInterface
     */
    public function addCharacteristic($idx, string $characteristic): NavigationURLsInterface;

    /**
     * @return array
     */
    public function getCharacteristicValues(): array;

    /**
     * @param string $value
     * @return NavigationURLsInterface
     */
    public function setCharacteristicValues(string $value): NavigationURLsInterface;

    /**
     * @param string|int $idx
     * @param string     $value
     * @return NavigationURLsInterface
     */
    public function addCharacteristicValue($idx, string $value): NavigationURLsInterface;

    /**
     * @return array
     */
    public function getSearchFilters(): array;

    /**
     * @param string $searchFilters
     * @return NavigationURLsInterface
     */
    public function setSearchFilters(string $searchFilters): NavigationURLsInterface;

    /**
     * @param string|int $idx
     * @param string     $searchFilter
     * @return NavigationURLsInterface
     */
    public function addSearchFilter($idx, string $searchFilter): NavigationURLsInterface;

    /**
     * @return string
     */
    public function getUnsetAll(): string;

    /**
     * @param string $unsetAll
     * @return NavigationURLsInterface
     */
    public function setUnsetAll(string $unsetAll): NavigationURLsInterface;
}
