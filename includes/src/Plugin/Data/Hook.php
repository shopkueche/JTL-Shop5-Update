<?php declare(strict_types=1);

namespace JTL\Plugin\Data;

/**
 * Class Hook
 * @package JTL\Plugin\Data
 */
class Hook
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $pluginID;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var int
     * @todo
     */
    private $calledHookID = -1;

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getPluginID(): int
    {
        return $this->pluginID;
    }

    /**
     * @param int $pluginID
     */
    public function setPluginID(int $pluginID): void
    {
        $this->pluginID = $pluginID;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return int
     */
    public function getCalledHookID(): int
    {
        return $this->calledHookID;
    }

    /**
     * @param int $calledHookID
     */
    public function setCalledHookID(int $calledHookID): void
    {
        $this->calledHookID = $calledHookID;
    }
}
