<?php
/**
 * @copyright JTL-Software-GmbH
 */

namespace jtl\Wizard;

use stdClass;

/**
 * Class Question
 * @package jtl\Wizard
 */
class Question implements \JsonSerializable
{
    public $id = 0;

    /**
     * @var string
     */
    private $text;

    /**
     * @var int
     */
    private $type;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var int|null
     */
    private $dependsOn;

    const TYPE_BOOL = 0;
    const TYPE_TEXT = 1;
    const TYPE_EMAIL = 2;

    /**
     * Question constructor.
     * @param string   $text
     * @param int      $type
     * @param int      $id
     * @param int|null $depensOn
     */
    public function __construct($text, $type, $id, $depensOn = null)
    {
        $this->text      = $text;
        $this->type      = $type;
        $this->id        = $id;
        $this->dependsOn = $depensOn;
    }

    /**
     * @return string $text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return int $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return stdClass
     */
    public function jsonSerialize()
    {
        $data = new stdClass();
        foreach (\get_object_vars($this) as $k => $v)
        {
            $data->$k = $v;
        }

        return $data;
    }

    /**
     * @return int|null
     */
    public function getDependency()
    {
        return $this->dependsOn;
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setID($id)
    {
        $this->id = $id;

        return $this;
    }
}
