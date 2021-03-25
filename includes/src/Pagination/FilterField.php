<?php

namespace JTL\Pagination;

/**
 * Class FilterField
 * @package JTL\Pagination
 */
abstract class FilterField
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var array|mixed|string
     */
    protected $title = '';

    /**
     * @var mixed|string
     */
    protected $titleLong = '';

    /**
     * @var string
     */
    protected $column = '';

    /**
     * @var mixed|string
     */
    protected $value = '';

    /**
     * @var null|string|string[]
     */
    protected $id = '';

    /**
     * FilterField constructor.
     *
     * @param Filter       $filter
     * @param string       $type
     * @param string|array $title - either title-string for this field or a pair of short title and long title
     * @param string|array $column
     * @param string       $defaultValue
     */
    public function __construct($filter, string $type, $title, $column, $defaultValue = '')
    {
        $this->filter    = $filter;
        $this->type      = $type;
        $this->title     = \is_array($title) ? $title[0] : $title;
        $this->titleLong = \is_array($title) ? $title[1] : '';
        $this->column    = $column;
        $this->id        = \preg_replace('/[^a-zA-Z0-9_]+/', '', $this->title);
        $this->value     =
            $filter->getAction() === $filter->getID() . '_filter' ? $_GET[$filter->getID() . '_' . $this->id] : (
            $filter->getAction() === $filter->getID() . '_resetfilter' ? $defaultValue : (
            $filter->hasSessionField($this->id) ? $filter->getSessionField($this->id) :
                $defaultValue
            ));
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getTitleLong()
    {
        return $this->titleLong;
    }

    /**
     * @return mixed
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    abstract public function getWhereClause(): ?string;
}
