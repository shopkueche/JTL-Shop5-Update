<?php declare(strict_types=1);

namespace JTL\Filter\States;

use JTL\Filter\AbstractFilter;
use JTL\Filter\FilterInterface;
use JTL\Filter\ProductFilter;

/**
 * Class DummyState
 * @package JTL\Filter\States
 */
class DummyState extends AbstractFilter
{
    /**
     * @var null
     */
    public $dummyValue;

    /**
     * DummyState constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('ds')
             ->setUrlParamSEO(null);
    }

    /**
     * @inheritdoc
     */
    public function setValue($value): FilterInterface
    {
        $this->dummyValue = (int)$value;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->dummyValue;
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function init($value): FilterInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return [];
    }
}
