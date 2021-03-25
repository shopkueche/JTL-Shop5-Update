<?php

namespace JTL\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\RuleInterface;
use JTL\Services\JTL\Validation\RuleResult;

/**
 * Class LessThan
 * @package JTL\Services\JTL\Validation\Rules
 */
class LessThan implements RuleInterface
{
    protected $value;

    /**
     * LessThan constructor.
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function validate($value): RuleResult
    {
        return $value < $this->value
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'value too high', $value);
    }
}
