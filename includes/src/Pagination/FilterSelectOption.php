<?php

namespace JTL\Pagination;

/**
 * Class FilterSelectOption
 * @package JTL\Pagination
 */
class FilterSelectOption
{
    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string|int
     */
    protected $value = '';

    /**
     * @var int
     */
    protected $testOp = Operation::CUSTOM;

    /**
     * FilterSelectOption constructor.
     *
     * @param string $title
     * @param string|int $value
     * @param int    $testOp
     */
    public function __construct($title, $value, int $testOp)
    {
        $this->title  = $title;
        $this->value  = $value;
        $this->testOp = $testOp;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string|int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getTestOp(): int
    {
        return (int)$this->testOp;
    }
}
