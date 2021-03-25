<?php declare(strict_types=1);

namespace JTL\Filter\Items;

use JTL\DB\ReturnType;
use JTL\Filter\AbstractFilter;
use JTL\Filter\FilterInterface;
use JTL\Filter\Join;
use JTL\Filter\Option;
use JTL\Filter\ProductFilter;
use JTL\Filter\StateSQL;
use JTL\MagicCompatibilityTrait;
use JTL\Shop;

/**
 * Class Rating
 * @package JTL\Filter\Items
 */
class Rating extends AbstractFilter
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    public static $mapping = [
        'nSterne' => 'Value'
    ];

    /**
     * Rating constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('bf')
             ->setVisibility($this->getConfig('navigationsfilter')['bewertungsfilter_benutzen'])
             ->setParamExclusive(true)
             ->setFrontendName(Shop::isAdmin() ? __('filterRatings') : Shop::Lang()->get('Votes'));
    }

    /**
     * @inheritdoc
     */
    public function setValue($value): FilterInterface
    {
        return parent::setValue((int)$value);
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        $this->setName(
            Shop::Lang()->get('from', 'productDetails') . ' ' .
            $this->getValue() . ' ' .
            Shop::Lang()->get($this->getValue() > 0 ? 'starPlural' : 'starSingular')
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKeyRow(): string
    {
        return 'nSterne';
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'ttags';
    }

    /**
     * @inheritdoc
     */
    public function getSQLCondition(): string
    {
        return 'ROUND(tartikelext.fDurchschnittsBewertung, 0) >= ' . $this->getValue();
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return (new Join())
            ->setType('JOIN')
            ->setTable('tartikelext')
            ->setOn('tartikel.kArtikel = tartikelext.kArtikel')
            ->setComment('JOIN from ' . __METHOD__)
            ->setOrigin(__CLASS__);
    }

    /**
     * @inheritdoc
     */
    public function getOptions($mixed = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        if ($this->getConfig('navigationsfilter')['bewertungsfilter_benutzen'] === 'N') {
            $this->hide();
            $this->options = [];

            return $this->options;
        }
        $options = [];
        $state   = $this->productFilter->getCurrentStateData();
        $sql     = (new StateSQL())->from($state);
        $sql->setSelect(['ROUND(tartikelext.fDurchschnittsBewertung, 0) AS nSterne', 'tartikel.kArtikel']);
        $sql->setOrderBy(null);
        $sql->setLimit('');
        $sql->setGroupBy(['tartikel.kArtikel']);
        $sql->addJoin($this->getSQLJoin());

        $baseQuery = $this->productFilter->getFilterSQL()->getBaseQuery($sql);
        $cacheID   = $this->getCacheID($baseQuery);
        if (($cached = $this->productFilter->getCache()->get($cacheID)) !== false) {
            $this->options = $cached;

            return $this->options;
        }
        $res         = $this->productFilter->getDB()->query(
            'SELECT ssMerkmal.nSterne, COUNT(*) AS nAnzahl
                FROM (' . $baseQuery . ' ) AS ssMerkmal
                GROUP BY ssMerkmal.nSterne
                ORDER BY ssMerkmal.nSterne DESC',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $stars       = 0;
        $extraFilter = new self($this->getProductFilter());
        foreach ($res as $row) {
            $stars += (int)$row->nAnzahl;

            $options[] = (new Option())
                ->setParam($this->getUrlParam())
                ->setURL($this->productFilter->getFilterURL()->getURL(
                    $extraFilter->init((int)$row->nSterne)
                ))
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setName(
                    Shop::Lang()->get('from', 'productDetails') . ' ' .
                    $row->nSterne . ' ' .
                    Shop::Lang()->get($row->nSterne > 1 ? 'starPlural' : 'starSingular')
                )
                ->setValue((int)$row->nSterne)
                ->setCount($stars);
        }
        $this->options = $options;
        if (\count($options) === 0) {
            $this->hide();
        }
        $this->productFilter->getCache()->set($cacheID, $options, [\CACHING_GROUP_FILTER]);

        return $options;
    }
}
