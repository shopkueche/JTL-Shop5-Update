<?php declare(strict_types=1);

namespace JTL\Filter\Items;

use JTL\Catalog\Category\Kategorie;
use JTL\DB\ReturnType;
use JTL\Filter\FilterInterface;
use JTL\Filter\Join;
use JTL\Filter\Option;
use JTL\Filter\ProductFilter;
use JTL\Filter\States\BaseCharacteristic;
use JTL\Filter\StateSQL;
use JTL\Filter\StateSQLInterface;
use JTL\Filter\Type;
use JTL\Language\LanguageHelper;
use JTL\MagicCompatibilityTrait;
use JTL\Media\Image;
use JTL\Shop;
use stdClass;
use function Functional\every;
use function Functional\first;
use function Functional\group;
use function Functional\map;

/**
 * Class Characteristic
 * @package JTL\Filter\Items
 */
class Characteristic extends BaseCharacteristic
{
    use MagicCompatibilityTrait;

    /**
     * @var int
     */
    private $characteristicValueID;

    /**
     * @var int
     */
    private $id;

    /**
     * @var bool
     */
    private $isMultiSelect = false;

    /**
     * @var array
     */
    private $batchCharacteristicData;

    /**
     * @var array
     */
    public static $mapping = [
        'kMerkmal'     => 'CharacteristicIDCompat',
        'kMerkmalWert' => 'ValueCompat',
        'cName'        => 'Name',
        'cWert'        => 'Name'
    ];

    /**
     * Characteristic constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
            ->setUrlParam('mf')
            ->setUrlParamSEO(\SEP_MERKMAL)
            ->setFrontendName(Shop::isAdmin()
                ? __('filterCharacteristics')
                : Shop::Lang()->get('characteristics', 'comparelist'))
            ->setVisibility($this->getConfig('navigationsfilter')['merkmalfilter_verwenden']);
    }

    /**
     * @return bool
     */
    public function isMultiSelect(): bool
    {
        return $this->isMultiSelect;
    }

