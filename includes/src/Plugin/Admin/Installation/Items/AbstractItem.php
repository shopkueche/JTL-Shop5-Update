<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\DB\DbInterface;
use JTL\Plugin\LegacyPlugin;
use JTL\Plugin\PluginInterface;
use stdClass;

/**
 * Class AbstractItem
 * @package JTL\Plugin\Admin\Installation\Items
 */
abstract class AbstractItem implements ItemInterface
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var stdClass
     */
    protected $plugin;

    /**
     * @var stdClass|LegacyPlugin|null
     */
    protected $oldPlugin;

    /**
     * @var array
     */
    protected $baseNode;

    /**
     * @inheritdoc
     */
    public function __construct(DbInterface $db = null, array $baseNode = null, $plugin = null, $oldPlugin = null)
    {
        $this->db        = $db;
        $this->baseNode  = $baseNode;
        $this->plugin    = $plugin;
        $this->oldPlugin = $oldPlugin;
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
    }

    /**
     * @inheritDoc
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @inheritDoc
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function getPlugin(): stdClass
    {
        return $this->plugin;
    }

    /**
     * @inheritDoc
     */
    public function setPlugin(stdClass $plugin): void
    {
        $this->plugin = $plugin;
    }

    /**
     * @inheritDoc
     */
    public function getOldPlugin(): ?PluginInterface
    {
        return $this->oldPlugin;
    }

    /**
     * @inheritDoc
     */
    public function setOldPlugin($plugin): void
    {
        $this->oldPlugin = $plugin;
    }

    /**
     * @inheritDoc
     */
    public function getBaseNode(): array
    {
        return $this->baseNode;
    }

    /**
     * @inheritDoc
     */
    public function setBaseNode(array $baseNode): void
    {
        $this->baseNode = $baseNode;
    }
}
