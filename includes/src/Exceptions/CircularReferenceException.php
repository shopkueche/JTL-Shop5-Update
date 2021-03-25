<?php

namespace JTL\Exceptions;

use Psr\Container\ContainerExceptionInterface;

/**
 * Class CircularReferenceException
 * @package JTL\Exceptions
 */
class CircularReferenceException extends \Exception implements ContainerExceptionInterface
{
    /**
     * @var string
     */
    protected $interface;

    /**
     * CircularReferenceException constructor.
     * @param string $interface
     */
    public function __construct($interface)
    {
        $this->interface = $interface;
        parent::__construct('Circular reference for "' . $interface .'" detected.');
    }
}
