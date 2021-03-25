<?php declare(strict_types=1);

namespace JTL\Mapper;

use JTL\Filter\SortingOptions\Bestseller;
use JTL\Filter\SortingOptions\DateCreated;
use JTL\Filter\SortingOptions\DateOfIssue;
use JTL\Filter\SortingOptions\EAN;
use JTL\Filter\SortingOptions\NameASC;
use JTL\Filter\SortingOptions\NameDESC;
use JTL\Filter\SortingOptions\None;
use JTL\Filter\SortingOptions\PriceASC;
use JTL\Filter\SortingOptions\PriceDESC;
use JTL\Filter\SortingOptions\ProductNumber;
use JTL\Filter\SortingOptions\RatingDESC;
use JTL\Filter\SortingOptions\SortDefault;
use JTL\Filter\SortingOptions\Weight;

/**
 * Class SortingType
 * @package JTL\Mapper
 */
class SortingType
{
    /**
     * @param int $type
     * @return string|null
     */
    public function mapSortTypeToClassName(int $type): ?string
    {
        switch ($type) {
            case \SEARCH_SORT_NONE:
                return None::class;
            case \SEARCH_SORT_STANDARD:
            case \SEARCH_SORT_AVAILABILITY: // option removed in 5.0.0
                return SortDefault::class;
            case \SEARCH_SORT_NAME_ASC:
                return NameASC::class;
            case \SEARCH_SORT_NAME_DESC:
                return NameDESC::class;
            case \SEARCH_SORT_PRICE_ASC:
                return PriceASC::class;
            case \SEARCH_SORT_PRICE_DESC:
                return PriceDESC::class;
            case \SEARCH_SORT_EAN:
                return EAN::class;
            case \SEARCH_SORT_NEWEST_FIRST:
                return DateCreated::class;
            case \SEARCH_SORT_PRODUCTNO:
                return ProductNumber::class;
            case \SEARCH_SORT_WEIGHT:
                return Weight::class;
            case \SEARCH_SORT_DATEOFISSUE:
                return DateOfIssue::class;
            case \SEARCH_SORT_BESTSELLER:
                return Bestseller::class;
            case \SEARCH_SORT_RATING:
                return RatingDESC::class;
            default:
                return null;
        }
    }

    /**
     * @param string|int $sort
     * @return int
     */
    public function mapUserSorting($sort): int
    {
        if (\is_numeric($sort)) {
            return (int)$sort;
        }
        // Usersortierung ist ein String aus einem Kategorieattribut
        switch (\mb_convert_case($sort, \MB_CASE_LOWER)) {
            case \SEARCH_SORT_CRITERION_NAME:
            case \SEARCH_SORT_CRITERION_NAME_ASC:
                return \SEARCH_SORT_NAME_ASC;
            case \SEARCH_SORT_CRITERION_NAME_DESC:
                return \SEARCH_SORT_NAME_DESC;
            case \SEARCH_SORT_CRITERION_PRODUCTNO:
                return \SEARCH_SORT_PRODUCTNO;
            case \SEARCH_SORT_CRITERION_WEIGHT:
                return \SEARCH_SORT_WEIGHT;
            case \SEARCH_SORT_CRITERION_PRICE_ASC:
            case \SEARCH_SORT_CRITERION_PRICE:
                return \SEARCH_SORT_PRICE_ASC;
            case \SEARCH_SORT_CRITERION_PRICE_DESC:
                return \SEARCH_SORT_PRICE_DESC;
            case \SEARCH_SORT_CRITERION_EAN:
                return \SEARCH_SORT_EAN;
            case \SEARCH_SORT_CRITERION_NEWEST_FIRST:
                return \SEARCH_SORT_NEWEST_FIRST;
            case \SEARCH_SORT_CRITERION_DATEOFISSUE:
                return \SEARCH_SORT_DATEOFISSUE;
            case \SEARCH_SORT_CRITERION_BESTSELLER:
                return \SEARCH_SORT_BESTSELLER;
            case \SEARCH_SORT_CRITERION_RATING:
                return \SEARCH_SORT_RATING;
            default:
                return \SEARCH_SORT_STANDARD;
        }
    }
}
