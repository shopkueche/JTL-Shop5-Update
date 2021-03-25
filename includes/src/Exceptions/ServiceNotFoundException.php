<?php

namespace JTL\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ServiceNotFoundException
 * @package JTL\Exceptions
 */
class ServiceNotFoundException extends \Exception implements NotFoundExceptionInterface
{
    /**
     * @var string
     */
    protected $interface;

    /**
     * ServiceNotFoundException constructor.
     * @param string $interface
     */
    public function __construct($interface)
    {
        $this->interface = $interface;
        parent::__construct('The Service "' . $interface . '" could not be found.');
    }
}
