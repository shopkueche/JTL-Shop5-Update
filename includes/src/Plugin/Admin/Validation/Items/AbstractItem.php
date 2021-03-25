<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\Admin\Validation\ValidationItemInterface;
use JTL\Plugin\InstallCode;

/**
 * Class AbstractItem
 * @package JTL\Plugin\Admin\Validation\Items
 */
class AbstractItem implements ValidationItemInterface
{
    /**
     * @var array
     */
    protected $baseNode;

    /**
     * @var array
     */
    protected $installNode;

    /**
     * @var string
     */
    protected $baseDir = '';

    /**
     * @var string
     */
    protected $dir = '';

    /**
     * @var int
     */
    protected $version = 100;

    /**
     * @var string
     */
    protected $pluginID = '';

    /**
     * @var string
     */
    protected $context = '';

    /**
     * AbstractItem constructor.
     * @param array  $baseNode
     * @param string $baseDir
     * @param string $version
     * @param string $pluginID
     */
    public function __construct(array $baseNode, string $baseDir, string $version, string $pluginID)
    {
        $this->baseNode    = $baseNode;
        $this->installNode = $baseNode['Install'][0] ?? null;
        $this->baseDir     = $baseDir;
        $this->dir         = $baseDir . \DIRECTORY_SEPARATOR . \PFAD_PLUGIN_VERSION . $version . \DIRECTORY_SEPARATOR;
        $this->version     = $version;
        $this->pluginID    = $pluginID;
        $this->context     = self::CONTEXT_LEGACY_PLUGIN;
    }

    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        return InstallCode::OK;
    }

    /**
     * @inheritdoc
     */
    public function getBaseNode(): array
    {
        return $this->baseNode;
    }

    /**
     * @inheritdoc
     */
    public function setBaseNode(array $node): void
    {
        $this->baseNode = $node;
    }

    /**
     * @inheritdoc
     */
    public function getInstallNode(): array
    {
        return $this->installNode;
    }

    /**
     * @inheritdoc
     */
    public function setInstallNode(array $node): void
    {
        $this->installNode = $node;
    }

    /**
     * @inheritdoc
     */
    public function getPluginID(): string
    {
        return $this->pluginID;
    }

    /**
     * @inheritdoc
     */
    public function setPluginID(string $id): void
    {
        $this->pluginID = $id;
    }

    /**
     * @inheritdoc
     */
    public function getBaseDir(): string
    {
        return $this->baseDir;
    }

    /**
     * @inheritdoc
     */
    public function setBaseDir(string $dir): void
    {
        $this->baseDir = $dir;
    }

    /**
     * @inheritdoc
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * @inheritdoc
     */
    public function setDir(string $dir): void
    {
        $this->dir = $dir;
    }

    /**
     * @inheritdoc
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @inheritdoc
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * @inheritdoc
     */
    public function setContext(string $context): void
    {
        $this->context = $context;
    }
}
