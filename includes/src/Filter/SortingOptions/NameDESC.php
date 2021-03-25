<?php declare(strict_types=1);

namespace JTL\Filter\SortingOptions;

use JTL\Filter\ProductFilter;
use JTL\Shop;

/**
 * Class NameDESC
 * @package JTL\Filter\SortingOptions
 */
class NameDESC extends AbstractSortingOption
{
    /**
     * NameDESC constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setOrderBy('tartikel.cName DESC');
        $this->setName(Shop::Lang()->get('sortNameDesc'));
        $this->setPriority($this->getConfig('artikeluebersicht')['suche_sortierprio_name_ab']);
        $this->setValue(\SEARCH_SORT_NAME_DESC);
    }
}
