<?php declare(strict_types=1);

namespace JTL\Template;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\License\Struct\ExsLicense;
use JTL\Smarty\JTLSmarty;

/**
 * Class Bootstrapper
 * @package JTL\Plugin
 */
abstract class Bootstrapper implements BootstrapperInterface
{
    /**
     * @var Model
     */
    private $template;

    /**
     * @var JTLSmarty
     */
    private $smarty;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * Bootstrapper constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    final public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->db    = $db;
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function boot(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function installed(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function uninstalled(bool $deleteData = true): void
    {
    }

    /**
     * @inheritdoc
     */
    public function enabled(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function disabled(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function updated($oldVersion, $newVersion): void
    {
    }

    /**
     * @inheritdoc
     */
    public function getTemplate(): Model
    {
        return $this->template;
    }

    /**
     * @inheritdoc
     */
    public function setTemplate(Model $template): void
    {
        $this->template = $template;
    }

    /**
     * @inheritDoc
     */
    public function getSmarty(): ?JTLSmarty
    {
        return $this->smarty;
    }

    /**
     * @inheritDoc
     */
    public function setSmarty(JTLSmarty $smarty): void
    {
        $this->smarty = $smarty;
    }

    /**
     * @inheritdoc
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @inheritdoc
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function getCache(): JTLCacheInterface
    {
        return $this->cache;
    }

    /**
     * @inheritdoc
     */
    public function setCache(JTLCacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function licenseExpired(ExsLicense $license): void
    {
    }
}
