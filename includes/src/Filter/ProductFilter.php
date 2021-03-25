<?php declare(strict_types=1);

namespace JTL\Filter;

use Illuminate\Support\Collection;
use JTL\Cache\JTLCacheInterface;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Product\Artikel;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Filter\Items\Availability;
use JTL\Filter\Items\Category;
use JTL\Filter\Items\Characteristic;
use JTL\Filter\Items\Limit;
use JTL\Filter\Items\Manufacturer;
use JTL\Filter\Items\PriceRange;
use JTL\Filter\Items\Rating;
use JTL\Filter\Items\Search;
use JTL\Filter\Items\SearchSpecial;
use JTL\Filter\Items\Sort;
use JTL\Filter\Pagination\Info;
use JTL\Filter\SortingOptions\Factory;
use JTL\Filter\States\BaseCategory;
use JTL\Filter\States\BaseCharacteristic;
use JTL\Filter\States\BaseManufacturer;
use JTL\Filter\States\BaseSearchQuery;
use JTL\Filter\States\BaseSearchSpecial;
use JTL\Filter\States\DummyState;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\MagicCompatibilityTrait;
use JTL\Mapper\SortingType;
use stdClass;
use function Functional\first;
use function Functional\flatten;
use function Functional\group;
use function Functional\map;
use function Functional\select;

/**
 * Class ProductFilter
 * @package JTL\Filter
 */
class ProductFilter
{
    use MagicCompatibilityTrait;

    /**
     * @var BaseCategory
     */
    private $category;

    /**
     * @var Category
     */
    private $categoryFilter;

    /**
     * @var BaseManufacturer
     */
    private $manufacturer;

    /**
     * @var Manufacturer
     */
    private $manufacturerFilter;

    /**
     * @var BaseCharacteristic
     */
    private $characteristicValue;

    /**
     * @var BaseSearchQuery
     */
    private $searchQuery;

    /**
     * @var Search[]
     */
    private $searchFilter = [];

    /**
     * @var Characteristic[]
     */
    private $characteristicFilter = [];

    /**
     * @var SearchSpecial
     */
    private $searchSpecialFilter;

    /**
     * @var Availability
     */
    private $availabilityFilter;

    /**
     * @var Rating
     */
    private $ratingFilter;

    /**
     * @var PriceRange
     */
    private $priceRangeFilter;

    /**
     * @var BaseSearchSpecial
     */
    private $searchSpecial;

    /**
     * @var Search
     */
    private $search;

    /**
     * @var object
     */
    private $EchteSuche;

    /**
     * @var int
     */
    private $productLimit = 0;

    /**
     * @var int
     */
    private $nSeite = 1;

    /**
     * @var int
     */
    private $nSortierung = 0;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var FilterInterface[]
     */
    private $filters = [];

    /**
     * @var FilterInterface[]
     */
    private $activeFilters = [];

    /**
     * @var FilterInterface
     */
    private $baseState;

    /**
     * @var NavigationURLsInterface
     */
    private $url;

    /**
     * @var Characteristic
     */
    private $characteristicFilterCollection;

    /**
     * @var Search
     */
    public $searchFilterCompat;

    /**
     * @var SearchResultsInterface
     */
    private $searchResults;

    /**
     * @var MetadataInterface
     */
    private $metaData;

    /**
     * @var ProductFilterSQLInterface
     */
    private $filterSQL;

    /**
     * @var ProductFilterURL
     */
    private $filterURL;

    /**
     * @var bool
     */
    private $bExtendedJTLSearch = false;

    /**
     * @var stdClass|null
     */
    private $oExtendedJTLSearchResponse;

    /**
     * @var int
     */
    private $showChildProducts;

    /**
     * @var Sort
     */
    private $sorting;

    /**
     * @var Limit
     */
    private $limits;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var Config
     */
    private $filterConfig;

    /**
     * @var array
     */
    public static $mapping = [
        'nAnzahlFilter'      => 'FilterCount',
        'nAnzahlProSeite'    => 'ProductLimit',
        'Kategorie'          => 'Category',
        'KategorieFilter'    => 'CategoryFilter',
        'Hersteller'         => 'Manufacturer',
        'HerstellerFilter'   => 'ManufacturerFilter',
        'Suchanfrage'        => 'SearchQuery',
        'MerkmalWert'        => 'CharacteristicValue',
        'Suchspecial'        => 'SearchSpecial',
        'MerkmalFilter'      => 'CharacteristicFilter',
        'SuchFilter'         => 'SearchFilter',
        'SuchspecialFilter'  => 'SearchSpecialFilter',
        'BewertungFilter'    => 'RatingFilter',
        'PreisspannenFilter' => 'PriceRangeFilter',
        'Suche'              => 'Search',
        'EchteSuche'         => 'RealSearch',
        'oSprache_arr'       => 'Languages',
        'URL'                => 'URL'
    ];

    /**
     * ProductFilter constructor.
     * @param ConfigInterface   $config
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(ConfigInterface $config, DbInterface $db, JTLCacheInterface $cache)
    {
        $this->filterConfig      = $config;
        $this->db                = $db;
        $this->cache             = $cache;
        $this->showChildProducts = \defined('SHOW_CHILD_PRODUCTS')
            ? \SHOW_CHILD_PRODUCTS
            : 0;

        $this->url       = new NavigationURLs();
        $this->metaData  = new Metadata($this);
        $this->filterSQL = new ProductFilterSQL($this);
        $this->filterURL = new ProductFilterURL($this);

        $this->initBaseStates();
        \executeHook(\HOOK_PRODUCTFILTER_CREATE, ['productFilter' => $this]);
    }

    /**
     * @return int
     */
    public function showChildProducts(): int
    {
        return $this->showChildProducts;
    }

    /**
     * @param int $showChildProducts
     * @return ProductFilter
     */
    public function setShowChildProducts(int $showChildProducts): self
    {
        $this->showChildProducts = $showChildProducts;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->nSortierung;
    }

    /**
     * @param int $sort
     * @return ProductFilter
     */
    public function setSort(int $sort): self
    {
        $this->nSortierung = $sort;

        return $this;
    }

    /**
     * @return NavigationURLsInterface
     */
    public function getURL(): NavigationURLsInterface
    {
        return $this->url;
    }

