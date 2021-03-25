<?php declare(strict_types=1);

namespace JTL\Filter;

use Illuminate\Support\Collection;
use JTL\Boxes\Items\AbstractBox;
use JTL\Filter\Pagination\Info;
use JTL\Helpers\Text;
use JTL\MagicCompatibilityTrait;
use stdClass;
use function Functional\every;
use function Functional\filter;
use function Functional\invoke;
use function Functional\map;

/**
 * Class SearchResults
 * @package JTL\Filter
 */
class SearchResults implements SearchResultsInterface
{
    use MagicCompatibilityTrait;

    /**
     * @var Collection()
     * @former Artikel
     */
    private $products;

    /**
     * @var int
     * @former GesamtanzahlArtikel
     */
    private $productCount = 0;

    /**
     * @var int
     */
    private $visibileProductCount = 0;

    /**
     * @var int
     * former ArtikelVon
     */
    private $offsetStart = 0;

    /**
     * @var int
     * @former ArtikelBis
     */
    private $offsetEnd = 0;

    /**
     * @var stdClass
     * @former Seitenzahlen
     */
    private $pages;

    /**
     * @var string
     * @former cSuche
     */
    private $searchTerm;

    /**
     * @var string
     * @former SuchausdruckWrite
     */
    private $searchTermWrite;

    /**
     * @var bool
     * @former SucheErfolglos
     */
    private $searchUnsuccessful = false;

    /**
     * @var Option[]
     * @former Herstellerauswahl
     */
    private $manufacturerFilterOptions = [];

    /**
     * @var Option[]
     * @former Bewertung
     */
    private $ratingFilterOptions = [];

    /**
     * @var Option[]
     * @former MerkmalFilter
     */
    private $characteristicFilterOptions = [];

    /**
     * @var Option[]
     * @former Preisspanne
     */
    private $priceRangeFilterOptions = [];

    /**
     * @var Option[]
     * @former Kategorieauswahl
     */
    private $categoryFilterOptions = [];

    /**
     * @var Option[]
     * @former SuchFilter
     */
    private $searchFilterOptions = [];

    /**
     * @var Option[]
     * @former Suchspecialauswahl
     */
    private $searchSpecialFilterOptions = [];

    /**
     * @var Option[]
     */
    private $availabilityFilterOptions = [];

    /**
     * @var Option[]
     */
    private $customFilterOptions = [];

    /**
     * @var Collection
     */
    private $productKeys;

    /**
     * @var string
     * @former cFehler
     */
    private $error;

    /**
     * @var string
     */
    public $searchFilterJSON;

    /**
     * @var array
     */
    private $sortingOptions = [];

    /**
     * @var array
     */
    private $limitOptions = [];

    /**
     * @var array
     */
    public static $mapping = [
        'Artikel'             => 'ProductsCompat',
        'GesamtanzahlArtikel' => 'ProductCount',
        'ArtikelBis'          => 'OffsetEnd',
        'ArtikelVon'          => 'OffsetStart',
        'Seitenzahlen'        => 'Pages',
        'SuchausdruckWrite'   => 'SearchTermWrite',
        'cSuche'              => 'SearchTerm',
        'cFehler'             => 'Error',
        'SucheErfolglos'      => 'SearchUnsuccessful',
        'Herstellerauswahl'   => 'ManufacturerFilterOptions',
        'Bewertung'           => 'RatingFilterOptions',
        'MerkmalFilter'       => 'CharacteristicFilterOptions',
        'Preisspanne'         => 'PriceRangeFilterOptions',
        'Kategorieauswahl'    => 'CategoryFilterOptions',
        'SuchFilter'          => 'SearchFilterOptions',
        'Suchspecialauswahl'  => 'SearchSpecialFilterOptions',
        'SuchFilterJSON'      => 'SearchFilterJSON',
    ];

    public function __construct()
    {
        $this->products    = new Collection();
        $this->productKeys = new Collection();
        $this->pages       = new Info();
    }

