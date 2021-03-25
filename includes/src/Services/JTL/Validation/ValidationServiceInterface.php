<?php

namespace JTL\Services\JTL\Validation;

/**
 * Interface ValidationServiceInterface
 * @package JTL\Services\JTL\Validation
 */
interface ValidationServiceInterface
{
    /**
     * @param string $name
     * @return RuleSet
     */
    public function getRuleSet(string $name): RuleSet;

    /**
     * @param string  $name
     * @param RuleSet $ruleSet
     * @return void
     */
    public function setRuleSet(string $name, RuleSet $ruleSet): void;

    /**
     * @param mixed          $value
     * @param RuleSet|string $ruleSet
     * @return ValidationResultInterface
     */
    public function validate($value, $ruleSet): ValidationResultInterface;

    /**
     * @param string $name
     * @return bool
     */
    public function hasGet(string $name): bool;

    /**
     * @param string $name
     * @return bool
     */
    public function hasPost(string $name): bool;

    /**
     * @param string $name
     * @return bool
     */
    public function hasCookie(string $name): bool;

    /**
     * @param string $name
     * @return bool
     */
    public function hasGPC(string $name): bool;

    /**
     * @param string $name
     * @return bool
     */
    public function hasGP(string $name): bool;

    /**
     * @param string         $name
     * @param string|RuleSet $ruleSet
     * @return ValidationResultInterface
     */
    public function validateGet(string $name, $ruleSet): ValidationResultInterface;

    /**
     * @param string         $name
     * @param string|RuleSet $ruleSet
     * @return ValidationResultInterface
     */
    public function validatePost(string $name, $ruleSet): ValidationResultInterface;

    /**
     * @param string         $name
     * @param string|RuleSet $ruleSet
     * @return ValidationResultInterface
     */
    public function validateCookie(string $name, $ruleSet): ValidationResultInterface;

    /**
     * @param string         $name
     * @param string|RuleSet $ruleSet
     * @return ValidationResultInterface
     */
    public function validateGPC(string $name, $ruleSet): ValidationResultInterface;

    /**
     * @param string         $name
     * @param string|RuleSet $ruleSet
     * @return ValidationResultInterface
     */
    public function validateGP(string $name, $ruleSet): ValidationResultInterface;

    /**
     * @param array $set
     * @param array        $rulesConfig
     * @return SetValidationResultInterface
     */
    public function validateSet($set, $rulesConfig): SetValidationResultInterface;

    /**
     * @param array $rulesConfig
     * @return SetValidationResultInterface
     */
    public function validateFullGet(array $rulesConfig): SetValidationResultInterface;

    /**
     * @param array $rulesConfig
     * @return SetValidationResultInterface
     */
    public function validateFullPost(array $rulesConfig): SetValidationResultInterface;

    /**
     * @param array $rulesConfig
     * @return SetValidationResultInterface
     */
    public function validateFullCookie(array $rulesConfig): SetValidationResultInterface;
}
