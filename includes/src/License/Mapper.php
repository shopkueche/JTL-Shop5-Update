<?php declare(strict_types=1);

namespace JTL\License;

use JTL\License\Struct\ExsLicense;
use JTL\License\Struct\ReferencedPlugin;
use JTL\License\Struct\ReferencedTemplate;
use stdClass;

/**
 * Class Mapper
 * @package JTL\License
 */
class Mapper
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * Mapper constructor.
     * @param Manager     $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return Collection
     */
    public function getCollection(): Collection
    {
        $cacheID = 'mapper_lic_collection';
        if (($collection = $this->manager->getCache()->get($cacheID)) !== false) {
            return $collection;
        }
        $collection = new Collection();
        $data       = $this->manager->getLicenseData();
        if ($data === null) {
            return $collection;
        }
        foreach ($data->extensions as $extension) {
            $exsLicense = new ExsLicense($extension);
            $exsLicense->setQueryDate($data->timestamp);
            if ($exsLicense->getState() === ExsLicense::STATE_ACTIVE) {
                $this->setReference($exsLicense, $extension);
            }
            $collection->push($exsLicense);
        }
        $this->manager->getCache()->set($cacheID, $collection, [\CACHING_GROUP_LICENSES]);

        return $collection;
    }

    /**
     * @param ExsLicense $esxLicense
     * @param stdClass   $license
     * @throws \Exception
     */
    private function setReference(ExsLicense $esxLicense, stdClass $license): void
    {
        switch ($esxLicense->getType()) {
            case ExsLicense::TYPE_PLUGIN:
            case ExsLicense::TYPE_PORTLET:
                $plugin = new ReferencedPlugin();
                $plugin->initByExsID(
                    $this->manager->getDB(),
                    $license,
                    $esxLicense->getReleases()->getLatest() ?? $esxLicense->getReleases()->getAvailable()
                );
                $esxLicense->setReferencedItem($plugin);
                break;
            case ExsLicense::TYPE_TEMPLATE:
                $template = new ReferencedTemplate();
                $template->initByExsID(
                    $this->manager->getDB(),
                    $license,
                    $esxLicense->getReleases()->getLatest() ?? $esxLicense->getReleases()->getAvailable()
                );
                $esxLicense->setReferencedItem($template);
                break;
        }
    }
}
