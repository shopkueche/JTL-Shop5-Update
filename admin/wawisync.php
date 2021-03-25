<?php

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('WAWI_SYNC_VIEW', true, true);

$alertHelper = Shop::Container()->getAlertService();

if (isset($_POST['wawi-pass'], $_POST['wawi-user']) && Form::validateToken()) {
    $passwordService = Shop::Container()->getPasswordService();
    if ($passwordService->hasOnlyValidCharacters($_POST['wawi-pass'])) {
        $passInfo   = $passwordService->getInfo($_POST['wawi-pass']);
        $upd        = new stdClass();
        $upd->cName = $_POST['wawi-user'];
        $upd->cPass = $passInfo['algo'] > 0
            ? $_POST['wawi-pass'] // hashed password was not changed
            : $passwordService->hash($_POST['wawi-pass']); // new clear text password was given

        Shop::Container()->getDB()->queryPrepared(
            'INSERT INTO `tsynclogin` (kSynclogin, cName, cPass)
                VALUES (1, :cName, :cPass)
                ON DUPLICATE KEY UPDATE
                cName = :cName,
                cPass = :cPass',
            ['cName' => $upd->cName, 'cPass' => $upd->cPass],
            ReturnType::AFFECTED_ROWS
        );

        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successConfigSave'), 'successConfigSave');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorInvalidPassword'), 'errorInvalidPassword');
    }
}

$user = Shop::Container()->getDB()->select('tsynclogin', 'kSynclogin', 1);
$smarty->assign('wawiuser', $user->cName)
       ->assign('wawipass', $user->cPass)
       ->display('wawisync.tpl');
