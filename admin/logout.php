<?php

use JTL\Helpers\Form;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */

if (Form::validateToken()) {
    $oAccount->logout();
}
$oAccount->redirectOnFailure();
