<?php

namespace JTL\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\RuleInterface;
use JTL\Services\JTL\Validation\RuleResult;

/**
 * Class DateTime
 * @package JTL\Services\JTL\Validation\Rules
 *
 * Validates that the $value is an valid datetime according to the specified format.
 *
 * Transforms $value to an instance of \DateTime
 */
class DateTime implements RuleInterface
{
    protected $format;

    /**
     * Date constructor.
     * @param string $format
     */
    public function __construct(string $format)
    {
        $this->format = $format;
    }

    /**
     * @inheritdoc
     */
    public function validate($value): RuleResult
    {
        if ($value instanceof \DateTime) {
            return new RuleResult(true, '', $value);
        }
        if (!\is_string($value)) {
            return new RuleResult(false, 'invalid date', $value);
        }
        $dateTime = \DateTime::createFromFormat($this->format, $value);

        return $dateTime ? new RuleResult(true, '', $dateTime) : new RuleResult(false, 'invalid date', $value);
    }
}
