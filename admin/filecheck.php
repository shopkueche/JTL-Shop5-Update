<?php

use JTL\Alert\Alert;
use JTL\Backend\FileCheck;
use JTL\Backend\Status;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'filecheck_inc.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('FILECHECK_VIEW', true, true);
$cache = Shop::Container()->getCache();
$cache->flush(Status::CACHE_ID_MODIFIED_FILE_STRUCT);
$cache->flush(Status::CACHE_ID_ORPHANED_FILE_STRUCT);

$fileChecker        = new FileCheck();
$zipArchiveError    = '';
$backupMessage      = '';
$modifiedFilesError = '';
$orphanedFilesError = '';
$md5basePath        = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_SHOPMD5;
$coreMD5HashFile    = $md5basePath . $fileChecker->getVersionString() . '.csv';
$orphanedFilesFile  = $md5basePath . 'deleted_files_' . $fileChecker->getVersionString() . '.csv';
$modifiedFiles      = [];
$orphanedFiles      = [];
$modifiedFilesCount = 0;
$orphanedFilesCount = 0;
$modifiedFilesCheck = $fileChecker->validateCsvFile($coreMD5HashFile, $modifiedFiles, $modifiedFilesCount);
$orphanedFilesCheck = $fileChecker->validateCsvFile($orphanedFilesFile, $orphanedFiles, $orphanedFilesCount);
$alertHelper        = Shop::Container()->getAlertService();
if ($modifiedFilesCheck !== FileCheck::OK) {
    switch ($modifiedFilesCheck) {
        case FileCheck::ERROR_INPUT_FILE_MISSING:
            $modifiedFilesError = __('errorFileNotFound');
            break;
        case FileCheck::ERROR_NO_HASHES_FOUND:
            $modifiedFilesError = __('errorFileListEmpty');
            break;
        default:
            $modifiedFilesError = '';
            break;
    }
}
if ($orphanedFilesCheck !== FileCheck::OK) {
    switch ($orphanedFilesCheck) {
        case FileCheck::ERROR_INPUT_FILE_MISSING:
            $orphanedFilesError = __('errorFileNotFound');
            break;
        case FileCheck::ERROR_NO_HASHES_FOUND:
            $orphanedFilesError = __('errorFileListEmpty');
            break;
        default:
            $orphanedFilesError = '';
            break;
    }
} elseif (Request::verifyGPCDataInt('delete-orphans') === 1 && Form::validateToken()) {
    $backup   = PFAD_ROOT . PFAD_EXPORT_BACKUP . 'orphans_' . date_format(date_create(), 'Y-m-d_H:i:s') . '.zip';
    $count    = $fileChecker->deleteOrphanedFiles($orphanedFiles, $backup);
    $newCount = count($orphanedFiles);
    if ($count === -1) {
        $zipArchiveError = sprintf(__('errorCreatingZipArchive'), $backup);
    } else {
        $backupMessage = sprintf(__('backupText'), $backup, $count);
    }
    if ($newCount > 0) {
        $orphanedFilesError = __('errorNotDeleted');
    }
}

$hasModifiedFiles = !empty($modifiedFilesError) || count($modifiedFiles) > 0;
$hasOrphanedFiles = !empty($orphanedFilesError) || count($orphanedFiles) > 0;
if (!$hasModifiedFiles && !$hasOrphanedFiles) {
    $alertHelper->addAlert(
        Alert::TYPE_NOTE,
        __('fileCheckNoneModifiedOrphanedFiles'),
        'fileCheckNoneModifiedOrphanedFiles'
    );
}
$alertHelper->addAlert(
    Alert::TYPE_INFO,
    $backupMessage,
    'backupMessage',
    ['showInAlertListTemplate' => false]
);
$alertHelper->addAlert(
    Alert::TYPE_ERROR,
    $zipArchiveError,
    'zipArchiveError',
    ['showInAlertListTemplate' => false]
);
$alertHelper->addAlert(
    Alert::TYPE_ERROR,
    $modifiedFilesError,
    'modifiedFilesError',
    ['showInAlertListTemplate' => false]
);
$alertHelper->addAlert(
    Alert::TYPE_ERROR,
    $orphanedFilesError,
    'orphanedFilesError',
    ['showInAlertListTemplate' => false]
);
$smarty->assign('modifiedFilesError', $modifiedFilesError !== '')
    ->assign('orphanedFilesError', $orphanedFilesError !== '')
    ->assign('modifiedFiles', $modifiedFiles)
    ->assign('orphanedFiles', $orphanedFiles)
    ->assign('modifiedFilesCheck', $hasModifiedFiles)
    ->assign('orphanedFilesCheck', $hasOrphanedFiles)
    ->assign('errorsCountModifiedFiles', $modifiedFilesCount)
    ->assign('errorsCountOrphanedFiles', $orphanedFilesCount)
    ->assign('deleteScript', $fileChecker->generateBashScript())
    ->display('filecheck.tpl');
