<?php declare(strict_types=1);

namespace JTL\Filter;

/**
 * Class AbstractFilter
 * @package JTL\Filter
 */
abstract class AbstractFilter implements FilterInterface
{
    /**
     * @var string|null
     */
    protected $icon;

    /**
     * @var bool
     */
    protected $isCustom = true;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    public $cSeo = [];

    /**
     * @var int
     */
    protected $type;

    /**
     * @var string
     */
    protected $urlParam = '';

    /**
     * @var string
     */
    protected $urlParamSEO = '';

    /**
     * @var int|string|array
     */
    protected $value;

    /**
     * @var int
     */
    protected $customerGroupID = 0;

    /**
     * @var array
     */
    protected $availableLanguages = [];

    /**
     * @var bool
     */
    protected $isInitialized = false;

    /**
     * @var string
     */
    protected $className = '';

    /**
     * @var string
     */
    protected $niceName = '';

    /**
     * @var int
     */
    protected $inputType;

    /**
     * @var Option[]
     */
    protected $activeValues;

    /**
     * workaround since built-in filters can be registered multiple times (like Navigationsfilter->KategorieFilter)
     * this makes sure there value is not used more then once when Navigationsfilter::getURL()
     * generates the current URL.
     *
     * @var bool
     */
    private $isChecked = false;

    /**
     * used to create FilterLoesenURLs
     *
     * @var bool
     */
    private $doUnset = false;

    /**
     * @var string|array
     */
    private $unsetFilterURL = '';

    /**
     * @var int
     */
    private $visibility;

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var int
     */
    private $sort = 0;

    /**
     * @var string
     */
    protected $frontendName = '';

    /**
     * list of filter options for CharacteristicFilters etc. that consist of multiple different filter options
     *
     * @var array
     */
    private $filterCollection = [];

    /**
     * @var ProductFilter
     */
    protected $productFilter;

    /**
     * @var mixed
     */
    protected $options;

    /**
     * @var string
     */
    protected $tableName = '';

    /**
     * @var bool
     */
    protected $isActive = false;

    /**
     * @var bool
     */
    protected $paramExclusive = false;

    /**
     * AbstractFilter constructor.
     * @param ProductFilter|null $productFilter
     */
    public function __construct($productFilter = null)
    {
        $this->type       = Type::AND;
        $this->visibility = Visibility::SHOW_ALWAYS;
        $this->inputType  = InputType::SELECT;
        if ($productFilter !== null) {
            $this->setBaseData($productFilter)->setClassName(\get_class($this));
        }
    }

