<?php declare(strict_types=1);

namespace JTL\Media\Image;

use Exception;
use Generator;
use JTL\DB\DbInterface;
use JTL\Media\Image;
use JTL\Media\IMedia;
use JTL\Media\MediaImageRequest;
use JTL\Shop;
use stdClass;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use function Functional\every;
use function Functional\filter;
use function Functional\map;
use function Functional\select;

/**
 * Class AbstractImage
 * @package JTL\Media\Image
 */
abstract class AbstractImage implements IMedia
{
    public const TYPE = '';

    /**
     * @var string
     */
    public const REGEX = '';

    /**
     * @var string
     */
    public const REGEX_ALLOWED_CHARS = 'a-zA-Z0-9 äööüÄÖÜß\$\-\_\.\+\!\*\\\'\(\)\,';

    /**
     * @var array
     */
    protected static $imageExtensions = ['jpg', 'jpeg', 'webp', 'gif', 'png', 'bmp'];

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * AbstractImage constructor.
     * @param DbInterface|null $db
     */
    public function __construct(DbInterface $db = null)
    {
        $this->db = $db ?? Shop::Container()->getDB();
    }

    /**
     * @inheritDoc
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @inheritDoc
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function handle(string $request)
    {
        try {
            $request      = '/' . \ltrim($request, '/');
            $mediaReq     = $this->create($request);
            $allowedNames = $this->getImageNames($mediaReq);
            if (\count($allowedNames) === 0) {
                throw new Exception('No such image id: ' . (int)$mediaReq->id);
            }

            $imgPath      = null;
            $matchFound   = false;
            $allowedFiles = [];
            foreach ($allowedNames as $allowedName) {
                $mediaReq->path   = $allowedName . '.' . $mediaReq->ext;
                $mediaReq->name   = $allowedName;
                $mediaReq->number = (int)$mediaReq->number;
                $imgPath          = static::getThumbByRequest($mediaReq);
                $allowedFiles[]   = $imgPath;
                if ('/' . $imgPath === $request) {
                    $matchFound = true;
                    break;
                }
            }
            if ($matchFound === false) {
                \header('Location: ' . Shop::getImageBaseURL() . $allowedFiles[0], true, 301);
                exit;
            }
            if ($imgPath === null || !\is_file(\PFAD_ROOT . $imgPath)) {
                Image::render($mediaReq, true);
            }
        } catch (Exception $e) {
            $display = \strtolower(\ini_get('display_errors'));
            if (\in_array($display, ['on', '1', 'true'], true)) {
                echo $e->getMessage();
            }
            \http_response_code(404);
        }
        exit;
    }

    /**
     * @inheritdoc
     */
    public static function getThumb(string $type, $id, $mixed, $size, int $number = 1, string $source = null): string
    {
        $req   = static::getRequest($type, $id, $mixed, $size, $number, $source);
        $thumb = $req->getThumb($size);
        if (!\file_exists(\PFAD_ROOT . $thumb) && (($raw = $req->getRaw()) === null || !\file_exists($raw))) {
            $thumb = \BILD_KEIN_ARTIKELBILD_VORHANDEN;
        }

        return $thumb;
    }

    /**
     * @inheritdoc
     */
    public static function getThumbByRequest(MediaImageRequest $req): string
    {
        $thumb = $req->getThumb($req->getSizeType());
        if (!\file_exists(\PFAD_ROOT . $thumb) && (($raw = $req->getRaw()) === null || !\file_exists($raw))) {
            $thumb = \BILD_KEIN_ARTIKELBILD_VORHANDEN;
        }

        return $thumb;
    }

