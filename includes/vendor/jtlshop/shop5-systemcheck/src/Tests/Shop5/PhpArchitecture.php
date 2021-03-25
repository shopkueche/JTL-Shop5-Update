<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\ProgramTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpArchitecture
 * @package Systemcheck\Tests\Shop5
 */
class PhpArchitecture extends ProgramTest
{
    protected $name          = 'Architektur';
    protected $requiredState = '64bit';
    protected $description   = '';
    protected $isOptional    = false;
    protected $isRecommended = false;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        $is64bits           = \PHP_INT_SIZE === 8;
        $this->currentState = $is64bits ? '64bit' : '32bit';

        return $is64bits === true;
    }
}
