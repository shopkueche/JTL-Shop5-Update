<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\ProgramTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class OperatingSystem
 * @package Systemcheck\Tests\Shop5
 */
class OperatingSystem extends ProgramTest
{
    protected $name          = 'Betriebssystem';
    protected $requiredState = 'Linux';
    protected $description   = 'JTL-Software empfiehlt den Betrieb mit Linux-Webservern. ' .
    'Der Betrieb unter Solaris, FreeBSD oder Windows wird weder empfohlen noch unterstützt.';
    protected $isOptional    = true;
    protected $isRecommended = true;

    private $unameMap = [
        'CYGWIN_NT-5.1' => 'Windows',
        'Darwin'        => 'Mac OS X',
        'IRIX64'        => 'IRIX',
        'SunOS'         => 'Solaris/OpenSolaris',
        'WIN32'         => 'Windows',
        'WINNT'         => 'Windows'
    ];

    private $archMap = [
        'i386'   => 'Intel x86',
        'i486'   => 'Intel x86',
        'i586'   => 'Intel x86',
        'i686'   => 'Intel x86',
        'x86_64' => 'Intel x86_64',
        'sparc'  => 'SPARC'
    ];

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        // Operating system
        $os = \php_uname('s');
        if (\array_key_exists($os, $this->unameMap)) {
            $os = $this->unameMap[$os];
        }

        // Processor architecture
        $arch = \php_uname('m');
        if (\array_key_exists($arch, $this->archMap)) {
            $arch = $this->archMap[$arch];
        }

        $this->currentState = \sprintf('%s (%s)', $os, $arch);

        return $os === 'Linux';
    }
}
