<?php

namespace JTL\Services;

/**
 * Class ContainerEntry
 * @package JTL\Services
 */
class ContainerEntry
{
    public const TYPE_FACTORY = 1;

    public const TYPE_SINGLETON = 2;

    /**
     * @var callable
     */
    protected $factory;

    /**
     * @var object
     */
    protected $instance;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var bool
     */
    protected $locked = false;

    /**
     * ContainerEntry constructor.
     * @param callable $factory
     * @param int      $type
     */
    public function __construct(callable $factory, int $type)
    {
        if ($type !== self::TYPE_FACTORY && $type !== self::TYPE_SINGLETON) {
            throw new \InvalidArgumentException('$type incorrect');
        }
        $this->factory = $factory;
        $this->type    = $type;
    }

    /**
     * @return object
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @return bool
     */
    public function hasInstance(): bool
    {
        return $this->instance !== null;
    }

    /**
     * @param object $instance
     */
    public function setInstance($instance): void
    {
        $this->instance = $instance;
    }

    /**
     * @return callable
     */
    public function getFactory(): callable
    {
        return $this->factory;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function lock(): void
    {
        $this->locked = true;
    }

    public function unlock(): void
    {
        $this->locked = false;
    }
}
