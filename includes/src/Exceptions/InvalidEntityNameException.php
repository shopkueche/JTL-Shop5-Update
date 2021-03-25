<?php

namespace JTL\Exceptions;

/**
 * Class InvalidEntityNameException
 * @package JTL\Exceptions
 */
class InvalidEntityNameException extends \Exception
{
    /**
     * @var string
     */
    protected $entityName;

    /**
     * InvalidEntityNameException constructor.
     * @param string $entityName
     */
    public function __construct($entityName)
    {
        $this->entityName = $entityName;
        parent::__construct('Invalid entity name ' . $entityName);
    }
}
