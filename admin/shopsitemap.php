<?php

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('SETTINGS_SITEMAP_VIEW', true, true);
if (isset($_POST['speichern']) && Form::validateToken()) {
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(CONF_SITEMAP, $_POST),
        'saveSettings'
    );
    if (GeneralObject::hasCount('nVon', $_POST) && GeneralObject::hasCount('nBis', $_POST)) {
        Shop::Container()->getDB()->query('TRUNCATE TABLE tpreisspannenfilter', ReturnType::AFFECTED_ROWS);
        for ($i = 0; $i < 10; $i++) {
            if ((int)$_POST['nVon'][$i] >= 0 && (int)$_POST['nBis'][$i] > 0) {
                $filter       = new stdClass();
                $filter->nVon = (int)$_POST['nVon'][$i];
                $filter->nBis = (int)$_POST['nBis'][$i];

                Shop::Container()->getDB()->insert('tpreisspannenfilter', $filter);
            }
        }
    }
}

$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_SITEMAP))
    ->display('shopsitemap.tpl');
