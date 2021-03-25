<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpConfigTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpAllowUrlFopen
 * @package Systemcheck\Tests\Shop5
 */
class PhpAllowUrlFopen extends PhpConfigTest
{
    protected $name          = 'allow_url_fopen';
    protected $requiredState = 'on';
    protected $description   = '';
    protected $isOptional    = true;
    protected $isRecommended = true;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        $allowUrlFopen      = (bool)\ini_get('allow_url_fopen');
        $this->currentState = $allowUrlFopen ? 'on' : 'off';

        return $allowUrlFopen === true;
    }
}
