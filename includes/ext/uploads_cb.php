<?php

use JTL\Catalog\Product\Artikel;
use JTL\Extensions\Upload\File;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Seo;
use JTL\Nice;
use JTL\Session\Frontend;
use JTL\Shop;

require_once __DIR__ . '/../globalinclude.php';

/**
 * output
 *
 * @param bool $bOk
 * @param int $responseCode
 * @param string $responseErrMsg
 */
function retCode(bool $bOk, int $responseCode = 200, string $responseErrMsg = 'error')
{
    http_response_code($responseCode);
    die(json_encode(['status' => $bOk ? 'ok' : $responseErrMsg]));
}

$session = Frontend::getInstance();
$conf    = Shop::getSettings([CONF_ARTIKELDETAILS]);
$limit   = (int)$conf['artikeldetails']['upload_modul_limit'];


if (!Form::validateToken()
    || !Nice::getInstance()->checkErweiterung(SHOP_ERWEITERUNG_UPLOADS)) {
    retCode(false, 403);
}
if (Form::reachedUploadLimitPerHour($limit)) {
    retCode(false, 403, 'reached_limit_per_hour');
}

$uploadProtect            = new stdClass();
$uploadProtect->cIP       = Request::getRealIP();
$uploadProtect->dErstellt = 'NOW()';
$uploadProtect->cTyp      = 'upload';
Shop::Container()->getDB()->insert('tfloodprotect', $uploadProtect);

if (!empty($_FILES)) {
    $blacklist = [
        'application/x-httpd-php-source',
        'application/x-httpd-php',
        'application/x-php',
        'application/php',
        'text/x-php',
        'text/php',
        'application/x-sh',
        'application/x-csh',
        'application/x-httpd-cgi',
        'application/x-httpd-perl',
        'application/octet-stream',
        'application/sql',
        'text/x-sql',
        'text/sql',
    ];

    $fileData          = isset($_FILES['Filedata']['tmp_name'])
        ? $_FILES['Filedata']
        : $_FILES['file_data'];
    $sourceInfo        = pathinfo($fileData['name']);
    $mime              = mime_content_type($fileData['tmp_name']);
    $allowedExtensions = [];

    foreach (Upload::gibArtikelUploads($_REQUEST['prodID']) as $scheme) {
        if ((int)$scheme->kUploadSchema === (int)$_REQUEST['kUploadSchema']) {
            $allowedExtensions = $scheme->cDateiTyp_arr;
        }
    }

    if (!isset($_REQUEST['uniquename'], $_REQUEST['cname'])) {
        retCode(false);
    }
    if (empty($allowedExtensions)
        || !in_array('*.' . strtolower($sourceInfo['extension']), $allowedExtensions, true)
    ) {
        retCode(false, 400, 'extension_not_listed');
    }
    if (in_array($mime, $blacklist, true)) {
        retCode(false, 403, 'filetype_forbidden');
    }

    $unique     = $_REQUEST['uniquename'];
    $targetFile = PFAD_UPLOADS . $unique;
    $tempFile   = $fileData['tmp_name'];
    $targetInfo = pathinfo($targetFile);
    $realPath   = str_replace('\\', '/', realpath($targetInfo['dirname']) . DS);

    // legitimate uploads do not have an extension for the destination file name - but for the originally uploaded file
    if (!isset($sourceInfo['extension']) || isset($targetInfo['extension'])) {
        retCode(false);
    }
    if (isset($fileData['error'], $fileData['name'])
        && (int)$fileData['error'] === UPLOAD_ERR_OK
        && mb_strpos($realPath, PFAD_UPLOADS) === 0
        && move_uploaded_file($tempFile, $targetFile)
    ) {
        $file    = new stdClass();
        $product = (new Artikel())->fuelleArtikel((int)$_REQUEST['prodID']);
        if (isset($_REQUEST['cname'])) {
            $preName = (int)$_REQUEST['prodID']
                . '_' . $product->cArtNr
                . '_' . Seo::sanitizeSeoSlug(Seo::getFlatSeoPath($_REQUEST['cname']));
        } else {
            $preName = (int)$_REQUEST['prodID']
                . '_' . $product->cArtNr
                . '_' . Seo::sanitizeSeoSlug(Seo::getFlatSeoPath($product->cName));
        }
        if (empty($_REQUEST['variation'])) {
            $postName = '_' . $unique . '.' . $sourceInfo['extension'];
        } else {
            $postName = '_' . Seo::sanitizeSeoSlug(Seo::getFlatSeoPath($_REQUEST['variation']))
                . '_' . $unique . '.' . $sourceInfo['extension'];
        }

        $file->cName  = mb_substr($preName, 0, 200 - mb_strlen($postName)) . $postName;
        $file->nBytes = $fileData['size'];
        $file->cKB    = round($fileData['size'] / 1024, 2);

        if (!isset($_SESSION['Uploader'])) {
            $_SESSION['Uploader'] = [];
        }
        $_SESSION['Uploader'][$unique] = $file;
        if (isset($_REQUEST['uploader'])) {
            die(json_encode($file));
        }
        retCode(true);
    }
    retCode(false);
}
if (!empty($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'remove':
            $unique     = $_REQUEST['uniquename'];
            $filePath   = PFAD_UPLOADS . $unique;
            $targetInfo = pathinfo($filePath);
            $realPath   = str_replace('\\', '/', realpath($targetInfo['dirname'] . DS));
            if (!isset($targetInfo['extension'])
                && isset($_SESSION['Uploader'][$unique])
                && mb_strpos($realPath, PFAD_UPLOADS) === 0
            ) {
                unset($_SESSION['Uploader'][$unique]);
                if (file_exists($filePath)) {
                    retCode(@unlink($filePath));
                }
            } else {
                retCode(false);
            }
            break;

        case 'exists':
            $filePath = PFAD_UPLOADS . $_REQUEST['uniquename'];
            $info     = pathinfo($filePath);
            retCode(!isset($info['extension']) && file_exists(realpath($filePath)));
            break;

        case 'preview':
            $uploadFile = new File();
            $customerID = (int)($_SESSION['Kunde']->kKunde ?? 0);
            $filePath   = PFAD_ROOT . BILD_UPLOAD_ZUGRIFF_VERWEIGERT;
            $uploadID   = (int)Shop::Container()->getCryptoService()->decryptXTEA(rawurldecode($_REQUEST['secret']));
            if ($uploadID > 0 && $customerID > 0 && $uploadFile->loadFromDB($uploadID)) {
                $tmpFilePath = PFAD_UPLOADS . $uploadFile->cPfad;
                if (file_exists($tmpFilePath)) {
                    $filePath = $tmpFilePath;
                }
            }
            header('Cache-Control: max-age=3600, public');
            header('Content-type: Image');

            readfile($filePath);
            exit;

        default:
            break;
    }
}

retCode(false, 400);
