<?php declare(strict_types=1);

namespace JTL\Plugin\Admin;

use DateTime;
use InvalidArgumentException;
use JTL\Helpers\GeneralObject;
use JTL\Mapper\PluginValidation;
use JTL\Plugin\InstallCode;
use JTL\Plugin\PluginInterface;
use JTL\Plugin\State;
use JTLShop\SemVer\Version;

/**
 * Class ListingItem
 * @package JTL\Plugin\Admin
 */
class ListingItem
{
    /**
     * @var bool
     */
    private $isShop4Compatible = false;

    /**
     * @var bool
     */
    private $isShop5Compatible = false;

    /**
     * @var string
     */
    private $path = '';

    /**
     * @var string
     */
    private $dir = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var Version
     */
    private $version;

    /**
     * @var Version
     */
    private $minShopVersion;

    /**
     * @var Version
     */
    private $maxShopVersion;

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var string
     */
    private $author = '';

    /**
     * @var string
     */
    private $icon = '';

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var string
     */
    private $pluginID = '';

    /**
     * @var int
     */
    private $errorCode = 0;

    /**
     * @var string
     */
    private $errorMessage = '';

    /**
     * @var bool
     */
    private $hasError = false;

    /**
     * @var bool
     */
    private $available = false;

    /**
     * @var bool
     */
    private $installed = false;

    /**
     * @var int
     */
    private $state = State::NONE;

    /**
     * @var bool
     */
    private $isLegacy = true;

    /**
     * @var bool|Version
     */
    private $updateAvailable = false;

    /**
     * @var bool
     */
    private $hasLicenseCheck = false;

    /**
     * @var string
     */
    private $license = '';

    /**
     * @var string|null
     */
    private $updateFromDir;

    /**
     * @var DateTime|null
     */
    private $dateInstalled;

    /**
     * @var int
     */
    private $langVarCount = 0;

    /**
     * @var int
     */
    private $linkCount = 0;

    /**
     * @var int
     */
    private $optionsCount = 0;

    /**
     * @var string|null
     */
    private $readmeMD;

    /**
     * @var string|null
     */
    private $licenseMD;

