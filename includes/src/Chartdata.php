<?php

namespace JTL;

use Exception;

/**
 * Class Chartdata
 * @package JTL
 */
class Chartdata
{
    /**
     * @var bool
     */
    protected $_bActive;

    /**
     * @var object
     */
    protected $_xAxis;

    /**
     * @var array
     */
    protected $_series;

    /**
     * @var string
     */
    protected $_xAxisJSON;

    /**
     * @var string
     */
    protected $_seriesJSON;

    /**
     * @var string
     */
    protected $_url;

    /**
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        if (\is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return $this
     * @throws Exception
     */
    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if ($name === 'mapper' || !\method_exists($this, $method)) {
            throw new Exception('Invalid Query property');
        }
        $this->$method($value);

        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name)
    {
        $method = 'get' . $name;
        if ($name === 'mapper' || !\method_exists($this, $method)) {
            throw new Exception('Invalid Query property');
        }

        return $this->$method();
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $methods = \get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . \ucfirst($key);
            if (\in_array($method, $methods, true)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array   = [];
        $members = \array_keys(\get_object_vars($this));
        foreach ($members as $member) {
            $array[\mb_substr($member, 1)] = $this->$member;
        }

        return $array;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function setActive($active): self
    {
        $this->_bActive = (bool)$active;

        return $this;
    }

    /**
     * @param object $axis
     * @return $this
     */
    public function setAxis($axis): self
    {
        $this->_xAxis = $axis;

        return $this;
    }

    /**
     * @param array $series
     * @return $this
     */
    public function setSeries($series): self
    {
        $this->_series = $series;

        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url): self
    {
        $this->_url = $url;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->_url;
    }

    /**
     * @return bool|null
     */
    public function getActive(): ?bool
    {
        return $this->_bActive;
    }

    /**
     * @return object|null
     */
    public function getAxis()
    {
        return $this->_xAxis;
    }

    /**
     * @return array|null
     */
    public function getSeries(): ?array
    {
        return $this->_series;
    }

    /**
     * @return string|null
     */
    public function getAxisJSON(): ?string
    {
        return $this->_xAxisJSON;
    }

    /**
     * @return string|null
     */
    public function getSeriesJSON(): ?string
    {
        return $this->_seriesJSON;
    }

    /**
     * @return $this
     */
    public function memberToJSON(): self
    {
        $this->_seriesJSON = \json_encode($this->_series);
        $this->_xAxisJSON  = \json_encode($this->_xAxis);

        return $this;
    }
}
