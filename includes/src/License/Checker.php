<?php declare(strict_types=1);

namespace JTL\License;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Events\Dispatcher;
use JTL\License\Struct\ExpiredExsLicense;
use JTL\License\Struct\ExsLicense;
use JTL\Plugin\Admin\StateChanger;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\PluginLoader;
use JTL\Plugin\State;
use JTL\Shop;
use JTL\Template\BootChecker;
use Psr\Log\LoggerInterface;

/**
 * Class Checker
 * @package JTL\License
 */
class Checker
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * Checker constructor.
     * @param LoggerInterface   $logger
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(LoggerInterface $logger, DbInterface $db, JTLCacheInterface $cache)
    {
        $this->logger = $logger;
        $this->db     = $db;
        $this->cache  = $cache;
    }

    /**
     * @param Mapper $mapper
     * @return Collection
     */
    public function getUpdates(Mapper $mapper): Collection
    {
        return $mapper->getCollection()->getUpdateableItems();
    }

    /**
     * @param Manager $manager
     */
    public function handleExpiredLicenses(Manager $manager): void
    {
        $mapper     = new Mapper($manager);
        $collection = $mapper->getCollection();
        $this->notifyPlugins($collection);
        $this->notifyTemplates($collection);
        $this->handleExpiredPluginTestLicenses($collection);
    }

    /**
     * @param Mapper $mapper
     * @return Collection
     */
    public function getLicenseViolations(Mapper $mapper): Collection
    {
        return $this->getPluginsWithoutLicense($mapper->getCollection()->getLicenseViolations());
    }

    /**
     * @param Collection $items
     * @return Collection
     */
    private function getPluginsWithoutLicense(Collection $items): Collection
    {
        $plugins = $this->db->selectAll('tplugin', ['bExtension', 'nStatus'], [1, 2]);
        $loader  = new PluginLoader($this->db, $this->cache);
        foreach ($plugins as $dataItem) {
            $plugin     = $loader->loadFromObject($dataItem, Shop::getLanguageCode());
            $exsLicense = $plugin->getLicense()->getExsLicense();
            if ($exsLicense !== null && \is_a($exsLicense, ExpiredExsLicense::class)) {
                $items->add($exsLicense);
            }
        }

        return $items;
    }

    /**
     * @param Collection $collection
     */
    private function notifyTemplates(Collection $collection): void
    {
        foreach ($collection->getTemplates()->getDedupedActiveExpired() as $license) {
            /** @var ExsLicense $license */
            $this->logger->info(\sprintf('License for template %s is expired.', $license->getID()));
            $bootstrapper = BootChecker::bootstrap($license->getID());
            if ($bootstrapper !== null) {
                $bootstrapper->licenseExpired($license);
            }
        }
    }

    /**
     * @param Collection $collection
     */
    private function notifyPlugins(Collection $collection): void
    {
        $dispatcher = Dispatcher::getInstance();
        $loader     = new PluginLoader($this->db, $this->cache);
        foreach ($collection->getPlugins()->getDedupedActiveExpired() as $license) {
            /** @var ExsLicense $license */
            $this->logger->info(\sprintf('License for plugin %s is expired.', $license->getID()));
            if (($p = PluginHelper::bootstrap($license->getReferencedItem()->getInternalID(), $loader)) !== null) {
                $p->boot($dispatcher);
                $p->licenseExpired($license);
            }
        }
    }

    /**
     * @param Collection $collection
     */
    private function handleExpiredPluginTestLicenses(Collection $collection): void
    {
        $expired = $collection->getDedupedExpiredBoundTests()->filter(static function (ExsLicense $e) {
            return $e->getType() === ExsLicense::TYPE_PLUGIN;
        });
        if ($expired->count() === 0) {
            return;
        }
        $stateChanger = new StateChanger($this->db, $this->cache);
        foreach ($expired as $license) {
            /** @var ExsLicense $license */
            $this->logger->warning(\sprintf('Plugin %s disabled due to expired test license.', $license->getID()));
            $stateChanger->deactivate($license->getReferencedItem()->getInternalID(), State::LICENSE_KEY_INVALID);
        }
    }
}
