<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpModuleTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpMbstringExtension
 * @package Systemcheck\Tests\Shop5
 */
class PhpMbstringExtension extends PhpModuleTest
{
    protected $name          = 'mbstring';
    protected $requiredState = 'enabled';
    protected $description   = 'Die <code>mbstring</code>-Erweiterung ist zum Betrieb des JTL-Shop zwingend erforderlich.';
    protected $isOptional    = false;
    protected $isRecommended = true;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        return \extension_loaded('mbstring');
    }
}
