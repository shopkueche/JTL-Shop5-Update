<?php declare(strict_types=1);

namespace JTL\Media\Image;

use Generator;
use JTL\DB\ReturnType;
use JTL\Media\Image;
use JTL\Media\MediaImageRequest;
use PDO;
use stdClass;

/**
 * Class Category
 * @package JTL\Media\Image
 */
class Category extends AbstractImage
{
    public const TYPE = Image::TYPE_CATEGORY;

    /**
     * @var string
     */
    public const REGEX = '/^media\/image'
    . '\/(?P<type>category)'
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
            'stmt' => 'SELECT kKategorie, 0 AS number 
                          FROM tkategoriepict 
                          WHERE kKategorie = :kKategorie',
            'bind' => ['kKategorie' => $id]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getImageNames(MediaImageRequest $req): array
    {
        return $this->db->queryPrepared(
            'SELECT pic.cPfad AS path, pic.kKategorie, pic.kKategorie AS id, cat.cName,
                atr.cWert AS customImgName, cat.cSeo AS seoPath
                FROM tkategorie cat
                JOIN tkategoriepict pic
                    ON cat.kKategorie = pic.kKategorie
                LEFT JOIN tkategorieattribut atr
                    ON cat.kKategorie = atr.kKategorie
                    AND atr.cName = :atr
                WHERE pic.kKategorie = :cid',
            ['cid' => $req->getID(), 'atr' => \KAT_ATTRIBUT_BILDNAME],
            ReturnType::COLLECTION
        )->map(static function ($item) {
            return self::getCustomName($item);
        })->toArray();
    }

    /**
     * @inheritdoc
     */
    public static function getCustomName($mixed): string
    {
        if (\is_string($mixed)) {
            $result = \pathinfo($mixed)['filename'];
        } elseif (isset($mixed->customImgName)) {
            $result = $mixed->customImgName;
        } elseif (isset($mixed->currentImagePath)) {
            $result = \pathinfo($mixed->currentImagePath)['filename'];
        } else {
            switch (Image::getSettings()['naming'][Image::TYPE_CATEGORY]) {
                case 2:
                    $result = $mixed->path ?? $mixed->cBildpfad ?? null;
                    if ($result !== null) {
                        $result = \pathinfo($result)['filename'];
                    }
                    break;
                case 1:
                    $result = \method_exists($mixed, 'getURL')
                        ? $mixed->getURL()
                        : ($mixed->originalSeo ?? $mixed->seoPath ?? $mixed->cName ?? null);
                    break;
                case 0:
                default:
                    $result = \method_exists($mixed, 'getID')
                        ? $mixed->getID()
                        : ($mixed->id ?? $mixed->kKategorie ?? null);
                    break;
            }
        }

        return empty($result) ? 'image' : Image::getCleanFilename((string)$result);
    }

    /**
     * @inheritdoc
     */
    public function getPathByID($id, int $number = null): ?string
    {
        return $this->db->queryPrepared(
            'SELECT cPfad AS path
                FROM tkategoriepict
                WHERE kKategorie = :cid LIMIT 1',
            ['cid' => $id],
            ReturnType::SINGLE_OBJECT
        )->path ?? null;
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \STORAGE_CATEGORIES;
    }

    /**
     * @inheritdoc
     */
    public function getAllImages(int $offset = null, int $limit = null): Generator
    {
        $images = $this->db->queryPrepared(
            'SELECT pic.cPfad AS path, pic.kKategorie, pic.kKategorie AS id, cat.cName, 
                atr.cWert AS customImgName, cat.cSeo AS seoPath
                FROM tkategorie cat
                JOIN tkategoriepict pic
                    ON cat.kKategorie = pic.kKategorie
                LEFT JOIN tkategorieattribut atr
                    ON cat.kKategorie = atr.kKategorie
                    AND atr.cName = :atr'
            . self::getLimitStatement($offset, $limit),
            ['atr' => \KAT_ATTRIBUT_BILDNAME],
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
            'SELECT COUNT(tkategoriepict.kKategorie) AS cnt
                FROM tkategoriepict
                INNER JOIN tkategorie
                    ON tkategorie.kKategorie = tkategoriepict.kKategorie',
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @inheritdoc
     */
    public function imageIsUsed(string $path): bool
    {
        return $this->db->select('tkategoriepict', 'cPfad', $path) !== null;
    }
}
