<?php
require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */

$oAccount->permission('SETTINGS_META_KEYWORD_BLACKLIST_VIEW', true, true);

require_once __DIR__ . '/globalemetaangaben.php';
