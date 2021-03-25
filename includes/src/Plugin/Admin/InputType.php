<?php declare(strict_types=1);

namespace JTL\Plugin\Admin;

use MyCLabs\Enum\Enum;

/**
 * Class InputType
 * @package JTL\Plugin\Admin
 */
class InputType extends Enum
{
    public const SELECT = 'selectbox';

    public const COLOR = 'color';

    public const RANGE = 'range';

    public const MONTH = 'month';

    public const WEEK = 'week';

    public const TEL = 'tel';

    public const TIME = 'time';

    public const URL = 'url';

    public const COLORPICKER = 'colorpicker';

    public const PASSWORD = 'password';

    public const EMAIL = 'email';

    public const DATE = 'date';

    public const TEXT = 'text';

    public const TEXTAREA = 'textarea';

    public const NUMBER = 'number';

    public const CHECKBOX = 'checkbox';

    public const RADIO = 'radio';

    public const NONE = 'none';
}
