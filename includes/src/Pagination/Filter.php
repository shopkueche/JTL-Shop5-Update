<?php

namespace JTL\Pagination;

/**
 * Class Filter
 * @package JTL\Pagination
 */
class Filter
{
    /**
     * @var string
     */
    protected $id = 'Filter';

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var string
     */
    protected $whereSQL = '';

    /**
     * @var string
     */
    protected $action = '';

    /**
     * @var array
     */
    protected $sessionData = [];

    /**
     * Filter constructor.
     * Create a new empty filter object
     * @param string|null $id
     */
    public function __construct($id = null)
    {
        if (\is_string($id)) {
            $this->id = $id;
        }

        $this->action = $_GET['action'] ?? '';
        $this->loadSessionStore();
    }

    /**
     * Add a text field to a filter object
     *
     * @param string|array $title - either title-string for this field or a pair of short title and long title
     * @param string|array $column - the column name to be compared
     * @param int          $testOp
     * @param int          $dataType
     * @return FilterTextField
     */
    public function addTextfield(
        $title,
        $column,
        int $testOp = Operation::CUSTOM,
        int $dataType = DataType::TEXT
    ): FilterTextField {
        $field                                      = new FilterTextField(
            $this,
            $title,
            $column,
            $testOp,
            $dataType
        );
        $this->fields[]                             = $field;
        $this->sessionData[$field->getID()]         = $field->getValue();
        $this->sessionData[$field->getID() . '_op'] = $field->getTestOp();

        return $field;
    }

    /**
     * Add a select field to a filter object. Options can be added with FilterSelectField->addSelectOption() to this
     * select field
     *
     * @param string|array $title - either title-string for this field or a pair of short title and long title
     * @param string       $column - the column name to be compared
     * @param int          $defaultOption
     * @return FilterSelectField
     */
    public function addSelectfield($title, $column, $defaultOption = 0): FilterSelectField
    {
        $field                              = new FilterSelectField($this, $title, $column, $defaultOption);
        $this->fields[]                     = $field;
        $this->sessionData[$field->getID()] = $field->getValue();

        return $field;
    }

    /**
     * Add a DateRange field to the filter object.
     *
     * @param string $cTitle
     * @param string $cColumn
     * @param string $cDefValue
     * @return FilterDateRangeField
     */
    public function addDaterangefield($cTitle, $cColumn, $cDefValue = ''): FilterDateRangeField
    {
        $field                              = new FilterDateRangeField($this, $cTitle, $cColumn, $cDefValue);
        $this->fields[]                     = $field;
        $this->sessionData[$field->getID()] = $field->getValue();

        return $field;
    }

    /**
     * Assemble filter object to be ready for use. Build WHERE clause.
     */
    public function assemble(): void
    {
        $this->whereSQL = \implode(
            ' AND ',
            \array_filter(
                \array_map(static function (FilterField $oField) {
                    return $oField->getWhereClause();
                }, $this->fields)
            )
        );
        $this->saveSessionStore();
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param int $i
     * @return mixed
     */
    public function getField($i)
    {
        return $this->fields[$i];
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getWhereSQL(): string
    {
        return $this->whereSQL;
    }

    /**
     *
     */
    public function loadSessionStore(): void
    {
        $this->sessionData = $_SESSION['filter_' . $this->id] ?? [];
    }

    /**
     *
     */
    public function saveSessionStore(): void
    {
        $_SESSION['filter_' . $this->id] = $this->sessionData;
    }

    /**
     * @param string $cField
     * @return bool
     */
    public function hasSessionField($cField): bool
    {
        return isset($this->sessionData[$cField]);
    }

    /**
     * @param string $cField
     * @return mixed
     */
    public function getSessionField($cField)
    {
        return $this->sessionData[$cField];
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->id;
    }
}
