<?php

namespace JTL\Services;

/**
 * Interface ContainerInterface
 * @package JTL\Services
 */
interface ContainerInterface extends \Psr\Container\ContainerInterface
{
    /**
     * @param string   $id
     * @param callable $factory
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function setSingleton($id, $factory): void;

    /**
     * @param string   $id
     * @param callable $factory
     * @throws \InvalidArgumentException
     */
    public function setFactory($id, $factory): void;

    /**
     * @param string $id
     * @return mixed
     */
    public function getFactoryMethod($id);
}
