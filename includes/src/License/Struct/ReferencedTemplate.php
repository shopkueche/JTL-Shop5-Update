<?php declare(strict_types=1);

namespace JTL\License\Struct;

use JTL\DB\DbInterface;
use JTL\Template\Admin\Listing;
use JTL\Template\Admin\ListingItem;
use JTL\Template\Admin\Validation\TemplateValidator;
use JTLShop\SemVer\Version;
use stdClass;

/**
 * Class ReferencedTemplate
 * @package JTL\License\Struct
 */
class ReferencedTemplate extends ReferencedItem
{
    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function initByExsID(DbInterface $db, stdClass $license, ?Release $release): void
    {
        $exsid = $license->exsid;
        $data  = $db->select('ttemplate', 'eTyp', 'standard');
        if ($data !== null && $data->exsID === $exsid) {
            $releaseVersion   = $release === null ? Version::parse('0.0.0') : $release->getVersion();
            $installedVersion = Version::parse($data->version);
            $this->setID($data->cTemplate);
            $this->setMaxInstallableVersion($releaseVersion);
            $this->setHasUpdate($installedVersion->smallerThan($releaseVersion));
            $this->setInstalled(true);
            $this->setInstalledVersion($installedVersion);
            $this->setActive(true);
            $this->setInitialized(true);
        } else {
            $lstng = new Listing($db, new TemplateValidator($db));
            foreach ($lstng->getAll() as $template) {
                /** @var ListingItem $template */
                if ($template->getExsID() === $exsid) {
                    $installedVersion = Version::parse($template->getVersion());
                    $this->setID($template->getPath());
                    $this->setMaxInstallableVersion($release->getVersion());
                    $this->setHasUpdate($installedVersion->smallerThan($release->getVersion()));
                    $this->setInstalled(true);
                    $this->setInstalledVersion($installedVersion);
                    $this->setActive(false);
                    $this->setInitialized(true);
                    break;
                }
            }
        }
    }
}
