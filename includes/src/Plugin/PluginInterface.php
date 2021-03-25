<?php declare(strict_types=1);

namespace JTL\Plugin;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Plugin\Data\AdminMenu;
use JTL\Plugin\Data\Cache;
use JTL\Plugin\Data\Config;
use JTL\Plugin\Data\Hook;
use JTL\Plugin\Data\License;
use JTL\Plugin\Data\Links;
use JTL\Plugin\Data\Localization;
use JTL\Plugin\Data\MailTemplates;
use JTL\Plugin\Data\Meta;
use JTL\Plugin\Data\Paths;
use JTL\Plugin\Data\PaymentMethods;
use JTL\Plugin\Data\Widget;
use JTLShop\SemVer\Version;

/**
 * Interface PluginInterface
 * @package JTL\Plugin
 */
interface PluginInterface
{
    /**
     * @return int
     */
    public function getID(): int;

    /**
     * @param int $id
     */
    public function setID(int $id): void;

    /**
     * @return string
     */
    public function getPluginID(): string;

    /**
     * @param string $pluginID
     */
    public function setPluginID(string $pluginID): void;

    /**
     * @return int
     */
    public function getState(): int;

    /**
     * @param int $state
     */
    public function setState(int $state): void;

    /**
     * @return Meta
     */
    public function getMeta(): Meta;

    /**
     * @param Meta $meta
     */
    public function setMeta(Meta $meta): void;

    /**
     * @return Paths
     */
    public function getPaths(): Paths;

    /**
     * @param Paths $paths
     */
    public function setPaths(Paths $paths): void;

    /**
     * @return int
     */
    public function getPriority(): int;

    /**
     * @param int $priority
     */
    public function setPriority(int $priority): void;

    /**
     * @return Config
     */
    public function getConfig(): Config;

    /**
     * @param Config $config
     */
    public function setConfig(Config $config): void;

    /**
     * @return Links
     */
    public function getLinks(): Links;

    /**
     * @param Links $links
     */
    public function setLinks(Links $links): void;

    /**
     * @return License
     */
    public function getLicense(): License;

    /**
     * @param License $license
     */
    public function setLicense(License $license): void;

    /**
     * @return Cache
     */
    public function getCache(): Cache;

    /**
     * @param Cache $cache
     */
    public function setCache(Cache $cache): void;

    /**
     * @return bool
     */
    public function isLegacy(): bool;

    /**
     * @param bool $isLegacy
     */
    public function setIsLegacy(bool $isLegacy): void;

    /**
     * @return bool
     */
    public function isExtension(): bool;

    /**
     * @param bool $isExtension
     */
    public function setIsExtension(bool $isExtension): void;

    /**
     * @return bool
     */
    public function isBootstrap(): bool;

    /**
     * @param bool $bootstrap
     */
    public function setBootstrap(bool $bootstrap): void;

    /**
     * @return Hook[]
     */
    public function getHooks(): array;

    /**
     * @param Hook[] $hooks
     */
    public function setHooks(array $hooks): void;

    /**
     * @return AdminMenu
     */
    public function getAdminMenu(): AdminMenu;

    /**
     * @param AdminMenu $adminMenu
     */
    public function setAdminMenu(AdminMenu $adminMenu): void;

    /**
     * @return Localization
     */
    public function getLocalization(): Localization;

    /**
     * @param Localization $localization
     */
    public function setLocalization(Localization $localization): void;

    /**
     * @return Widget
     */
    public function getWidgets(): Widget;

    /**
     * @param Widget $widgets
     */
    public function setWidgets(Widget $widgets): void;

    /**
     * @return MailTemplates
     */
    public function getMailTemplates(): MailTemplates;

    /**
     * @param MailTemplates $mailTemplates
     */
    public function setMailTemplates(MailTemplates $mailTemplates): void;

    /**
     * @return PaymentMethods
     */
    public function getPaymentMethods(): PaymentMethods;

    /**
     * @param PaymentMethods $paymentMethods
     */
    public function setPaymentMethods(PaymentMethods $paymentMethods): void;

    /**
     * @return Version
     */
    public function getCurrentVersion(): Version;

    /**
     * @param int                    $newState
     * @param DbInterface|null       $db
     * @param JTLCacheInterface|null $cache
     * @return int
     */
    public function selfDestruct(
        int $newState = State::DISABLED,
        DbInterface $db = null,
        JTLCacheInterface $cache = null
    ): int;
}
