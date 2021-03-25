<?php declare(strict_types=1);

namespace JTL\Media\Image;

use Generator;
use JTL\Catalog\Product\Artikel;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Media\Image;
use JTL\Media\MediaImageRequest;
use JTL\Shop;
use PDO;
use stdClass;

/**
 * Class Product
 * @package JTL\Media\Image
 */
class Product extends AbstractImage
{
    public const TYPE = Image::TYPE_PRODUCT;

    /**
     * @var string
     */
    public const REGEX = '/^media\/image'
    . '\/(?P<type>product)'
    . '\/(?P<id>\d+)'
    . '\/(?P<size>xs|sm|md|lg|xl|os)'
    . '\/(?P<name>[' . self::REGEX_ALLOWED_CHARS . ']+)'
    . '(?:(?:~(?P<number>\d+))?)\.(?P<ext>jpg|jpeg|png|gif|webp)$/';

    /**
     * @inheritdoc
     */
    public function getImageNames(MediaImageRequest $req): array
    {
        return $this->db->queryPrepared(
            'SELECT A.kArtikel, A.cName, A.cSeo, A.cSeo AS originalSeo, A.cArtNr, A.cBarcode, B.cWert AS customImgName
                FROM tartikel A
                LEFT JOIN tartikelattribut B 
                    ON A.kArtikel = B.kArtikel
                    AND B.cName = :atr
                WHERE A.kArtikel = :pid',
            ['pid' => $req->getID(), 'atr' => 'bildname'],
            ReturnType::COLLECTION
        )->map(static function ($item) {
            return self::getCustomName($item);
        })->toArray();
    }

    /**
     * @inheritdoc
     */
    public function getTotalImageCount(): int
    {
        return (int)$this->db->query(
            'SELECT COUNT(tartikelpict.kArtikel) AS cnt
                FROM tartikelpict
                INNER JOIN tartikel
                    ON tartikelpict.kArtikel = tartikel.kArtikel',
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @inheritdoc
     */
    public function getAllImages(int $offset = null, int $limit = null): Generator
    {
        $cols = '';
        switch (Image::getSettings()['naming'][Image::TYPE_PRODUCT]) {
            case 1:
                $cols = ', tartikel.cArtNr';
                break;
            case 2:
                $cols = ', tartikel.cSeo, tartikel.cSeo AS originalSeo, tartikel.cName';
                break;
            case 3:
                $cols = ', tartikel.cArtNr, tartikel.cSeo, tartikel.cSeo AS originalSeo, tartikel.cName';
                break;
            case 4:
                $cols = ', tartikel.cBarcode';
                break;
            case 0:
            default:
                break;
        }
        $images = $this->db->query(
            'SELECT B.cWert AS customImgName, P.cPfad AS path, P.nNr AS number, P.kArtikel ' . $cols . '
                FROM tartikelpict P
                INNER JOIN tartikel
                    ON P.kArtikel = tartikel.kArtikel
                LEFT JOIN tartikelattribut B 
                    ON tartikel.kArtikel = B.kArtikel
                    AND B.cName = \'bildname\''
            . self::getLimitStatement($offset, $limit),
            ReturnType::QUERYSINGLE
        );
        while (($image = $images->fetch(PDO::FETCH_OBJ)) !== false) {
            yield MediaImageRequest::create([
                'id'         => $image->kArtikel,
                'type'       => self::TYPE,
                'name'       => self::getCustomName($image),
                'number'     => $image->number,
                'path'       => $image->path,
                'sourcePath' => $image->path,
                'ext'        => static::getFileExtension($image->path)
            ]);
        }
    }

    /**
     * @param Artikel $mixed
     * @return string
     */
    public static function getCustomName($mixed): string
    {
        if (!empty($mixed->customImgName)) { // set by FKT_ATTRIBUT_BILDNAME
            return Image::getCleanFilename($mixed->customImgName);
        }
        switch (Image::getSettings()['naming'][Image::TYPE_PRODUCT]) {
            case 0:
                $result = (string)$mixed->kArtikel;
                break;
            case 1:
                $result = $mixed->cArtNr;
                break;
            case 2:
                $result = $mixed->originalSeo ?? $mixed->cSeo ?? $mixed->cName;
                break;
            case 3:
                $result = \sprintf('%s_%s', $mixed->cArtNr, empty($mixed->cSeo) ? $mixed->cName : $mixed->cSeo);
                break;
            case 4:
                $result = $mixed->cBarcode;
                break;
            default:
                $result = 'image';
                break;
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
                FROM tartikelpict
                WHERE kArtikel = :pid
                    AND nNr = :no
                ORDER BY nNr
                LIMIT 1',
            ['pid' => $id, 'no' => $number],
            ReturnType::SINGLE_OBJECT
        )->path ?? null;
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \PFAD_MEDIA_IMAGE_STORAGE;
    }

    /**
     * @param int              $id
     * @param DbInterface|null $db
     * @return int|null
     */
    public static function getPrimaryNumber(int $id, DbInterface $db = null): ?int
    {
        $prepared = self::getImageStmt(Image::TYPE_PRODUCT, $id);
        if ($prepared !== null) {
            $db      = $db ?? Shop::Container()->getDB();
            $primary = $db->queryPrepared(
                $prepared->stmt,
                $prepared->bind,
                ReturnType::SINGLE_OBJECT
            );
            if (\is_object($primary)) {
                return \max(1, (int)$primary->number);
            }
        }

        return null;
    }

    /**
     * @param string $type
     * @param int    $id
     * @return stdClass|null
     */
    public static function getImageStmt(string $type, int $id): ?stdClass
    {
        return (object)[
            'stmt' => 'SELECT kArtikel, nNr AS number
                FROM tartikelpict 
                WHERE kArtikel = :kArtikel 
                GROUP BY cPfad 
                ORDER BY nNr ASC',
            'bind' => ['kArtikel' => $id]
        ];
    }

    /**
     * @inheritdoc
     */
    public function imageIsUsed(string $path): bool
    {
        return $this->db->select('tartikelpict', 'cPfad', $path) !== null;
    }
}
