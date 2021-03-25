<?php

namespace JTL\Services\JTL\Validation;

/**
 * Interface ValidationResultInterface
 * @package JTL\Services\JTL\Validation
 */
interface RuleResultInterface
{
    /**
     * @return bool
     */
    public function isValid(): bool;

    /**
     * @return string
     */
    public function getMessageId(): string;

    /**
     * @return mixed
     */
    public function getTransformedValue();
}
