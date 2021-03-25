<?php declare(strict_types=1);

namespace JTL\Media\Image;

use JsonSerializable;
use JTL\Media\Image;

/**
 * Class StatsItem
 * @package JTL\Media\Image
 */
class StatsItem implements JsonSerializable
{
    /**
     * @var int
     */
    private $total = 0;

    /**
     * @var int
     */
    private $totalSize = 0;

    /**
     * @var int
     */
    private $corrupted = 0;

    /**
     * @var array
     */
    private $generated = [
        Image::SIZE_XS       => 0,
        Image::SIZE_SM       => 0,
        Image::SIZE_MD       => 0,
        Image::SIZE_LG       => 0,
        Image::SIZE_XL       => 0,
    ];

    /**
     * @var array
     */
    private $generatedSize = [
        Image::SIZE_XS       => 0,
        Image::SIZE_SM       => 0,
        Image::SIZE_MD       => 0,
        Image::SIZE_LG       => 0,
        Image::SIZE_XL       => 0,
    ];

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param int $total
     */
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    /**
     * @return int
     */
    public function addItem(): int
    {
        return ++$this->total;
    }

    /**
     * @return int
     */
    public function getTotalSize(): int
    {
        return $this->totalSize;
    }

    /**
     * @param int $totalSize
     */
    public function setTotalSize(int $totalSize): void
    {
        $this->totalSize = $totalSize;
    }

    /**
     * @return int
     */
    public function getCorrupted(): int
    {
        return $this->corrupted;
    }

    /**
     * @param int $corrupted
     */
    public function setCorrupted(int $corrupted): void
    {
        $this->corrupted = $corrupted;
    }

    /**
     * @return int
     */
    public function addCorrupted(): int
    {
        return ++$this->corrupted;
    }

    /**
     * @return array
     */
    public function getGenerated(): array
    {
        return $this->generated;
    }

    /**
     * @param string $size
     * @return int
     */
    public function getGeneratedBySize(string $size): int
    {
        return $this->generated[$size];
    }

    /**
     * @param array $generated
     */
    public function setGenerated(array $generated): void
    {
        $this->generated = $generated;
    }

    /**
     * @param string $size
     * @return int
     */
    public function addGeneratedItem(string $size): int
    {
        return ++$this->generated[$size];
    }

    /**
     * @return array
     */
    public function getGeneratedSize(): array
    {
        return $this->generatedSize;
    }

    /**
     * @param array $generatedSize
     */
    public function setGeneratedSize(array $generatedSize): void
    {
        $this->generatedSize = $generatedSize;
    }

    /**
     * @param string $size
     * @param int    $bytes
     * @return int
     */
    public function addGeneratedSizeItem(string $size, int $bytes): int
    {
        $this->generatedSize[$size] += $bytes;
        $this->totalSize            += $bytes;

        return $this->generatedSize[$size];
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return \get_object_vars($this);
    }
}
