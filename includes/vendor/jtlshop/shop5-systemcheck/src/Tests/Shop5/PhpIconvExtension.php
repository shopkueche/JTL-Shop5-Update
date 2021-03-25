<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpModuleTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpIconvExtension
 * @package Systemcheck\Tests\Shop5
 */
class PhpIconvExtension extends PhpModuleTest
{
    protected $name          = 'Iconv';
    protected $requiredState = 'enabled';
    protected $description   = 'JTL-Shop benötigt die PHP-Erweiterung <code>php-iconv</code> für die Internationalisierung.';
    protected $isOptional    = false;
    protected $isRecommended = true;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        return \extension_loaded('iconv');
    }
}
