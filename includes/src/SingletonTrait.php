<?php

namespace JTL;

/**
 * Trait SingletonTrait
 * @package JTL
 */
trait SingletonTrait
{
    /**
     * @var static
     */
    private static $instance;

    /**
     * @return static
     */
    final public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * SingletonTrait constructor.
     */
    final public function __construct()
    {
        $this->init();
    }

    /**
     *
     */
    final public function __wakeup()
    {
    }

    /**
     *
     */
    final public function __clone()
    {
    }

    /**
     *
     */
    protected function init()
    {
    }
}
