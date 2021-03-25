<?php declare(strict_types=1);

namespace JTL\Filter\SortingOptions;

use JTL\Filter\Join;
use JTL\Filter\Option;
use JTL\Filter\ProductFilter;
use JTL\Plugin\PluginInterface;

/**
 * Class AbstractSortingOption
 * @package JTL\Filter\SortingOptions
 */
abstract class AbstractSortingOption extends Option implements SortingOptionInterface
{
    /**
     * @var Join
     */
    protected $join;

    /**
     * @var string
     */
    protected $orderBy = '';

    /**
     * @var int
     */
    protected $priority = 0;

    /**
     * @var PluginInterface|null
     */
    protected $plugin;

    /**
     * @var array
     */
    public static $mapping = [
        'angezeigterName' => 'Name',
        'value'           => 'Value'
    ];

    /**
     * AbstractSortingOption constructor.
     * @param ProductFilter $productFilter
     * @param PluginInterface|null $plugin
     */
    public function __construct(ProductFilter $productFilter, ?PluginInterface $plugin = null)
    {
        parent::__construct($productFilter);
        $this->productFilter = $productFilter;
        $this->plugin        = $plugin;
        $this->join          = new Join();
        $this->isCustom      = false;
    }

    /**
     * @inheritdoc
     */
    public function getJoin(): Join
    {
        return $this->join;
    }

    /**
     * @inheritdoc
     */
    public function setJoin(Join $join): void
    {
        $this->join = $join;
    }

    /**
     * @inheritdoc
     */
    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    /**
     * @inheritdoc
     */
    public function setOrderBy(string $orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @inheritdoc
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @inheritdoc
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res                  = \get_object_vars($this);
        $res['productFilter'] = '*truncated*';

        return $res;
    }
}
