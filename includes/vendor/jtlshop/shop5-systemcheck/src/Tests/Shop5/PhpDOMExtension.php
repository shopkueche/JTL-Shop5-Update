<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpModuleTest;

/**
 * Class PhpDOMExtension
 * @package Systemcheck\Tests\Shop5
 */
class PhpDOMExtension extends PhpModuleTest
{
    protected $name          = 'DOM';
    protected $requiredState = 'enabled';
    protected $description   = 'JTL-Shop benötigt die PHP-Erweiterung <code>php-dom</code>.';
    protected $isOptional    = false;
    protected $isRecommended = true;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        return \extension_loaded('dom');
    }
}
