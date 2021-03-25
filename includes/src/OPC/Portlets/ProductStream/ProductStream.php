<?php declare(strict_types=1);

namespace JTL\OPC\Portlets\ProductStream;

use Illuminate\Support\Collection;
use JTL\Catalog\Product\Artikel;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Filter\AbstractFilter;
use JTL\Filter\Config;
use JTL\Filter\ProductFilter;
use JTL\Filter\Type;
use JTL\OPC\InputType;
use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;
use JTL\Shop;

/**
 * Class ProductStream
 * @package JTL\OPC\Portlets
 */
class ProductStream extends Portlet
{
    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'search' => [
                'type'  => InputType::SEARCH,
                'label' => 'Suche',
                'placeholder' => __('search'),
                'width' => 67,
                'order' => 2,
            ],
            'listStyle'    => [
                'type'    => InputType::SELECT,
                'label'   => __('presentation'),
                'width'   => 34,
                'order'   => 1,
                'options' => [
                    'gallery'      => __('presentationGallery'),
                    'list'         => __('presentationList'),
                    'simpleSlider' => __('presentationSimpleSlider'),
                    'slider'       => __('presentationSlider'),
                    'box-slider'   => __('presentationBoxSlider'),
                ],
                'default' => 'gallery',
            ],
            'filters'      => [
                'type'     => InputType::FILTER,
                'label'    => __('itemFilter'),
                'default'  => [],
                'searcher' => 'search',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            __('Styles') => 'styles',
        ];
    }

    /**
     * @param PortletInstance $instance
     * @return Collection
     */
    public function getFilteredProductIds(PortletInstance $instance): Collection
    {
        $enabledFilters = $instance->getProperty('filters');
        $productFilter  = new ProductFilter(
            Config::getDefault(),
            Shop::Container()->getDB(),
            Shop::Container()->getCache()
        );

        foreach ($enabledFilters as $enabledFilter) {
            /** @var AbstractFilter $newFilter * */
            $newFilter = new $enabledFilter['class']($productFilter);
            $newFilter->setType(Type::AND);
            $productFilter->addActiveFilter($newFilter, $enabledFilter['value']);
        }

        return $productFilter->getProductKeys();
    }

    /**
     * @param PortletInstance $instance
     * @return array
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function getFilteredProducts(PortletInstance $instance): array
    {
        $products       = [];
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($this->getFilteredProductIds($instance) as $productID) {
            $products[] = (new Artikel())->fuelleArtikel($productID, $defaultOptions);
        }

        return $products;
    }
}
