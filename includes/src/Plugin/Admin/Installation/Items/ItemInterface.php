<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\DB\DbInterface;
use JTL\Plugin\PluginInterface;
use stdClass;

/**
 * Interface ItemInterface
 * @package JTL\Plugin\Admin\Installation\Items
 */
interface ItemInterface
{
    /**
     * ItemInterface constructor.
     * @param DbInterface|null     $db
     * @param array|null           $baseNode
     * @param stdClass|null        $plugin
     * @param PluginInterface|null $oldPlugin
     */
    public function __construct(DbInterface $db = null, array $baseNode = null, $plugin = null, $oldPlugin = null);

    /**
     * @return array
     */
    public function getNode(): array;

    /**
     * @return mixed
     */
    public function install();

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface;

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void;

    /**
     * @return stdClass
     */
    public function getPlugin(): stdClass;

    /**
     * @param stdClass $plugin
     */
    public function setPlugin(stdClass $plugin): void;

    /**
     * @return PluginInterface|null
     */
    public function getOldPlugin(): ?PluginInterface;

    /**
     * @param PluginInterface|null $plugin
     */
    public function setOldPlugin(?PluginInterface $plugin): void;

    /**
     * @return array
     */
    public function getBaseNode(): array;

    /**
     * @param array $baseNode
     */
    public function setBaseNode(array $baseNode): void;
}
