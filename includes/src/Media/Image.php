<?php declare(strict_types=1);

namespace JTL\Media;

use Exception;
use Imagick;
use Intervention\Image\Constraint;
use Intervention\Image\Image as InImage;
use Intervention\Image\ImageManager;
use JTL\Media\Image\AbstractImage;
use JTL\Shop;

/**
 * Class Image
 * @package JTL\Media
 */
class Image
{
    /**
     * Image types
     */
    public const TYPE_PRODUCT              = 'product';
    public const TYPE_CATEGORY             = 'category';
    public const TYPE_OPC                  = 'opc';
    public const TYPE_CONFIGGROUP          = 'configgroup';
    public const TYPE_VARIATION            = 'variation';
    public const TYPE_MANUFACTURER         = 'manufacturer';
    public const TYPE_NEWS                 = 'news';
    public const TYPE_NEWSCATEGORY         = 'newscategory';
    public const TYPE_CHARACTERISTIC       = 'characteristic';
    public const TYPE_CHARACTERISTIC_VALUE = 'characteristicvalue';

    /**
     * Image sizes
     */
    public const SIZE_XS = 'xs';
    public const SIZE_SM = 'sm';
    public const SIZE_MD = 'md';
    public const SIZE_LG = 'lg';
    public const SIZE_XL = 'xl';

    /**
     * Image size map
     *
     * @var array
     */
    private static $sizes = [
        self::SIZE_XS,
        self::SIZE_SM,
        self::SIZE_MD,
        self::SIZE_LG,
        self::SIZE_XL
    ];

    /**
     * Image settings
     *
     * @var array
     */
    private static $settings;

    /**
     * @var bool
     */
    private static $webPSupport;

    /**
     * @return array
     */
    public static function getAllSizes(): array
    {
        return self::$sizes;
    }

