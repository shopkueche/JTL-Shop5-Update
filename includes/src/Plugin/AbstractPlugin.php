<?php declare(strict_types=1);

namespace JTL\Plugin;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Plugin\Admin\StateChanger;
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
use JTL\Shop;

/**
 * Class AbstractPlugin
 * @package JTL\Plugin
 */
abstract class AbstractPlugin implements PluginInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $pluginID;

    /**
     * @var int
     */
    protected $state = State::DISABLED;

    /**
     * @var Meta
     */
    protected $meta;

    /**
     * @var Paths
     */
    protected $paths;

    /**
     * @var int
     */
    protected $priority = 5;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Links
     */
    protected $links;

    /**
     * @var License
     */
    protected $license;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var bool
     */
    protected $isLegacy = false;

    /**
     * @var bool
     */
    protected $bootstrap = false;

    /**
     * @var Hook[]
     */
    protected $hooks;

    /**
     * @var AdminMenu
     */
    protected $adminMenu;

    /**
     * @var Localization
     */
    protected $localization;

    /**
     * @var Widget
     */
    protected $widgets;

    /**
     * @var MailTemplates
     */
    protected $mailTemplates;

    /**
     * @var PaymentMethods
     */
    protected $paymentMethods;

    /**
     * @inheritdoc
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setID(int $id): void
    {
        $this->id = $id;
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
    public function setPluginID(string $pluginID): void
    {
        $this->pluginID = $pluginID;
    }

    /**
     * @inheritdoc
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @inheritdoc
     */
    public function setState(int $state): void
    {
        $this->state = $state;
    }

    /**
     * @inheritdoc
     */
    public function getMeta(): Meta
    {
        return $this->meta;
    }

    /**
     * @inheritdoc
     */
    public function setMeta(Meta $meta): void
    {
        $this->meta = $meta;
    }

    /**
     * @inheritdoc
     */
    public function getPaths(): Paths
    {
        return $this->paths;
    }

    /**
     * @inheritdoc
     */
    public function setPaths(Paths $paths): void
    {
        $this->paths = $paths;
    }

    /**
     * @inheritdoc
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @inheritdoc
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @inheritdoc
     */
    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getLinks(): Links
    {
        return $this->links;
    }

    /**
     * @inheritdoc
     */
    public function setLinks(Links $links): void
    {
        $this->links = $links;
    }

    /**
     * @inheritdoc
     */
    public function getLicense(): License
    {
        return $this->license;
    }

    /**
     * @inheritdoc
     */
    public function setLicense(License $license): void
    {
        $this->license = $license;
    }

    /**
     * @inheritdoc
     */
    public function getCache(): Cache
    {
        return $this->cache;
    }

    /**
     * @inheritdoc
     */
    public function setCache(Cache $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function isLegacy(): bool
    {
        return $this->isLegacy;
    }

    /**
     * @inheritdoc
     */
    public function setIsLegacy(bool $isLegacy): void
    {
        $this->isLegacy = $isLegacy;
    }

    /**
     * @inheritdoc
     */
    public function isExtension(): bool
    {
        return !$this->isLegacy;
    }

    /**
     * @inheritdoc
     */
    public function setIsExtension(bool $isExtension): void
    {
        $this->isLegacy = !$isExtension;
    }

    /**
     * @inheritdoc
     */
    public function isBootstrap(): bool
    {
        return $this->bootstrap;
    }

    /**
     * @inheritdoc
     */
    public function setBootstrap(bool $bootstrap): void
    {
        $this->bootstrap = $bootstrap;
    }

    /**
     * @inheritdoc
     */
    public function getHooks(): array
    {
        return $this->hooks;
    }

    /**
     * @inheritdoc
     */
    public function setHooks(array $hooks): void
    {
        $this->hooks = $hooks;
    }

    /**
     * @inheritdoc
     */
    public function getAdminMenu(): AdminMenu
    {
        return $this->adminMenu;
    }

    /**
     * @inheritdoc
     */
    public function setAdminMenu(AdminMenu $adminMenu): void
    {
        $this->adminMenu = $adminMenu;
    }

    /**
     * @inheritdoc
     */
    public function getLocalization(): Localization
    {
        return $this->localization;
    }

    /**
     * @inheritdoc
     */
    public function setLocalization(Localization $localization): void
    {
        $this->localization = $localization;
    }

    /**
     * @inheritdoc
     */
    public function getWidgets(): Widget
    {
        return $this->widgets;
    }

    /**
     * @inheritdoc
     */
    public function setWidgets(Widget $widgets): void
    {
        $this->widgets = $widgets;
    }

    /**
     * @inheritdoc
     */
    public function getMailTemplates(): MailTemplates
    {
        return $this->mailTemplates;
    }

    /**
     * @inheritdoc
     */
    public function setMailTemplates(MailTemplates $mailTemplates): void
    {
        $this->mailTemplates = $mailTemplates;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentMethods(): PaymentMethods
    {
        return $this->paymentMethods;
    }

    /**
     * @inheritdoc
     */
    public function setPaymentMethods(PaymentMethods $paymentMethods): void
    {
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * @inheritDoc
     */
    public function selfDestruct(
        int $newState = State::DISABLED,
        DbInterface $db = null,
        JTLCacheInterface $cache = null
    ): int {
        $stateChanger = new StateChanger($db ?? Shop::Container()->getDB(), $cache ?? Shop::Container()->getCache());

        return $stateChanger->deactivate($this->getID(), $newState);
    }
}
