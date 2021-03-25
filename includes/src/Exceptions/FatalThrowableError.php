<?php

namespace JTL\Exceptions;

/**
 * Class FatalThrowableError
 * @package JTL\Exceptions
 * @author Nicolas Grekas <p@tchwork.com>
 */
class FatalThrowableError extends FatalErrorException
{
    /**
     * @var string
     */
    private $originalClassName;

    /**
     * FatalThrowableError constructor.
     * @param \Throwable $e
     */
    public function __construct(\Throwable $e)
    {
        $this->originalClassName = \get_class($e);

        if ($e instanceof \ParseError) {
            $severity = \E_PARSE;
        } elseif ($e instanceof \TypeError) {
            $severity = \E_RECOVERABLE_ERROR;
        } else {
            $severity = \E_ERROR;
        }

        \ErrorException::__construct(
            $e->getMessage(),
            $e->getCode(),
            $severity,
            $e->getFile(),
            $e->getLine(),
            $e->getPrevious()
        );

        $this->setTrace($e->getTrace());
    }

    /**
     * @return string
     */
    public function getOriginalClassName(): string
    {
        return $this->originalClassName;
    }
}
