<?php declare(strict_types=1);

namespace JTL\License\Struct;

use DateTime;
use JTL\Template\Model;
use JTLShop\SemVer\Version;
use stdClass;

/**
 * Class ExsLicense
 * @package JTL\License
 */
class ExpiredExsLicense extends ExsLicense
{
    private function setDefaults(): void
    {
        $this->setQueryDate(new DateTime());
        $this->setState(self::STATE_ACTIVE);
        $this->setLinks([]);
        $this->setReleases(new Releases());
        $this->setParent(new InAppParent());
        $this->setCanBeUsed(false);
    }

    /**
     * @param stdClass $data
     * @throws \Exception
     */
    public function initFromPluginData(stdClass $data): void
    {
        $this->setDefaults();
        $this->setType(self::TYPE_PLUGIN);
        $this->setName($data->cName);
        $this->setExsID($data->exsID);
        $license = new License();
        $license->setIsBound(true);
        $license->setKey($data->cPluginID);
        $license->setExpired(true);
        $license->setCreated(new DateTime());
        $license->setType(License::TYPE_NONE);
        $this->setLicense($license);
        $this->setID($data->cPluginID);
        $subscription = new Subscription();
        $subscription->setExpired(true);
        $license->setSubscription($subscription);
        $vendor = new Vendor();
        $vendor->setName($data->cAutor);
        $vendor->setHref($data->cURL);
        $this->setVendor($vendor);
        $ref = new ReferencedPlugin();
        $ref->setInternalID((int)$data->kPlugin);
        $ref->setInstalled(true);
        $ref->setInstalledVersion(Version::parse($data->nVersion));
        $ref->setDateInstalled($data->dInstalliert);
        $this->setReferencedItem($ref);
    }

    /**
     * @param Model $data
     * @throws \Exception
     */
    public function initFromTemplateData(Model $data): void
    {
        $this->setDefaults();
        $this->setType(self::TYPE_TEMPLATE);
        $this->setName($data->cTemplate);
        $this->setExsID($data->getExsID());
        $license = new License();
        $license->setIsBound(true);
        $license->setKey($data->cTemplate);
        $license->setExpired(true);
        $license->setCreated(new DateTime());
        $license->setType(License::TYPE_NONE);
        $this->setLicense($license);
        $this->setID($data->cTemplate);
        $subscription = new Subscription();
        $subscription->setExpired(true);
        $license->setSubscription($subscription);
        $vendor = new Vendor();
        $vendor->setName($data->getUrl());
        $vendor->setHref($data->getUrl());
        $this->setVendor($vendor);
        $this->setReleases(new Releases());
        $ref = new ReferencedTemplate();
        $ref->setInternalID($data->getTemplateID());
        $ref->setInstalled(true);
        $ref->setInstalledVersion(Version::parse($data->getVersion()));
        $this->setReferencedItem($ref);
    }
}
