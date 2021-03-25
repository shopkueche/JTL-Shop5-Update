<?php

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Shop;

/**
 * @return array
 */
function gibAlleSuchspecialOverlays()
{
    $overlays = [];
    $types    = Shop::Container()->getDB()->query(
        'SELECT kSuchspecialOverlay
            FROM tsuchspecialoverlay',
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($types as $type) {
        $overlays[] = JTL\Media\Image\Overlay::getInstance(
            (int)$type->kSuchspecialOverlay,
            (int)$_SESSION['editLanguageID']
        );
    }

    return $overlays;
}

/**
 * @param int $overlayID
 * @return JTL\Media\Image\Overlay
 */
function gibSuchspecialOverlay(int $overlayID)
{
    return JTL\Media\Image\Overlay::getInstance($overlayID, (int)$_SESSION['editLanguageID']);
}

/**
 * @param int $overlayID
 * @param array $post
 * @param array $files
 * @param int|null $lang
 * @param string|null $template
 * @return bool
 */
function speicherEinstellung(
    int $overlayID,
    array $post,
    array $files,
    int $lang = null,
    string $template = null
): bool {
    $overlay = JTL\Media\Image\Overlay::getInstance(
        $overlayID,
        $lang ?? (int)$_SESSION['editLanguageID'],
        $template,
        false
    );

    if ($overlay->getType() <= 0) {
        Shop::Container()->getAlertService()->addAlert(Alert::TYPE_ERROR, __('invalidOverlay'), 'invalidOverlay');
        return false;
    }
    $overlay->setActive((int)$post['nAktiv'])
            ->setTransparence((int)$post['nTransparenz'])
            ->setSize((int)$post['nGroesse'])
            ->setPosition((int)($post['nPosition'] ?? 0))
            ->setPriority((int)$post['nPrio']);

    if (mb_strlen($files['name']) > 0) {
        $template    = $template
            ?: Shop::Container()->getTemplateService()->getActiveTemplate()->getName();
        $overlayPath = PFAD_ROOT . PFAD_TEMPLATES . $template . PFAD_OVERLAY_TEMPLATE;
        if (!is_writable($overlayPath)) {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_ERROR,
                sprintf(__('errorOverlayWritePermissions'), PFAD_TEMPLATES . $template . PFAD_OVERLAY_TEMPLATE),
                'errorOverlayWritePermissions',
                ['saveInSession' => true]
            );

            return false;
        }

        loescheBild($overlay);
        $overlay->setImageName(
            JTL\Media\Image\Overlay::IMAGENAME_TEMPLATE . '_' . $overlay->getLanguage() . '_' . $overlay->getType() .
            mappeFileTyp($files['type'])
        );
        $imageCreated = speicherBild($files, $overlay);
    }
    if (!isset($imageCreated) || $imageCreated) {
        $overlay->save();
    } else {
        Shop::Container()->getAlertService()->addAlert(
            Alert::TYPE_ERROR,
            __('errorFileUploadGeneral'),
            'errorFileUploadGeneral',
            ['saveInSession' => true]
        );

        return false;
    }

    return true;
}

/**
 * @param resource $dst_im
 * @param resource $src_im
 * @param int      $dst_x
 * @param int      $dst_y
 * @param int      $src_x
 * @param int      $src_y
 * @param int      $src_w
 * @param int      $src_h
 * @param int      $pct
 * @return bool
 */
function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
{
    if ($pct === null) {
        return false;
    }
    $pct /= 100;
    // Get image width and height
    $w = imagesx($src_im);
    $h = imagesy($src_im);
    // Turn alpha blending off
    imagealphablending($src_im, false);

    $minalpha = 0;

    // loop through image pixels and modify alpha for each
    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y++) {
            // get current alpha value (represents the TANSPARENCY!)
            $colorxy = imagecolorat($src_im, $x, $y);
            $alpha   = ($colorxy >> 24) & 0xFF;
            // calculate new alpha
            if ($minalpha !== 127) {
                $alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
            } else {
                $alpha += 127 * $pct;
            }
            // get the color index with new alpha
            $alphacolorxy = imagecolorallocatealpha(
                $src_im,
                ($colorxy >> 16) & 0xFF,
                ($colorxy >> 8) & 0xFF,
                $colorxy & 0xFF,
                $alpha
            );
            // set pixel with the new color + opacity
            if (!imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
                return false;
            }
        }
    }

    return imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
}

/**
 * @param string $img
 * @param int    $width
 * @param int    $height
 * @return resource|null
 */
function imageload_alpha($img, $width, $height)
{
    $imgInfo = getimagesize($img);
    switch ($imgInfo[2]) {
        case 1:
            $im = imagecreatefromgif($img);
            break;
        case 2:
            $im = imagecreatefromjpeg($img);
            break;
        case 3:
            $im = imagecreatefrompng($img);
            break;
        default:
            return null;
    }

    $new = imagecreatetruecolor($width, $height);

    if ($imgInfo[2] == 1 || $imgInfo[2] == 3) {
        imagealphablending($new, false);
        imagesavealpha($new, true);
        $transparent = imagecolorallocatealpha($new, 255, 255, 255, 127);
        imagefilledrectangle($new, 0, 0, $width, $height, $transparent);
    }

    imagecopyresampled($new, $im, 0, 0, 0, 0, $width, $height, $imgInfo[0], $imgInfo[1]);

    return $new;
}

/**
 * @param string $image
 * @param int    $width
 * @param int    $height
 * @param int    $transparency
 * @return resource
 */
