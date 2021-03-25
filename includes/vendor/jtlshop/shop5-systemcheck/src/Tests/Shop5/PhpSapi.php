<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\ProgramTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpSapi
 * @package Systemcheck\Tests\Shop5
 */
class PhpSapi extends ProgramTest
{
    protected $name          = 'PHP-SAPI';
    protected $requiredState = 'Apache2, FastCGI, FPM';
    protected $description   = '';
    protected $isOptional    = true;
    protected $isRecommended = true;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        $sapi               = \PHP_SAPI;
        $sapiNames          = [
            'apache'         => 'Apache',
            'apache2filter'  => 'Apache 2.0',
            'apache2handler' => 'Apache 2.0',
            'cgi'            => 'CGI',
            'cgi-fcgi'       => 'FastCGI',
            'fpm-fcgi'       => 'FPM',
            'fpm'            => 'FPM',
            'cli'            => 'CLI'
        ];
        $this->currentState = $sapiNames[$sapi] ?? null;
        if (\array_key_exists($sapi, $sapiNames)) {
            return true;
        }
        // Refine detection in case the SAPI check gives unexpected results
        if (\function_exists('fastcgi_finish_request')) {
            $sapi               = 'fpm';
            $this->currentState = $sapiNames[$sapi];

            return true;
        }

        return false;
    }
}
