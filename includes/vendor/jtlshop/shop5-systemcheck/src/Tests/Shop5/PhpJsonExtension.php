<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpModuleTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpJsonExtension
 * @package Systemcheck\Tests\Shop5
 */
class PhpJsonExtension extends PhpModuleTest
{
    protected $name          = 'JSON';
    protected $requiredState = 'enabled';
    protected $description   = 'JTL-Shop benötigt PHP-Unterstützung für das JSON-Format.';
    protected $isOptional    = false;
    protected $isRecommended = false;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        return \extension_loaded('json');
    }
}
