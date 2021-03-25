<?php

namespace JTL\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\RuleInterface;
use JTL\Services\JTL\Validation\RuleResult;

/**
 * Class Numeric
 * @package JTL\Services\JTL\Validation\Rules
 *
 * Validates, that $value is numeric
 *
 * No transform
 */
class Numeric implements RuleInterface
{
    /**
     * @inheritdoc
     */
    public function validate($value): RuleResult
    {
        return \is_numeric($value)
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'not numeric', $value);
    }
}
