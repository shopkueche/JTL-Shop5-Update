<?php declare(strict_types=1);

namespace JTL\License\Installer;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\License\AjaxResponse;
use JTL\Plugin\Admin\Installation\Extractor;
use JTL\Plugin\Admin\Installation\InstallationResponse;
use JTL\Shop;
use JTL\XMLParser;

/**
 * Class PluginInstaller
 * @package JTL\License\Installer
 */
class TemplateInstaller implements InstallerInterface
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var JTLCacheInterface
     */
    protected $cache;

    /**
     * TemplateInstaller constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->db    = $db;
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function update(string $exsID, string $zip, AjaxResponse $response): int
    {
        $extractor        = new Extractor(new XMLParser());
        $installResponse  = $extractor->extractTemplate($zip);
        $response->status = $installResponse->getStatus();
        if ($response->status === InstallationResponse::STATUS_FAILED) {
            $response->error      = $installResponse->getError() ?? \implode(', ', $installResponse->getMessages());
            $response->additional = $installResponse;

            return 0;
        }
        Shop::Container()->getTemplateService()->reset();

        return 1;
    }

    /**
     * @inheritDoc
     */
    public function install(string $itemID, string $zip, AjaxResponse $response): int
    {
        return $this->update($itemID, $zip, $response);
    }

    /**
     * @inheritDoc
     */
    public function forceUpdate(string $zip, AjaxResponse $response): int
    {
        return $this->install('', $zip, $response);
    }
}
