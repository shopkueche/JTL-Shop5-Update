<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpModuleTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpSoapExtension
 * @package Systemcheck\Tests\Shop5
 */
class PhpSoapExtension extends PhpModuleTest
{
    protected $name          = 'SOAP';
    protected $requiredState = 'enabled';
    protected $description   = 'Die Prüfung der Umsatzsteuer-ID erfolgt per "MwSt-Informationsaustauschsystem (MIAS) ' .
        'der Europäischen Kommission".<br> Dieses System wird mit dem Übertragungsprotokoll "SOAP" abgefragt, was ' .
        'eine entsprechende PHP-Unterstützung voraussetzt.';
    protected $isOptional    = true;
    protected $isRecommended = true;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        return \class_exists('SoapClient');
    }
}
