<?php declare(strict_types=1);

namespace JTL\Sitemap\Items;

use JTL\Language\LanguageModel;
use function Functional\first;

/**
 * Class AbstractItem
 * @package JTL\Sitemap\Items
 */
abstract class AbstractItem implements ItemInterface
{
    /**
     * @var string|null
     */
    protected $lastModificationTime;

    /**
     * @var string|null
     */
    protected $changeFreq;

    /**
     * @var string|null
     */
    protected $image;

    /**
     * @var string
     */
    protected $location;

    /**
     * @var string|null
     */
    protected $priority;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $baseURL;

    /**
     * @var string
     */
    protected $baseImageURL;

    /**
     * @var int
     */
    protected $languageID;

    /**
     * @var string
     */
    protected $languageCode;

    /**
     * @var string
     */
    protected $languageCode639;

    /**
     * @var int
     */
    protected $primaryKeyID = 0;

    /**
     * AbstractItem constructor.
     * @param array  $config
     * @param string $baseURL
     * @param string $baseImageURL
     */
    public function __construct(array $config, string $baseURL, string $baseImageURL)
    {
        $this->config       = $config;
        $this->baseURL      = $baseURL;
        $this->baseImageURL = $baseImageURL;
    }

    /**
     * @inheritdoc
     */
    public function getChangeFreq(): ?string
    {
        return $this->changeFreq;
    }

    /**
     * @inheritdoc
     */
    public function setChangeFreq(?string $changeFreq): void
    {
        $this->changeFreq = $changeFreq;
    }

    /**
     * @inheritdoc
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @inheritdoc
     */
    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    /**
     * @inheritdoc
     */
    public function getLastModificationTime(): ?string
    {
        return $this->lastModificationTime;
    }

    /**
     * @inheritdoc
     */
    public function setLastModificationTime(?string $time): void
    {
        $this->lastModificationTime = $time;
    }

    /**
     * @inheritdoc
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @inheritdoc
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * @inheritdoc
     */
    public function getPriority(): ?string
    {
        return $this->priority;
    }

    /**
     * @inheritdoc
     */
    public function setPriority(?string $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageID(int $langID): void
    {
        $this->languageID = $langID;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageID(): ?int
    {
        return $this->languageID;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageCode(?string $langCode): void
    {
        $this->languageCode = $langCode;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageCode639(?string $langCode): void
    {
        $this->languageCode639 = $langCode;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageCode639(): ?string
    {
        return $this->languageCode639;
    }

    /**
     * @param LanguageModel[] $languages
     * @param int   $currentLangID
     */
    public function setLanguageData(array $languages, int $currentLangID): void
    {
        $lang = first($languages, static function ($e) use ($currentLangID) {
            return $e->kSprache === $currentLangID;
        });
        /** @var LanguageModel $lang */
        if ($lang !== null) {
            $this->setLanguageCode($lang->iso);
            $this->setLanguageID($lang->id);
            $this->setLanguageCode639($lang->getIso639());
        }
    }

    /**
     * @inheritdoc
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKeyID(): int
    {
        return $this->primaryKeyID;
    }

    /**
     * @inheritdoc
     */
    public function setPrimaryKeyID(int $id): void
    {
        $this->primaryKeyID = $id;
    }

    /**
     * @inheritdoc
     */
    public function generateImage(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function generateLocation(): void
    {
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        $res           = \get_object_vars($this);
        $res['config'] = '*truncated*';

        return $res;
    }
}