    /**
     * @inheritdoc
     */
    public function convert($legacy): SearchResultsInterface
    {
        if (\get_class($legacy) === __CLASS__) {
            return $legacy;
        }
        \trigger_error('Using a stdClass object for search results is deprecated.', \E_USER_DEPRECATED);
        foreach (\get_object_vars($legacy) as $var => $value) {
            $this->$var = $value;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProductsCompat(): stdClass
    {
        $compat              = new stdClass();
        $compat->elemente    = $this->getProducts();
        $compat->productKeys = $this->getProductKeys();

        return $compat;
    }

    /**
     * @inheritdoc
     */
    public function setProductsCompat(): SearchResultsInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProductKeys(): Collection
    {
        return $this->productKeys;
    }

    /**
     * @inheritdoc
     */
    public function setProductKeys(Collection $keys): SearchResultsInterface
    {
        $this->productKeys = $keys;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * @inheritdoc
     */
    public function setProducts($products): SearchResultsInterface
    {
        $this->products = $products;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProductCount(): int
    {
        return $this->productCount;
    }

    /**
     * @inheritdoc
     */
    public function setProductCount($productCount): SearchResultsInterface
    {
        $this->productCount = $productCount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVisibleProductCount(): int
    {
        return $this->visibileProductCount;
    }

    /**
     * @inheritdoc
     */
    public function setVisibleProductCount(int $count): SearchResultsInterface
    {
        $this->visibileProductCount = $count;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOffsetStart(): int
    {
        return $this->offsetStart;
    }

    /**
     * @inheritdoc
     */
    public function setOffsetStart($offsetStart): SearchResultsInterface
    {
        $this->offsetStart = $offsetStart;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOffsetEnd(): int
    {
        return $this->offsetEnd;
    }

    /**
     * @inheritdoc
     */
    public function setOffsetEnd($offsetEnd): SearchResultsInterface
    {
        $this->offsetEnd = $offsetEnd;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPages(): Info
    {
        return $this->pages;
    }

    /**
     * @inheritdoc
     */
    public function setPages(Info $pages): SearchResultsInterface
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSearchTerm(): ?string
    {
        return $this->searchTerm;
    }

    /**
     * @inheritdoc
     */
    public function setSearchTerm($searchTerm): SearchResultsInterface
    {
        $this->searchTerm = $searchTerm;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSearchTermWrite(): ?string
    {
        return $this->searchTermWrite;
    }

    /**
     * @inheritdoc
     */
    public function setSearchTermWrite($searchTerm): SearchResultsInterface
    {
        $this->searchTermWrite = $searchTerm;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSearchUnsuccessful(): bool
    {
        return $this->searchUnsuccessful;
    }

    /**
     * @inheritdoc
     */
    public function setSearchUnsuccessful($searchUnsuccessful): SearchResultsInterface
    {
        $this->searchUnsuccessful = $searchUnsuccessful;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getManufacturerFilterOptions(): array
    {
        return $this->manufacturerFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setManufacturerFilterOptions($options): SearchResultsInterface
    {
        $this->manufacturerFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRatingFilterOptions(): array
    {
        return $this->ratingFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setRatingFilterOptions($options): SearchResultsInterface
    {
        $this->ratingFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCharacteristicFilterOptions(): array
    {
        return $this->characteristicFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setCharacteristicFilterOptions($options): SearchResultsInterface
    {
        $this->characteristicFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriceRangeFilterOptions(): array
    {
        return $this->priceRangeFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setPriceRangeFilterOptions($options): SearchResultsInterface
    {
        $this->priceRangeFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCategoryFilterOptions(): array
    {
        return $this->categoryFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setCategoryFilterOptions($options): SearchResultsInterface
    {
        $this->categoryFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSearchFilterOptions(): array
    {
        return $this->searchFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setSearchFilterOptions($options): SearchResultsInterface
    {
        $this->searchFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSearchSpecialFilterOptions(): array
    {
        return $this->searchSpecialFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setSearchSpecialFilterOptions($options): SearchResultsInterface
    {
        $this->searchSpecialFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAvailabilityFilterOptions(): array
    {
        return $this->availabilityFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setAvailabilityFilterOptions($options): SearchResultsInterface
    {
        $this->availabilityFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomFilterOptions(): array
    {
        return $this->customFilterOptions;
    }

    /**
     * @inheritdoc
     */
    public function setCustomFilterOptions($options): SearchResultsInterface
    {
        $this->customFilterOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSearchFilterJSON(): ?string
    {
        return $this->searchFilterJSON;
    }

    /**
     * @inheritdoc
     */
    public function setSearchFilterJSON($json): SearchResultsInterface
    {
        $this->searchFilterJSON = $json;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @inheritdoc
     */
    public function setError($error): SearchResultsInterface
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSortingOptions(): array
    {
        return $this->sortingOptions;
    }

    /**
     * @inheritdoc
     */
    public function setSortingOptions($options): SearchResultsInterface
    {
        $this->sortingOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLimitOptions(): array
    {
        return $this->limitOptions;
    }

    /**
     * @inheritdoc
     */
    public function setLimitOptions($options): SearchResultsInterface
    {
        $this->limitOptions = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAllFilterOptions(): array
    {
        return [
            'manufacturerFilterOptions'   => $this->getManufacturerFilterOptions(),
            'ratingFilterOptions'         => $this->getRatingFilterOptions(),
            'CharacteristicFilterOptions' => $this->getCharacteristicFilterOptions(),
            'priceRangeFilterOptions'     => $this->getPriceRangeFilterOptions(),
            'categoryFilterOptions'       => $this->getCategoryFilterOptions(),
            'searchFilterOptions'         => $this->getSearchFilterOptions(),
            'searchSpecialFilterOptions'  => $this->getSearchSpecialFilterOptions(),
            'customFilterOptions'         => $this->getCustomFilterOptions()
        ];
    }

    /**
     * @param FilterInterface[] $activeFilters
     * @param FilterInterface[] $availableFilters
     */
    private function autoActivateOptions(array $activeFilters, array $availableFilters): void
    {
        foreach ($activeFilters as $activeFilter) {
            $class        = $activeFilter->getClassName();
            $activeValues = $activeFilter->getActiveValues();
            foreach ($this->getActiveFiltersByClassName($availableFilters, $class, $activeValues) as $filter) {
                /** @var FilterInterface $filter */
                $currentValues = $filter->getActiveValues();
                $act           = \is_array($currentValues)
                    ? map($currentValues, static function (FilterInterface $e) {
                        return $e->getValue();
                    })
                    : [$currentValues->getValue()];
                $this->updateOptions($filter, $act);
            }
        }
    }

    /**
     * @param FilterInterface $filter
     * @param array           $values
     */
    private function updateOptions(FilterInterface $filter, array $values): void
    {
        invoke(filter($filter->getOptions(), static function (Option $e) use ($values) {
            return \in_array($e->getValue(), $values, true);
        }), 'setIsActive', [true]);
    }

    /**
     * @param FilterInterface[] $filters
     * @param string            $class
     * @param Option|Option[]   $activeValues
     * @return array
     */
    private function getActiveFiltersByClassName(array $filters, string $class, $activeValues): array
    {
        return filter($filters, static function (FilterInterface $f) use ($class, $activeValues) {
            return $f->getClassName() === $class && $f->getActiveValues() === $activeValues;
        });
    }

    /**
     * @inheritdoc
     */
    public function setFilterOptions(
        ProductFilter $productFilter,
        $currentCategory = null,
        $selectionWizard = false
    ): SearchResultsInterface {
        if ($productFilter->isExtendedJTLSearch()) {
            return $this;
        }
        // @todo: make option
        $hideActiveOnly              = true;
        $manufacturerOptions         = $productFilter->getManufacturerFilter()->getOptions();
        $ratingOptions               = $productFilter->getRatingFilter()->getOptions();
        $categoryOptions             = $productFilter->getCategoryFilter()->getOptions();
        $priceRangeOptions           = $productFilter->getPriceRangeFilter()->getOptions($this->getProductCount());
        $searchSpecialFilters        = $productFilter->getSearchSpecialFilter()->getOptions();
        $availabilityOptions         = $productFilter->getAvailabilityFilter()->getOptions();
        $characteristicFilterOptions = $productFilter->getCharacteristicFilterCollection()->getOptions([
            'oAktuelleKategorie' => $currentCategory,
            'bForce'             => $selectionWizard === true
        ]);
        $searchFilterOptions         = [];
        foreach ($productFilter->getSearchFilter() as $searchFilter) {
            $opt = $searchFilter->getOptions();
            if (\is_array($opt)) {
                foreach ($opt as $_o) {
                    $searchFilterOptions[] = $_o;
                }
            }
        }
        $this->autoActivateOptions($productFilter->getActiveFilters(), $productFilter->getAvailableFilters());

        $customFilterOptions = map(
            $productFilter->getCustomFilters(),
            static function (FilterInterface $e) {
                if (\count($e->getOptions()) === 0) {
                    $e->hide();
                }

                return $e;
            }
        );
        $json                = AbstractBox::getJSONString(
            \array_map(
                static function ($e) {
                    $e->cURL = Text::htmlentitydecode($e->cURL);

                    return $e;
                },
                $searchFilterOptions
            )
        );

        $this->setManufacturerFilterOptions($manufacturerOptions)
            ->setSortingOptions($productFilter->getSorting()->getOptions())
            ->setLimitOptions($productFilter->getLimits()->getOptions())
            ->setRatingFilterOptions($ratingOptions)
            ->setPriceRangeFilterOptions($priceRangeOptions)
            ->setCategoryFilterOptions($categoryOptions)
            ->setSearchFilterOptions($searchFilterOptions)
            ->setSearchSpecialFilterOptions($searchSpecialFilters)
            ->setAvailabilityFilterOptions($availabilityOptions)
            ->setCharacteristicFilterOptions($characteristicFilterOptions)
            ->setCustomFilterOptions($customFilterOptions)
            ->setSearchFilterJSON($json);

        if (empty($searchSpecialFilters)) {
            // hide category filter when a category is being browsed
            $productFilter->getSearchSpecialFilter()->hide();
        }
        if (empty($categoryOptions)
            || \count($categoryOptions) === 0
            || ($productFilter->getCategory()->isInitialized()
                && $productFilter->getCategory()->getValue() !== null)
        ) {
            // hide category filter when a category is being browsed
            $productFilter->getCategoryFilter()->hide();
        }
        if (empty($priceRangeOptions)
            || \count($priceRangeOptions) === 0
            || ($productFilter->getPriceRangeFilter()->isInitialized()
                && $productFilter->getPriceRangeFilter()->getValue() !== null)
        ) {
            // hide empty price ranges
            $productFilter->getPriceRangeFilter()->hide();
        }
        if (empty($manufacturerOptions) || \count($manufacturerOptions) === 0
            || $productFilter->getManufacturer()->isInitialized()
            || ($productFilter->getManufacturerFilter()->isInitialized()
                && \count($manufacturerOptions) === 1
                && $hideActiveOnly)
        ) {
            // hide manufacturer filter when browsing manufacturer products
            $productFilter->getManufacturerFilter()->hide();
        }
        if (empty($ratingOptions)) {
            $productFilter->getRatingFilter()->hide();
        }
        if (\count($characteristicFilterOptions) < 1) {
            $productFilter->getCharacteristicFilterCollection()->hide();
        } elseif ($hideActiveOnly === true) {
            foreach ($characteristicFilterOptions as $af) {
                $options = $af->getOptions();
                if (\is_array($options)
                    && $af->getVisibility() !== Visibility::SHOW_NEVER
                    && \array_reduce(
                        $options,
                        static function ($carry, $option) {
                            /** @var Option $option */
                            return $carry && $option->isActive();
                        },
                        true
                    ) === true
                ) {
                    $af->hide();
                }
            }
            if (every($characteristicFilterOptions, static function (Option $item) {
                return $item->getVisibility() === Visibility::SHOW_NEVER;
            })) {
                // hide the whole attribute filter collection if every filter consists of only active options
                $productFilter->getCharacteristicFilterCollection()->hide();
            }
        }
        $productFilter->getCharacteristicFilterCollection()
            ->setFilterCollection($characteristicFilterOptions);

        return $this;
    }
}
