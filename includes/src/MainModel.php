<?php

namespace JTL;

use stdClass;

/**
 * Class MainModel
 * @package JTL
 */
abstract class MainModel
{
    /**
     * @param null|int    $id
     * @param null|object $data
     * @param null|mixed  $option
     */
    public function __construct($id = null, $data = null, $option = null)
    {
        if (\is_object($data)) {
            $this->loadObject($data);
        } elseif ($id !== null) {
            $this->load($id, $data, $option);
        }
    }

    /**
     * @param int         $id
     * @param null|object $data
     * @param null|array  $option
     */
    abstract public function load($id, $data = null, $option = null);

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return \array_keys(\get_object_vars($this));
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
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
     * @return mixed|string
     */
    public function toJSON()
    {
        $item = new stdClass();
        foreach (\array_keys(\get_object_vars($this)) as $member) {
            $method = 'get' . \mb_substr($member, 1);
            if (\method_exists($this, $method)) {
                $item->$member = $this->$method();
            }
        }

        return \json_encode($item);
    }

    /**
     * @return string
     */
    public function toCSV()
    {
        $csv = '';
        foreach (\array_keys(\get_object_vars($this)) as $i => $member) {
            $method = 'get' . \mb_substr($member, 1);
            if (\method_exists($this, $method)) {
                $sep = '';
                if ($i > 0) {
                    $sep = ';';
                }

                $csv .= $sep . $this->$method();
            }
        }

        return $csv;
    }

    /**
     * @param array $nonpublics
     * @return stdClass
     */
    public function getPublic(array $nonpublics): stdClass
    {
        $item = new stdClass();
        foreach (\array_keys(\get_object_vars($this)) as $member) {
            if (!\in_array($member, $nonpublics, true)) {
                $item->$member = $this->$member;
            }
        }

        return $item;
    }

    /**
     * @param object $obj
     */
    public function loadObject($obj): void
    {
        foreach (\array_keys(\get_object_vars($obj)) as $member) {
            $method = 'set' . \mb_substr($member, 1);
            if (\method_exists($this, $method)) {
                $this->$method($obj->$member);
            }
        }
    }
}