    /**
     * @param bool $isMultiSelect
     * @return Characteristic
     */
    public function setIsMultiSelect(bool $isMultiSelect): FilterInterface
    {
        $this->isMultiSelect = $isMultiSelect;

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setCharacteristicIDCompat($value): FilterInterface
    {
        $this->id = (int)$value;
        if ($this->value > 0) {
            $this->productFilter->enableFilter($this);
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCharacteristicIDCompat()
    {
        return $this->id;
    }

    /**
     * sets "kMerkmal"
     *
     * @param int $value
     * @return $this
     */
    public function setID($value): FilterInterface
    {
        $this->id = (int)$value;

        return $this;
    }

    /**
     * returns "kMerkmal"
     *
     * @return int|null
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function init($value): FilterInterface
    {
        $this->isInitialized = true;
        if (\is_object($value)) {
            $this->setValue($value->kMerkmalWert)
                ->setID($value->kMerkmal)
                ->setIsMultiSelect($value->nMehrfachauswahl === 1);

            return $this->setType($this->isMultiSelect() ? Type::OR : Type::AND)
                ->setSeo($this->getAvailableLanguages());
        }

        return $this->setValue($value)->setSeo($this->getAvailableLanguages());
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        $value         = $this->getValue();
        $seoData       = $this->batchCharacteristicData[$value]
            ?? $this->productFilter->getDB()->queryPrepared(
                'SELECT tmerkmalwertsprache.cWert, tmerkmalwert.kMerkmal, 
                    tmerkmalwertsprache.cSeo, tmerkmalwertsprache.kSprache
                    FROM tmerkmalwertsprache
                    JOIN tmerkmalwert 
                        ON tmerkmalwert.kMerkmalWert = tmerkmalwertsprache.kMerkmalWert
                    WHERE tmerkmalwertsprache.kMerkmalWert = :val',
                ['val' => $value],
                ReturnType::ARRAY_OF_OBJECTS
            );
        $currentLangID = $this->productFilter->getFilterConfig()->getLanguageID();
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            foreach ($seoData as $seo) {
                $seo->kSprache = (int)$seo->kSprache;
                if ($language->kSprache === $seo->kSprache) {
                    $this->cSeo[$language->kSprache] = $seo->cSeo;
                    if ($language->kSprache === $currentLangID) {
                        $this->setID((int)$seo->kMerkmal)
                            ->setName($seo->cWert)
                            ->setFrontendName($seo->cWert);
                    }
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * @param array $data
     */
    private function setBatchCharacteristicData(array $data): void
    {
        $this->batchCharacteristicData = $data;
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tartikelmerkmal';
    }

    /**
     * @inheritdoc
     */
    public function getSQLCondition(): string
    {
        return "\n" . 'tartikelmerkmal.kArtikel IN (' .
            'SELECT kArtikel FROM ' . $this->getTableName() .
            ' WHERE ' . $this->getPrimaryKeyRow() . ' IN (' .
            $this->getValue() .
            '))' .
            ' #condition from ' . __METHOD__ . ' ' . $this->getName() . "\n";
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return (new Join())
            ->setType('JOIN')
            ->setTable('tartikelmerkmal')
            ->setOn('tartikel.kArtikel = tartikelmerkmal.kArtikel')
            ->setComment('join from ' . __METHOD__)
            ->setOrigin(__CLASS__);
    }

    /**
     * @param int $characteristicValueID
     * @return bool
     */
    public function characteristicValueIsActive($characteristicValueID): bool
    {
        return \array_reduce(
            $this->productFilter->getCharacteristicFilter(),
            static function ($a, $b) use ($characteristicValueID) {
                /** @var Characteristic $b */
                return $a || $b->getValue() === $characteristicValueID;
            },
            false
        );
    }

    /**
     * @param Kategorie|null $category
     * @return StateSQLInterface
     */
    protected function getState(Kategorie $category = null): StateSQLInterface
    {
        $base  = $this->productFilter->getCurrentStateData(self::class);
        $state = (new StateSQL())->from($base);
        $state->setOrderBy('');
        $state->setLimit('');
        $state->setGroupBy([]);
        $state->setSelect(['tmerkmal.cName']);
        // @todo?
        if (true
            || (!$this->productFilter->hasCharacteristicValue() && !$this->productFilter->hasCharacteristicFilter())
        ) {
            $state->addJoin((new Join())
                ->setComment('join1 from ' . __METHOD__)
                ->setType('JOIN')
                ->setTable('tartikelmerkmal')
                ->setOn('tartikel.kArtikel = tartikelmerkmal.kArtikel')
                ->setOrigin(__CLASS__));
        }
        $state->addJoin((new Join())
            ->setComment('join2 from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('tmerkmalwert')
            ->setOn('tmerkmalwert.kMerkmalWert = tartikelmerkmal.kMerkmalWert')
            ->setOrigin(__CLASS__));
        $state->addJoin((new Join())
            ->setComment('join4 from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('tmerkmal')
            ->setOn('tmerkmal.kMerkmal = tartikelmerkmal.kMerkmal')
            ->setOrigin(__CLASS__));

        $langID           = $this->getLanguageID();
        $kStandardSprache = LanguageHelper::getDefaultLanguage()->kSprache;
        if ($langID !== $kStandardSprache) {
            $state->setSelect([
                'COALESCE(tmerkmalsprache.cName, tmerkmal.cName) AS cName',
                'COALESCE(fremdSprache.cSeo, standardSprache.cSeo) AS cSeo',
                'COALESCE(fremdSprache.cWert, standardSprache.cWert) AS cWert'
            ]);
            $state->addJoin((new Join())
                ->setComment('non default lang join1 from ' . __METHOD__)
                ->setType('LEFT JOIN')
                ->setTable('tmerkmalsprache')
                ->setOn('tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal 
                            AND tmerkmalsprache.kSprache = ' . $langID)
                ->setOrigin(__CLASS__));
            $state->addJoin((new Join())
                ->setComment('non default lang join2 from ' . __METHOD__)
                ->setType('INNER JOIN')
                ->setTable('tmerkmalwertsprache AS standardSprache')
                ->setOn('standardSprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                            AND standardSprache.kSprache = ' . $kStandardSprache)
                ->setOrigin(__CLASS__));
            $state->addJoin((new Join())
                ->setComment('non default lang join3 from ' . __METHOD__)
                ->setType('LEFT JOIN')
                ->setTable('tmerkmalwertsprache AS fremdSprache')
                ->setOn('fremdSprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert 
                            AND fremdSprache.kSprache = ' . $langID)
                ->setOrigin(__CLASS__));
        } else {
            $state->setSelect(['tmerkmalwertsprache.cWert', 'tmerkmalwertsprache.cSeo', 'tmerkmal.cName']);
            $state->addJoin((new Join())
                ->setComment('join default lang from ' . __METHOD__)
                ->setType('INNER JOIN')
                ->setTable('tmerkmalwertsprache')
                ->setOn('tmerkmalwertsprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                            AND tmerkmalwertsprache.kSprache = ' . $langID)
                ->setOrigin(__CLASS__));
        }

        if ($this->productFilter->hasCharacteristicFilter()) {
            $activeOrFilterIDs  = [];
            $activeAndFilterIDs = [];
            foreach ($this->productFilter->getCharacteristicFilter() as $filter) {
                $values = $filter->getValue();
                if ($filter->getType() === Type::OR) {
                    if (\is_array($values)) {
                        $activeOrFilterIDs = $values;
                    } else {
                        $activeOrFilterIDs[] = $values;
                    }
                } elseif (\is_array($values)) {
                    $activeAndFilterIDs = $values;
                } else {
                    $activeAndFilterIDs[] = $values;
                }
            }
            $productFilter = $this->productFilter->showChildProducts()
                ? '(innerProduct.kVaterArtikel > 0 OR innerProduct.nIstVater = 0)'
                : 'innerProduct.kVaterArtikel = 0';

            if (\count($activeAndFilterIDs) > 0) {
                $state->addJoin((new Join())
                    ->setComment('join active AND filters from ' . __METHOD__)
                    ->setType('JOIN')
                    ->setTable('(SELECT kArtikel
                                    FROM tartikelmerkmal
                                        WHERE kMerkmalWert IN (' . \implode(', ', $activeAndFilterIDs) . ' )
                                    GROUP BY kArtikel
                                    HAVING COUNT(*) = ' . \count($activeAndFilterIDs) . '
                                ) AS ssj1')
                    ->setOn('tartikel.kArtikel = ssj1.kArtikel')
                    ->setOrigin(__CLASS__));
            }
            if (\count($activeOrFilterIDs) > 0) {
                $state->addSelect(
                    'IF(EXISTS (SELECT 1
                     FROM tartikelmerkmal AS im1
                     INNER JOIN tartikel AS innerProduct ON innerProduct.kArtikel = im1.kArtikel
                        WHERE ' . $productFilter . ' AND im1.kMerkmalWert IN (' .
                    \implode(', ', \array_merge($activeOrFilterIDs, ['tartikelmerkmal.kMerkmalWert'])) . ')
                            AND im1.kArtikel = tartikel.kArtikel
                        GROUP BY innerProduct.kArtikel
                        HAVING COUNT(im1.kArtikel) = (SELECT COUNT(DISTINCT im2.kMerkmal)
                           FROM tartikelmerkmal im2
                           INNER JOIN tartikel AS innerProduct ON innerProduct.kArtikel = im2.kArtikel
                           WHERE ' . $productFilter . ' AND im2.kMerkmalWert IN (' .
                    \implode(
                        ', ',
                        \array_merge($activeOrFilterIDs, ['tartikelmerkmal.kMerkmalWert'])
                    ) . '))), tartikel.kArtikel, NULL) AS kArtikel'
                );
            } else {
                $state->addSelect('tartikel.kArtikel AS kArtikel');
            }
        } else {
            $state->addSelect('tartikel.kArtikel AS kArtikel');
        }
        $state->addSelect('tartikelmerkmal.kMerkmal');
        $state->addSelect('tartikelmerkmal.kMerkmalWert');
        $state->addSelect('tmerkmalwert.cBildPfad AS cMMWBildPfad');
        $state->addSelect('tmerkmal.nSort AS nSortMerkmal');
        $state->addSelect('tmerkmalwert.nSort');
        $state->addSelect('tmerkmal.cTyp');
        $state->addSelect('tmerkmal.nMehrfachauswahl');
        $state->addSelect('tmerkmal.cBildPfad AS cMMBildPfad');
        if ($category !== null
            && !empty($category->categoryFunctionAttributes[\KAT_ATTRIBUT_MERKMALFILTER])
            && $this->productFilter->hasCategory()
        ) {
            $catAttributeFilters = \explode(
                ';',
                $category->categoryFunctionAttributes[\KAT_ATTRIBUT_MERKMALFILTER]
            );
            if (\count($catAttributeFilters) > 0) {
                $state->addCondition('tmerkmal.cName IN (' . \implode(',', map(
                    $catAttributeFilters,
                    static function ($e) {
                            return '"' . $e . '"';
                    }
                )) . ')');
            }
        }

        return $state;
    }

    /**
     * @inheritDoc
     */
    public function getOptions($mixed = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $conf                    = $this->getConfig('navigationsfilter');
        $force                   = $mixed['bForce'] ?? false;
        $characteristicFilters   = [];
        $useCharacteristicFilter = $conf['merkmalfilter_verwenden'] !== 'N';
        $limit                   = $force === true
            ? 0
            : (int)$conf['merkmalfilter_maxmerkmale'];
        $valueLimit              = $force === true
            ? 0
            : (int)$conf['merkmalfilter_maxmerkmalwerte'];
        if (!$force && !$useCharacteristicFilter) {
            return $characteristicFilters;
        }
        $state     = $this->getState($mixed['oAktuelleKategorie'] ?? null);
        $baseQuery = $this->productFilter->getFilterSQL()->getBaseQuery($state);
        $cacheID   = $this->getCacheID($baseQuery);
        if (($cached = $this->productFilter->getCache()->get($cacheID)) !== false) {
            $this->options = $cached;

            return $this->options;
        }
        $qryRes           = $this->productFilter->getDB()->executeQuery(
            'SELECT ssMerkmal.cSeo, ssMerkmal.kMerkmal, ssMerkmal.kMerkmalWert, ssMerkmal.cMMWBildPfad, 
            ssMerkmal.nMehrfachauswahl, ssMerkmal.cWert, ssMerkmal.cName, ssMerkmal.cTyp, 
            ssMerkmal.cMMBildPfad, COUNT(DISTINCT ssMerkmal.kArtikel) AS nAnzahl
                FROM (' . $baseQuery . ') AS ssMerkmal
                GROUP BY ssMerkmal.kMerkmalWert
                ORDER BY ssMerkmal.nSortMerkmal, ssMerkmal.nSort, ssMerkmal.cWert',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $currentValue     = $this->productFilter->getCharacteristicValue()->getValue();
        $additionalFilter = new self($this->productFilter);
        $filterCollection = group($qryRes, static function ($e) {
            return $e->kMerkmal;
        });
        foreach ($filterCollection as $characteristicID => $characteristicValues) {
            $first                                = first($characteristicValues);
            $characteristic                       = new stdClass();
            $characteristic->kMerkmal             = (int)$first->kMerkmal;
            $characteristic->nMehrfachauswahl     = (int)$first->nMehrfachauswahl;
            $characteristic->cName                = $first->cName;
            $characteristic->cMMBildPfad          = $first->cMMBildPfad;
            $characteristic->cTyp                 = $first->cTyp;
            $characteristic->characteristicValues = map($characteristicValues, static function ($e) {
                $av               = new stdClass();
                $av->kMerkmal     = (int)$e->kMerkmal;
                $av->kMerkmalWert = (int)$e->kMerkmalWert;
                $av->cMMWBildPfad = $e->cMMWBildPfad;
                $av->cWert        = $e->cWert;
                $av->nAnzahl      = (int)$e->nAnzahl;

                return $av;
            });
            $filterCollection[$characteristicID]  = $characteristic;
        }
        $imageBaseURL       = Shop::getImageBaseURL();
        $filterURLGenerator = $this->productFilter->getFilterURL();
        $i                  = 0;
        foreach ($filterCollection as $filter) {
            $baseSrcSmall  = \mb_strlen($filter->cMMBildPfad) > 0
                ? \PFAD_MERKMALBILDER_KLEIN . $filter->cMMBildPfad
                : \BILD_KEIN_MERKMALBILD_VORHANDEN;
            $baseSrcNormal = \mb_strlen($filter->cMMBildPfad) > 0
                ? \PFAD_MERKMALBILDER_NORMAL . $filter->cMMBildPfad
                : \BILD_KEIN_MERKMALBILD_VORHANDEN;

            $option = new Option();
            $option->setURL('');
            $option->setData('cTyp', $filter->cTyp)
                ->setData('kMerkmal', $filter->kMerkmal)
                ->setData('cBildpfadKlein', $baseSrcSmall)
                ->setData('cBildpfadNormal', $baseSrcNormal)
                ->setData('cBildURLKlein', $imageBaseURL . $baseSrcSmall)
                ->setData('cBildURLNormal', $imageBaseURL . $baseSrcNormal);
            $option->setImageType(Image::TYPE_CHARACTERISTIC);
            $option->setID($filter->kMerkmal);
            $option->setParam($this->getUrlParam());
            $option->setType($filter->nMehrfachauswahl === 1 ? Type::OR : Type::AND);
            $option->setType($this->getType());
            $option->setClassName($this->getClassName());
            $option->setName($filter->cName);
            $option->setFrontendName($filter->cName);
            $option->setValue($filter->kMerkmal);
            $option->setCount(0);
            $option->generateAllImageSizes();
            $additionalFilter->setBatchCharacteristicData(
                $this->batchGetDataForCharacteristicValue($filter->characteristicValues)
            );
            foreach ($filter->characteristicValues as $filterValue) {
                $filterValue->kMerkmalWert = (int)$filterValue->kMerkmalWert;
                $characteristicOption      = new Option();
                $baseSrcSmall              = \mb_strlen($filterValue->cMMWBildPfad) > 0
                    ? \PFAD_MERKMALWERTBILDER_KLEIN . $filterValue->cMMWBildPfad
                    : \BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                $baseSrcNormal             = \mb_strlen($filterValue->cMMWBildPfad) > 0
                    ? \PFAD_MERKMALWERTBILDER_NORMAL . $filterValue->cMMWBildPfad
                    : \BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                $characteristicOption->setData('kMerkmalWert', $filterValue->kMerkmalWert)
                    ->setData('kMerkmal', (int)$filter->kMerkmal)
                    ->setData('cWert', $filterValue->cWert);
                $characteristicOption->setIsActive($currentValue === $filterValue->kMerkmalWert
                    || $this->characteristicValueIsActive($filterValue->kMerkmalWert));
                $characteristicOption->setData('cBildpfadKlein', $baseSrcSmall)
                    ->setData('cBildpfadNormal', $baseSrcNormal)
                    ->setData('cBildURLKlein', $imageBaseURL . $baseSrcSmall)
                    ->setData('cBildURLNormal', $imageBaseURL . $baseSrcNormal);
                $characteristicOption->setType($filter->nMehrfachauswahl === 1 ? Type::OR : Type::AND);
                $characteristicOption->setClassName($this->getClassName());
                $characteristicOption->setParam($this->getUrlParam());
                $characteristicOption->setName(\htmlentities($filterValue->cWert));
                $characteristicOption->setValue($filterValue->cWert);
                $characteristicOption->setCount((int)$filterValue->nAnzahl);
                $characteristicOption->setImageType(Image::TYPE_CHARACTERISTIC_VALUE);
                $characteristicOption->setID($filterValue->kMerkmalWert);
                if ($characteristicOption->isActive()) {
                    $option->setIsActive(true);
                }
                $url = $filterURLGenerator->getURL($additionalFilter->init($filterValue->kMerkmalWert));
                $characteristicOption->setURL($url);
                $characteristicOption->generateAllImageSizes();
                $option->addOption($characteristicOption);
            }
            // backwards compatibility
            $characteristicOptions = $option->getOptions() ?? [];
            $option->setData('oMerkmalWerte_arr', $characteristicOptions);
            if (($optionsCount = \count($characteristicOptions)) > 0) {
                $characteristicFilters[] = $option->setCount($optionsCount);
            }
            if ($limit > 0 && ++$i >= $limit) {
                break;
            }
        }
        foreach ($characteristicFilters as $af) {
            /** @var Option $af */
            $options = $af->getOptions();
            if (!\is_array($options)) {
                continue;
            }
            if ($this->isNumeric($af)) {
                $this->sortNumeric($af);
            }
            $this->applyOptionLimit($af, $valueLimit);
        }
        $this->options = $characteristicFilters;
        $this->productFilter->getCache()->set(
            $cacheID,
            $characteristicFilters,
            [\CACHING_GROUP_FILTER, \CACHING_GROUP_FILTER_CHARACTERISTIC]
        );

        return $characteristicFilters;
    }

    /**
     * @param Option $option
     * @return bool
     */
    protected function isNumeric(Option $option): bool
    {
        return every($option->getOptions(), static function (Option $item) {
            return \is_numeric($item->getValue());
        });
    }

    /**
     * @param Option $option
     */
    protected function sortNumeric(Option $option): void
    {
        $options = $option->getOptions();
        \usort($options, static function (Option $a, Option $b) {
            return $a->getValue() <=> $b->getValue();
        });
        $option->setOptions($options);
    }

    /**
     * @param Option $option
     */
    protected function sortByCountDesc(Option $option): void
    {
        $options = $option->getOptions();
        \usort($options, static function (Option $a, Option $b) {
            return -($a->getCount() <=> $b->getCount());
        });
        $option->setOptions($options);
    }

    /**
     * @param Option $option
     * @param int    $limit
     */
    protected function applyOptionLimit(Option $option, int $limit): void
    {
        if ($limit <= 0 || $limit >= \count($option->getOptions())) {
            return;
        }
        $this->sortByCountDesc($option);
        $option->setOptions(\array_slice($option->getOptions(), 0, $limit));
    }

    /**
     * @param array $characteristicValues
     * @return array
     */
    protected function batchGetDataForCharacteristicValue(array $characteristicValues): array
    {
        if (\count($characteristicValues) === 0) {
            return [];
        }
        $characteristicValueIDs = \implode(',', \array_map(static function ($row) {
            return (int)$row->kMerkmalWert;
        }, $characteristicValues));
        $queryResult            = $this->productFilter->getDB()->query(
            'SELECT tmerkmalwertsprache.cWert, tmerkmalwertsprache.kMerkmalWert, 
            tmerkmalwertsprache.cSeo, tmerkmalwert.kMerkmal, tmerkmalwertsprache.kSprache
                FROM tmerkmalwertsprache
                JOIN tmerkmalwert 
                    ON tmerkmalwert.kMerkmalWert = tmerkmalwertsprache.kMerkmalWert
                WHERE tmerkmalwertsprache.kMerkmalWert IN (' . $characteristicValueIDs . ')',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $result                 = [];
        foreach ($queryResult as $row) {
            $row->kMerkmalWert = (int)$row->kMerkmalWert;
            $row->kMerkmal     = (int)$row->kMerkmal;
            $row->kSprache     = (int)$row->kSprache;
            if (!isset($result[$row->kMerkmalWert])) {
                $result[$row->kMerkmalWert] = [];
            }
            $result[$row->kMerkmalWert][$row->kSprache] = $row;
        }

        return $result;
    }
}