    /**
     *  Global image settings
     *
     * @return array
     */
    public static function getSettings(): array
    {
        if (self::$settings !== null) {
            return self::$settings;
        }
        $settings = Shop::getSettings([\CONF_BILDER, \CONF_BRANDING]);
        $branding = $settings['branding'];
        $settings = $settings['bilder'];

        self::$settings         = [
            'background'                    => $settings['bilder_hintergrundfarbe'],
            'container'                     => $settings['container_verwenden'] === 'Y',
            'format'                        => \mb_convert_case($settings['bilder_dateiformat'], \MB_CASE_LOWER),
            'quality'                       => (int)$settings['bilder_jpg_quali'],
            'branding'                      => $branding,
            self::TYPE_PRODUCT              => [
                self::SIZE_XS => [
                    'width'  => (int)$settings['bilder_artikel_mini_breite'],
                    'height' => (int)$settings['bilder_artikel_mini_hoehe']
                ],
                self::SIZE_SM => [
                    'width'  => (int)$settings['bilder_artikel_klein_breite'],
                    'height' => (int)$settings['bilder_artikel_klein_hoehe']
                ],
                self::SIZE_MD => [
                    'width'  => (int)$settings['bilder_artikel_normal_breite'],
                    'height' => (int)$settings['bilder_artikel_normal_hoehe']
                ],
                self::SIZE_LG => [
                    'width'  => (int)$settings['bilder_artikel_gross_breite'],
                    'height' => (int)$settings['bilder_artikel_gross_hoehe']
                ]
            ],
            self::TYPE_MANUFACTURER         => [
                self::SIZE_XS => [
                    'width'  => (int)$settings['bilder_hersteller_mini_breite'],
                    'height' => (int)$settings['bilder_hersteller_mini_hoehe']
                ],
                self::SIZE_SM => [
                    'width'  => (int)$settings['bilder_hersteller_klein_breite'],
                    'height' => (int)$settings['bilder_hersteller_klein_hoehe']
                ],
                self::SIZE_MD => [
                    'width'  => (int)$settings['bilder_hersteller_normal_breite'],
                    'height' => (int)$settings['bilder_hersteller_normal_hoehe']
                ],
                self::SIZE_LG => [
                    'width'  => (int)$settings['bilder_hersteller_gross_breite'],
                    'height' => (int)$settings['bilder_hersteller_gross_hoehe']
                ]
            ],
            self::TYPE_CHARACTERISTIC       => [
                self::SIZE_XS => [
                    'width'  => (int)$settings['bilder_merkmal_mini_breite'],
                    'height' => (int)$settings['bilder_merkmal_mini_hoehe']
                ],
                self::SIZE_SM => [
                    'width'  => (int)$settings['bilder_merkmal_klein_breite'],
                    'height' => (int)$settings['bilder_merkmal_klein_hoehe']
                ],
                self::SIZE_MD => [
                    'width'  => (int)$settings['bilder_merkmal_normal_breite'],
                    'height' => (int)$settings['bilder_merkmal_normal_hoehe']
                ],
                self::SIZE_LG => [
                    'width'  => (int)$settings['bilder_merkmal_gross_breite'],
                    'height' => (int)$settings['bilder_merkmal_gross_hoehe']
                ]
            ],
            self::TYPE_CHARACTERISTIC_VALUE => [
                self::SIZE_XS => [
                    'width'  => (int)$settings['bilder_merkmalwert_mini_breite'],
                    'height' => (int)$settings['bilder_merkmalwert_mini_hoehe']
                ],
                self::SIZE_SM => [
                    'width'  => (int)$settings['bilder_merkmalwert_klein_breite'],
                    'height' => (int)$settings['bilder_merkmalwert_klein_hoehe']
                ],
                self::SIZE_MD => [
                    'width'  => (int)$settings['bilder_merkmalwert_normal_breite'],
                    'height' => (int)$settings['bilder_merkmalwert_normal_hoehe']
                ],
                self::SIZE_LG => [
                    'width'  => (int)$settings['bilder_merkmalwert_gross_breite'],
                    'height' => (int)$settings['bilder_merkmalwert_gross_hoehe']
                ]
            ],
            self::TYPE_CONFIGGROUP          => [
                self::SIZE_XS => [
                    'width'  => (int)$settings['bilder_konfiggruppe_mini_breite'],
                    'height' => (int)$settings['bilder_konfiggruppe_mini_hoehe']
                ],
                self::SIZE_SM => [
                    'width'  => (int)$settings['bilder_konfiggruppe_klein_breite'],
                    'height' => (int)$settings['bilder_konfiggruppe_klein_hoehe']
                ],
                self::SIZE_MD => [
                    'width'  => (int)$settings['bilder_konfiggruppe_normal_breite'],
                    'height' => (int)$settings['bilder_konfiggruppe_normal_hoehe']
                ],
                self::SIZE_LG => [
                    'width'  => (int)$settings['bilder_konfiggruppe_gross_breite'],
                    'height' => (int)$settings['bilder_konfiggruppe_gross_hoehe']
                ]
            ],
            self::TYPE_CATEGORY             => [
                self::SIZE_XS => [
                    'width'  => (int)$settings['bilder_kategorien_mini_breite'],
                    'height' => (int)$settings['bilder_kategorien_mini_hoehe']
                ],
                self::SIZE_SM => [
                    'width'  => (int)$settings['bilder_kategorien_klein_breite'],
                    'height' => (int)$settings['bilder_kategorien_klein_hoehe']
                ],
                self::SIZE_MD => [
                    'width'  => (int)$settings['bilder_kategorien_breite'],
                    'height' => (int)$settings['bilder_kategorien_hoehe']
                ],
                self::SIZE_LG => [
                    'width'  => (int)$settings['bilder_kategorien_gross_breite'],
                    'height' => (int)$settings['bilder_kategorien_gross_hoehe']
                ]
            ],
            self::TYPE_VARIATION            => [
                self::SIZE_XS => [
                    'width'  => (int)$settings['bilder_variationen_mini_breite'],
                    'height' => (int)$settings['bilder_variationen_mini_hoehe']
                ],
                self::SIZE_SM => [
                    'width'  => (int)$settings['bilder_variationen_klein_breite'],
                    'height' => (int)$settings['bilder_variationen_klein_hoehe']
                ],
                self::SIZE_MD => [
                    'width'  => (int)$settings['bilder_variationen_breite'],
                    'height' => (int)$settings['bilder_variationen_hoehe']
                ],
                self::SIZE_LG => [
                    'width'  => (int)$settings['bilder_variationen_gross_breite'],
                    'height' => (int)$settings['bilder_variationen_gross_hoehe']
                ]
            ],
            self::TYPE_OPC                  => [
                self::SIZE_XS => [
                    'width'  => 480,
                    'height' => 480
                ],
                self::SIZE_SM => [
                    'width'  => 720,
                    'height' => 720
                ],
                self::SIZE_MD => [
                    'width'  => 1080,
                    'height' => 1080
                ],
                self::SIZE_LG => [
                    'width'  => 1440,
                    'height' => 1440
                ]
            ],
            self::TYPE_NEWS                 => [
                self::SIZE_XS => [
                    'width'  => (int)$settings['bilder_news_mini_breite'],
                    'height' => (int)$settings['bilder_news_mini_hoehe']
                ],
                self::SIZE_SM => [
                    'width'  => (int)$settings['bilder_news_klein_breite'],
                    'height' => (int)$settings['bilder_news_klein_hoehe']
                ],
                self::SIZE_MD => [
                    'width'  => (int)$settings['bilder_news_normal_breite'],
                    'height' => (int)$settings['bilder_news_normal_hoehe']
                ],
                self::SIZE_LG => [
                    'width'  => (int)$settings['bilder_news_gross_breite'],
                    'height' => (int)$settings['bilder_news_gross_hoehe']
                ]
            ],
            self::TYPE_NEWSCATEGORY         => [
                self::SIZE_XS => [
                    'width'  => (int)$settings['bilder_newskategorie_mini_breite'],
                    'height' => (int)$settings['bilder_newskategorie_mini_hoehe']
                ],
                self::SIZE_SM => [
                    'width'  => (int)$settings['bilder_newskategorie_klein_breite'],
                    'height' => (int)$settings['bilder_newskategorie_klein_hoehe']
                ],
                self::SIZE_MD => [
                    'width'  => (int)$settings['bilder_newskategorie_normal_breite'],
                    'height' => (int)$settings['bilder_newskategorie_normal_hoehe']
                ],
                self::SIZE_LG => [
                    'width'  => (int)$settings['bilder_newskategorie_gross_breite'],
                    'height' => (int)$settings['bilder_newskategorie_gross_hoehe']
                ]
            ],
            'naming'                        => [
                self::TYPE_PRODUCT              => (int)$settings['bilder_artikel_namen'],
                self::TYPE_CATEGORY             => (int)$settings['bilder_kategorie_namen'],
                self::TYPE_VARIATION            => (int)$settings['bilder_variation_namen'],
                self::TYPE_MANUFACTURER         => (int)$settings['bilder_hersteller_namen'],
                self::TYPE_CHARACTERISTIC       => (int)$settings['bilder_merkmal_namen'],
                self::TYPE_CHARACTERISTIC_VALUE => (int)$settings['bilder_merkmalwert_namen'],
            ]
        ];
        self::$settings['size'] = self::$settings[self::TYPE_PRODUCT];

        return self::$settings;
    }

