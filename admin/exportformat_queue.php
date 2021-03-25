<?php

use JTL\Helpers\Form;

require_once __DIR__ . '/includes/admininclude.php';

/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('EXPORT_SCHEDULE_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_queue_inc.php';

$action   = isset($_GET['action'])
    ? [$_GET['action'] => 1]
    : ($_POST['action'] ?? ['uebersicht' => 1]);
$step     = 'uebersicht';
$messages = [
    'notice' => '',
    'error'  => ''
];
if (isset($action['erstellen']) && (int)$action['erstellen'] === 1 && Form::validateToken()) {
    $step = exportformatQueueActionErstellen($smarty);
}
if (isset($action['editieren']) && (int)$action['editieren'] === 1 && Form::validateToken()) {
    $step = exportformatQueueActionEditieren($smarty, $messages);
}
if (isset($action['loeschen']) && (int)$action['loeschen'] === 1 && Form::validateToken()) {
    $step = exportformatQueueActionLoeschen($messages);
}
if (isset($action['triggern']) && (int)$action['triggern'] === 1 && Form::validateToken()) {
    $step = exportformatQueueActionTriggern($messages);
}
if (isset($action['fertiggestellt']) && (int)$action['fertiggestellt'] === 1 && Form::validateToken()) {
    $step = exportformatQueueActionFertiggestellt($smarty);
}
if (isset($action['erstellen_eintragen']) && (int)$action['erstellen_eintragen'] === 1 && Form::validateToken()) {
    $step = exportformatQueueActionErstellenEintragen($smarty, $messages);
}

exportformatQueueFinalize($step, $smarty, $messages);
