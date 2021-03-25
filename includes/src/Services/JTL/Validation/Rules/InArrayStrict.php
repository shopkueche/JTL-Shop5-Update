<?php

namespace JTL\Services\JTL\Validation\Rules;

use JTL\Services\JTL\Validation\RuleInterface;
use JTL\Services\JTL\Validation\RuleResult;

/**
 * Class WhitelistStrict
 * @package JTL\Services\JTL\Validation\Rules
 *
 * Validates, that $value is in a specified list of items
 */
class InArrayStrict implements RuleInterface
{
    /**
     * @var array|mixed[]
     */
    protected $whitelist;

    /**
     * WhitelistStrict constructor.
     * @param mixed[] $whitelist
     */
    public function __construct(array $whitelist)
    {
        $this->whitelist = $whitelist;
    }

    /**
     * @inheritdoc
     */
    public function validate($value): RuleResult
    {
        return \in_array($value, $this->whitelist, true)
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'value not in whitelist', $value);
    }
}
