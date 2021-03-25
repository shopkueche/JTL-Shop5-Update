<?php declare(strict_types=1);

namespace JTL\Sitemap\Items;

/**
 * Interface ItemInterface
 * @package JTL\Sitemap\Items
 */
interface ItemInterface
{
    /**
     * @return string|null
     */
    public function getLastModificationTime(): ?string;

    /**
     * @param string|null $time
     */
    public function setLastModificationTime(?string $time): void;

    /**
     * @return void
     */
    public function generateImage(): void;

    /**
     * @return string|null
     */
    public function getImage(): ?string;

    /**
     * @param string|null $image
     */
    public function setImage(?string $image): void;

    /**
     * @return void
     */
    public function generateLocation(): void;

    /**
     * @return string
     */
    public function getLocation(): string;

    /**
     * @param string $location
     */
    public function setLocation(string $location): void;

    /**
     * @return string|null
     */
    public function getChangeFreq(): ?string;

    /**
     * @param string|null $changeFreq
     */
    public function setChangeFreq(?string $changeFreq): void;

    /**
     * @return string|null
     */
    public function getPriority(): ?string;

    /**
     * @param string|null $priority
     */
    public function setPriority(?string $priority): void;

    /**
     * @param int $langID
     */
    public function setLanguageID(int $langID): void;

    /**
     * @return int|null
     */
    public function getLanguageID(): ?int;

    /**
     * @param string|null $langCode
     */
    public function setLanguageCode(?string $langCode): void;

    /**
     * @return string|null
     */
    public function getLanguageCode(): ?string;

    /**
     * @param string|null $langCode
     */
    public function setLanguageCode639(?string $langCode): void;

    /**
     * @return null|string
     */
    public function getLanguageCode639(): ?string;

    /**
     * @param array $languages
     * @param int   $currentLangID
     */
    public function setLanguageData(array $languages, int $currentLangID): void;

    /**
     * @param mixed $data
     */
    public function setData($data): void;

    /**
     * @return int
     */
    public function getPrimaryKeyID(): int;

    /**
     * @param int $id
     */
    public function setPrimaryKeyID(int $id): void;

    /**
     * @param mixed $data
     * @param array $languages
     */
    public function generateData($data, array $languages): void;
}
