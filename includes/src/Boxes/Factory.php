<?php declare(strict_types=1);

namespace JTL\Boxes;

use JTL\Boxes\Items\BestsellingProducts;
use JTL\Boxes\Items\BoxDefault;
use JTL\Boxes\Items\BoxInterface;
use JTL\Boxes\Items\Cart;
use JTL\Boxes\Items\CompareList;
use JTL\Boxes\Items\Container;
use JTL\Boxes\Items\DirectPurchase;
use JTL\Boxes\Items\Extension;
use JTL\Boxes\Items\FilterAttribute;
use JTL\Boxes\Items\FilterAvailability;
use JTL\Boxes\Items\FilterCategory;
use JTL\Boxes\Items\FilterItem;
use JTL\Boxes\Items\FilterManufacturer;
use JTL\Boxes\Items\FilterPricerange;
use JTL\Boxes\Items\FilterRating;
use JTL\Boxes\Items\FilterSearch;
use JTL\Boxes\Items\LinkGroup;
use JTL\Boxes\Items\Login;
use JTL\Boxes\Items\Manufacturer;
use JTL\Boxes\Items\NewProducts;
use JTL\Boxes\Items\NewsCategories;
use JTL\Boxes\Items\NewsCurrentMonth;
use JTL\Boxes\Items\Plain;
use JTL\Boxes\Items\Plugin;
use JTL\Boxes\Items\ProductCategories;
use JTL\Boxes\Items\RecentlyViewedProducts;
use JTL\Boxes\Items\SearchCloud;
use JTL\Boxes\Items\SpecialOffers;
use JTL\Boxes\Items\TopOffers;
use JTL\Boxes\Items\TopRatedProducts;
use JTL\Boxes\Items\UpcomingProducts;
use JTL\Boxes\Items\Wishlist;

/**
 * Class Factory
 * @package JTL\Boxes
 */
class Factory implements FactoryInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * Factory constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getBoxByBaseType(int $baseType, string $type = null): BoxInterface
    {
        switch ($baseType) {
            case \BOX_BESTSELLER:
                return new BestsellingProducts($this->config);
            case \BOX_CONTAINER:
                return new Container($this->config);
            case \BOX_IN_KUERZE_VERFUEGBAR:
                return new UpcomingProducts($this->config);
            case \BOX_ZULETZT_ANGESEHEN:
                return new RecentlyViewedProducts($this->config);
            case \BOX_NEUE_IM_SORTIMENT:
                return new NewProducts($this->config);
            case \BOX_TOP_ANGEBOT:
                return new TopOffers($this->config);
            case \BOX_SONDERANGEBOT:
                return new SpecialOffers($this->config);
            case \BOX_LOGIN:
                return new Login($this->config);
            case \BOX_KATEGORIEN:
                return new ProductCategories($this->config);
            case \BOX_NEWS_KATEGORIEN:
                return new NewsCategories($this->config);
            case \BOX_NEWS_AKTUELLER_MONAT:
                return new NewsCurrentMonth($this->config);
            case \BOX_WUNSCHLISTE:
                return new Wishlist($this->config);
            case \BOX_WARENKORB:
                return new Cart($this->config);
            case \BOX_SCHNELLKAUF:
                return new DirectPurchase($this->config);
            case \BOX_VERGLEICHSLISTE:
                return new CompareList($this->config);
            case \BOX_EIGENE_BOX_MIT_RAHMEN:
            case \BOX_EIGENE_BOX_OHNE_RAHMEN:
                return new Plain($this->config);
            case \BOX_LINKGRUPPE:
                return new LinkGroup($this->config);
            case \BOX_HERSTELLER:
                return new Manufacturer($this->config);
            case \BOX_FILTER_MERKMALE:
                return new FilterAttribute($this->config);
            case \BOX_FILTER_KATEGORIE:
                return new FilterCategory($this->config);
            case \BOX_FILTER_HERSTELLER:
                return new FilterManufacturer($this->config);
            case \BOX_FILTER_PREISSPANNE:
                return new FilterPricerange($this->config);
            case \BOX_FILTER_BEWERTUNG:
                return new FilterRating($this->config);
            case \BOX_FILTER_SUCHE:
                return new FilterSearch($this->config);
            case \BOX_FILTER_SUCHSPECIAL:
                return new FilterItem($this->config);
            case \BOX_FILTER_AVAILABILITY:
                return new FilterAvailability($this->config);
            case \BOX_TOP_BEWERTET:
                return new TopRatedProducts($this->config);
            case \BOX_SUCHWOLKE:
                return new SearchCloud($this->config);
            default:
                if ($type === Type::PLUGIN) {
                    return new Plugin($this->config);
                }
                if ($type === Type::EXTENSION) {
                    return new Extension($this->config);
                }

                return new BoxDefault($this->config);
        }
    }
}
