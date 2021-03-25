<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpModuleTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpImagickExtension
 * @package Systemcheck\Tests\Shop5
 */
class PhpImagickExtension extends PhpModuleTest
{
    protected $name          = 'ImageMagick';
    protected $requiredState = 'enabled';
    protected $description   = 'JTL-Shop benötigt die PHP-Erweiterung <code>php-imagick</code> 
für die dynamische Generierung von Bildern.<br>Diese Erweiterung ist auf Debian-Systemen als 
<code>php5-imagick,</code> sowie auf Fedora/RedHat-Systemen als <code>php-pecl-imagick</code> verfügbar.';
    protected $isOptional    = true;
    protected $isRecommended = true;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        return \extension_loaded('imagick');
    }
}
