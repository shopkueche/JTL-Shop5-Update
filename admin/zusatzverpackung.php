<?php

use JTL\Alert\Alert;
use JTL\Backend\AdminAccount;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Pagination;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;

require_once __DIR__ . '/includes/admininclude.php';
/** @global AdminAccount $oAccount */
/** @global JTLSmarty $smarty */

$oAccount->permission('ORDER_PACKAGE_VIEW', true, true);

$step        = 'zusatzverpackung';
$languages   = LanguageHelper::getAllLanguages();
$action      = '';
$alertHelper = Shop::Container()->getAlertService();
$db          = Shop::Container()->getDB();
if (Form::validateToken()) {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
    } elseif (Request::getInt('kVerpackung', -1) >= 0) {
        $action = 'edit';
    }
}

if ($action === 'save') {
    $nameIDX                        = 'cName_' . $languages[0]->getCode();
    $packagingID                    = Request::postInt('kVerpackung');
    $customerGroupIDs               = $_POST['kKundengruppe'] ?? null;
    $packaging                      = new stdClass();
    $packaging->fBrutto             = (float)str_replace(',', '.', $_POST['fBrutto'] ?? 0);
    $packaging->fMindestbestellwert = (float)str_replace(',', '.', $_POST['fMindestbestellwert'] ?? 0);
    $packaging->fKostenfrei         = (float)str_replace(',', '.', $_POST['fKostenfrei'] ?? 0);
    $packaging->kSteuerklasse       = Request::postInt('kSteuerklasse');
    $packaging->nAktiv              = Request::postInt('nAktiv');
    $packaging->cName               = htmlspecialchars(
        strip_tags(trim($_POST[$nameIDX])),
        ENT_COMPAT | ENT_HTML401,
        JTL_CHARSET
    );
    if ($packaging->kSteuerklasse < 0) {
        $packaging->kSteuerklasse = 0;
    }
    if (!(isset($_POST[$nameIDX]) && mb_strlen($_POST[$nameIDX]) > 0)) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorNameMissing'), 'errorNameMissing');
    }
    if (!(is_array($customerGroupIDs) && count($customerGroupIDs) > 0)) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCustomerGroupMissing'), 'errorCustomerGroupMissing');
    }

    if ($alertHelper->alertTypeExists(Alert::TYPE_ERROR)) {
        holdInputOnError($packaging, $customerGroupIDs, $packagingID, $smarty);
        $action = 'edit';
    } else {
        if ((int)$customerGroupIDs[0] === -1) {
            $packaging->cKundengruppe = '-1';
        } else {
            $packaging->cKundengruppe = ';' . implode(';', $customerGroupIDs) . ';';
        }
        // Update?
        if ($packagingID > 0) {
            $db->query(
                'DELETE tverpackung, tverpackungsprache
                    FROM tverpackung
                    LEFT JOIN tverpackungsprache 
                        ON tverpackungsprache.kVerpackung = tverpackung.kVerpackung
                    WHERE tverpackung.kVerpackung = ' . $packagingID,
                ReturnType::AFFECTED_ROWS
            );
            $packaging->kVerpackung = $packagingID;
            $db->insert('tverpackung', $packaging);
        } else {
            $packagingID = $db->insert('tverpackung', $packaging);
        }
        foreach ($languages as $lang) {
            $langCode                 = $lang->getCode();
            $localized                = new stdClass();
            $localized->kVerpackung   = $packagingID;
            $localized->cISOSprache   = $langCode;
            $localized->cName         = !empty($_POST['cName_' . $langCode])
                ? htmlspecialchars($_POST['cName_' . $langCode], ENT_COMPAT | ENT_HTML401, JTL_CHARSET)
                : htmlspecialchars($_POST[$nameIDX], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $localized->cBeschreibung = !empty($_POST['cBeschreibung_' . $langCode])
                ? htmlspecialchars($_POST['cBeschreibung_' . $langCode], ENT_COMPAT | ENT_HTML401, JTL_CHARSET)
                : htmlspecialchars(
                    $_POST['cBeschreibung_' . $languages[0]->getCode()],
                    ENT_COMPAT | ENT_HTML401,
                    JTL_CHARSET
                );
            $db->insert('tverpackungsprache', $localized);
        }
        $alertHelper->addAlert(
            Alert::TYPE_SUCCESS,
            sprintf(__('successPackagingSave'), $_POST[$nameIDX]),
            'successPackagingSave'
        );
    }
} elseif ($action === 'edit' && Request::verifyGPCDataInt('kVerpackung') > 0) { // Editieren
    $packagingID = Request::verifyGPCDataInt('kVerpackung');
    $packaging   = $db->select('tverpackung', 'kVerpackung', $packagingID);

    if (isset($packaging->kVerpackung) && $packaging->kVerpackung > 0) {
        $packaging->oSprach_arr = [];
        $localizations          = $db->selectAll(
            'tverpackungsprache',
            'kVerpackung',
            $packagingID,
            'cISOSprache, cName, cBeschreibung'
        );
        foreach ($localizations as $localization) {
            $packaging->oSprach_arr[$localization->cISOSprache] = $localization;
        }
        $customerGroup                = gibKundengruppeObj($packaging->cKundengruppe);
        $packaging->kKundengruppe_arr = $customerGroup->kKundengruppe_arr;
        $packaging->cKundengruppe_arr = $customerGroup->cKundengruppe_arr;
    }
    $smarty->assign('kVerpackung', $packaging->kVerpackung)
        ->assign('oVerpackungEdit', $packaging);
} elseif ($action === 'delete') {
    if (GeneralObject::hasCount('kVerpackung', $_POST)) {
        foreach ($_POST['kVerpackung'] as $packagingID) {
            $packagingID = (int)$packagingID;
            // tverpackung loeschen
            $db->delete('tverpackung', 'kVerpackung', $packagingID);
            $db->delete('tverpackungsprache', 'kVerpackung', $packagingID);
        }
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successPackagingDelete'), 'successPackagingDelete');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOnePackaging'), 'errorAtLeastOnePackaging');
    }
} elseif ($action === 'refresh') {
    if (isset($_POST['nAktivTMP']) && is_array($_POST['nAktivTMP']) && count($_POST['nAktivTMP']) > 0) {
        foreach ($_POST['nAktivTMP'] as $packagingID) {
            $upd         = new stdClass();
            $upd->nAktiv = isset($_POST['nAktiv']) && in_array($packagingID, $_POST['nAktiv'], true) ? 1 : 0;
            $db->update('tverpackung', 'kVerpackung', (int)$packagingID, $upd);
        }
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successPackagingSaveMultiple'), 'successPackagingSaveMultiple');
    }
}
$taxClasses = $db->query(
    'SELECT * FROM tsteuerklasse',
    ReturnType::ARRAY_OF_OBJECTS
);

