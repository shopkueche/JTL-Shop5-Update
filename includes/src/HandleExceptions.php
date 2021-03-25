<?php

namespace JTL;

use ErrorException;
use Exception;
use JTL\Exceptions\FatalErrorException;
use JTL\Exceptions\FatalThrowableError;

/**
 * Class HandleExceptions
 * @package JTL
 */
class HandleExceptions
{
    /**
     * HandleExceptions constructor.
     */
    public function __construct()
    {
        \error_reporting(-1);
        \set_error_handler([$this, 'handleError']);
        \set_exception_handler([$this, 'handleException']);
        \register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Convert PHP errors to ErrorException instances.
     *
     * @param  int    $level
     * @param  string $message
     * @param  string $file
     * @param  int    $line
     * @param  array  $context
     * @return void
     *
     * @throws ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = []): void
    {
        if (\error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handle an uncaught exception from the application.
     *
     * Note: Most exceptions can be handled via the try / catch block in
     * the HTTP and Console kernels. But, fatal error exceptions must
     * be handled differently since they are not normal exceptions.
     *
     * @param \Throwable|FatalThrowableError $e
     * @return void
     */
    public function handleException($e): void
    {
        if (!$e instanceof Exception) {
            $e = new FatalThrowableError($e);
        }

        // report / log
        \dump($e);
    }

    /**
     * Handle the PHP shutdown event.
     *
     * @return void
     */
    public function handleShutdown(): void
    {
        if (($error = \error_get_last() !== null) && $this->isFatal($error['type'])) {
            $this->handleException($this->fatalExceptionFromError($error, 0));
        }
    }

    /**
     * Create a new fatal exception instance from an error array.
     *
     * @param  array    $error
     * @param  int|null $traceOffset
     * @return FatalErrorException
     */
    protected function fatalExceptionFromError(array $error, $traceOffset = null): FatalErrorException
    {
        return new FatalErrorException(
            $error['message'],
            $error['type'],
            0,
            $error['file'],
            $error['line'],
            $traceOffset
        );
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param  int $type
     * @return bool
     */
    protected function isFatal($type): bool
    {
        return \in_array($type, [\E_COMPILE_ERROR, \E_CORE_ERROR, \E_ERROR, \E_PARSE], true);
    }
}
