<?php

namespace JTL\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\RuleInterface;
use JTL\Services\JTL\Validation\RuleResult;

/**
 * Class Equals
 * @package JTL\Services\JTL\Validation\Rules
 */
class EqualsLax implements RuleInterface
{
    /**
     * @var mixed
     */
    protected $expected;

    /**
     * Equals constructor.
     * @param mixed $expected
     */
    public function __construct($expected)
    {
        $this->expected = $expected;
    }

    /**
     * @param mixed $value
     * @return RuleResult
     */
    public function validate($value): RuleResult
    {
        return $this->expected == $value
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'values not equal', $value);
    }
}