    /**
     * @param array $xml
     * @return ListingItem
     */
    public function parseXML(array $xml): self
    {
        $node                 = null;
        $this->name           = $xml['cVerzeichnis'];
        $this->dir            = $xml['cVerzeichnis'];
        $this->minShopVersion = Version::parse('5.0.0');
        $this->maxShopVersion = Version::parse('0.0.0');
        if (GeneralObject::isCountable('jtlshopplugin', $xml)) {
            $node                    = $xml['jtlshopplugin'][0];
            $this->isShop5Compatible = true;
        } elseif (GeneralObject::isCountable('jtlshop3plugin', $xml)) {
            $node = $xml['jtlshop3plugin'][0];
        }
        if ($node !== null) {
            if ($this->isShop5Compatible) {
                if (!isset($node['Version'])) {
                    return $this->fail();
                }
            } elseif (!isset($node['Install'][0]['Version'])) {
                return $this->fail();
            }
            if (!isset($node['Name'])) {
                return $this->fail();
            }
            $this->name        = $node['Name'] ?? '';
            $this->description = $node['Description'] ?? '';
            $this->author      = $node['Author'] ?? '';
            $this->pluginID    = $node['PluginID'] ?? '';
            $this->icon        = $node['Icon'] ?? null;
            if (isset($node['Install'][0]['Version']) && \is_array($node['Install'][0]['Version'])) {
                $lastVersion = \count($node['Install'][0]['Version']) / 2 - 1;
                $version     = (int)($node['Install'][0]['Version'][$lastVersion . ' attr']['nr'] ?? 0);
            } else {
                $version = $node['Version'];
            }
            try {
                $this->version        = Version::parse($version);
                $this->minShopVersion = Version::parse($node['MinShopVersion'] ?? $node['ShopVersion'] ?? '5.0.0');
                $this->maxShopVersion = Version::parse($node['MaxShopVersion'] ?? '0.0.0');
            } catch (InvalidArgumentException $e) {
            }
        }
        if ($xml['cFehlercode'] !== InstallCode::OK) {
            $mapper             = new PluginValidation();
            $this->hasError     = true;
            $this->errorCode    = $xml['cFehlercode'];
            $this->errorMessage = $mapper->map($xml['cFehlercode'], $this->getPluginID());

            return $this->fail();
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function fail(): self
    {
        $this->version = $this->version ?? Version::parse('0.0.0');

        return $this;
    }

    /**
     * @param PluginInterface $plugin
     * @return $this
     */
    public function loadFromPlugin(PluginInterface $plugin): self
    {
        $meta = $plugin->getMeta();
        $this->setName($meta->getName());
        $this->setDescription($meta->getDescription());
        $this->setAuthor($meta->getAuthor());
        $this->setID($plugin->getID());
        $this->setPluginID($plugin->getPluginID());
        $this->setPath($plugin->getPaths()->getVersionedPath());
        $this->setDir($plugin->getPaths()->getBaseDir());
        $this->setIsLegacy($plugin->isLegacy());
        $this->setIcon($meta->getIcon() ?? '');
        $this->setVersion($meta->getSemVer());
        $this->setState($plugin->getState());
        $this->setDateInstalled($meta->getDateInstalled());
        $this->setLangVarCount($plugin->getLocalization()->getLangVars()->count());
        $this->setLinkCount($plugin->getLinks()->getLinks()->count());
        $this->setHasLicenseCheck($plugin->getLicense()->hasLicenseCheck());
        $this->setOptionsCount($plugin->getConfig()->getOptions()->count()
            + $plugin->getAdminMenu()->getItems()->count());
        $this->setReadmeMD($meta->getReadmeMD());
        $this->setLicenseMD($meta->getLicenseMD());
        $this->setIsShop5Compatible(!$this->isLegacy());
        $this->setLicenseKey($plugin->getLicense()->getKey());
        $this->setUpdateAvailable($plugin->getMeta()->getUpdateAvailable());
        $this->setMinShopVersion(Version::parse('0.0.0'));
        $this->setMaxShopVersion(Version::parse('0.0.0'));

        return $this;
    }

    /**
     * @return bool
     */
    public function isShop4Compatible(): bool
    {
        return $this->isShop4Compatible;
    }

    /**
     * @param bool $isShop4Compatible
     */
    public function setIsShop4Compatible(bool $isShop4Compatible): void
    {
        $this->isShop4Compatible = $isShop4Compatible;
    }

    /**
     * @return bool
     */
    public function isShop5Compatible(): bool
    {
        return $this->isShop5Compatible;
    }

    /**
     * @param bool $isShop5Compatible
     */
    public function setIsShop5Compatible(bool $isShop5Compatible): void
    {
        $this->isShop5Compatible = $isShop5Compatible;
    }

    /**
     * @return string
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * @param string $dir
     */
    public function setDir(string $dir): void
    {
        $this->dir = $dir;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
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
     * @return Version
     */
    public function getVersion(): Version
    {
        return $this->version;
    }

    /**
     * @param Version $version
     */
    public function setVersion(Version $version): void
    {
        $this->version = $version;
    }

    /**
     * @return Version
     */
    public function getMinShopVersion(): Version
    {
        return $this->minShopVersion;
    }

    /**
     * @param Version $minShopVersion
     */
    public function setMinShopVersion(Version $minShopVersion): void
    {
        $this->minShopVersion = $minShopVersion;
    }

    /**
     * @return Version
     */
    public function getMaxShopVersion(): Version
    {
        return $this->maxShopVersion;
    }

    /**
     * @param Version $maxShopVersion
     */
    public function setMaxShopVersion(Version $maxShopVersion): void
    {
        $this->maxShopVersion = $maxShopVersion;
    }

    /**
     * @return string
     */
    public function displayVersionRange(): string
    {
        $min = null;
        $max = null;
        if ($this->minShopVersion !== null && $this->minShopVersion->greaterThan('0.0.0')) {
            $min = (string)$this->minShopVersion;
        }
        if ($this->maxShopVersion !== null && $this->maxShopVersion->greaterThan('0.0.0')) {
            $max = (string)$this->maxShopVersion;
        }
        if ($min === null && $max !== null) {
            return '<= ' . $max;
        }
        if ($min !== null && $max === null) {
            return '>= ' . $min;
        }
        if ($min !== null && $max !== null) {
            return $min === $max ? $min : $min . ' &dash; ' . $max;
        }

        return '?';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @return string
     */
    public function getPluginID(): string
    {
        return $this->pluginID;
    }

    /**
     * @param string $pluginID
     */
    public function setPluginID(string $pluginID): void
    {
        $this->pluginID = $pluginID;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * @param int $errorCode
     */
    public function setErrorCode(int $errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return bool
     */
    public function isHasError(): bool
    {
        return $this->hasError;
    }

    /**
     * @param bool $hasError
     */
    public function setHasError(bool $hasError): void
    {
        $this->hasError = $hasError;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @param bool $available
     */
    public function setAvailable(bool $available): void
    {
        $this->available = $available;
    }

    /**
     * @return bool
     */
    public function isInstalled(): bool
    {
        return $this->installed;
    }

    /**
     * @param bool $installed
     */
    public function setInstalled(bool $installed): void
    {
        $this->installed = $installed;
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
     * @return bool
     */
    public function isLegacy(): bool
    {
        return $this->isLegacy;
    }

    /**
     * @param bool $isLegacy
     */
    public function setIsLegacy(bool $isLegacy): void
    {
        $this->isLegacy = $isLegacy;
    }

    /**
     * @return bool|Version
     */
    public function isUpdateAvailable()
    {
        return $this->updateAvailable;
    }

    /**
     * @param bool|Version $updateAvailable
     */
    public function setUpdateAvailable($updateAvailable): void
    {
        $this->updateAvailable = $updateAvailable;
    }

    /**
     * @return string|null
     */
    public function getUpdateFromDir(): ?string
    {
        return $this->updateFromDir;
    }

    /**
     * @param string|null $updateFromDir
     */
    public function setUpdateFromDir(?string $updateFromDir): void
    {
        $this->updateFromDir = $updateFromDir;
    }

    /**
     * @return DateTime|null
     */
    public function getDateInstalled(): ?DateTime
    {
        return $this->dateInstalled;
    }

    /**
     * @param DateTime|null $dateInstalled
     */
    public function setDateInstalled(?DateTime $dateInstalled): void
    {
        $this->dateInstalled = $dateInstalled;
    }

    /**
     * @return int
     */
    public function getLangVarCount(): int
    {
        return $this->langVarCount;
    }

    /**
     * @param int $langVarCount
     */
    public function setLangVarCount(int $langVarCount): void
    {
        $this->langVarCount = $langVarCount;
    }

    /**
     * @return bool
     */
    public function hasLicenseCheck(): bool
    {
        return $this->hasLicenseCheck;
    }

    /**
     * @param bool $hasLicenseCheck
     */
    public function setHasLicenseCheck(bool $hasLicenseCheck): void
    {
        $this->hasLicenseCheck = $hasLicenseCheck;
    }

    /**
     * @return string
     */
    public function getLicenseKey(): string
    {
        return $this->license;
    }

    /**
     * @param string $license
     */
    public function setLicenseKey(string $license): void
    {
        $this->license = $license;
    }

    /**
     * @return int
     */
    public function getLinkCount(): int
    {
        return $this->linkCount;
    }

    /**
     * @param int $linkCount
     */
    public function setLinkCount(int $linkCount): void
    {
        $this->linkCount = $linkCount;
    }

    /**
     * @return int
     */
    public function getOptionsCount(): int
    {
        return $this->optionsCount;
    }

    /**
     * @param int $optionsCount
     */
    public function setOptionsCount(int $optionsCount): void
    {
        $this->optionsCount = $optionsCount;
    }

    /**
     * @return string|null
     */
    public function getReadmeMD(): ?string
    {
        return $this->readmeMD;
    }

    /**
     * @param string|null $readmeMD
     */
    public function setReadmeMD(?string $readmeMD): void
    {
        $this->readmeMD = $readmeMD;
    }

    /**
     * @return string|null
     */
    public function getLicenseMD(): ?string
    {
        return $this->licenseMD;
    }

    /**
     * @param string|null $licenseMD
     */
    public function setLicenseMD(?string $licenseMD): void
    {
        $this->licenseMD = $licenseMD;
    }
}