    /**
     * @param NavigationURLsInterface $url
     * @return ProductFilter
     */
    public function setURL(NavigationURLsInterface $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * for compatibility reasons only - called when oSprache_arr is directly read from ProductFilter instance
     *
     * @return array
     * @deprecated since 5.0.0
     */
    public function getLanguages(): array
    {
        return $this->filterConfig->getLanguages();
    }

    /**
     * for compatibility reasons only - called when oSprache_arr is directly set on ProductFilter instance
     *
     * @param array $languages
     * @return array
     * @deprecated since 5.0.0
     */
    public function setLanguages(array $languages): array
    {
        $this->filterConfig->setLanguages($languages);

        return $languages;
    }

    /**
     * @return ProductFilterSQLInterface
     */
    public function getFilterSQL(): ProductFilterSQLInterface
    {
        return $this->filterSQL;
    }

    /**
     * @param ProductFilterSQLInterface $filterSQL
     * @return ProductFilter
     */
    public function setFilterSQL(ProductFilterSQLInterface $filterSQL): self
    {
        $this->filterSQL = $filterSQL;

        return $this;
    }

    /**
     * @return ProductFilterURL
     */
    public function getFilterURL(): ProductFilterURL
    {
        return $this->filterURL;
    }

    /**
     * @param ProductFilterURL $filterURL
     * @return ProductFilter
     */
    public function setFilterURL(ProductFilterURL $filterURL): self
    {
        $this->filterURL = $filterURL;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getSearchResultProducts(): Collection
    {
        if ($this->searchResults === null) {
            $this->searchResults = new SearchResults();
        }

        return $this->searchResults->getProducts();
    }

    /**
     * @return SearchResultsInterface
     */
    public function getSearchResults(): SearchResultsInterface
    {
        if ($this->searchResults === null) {
            $this->searchResults = new SearchResults();
        }

        return $this->searchResults;
    }

    /**
     * @param SearchResultsInterface $results
     * @return $this
     */
    public function setSearchResults(SearchResultsInterface $results): self
    {
        $this->searchResults = $results;

        return $this;
    }

    /**
     * @return MetadataInterface
     */
    public function getMetaData(): MetadataInterface
    {
        return $this->metaData;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->nSeite;
    }

    /**
     * @return FilterInterface
     */
    public function getBaseState(): FilterInterface
    {
        return $this->baseState;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function setBaseState(FilterInterface $filter): self
    {
        $this->baseState = $filter;

        return $this;
    }

    /**
     * @return int
     */
    public function getProductLimit(): int
    {
        return $this->productLimit;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setProductLimit(int $limit): self
    {
        $this->productLimit = $limit;

        return $this;
    }

    /**
     * @return Sort
     */
    public function getSorting(): Sort
    {
        return $this->sorting;
    }

    /**
     * @param Sort $sorting
     * @return $this
     */
    public function setSorting(Sort $sorting): self
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * @return Limit
     */
    public function getLimits(): Limit
    {
        return $this->limits;
    }

    /**
     * @param Limit $limits
     * @return $this
     */
    public function setLimits(Limit $limits): self
    {
        $this->limits = $limits;

        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return \array_merge($this->getParamsPrototype(), $this->params);
    }

    /**
     * @return array - default array keys
     */
    private function getParamsPrototype(): array
    {
        return [
            'kKategorie'             => 0,
            'kKonfigPos'             => 0,
            'kHersteller'            => 0,
            'kArtikel'               => 0,
            'kVariKindArtikel'       => 0,
            'kSeite'                 => 0,
            'kLink'                  => 0,
            'kSuchanfrage'           => 0,
            'kMerkmalWert'           => 0,
            'kSuchspecial'           => 0,
            'kKategorieFilter'       => 0,
            'kHerstellerFilter'      => 0,
            'nBewertungSterneFilter' => 0,
            'cPreisspannenFilter'    => '',
            'kSuchspecialFilter'     => 0,
            'nSortierung'            => 0,
            'nSort'                  => 0,
            'MerkmalFilter_arr'      => [],
            'SuchFilter_arr'         => [],
            'nArtikelProSeite'       => null,
            'cSuche'                 => null,
            'seite'                  => null,
            'show'                   => true,
            'kSuchFilter'            => 0,
            'kWunschliste'           => 0,
            'MerkmalFilter'          => null,
            'SuchFilter'             => null,
            'vergleichsliste'        => null,
            'nDarstellung'           => 0,
            'isSeoMainword'          => false,
            'cDatum'                 => '',
            'nAnzahl'                => 0,
            'nSterne'                => 0,
            'customFilters'          => [],
            'searchSpecialFilters'   => [],
            'manufacturerFilters'    => [],
            'categoryFilters'        => []
        ];
    }

    /**
     * @return $this
     */
    public function initBaseStates(): self
    {
        $this->category       = new BaseCategory($this);
        $this->categoryFilter = new Category($this);

        $this->manufacturer       = new BaseManufacturer($this);
        $this->manufacturerFilter = new Manufacturer($this);

        $this->searchQuery = new BaseSearchQuery($this);

        $this->characteristicValue = new BaseCharacteristic($this);

        $this->searchSpecial = new BaseSearchSpecial($this);

        $this->filters              = [];
        $this->characteristicFilter = [];
        $this->searchFilter         = [];

        $this->searchSpecialFilter = new SearchSpecial($this);

        $this->availabilityFilter = new Availability($this);

        $this->ratingFilter = new Rating($this);

        $this->priceRangeFilter = new PriceRange($this);

        $this->characteristicFilterCollection = new Characteristic($this);
        $this->searchFilterCompat             = new Search($this);

        $this->search = new Search($this);

        $this->baseState = new DummyState($this);

        \executeHook(\HOOK_PRODUCTFILTER_INIT, ['productFilter' => $this]);

        $this->filters[] = $this->categoryFilter;
        $this->filters[] = $this->manufacturerFilter;
        $this->filters[] = $this->characteristicFilterCollection;
        $this->filters[] = $this->searchSpecialFilter;
        $this->filters[] = $this->priceRangeFilter;
        $this->filters[] = $this->ratingFilter;
        $this->filters[] = $this->search;
        $this->filters[] = $this->availabilityFilter;

        $this->sorting = new Sort($this);
        $this->limits  = new Limit($this);

        $this->sorting->setFactory(new Factory($this));
        $this->sorting->registerSortingOptions();

        return $this;
    }

    /**
     * @param array $params
     * @param bool  $validate
     * @return $this
     */
    public function initStates(array $params, bool $validate = true): self
    {
        $params = \array_merge($this->getParamsPrototype(), $params);
        if ($params['kKategorie'] > 0) {
            $this->baseState = $this->category->init($params['kKategorie']);
        } elseif ($params['kHersteller'] > 0) {
            $this->manufacturer->init($params['kHersteller']);
            $this->baseState = $this->manufacturer;
        } elseif ($params['kMerkmalWert'] > 0) {
            $this->characteristicValue = (new BaseCharacteristic($this))->init($params['kMerkmalWert']);
            $this->baseState           = $this->characteristicValue;
        } elseif ($params['kSuchspecial'] > 0) {
            $this->searchSpecial->init($params['kSuchspecial']);
            $this->baseState = $this->searchSpecial;
        }
        if ($params['kKategorieFilter'] > 0 && \count($params['categoryFilters']) === 0) {
            // backwards compatibility
            if (\is_array($params['kKategorieFilter'])) {
                foreach ($params['kKategorieFilter'] as $param) {
                    $params['categoryFilters'][] = $param;
                }
            } else {
                $params['categoryFilters'][] = $params['kKategorieFilter'];
            }
        }
        if (\count($params['categoryFilters']) > 0) {
            $this->addActiveFilter($this->categoryFilter, $params['categoryFilters']);
        }
        if ($params['kHerstellerFilter'] > 0 || \count($params['manufacturerFilters']) > 0) {
            $this->addActiveFilter(
                $this->manufacturerFilter,
                \count($params['manufacturerFilters']) > 0
                    ? $params['manufacturerFilters']
                    : $params['kHerstellerFilter']
            );
        }
        if ($params['nBewertungSterneFilter'] > 0) {
            $this->addActiveFilter($this->ratingFilter, $params['nBewertungSterneFilter']);
        }
        if (\mb_strlen($params['cPreisspannenFilter']) > 0) {
            $this->addActiveFilter($this->priceRangeFilter, $params['cPreisspannenFilter']);
        }
        $this->initCharacteristicFilters($params['MerkmalFilter_arr']);
        if ($params['kSuchspecialFilter'] > 0 && \count($params['searchSpecialFilters']) === 0) {
            // backwards compatibility
            $params['searchSpecialFilters'][] = $params['kSuchspecialFilter'];
        }
        if (\count($params['searchSpecialFilters']) > 0) {
            $this->addActiveFilter($this->searchSpecialFilter, $params['searchSpecialFilters']);
        }

        // @todo - same as suchfilter?
        foreach ($params['SuchFilter_arr'] as $sf) {
            $this->searchFilter[] = $this->addActiveFilter(new Search($this), $sf);
        }
        if ($params['nSortierung'] > 0) {
            $this->nSortierung = (int)$params['nSortierung'];
        }
        if ($params['nArtikelProSeite'] !== 0) {
            $this->productLimit = (int)$params['nArtikelProSeite'];
        }
        // @todo: how to handle \mb_strlen($params['cSuche']) === 0?
        if ($params['kSuchanfrage'] > 0) {
            $oSuchanfrage = $this->db->select(
                'tsuchanfrage',
                'kSuchanfrage',
                $params['kSuchanfrage']
            );
            if (isset($oSuchanfrage->cSuche) && \mb_strlen($oSuchanfrage->cSuche) > 0) {
                $this->search->setName($oSuchanfrage->cSuche);
            }
            // Suchcache beachten / erstellen
            $searchName = $this->search->getName();
            if (!empty($searchName)) {
                $this->search->setSearchCacheID($this->searchQuery->editSearchCache());
                $this->searchQuery->init($oSuchanfrage->kSuchanfrage);
                $this->searchQuery->setSearchCacheID($this->search->getSearchCacheID())
                                  ->setName($this->search->getName());
                if (!$this->baseState->isInitialized()) {
                    $this->baseState = $this->searchQuery;
                }
            }
        } elseif ($params['cSuche'] !== null && \mb_strlen($params['cSuche']) > 0) {
            $params['cSuche'] = Text::filterXSS($params['cSuche']);
            $this->search->setName($params['cSuche']);
            $this->searchQuery->setName($params['cSuche']);
            $oSuchanfrage = $this->db->select(
                'tsuchanfrage',
                'cSuche',
                $params['cSuche'],
                'kSprache',
                $this->getFilterConfig()->getLanguageID(),
                'nAktiv',
                1,
                false,
                'kSuchanfrage'
            );
            $kSuchCache   = $this->searchQuery->editSearchCache();
            $kSuchAnfrage = isset($oSuchanfrage->kSuchanfrage)
                ? (int)$oSuchanfrage->kSuchanfrage
                : $params['kSuchanfrage'];
            $this->search->setSearchCacheID($kSuchCache);
            $this->searchQuery->setSearchCacheID($kSuchCache)
                              ->init($kSuchAnfrage)
                              ->setName($params['cSuche']);
            $this->EchteSuche         = new stdClass();
            $this->EchteSuche->cSuche = $params['cSuche'];
            if (!$this->baseState->isInitialized()) {
                $this->baseState = $this->searchQuery;
            }
            $limit                            = $this->limits->getProductsPerPageLimit();
            $this->oExtendedJTLSearchResponse = null;
            $this->bExtendedJTLSearch         = false;

            \executeHook(\HOOK_NAVI_PRESUCHE, [
                'cValue'             => &$this->EchteSuche->cSuche,
                'bExtendedJTLSearch' => &$this->bExtendedJTLSearch
            ]);
            if (empty($params['cSuche'])) {
                $this->bExtendedJTLSearch = false;
            }
            $this->nSeite = \max(1, Request::verifyGPCDataInt('seite'));
            \executeHook(\HOOK_NAVI_SUCHE, [
                'bExtendedJTLSearch'         => &$this->bExtendedJTLSearch,
                'oExtendedJTLSearchResponse' => &$this->oExtendedJTLSearchResponse,
                'cValue'                     => &$this->EchteSuche->cSuche,
                'nArtikelProSeite'           => &$limit,
                'nSeite'                     => &$this->nSeite,
                'nSortierung'                => $_SESSION['Usersortierung'] ?? null,
                'bLagerbeachten'             => (int)$this->getFilterConfig()->getConfig('global')
                    ['artikel_artikelanzeigefilter'] === \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL
            ]);
            $this->search->bExtendedJTLSearch = $this->bExtendedJTLSearch;
        }
        $this->nSeite = \max(1, Request::verifyGPCDataInt('seite'));
        foreach ($this->getCustomFilters() as $filter) {
            $filterParam = $filter->getUrlParam();
            $filterClass = $filter->getClassName();
            if (isset($_GET[$filterParam])) {
                // OR filters should always get an array as input - even if there is just one value active
                if (!\is_array($_GET[$filterParam]) && $filter->getType() === Type::OR) {
                    $_GET[$filterParam] = [$_GET[$filterParam]];
                }
                // escape all input values
                if (($filter->getType() === Type::OR && \is_array($_GET[$filterParam]))
                    || ($filter->getType() === Type::AND
                        && (Request::verifyGPCDataInt($filterParam) > 0
                            || Request::verifyGPDataString($filterParam) !== ''))
                ) {
                    $filterValue = \is_array($_GET[$filterParam])
                        ? \array_map([$this->db, 'realEscape'], $_GET[$filterParam])
                        : $this->db->realEscape($_GET[$filterParam]);
                    $this->addActiveFilter($filter, $filterValue);
                    $params[$filterParam] = $filterValue;
                }
            } elseif (\count($params['customFilters']) > 0) {
                foreach ($params['customFilters'] as $className => $filterValue) {
                    if ($filterClass === $className) {
                        $this->addActiveFilter($filter, $filterValue);
                        $params[$filterParam] = $filterValue;
                    }
                }
            }
        }
        \executeHook(\HOOK_PRODUCTFILTER_INIT_STATES, [
            'productFilter' => $this,
            'params'        => $params
        ]);
        $this->params = $params;

        return $validate === true ? $this->validate() : $this;
    }

    /**
     * @param array $values
     * @return $this
     */
    private function initCharacteristicFilters(array $values): self
    {
        if (\count($values) === 0) {
            return $this;
        }
        $characteristics = $this->db->query(
            'SELECT tmerkmalwert.kMerkmal, tmerkmalwert.kMerkmalWert, tmerkmal.nMehrfachauswahl
                FROM tmerkmalwert
                JOIN tmerkmal 
                    ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
                WHERE kMerkmalWert IN (' . \implode(',', \array_map('\intval', $values)) . ')',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($characteristics as $characteristic) {
            $characteristic->kMerkmal         = (int)$characteristic->kMerkmal;
            $characteristic->kMerkmalWert     = (int)$characteristic->kMerkmalWert;
            $characteristic->nMehrfachauswahl = (int)$characteristic->nMehrfachauswahl;
            $this->characteristicFilter[]     = $this->addActiveFilter(new Characteristic($this), $characteristic);
        }

        return $this;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function registerFilter(FilterInterface $filter): self
    {
        $this->filters[] = $filter->setBaseData($this);

        return $this;
    }

    /**
     * @param string $filterName
     * @return FilterInterface
     * @throws \InvalidArgumentException
     */
    public function registerFilterByClassName(string $filterName): FilterInterface
    {
        $filter = null;
        if (\class_exists($filterName)) {
            /** @var FilterInterface $filter */
            $filter          = new $filterName($this);
            $this->filters[] = $filter->setClassName($filterName);
        } else {
            throw new \InvalidArgumentException('Cannot register filter class ' . $filterName);
        }

        return $filter;
    }

    /**
     * @param FilterInterface $filter
     * @param mixed           $filterValue - shortcut to set active value (same as calling init($filterValue)
     * @return FilterInterface
     */
    public function addActiveFilter(FilterInterface $filter, $filterValue): FilterInterface
    {
        $this->activeFilters[] = $filter->setBaseData($this)->init($filterValue)->generateActiveFilterData();

        return $filter;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function enableFilter(FilterInterface $filter): self
    {
        foreach ($this->filters as $idx => $registeredFilter) {
            if ($filter->getName() === $registeredFilter->getName()) {
                $this->filters[$idx] = $filter;
            }
        }
        $this->activeFilters[] = $filter;

        return $this;
    }

    /**
     * @param string $filterClassName
     * @return int|array|null
     */
    public function getFilterValue(string $filterClassName)
    {
        return \array_reduce(
            $this->activeFilters,
            static function ($carry, $item) use ($filterClassName) {
                /** @var FilterInterface $item */
                return $carry ?? ($item->getClassName() === $filterClassName
                        ? $item->getValue()
                        : null);
            }
        );
    }

    /**
     * @param string $filterClassName
     * @return bool
     */
    public function hasFilter(string $filterClassName): bool
    {
        return $this->getActiveFilterByClassName($filterClassName) !== null;
    }

    /**
     * @param string $filterClassName
     * @return FilterInterface|null
     */
    public function getFilterByClassName(string $filterClassName): ?FilterInterface
    {
        $filter = \array_filter(
            $this->filters,
            static function ($f) use ($filterClassName) {
                return $f->getClassName() === $filterClassName;
            }
        );

        return \is_array($filter) ? \current($filter) : null;
    }

    /**
     * @param string $filterClassName
     * @return FilterInterface|null
     */
    public function getActiveFilterByClassName(string $filterClassName): ?FilterInterface
    {
        $filter = \array_filter(
            $this->activeFilters,
            static function ($f) use ($filterClassName) {
                return $f->getClassName() === $filterClassName;
            }
        );

        return \is_array($filter) ? \current($filter) : null;
    }

    /**
     * @return FilterInterface[]
     */
    public function getCustomFilters(): array
    {
        return \array_filter(
            $this->filters,
            static function ($e) {
                return $e->isCustom();
            }
        );
    }

    /**
     * @return FilterInterface[]
     */
    public function getAvailableFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param FilterInterface[] $filters
     * @return $this
     */
    public function setAvailableFilters($filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * get filters that can be displayed at content level
     *
     * @return array|FilterInterface[]
     */
    public function getAvailableContentFilters(): array
    {
        if ($this->bExtendedJTLSearch === true) {
            return [];
        }
        $templateSettings = $this->filterConfig->getConfig('template');

        return \array_filter(
            $this->filters,
            static function ($f) use ($templateSettings) {
                /** @var FilterInterface $f */
                return $f->getVisibility() === Visibility::SHOW_ALWAYS
                    || $f->getVisibility() === Visibility::SHOW_CONTENT
                    || ($f->getClassName() === PriceRange::class
                        && ($templateSettings['productlist']['always_show_price_range'] ?? 'N') === 'Y'
                    );
            }
        );
    }

    /**
     * @return int
     */
    public function getFilterCount(): int
    {
        return \count($this->activeFilters);
    }

    /**
     * @param string          $className
     * @param FilterInterface $filter
     * @return bool
     */
    public function override(string $className, FilterInterface $filter): bool
    {
        foreach ($this->filters as $i => $registerdFilter) {
            if ($registerdFilter->getClassName() === $className) {
                $this->filters[$i] = $filter;

                return true;
            }
        }

        return false;
    }

    /**
     * @return Manufacturer
     */
    public function getManufacturerFilter(): FilterInterface
    {
        return $this->manufacturerFilter;
    }

    /**
     * @param Manufacturer|stdClass $filter
     * @return $this
     */
    public function setManufacturerFilter($filter): self
    {
        if (\is_a($filter, stdClass::class) && !isset($filter->kHersteller)) {
            // disallow setting manufacturer filter to empty stdClass
            return $this;
        }
        $this->manufacturerFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasManufacturerFilter(): bool
    {
        return $this->manufacturerFilter->isInitialized();
    }

    /**
     * @return BaseManufacturer
     */
    public function getManufacturer(): FilterInterface
    {
        return $this->manufacturer;
    }

    /**
     * @param Manufacturer $filter
     * @return $this
     */
    public function setManufacturer($filter): self
    {
        if (\is_a($filter, stdClass::class) && !isset($filter->kHersteller)) {
            // disallow setting manufacturer base to empty stdClass
            return $this;
        }
        $this->manufacturer = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasManufacturer(): bool
    {
        return $this->manufacturer->isInitialized();
    }

    /**
     * returns ALL registered characteristic filters
     *
     * @return Characteristic[]
     */
    public function getCharacteristicFilters(): array
    {
        return $this->characteristicFilter;
    }

    /**
     * this method works like pre Shop 4.06 - only returns ACTIVE attribute filters
     *
     * @param null|int $idx
     * @return Characteristic|Characteristic[]
     */
    public function getCharacteristicFilter($idx = null)
    {
        return $idx === null ? $this->characteristicFilter : $this->characteristicFilter[$idx];
    }

    /**
     * @param array|stdClass $filter
     * @return $this
     */
    public function setCharacteristicFilter($filter): self
    {
        if (\is_a($filter, stdClass::class) && !isset($filter->kMerkmal)) {
            // disallow setting attribute filter to empty stdClass
            return $this;
        }
        $this->characteristicFilter = $filter;

        return $this;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function addCharacteristicFilter(FilterInterface $filter): self
    {
        $this->characteristicFilter[] = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCharacteristicFilter(): bool
    {
        return \count($this->characteristicFilter) > 0;
    }

    /**
     * @return BaseCharacteristic
     */
    public function getCharacteristicValue(): FilterInterface
    {
        return $this->characteristicValue;
    }

    /**
     * @param BaseCharacteristic $filter
     * @return $this
     */
    public function setCharacteristicValue($filter): self
    {
        if (\is_a($filter, stdClass::class) && !isset($filter->kMerkmalWert)) {
            // disallow setting attribute value to empty stdClass
            return $this;
        }
        $this->characteristicFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCharacteristicValue(): bool
    {
        return $this->characteristicValue->isInitialized();
    }

    /**
     * @return Characteristic
     */
    public function getCharacteristicFilterCollection(): FilterInterface
    {
        return $this->characteristicFilterCollection;
    }

    /**
     * @return BaseCategory
     */
    public function getCategory(): FilterInterface
    {
        return $this->category;
    }

    /**
     * @param BaseCategory $filter
     * @return $this
     */
    public function setCategory($filter): self
    {
        if (\is_a($filter, stdClass::class) && !isset($filter->kKategorie)) {
            // disallow setting category base to empty stdClass
            return $this;
        }
        $this->category = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCategory(): bool
    {
        return $this->category->isInitialized();
    }

    /**
     * @return Category
     */
    public function getCategoryFilter(): FilterInterface
    {
        return $this->categoryFilter;
    }

    /**
     * @param BaseCategory $filter
     * @return $this
     */
    public function setCategoryFilter($filter): self
    {
        if (\is_a($filter, stdClass::class) && !isset($filter->kKategorie)) {
            // disallow setting category filter to empty stdClass
            return $this;
        }
        $this->categoryFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCategoryFilter(): bool
    {
        return $this->categoryFilter->isInitialized();
    }

    /**
     * @return Search
     */
    public function getSearch(): FilterInterface
    {
        return $this->search;
    }

    /**
     * @return bool
     */
    public function hasSearch(): bool
    {
        return $this->search->getName() !== null;
    }

    /**
     * @return BaseSearchQuery
     */
    public function getSearchQuery(): FilterInterface
    {
        return $this->searchQuery;
    }

    /**
     * @return bool
     */
    public function hasSearchQuery(): bool
    {
        return $this->searchQuery->isInitialized();
    }

    /**
     * @param BaseSearchQuery $filter
     * @return $this
     */
    public function setSearchQuery($filter): self
    {
        $this->searchQuery = $filter;

        return $this;
    }

    /**
     * @param null|int $idx
     * @return Search|Search[]
     */
    public function getSearchFilter(int $idx = null)
    {
        return $idx === null ? $this->searchFilter : $this->searchFilter[$idx];
    }

    /**
     * @return bool
     */
    public function hasSearchFilter(): bool
    {
        return \count($this->searchFilter) > 0;
    }

    /**
     * @return BaseSearchSpecial
     */
    public function getSearchSpecial(): FilterInterface
    {
        return $this->searchSpecial;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function setSearchSpecial(FilterInterface $filter): self
    {
        $this->searchSpecial = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSearchSpecial(): bool
    {
        return $this->searchSpecial->isInitialized();
    }

    /**
     * @return SearchSpecial
     */
    public function getSearchSpecialFilter(): FilterInterface
    {
        return $this->searchSpecialFilter;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function setSearchSpecialFilter(FilterInterface $filter): self
    {
        $this->searchSpecialFilter = $filter;

        return $this;
    }

    /**
     * @return SearchSpecial
     */
    public function getAvailabilityFilter(): FilterInterface
    {
        return $this->availabilityFilter;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function setAvailabilityFilter(FilterInterface $filter): self
    {
        $this->availabilityFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSearchSpecialFilter(): bool
    {
        return $this->searchSpecialFilter->isInitialized();
    }

    /**
     * @return null|object
     */
    public function getRealSearch()
    {
        return empty($this->EchteSuche->cSuche)
            ? null
            : $this->EchteSuche;
    }

    /**
     * @return Rating
     */
    public function getRatingFilter(): FilterInterface
    {
        return $this->ratingFilter;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function setRatingFilter(FilterInterface $filter): self
    {
        $this->ratingFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasRatingFilter(): bool
    {
        return $this->ratingFilter->isInitialized();
    }

    /**
     * @return PriceRange
     */
    public function getPriceRangeFilter(): FilterInterface
    {
        return $this->priceRangeFilter;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function setPriceRangeFilter(FilterInterface $filter): self
    {
        $this->priceRangeFilter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPriceRangeFilter(): bool
    {
        return $this->priceRangeFilter->isInitialized();
    }

    /**
     * @return $this
     */
    public function validate(): self
    {
        if ($this->getFilterCount() === 0) {
            return $this;
        }
        $languageID = $this->getFilterConfig()->getLanguageID();
        $location   = 'Location: ' . $this->getFilterConfig()->getBaseURL();
        if (empty($this->search->getName())
            && !$this->hasManufacturer()
            && !$this->hasCategory()
            && !$this->hasSearchQuery()
            && !$this->hasCharacteristicValue()
            && !$this->hasSearchSpecial()
        ) {
            // we have a manufacturer filter that doesn't filter anything
            if ($this->manufacturerFilter->getSeo($languageID) !== null) {
                \http_response_code(301);
                \header($location . $this->manufacturerFilter->getSeo($languageID));
                exit();
            }
            // we have a category filter that doesn't filter anything
            if ($this->categoryFilter->getSeo($languageID) !== null) {
                \http_response_code(301);
                \header($location . $this->categoryFilter->getSeo($languageID));
                exit();
            }
        } elseif ($this->hasManufacturer()
            && $this->hasManufacturerFilter()
            && $this->manufacturer->getSeo($languageID) !== null
        ) {
            // we have a manufacturer page with some manufacturer filter
            \http_response_code(301);
            \header($location . $this->manufacturer->getSeo($languageID));
            exit();
        } elseif ($this->hasCategory()
            && $this->hasCategoryFilter()
            && $this->category->getSeo($languageID) !== null
        ) {
            // we have a category page with some category filter
            \http_response_code(301);
            \header($location . $this->category->getSeo($languageID));
            exit();
        }

        return $this;
    }

    /**
     * @param Kategorie|null $category
     * @return $this
     */
    public function setUserSort(Kategorie $category = null): self
    {
        $gpcSort = Request::verifyGPCDataInt('Sortierung');
        // user wants to reset default sorting
        if ($gpcSort === \SEARCH_SORT_STANDARD) {
            unset($_SESSION['Usersortierung'], $_SESSION['nUsersortierungWahl'], $_SESSION['UsersortierungVorSuche']);
        }
        // no sorting configured - use default from config
        if (!isset($_SESSION['Usersortierung'])) {
            unset($_SESSION['nUsersortierungWahl']);

            $_SESSION['Usersortierung'] = (int)$this->getFilterConfig()->getConfig('artikeluebersicht')
            ['artikeluebersicht_artikelsortierung'];
        }
        if (!isset($_SESSION['nUsersortierungWahl'])) {
            $_SESSION['Usersortierung'] = (int)$this->getFilterConfig()->getConfig('artikeluebersicht')
            ['artikeluebersicht_artikelsortierung'];
        }
        if (!isset($_SESSION['nUsersortierungWahl']) && $this->getSearch()->getSearchCacheID() > 0) {
            // nur bei initialsuche Sortierung zurücksetzen
            $_SESSION['UsersortierungVorSuche'] = $_SESSION['Usersortierung'];
            $_SESSION['Usersortierung']         = \SEARCH_SORT_STANDARD;
        }
        // custom category attribute
        if ($category !== null && !empty($category->categoryFunctionAttributes[\KAT_ATTRIBUT_ARTIKELSORTIERUNG])) {
            $mapper                     = new SortingType();
            $_SESSION['Usersortierung'] = $mapper->mapUserSorting(
                $category->categoryFunctionAttributes[\KAT_ATTRIBUT_ARTIKELSORTIERUNG]
            );
        }
        if (isset($_SESSION['UsersortierungVorSuche']) && (int)$_SESSION['UsersortierungVorSuche'] > 0) {
            $_SESSION['Usersortierung'] = (int)$_SESSION['UsersortierungVorSuche'];
        }
        // search special sorting
        if ($this->hasSearchSpecial()) {
            $mapping = $this->getSearchSpecialConfigMapping();
            $idx     = $this->getSearchSpecial()->getValue();
            $ssConf  = isset($mapping[$idx]) ?: null;
            if ($ssConf !== null && $ssConf !== -1 && \count($mapping) > 0) {
                $_SESSION['Usersortierung'] = (int)$mapping[$idx];
            }
        }
        // explicitly set by user
        if ($gpcSort > 0 && $gpcSort !== \SEARCH_SORT_STANDARD) {
            $_SESSION['Usersortierung']         = $gpcSort;
            $_SESSION['UsersortierungVorSuche'] = $_SESSION['Usersortierung'];
            $_SESSION['nUsersortierungWahl']    = 1;
        }
        $this->sorting->setActiveSortingType($_SESSION['Usersortierung']);

        return $this;
    }

    /**
     * @return array
     */
    public function getSearchSpecialConfigMapping(): array
    {
        $config = $this->getFilterConfig()->getConfig('suchspecials');

        return [
            \SEARCHSPECIALS_BESTSELLER       => $config['suchspecials_sortierung_bestseller'],
            \SEARCHSPECIALS_SPECIALOFFERS    => $config['suchspecials_sortierung_sonderangebote'],
            \SEARCHSPECIALS_NEWPRODUCTS      => $config['suchspecials_sortierung_neuimsortiment'],
            \SEARCHSPECIALS_TOPOFFERS        => $config['suchspecials_sortierung_topangebote'],
            \SEARCHSPECIALS_UPCOMINGPRODUCTS => $config['suchspecials_sortierung_inkuerzeverfuegbar'],
            \SEARCHSPECIALS_TOPREVIEWS       => $config['suchspecials_sortierung_topbewertet'],
        ];
    }

    /**
     * get list of product IDs matching the current filter
     *
     * @return Collection
     */
    public function getProductKeys(): Collection
    {
        $sorting = $this->getSorting()->getActiveSorting();
        $sql     = (new StateSQL())->from($this->getCurrentStateData());
        $sql->addJoin($sorting->getJoin());
        $sql->setSelect(['tartikel.kArtikel']);
        $sql->setOrderBy($sorting->getOrderBy());
        $sql->setLimit('');
        $sql->setGroupBy(['tartikel.kArtikel']);
        $qry         = $this->getFilterSQL()->getBaseQuery($sql, 'listing');
        $productKeys = \collect(\array_map(
            static function ($e) {
                return (int)$e->kArtikel;
            },
            $this->db->query($qry, ReturnType::ARRAY_OF_OBJECTS)
        ));

        $orderData         = new stdClass();
        $orderData->cJoin  = $sorting->getJoin()->getSQL();
        $orderData->cOrder = $sorting->getOrderBy();

        \executeHook(\HOOK_FILTER_INC_GIBARTIKELKEYS, [
            'oArtikelKey_arr'            => &$productKeys,
            'FilterSQL'                  => new stdClass(),
            'NaviFilter'                 => $this,
            'SortierungsSQL'             => &$orderData,
            'bExtendedJTLSearch'         => $this->bExtendedJTLSearch,
            'oExtendedJTLSearchResponse' => $this->oExtendedJTLSearchResponse
        ]);

        return $productKeys;
    }

    /**
     * checks if a given combination of filter class and filter value is currently active
     *
     * @param string $class
     * @param mixed  $value
     * @return bool
     */
    public function filterOptionIsActive($class, $value): bool
    {
        foreach ($this->getActiveFilters() as $filter) {
            if ($filter->getClassName() !== $class) {
                continue;
            }
            $filterValue = $filter->getValue();
            if ($value === $filterValue) {
                return true;
            }
            if (\is_array($filterValue)) {
                foreach ($filterValue as $val) {
                    if ($val === $value) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param Kategorie|null $category
     * @param bool           $fill - if true, return Artikel class instances, otherwise keys only
     * @param int|null       $limit
     * @return SearchResultsInterface
     */
    public function generateSearchResults(
        Kategorie $category = null,
        bool $fill = true,
        int $limit = null
    ): SearchResultsInterface {
        $productsPerPage        = $limit ?? $this->limits->getProductsPerPageLimit();
        $nLimitN                = $productsPerPage * ($this->nSeite - 1);
        $maxPaginationPageCount = (int)$this->getFilterConfig()->getConfig('artikeluebersicht')
        ['artikeluebersicht_max_seitenzahl'];
        $error                  = false;
        if ($this->searchResults === null) {
            $productList         = new Collection();
            $productKeys         = $this->getProductKeys();
            $productCount        = $productKeys->count();
            $this->searchResults = (new SearchResults())
                ->setProductCount($productCount)
                ->setProductKeys($productKeys);
            if (!empty($this->search->getName())) {
                if ($this->searchQuery->getError() === null) {
                    $this->search->saveQuery($productCount, $this->search->getName(), !$this->bExtendedJTLSearch);
                    $this->search->setQueryID(
                        $this->search->getName() ?? '',
                        $this->getFilterConfig()->getLanguageID()
                    );
                    $this->searchQuery->setValue($this->search->getValue())
                                      ->setSeo($this->getFilterConfig()->getLanguages());
                } else {
                    $error = $this->searchQuery->getError();
                }
            }
            $end = \min($nLimitN + $productsPerPage, $productCount);
            $this->searchResults->setOffsetStart($nLimitN + 1)
                                ->setOffsetEnd($end > 0 ? $end : $productCount);
            $total   = $productsPerPage > 0 ? (int)\ceil($productCount / $productsPerPage) : 1;
            $minPage = (int)\max($this->nSeite - \floor($maxPaginationPageCount / 2), 1);
            $maxPage = $minPage + $maxPaginationPageCount - 1;
            if ($maxPage > $total) {
                $diff     = $total - $maxPage;
                $maxPage  = $total;
                $minPage += $diff;
                $minPage  = (int)\max($minPage, 1);
            }
            $pages = new Info();
            $pages->setMinPage($minPage);
            $pages->setMaxPage($maxPage);
            $pages->setTotalPages($total);
            $pages->setCurrentPage($this->nSeite);

            $this->searchResults->setPages($pages)
                                ->setFilterOptions($this, $category)
                                ->setSearchTermWrite($this->metaData->getHeader());
        } else {
            $productList = $this->searchResults->getProducts();
            $productKeys = $this->searchResults->getProductKeys();
        }
        if ($error !== false) {
            $pages = new Info();
            $pages->setMinPage(0);
            $pages->setMaxPage(0);
            $pages->setTotalPages(0);
            $pages->setCurrentPage(0);

            return $this->searchResults
                ->setPages($pages)
                ->setProductCount(0)
                ->setVisibleProductCount(0)
                ->setProducts($productList)
                ->setSearchUnsuccessful(true)
                ->setSearchTerm(\strip_tags(\trim($this->params['cSuche'])))
                ->setError($error);
        }
        if ($fill === true) { // @todo: slice list of IDs when not filling?
            $opt                        = Artikel::getDefaultOptions();
            $opt->nKategorie            = 1;
            $opt->nVariationen          = 1;
            $opt->nWarenlager           = 1;
            $opt->nRatings              = \PRODUCT_LIST_SHOW_RATINGS === true ? 1 : 0;
            $opt->nVariationDetailPreis = (int)$this->getFilterConfig()->getConfig('artikeldetails')
            ['artikel_variationspreisanzeige'] !== 0
                ? 1
                : 0;
            if ($productsPerPage < 0) {
                $productsPerPage = null;
            }
            foreach ($productKeys->forPage($this->nSeite, $productsPerPage) as $id) {
                $productList->push((new Artikel())->fuelleArtikel($id, $opt));
            }
            $this->searchResults->setVisibleProductCount($productList->count());
        }
        $this->url                             = $this->filterURL->createUnsetFilterURLs($this->url);
        $_SESSION['oArtikelUebersichtKey_arr'] = $productKeys;

        $this->searchResults->setProducts($productList);

        return $this->searchResults;
    }

    /**
     * @param bool        $byType
     * @param string|null $ignore
     * @return array|FilterInterface[]
     */
    public function getActiveFilters(bool $byType = false, string $ignore = null): array
    {
        $activeFilters = select($this->activeFilters, static function (FilterInterface $f) use ($ignore) {
            return $ignore === null || $f->getClassName() !== $ignore;
        });
        if ($byType === false) {
            return $activeFilters;
        }
        $grouped = group($activeFilters, static function (FilterInterface $f) {
            if ($f->isCustom()) {
                return 'custom';
            }

            return $f->isInitialized() && ($param = $f->getUrlParam()) !== ''
                ? $param
                : 'misc';
        });

        return \array_merge([
            'kf'     => [],
            'hf'     => [],
            'mm'     => [],
            'ssf'    => [],
            'tf'     => [],
            'sf'     => [],
            'bf'     => [],
            'custom' => [],
            'misc'   => []
        ], map($grouped, static function ($e) {
            return \array_values($e);
        }));
    }

    /**
     * @param null|string $ignore - filter class to ignore
     * @return StateSQLInterface
     */
    public function getCurrentStateData(string $ignore = null): StateSQLInterface
    {
        $state          = $this->getBaseState();
        $stateCondition = $state->getSQLCondition();
        $stateJoin      = $state->getSQLJoin();
        $data           = new StateSQL();
        $data->setGroupBy([]);
        $data->setOrderBy('');
        $data->setLimit('');
        $data->setSelect([]);
        $having     = [];
        $conditions = [];
        $joins      = \is_array($stateJoin) ? $stateJoin : [$stateJoin];
        if (!empty($stateCondition)) {
            $conditions[] = $stateCondition;
        }
        /** @var FilterInterface $filter */
        foreach ($this->getActiveFilters(true, $ignore) as $type => $active) {
            if ($type !== 'misc' && $type !== 'custom' && \count($active) > 1) {
                $orFilters = select($active, static function (FilterInterface $f) {
                    return $f->getType() === Type::OR;
                });
                /** @var AbstractFilter $filter */
                foreach ($active as $filter) {
                    // the built-in filter behave quite strangely and have to be combined this way
                    $joins[] = $filter->getSQLJoin();
                    if (!\in_array($filter, $orFilters, true)) {
                        $conditions[] = $filter->getSQLCondition();
                    }
                }
                $conditions = $this->extractConditionsFromORFilters($orFilters, $conditions);
            } else {
                // this is the most clean and usual behaviour.
                // 'misc' and custom contain clean new filters that can be calculated by just iterating over the array
                foreach ($active as $filter) {
                    $joins[]   = $filter->getSQLJoin();
                    $condition = $filter->getSQLCondition();
                    if (!empty($condition)) {
                        $conditions[] = "\n#condition from filter " . $type . "\n" . $condition;
                    }
                }
            }
        }
        $data->setConditions($conditions);
        $data->setHaving($having);
        $data->setJoins(flatten($joins));

        return $data;
    }

    /**
     * @param array $filters
     * @param array $conditions
     * @return array
     */
    private function extractConditionsFromORFilters(array $filters, array $conditions): array
    {
        $groupedOrFilters = group($filters, static function (FilterInterface $f) {
            return $f->getClassName() === Characteristic::class
                ? $f->getID()
                : $f->getPrimaryKeyRow();
        });
        foreach ($groupedOrFilters as $idx => $orFilters) {
            /** @var FilterInterface[] $orFilters */
            $values        = \implode(
                ',',
                \array_map(static function ($f) {
                    $val = $f->getValue();

                    return \is_array($val) ? \implode(',', $val) : $val;
                }, $orFilters)
            );
            $first         = first($orFilters);
            $primaryKeyRow = $first->getPrimaryKeyRow();
            $table         = $first->getTableAlias();
            if (empty($table)) {
                $table = first($orFilters)->getTableName();
            }
            $conditions[] = "\n#combined conditions from OR filter " . $primaryKeyRow . "\n" .
                $table . '.kArtikel IN ' .
                '(SELECT kArtikel 
                    FROM ' . $first->getTableName() . ' 
                    WHERE ' . $primaryKeyRow . ' IN (' . $values . '))';
        }

        return $conditions;
    }

    /**
     * @param array $filters
     * @return array
     */
    public static function initCharacteristicFilter(array $filters = []): array
    {
        $filter = [];
        if (\is_array($filters) && \count($filters) > 1) {
            foreach ($filters as $nFilter) {
                if ((int)$nFilter > 0) {
                    $filter[] = (int)$nFilter;
                }
            }
        } elseif (isset($_GET['mf'])) {
            if (\is_string($_GET['mf'])) {
                $filter[] = $_GET['mf'];
            } else {
                foreach ($_GET['mf'] as $mf => $value) {
                    $filter[] = $value;
                }
            }
        } elseif (isset($_POST['mf'])) {
            if (\is_string($_POST['mf'])) {
                $filter[] = $_POST['mf'];
            } else {
                foreach ($_POST['mf'] as $mf => $value) {
                    $filter[] = $value;
                }
            }
        } elseif (isset($_SERVER['REQUEST_METHOD'])) {
            if ($_SERVER['REQUEST_METHOD'] === 'GET' && \count($_GET) > 0) {
                foreach ($_GET as $key => $value) {
                    if (\preg_match('/mf\d+/i', $key)) {
                        $filter[] = (int)$value;
                    }
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && \count($_POST) > 0) {
                foreach ($_POST as $key => $value) {
                    if (\preg_match('/mf\d+/i', $key)) {
                        $filter[] = (int)$value;
                    }
                }
            }
        }

        return $filter;
    }

    /**
     * @param array $filters
     * @return array
     */
    public static function initSearchFilter(array $filters = []): array
    {
        $filter = [];
        if (\is_array($filters) && \count($filters) > 1) {
            foreach ($filters as $nFilter) {
                if ((int)$nFilter > 0) {
                    $filter[] = (int)$nFilter;
                }
            }
        } elseif (isset($_GET['sf'])) {
            if (\is_string($_GET['sf'])) {
                $filter[] = $_GET['sf'];
            } else {
                foreach ($_GET['sf'] as $mf => $value) {
                    $filter[] = $value;
                }
            }
        } elseif (isset($_POST['sf'])) {
            if (\is_string($_POST['sf'])) {
                $filter[] = $_POST['sf'];
            } else {
                foreach ($_POST['sf'] as $mf => $value) {
                    $filter[] = $value;
                }
            }
        } else {
            $i = 1;
            while ($i < 20) {
                if (Request::verifyGPCDataInt('sf' . $i) > 0) {
                    $filter[] = Request::verifyGPCDataInt('sf' . $i);
                }
                ++$i;
            }
        }

        return $filter;
    }

    /**
     * @param array $filters
     * @return array
     */
    public static function initCategoryFilter(array $filters = []): array
    {
        $filter = [];
        if (\is_array($filters) && \count($filters) > 1) {
            foreach ($filters as $value) {
                if ((int)$value > 0) {
                    $filter[] = (int)$value;
                }
            }
        } elseif (isset($_GET['kf'])) {
            if (\is_string($_GET['kf'])) {
                $filter[] = $_GET['kf'];
            } else {
                foreach ($_GET['kf'] as $cf => $value) {
                    $filter[] = $value;
                }
            }
        } elseif (isset($_POST['kf'])) {
            if (\is_string($_POST['kf'])) {
                $filter[] = $_POST['kf'];
            } else {
                foreach ($_POST['kf'] as $cf => $value) {
                    $filter[] = $value;
                }
            }
        } else {
            $i = 1;
            while ($i < 20) {
                if (Request::verifyGPCDataInt('kf' . $i) > 0) {
                    $filter[] = Request::verifyGPCDataInt('kf' . $i);
                }
                ++$i;
            }
        }

        return $filter;
    }

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @return JTLCacheInterface
     */
    public function getCache(): JTLCacheInterface
    {
        return $this->cache;
    }

    /**
     * @param JTLCacheInterface $cache
     */
    public function setCache(JTLCacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @return Config
     */
    public function getFilterConfig(): Config
    {
        return $this->filterConfig;
    }

    /**
     * @param Config $filterConfig
     */
    public function setFilterConfig(Config $filterConfig): void
    {
        $this->filterConfig = $filterConfig;
    }

    /**
     * @return bool
     */
    public function isExtendedJTLSearch(): bool
    {
        return $this->bExtendedJTLSearch;
    }

    /**
     * @param bool $isSearch
     */
    public function setExtendedJTLSearch(bool $isSearch): void
    {
        $this->bExtendedJTLSearch = $isSearch;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        if (\property_exists($this, $name)) {
            return true;
        }
        $mapped = self::getMapping($name);
        if ($mapped === null) {
            return false;
        }
        $method = 'get' . $mapped;
        $result = $this->$method();
        if (\is_a($result, FilterInterface::class)) {
            /** @var FilterInterface $result */
            return $result->isInitialized();
        }

        return \is_array($result)
            ? \count($result) > 0
            : false;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res         = \get_object_vars($this);
        $res['conf'] = '*truncated*';

        return $res;
    }
}
