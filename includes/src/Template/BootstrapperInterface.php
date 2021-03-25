<?php declare(strict_types=1);

namespace JTL\Template;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\License\Struct\ExsLicense;
use JTL\Smarty\JTLSmarty;

/**
 * Interface BootstrapperInterface
 * @package JTL\Template
 */
interface BootstrapperInterface
{
    /**
     *
     */
    public function boot(): void;

    /**
     *
     */
    public function installed(): void;

    /**
     * @param bool $deleteData
     */
    public function uninstalled(bool $deleteData = true): void;

    /**
     *
     */
    public function enabled(): void;

    /**
     *
     */
    public function disabled(): void;

    /**
     * @param mixed $oldVersion
     * @param mixed $newVersion
     */
    public function updated($oldVersion, $newVersion): void;

    /**
     * @return Model
     */
    public function getTemplate(): Model;

    /**
     * @param Model $template
     */
    public function setTemplate(Model $template): void;

    /**
     * this will only work after boot()
     *
     * @return JTLSmarty|null
     */
    public function getSmarty(): ?JTLSmarty;

    /**
     * @param JTLSmarty $smarty
     */
    public function setSmarty(JTLSmarty $smarty): void;

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface;

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void;

    /**
     * @return JTLCacheInterface
     */
    public function getCache(): JTLCacheInterface;

    /**
     * @param JTLCacheInterface $cache
     */
    public function setCache(JTLCacheInterface $cache): void;

    /**
     * @param ExsLicense $license
     */
    public function licenseExpired(ExsLicense $license): void;
}
