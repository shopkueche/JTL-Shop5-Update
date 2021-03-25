<?php declare(strict_types=1);

namespace JTL\License\Installer;

use InvalidArgumentException;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\License\Downloader;
use JTL\License\Exception\ApiResultCodeException;
use JTL\License\Exception\ChecksumValidationException;
use JTL\License\Exception\DownloadValidationException;
use JTL\License\Exception\FilePermissionException;
use JTL\License\Manager;
use JTL\License\Struct\ExsLicense;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Helper
 * @package JTL\License\Installer
 */
class Helper
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * Helper constructor.
     * @param Manager           $manager
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(Manager $manager, DbInterface $db, JTLCacheInterface $cache)
    {
        $this->manager = $manager;
        $this->db      = $db;
        $this->cache   = $cache;
    }

    /**
     * @param string $itemID
     * @return PluginInstaller|TemplateInstaller
     */
    public function getInstaller(string $itemID)
    {
        $licenseData = $this->manager->getLicenseByItemID($itemID);
        if ($licenseData === null) {
            throw new InvalidArgumentException('Could not find item with ID ' . $itemID);
        }
        $available = $licenseData->getReleases()->getAvailable();
        if ($available === null) {
            throw new InvalidArgumentException('Could not find release for item with ID ' . $itemID);
        }
        switch ($licenseData->getType()) {
            case ExsLicense::TYPE_PLUGIN:
            case ExsLicense::TYPE_PORTLET:
                return new PluginInstaller($this->db, $this->cache);
            case ExsLicense::TYPE_TEMPLATE:
                return new TemplateInstaller($this->db, $this->cache);
            default:
                throw new InvalidArgumentException('Cannot update type ' . $licenseData->getType());
        }
    }

    /**
     * @param string $itemID
     * @return ResponseInterface|string
     * @throws DownloadValidationException
     * @throws InvalidArgumentException
     * @throws ApiResultCodeException
     * @throws FilePermissionException
     * @throws ChecksumValidationException
     */
    public function getDownload(string $itemID)
    {
        $licenseData = $this->manager->getLicenseByItemID($itemID);
        if ($licenseData === null) {
            throw new InvalidArgumentException('Could not find item with ID ' . $itemID);
        }
        $available = $licenseData->getReleases()->getAvailable();
        if ($available === null) {
            throw new InvalidArgumentException('Could not find update for item with ID ' . $itemID);
        }
        $downloader = new Downloader();

        return $downloader->downloadRelease($available);
    }
}
