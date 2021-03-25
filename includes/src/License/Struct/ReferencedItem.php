<?php declare(strict_types=1);

namespace JTL\License\Struct;

use JTLShop\SemVer\Version;

/**
 * Class ReferencedItem
 * @package JTL\License\Struct
 */
abstract class ReferencedItem implements ReferencedItemInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var bool
     */
    private $installed = false;

    /**
     * @var Version|null
     */
    private $installedVersion;

    /**
     * @var Version|null
     */
    private $maxInstallableVersion;

    /**
     * @var bool
     */
    private $hasUpdate = false;

    /**
     * @var bool
     */
    private $active = false;

    /**
     * @var int
     */
    private $internalID = 0;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var string|null
     */
    private $dateInstalled;

    /**
     * @inheritDoc
     */
    public function getID(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setID(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function isInstalled(): bool
    {
        return $this->installed;
    }

    /**
     * @inheritDoc
     */
    public function setInstalled(bool $installed): void
    {
        $this->installed = $installed;
    }

    /**
     * @inheritDoc
     */
    public function getInstalledVersion(): ?Version
    {
        return $this->installedVersion;
    }

    /**
     * @inheritDoc
     */
    public function setInstalledVersion(?Version $installedVersion): void
    {
        $this->installedVersion = $installedVersion;
    }

    /**
     * @inheritDoc
     */
    public function getMaxInstallableVersion(): ?Version
    {
        return $this->maxInstallableVersion;
    }

    /**
     * @inheritDoc
     */
    public function setMaxInstallableVersion(?Version $maxInstallableVersion): void
    {
        $this->maxInstallableVersion = $maxInstallableVersion;
    }

    /**
     * @inheritDoc
     */
    public function hasUpdate(): bool
    {
        return $this->hasUpdate;
    }

    /**
     * @inheritDoc
     */
    public function setHasUpdate(bool $hasUpdate): void
    {
        $this->hasUpdate = $hasUpdate;
    }

    /**
     * @inheritDoc
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @inheritDoc
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @inheritDoc
     */
    public function getInternalID(): int
    {
        return $this->internalID;
    }

    /**
     * @inheritDoc
     */
    public function setInternalID(int $internalID): void
    {
        $this->internalID = $internalID;
    }

    /**
     * @inheritDoc
     */
    public function getDateInstalled(): ?string
    {
        return $this->dateInstalled;
    }

    /**
     * @inheritDoc
     */
    public function setDateInstalled(?string $dateInstalled): void
    {
        $this->dateInstalled = $dateInstalled;
    }

    /**
     * @inheritDoc
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * @inheritDoc
     */
    public function setInitialized(bool $initialized): void
    {
        $this->initialized = $initialized;
    }
}
