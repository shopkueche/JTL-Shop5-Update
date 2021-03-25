<?php declare(strict_types=1);

namespace JTL\Media\Image;

use Generator;
use JTL\DB\ReturnType;
use JTL\Media\Image;
use JTL\Media\MediaImageRequest;
use PDO;
use stdClass;

/**
 * Class Variation
 * @package JTL\Media\Image
 */
class Variation extends AbstractImage
{
    public const TYPE = Image::TYPE_VARIATION;

    /**
     * @var string
     */
    public const REGEX = '/^media\/image'
    . '\/(?P<type>variation)'
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
            'stmt' => 'SELECT kEigenschaftWert, 0 AS number 
                           FROM teigenschaftwertpict 
                           WHERE kEigenschaftWert = :vid',
            'bind' => ['vid' => $id]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getImageNames(MediaImageRequest $req): array
    {
        return $this->db->queryPrepared(
            'SELECT p.kEigenschaftWert, p.kEigenschaftWertPict, p.cPfad AS path, t.cName
                FROM teigenschaftwertpict p
                JOIN teigenschaftwert t
                    ON p.kEigenschaftWert = t.kEigenschaftWert
                WHERE p.kEigenschaftWert = :vid',
            ['vid' => $req->getID()],
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
        if (isset($mixed->cPfad)) {
            $result = \pathinfo($mixed->cPfad)['filename'];
        } elseif (isset($mixed->path)) {
            $result = \pathinfo($mixed->path)['filename'];
        } else {
            $result = $mixed->cName;
        }

        return empty($result) ? 'image' : Image::getCleanFilename($result);
    }

    /**
     * @inheritdoc
     */
    public function getPathByID($id, int $number = null): ?string
    {
        return $this->db->queryPrepared(
            'SELECT cPfad AS path
                FROM teigenschaftwertpict
                WHERE kEigenschaftWert = :vid
                LIMIT 1',
            ['vid' => $id],
            ReturnType::SINGLE_OBJECT
        )->path ?? null;
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \STORAGE_VARIATIONS;
    }

    /**
     * @inheritdoc
     */
    public function getAllImages(int $offset = null, int $limit = null): Generator
    {
        $images = $this->db->query(
            'SELECT p.kEigenschaftWert AS id, p.kEigenschaftWertPict, p.cPfad AS path, t.cName
                FROM teigenschaftwertpict p
                JOIN teigenschaftwert t
                    ON p.kEigenschaftWert = t.kEigenschaftWert' . self::getLimitStatement($offset, $limit),
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
            'SELECT COUNT(kEigenschaftWertPict) AS cnt
                FROM teigenschaftwertpict
                WHERE cPfad IS NOT NULL
                    AND cPfad != \'\'',
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }
}
