<?php declare(strict_types=1);

namespace JTL\News;

use DateTime;
use Illuminate\Support\Collection;

/**
 * Interface CategoryInterface
 * @package JTL\News
 */
interface CategoryInterface
{
    /**
     * @return ItemListInterface|Collection
     */
    public function getItems();

    /**
     * @param ItemListInterface|Collection $items
     */
    public function setItems($items): void;

    /**
     * @param int|null $idx
     * @return string
     */
    public function getName(int $idx = null): string;

    /**
     * @return string[]
     */
    public function getNames(): array;

    /**
     * @param string   $name
     * @param int|null $idx
     */
    public function setName(string $name, int $idx = null): void;

    /**
     * @param string[] $names
     */
    public function setNames(array $names): void;

    /**
     * @return string[]
     */
    public function getMetaTitles(): array;

    /**
     * @param int|null $idx
     * @return string
     */
    public function getMetaTitle(int $idx = null): string;

    /**
     * @param string   $metaTitle
     * @param int|null $idx
     */
    public function setMetaTitle(string $metaTitle, int $idx = null): void;

    /**#
     * @param string[] $metaTitles
     */
    public function setMetaTitles(array $metaTitles);

    /**
     * @param int|null $idx
     * @return string
     */
    public function getMetaKeyword(int $idx = null): string;

    /**
     * @return string[]
     */
    public function getMetaKeywords(): array;

    /**
     * @param string   $metaKeyword
     * @param int|null $idx
     */
    public function setMetaKeyword(string $metaKeyword, int $idx = null);

    /**
     * @param string[] $metaKeywords
     */
    public function setMetaKeywords(array $metaKeywords);

    /**
     * @param int|null $idx
     * @return string
     */
    public function getMetaDescription(int $idx = null): string;

    /**
     * @return string[]
     */
    public function getMetaDescriptions(): array;

    /**
     * @param string   $metaDescription
     * @param int|null $idx
     */
    public function setMetaDescription(string $metaDescription, int $idx = null): void;

    /**
     * @param string[] $metaDescriptions
     */
    public function setMetaDescriptions(array $metaDescriptions);

    /**
     * @param int|null $idx
     * @return string
     */
    public function getURL(int $idx = null): string;

    /**
     * @return array
     */
    public function getURLs(): array;

    /**
     * @param int|null $idx
     * @return string
     */
    public function getSEO(int $idx = null): string;

    /**
     * @return array
     */
    public function getSEOs(): array;

    /**
     * @return int
     */
    public function getID(): int;

    /**
     * @param int $id
     */
    public function setID(int $id): void;

    /**
     * @return int
     */
    public function getParentID(): int;

    /**
     * @param int $parentID
     */
    public function setParentID(int $parentID): void;

    /**
     * @param int|null $idx
     * @return int
     */
    public function getLanguageID(int $idx = null): int;

    /**
     * @return int[]
     */
    public function getLanguageIDs(): array;

    /**
     * @param int[] $languageIDs
     */
    public function setLanguageIDs(array $languageIDs): void;

    /**
     * @param int|null $idx
     * @return string
     */
    public function getLanguageCode(int $idx = null): string;

    /**
     * @return string[]
     */
    public function getLanguageCodes(): array;

    /**
     * @param string[] $languageCodes
     */
    public function setLanguageCodes(array $languageCodes): void;

    /**
     * @param int|null $idx
     * @return string
     */
    public function getDescription(int $idx = null): string;

    /**
     * @return string[]
     */
    public function getDescriptions(): array;

    /**
     * @param string[] $descriptions
     */
    public function setDescriptions(array $descriptions): void;

    /**
     * @param int|null $idx
     * @return string
     */
    public function getPreviewImage(int $idx = null): string;

    /**
     * @return string[]
     */
    public function getPreviewImages(): array;

    /**
     * @param string[] $previewImages
     */
    public function setPreviewImages(array $previewImages): void;

    /**
     * @return int
     */
    public function getSort(): int;

    /**
     * @param int $sort
     */
    public function setSort(int $sort): void;

    /**
     * @return bool
     */
    public function getIsActive(): bool;

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive): void;

    /**
     * @return DateTime
     */
    public function getDateLastModified(): DateTime;

    /**
     * @param DateTime $dateLastModified
     */
    public function setDateLastModified(DateTime $dateLastModified): void;

    /**
     * @return int
     */
    public function getLevel(): int;

    /**
     * @param int $level
     */
    public function setLevel(int $level): void;

    /**
     * @return Collection
     */
    public function getChildren(): Collection;

    /**
     * @param Category $child
     */
    public function addChild(Category $child): void;

    /**
     * @param Collection $children
     */
    public function setChildren(Collection $children): void;
}
