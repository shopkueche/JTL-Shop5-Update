<?php declare(strict_types=1);

namespace JTL\Installation\Faker;

use Faker\Provider\Base;
use Intervention\Image\ImageManager;

/**
 * Class ImageProvider
 * @package VueInstaller\Faker
 */
class ImageProvider extends Base
{
    private const JPEG_QUALITY = 90;

    private const DEFAULT_TEXT_COLOR = '#ffffff';

    private const DEFAULT_TEXT_FORMAT = '%width%x%height%';

    /**
     * The ultimate web 2.0 color list.
     *
     * https://docs.google.com/spreadsheets/d/1DhLdGhV4Fv_amgIbi5o2UFYZkkdp_TQNQcL6Of3LbCk/pub?output=html
     */
    private static $colors = [
        '#5b5b95', '#C79810', '#c69d18', '#6BBA70', '#ff6600', '#1393c0', '#4CADD4', '#ff0000',
        '#277fba', '#57A9FD', '#B71313', '#9AC65C', '#0e63fd', '#99cc33', '#3775e2', '#8dbb01',
        '#ffb640', '#1b5891', '#356AA0', '#e24602', '#4caae4', '#ffcc00', '#cf5700', '#D15600',
        '#5d82ff', '#00bada', '#3b5999', '#0170ca', '#eb003a', '#98cc00', '#bd1d01', '#0061DE',
        '#FF0084', '#0f0f0f', '#4096EE', '#a71a10', '#025aa2', '#e84c1f', '#FD65C2', '#d50000',
        '#ef9a19', '#96c63f', '#69dbff', '#FFC300', '#9CB6DE', '#D61C39', '#d10039', '#fe198e',
        '#bedb8a', '#1a7fb3', '#FF7B38', '#ff7638', '#3d5381', '#febf0f', '#f28fbf', '#ff910d',
        '#e51837', '#87c1e7', '#009f59', '#00457B', '#2971AD', '#fa9b65', '#3F4C6B', '#FF1A00',
        '#003399', '#478898', '#62b857', '#006E2E', '#00722d', '#2d9500', '#ec449b', '#5a471c',
        '#030303', '#ff6200', '#295e92', '#28CF21', '#383121', '#ff4600', '#6dc646', '#CC0000',
        '#FF7400', '#B02B2C', '#e51905', '#c00000', '#f78325', '#924357', '#36393D', '#87be2f',
        '#6cd0f6', '#03646a', '#00a8aa', '#89c122', '#9ac80d', '#6b9cc9', '#6699cc', '#505050',
        '#21628c', '#e5791e', '#057db9', '#2acc54', '#5ebe8f', '#780000', '#008C00', '#4ABA00',
        '#7f7f7f', '#EA0101', '#003368', '#fcbd00', '#d71920', '#128f34', '#0f0e13', '#174c89',
        '#64bb69', '#73880A', '#F3AE48', '#4db848', '#fc0234', '#d11001', '#ff3237', '#FF6666',
        '#03a0fa', '#2e2d2e',
    ];

    /**
     * Generate a new image to disk and return its location.
     *
     * @param string|null $dir Path of the generated file, if null will use the system temp dir
     * @param int         $width Width of the picture in pixels
     * @param int         $height Height of the picture in pixels
     * @param string      $format Image format, jpg or png
     * @param bool        $fullPath If true, returns the full path of the file generated, otherwise will only return the
     *     filename, default to true
     * @param string|null $text Text to generate on the picture
     * @param string|null $textColor Text color in hexadecimal format
     * @param string|null $backgroundColor Background color in hexadecimal format
     * @param string      $fontPath The name/path to the font
     * @return string
     */
    public static function imageFile(
        $dir = null,
        $width = 800,
        $height = 600,
        $format = 'png',
        $fullPath = true,
        $text = null,
        $textColor = null,
        $backgroundColor = null,
        $fontPath = \PFAD_ROOT . 'install/OpenSans-Regular.ttf'
    ): string {
        $dir = $dir ?? \sys_get_temp_dir(); // GNU/Linux / OS X / Windows compatible
        // Validate directory path
        if (!\is_dir($dir) || !\is_writable($dir)) {
            throw new \InvalidArgumentException(\sprintf('Cannot write to directory "%s"', $dir));
        }

        // Generate a random filename. Use the server address so that a file
        // generated at the same time on a different server won't have a collision.
        $name     = \md5(\uniqid(empty($_SERVER['SERVER_ADDR'])
            ? '' : $_SERVER['SERVER_ADDR'], true));
        $filename = \implode('.', [$name, $format]);
        $filepath = $dir . \DIRECTORY_SEPARATOR . $filename;

        if ($text === null) {
            $text = static::DEFAULT_TEXT_FORMAT;
        }

        if ($textColor === null) {
            $textColor = static::DEFAULT_TEXT_COLOR;
        }

        if ($backgroundColor === null) {
            $backgroundColor = static::randomElement(static::$colors);
        }

        $textColor       = static::hexColor($textColor);
        $backgroundColor = static::hexColor($backgroundColor);
        $formattedText   = \str_replace(
            ['%width%', '%height%', '%format%', '%file%', '%filepath%', '%color%', '%bgcolor%'],
            [$width, $height, $format, $filename, $filepath, $textColor, $backgroundColor],
            $text
        );
        $img             = new ImageManager(['driver' => \extension_loaded('imagick') ? 'imagick' : 'gd']);
        $canvas          = $img->canvas($width, $height, $backgroundColor);
        $canvas->text($formattedText, 20, $height / 2, static function ($font) use ($textColor, $fontPath) {
            $font->file($fontPath);
            $font->size(40);
            $font->color($textColor);
            $font->align('left');
            $font->valign('top');
        });
        $canvas->save($filepath, static::JPEG_QUALITY, $format);

        return $fullPath ? $filepath : $filename;
    }

    /**
     * @param string $color
     * @return string
     */
    private static function hexColor(string $color): string
    {
        if (\preg_match('/^#?([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color, $rgb)) {
            return '#' . $rgb[1];
        }
        throw new \InvalidArgumentException(\sprintf('Unrecognized hexcolor "%s"', $color));
    }
}
