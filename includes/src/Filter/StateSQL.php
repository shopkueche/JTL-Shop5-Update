<?php declare(strict_types=1);

namespace JTL\Filter;

use function Functional\reduce_left;

/**
 * Class StateSQL
 * @package JTL\Filter
 */
class StateSQL implements StateSQLInterface
{
    /**
     * @var array
     */
    protected $having = [];

    /**
     * @var array
     */
    protected $conditions = [];

    /**
     * @var array
     */
    protected $joins = [];

    /**
     * @var array
     */
    protected $select = ['tartikel.kArtikel'];

    /**
     * @var string|null
     */
    private $orderBy = '';

    /**
     * @var string
     */
    private $limit = '';

    /**
     * @var array
     */
    private $groupBy = ['tartikel.kArtikel'];

    /**
     * StateSQL constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param StateSQLInterface $source
     * @return $this
     */
    public function from(StateSQLInterface $source): StateSQLInterface
    {
        $this->setJoins($source->getJoins());
        $this->setSelect($source->getSelect());
        $this->setConditions($source->getConditions());
        $this->setHaving($source->getHaving());

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getHaving(): array
    {
        return $this->having;
    }

    /**
     * @inheritdoc
     */
    public function setHaving(array $having): void
    {
        $this->having = $having;
    }

    /**
     * @inheritdoc
     */
    public function addHaving(string $having): array
    {
        $this->having[] = $having;

        return $this->having;
    }

    /**
     * @inheritdoc
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @inheritdoc
     */
    public function setConditions(array $conditions): void
    {
        $this->conditions = $conditions;
    }

    /**
     * @inheritdoc
     */
    public function addCondition(string $condition): array
    {
        $this->conditions[] = $condition;

        return $this->conditions;
    }

    /**
     * @inheritdoc
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * @inheritdoc
     */
    public function getDeduplicatedJoins(): array
    {
        $checked = [];

        return reduce_left($this->joins, static function (JoinInterface $value, $d, $c, $reduction) use (&$checked) {
            $key = $value->getTable();
            if (!\in_array($key, $checked, true)) {
                $checked[]   = $key;
                $reduction[] = $value;
            }

            return $reduction;
        }, []);
    }

    /**
     * @inheritdoc
     */
    public function setJoins(array $joins): void
    {
        $this->joins = $joins;
    }

    /**
     * @inheritdoc
     */
    public function addJoin(JoinInterface $join): array
    {
        $this->joins[] = $join;

        return $this->joins;
    }

    /**
     * @inheritdoc
     */
    public function getSelect(): array
    {
        return $this->select;
    }

    /**
     * @inheritdoc
     */
    public function setSelect(array $select): void
    {
        $this->select = $select;
    }

    /**
     * @inheritdoc
     */
    public function addSelect(string $select): array
    {
        $this->select[] = $select;

        return $this->select;
    }

    /**
     * @return string|null
     */
    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    /**
     * @param string|null $orderBy
     */
    public function setOrderBy($orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @return string
     */
    public function getLimit(): string
    {
        return $this->limit;
    }

    /**
     * @param string $limit
     */
    public function setLimit(string $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return array
     */
    public function getGroupBy(): array
    {
        return $this->groupBy;
    }

    /**
     * @param string $groupBy
     * @return array
     */
    public function addGroupBy(string $groupBy): array
    {
        $this->groupBy[] = $groupBy;

        return $this->groupBy;
    }

    /**
     * @param array $groupBy
     */
    public function setGroupBy(array $groupBy): void
    {
        $this->groupBy = $groupBy;
    }
}
