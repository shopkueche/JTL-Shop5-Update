<?php

use JTL\Shop;

/**
 * Speichert das aktuelle ShopLogo
 *
 * @param array $files
 * @return int
 * 1 = Alles O.K.
 * 2 = Dateiname leer
 * 3 = Dateityp entspricht nicht der Konvention (Nur jpg/gif/png/bmp/ Bilder) oder fehlt
 * 4 = Konnte nicht bewegen
 */
function saveShopLogo(array $files): int
{
    if (!file_exists(PFAD_ROOT . PFAD_SHOPLOGO)
        && !mkdir($concurrentDirectory = PFAD_ROOT . PFAD_SHOPLOGO)
        && !is_dir($concurrentDirectory)
    ) {
        return 4;
    }
    if (empty($files['shopLogo']['name'])) {
        return 2;
    }
    $allowedTypes = [
        'image/jpeg',
        'image/pjpeg',
        'image/gif',
        'image/png',
        'image/x-png',
        'image/bmp',
        'image/jpg',
        'image/svg+xml',
        'image/svg',
        'image/webp'
    ];
    if (!in_array($files['shopLogo']['type'], $allowedTypes, true)
        || (extension_loaded('fileinfo')
            && !in_array(mime_content_type($files['shopLogo']['tmp_name']), $allowedTypes, true))
    ) {
        return 3;
    }
    $file = PFAD_ROOT . PFAD_SHOPLOGO . basename($files['shopLogo']['name']);
    if ($files['shopLogo']['error'] === UPLOAD_ERR_OK && move_uploaded_file($files['shopLogo']['tmp_name'], $file)) {
        $option                        = new stdClass();
        $option->kEinstellungenSektion = CONF_LOGO;
        $option->cName                 = 'shop_logo';
        $option->cWert                 = $files['shopLogo']['name'];
        Shop::Container()->getDB()->update('teinstellungen', 'cName', 'shop_logo', $option);
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);

        return 1;
    }

    return 4;
}

/**
 * @return bool
 * @var string $logo
 */
function deleteShopLogo(string $logo): bool
{
    return is_file(PFAD_ROOT . $logo)
        ? unlink(PFAD_ROOT . $logo)
        : false;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function loescheAlleShopBilder(): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (is_dir(PFAD_ROOT . PFAD_SHOPLOGO) && $dh = opendir(PFAD_ROOT . PFAD_SHOPLOGO)) {
        while (($file = readdir($dh)) !== false) {
            if ($file !== '.' && $file !== '..' && $file !== '.gitkeep') {
                @unlink(PFAD_ROOT . PFAD_SHOPLOGO . $file);
            }
        }
        closedir($dh);

        return true;
    }

    return false;
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
        case 'image/pjpeg':
        case 'image/jpg':
        case 'image/jpeg':
        default:
            return '.jpg';
    }
}
