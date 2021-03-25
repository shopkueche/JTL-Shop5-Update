<?php

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('IMAGE_UPLOAD', true, true);
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'elfinder_inc.php';

if (Form::validateToken()) {
    $mediafilesSubdir = STORAGE_OPC;
    $mediafilesType   = Request::verifyGPDataString('mediafilesType');
    $elfinderCommand  = Request::verifyGPDataString('cmd');
    $isCKEditor       = Request::verifyGPDataString('ckeditor') === '1';
    $CKEditorFuncNum  = Request::verifyGPDataString('CKEditorFuncNum');

    if ($mediafilesType === 'video') {
        $mediafilesSubdir = PFAD_MEDIA_VIDEO;
    }

    $mediafilesBaseUrlPath = parse_url(URL_SHOP . '/' . $mediafilesSubdir, PHP_URL_PATH);

    if (!empty($elfinderCommand)) {
        // Documentation for connector options:
        // https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
        // run elFinder
        $connector = new elFinderConnector(new elFinder([
            'bind'  => [
                'rm rename' => static function ($cmd, &$result, $args, $elfinder, $volume) {
                    $sizes = ['xs', 'sm', 'md', 'lg', 'xl'];

                    foreach ($result['added'] as &$item) {
                        $item['name'] = mb_strtolower($item['name']);
                    }

                    foreach ($result['removed'] as $filename) {
                        foreach ($sizes as $size) {
                            $scaledFile = PFAD_ROOT . PFAD_MEDIA_IMAGE . 'opc/' . $size . '/' . $filename['name'];
                            if (file_exists($scaledFile)) {
                                @unlink($scaledFile);
                            }
                        }
                    }
                },
                'upload.presave' => static function (&$path, &$name, $tmpname, $_this, $volume) {
                    $name = mb_strtolower($name);
                },
            ],
            'roots' => [
                // Items volume
                [
                    // make the thumbnails 120px wide, suitable for the nivo slider
                    'tmbSize' => 120,
                    // driver for accessing file system (REQUIRED)
                    'driver'        => 'LocalFileSystem',
                    // path to files (REQUIRED)
                    'path'          => PFAD_ROOT . $mediafilesSubdir,
                    // URL to files (REQUIRED)
                    'URL'           => $mediafilesBaseUrlPath,
                    // to make hash same to Linux one on windows too
                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                    // All Mimetypes not allowed to upload
                    'uploadDeny'    => ['all'],
                    // Mimetype `image` and `text/plain` allowed to upload
                    'uploadAllow'   => ['image',
                                        'video',
                                        'text/plain',
                                        'application/pdf',
                                        'application/msword',
                                        'application/excel',
                                        'application/vnd.ms-excel',
                                        'application/x-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ],
                    // allowed Mimetype `image` and `text/plain` only
                    'uploadOrder'   => ['deny', 'allow'],
                    // disable and hide dot starting files (OPTIONAL)
                    'accessControl' => 'access',
                ],
            ],
        ]));

        $connector->run();
    } else {
        $smarty->assign('mediafilesType', $mediafilesType)
               ->assign('mediafilesSubdir', $mediafilesSubdir)
               ->assign('isCKEditor', $isCKEditor)
               ->assign('CKEditorFuncNum', $CKEditorFuncNum)
               ->assign('templateUrl', Shop::getAdminURL() . '/' . $currentTemplateDir)
               ->assign('mediafilesBaseUrlPath', $mediafilesBaseUrlPath)
               ->display('elfinder.tpl');
    }
}