    /**
     * @param string $filepath
     * @return string
     */
    public static function getMimeType(string $filepath): string
    {
        return \image_type_to_mime_type(self::getImageType($filepath) ?? \IMAGETYPE_JPEG);
    }

    /**
     * @param string $filepath
     * @return int|null
     */
    public static function getImageType(string $filepath): ?int
    {
        return \function_exists('exif_imagetype')
            ? \exif_imagetype($filepath)
            : \getimagesize($filepath)[2] ?? null;
    }

    /**
     * @param string $filename
     * @return string
     */
    public static function getCleanFilename(string $filename): string
    {
        $source   = ['.', ' ', '/', 'ä', 'ö', 'ü', 'ß'];
        $replace  = ['-', '-', '-', 'ae', 'oe', 'ue', 'ss'];
        $filename = \str_replace($source, $replace, \mb_convert_case($filename, \MB_CASE_LOWER));

        return \preg_replace('/[^' . AbstractImage::REGEX_ALLOWED_CHARS . ']/', '', $filename);
    }

    /**
     * @param MediaImageRequest $req
     * @param bool              $streamOutput
     * @throws Exception
     */
    public static function render(MediaImageRequest $req, bool $streamOutput = false): void
    {
        $rawPath = $req->getRaw();
        if ($rawPath === null || !\is_file($rawPath)) {
            throw new Exception(\sprintf('Image "%s" does not exist', $rawPath));
        }
        $settings  = self::getSettings();
        $thumbnail = $req->getThumb($req->getSize(), true);
        $manager   = new ImageManager(['driver' => self::getImageDriver()]);
        $img       = $manager->make($rawPath);
        $regExt    = $req->getExt();
        if (($regExt === 'jpg' || $regExt === 'jpeg') && $settings['container'] === true) {
            $canvas = $manager->canvas($img->width(), $img->height(), $settings['background']);
            $canvas->insert($img);
            $img = $canvas;
        }
        self::checkDirectory($thumbnail);
        self::resize($req, $img, $settings);
        self::addBranding($manager, $req, $img);
        self::optimizeImage($img, $regExt);
        \executeHook(\HOOK_IMAGE_RENDER, [
            'image'    => $img,
            'settings' => $settings,
            'path'     => $thumbnail
        ]);
        $img->save($thumbnail, $settings['quality'], $regExt);
        if ($streamOutput) {
            echo $img->response($regExt);
        }
    }

