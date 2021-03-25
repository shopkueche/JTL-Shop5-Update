<?php declare(strict_types=1);

namespace JTL\Model;

use JTL\Plugin\Admin\InputType;

/**
 * Class InputConfig
 * @package JTL\Model
 */
class InputConfig
{
    /**
     * @var array
     */
    public $allowedValues = [];

    /**
     * @var string
     */
    public $inputType = InputType::TEXT;

    /**
     * @var bool
     */
    public $modifyable = true;

    /**
     * @var bool
     */
    public $hidden = false;

    /**
     * @var bool
     */
    public $multiselect = false;

    /**
     * @return array
     */
    public function getAllowedValues(): array
    {
        return $this->allowedValues;
    }

    /**
     * @param array $allowedValues
     */
    public function setAllowedValues(array $allowedValues): void
    {
        $this->allowedValues = $allowedValues;
    }

    /**
     * @return string
     */
    public function getInputType(): string
    {
        return $this->inputType;
    }

    /**
     * @param string $inputType
     */
    public function setInputType(string $inputType): void
    {
        $this->inputType = $inputType;
    }

    /**
     * @return bool
     */
    public function isModifyable(): bool
    {
        return $this->modifyable;
    }

    /**
     * @param bool $modifyable
     */
    public function setModifyable(bool $modifyable): void
    {
        $this->modifyable = $modifyable;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    /**
     * @return bool
     */
    public function isMultiselect(): bool
    {
        return $this->multiselect;
    }

    /**
     * @param bool $multiselect
     */
    public function setMultiselect(bool $multiselect): void
    {
        $this->multiselect = $multiselect;
    }
}
