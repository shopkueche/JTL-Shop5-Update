<?php

namespace JTL\Services\JTL\Validation;

/**
 * Interface RuleInterface
 * @package JTL\Services\JTL\Validation
 */
interface RuleInterface
{
    /**
     * Validate a value against the specified Rule
     *
     * @param mixed $value
     * @return RuleResult
     */
    public function validate($value): RuleResult;
}
