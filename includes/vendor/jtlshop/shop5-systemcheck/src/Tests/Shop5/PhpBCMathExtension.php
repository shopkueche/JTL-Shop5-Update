<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpModuleTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpBCMathExtension
 * @package Systemcheck\Tests\Shop5
 */
class PhpBCMathExtension extends PhpModuleTest
{
    protected $name          = 'BCMath';
    protected $requiredState = 'enabled';
    protected $description   = 'JTL-Shop benötigt die PHP-Erweiterung <code>php-bcmath</code> für diverse Berechnungen.';
    protected $isOptional    = false;
    protected $isRecommended = true;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        return \extension_loaded('bcmath');
    }
}