    /**
     * @inheritdoc
     */
    public static function getRequest(
        string $type,
        $id,
        $mixed,
        string $size,
        int $number = 1,
        string $sourcePath = null
    ): MediaImageRequest {
        return MediaImageRequest::create([
            'size'       => $size,
            'id'         => $id,
            'type'       => $type,
            'number'     => $number,
            'name'       => static::getCustomName($mixed),
            'ext'        => static::getFileExtension($sourcePath),
            'path'       => $sourcePath,
            'sourcePath' => $sourcePath
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function getCustomName($mixed): string
    {
        return 'image';
    }

    /**
     * @inheritdoc
     */
    public static function isValid(string $request): bool
    {
        return self::parse($request) !== null;
    }

    /**
     * @param string $type
     * @param int    $id
     * @return stdClass|null
     */
    public static function getImageStmt(string $type, int $id): ?stdClass
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getImageNames(MediaImageRequest $req): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getPathByID($id, int $number = null): ?string
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \PFAD_MEDIA_IMAGE_STORAGE;
    }

    /**
     * @inheritdoc
     */
    public function getStats(bool $filesize = false): StatsItem
    {
        $result      = new StatsItem();
        $totalImages = $this->getTotalImageCount();
        $offset      = 0;
        do {
            foreach ($this->getAllImages($offset, \MAX_IMAGES_PER_STEP) as $image) {
                if ($image === null) {
                    continue;
                }
                $raw = $image->getRaw();
                $result->addItem();
                if ($raw !== null && \file_exists($raw)) {
                    foreach (Image::getAllSizes() as $size) {
                        $thumb = $image->getThumb($size, true);
                        if (!\file_exists($thumb)) {
                            continue;
                        }
                        $result->addGeneratedItem($size);
                        if ($filesize === true) {
                            $bytes = \filesize($thumb);
                            if ($bytes === false) {
                                $bytes = 0;
                            }
                            $result->addGeneratedSizeItem($size, $bytes);
                        }
                    }
                } else {
                    $result->addCorrupted();
                }
            }
            $offset += \MAX_IMAGES_PER_STEP;
        } while ($offset < $totalImages);

        return $result;
    }

    /**
     * @param int|null $offset
     * @param int|null $limit
     * @return string
     */
    protected static function getLimitStatement(int $offset = null, int $limit = null): string
    {
        if ($limit === null) {
            return '';
        }
        $limitStmt = ' LIMIT ';
        if ($offset !== null) {
            $limitStmt .= $offset . ', ';
        }
        $limitStmt .= $limit;

        return $limitStmt;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function getImages(bool $notCached = false, int $offset = null, int $limit = null): array
    {
        $requests = [];
        foreach ($this->getAllImages($offset, $limit) as $req) {
            if ($notCached && $this->isCached($req)) {
                continue;
            }
            $requests[] = $req;
        }

        return $requests;
    }

    /**
     * @inheritdoc
     */
    public function getAllImages(int $offset = null, int $limit = null): Generator
    {
        yield from [];
    }

    /**
     * @inheritdoc
     */
    public function getUncachedImageCount(): int
    {
        return \count(select($this->getAllImages(), function (MediaImageRequest $e) {
            return !$this->isCached($e) && ($file = $e->getRaw()) !== null && \file_exists($file);
        }));
    }

    /**
     * @inheritDoc
     */
    public function cacheImage(MediaImageRequest $req, bool $overwrite = false): array
    {
        $result     = [];
        $rawPath    = $req->getRaw();
        $extensions = [$req->getExt() === 'auto' ? 'jpg' : $req->getExt()];
        if (Image::hasWebPSupport()) {
            $extensions[] = 'webp';
        }
        if ($overwrite === true) {
            static::clearCache($req->getID());
        }
        foreach ($extensions as $extension) {
            $req->setExt($extension);
            foreach (Image::getAllSizes() as $size) {
                $res = (object)[
                    'success'    => true,
                    'error'      => null,
                    'renderTime' => 0,
                    'cached'     => false,
                ];
                try {
                    $req->setSizeType($size);
                    $thumbPath   = $req->getThumb(null, true);
                    $res->cached = \is_file($thumbPath);
                    if ($res->cached === false) {
                        $renderStart = \microtime(true);
                        if ($rawPath !== null && !\is_file($rawPath)) {
                            throw new Exception(\sprintf('Image source "%s" does not exist', $rawPath));
                        }
                        Image::render($req);
                        $res->renderTime = (\microtime(true) - $renderStart) * 1000;
                    }
                } catch (Exception $e) {
                    $res->success = false;
                    $res->error   = $e->getMessage();
                }
                $result[$size] = $res;
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public static function clearCache($id = null): bool
    {
        $baseDir     = \realpath(\PFAD_ROOT . MediaImageRequest::getCachePath(static::getType()));
        $ids         = \is_array($id) ? $id : [$id];
        $directories = filter(
            map($ids, static function ($e) use ($baseDir) {
                return $e === null ? $baseDir : \realpath($baseDir . '/' . $e);
            }),
            static function ($e) use ($baseDir) {
                return $e !== false && \strpos($e, $baseDir) === 0;
            }
        );
        try {
            $res    = true;
            $finder = new Finder();
            $finder->ignoreUnreadableDirs()->in($directories);
            foreach ($finder->files() as $file) {
                /** @var SplFileInfo $file */
                $loop = \unlink($file->getRealPath());
                $res  = $res && $loop;
            }
            foreach (\array_reverse(\iterator_to_array($finder->directories(), true)) as $directory) {
                /** @var SplFileInfo $directory */
                $loop = \rmdir($directory->getRealPath());
                $res  = $res && $loop;
            }
            foreach ($directories as $directory) {
                if ($directory !== $baseDir) {
                    $loop = \rmdir($directory);
                    $res  = $res && $loop;
                }
            }
        } catch (Exception $e) {
            $res = false;
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function imageIsUsed(string $path): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getTotalImageCount(): int
    {
        return 0;
    }

    /**
     * @param MediaImageRequest $req
     * @return bool
     */
    protected function isCached(MediaImageRequest $req): bool
    {
        return every(Image::getAllSizes(), static function ($e) use ($req) {
            return \file_exists($req->getThumb($e, true));
        });
    }

    /**
     * @param string|null $filePath
     * @return string
     */
    protected static function getFileExtension(string $filePath = null): string
    {
        $config = Image::getSettings()['format'];

        return $config === 'auto' && $filePath !== null
            ? \pathinfo($filePath)['extension'] ?? 'jpg'
            : $config;
    }

    /**
     * @param string|null $request
     * @return array|null
     */
    protected static function parse(?string $request): ?array
    {
        if (!\is_string($request) || \mb_strlen($request) === 0) {
            return null;
        }
        if (\mb_strpos($request, '/') === 0) {
            $request = \mb_substr($request, 1);
        }

        return \preg_match(static::REGEX, $request, $matches)
            ? \array_intersect_key($matches, \array_flip(\array_filter(\array_keys($matches), '\is_string')))
            : null;
    }

    /**
     * @param string $imageUrl
     * @return MediaImageRequest
     */
    public static function toRequest(string $imageUrl): MediaImageRequest
    {
        return (new static())->create($imageUrl);
    }

    /**
     * @param string|null $request
     * @return MediaImageRequest
     */
    protected function create(?string $request): MediaImageRequest
    {
        return MediaImageRequest::create(self::parse($request));
    }

    /**
     * @return string
     */
    public static function getType(): string
    {
        return static::TYPE;
    }
}
