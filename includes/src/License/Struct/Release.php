<?php declare(strict_types=1);

namespace JTL\License\Struct;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use JTLShop\SemVer\Version;
use stdClass;

/**
 * Class Release
 * @package JTL\License
 */
class Release
{
    public const TYPE_SECURITY = 'security';

    public const TYPE_FEATURE = 'feature';

    public const TYPE_BUGFIX = 'bugfix';

    /**
     * @var Version
     */
    private $version;

    /**
     * @var string
     */
    private $type;

    /**
     * @var DateTime
     */
    private $releaseDate;

    /**
     * @var string
     */
    private $shortDescription;

    /**
     * @var string
     */
    private $downloadUrl;

    /**
     * @var string - sha1 checksum
     */
    private $checksum;

    /**
     * @var bool
     */
    private $includesSecurityFixes = false;

    /**
     * Release constructor.
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
        $this->setVersion(Version::parse($json->version));
        $this->setType($json->type);
        $this->setReleaseDate($json->release_date);
        $this->setShortDescription($json->short_description);
        $this->setDownloadURL($json->download_url);
        $this->setChecksum($json->checksum ?? '');
        $this->setIncludesSecurityFixes($json->includes_security_fixes ?? false);
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
     * @return DateTime
     */
    public function getReleaseDate(): DateTime
    {
        return $this->releaseDate;
    }

    /**
     * @param DateTime|string $releaseDate
     * @throws \Exception
     */
    public function setReleaseDate($releaseDate): void
    {
        $this->releaseDate = \is_a($releaseDate, DateTime::class)
            ? $releaseDate
            : Carbon::createFromTimeString($releaseDate, 'UTC')
                ->toDateTime()
                ->setTimezone(new DateTimeZone(\SHOP_TIMEZONE));
    }

    /**
     * @return string
     */
    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    /**
     * @param string $shortDescription
     */
    public function setShortDescription(string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * @return string
     */
    public function getDownloadURL(): string
    {
        return $this->downloadUrl;
    }

    /**
     * @param string $downloadURL
     */
    public function setDownloadURL(string $downloadURL): void
    {
        $this->downloadUrl = $downloadURL;
    }

    /**
     * @return string
     */
    public function getChecksum(): string
    {
        return $this->checksum;
    }

    /**
     * @param string $checksum
     */
    public function setChecksum(string $checksum): void
    {
        $this->checksum = $checksum;
    }

    /**
     * @return bool
     */
    public function includesSecurityFixes(): bool
    {
        return $this->includesSecurityFixes;
    }

    /**
     * @param bool $includesSecurityFixes
     */
    public function setIncludesSecurityFixes(bool $includesSecurityFixes): void
    {
        $this->includesSecurityFixes = $includesSecurityFixes;
    }
}