    /**
     * @param InImage $image
     * @param string  $extension
     */
    private static function optimizeImage(InImage $image, string $extension): void
    {
        // @todo: doesn't look very good with small images
//        $image->blur(1);
        // @todo: strange blue tones with PNG
//        if (self::getImageDriver() === 'imagick') {
//            $image->getCore()->setColorspace(\Imagick::COLORSPACE_RGB);
//            $image->getCore()->transformImageColorspace(\Imagick::COLORSPACE_RGB);
//            $image->getCore()->stripImage();
//        }
        if ($extension === 'jpg') {
            $image->interlace();
        }
    }

    /**
     * @param MediaImageRequest $req
     * @param InImage           $img
     * @param array             $settings
     */
    private static function resize(MediaImageRequest $req, InImage $img, array $settings): void
    {
        $containerDim = $req->getSize();
        $maxWidth     = $containerDim->getWidth();
        $maxHeight    = $containerDim->getHeight();
        if ($maxWidth > 0 && $maxHeight > 0) {
            if ($img->getWidth() > $maxWidth || $img->getHeight() > $maxHeight) {
                $img->resize($maxWidth, $maxHeight, static function (Constraint $constraint) {
                    $constraint->aspectRatio();
                });
            }
            if ($settings['container'] === true && $req->getType() !== self::TYPE_OPC) {
                $img->resizeCanvas($maxWidth, $maxHeight, 'center', false, $settings['background']);
            }
        }
    }

    /**
     * @param ImageManager      $manager
     * @param MediaImageRequest $req
     * @param InImage           $img
     */
    private static function addBranding(ImageManager $manager, MediaImageRequest $req, InImage $img): void
    {
        $type   = $req->getType();
        $size   = $req->getSize()->getSize();
        $config = self::getSettings()['branding'];
        $config = $config[$type] ?? null;
        if ($config === null || !\in_array($size, [self::SIZE_LG, self::SIZE_XL], true)) {
            return;
        }
        $watermark = $manager->make($config->path);
        if ($config->size > 0) {
            $brandWidth  = \round(($img->getWidth() * $config->size) / 100.0);
            $brandHeight = \round(($brandWidth / $watermark->getWidth()) * $watermark->getHeight());
            $newWidth    = \min($watermark->getWidth(), $brandWidth);
            $newHeight   = \min($watermark->getHeight(), $brandHeight);
            $watermark->resize($newWidth, $newHeight, static function (Constraint $constraint) {
                $constraint->aspectRatio();
            });
            $watermark->opacity(100 - $config->transparency);
            $img->insert($watermark, $config->position, 10, 10);
        }
    }

    /**
     * @param string $thumbnail
     * @throws Exception
     */
    private static function checkDirectory(string $thumbnail): void
    {
        $directory = \pathinfo($thumbnail, \PATHINFO_DIRNAME);
        if (!\is_dir($directory) && !\mkdir($directory, 0777, true) && !\is_dir($directory)) {
            $error = \error_get_last();
            if (empty($error)) {
                $error = 'Unable to create directory ' . $directory;
            }
            throw new Exception(\is_array($error) ? $error['message'] : $error);
        }
    }

    /**
     * @return string
     */
    public static function getImageDriver(): string
    {
        return \extension_loaded('imagick') && !\FORCE_IMAGEDRIVER_GD ? 'imagick' : 'gd';
    }

    /**
     * @return bool
     */
    public static function hasWebPSupport(): bool
    {
        if (self::getSettings()['format'] !== 'auto') {
            return false;
        }
        if (self::$webPSupport === null) {
            self::$webPSupport = self::getImageDriver() === 'imagick'
                ? \count(Imagick::queryFormats('WEBP')) > 0
                : \gd_info()['WebP Support'] ?? false;
        }

        return self::$webPSupport;
    }
}
