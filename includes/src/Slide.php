<?php

namespace JTL;

use JTL\DB\ReturnType;
use JTL\Helpers\Text;
use stdClass;

/**
 * Class Slide
 * @package JTL
 */
class Slide
{
    use MagicCompatibilityTrait;

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var int
     */
    private $sliderID = 0;

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var string
     */
    private $image = '';

    /**
     * @var string
     */
    private $text = '';

    /**
     * @var string
     */
    private $thumbnail = '';

    /**
     * @var string
     */
    private $link = '';

    /**
     * @var int
     */
    private $sort = 0;

    /**
     * @var string
     */
    private $absoluteImage = '';

    /**
     * @var string
     */
    private $absoluteThumbnail = '';

    /**
     * @var array
     */
    private static $mapping = [
        'kSlide'            => 'ID',
        'kSlider'           => 'SliderID',
        'cTitel'            => 'Title',
        'cBild'             => 'Image',
        'cText'             => 'Text',
        'cThumbnail'        => 'Thumbnail',
        'cLink'             => 'Link',
        'nSort'             => 'Sort',
        'cBildAbsolut'      => 'AbsoluteImage',
        'cThumbnailAbsolut' => 'AbsoluteThumbnail'
    ];

    /**
     *
     */
    private function __clone()
    {
    }

    /**
     * @param string $value
     * @return string|null
     */
    private function getMapping(string $value): ?string
    {
        return self::$mapping[$value] ?? null;
    }

    /**
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->load($id);
        }
    }

    /**
     * @param int $id
     * @return bool
     */
    public function load(int $id = 0): bool
    {
        if ($id > 0 || ((int)$this->id > 0)) {
            if ($id === 0) {
                $id = $this->id;
            }

            $slide = Shop::Container()->getDB()->select('tslide', 'kSlide', $id);

            if (\is_object($slide)) {
                $this->set($slide);

                return true;
            }
        }

        return false;
    }

    /**
     * @param stdClass $data
     * @return $this
     */
    public function map(stdClass $data): self
    {
        foreach (\get_object_vars($data) as $field => $value) {
            if (($mapping = $this->getMapping($field)) !== null) {
                $method = 'set' . $mapping;
                $this->$method($value);
            }
        }
        $this->setAbsoluteImagePaths();

        return $this;
    }

    /**
     * @param stdClass $data
     * @return $this
     */
    public function set(stdClass $data): self
    {
        foreach (\get_object_vars($data) as $field => $value) {
            if (($mapping = $this->getMapping($field)) !== null) {
                $method = 'set' . $mapping;
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function setAbsoluteImagePaths(): self
    {
        $basePath                = Shop::getImageBaseURL();
        $this->absoluteImage     = $basePath . $this->image;
        $this->absoluteThumbnail = $basePath . $this->thumbnail;

        return $this;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        if (!empty($this->image)) {
            if (Text::startsWith($this->image, 'Bilder/')) {
                $this->setThumbnail(\PFAD_MEDIAFILES . 'Bilder/.tmb/' . \basename($this->getThumbnail()));
            } else {
                $this->setThumbnail(\STORAGE_OPC . '.tmb/' . \basename($this->getThumbnail()));
            }
            $path = \parse_url(\Shop::getURL() . '/', \PHP_URL_PATH);
            if (Text::startsWith($this->image, $path)) {
                $this->image = \ltrim(\substr($this->image, \mb_strlen($path)), '/');
            }
        }

        return $this->id === null || $this->id === 0
            ? $this->append()
            : $this->update() > 0;
    }

    /**
     * @return int
     */
    private function update(): int
    {
        $slide = new stdClass();
        if (!empty($this->getThumbnail())) {
            $slide->cThumbnail = $this->getThumbnail();
        }
        $slide->kSlider = $this->getSliderID();
        $slide->cTitel  = $this->getTitle();
        $slide->cBild   = $this->getImage();
        $slide->nSort   = $this->getSort();
        $slide->cLink   = $this->getLink();

        return Shop::Container()->getDB()->update('tslide', 'kSlide', $this->getID(), $slide);
    }

    /**
     * @return bool
     */
    private function append(): bool
    {
        if (!empty($this->image)) {
            $slide = new stdClass();
            foreach (self::$mapping as $type => $methodName) {
                $method       = 'get' . $methodName;
                $slide->$type = $this->$method();
            }
            unset($slide->cBildAbsolut, $slide->cThumbnailAbsolut, $slide->kSlide);
            if ($this->sort === null) {
                $oSort        = Shop::Container()->getDB()->queryPrepared(
                    'SELECT nSort
                        FROM tslide
                        WHERE kSlider = :sliderID
                        ORDER BY nSort DESC LIMIT 1',
                    ['sliderID' => $this->sliderID],
                    ReturnType::SINGLE_OBJECT
                );
                $slide->nSort = (!\is_object($oSort) || (int)$oSort->nSort === 0) ? 1 : ($oSort->nSort + 1);
            }
            $id = Shop::Container()->getDB()->insert('tslide', $slide);
            if ($id > 0) {
                $this->id = $id;

                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        return (int)$this->id > 0 && Shop::Container()->getDB()->delete('tslide', 'kSlide', (int)$this->id) > 0;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int|string $id
     */
    public function setID($id): void
    {
        $this->id = (int)$id;
    }

    /**
     * @return int
     */
    public function getSliderID(): int
    {
        return $this->sliderID;
    }

    /**
     * @param int|string $sliderID
     */
    public function setSliderID($sliderID): void
    {
        $this->sliderID = (int)$sliderID;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     */
    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getThumbnail(): string
    {
        return $this->thumbnail;
    }

    /**
     * @param string $thumbnail
     */
    public function setThumbnail(string $thumbnail): void
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param int|string $sort
     */
    public function setSort($sort): void
    {
        $this->sort = (int)$sort;
    }

    /**
     * @return string
     */
    public function getAbsoluteImage(): string
    {
        return $this->absoluteImage;
    }

    /**
     * @param string $absoluteImage
     */
    public function setAbsoluteImage(string $absoluteImage): void
    {
        $this->absoluteImage = $absoluteImage;
    }

    /**
     * @return string
     */
    public function getAbsoluteThumbnail(): string
    {
        return $this->absoluteThumbnail;
    }

    /**
     * @param string $absoluteThumbnail
     */
    public function setAbsoluteThumbnail(string $absoluteThumbnail): void
    {
        $this->absoluteThumbnail = $absoluteThumbnail;
    }
}
