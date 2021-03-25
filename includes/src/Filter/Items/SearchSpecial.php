<?php declare(strict_types=1);

namespace JTL\Filter\Items;

use JTL\DB\ReturnType;
use JTL\Filter\AbstractFilter;
use JTL\Filter\FilterInterface;
use JTL\Filter\Join;
use JTL\Filter\Option;
use JTL\Filter\ProductFilter;
use JTL\Filter\StateSQL;
use JTL\Filter\Type;
use JTL\MagicCompatibilityTrait;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class SearchSpecial
 * @package JTL\Filter\Items
 */
class SearchSpecial extends AbstractFilter
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    public static $mapping = [
        'cName' => 'Name',
        'kKey'  => 'ValueCompat'
    ];

    /**
     * SearchSpecial constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('qf')
             ->setFrontendName(Shop::isAdmin() ? __('filterSearchSpecial') : Shop::Lang()->get('specificProducts'))
             ->setVisibility($this->getConfig('navigationsfilter')['allgemein_suchspecialfilter_benutzen'])
             ->setType($this->getConfig('navigationsfilter')['search_special_filter_type'] === 'O'
                 ? Type::OR
                 : Type::AND);
    }

    /**
     * @inheritdoc
     */
    public function setValue($value): FilterInterface
    {
        $this->value = \is_array($value) ? \array_map('\intval', $value) : (int)$value;

        return $this;
    }

    /**
     * @param array|int|string $value
     * @return $this
     */
    public function setValueCompat($value): FilterInterface
    {
        $this->value = [$value];

        return $this;
    }

    /**
     * @return int
     */
    public function getValueCompat()
    {
        return \is_array($this->value) ? $this->value[0] : $this->value;
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        $val = $this->getValue();
        if ((\is_numeric($val) && $val > 0) || (\is_array($val) && \count($val) > 0)) {
            if (!\is_array($val)) {
                $val = [$val];
            }
            $seoData = $this->productFilter->getDB()->query(
                "SELECT tseo.cSeo, tseo.kSprache
                    FROM tseo
                    WHERE cKey = 'suchspecial' 
                        AND kKey IN (" . \implode(', ', $val) . ')
                    ORDER BY kSprache',
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($languages as $language) {
                $this->cSeo[$language->kSprache] = '';
                foreach ($seoData as $seo) {
                    $seo->kSprache = (int)$seo->kSprache;
                    if ($language->kSprache === $seo->kSprache) {
                        $this->cSeo[$language->kSprache] = $seo->cSeo;
                    }
                }
            }
            switch ($val[0]) {
                case \SEARCHSPECIALS_BESTSELLER:
                    $this->setName(Shop::Lang()->get('bestsellers'));
                    break;
                case \SEARCHSPECIALS_SPECIALOFFERS:
                    $this->setName(Shop::Lang()->get('specialOffers'));
                    break;
                case \SEARCHSPECIALS_NEWPRODUCTS:
                    $this->setName(Shop::Lang()->get('newProducts'));
                    break;
                case \SEARCHSPECIALS_TOPOFFERS:
                    $this->setName(Shop::Lang()->get('topOffers'));
                    break;
                case \SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $this->setName(Shop::Lang()->get('upcomingProducts'));
                    break;
                case \SEARCHSPECIALS_TOPREVIEWS:
                    $this->setName(Shop::Lang()->get('topReviews'));
                    break;
                default:
                    // invalid search special ID
                    Shop::$is404        = true;
                    Shop::$kSuchspecial = 0;
                    break;
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKeyRow(): string
    {
        return 'kKey';
    }

    /**
     * @inheritdoc
     */
    public function getSQLCondition(): string
    {
        $or         = $this->getType() === Type::OR;
        $conf       = $this->getConfig();
        $conditions = [];
        $values     = $this->getValue();
        if (!\is_array($values)) {
            $values = [$values];
        }
        foreach ($values as $value) {
            switch ($value) {
                case \SEARCHSPECIALS_BESTSELLER:
                    $minSales = ($min = (int)$conf['global']['global_bestseller_minanzahl']) > 0
                        ? $min
                        : 100;

                    $conditions[] = 'ROUND(tbestseller.fAnzahl) >= ' . $minSales;
                    break;

                case \SEARCHSPECIALS_SPECIALOFFERS:
                    if ($this->productFilter->hasSearchSpecial()) {
                        break;
                    }
                    $tasp = 'tartikelsonderpreis';
                    $tsp  = 'tsonderpreise';
                    if (!$this->productFilter->hasPriceRangeFilter()) {
                        $tasp = 'tasp';
                        $tsp  = 'tsp';
                    }
                    $conditions[] = $tasp . ' .kArtikel = tartikel.kArtikel
                                        AND ' . $tasp . ".cAktiv = 'Y' 
                                        AND " . $tasp . '.dStart <= NOW()
                                        AND (' . $tasp . '.dEnde >= CURDATE() 
                                            OR ' . $tasp . '.dEnde IS NULL)
                                        AND ' . $tsp . ' .kKundengruppe = ' . Frontend::getCustomerGroup()->getID();
                    break;

                case \SEARCHSPECIALS_NEWPRODUCTS:
                    $days = ($d = $conf['boxen']['box_neuimsortiment_alter_tage']) > 0
                        ? (int)$d
                        : 30;

                    $conditions[] = "tartikel.cNeu = 'Y' 
                                AND DATE_SUB(NOW(),INTERVAL " . $days  . ' DAY) < tartikel.dErstellt';
                    break;

                case \SEARCHSPECIALS_TOPOFFERS:
                    $conditions[] = "tartikel.cTopArtikel = 'Y'";
                    break;

                case \SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $conditions[] = 'NOW() < tartikel.dErscheinungsdatum';
                    break;

                case \SEARCHSPECIALS_TOPREVIEWS:
                    if (!$this->productFilter->hasRatingFilter()) {
                        $minStars     = ($m = $conf['boxen']['boxen_topbewertet_minsterne']) > 0
                            ? (int)$m
                            : 4;
                        $conditions[] = 'ROUND(taex.fDurchschnittsBewertung) >= ' . $minStars;
                    }
                    break;

                default:
                    break;
            }
        }
        $conditions = \array_map(static function ($e) {
            return '(' . $e . ')';
        }, $conditions);

        return \count($conditions) > 0
            ? '(' . \implode($or === true ? ' OR ' : ' AND ', $conditions) . ')'
            : '';
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        $joins     = [];
        $values    = $this->getValue();
        $joinType  = $this->getType() === Type::AND
            ? 'JOIN'
            : 'LEFT JOIN';
        $baseValue = $this->productFilter->getSearchSpecial()->getValue();
        if (!\is_array($values)) {
            $values = [$values];
        }
        foreach ($values as $value) {
            switch ($value) {
                case \SEARCHSPECIALS_BESTSELLER:
                    if ($baseValue === $value) {
                        break;
                    }
                    $joins[] = (new Join())
                        ->setType($joinType)
                        ->setTable('tbestseller')
                        ->setOn('tbestseller.kArtikel = tartikel.kArtikel')
                        ->setComment('bestseller JOIN from ' . __METHOD__)
                        ->setOrigin(__CLASS__);
                    break;

                case \SEARCHSPECIALS_SPECIALOFFERS:
                    if ($baseValue === $value) {
                        break;
                    }
                    if (!$this->productFilter->hasPriceRangeFilter()) {
                        $joins[] = (new Join())
                            ->setType($joinType)
                            ->setTable('tartikelsonderpreis AS tasp')
                            ->setOn('tasp.kArtikel = tartikel.kArtikel')
                            ->setComment('special offers JOIN from ' . __METHOD__)
                            ->setOrigin(__CLASS__);
                        $joins[] = (new Join())
                            ->setType($joinType)
                            ->setTable('tsonderpreise AS tsp')
                            ->setOn('tsp.kArtikelSonderpreis = tasp.kArtikelSonderpreis')
                            ->setComment('special offers JOIN2 from ' . __METHOD__)
                            ->setOrigin(__CLASS__);
                    }
                    break;

                case \SEARCHSPECIALS_TOPREVIEWS:
                    if ($baseValue === $value) {
                        break;
                    }
                    if (!$this->productFilter->hasRatingFilter()) {
                        $joins[] = (new Join())
                            ->setType($joinType)
                            ->setTable('tartikelext AS taex ')
                            ->setOn('taex.kArtikel = tartikel.kArtikel')
                            ->setComment('top reviews JOIN from ' . __METHOD__)
                            ->setOrigin(__CLASS__);
                    }
                    break;

                case \SEARCHSPECIALS_NEWPRODUCTS:
                case \SEARCHSPECIALS_TOPOFFERS:
                case \SEARCHSPECIALS_UPCOMINGPRODUCTS:
                default:
                    break;
            }
        }

        return $joins;
    }

    /**
     * @inheritdoc
     */
    public function getOptions($mixed = null): array
    {
        if ($this->getConfig('navigationsfilter')['allgemein_suchspecialfilter_benutzen'] === 'N') {
            $this->options = [];
        }
        if ($this->options !== null) {
            return $this->options;
        }
        $baseValue        = $this->productFilter->getSearchSpecial()->getValue();
        $name             = '';
        $options          = [];
        $additionalFilter = new self($this->productFilter);
        $ignore           = $this->getType() === Type::OR
            ? $this->getClassName()
            : null;
        $state            = (new StateSQL())->from($this->productFilter->getCurrentStateData($ignore));
        $cacheID          = $this->getCacheID($this->productFilter->getFilterSQL()->getBaseQuery($state));
        if (($cached = $this->productFilter->getCache()->get($cacheID)) !== false) {
            $this->options = $cached;

            return $this->options;
        }
        for ($i = 1; $i < 7; ++$i) {
            $state = (new StateSQL())->from($this->productFilter->getCurrentStateData($ignore));
            $state->setSelect(['tartikel.kArtikel']);
            $state->setOrderBy(null);
            $state->setLimit('');
            $state->setGroupBy(['tartikel.kArtikel']);
            switch ($i) {
                case \SEARCHSPECIALS_BESTSELLER:
                    $name     = Shop::Lang()->get('bestsellers');
                    $minSales = (($min = $this->getConfig('global')['global_bestseller_minanzahl']) > 0)
                        ? (int)$min
                        : 100;

                    $state->addJoin((new Join())
                        ->setComment('bestseller JOIN from ' . __METHOD__)
                        ->setType('JOIN')
                        ->setTable('tbestseller')
                        ->setOn('tbestseller.kArtikel = tartikel.kArtikel')
                        ->setOrigin(__CLASS__));
                    $state->addCondition('ROUND(tbestseller.fAnzahl) >= ' . $minSales);
                    break;
                case \SEARCHSPECIALS_SPECIALOFFERS:
                    $name = Shop::Lang()->get('specialOffer');
                    if (true || !$this->isInitialized()) {
                        $state->addJoin((new Join())
                            ->setComment('special offer JOIN1 from ' . __METHOD__)
                            ->setType('JOIN')
                            ->setTable('tartikelsonderpreis')
                            ->setOn('tartikelsonderpreis.kArtikel = tartikel.kArtikel')
                            ->setOrigin(__CLASS__));
                        $state->addJoin((new Join())
                            ->setComment('special offer JOIN2 from ' . __METHOD__)
                            ->setType('JOIN')
                            ->setTable('tsonderpreise')
                            ->setOn('tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis')
                            ->setOrigin(__CLASS__));
                        $tsonderpreise = 'tsonderpreise';
                    } else {
                        $tsonderpreise = 'tsonderpreise';
                    }
                    $state->addCondition("tartikelsonderpreis.cAktiv = 'Y' 
                        AND tartikelsonderpreis.dStart <= NOW()");
                    $state->addCondition('(tartikelsonderpreis.dEnde IS NULL
                        OR tartikelsonderpreis.dEnde >= CURDATE())');
                    $state->addCondition($tsonderpreise . '.kKundengruppe = ' . $this->getCustomerGroupID());
                    break;
                case \SEARCHSPECIALS_NEWPRODUCTS:
                    $name = Shop::Lang()->get('newProducts');
                    $days = (($age = $this->getConfig('boxen')['box_neuimsortiment_alter_tage']) > 0)
                        ? (int)$age
                        : 30;
                    $state->addCondition("tartikel.cNeu = 'Y' 
                        AND DATE_SUB(NOW(), INTERVAL " . $days . ' DAY) < tartikel.dErstellt');
                    break;
                case \SEARCHSPECIALS_TOPOFFERS:
                    $name = Shop::Lang()->get('topOffer');
                    $state->addCondition("tartikel.cTopArtikel = 'Y'");
                    break;
                case \SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $name = Shop::Lang()->get('upcomingProducts');
                    $state->addCondition('NOW() < tartikel.dErscheinungsdatum');
                    break;
                case \SEARCHSPECIALS_TOPREVIEWS:
                    $name = Shop::Lang()->get('topReviews');
                    if (!$this->productFilter->hasRatingFilter()) {
                        $state->addJoin((new Join())
                            ->setComment('top reviews JOIN from ' . __METHOD__)
                            ->setType('JOIN')
                            ->setTable('tartikelext')
                            ->setOn('tartikelext.kArtikel = tartikel.kArtikel')
                            ->setOrigin(__CLASS__));
                    }
                    $state->addCondition('ROUND(tartikelext.fDurchschnittsBewertung) >= ' .
                        (int)$this->getConfig('boxen')['boxen_topbewertet_minsterne']);
                    break;
                default:
                    break;
            }
            $qry    = $this->productFilter->getFilterSQL()->getBaseQuery($state);
            $qryRes = $this->productFilter->getDB()->query($qry, ReturnType::ARRAY_OF_OBJECTS);
            if (($count = \count($qryRes)) > 0) {
                if ($baseValue === $i) {
                    continue;
                }
                $options[$i] = (new Option())
                    ->setIsActive($this->productFilter->filterOptionIsActive($this->getClassName(), $i))
                    ->setURL($this->productFilter->getFilterURL()->getURL($additionalFilter->init($i)))
                    ->setType($this->getType())
                    ->setClassName($this->getClassName())
                    ->setParam($this->getUrlParam())
                    ->setName($name)
                    ->setValue($i)
                    ->setCount($count)
                    ->setSort(0);
            }
        }
        $this->options = $options;
        $this->productFilter->getCache()->set($cacheID, $options, [\CACHING_GROUP_FILTER]);

        return $options;
    }
}
