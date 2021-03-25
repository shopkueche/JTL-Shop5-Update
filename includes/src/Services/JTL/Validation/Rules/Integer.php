<?php

namespace JTL\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\RuleInterface;
use JTL\Services\JTL\Validation\RuleResult;

/**
 * Class Integer
 * @package JTL\Services\JTL\Validation\Rules
 *
 * Validates, that $value is an integer
 *
 * Transforms value to int
 */
class Integer implements RuleInterface
{
    /**
     * @inheritdoc
     */
    public function validate($value): RuleResult
    {
        $result = \filter_var($value, \FILTER_VALIDATE_INT);

        return $result !== false
            ? new RuleResult(true, '', $result)
            : new RuleResult(false, 'invalid integer', $value);
    }
}