$packagingCount = (int)$db->query(
    'SELECT COUNT(kVerpackung) AS cnt
            FROM tverpackung',
    ReturnType::SINGLE_OBJECT
)->cnt;
$itemsPerPage   = 10;
$pagination     = (new Pagination('standard'))
    ->setItemsPerPageOptions([$itemsPerPage, $itemsPerPage * 2, $itemsPerPage * 5])
    ->setItemCount($packagingCount)
    ->assemble();
$packagings     = $db->query(
    'SELECT * FROM tverpackung 
       ORDER BY cName' .
    ($pagination->getLimitSQL() !== '' ? ' LIMIT ' . $pagination->getLimitSQL() : ''),
    ReturnType::ARRAY_OF_OBJECTS
);

foreach ($packagings as $i => $packaging) {
    $customerGroup                = gibKundengruppeObj($packaging->cKundengruppe);
    $packaging->kKundengruppe_arr = $customerGroup->kKundengruppe_arr;
    $packaging->cKundengruppe_arr = $customerGroup->cKundengruppe_arr;
}

$smarty->assign('customerGroups', CustomerGroup::getGroups())
    ->assign('taxClasses', $taxClasses)
    ->assign('packagings', $packagings)
    ->assign('step', $step)
    ->assign('pagination', $pagination)
    ->assign('action', $action)
    ->display('zusatzverpackung.tpl');

/**
 * @param string $groupString
 * @return stdClass
 */
function gibKundengruppeObj($groupString)
{
    $customerGroup = new stdClass();
    $tmpIDs        = [];
    $tmpNames      = [];

    if (mb_strlen($groupString) > 0) {
        // Kundengruppen holen
        $data             = Shop::Container()->getDB()->query(
            'SELECT kKundengruppe, cName FROM tkundengruppe',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $customerGroupIDs = array_map('\intval', array_filter(explode(';', $groupString)));
        if (!in_array(-1, $customerGroupIDs, true)) {
            foreach ($customerGroupIDs as $id) {
                $id       = (int)$id;
                $tmpIDs[] = $id;
                foreach ($data as $customerGroup) {
                    if ((int)$customerGroup->kKundengruppe === $id) {
                        $tmpNames[] = $customerGroup->cName;
                        break;
                    }
                }
            }
        } elseif (count($data) > 0) {
            foreach ($data as $customerGroup) {
                $tmpIDs[]   = $customerGroup->kKundengruppe;
                $tmpNames[] = $customerGroup->cName;
            }
        }
    }
    $customerGroup->kKundengruppe_arr = $tmpIDs;
    $customerGroup->cKundengruppe_arr = $tmpNames;

    return $customerGroup;
}

/**
 * @param stdClass                $packaging
 * @param array|null            $customerGroupIDs
 * @param int                   $packagingID
 * @param JTLSmarty $smarty
 * @return void
 */
function holdInputOnError(stdClass $packaging, ?array $customerGroupIDs, int $packagingID, JTLSmarty $smarty)
{
    $packaging->oSprach_arr = [];
    foreach ($_POST as $key => $value) {
        if (mb_strpos($key, 'cName') === false) {
            continue;
        }
        $cISO                                 = explode('cName_', $key)[1];
        $idx                                  = 'cBeschreibung_' . $cISO;
        $packaging->oSprach_arr[$cISO]        = new stdClass();
        $packaging->oSprach_arr[$cISO]->cName = $value;
        if (isset($_POST[$idx])) {
            $packaging->oSprach_arr[$cISO]->cBeschreibung = $_POST[$idx];
        }
    }

    if (is_array($customerGroupIDs) && $customerGroupIDs[0] !== '-1') {
        $packaging->cKundengruppe     = ';' . implode(';', $customerGroupIDs) . ';';
        $customerGroup                = gibKundengruppeObj($packaging->cKundengruppe);
        $packaging->kKundengruppe_arr = $customerGroup->kKundengruppe_arr;
        $packaging->cKundengruppe_arr = $customerGroup->cKundengruppe_arr;
    } else {
        $packaging->cKundengruppe = '-1';
    }

    $smarty->assign('oVerpackungEdit', $packaging)
        ->assign('kVerpackung', $packagingID);
}
