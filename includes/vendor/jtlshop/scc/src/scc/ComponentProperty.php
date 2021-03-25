<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc;

/**
 * Class ComponentProperty
 * @package scc
 */
class ComponentProperty implements ComponentPropertyInterface
{
    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @var bool
     */
    private $isRequired = false;

    /**
     * @var string
     */
    private $type = ComponentPropertyType::TYPE_STRING;

    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    /**
     * ComponentProperty constructor.
     * @param string|null $name
     * @param null        $defaultValue
     * @param string|null $type
     */
    public function __construct(string $name = null, $defaultValue = null, string $type = null)
    {
        if ($name !== null) {
            $this->setName($name);
        }
        if ($defaultValue !== null) {
            $this->setDefaultValue($defaultValue);
        }
        if ($type !== null) {
            $this->setType($type);
        }
    }

    /**
     * @inheritdoc
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @inheritdoc
     */
    public function setDefaultValue($value): void
    {
        $this->defaultValue = $value;
    }

    /**
     * @return bool
     */
    public function getIsRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * @inheritdoc
     */
    public function setIsRequired(bool $required): void
    {
        $this->isRequired = $required;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name): void
    {
        $this->name = $name;
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
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return bool
     */
    public function hasValue(): bool
    {
        return $this->value !== null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)($this->value ?? '');
    }
}
