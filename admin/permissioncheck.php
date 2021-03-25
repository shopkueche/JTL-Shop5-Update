<?php

use JTL\Backend\Status;
use Systemcheck\Platform\Filesystem;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('PERMISSIONCHECK_VIEW', true, true);
Shop::Container()->getCache()->flush(Status::CACHE_ID_FOLDER_PERMISSIONS);

$fsCheck = new Filesystem(PFAD_ROOT); // to get all folders which need to be writable

$smarty->assign('cDirAssoc_arr', $fsCheck->getFoldersChecked())
       ->assign('oStat', $fsCheck->getFolderStats())
       ->display('permissioncheck.tpl');
