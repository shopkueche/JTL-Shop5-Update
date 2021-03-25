<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpModuleTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpIntlExtension
 * @package Systemcheck\Tests\Shop5
 */
class PhpIntlExtension extends PhpModuleTest
{
    protected $name          = 'Intl';
    protected $requiredState = 'enabled';
    protected $description   = 'JTL-Shop benötigt die PHP-Erweiterung <code>php-intl</code> für die Internationalisierung.';
    protected $isOptional    = false;
    protected $isRecommended = true;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        return \extension_loaded('intl') && \defined('INTL_IDNA_VARIANT_UTS46');
    }
}
