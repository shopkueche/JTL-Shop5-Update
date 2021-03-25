<?php declare(strict_types=1);

namespace JTL\Filter\Items;

use JTL\Filter\FilterInterface;
use JTL\Filter\ProductFilter;
use JTL\Filter\States\BaseManufacturer;
use JTL\Filter\Type;
use JTL\Shop;

/**
 * Class Manufacturer
 * @package JTL\Filter\Items
 */
class Manufacturer extends BaseManufacturer
{
    /**
     * Manufacturer constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('hf')
             ->setUrlParamSEO(\SEP_HST)
             ->setVisibility($this->getConfig('navigationsfilter')['allgemein_herstellerfilter_benutzen'])
             ->setFrontendName(Shop::isAdmin() ? __('filterManufacturers') : Shop::Lang()->get('allManufacturers'))
             ->setType($this->getConfig('navigationsfilter')['manufacturer_filter_type'] === 'O'
                 ? Type::OR
                 : Type::AND);
    }

    /**
     * @param array|int $value
     * @return $this
     */
    public function setValue($value): FilterInterface
    {
        $this->value = \is_array($value) ? \array_map('\intval', $value) : (int)$value;

        return $this;
    }
}
