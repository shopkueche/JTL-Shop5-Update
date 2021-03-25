<?php declare(strict_types=1);

namespace Systemcheck\Tests;

use JsonSerializable;

/**
 * Class AbstractTest
 * @package Systemcheck\Tests
 */
abstract class AbstractTest implements JsonSerializable
{
    public const RESULT_OK = 0;

    public const RESULT_FAILED = 1;

    public const RESULT_UNKNOWN = 2;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $currentState;

    /**
     * @var int
     */
    protected $result = self::RESULT_FAILED;

    /**
     * @var string
     */
    protected $requiredState;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var bool
     */
    protected $isRecommended = false;

    /**
     * @var bool
     */
    protected $isOptional = false;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getRequiredState(): ?string
    {
        return $this->requiredState;
    }

    /**
     * @return string|null
     */
    public function getCurrentState(): ?string
    {
        return $this->currentState;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function getIsOptional(): bool
    {
        return $this->isOptional;
    }

    /**
     * getIsRecommended
     * @return bool
     */
    public function getIsRecommended(): bool
    {
        return $this->isRecommended;
    }

    /**
     * @return bool|string
     */
    public function getIsReplaceableBy()
    {
        return \property_exists($this, 'isReplaceableBy')
            ? $this->isReplaceableBy
            : false;
    }

    /**
     * @return bool
     */
    public function getRunStandAlone()
    {
        return \property_exists($this, 'runStandAlone')
            ? $this->runStandAlone
            : null; // do not change to 'false'! we need three states here!
    }

    /**
     * @return int
     */
    public function getResult(): int
    {
        return $this->result;
    }

    /**
     * @param bool $result
     */
    public function setResult(bool $result): void
    {
        $this->result = $result === true ? self::RESULT_OK : self::RESULT_FAILED;
    }

    /**
     * @return bool
     */
    abstract public function execute(): bool;

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return \get_object_vars($this);
    }
}
