<?php

namespace JTL\Services;

use Illuminate\Container\Container as IlluminateContainer;

/**
 * Class ContainerBase
 * @package JTL\Services
 */
class ContainerBase extends IlluminateContainer implements ContainerInterface
{
    /**
     * @inheritdoc
     */
    public function setSingleton($id, $factory): void
    {
        $this->singleton($id, $factory);
    }

    /**
     * @inheritdoc
     */
    public function setFactory($id, $factory): void
    {
        $this->bind($id, $factory);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function getFactoryMethod($id)
    {
        return $this->get($id);
    }
}
