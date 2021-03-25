<?php declare(strict_types=1);

namespace JTL\License\Struct;

use DateTime;
use stdClass;

/**
 * Class ExsLicense
 * @package JTL\License
 */
class ExsLicense
{
    public const TYPE_PLUGIN = 'plugin';

    public const TYPE_TEMPLATE = 'template';

    public const TYPE_PORTLET = 'portlet';

    public const STATE_ACTIVE = 1;

    public const STATE_UNBOUND = 0;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $exsid;

    /**
     * @var Vendor
     */
    private $vendor;

    /**
     * @var License
     */
    private $license;

    /**
     * @var Releases
     */
    private $releases;

    /**
     * @var Link[]
     */
    private $links;

    /**
     * @var DateTime
     */
    private $queryDate;

    /**
     * @var int
     */
    private $state = self::STATE_UNBOUND;

    /**
     * @var ReferencedItemInterface|null
     */
    private $referencedItem;

    /**
     * @var InAppParent
     */
    private $parent;

    /**
     * @var bool
     */
    private $isInApp = false;

    /**
     * @var bool
     */
    private $hasSubscription = false;

    /**
     * @var bool
     */
    private $hasLicense = false;

    /**
     * @var bool
     */
    private $canBeUsed = true;

    /**
     * ExsLicenseData constructor.
     * @param stdClass|null $json
     */
    public function __construct(?stdClass $json = null)
    {
        if ($json !== null) {
            $this->fromJSON($json);
        }
    }

    /**
     * @param stdClass $json
     */
    public function fromJSON(stdClass $json): void
    {
        $this->setID($json->id);
        $this->setType($json->type ?? self::TYPE_PLUGIN);
        $this->setName($json->name);
        $this->setExsID($json->exsid);
        if (isset($json->license)) {
            $this->setLicense(new License($json->license));
            $this->setHasLicense($this->getLicense()->getValidUntil() !== null);
            $this->setHasSubscription($this->getLicense()->getSubscription()->getValidUntil() !== null);
        }
        $this->setVendor(new Vendor($json->vendor));
        if (\is_array($json->releases)) {
            $json->releases = null; // the api sends an empty array instead of an object when there are none...
        }
        $this->releases = new Releases($json->releases);
        foreach ($json->links as $link) {
            $this->links[] = new Link($link);
        }
        if (isset($json->license->metas->in_app)) {
            $this->setParent(new InAppParent($json->license->metas->in_app));
            $this->setIsInApp(true);
        } else {
            $this->setParent(new InAppParent());
        }
        $this->check();
    }

    private function check(): void
    {
        $license             = $this->getLicense();
        $licenseExpired      = $license->isExpired();
        $subscriptionExpired = $license->getSubscription()->isExpired();
        if ($licenseExpired || $subscriptionExpired) {
            if ($license->getType() === License::TYPE_TEST) {
                $this->canBeUsed = false;
                return;
            }
            $release = $this->getReleases()->getAvailable();
            if ($release === null) {
                $this->canBeUsed = false;
                return;
            }
            if ($licenseExpired) {
                $this->canBeUsed = $license->getValidUntil() >= $release->getReleaseDate();
            } elseif ($subscriptionExpired) {
                $this->canBeUsed = $license->getSubscription()->getValidUntil() >= $release->getReleaseDate();
            }
        }
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setID(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getExsID(): string
    {
        return $this->exsid;
    }

    /**
     * @param string $exsid
     */
    public function setExsID(string $exsid): void
    {
        $this->exsid = $exsid;
    }

    /**
     * @return Vendor
     */
    public function getVendor(): Vendor
    {
        return $this->vendor;
    }

    /**
     * @param Vendor $vendor
     */
    public function setVendor(Vendor $vendor): void
    {
        $this->vendor = $vendor;
    }

    /**
     * @return License
     */
    public function getLicense(): License
    {
        return $this->license;
    }

    /**
     * @param License $license
     */
    public function setLicense(License $license): void
    {
        $this->license = $license;
        if ($license->isBound()) {
            $this->setState(self::STATE_ACTIVE);
        }
    }

    /**
     * @return Releases
     */
    public function getReleases(): Releases
    {
        return $this->releases;
    }

    /**
     * @param Releases $releases
     */
    public function setReleases(Releases $releases): void
    {
        $this->releases = $releases;
    }

    /**
     * @return Link[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param Link[] $links
     */
    public function setLinks(array $links): void
    {
        $this->links = $links;
    }

    /**
     * @return DateTime
     */
    public function getQueryDate(): DateTime
    {
        return $this->queryDate;
    }

    /**
     * @param DateTime|string $queryDate
     * @throws \Exception
     */
    public function setQueryDate($queryDate): void
    {
        $this->queryDate = \is_a($queryDate, DateTime::class) ? $queryDate : new DateTime($queryDate);
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void
    {
        $this->state = $state;
    }

    /**
     * @return ReferencedItemInterface|null
     */
    public function getReferencedItem(): ?ReferencedItemInterface
    {
        return $this->referencedItem;
    }

    /**
     * @param ReferencedItemInterface|null $referencedItem
     */
    public function setReferencedItem(?ReferencedItemInterface $referencedItem): void
    {
        $this->referencedItem = $referencedItem;
        if ($referencedItem !== null
            && $this->canBeUsed === true
            && ($this->getLicense()->isExpired() || $this->getLicense()->getSubscription()->isExpired())
        ) {
            $avail = $this->getReleases()->getAvailable();
            $inst  = $referencedItem->getInstalledVersion();
            if ($avail !== null && $inst !== null && $inst->greaterThan($avail->getVersion())) {
                $this->canBeUsed = false;
            }
        }
    }

    /**
     * @return InAppParent
     */
    public function getParent(): InAppParent
    {
        return $this->parent;
    }

    /**
     * @param InAppParent $parent
     */
    public function setParent(InAppParent $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return bool
     */
    public function isInApp(): bool
    {
        return $this->isInApp;
    }

    /**
     * @param bool $isInApp
     */
    public function setIsInApp(bool $isInApp): void
    {
        $this->isInApp = $isInApp;
    }

    /**
     * @return bool
     */
    public function hasSubscription(): bool
    {
        return $this->hasSubscription;
    }

    /**
     * @param bool $hasSubscription
     */
    public function setHasSubscription(bool $hasSubscription): void
    {
        $this->hasSubscription = $hasSubscription;
    }

    /**
     * @return bool
     */
    public function hasLicense(): bool
    {
        return $this->hasLicense;
    }

    /**
     * @param bool $hasLicense
     */
    public function setHasLicense(bool $hasLicense): void
    {
        $this->hasLicense = $hasLicense;
    }

    /**
     * @return bool
     */
    public function canBeUsed(): bool
    {
        return $this->canBeUsed;
    }

    /**
     * @param bool $canBeUsed
     */
    public function setCanBeUsed(bool $canBeUsed): void
    {
        $this->canBeUsed = $canBeUsed;
    }
}
