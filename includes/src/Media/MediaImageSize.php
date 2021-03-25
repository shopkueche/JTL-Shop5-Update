<?php declare(strict_types=1);

namespace JTL\Media;

/**
 * Class MediaImageSize
 * @package JTL\Media
 */
class MediaImageSize
{
    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var string
     */
    private $size;

    /**
     * @var string
     */
    private $imageType;

    /**
     * MediaImageSize constructor.
     * @param string $size
     * @param string $imageType
     */
    public function __construct(string $size, string $imageType = Image::TYPE_PRODUCT)
    {
        $this->size      = $size;
        $this->imageType = $imageType;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        if ($this->width === null) {
            $this->width = $this->getConfiguredSize('width');
        }

        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        if ($this->height === null) {
            $this->height = $this->getConfiguredSize('height');
        }

        return $this->height;
    }

    /**
     * @return string
     */
    public function getImageType(): string
    {
        return $this->imageType;
    }

    /**
     * @return string
     */
    public function getSize(): string
    {
        return $this->size;
    }

    /**
     * @param string $dimension
     * @return mixed
     */
    public function getConfiguredSize(string $dimension)
    {
        $settings = Image::getSettings();

        return (int)($settings[$this->imageType ?? 'size'][$this->size][$dimension] ?? -1);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return \sprintf('%s', $this->getSize());
    }
}
