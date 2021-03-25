<?php declare(strict_types=1);

namespace JTL\Filter\SortingOptions;

use JTL\Filter\ProductFilter;

/**
 * Class SortDefault
 * @package JTL\Filter\SortingOptions
 */
class SortDefault extends AbstractSortingOption
{
    /**
     * SortDefault constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setOrderBy('tartikel.nSort, tartikel.cName');
        if ($this->productFilter->getCategory()->getValue() > 0) {
            $this->orderBy = 'tartikel.nSort, tartikel.cName';
        } elseif (isset($_SESSION['Usersortierung'])
            && $_SESSION['Usersortierung'] === \SEARCH_SORT_STANDARD
            && $this->productFilter->getSearch()->getSearchCacheID() > 0
        ) {
            $this->setOrderBy('jSuche.nSort'); // was tsuchcachetreffer in 4.06, but is aliased to jSuche
        }
    }
}
