<?php

namespace JTL\IO;

use JsonSerializable;

/**
 * Class IOError
 * @package JTL\IO
 */
class IOError implements JsonSerializable
{
    /**
     * @var string
     */
    public $message = '';

    /**
     * @var int
     */
    public $code = 500;

    /**
     * @var array
     */
    public $errors = [];

    /**
     * IOError constructor.
     *
     * @param string     $message
     * @param int        $code
     * @param array|null $errors
     */
    public function __construct($message, $code = 500, array $errors = null)
    {
        $this->message = $message;
        $this->code    = $code;
        $this->errors  = $errors;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'error' => [
                'message' => $this->message,
                'code'    => $this->code,
                'errors'  => $this->errors
            ]
        ];
    }
}
