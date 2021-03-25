<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpModuleTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpSplSupport
 * @package Systemcheck\Tests\Shop5
 */
class PhpSplSupport extends PhpModuleTest
{
    protected $name          = 'Standard PHP Library';
    protected $requiredState = 'enabled';
    protected $description   = 'Für JTL-Shop5 wird Unterstützung für die Standard PHP Library (SPL) benötigt.';
    protected $isOptional    = false;
    protected $isRecommended = false;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        return \extension_loaded('SPL') && \function_exists('spl_autoload_register');
    }
}
