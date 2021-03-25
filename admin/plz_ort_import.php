<?php
/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
require_once __DIR__ . '/includes/plz_ort_import_inc.php';

$oAccount->permission('PLZ_ORT_IMPORT_VIEW', true, true);

$action   = 'index';
$messages = [
    'notice' => '',
    'error'  => '',
];

plzimportActionIndex($smarty, $messages);
plzimportFinalize($smarty, $messages);
