<?php declare(strict_types=1);

namespace JTL\Media\Image;

use Generator;
use JTL\DB\ReturnType;
use JTL\Media\Image;
use JTL\Media\MediaImageRequest;
use PDO;
use stdClass;

/**
 * Class Manufacturer
 * @package JTL\Media
 */
class Manufacturer extends AbstractImage
{
    public const TYPE = Image::TYPE_MANUFACTURER;

    /**
     * @var string
     */
    public const REGEX = '/^media\/image'
    . '\/(?P<type>manufacturer)'
    . '\/(?P<id>\d+)'
    . '\/(?P<size>xs|sm|md|lg|xl)'
    . '\/(?P<name>[' . self::REGEX_ALLOWED_CHARS . ']+)'
    . '(?:(?:~(?P<number>\d+))?)\.(?P<ext>jpg|jpeg|png|gif|webp)$/';

    /**
     * @inheritdoc
     */
    public static function getImageStmt(string $type, int $id): ?stdClass
    {
        return (object)[
            'stmt' => 'SELECT cBildpfad, 0 AS number 
                           FROM thersteller 
                           WHERE kHersteller = :kHersteller',
            'bind' => ['kHersteller' => $id]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getImageNames(MediaImageRequest $req): array
    {
        return $this->db->queryPrepared(
            'SELECT kHersteller, cName, cSeo AS seoPath, cSeo AS originalSeo, cBildpfad AS path
                FROM thersteller
                WHERE kHersteller = :mid',
            ['mid' => $req->getID()],
            ReturnType::COLLECTION
        )->each(static function ($item, $key) use ($req) {
            if ($key === 0 && !empty($item->path)) {
                $req->setSourcePath($item->path);
            }
            $item->imageName = self::getCustomName($item);
        })->pluck('imageName')->toArray();
    }

    /**
     * @inheritdoc
     */
    public static function getCustomName($mixed): string
    {
        switch (Image::getSettings()['naming'][Image::TYPE_MANUFACTURER]) {
            case 2:
                $result = $mixed->path ?? $mixed->cBildpfad ?? null;
                if ($result !== null) {
                    $result = \pathinfo($result)['filename'];
                }
                break;
            case 1:
                $result = $mixed->originalSeo ?? $mixed->seoPath ?? $mixed->cName ?? null;
                break;
            case 0:
            default:
                $result = $mixed->id ?? $mixed->kHersteller ?? null;
                break;
        }

        return empty($result) ? 'image' : Image::getCleanFilename((string)$result);
    }

    /**
     * @inheritdoc
     */
    public function getPathByID($id, int $number = null): ?string
    {
        return $this->db->queryPrepared(
            'SELECT cBildpfad AS path
                FROM thersteller
                WHERE kHersteller = :mid LIMIT 1',
            ['mid' => $id],
            ReturnType::SINGLE_OBJECT
        )->path ?? null;
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \STORAGE_MANUFACTURERS;
    }

    /**
     * @inheritdoc
     */
    public function getAllImages(int $offset = null, int $limit = null): Generator
    {
        $images = $this->db->query(
            'SELECT kHersteller AS id, cName, cSeo AS seoPath, cBildpfad AS path
                FROM thersteller
                WHERE cBildpfad IS NOT NULL AND cBildpfad != \'\'' . self::getLimitStatement($offset, $limit),
            ReturnType::QUERYSINGLE
        );
        while (($image = $images->fetch(PDO::FETCH_OBJ)) !== false) {
            yield MediaImageRequest::create([
                'id'         => $image->id,
                'type'       => self::TYPE,
                'name'       => self::getCustomName($image),
                'number'     => 1,
                'path'       => $image->path,
                'sourcePath' => $image->path,
                'ext'        => static::getFileExtension($image->path)
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getTotalImageCount(): int
    {
        return (int)$this->db->query(
            'SELECT COUNT(kHersteller) AS cnt
                FROM thersteller
                WHERE cBildpfad IS NOT NULL AND cBildpfad != \'\'',
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @inheritdoc
     */
    public function imageIsUsed(string $path): bool
    {
        return $this->db->select('thersteller', 'cBildpfad', $path) !== null;
    }
}
