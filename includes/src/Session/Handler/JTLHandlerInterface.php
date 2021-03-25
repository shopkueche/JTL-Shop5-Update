<?php

namespace JTL\Session\Handler;

/**
 * Interface JTLHandlerInterface
 * @package JTL\Session\Handler
 */
interface JTLHandlerInterface extends \SessionHandlerInterface
{
    /**
     * @param array|null $data
     */
    public function setSessionData(&$data): void;

    /**
     * @return array|null
     */
    public function getSessionData(): ?array;

    /**
     * @return array|null
     */
    public function getAll(): ?array;

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function set($name, $value);

    /**
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public static function array_set(&$array, $key, $value);

    /**
     * put a key/value pair or array of key/value pairs in the session.
     *
     * @param  string|array $key
     * @param  mixed|null   $value
     */
    public function put($key, $value = null): void;

    /**
     * push a value onto a session array.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function push($key, $value): void;
}
