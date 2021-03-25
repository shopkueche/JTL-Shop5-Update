<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpModuleTest;

/**
 * Class PhpCurlExtension
 * @package Systemcheck\Tests\Shop5
 */
class PhpCurlExtension extends PhpModuleTest
{
    protected $name          = 'cURL';
    protected $requiredState = 'enabled';
    protected $description   = '';
    protected $isOptional    = true;
    protected $isRecommended = true;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        return \extension_loaded('curl');
    }
}