    /**
     * @inheritdoc
     */
    public function init($value): FilterInterface
    {
        if ($value !== null) {
            $this->isInitialized = true;
            $this->setValue($value)->setSeo($this->availableLanguages);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @inheritdoc
     */
    public function setIsActive(bool $active): FilterInterface
    {
        $this->isActive = $active;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setIsInitialized(bool $value): FilterInterface
    {
        $this->isInitialized = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function generateActiveFilterData(): FilterInterface
    {
        $this->activeValues = [];
        $values             = $this->getValue();
        $split              = true;
        if (!\is_array($values)) {
            $split  = false;
            $values = [$values];
        }
        foreach ($values as $value) {
            if ($split === true) {
                $class = $this->getClassName();
                /** @var FilterInterface $instance */
                $instance = new $class($this->getProductFilter());
                $instance->init($value);
            } else {
                $instance = $this;
            }
            $this->activeValues[] = (new Option())
                ->setURL($this->getSeo($this->getLanguageID()))
                ->setFrontendName($instance->getName() ?? '')
                ->setValue($value)
                ->setName($instance->getFrontendName())
                ->setType($this->getType());
        }
        $this->isActive = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setFilterCollection(array $collection): FilterInterface
    {
        $this->filterCollection = $collection;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFilterCollection(bool $onlyVisible = true): array
    {
        return $onlyVisible === false
            ? $this->filterCollection
            : \array_filter(
                $this->filterCollection,
                static function (FilterInterface $f) {
                    return $f->getVisibility() !== Visibility::SHOW_NEVER;
                }
            );
    }

    /**
     * @inheritdoc
     */
    public function setFrontendName(string $name): FilterInterface
    {
        $this->frontendName = \htmlspecialchars($name);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFrontendName(): string
    {
        return $this->frontendName;
    }

    /**
     * @inheritdoc
     */
    public function getVisibility(): int
    {
        return $this->visibility;
    }

    /**
     * @inheritdoc
     */
    public function setVisibility($visibility): FilterInterface
    {
        $this->visibility = Visibility::SHOW_NEVER;
        if (\is_numeric($visibility)) {
            $this->visibility = (int)$visibility;
        } elseif ($visibility === 'content') {
            $this->visibility = Visibility::SHOW_CONTENT;
        } elseif ($visibility === 'box') {
            $this->visibility = Visibility::SHOW_BOX;
        } elseif ($visibility === 'Y') {
            $this->visibility = Visibility::SHOW_ALWAYS;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setUnsetFilterURL($url): FilterInterface
    {
        $this->unsetFilterURL = $url;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUnsetFilterURL($idx = null): ?string
    {
        if (\is_array($idx) && \count($idx) === 1) {
            $idx = $idx[0];
        }

        return $idx === null || \is_string($this->unsetFilterURL)
            ? $this->unsetFilterURL
            : $this->unsetFilterURL[$idx];
    }

    /**
     * @inheritdoc
     */
    public function getAvailableLanguages(): array
    {
        return $this->availableLanguages;
    }

    /**
     * @inheritdoc
     */
    public function addValue($value): FilterInterface
    {
        $this->value[] = (int)$value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }

    /**
     * @inheritdoc
     */
    public function getSeo($idx = null)
    {
        return $idx !== null
            ? ($this->cSeo[$idx] ?? null)
            : $this->cSeo;
    }

    /**
     * @inheritdoc
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType(int $type): FilterInterface
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name): FilterInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOptions($mixed = null): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function setOptions($options): FilterInterface
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setProductFilter(ProductFilter $productFilter): FilterInterface
    {
        $this->productFilter = $productFilter;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProductFilter(): ProductFilter
    {
        return $this->productFilter;
    }

    /**
     * @inheritdoc
     */
    public function setBaseData(ProductFilter $productFilter): FilterInterface
    {
        $this->productFilter      = $productFilter;
        $this->customerGroupID    = $productFilter->getFilterConfig()->getCustomerGroupID();
        $this->availableLanguages = $productFilter->getFilterConfig()->getLanguages();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUrlParam(): string
    {
        return $this->urlParam;
    }

    /**
     * @inheritdoc
     */
    public function setUrlParam($param): FilterInterface
    {
        $this->urlParam = $param;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUrlParamSEO(): string
    {
        return $this->urlParamSEO;
    }

    /**
     * @inheritdoc
     */
    public function setUrlParamSEO($param): FilterInterface
    {
        $this->urlParamSEO = $param;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isCustom(): bool
    {
        return $this->isCustom;
    }

    /**
     * @inheritdoc
     */
    public function setIsCustom(bool $custom): FilterInterface
    {
        $this->isCustom = $custom;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageID(): int
    {
        return $this->productFilter->getFilterConfig()->getLanguageID();
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupID(): int
    {
        return $this->customerGroupID;
    }

    /**
     * @inheritdoc
     */
    public function getConfig($idx = null): array
    {
        return $this->productFilter->getFilterConfig()->getConfig($idx);
    }

    /**
     * @inheritdoc
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @inheritdoc
     */
    public function setClassName($className): FilterInterface
    {
        $this->className = $className;
        $this->setNiceName(\basename(\str_replace('\\', '/', $className)));

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNiceName(): string
    {
        return $this->niceName;
    }

    /**
     * @inheritdoc
     */
    public function setNiceName($name): FilterInterface
    {
        $this->niceName = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsChecked(): bool
    {
        return $this->isChecked;
    }

    /**
     * @inheritdoc
     */
    public function setIsChecked(bool $isChecked): FilterInterface
    {
        $this->isChecked = $isChecked;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDoUnset(): bool
    {
        return $this->doUnset;
    }

    /**
     * @inheritdoc
     */
    public function setDoUnset(bool $doUnset): FilterInterface
    {
        $this->doUnset = $doUnset;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getInputType(): int
    {
        return $this->inputType;
    }

    /**
     * @inheritdoc
     */
    public function setInputType(int $type): FilterInterface
    {
        $this->inputType = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @inheritdoc
     */
    public function setIcon($icon): FilterInterface
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTableAlias(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @inheritdoc
     */
    public function setTableName($name): FilterInterface
    {
        $this->tableName = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getActiveValues($idx = null)
    {
        $activeValues = $this->activeValues ?? $this;
        if (\is_array($activeValues) && \count($activeValues) === 1) {
            $activeValues = $activeValues[0];
        }

        return $activeValues;
    }

    /**
     * @inheritdoc
     */
    public function hide(): FilterInterface
    {
        $this->visibility = Visibility::SHOW_NEVER;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isHidden(): bool
    {
        return $this->visibility === Visibility::SHOW_NEVER;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKeyRow(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getSQLCondition(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return new Join();
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value): FilterInterface
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @inheritdoc
     */
    public function setCount(int $count): FilterInterface
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @inheritdoc
     */
    public function setSort(int $sort): FilterInterface
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return int
     */
    public function getValueCompat()
    {
        return $this->value;
    }

    /**
     * this is only called when someone tries to directly set $NaviFilter->Suchanfrage->kSuchanfrage,
     * $NaviFilter-Kategorie->kKategorie etc.
     * it implies that this filter has to be enabled afterwards
     *
     * @param int $value
     * @return $this
     */
    public function setValueCompat($value): FilterInterface
    {
        $this->value = (int)$value;
        if ($this->value > 0) {
            $this->productFilter->enableFilter($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isParamExclusive(): bool
    {
        return $this->paramExclusive;
    }

    /**
     * @inheritDoc
     */
    public function setParamExclusive(bool $paramExclusive): FilterInterface
    {
        $this->paramExclusive = $paramExclusive;

        return $this;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res                  = \get_object_vars($this);
        $res['config']        = '*truncated*';
        $res['productFilter'] = '*truncated*';

        return $res;
    }

    /**
     * @param string $query
     * @return string
     */
    public function getCacheID(string $query): string
    {
        $value     = $this->getValue();
        $valuePart = $value === null ? '' : \json_encode($value);

        return 'fltr_' . \str_replace('\\', '', static::class) . \md5($query) . $valuePart;
    }
}
