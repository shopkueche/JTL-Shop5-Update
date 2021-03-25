<?php

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty     $smarty */

$oAccount->permission('SETTINGS_SEARCH_VIEW', true, true);
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'suche_inc.php';

$query = $_GET['cSuche'];

adminSearch(trim($query), true);
