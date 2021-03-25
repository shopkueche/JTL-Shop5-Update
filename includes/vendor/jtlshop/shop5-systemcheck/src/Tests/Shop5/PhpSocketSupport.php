<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpModuleTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpSocketSupport
 * @package Systemcheck\Tests\Shop5
 */
class PhpSocketSupport extends PhpModuleTest
{
    protected $name          = 'Sockets';
    protected $requiredState = 'enabled';
    protected $description   = '';
    protected $isOptional    = true;
    protected $isRecommended = true;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        return \function_exists('fsockopen');
    }
}
