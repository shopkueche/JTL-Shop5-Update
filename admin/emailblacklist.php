<?php

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('SETTINGS_EMAIL_BLACKLIST_VIEW', true, true);
$step = 'emailblacklist';
if (Request::postInt('einstellungen') > 0) {
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(CONF_EMAILBLACKLIST, $_POST),
        'saveSettings'
    );
}
if (Request::postInt('emailblacklist') === 1 && Form::validateToken()) {
    $addresses = explode(';', $_POST['cEmail']);
    if (is_array($addresses) && count($addresses) > 0) {
        Shop::Container()->getDB()->query('TRUNCATE temailblacklist', ReturnType::AFFECTED_ROWS);
        foreach ($addresses as $mail) {
            $mail = strip_tags(trim($mail));
            if (mb_strlen($mail) > 0) {
                Shop::Container()->getDB()->insert('temailblacklist', (object)['cEmail' => $mail]);
            }
        }
    }
}
$blacklist = Shop::Container()->getDB()->query(
    'SELECT * 
        FROM temailblacklist',
    ReturnType::ARRAY_OF_OBJECTS
);
$blocked   = Shop::Container()->getDB()->query(
    "SELECT *, DATE_FORMAT(dLetzterBlock, '%d.%m.%Y %H:%i') AS Datum
        FROM temailblacklistblock
        ORDER BY dLetzterBlock DESC
        LIMIT 100",
    ReturnType::ARRAY_OF_OBJECTS
);

$smarty->assign('blacklist', $blacklist)
       ->assign('blocked', $blocked)
       ->assign('config', getAdminSectionSettings(CONF_EMAILBLACKLIST))
       ->assign('step', $step)
       ->display('emailblacklist.tpl');
