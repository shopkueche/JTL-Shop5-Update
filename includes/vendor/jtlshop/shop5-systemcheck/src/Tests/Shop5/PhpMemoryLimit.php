<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpConfigTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpMemoryLimit
 * @package Systemcheck\Tests\Shop5
 */
class PhpMemoryLimit extends PhpConfigTest
{
    protected $name          = 'memory_limit';
    protected $requiredState = '>= 128MB';
    protected $description   = '';
    protected $isOptional    = false;
    protected $isRecommended = false;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        $memoryLimit        = \ini_get('memory_limit');
        $this->currentState = $memoryLimit;

        return ($memoryLimit == -1 || $this->shortHandToInt($memoryLimit) >= $this->shortHandToInt('64M'));
    }
}
