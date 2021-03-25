<?php

namespace JTL\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\RuleInterface;
use JTL\Services\JTL\Validation\RuleResult;

/**
 * Class Email
 * @package JTL\Services\JTL\Validation\Rules
 *
 * Validates, that $value is string containing a valid email
 *
 * No transform
 */
class Email implements RuleInterface
{
    /**
     * @inheritdoc
     */
    public function validate($value): RuleResult
    {
        return \filter_var($value, \FILTER_VALIDATE_EMAIL)
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'invalid email', $value);
    }
}
