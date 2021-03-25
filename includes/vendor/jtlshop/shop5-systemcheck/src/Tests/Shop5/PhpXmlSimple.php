<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpModuleTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpXmlSimple
 * @package Systemcheck\Tests\Shop5
 */
class PhpXmlSimple extends PhpModuleTest
{
    protected $name          = 'SimpleXML';
    protected $requiredState = 'enabled';
    protected $description   = 'Für JTL-Shop wird die PHP-Erweiterung Simple-XML benötigt.';
    protected $isOptional    = false;
    protected $isRecommended = true;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        if (\extension_loaded('libxml') && \extension_loaded('simplexml')) {
            // simplexml is loaded, but we need to check if it's actually working
            return \is_a(\simplexml_load_string('<?xml version="1.0"?><document></document>'), 'SimpleXMLElement');
        }

        return false;
    }
}
