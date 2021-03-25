<?php declare(strict_types=1);

namespace JTL\Plugin;

use MyCLabs\Enum\Enum;

/**
 * Class State
 * @package JTL\Plugin
 */
class State extends Enum
{
    public const NONE = 0;

    public const DISABLED = 1;

    public const ACTIVATED = 2;

    public const ERRONEOUS = 3;

    public const UPDATE_FAILED = 4;

    public const LICENSE_KEY_MISSING = 5;

    public const LICENSE_KEY_INVALID = 6;

    public const ESX_LICENSE_EXPIRED = 7;

    public const ESX_SUBSCRIPTION_EXPIRED = 8;
}
