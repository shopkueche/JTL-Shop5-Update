<?php

namespace JTL\Pagination;

/**
 * Class FilterDateRangeField
 * @package JTL\Pagination
 */
class FilterDateRangeField extends FilterField
{
    /**
     * @var string
     */
    private $dStart = '';

    /**
     * @var string
     */
    private $dEnd = '';

    /**
     * FilterDateRangeField constructor.
     * @param Filter       $filter
     * @param string|array $title
     * @param string       $column
     * @param string       $defaultValue
     */
    public function __construct($filter, $title, string $column, $defaultValue = '')
    {
        parent::__construct($filter, 'daterange', $title, $column, $defaultValue);

        $dRange = \explode(' - ', $this->value);

        if (\count($dRange) === 2) {
            $this->dStart = \date_create($dRange[0])->format('Y-m-d') . ' 00:00:00';
            $this->dEnd   = \date_create($dRange[1])->format('Y-m-d') . ' 23:59:59';
        }
    }

    /**
     * @return string|null
     */
    public function getWhereClause(): ?string
    {
        $dRange = \explode(' - ', $this->value);

        if (\count($dRange) === 2) {
            $dStart = \date_create($dRange[0])->format('Y-m-d') . ' 00:00:00';
            $dEnd   = \date_create($dRange[1])->format('Y-m-d') . ' 23:59:59';

            return $this->column . " >= '" . $dStart . "' AND " . $this->column . " <= '" . $dEnd . "'";
        }

        return null;
    }

    /**
     * @return string
     */
    public function getStart(): string
    {
        return $this->dStart;
    }

    /**
     * @return string
     */
    public function getEnd(): string
    {
        return $this->dEnd;
    }
}
