<?php declare(strict_types=1);

namespace JTL\Backend\Wizard;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JTL\DB\DbInterface;
use JTL\Helpers\Text;
use JTL\License\AjaxResponse;
use JTL\License\Exception\ApiResultCodeException;
use JTL\License\Exception\ChecksumValidationException;
use JTL\License\Exception\DownloadValidationException;
use JTL\License\Exception\FilePermissionException;
use JTL\License\Installer\Helper;
use JTL\License\Manager as LicenseManager;
use JTL\Mapper\PluginValidation;
use JTL\Plugin\InstallCode;
use JTL\Recommendation\Recommendation;
use JTL\Shop;

/**
 * Class ExtensionInstaller
 * @package JTL\Backend\Wizard
 */
class ExtensionInstaller
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var Collection
     */
    private $recommendations;

    /**
     * @var LicenseManager
     */
    private $manager;

    /**
     * ExtensionInstaller constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $cache                 = Shop::Container()->getCache();
        $this->recommendations = new Collection();
        $this->manager         = new LicenseManager($db, $cache);
        $this->helper          = new Helper($this->manager, $db, $cache);
    }

    /**
     * @param string $id
     * @return Recommendation|null
     */
    private function getRecommendationByID(string $id): ?Recommendation
    {
        return $this->recommendations->first(static function (Recommendation $rec) use ($id) {
            return $rec->getId() === $id;
        });
    }

    /**
     * @return Collection
     */
    public function getRecommendations(): Collection
    {
        return $this->recommendations;
    }

    /**
     * @param Collection $recommendations
     */
    public function setRecommendations(Collection $recommendations): void
    {
        $this->recommendations = $recommendations;
    }

    /**
     * @param array $requested
     * @return string
     * @throws ApiResultCodeException
     * @throws ChecksumValidationException
     * @throws DownloadValidationException
     * @throws FilePermissionException
     * @throws GuzzleException
     */
    public function onSaveStep(array $requested): string
    {
        $createdLicenseKeys = [];
        $errorMsg           = '';
        foreach ($requested as $id) {
            $recom = $this->getRecommendationByID($id);
            if ($recom === null) {
                continue;
            }
            foreach ($recom->getLinks() as $link) {
                if ($link->getRel() === 'createLicense') {
                    try {
                        $res  = $this->manager->createLicense($link->getHref());
                        $data = \json_decode($res);
                        if (isset($data->meta)) {
                            $createdLicenseKeys[] = $data->meta->exs_key;
                        }
                    } catch (ClientException | GuzzleException $e) {
                        if ($e->getResponse()->getStatusCode() === 400
                            && ($license = $this->manager->getLicenseByExsID($id)) !== null
                        ) {
                            $createdLicenseKeys[] = $license->getLicense()->getKey();
                        } else {
                            $errorMsg .= \sprintf(
                                '%s: %s <br>',
                                $recom->getTitle(),
                                Text::htmlentities($e->getMessage())
                            );
                        }
                    }
                }
            }
        }
        if (!empty($createdLicenseKeys)) {
            $this->manager->update(true);

            foreach ($createdLicenseKeys as $key) {
                $ajaxResponse = new AjaxResponse();
                $license      = $this->manager->getLicenseByLicenseKey($key);
                if ($license === null) {
                    continue;
                }
                $itemID    = $license->getID();
                $installer = $this->helper->getInstaller($itemID);
                try {
                    $download    = $this->helper->getDownload($itemID);
                    $installCode = $installer->install($itemID, $download, $ajaxResponse);
                    if ($installCode === InstallCode::DUPLICATE_PLUGIN_ID) {
                        $download    = $this->helper->getDownload($itemID);
                        $installCode = $installer->forceUpdate($download, $ajaxResponse);
                    }
                } catch (InvalidArgumentException $e) {
                    $errorMsg .= \sprintf('%s: %s <br>', $license->getName(), $e->getMessage());
                }
                if (isset($installCode) && $installCode !== InstallCode::OK) {
                    $mapper    = new PluginValidation();
                    $errorMsg .= \sprintf('%s: %s <br>', $license->getName(), $mapper->map($installCode));
                }
            }
        }

        return $errorMsg;
    }
}
