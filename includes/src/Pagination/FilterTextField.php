<?php

namespace JTL\Pagination;

use JTL\Shop;

/**
 * Class FilterTextField
 * @package JTL\Pagination
 */
class FilterTextField extends FilterField
{
    /**
     * @var int
     */
    protected $testOp = Operation::CUSTOM;

    /**
     * @var int
     */
    protected $dataType = DataType::TEXT;

    /**
     * @var bool
     */
    protected $customTestOp = true;

    /**
     * FilterTextField constructor.
     *
     * @param Filter       $filter
     * @param string|array $title - either title-string for this field or a pair of short title and long title
     * @param string|array $column - column/field or array of them to be searched disjunctively (OR)
     * @param int          $testOp
     * @param int          $dataType
     */
    public function __construct(
        $filter,
        $title,
        $column,
        int $testOp = Operation::CUSTOM,
        int $dataType = DataType::TEXT
    ) {
        parent::__construct($filter, 'text', $title, $column);

        $this->testOp       = $testOp;
        $this->dataType     = $dataType;
        $this->customTestOp = $this->testOp === Operation::CUSTOM;

        if ($this->customTestOp) {
            $this->testOp = $filter->getAction() === $filter->getID() . '_filter'
                ? (int)$_GET[$filter->getID() . '_' . $this->id . '_op']
                : (
                $filter->getAction() === $filter->getID() . '_resetfilter'
                    ? 1
                    : ($filter->hasSessionField($this->id . '_op')
                    ? (int)$filter->getSessionField($this->id . '_op')
                    : 1
                ));
        }
    }

    /**
     * @return string|null
     */
    public function getWhereClause(): ?string
    {
        if ($this->value !== ''
            || ($this->dataType === DataType::TEXT
                && ($this->testOp === Operation::EQUALS || $this->testOp === Operation::NOT_EQUAL))
        ) {
            $value   = Shop::Container()->getDB()->escape($this->value);
            $columns = \is_array($this->column)
                ? $this->column
                : [$this->column];
            $or      = [];
            foreach ($columns as $column) {
                switch ($this->testOp) {
                    case Operation::CONTAINS:
                        $or[] = $column . " LIKE '%" . $value . "%'";
                        break;
                    case Operation::BEGINS_WITH:
                        $or[] = $column . " LIKE '" . $value . "%'";
                        break;
                    case Operation::ENDS_WITH:
                        $or[] = $column . " LIKE '%" . $value . "'";
                        break;
                    case Operation::EQUALS:
                        $or[] = $column . " = '" . $value . "'";
                        break;
                    case Operation::LOWER_THAN:
                        $or[] = $column . " < '" . $value . "'";
                        break;
                    case Operation::GREATER_THAN:
                        $or[] = $column . " > '" . $value . "'";
                        break;
                    case Operation::LOWER_THAN_EQUAL:
                        $or[] = $column . " <= '" . $value . "'";
                        break;
                    case Operation::GREATER_THAN_EQUAL:
                        $or[] = $column . " >= '" . $value . "'";
                        break;
                    case Operation::NOT_EQUAL:
                        $or[] = $column . " != '" . $value . "'";
                        break;
                    default:
                        break;
                }
            }

            return '(' . \implode(' OR ', $or) . ')';
        }

        return null;
    }

    /**
     * @return int
     */
    public function getTestOp(): int
    {
        return (int)$this->testOp;
    }

    /**
     * @return int
     */
    public function getDataType(): int
    {
        return $this->dataType;
    }

    /**
     * @return bool
     */
    public function isCustomTestOp(): bool
    {
        return $this->customTestOp;
    }
}
