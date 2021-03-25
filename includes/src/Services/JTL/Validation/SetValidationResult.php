<?php

namespace JTL\Services\JTL\Validation;

use function Functional\none;

/**
 * Class ObjectValidationResult
 * @package JTL\Services\JTL\Validation
 */
class SetValidationResult implements SetValidationResultInterface
{
    /**
     * @var array
     */
    protected $fieldResults = [];

    /**
     * @var array|object
     */
    protected $set;

    /**
     * ObjectValidationResult constructor.
     * @param object|array $set
     */
    public function __construct($set)
    {
        $this->set = $set;
    }

    /**
     * @inheritdoc
     */
    public function setFieldResult($fieldName, ValidationResultInterface $valueValidationResult): void
    {
        $this->fieldResults[$fieldName] = $valueValidationResult;
    }

    /**
     * @inheritdoc
     */
    public function getFieldResult($fieldName): ValidationResultInterface
    {
        return $this->fieldResults[$fieldName];
    }

    /**
     * @inheritdoc
     */
    public function getSetAsArray(): ?array
    {
        return $this->isValid() ? $this->set : null;
    }

    /**
     * @inheritdoc
     */
    public function getSetAsArrayInsecure(): array
    {
        return $this->set;
    }

    /**
     * @inheritdoc
     */
    public function getSetAsObject()
    {
        return $this->isValid() ? (object)$this->set : null;
    }

    /**
     * @inheritdoc
     */
    public function getSetAsObjectInsecure()
    {
        return (object)$this->set;
    }

    /**
     * @inheritdoc
     */
    public function isValid(): bool
    {
        return none($this->fieldResults, static function (ValidationResultInterface $fieldResult) {
            return !$fieldResult->isValid();
        });
    }
}
