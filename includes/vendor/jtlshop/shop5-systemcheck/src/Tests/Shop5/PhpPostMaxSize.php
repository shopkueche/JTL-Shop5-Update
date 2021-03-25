<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpConfigTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpPostMaxSize
 * @package Systemcheck\Tests\Shop5
 */
class PhpPostMaxSize extends PhpConfigTest
{
    protected $name          = 'post_max_size';
    protected $requiredState = '>= 8M';
    protected $description   = '';
    protected $isOptional    = true;
    protected $isRecommended = true;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        $postMaxSize        = \ini_get('post_max_size');
        $this->currentState = $postMaxSize;

        return $this->shortHandToInt($postMaxSize) >= $this->shortHandToInt('8M');
    }
}