function ladeOverlay($image, $width, $height, $transparency)
{
    $src = imageload_alpha($image, $width, $height);
    if ($transparency > 0) {
        $new = imagecreatetruecolor($width, $height);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        $transparent = imagecolorallocatealpha($new, 255, 255, 255, 127);
        imagefilledrectangle($new, 0, 0, $width, $height, $transparent);
        imagealphablending($new, true);
        imagesavealpha($new, true);

        imagecopymerge_alpha($new, $src, 0, 0, 0, 0, $width, $height, 100 - $transparency);

        return $new;
    }

    return $src;
}

/**
 * @param resource $im
 * @param string   $extension
 * @param string   $path
 * @param int      $quality
 * @return bool
 */
function speicherOverlay($im, $extension, $path, $quality = 80)
{
    if (!$extension || !$im) {
        return false;
    }
    switch ($extension) {
        case '.jpg':
            return function_exists('imagejpeg') ? imagejpeg($im, $path, $quality) : false;
        case '.png':
            return function_exists('imagepng') ? imagepng($im, $path) : false;
        case '.gif':
            return function_exists('imagegif') ? imagegif($im, $path) : false;
        case '.bmp':
            return function_exists('imagewbmp') ? imagewbmp($im, $path) : false;
        default:
            return false;
    }
}

/**
 * @deprecated since 5.0.0
 * @param string $image
 * @param string $width
 * @param string $height
 * @param int    $size
 * @param int    $transparency
 * @param string $extension
 * @param string $path
 */
function erstelleOverlay($image, $width, $height, $size, $transparency, $extension, $path)
{
    $conf   = Shop::getSettings([CONF_BILDER]);
    $width  = $conf['bilder'][$width];
    $height = $conf['bilder'][$height];

    [$overlayWidth, $overlayHight] = getimagesize($image);

    $nOffX = $nOffY = 1;
    if ($size > 0) {
        $maxWidth  = $width * ($size / 100);
        $maxHeight = $height * ($size / 100);

        $nOffX = $overlayWidth / $maxWidth;
        $nOffY = $overlayHight / $maxHeight;
    }

    if ($nOffY > $nOffX) {
        $overlayWidth = round($overlayWidth * (1 / $nOffY));
        $overlayHight = round($overlayHight * (1 / $nOffY));
    } else {
        $overlayWidth = round($overlayWidth * (1 / $nOffX));
        $overlayHight = round($overlayHight * (1 / $nOffX));
    }

    $im = ladeOverlay($image, $overlayWidth, $overlayHight, $transparency);
    speicherOverlay($im, $extension, $path);
}

/**
 * @param string $image
 * @param int    $size
 * @param int    $transparency
 * @param string $extension
 * @param string $path
 * @return bool
 */
function erstelleFixedOverlay(string $image, int $size, int $transparency, string $extension, string $path): bool
{
    [$width, $height] = getimagesize($image);
    $factor           = $size / $width;

    return speicherOverlay(ladeOverlay($image, $size, $height * $factor, $transparency), $extension, $path);
}


/**
 * @param array $files
 * @param JTL\Media\Image\Overlay $overlay
 * @return bool
 */
function speicherBild(array $files, JTL\Media\Image\Overlay $overlay): bool
{
    if ($files['type'] === 'image/jpeg'
        || $files['type'] === 'image/pjpeg'
        || $files['type'] === 'image/jpg'
        || $files['type'] === 'image/gif'
        || $files['type'] === 'image/png'
        || $files['type'] === 'image/bmp'
        || $files['type'] === 'image/x-png'
    ) {
        if (empty($files['error'])) {
            $ext      = mappeFileTyp($files['type']);
            $original = $files['tmp_name'];

            $sizesToCreate = [
                ['size' => IMAGE_SIZE_XS,  'factor' => 1],
                ['size' => IMAGE_SIZE_SM, 'factor' => 2],
                ['size' => IMAGE_SIZE_MD,  'factor' => 3],
                ['size' => IMAGE_SIZE_LG, 'factor' => 4]
            ];

            foreach ($sizesToCreate as $sizeToCreate) {
                if (!is_dir(PFAD_ROOT . $overlay->getPathSize($sizeToCreate['size']))) {
                    mkdir(PFAD_ROOT . $overlay->getPathSize($sizeToCreate['size']), 0755, true);
                }
                $imageCreated = erstelleFixedOverlay(
                    $original,
                    $overlay->getSize() * $sizeToCreate['factor'],
                    $overlay->getTransparance(),
                    $ext,
                    PFAD_ROOT . $overlay->getPathSize($sizeToCreate['size']) . $overlay->getImageName()
                );
                if (!$imageCreated) {
                    return false;
                }
            }

            return true;
        }
    }

    return false;
}

/**
 * @param JTL\Media\Image\Overlay $overlay
 */
function loescheBild(JTL\Media\Image\Overlay $overlay): void
{
    foreach ($overlay->getPathSizes() as $path) {
        $path = PFAD_ROOT . $path . $overlay->getImageName();
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}

/**
 * @param string $type
 * @return string
 */
function mappeFileTyp(string $type): string
{
    switch ($type) {
        case 'image/gif':
            return '.gif';
        case 'image/png':
        case 'image/x-png':
            return '.png';
        case 'image/bmp':
            return '.bmp';
        case 'image/jpg':
        case 'image/jpeg':
        case 'image/pjpeg':
        default:
            return '.jpg';
    }
}
