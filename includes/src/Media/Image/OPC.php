<?php declare(strict_types=1);

namespace JTL\Media\Image;

use DirectoryIterator;
use JTL\Media\Image;
use JTL\Media\MediaImageRequest;
use JTL\OPC\PortletInstance;

/**
 * Class OPC
 * @package JTL\Media\Image
 */
class OPC extends AbstractImage
{
    public const TYPE = Image::TYPE_OPC;

    /**
     * @var string
     */
    public const REGEX = '/^media\/image'
    . '\/(?P<type>opc)'
    . '\/(?P<size>xs|sm|md|lg|xl)'
    . '\/(?P<name>[' . self::REGEX_ALLOWED_CHARS . '\/]+)'
    . '(?:(?:~(?P<number>\d+))?)\.(?P<ext>jpg|jpeg|png|gif|webp)$/';

    /**
     * @inheritdoc
     */
    public function getImageNames(MediaImageRequest $req): array
    {
        $name = $req->getName();
        $file = $name . '.' . $req->getExt();
        if (\file_exists(\PFAD_ROOT . \STORAGE_OPC . $file)) {
            $req->setSourcePath($file);
        } else {
            foreach (self::$imageExtensions as $extension) {
                $file = $name . '.' . $extension;
                if (\file_exists(\PFAD_ROOT . \STORAGE_OPC . $file)) {
                    $req->setSourcePath($file);
                    break;
                }
            }
        }

        return [$name];
    }

    /**
     * @inheritdoc
     */
    public static function getCustomName($mixed): string
    {
        /** @var PortletInstance $mixed */
        $pathInfo = \pathinfo($mixed->currentImagePath);
        return (!empty($pathInfo['dirname']) && $pathInfo['dirname'] !== '.'
                ? $pathInfo['dirname'] . '/'
                : '') . $pathInfo['filename'];
    }

    /**
     * @inheritdoc
     */
    public function getPathByID($id, int $number = null): ?string
    {
        return $id;
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \STORAGE_OPC;
    }

    /**
     * @inheritdoc
     */
    public function getTotalImageCount(): int
    {
        $iterator = new DirectoryIterator(\PFAD_ROOT . self::getStoragePath());
        $cnt      = 0;
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isDot() || !$fileinfo->isFile()) {
                continue;
            }
            ++$cnt;
        }

        return $cnt;
    }
}
