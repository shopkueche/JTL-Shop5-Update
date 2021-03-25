<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpConfigTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpMaxExecutionTime
 * @package Systemcheck\Tests\Shop5
 */
class PhpMaxExecutionTime extends PhpConfigTest
{
    protected $name          = 'max_execution_time';
    protected $requiredState = '>= 120';
    protected $description   = 'Für den Betrieb von JTL-Shop wird eine ausreichend lange Skriptlaufzeit benötigt, ' .
    'damit auch längere Aufgaben (z.B. Newsletterversand) zuverlässig funktionieren.';
    protected $isOptional    = true;
    protected $isRecommended = true;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        $maxExecutionTime = \ini_get('max_execution_time');
        $this->currentState = $maxExecutionTime;

        return $maxExecutionTime == 0 || $maxExecutionTime >= 120;
    }
}
