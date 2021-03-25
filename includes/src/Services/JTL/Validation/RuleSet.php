<?php

namespace JTL\Services\JTL\Validation;

use Eloquent\Pathogen\Exception\InvalidPathStateException;
use Eloquent\Pathogen\Path;
use JTL\Services\JTL\Validation\Rules;

/**
 * Class RuleSet
 * @package JTL\Services\JTL\Validation
 *
 * RuleSet is a collection of rules. The rules are applied in the order, they are added to the RuleSet.
 * A Rule can:
 *  - validate a value
 *  - transform a value
 */
class RuleSet
{
    /**
     * @var array|RuleInterface[]
     */
    protected $rules = [];

    /**
     * RuleSet constructor.
     * @param RuleInterface[] $rules
     */
    public function __construct($rules = [])
    {
        $this->rules = $rules;
    }

    /**
     * Creates a clone of the current RuleSet
     *
     * @return static
     */
    public function createClone(): RuleSet
    {
        return new static($this->rules);
    }

    /**
     * Add a rule to the current rule set. This function returns the same RuleSet instance, the method is called on.
     *
     * @param RuleInterface $rule
     * @return $this
     */
    public function addRule(RuleInterface $rule): RuleSet
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * Returns all rules, specified in this RuleSet
     *
     * @return RuleInterface[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Validates that the value is an valid email address (Warning! This function does not check, whether the domain or
     * the specific email address exists. It just validates the syntax.)
     *
     * No transform
     *
     * @return RuleSet
     */
    public function email(): RuleSet
    {
        return $this->addRule(new Rules\Email());
    }

    /**
     * Validates that the value is numeric (see PHP function is_numeric)
     *
     * No transform
     *
     * @return RuleSet
     */
    public function numeric(): RuleSet
    {
        return $this->addRule(new Rules\Numeric());
    }

    /**
     * Validates that the value is an integer
     *
     * Transform to int
     *
     * @return RuleSet
     */
    public function integer(): RuleSet
    {
        return $this->addRule(new Rules\Integer());
    }

    /**
     * Validates that the value is valid phone number
     *
     * No transform
     *
     * @return RuleSet
     */
    public function phoneNumber(): RuleSet
    {
        return $this->addRule(new Rules\PhoneNumber());
    }

    /**
     * Validates that the value is a valid DateTime according to the specified format
     *
     * Transform to DateTime
     *
     * @param string $format
     * @return RuleSet
     */
    public function dateTime(string $format): RuleSet
    {
        return $this->addRule(new Rules\DateTime($format));
    }

    /**
     * Validates that the value strictly equals the expected value (without type coercion / ===)
     *
     * @param mixed $expected
     * @return RuleSet
     */
    public function eqStrict($expected): RuleSet
    {
        return $this->addRule(new Rules\EqualsStrict($expected));
    }

    /**
     * Validates that the value equals the expected value with a lax comparison (with type coercion / ==)
     *
     * @param mixed $expected
     * @return RuleSet
     */
    public function eqLax($expected): RuleSet
    {
        return $this->addRule(new Rules\EqualsLax($expected));
    }

    /**
     * Validates that the value is greater than the expected value
     *
     * @param mixed $expected
     * @return RuleSet
     */
    public function gt($expected): RuleSet
    {
        return $this->addRule(new Rules\GreaterThan($expected));
    }

    /**
     * Validates that the value is greater than or equal to the expected value
     *
     * @param mixed $expected
     * @return RuleSet
     */
    public function gte($expected): RuleSet
    {
        return $this->addRule(new Rules\GreaterThanEquals($expected));
    }

    /**
     * Validates that the value is less than the expected value
     *
     * @param mixed $expected
     * @return RuleSet
     */
    public function lt($expected): RuleSet
    {
        return $this->addRule(new Rules\LessThan($expected));
    }

    /**
     * Validates that the value is less than or equal to the expected value
     *
     * @param mixed $expected
     * @return RuleSet
     */
    public function lte($expected): RuleSet
    {
        return $this->addRule(new Rules\LessThanEquals($expected));
    }

    /**
     * Validates that the value is between $lower and $upper (including)
     *
     * @param mixed $lower
     * @param mixed $upper
     * @return RuleSet
     */
    public function between($lower, $upper): RuleSet
    {
        return $this->addRule(new Rules\Between($lower, $upper));
    }

    /**
     * Validates that the value is in the specified list of items (without type coercion)
     * @param array $whitelist
     * @return RuleSet
     */
    public function inArrayStrict(array $whitelist): RuleSet
    {
        return $this->addRule(new Rules\InArrayStrict($whitelist));
    }

    /**
     * Validates that the type of the value equals the expected type
     *
     * @param string $expectedType
     * @return RuleSet
     */
    public function hasType(string $expectedType): RuleSet
    {
        return $this->addRule(new Rules\Type($expectedType));
    }

    /**
     * @param string|Path $basePath
     * @return RuleSet
     * @throws InvalidPathStateException
     */
    public function inPath($basePath): RuleSet
    {
        return $this->addRule(new Rules\InPath($basePath));
    }
}
