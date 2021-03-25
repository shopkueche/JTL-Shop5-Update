<?php declare(strict_types=1);

namespace Systemcheck\Tests\Shop5;

use Systemcheck\Tests\PhpConfigTest;
use Systemcheck\Tests\AbstractTest;

/**
 * Class PhpUploadMaxFilesize
 * @package Systemcheck\Tests\Shop5
 */
class PhpUploadMaxFilesize extends PhpConfigTest
{
    protected $name          = 'upload_max_filesize';
    protected $requiredState = '>= 6M';
    protected $description   = '';
    protected $isOptional    = true;
    protected $isRecommended = true;

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        $uploadMaxFilesize  = \ini_get('upload_max_filesize');
        $this->currentState = $uploadMaxFilesize;

        return $this->shortHandToInt($uploadMaxFilesize) >= $this->shortHandToInt('6M');
    }
}
