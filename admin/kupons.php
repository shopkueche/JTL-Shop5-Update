<?php

use JTL\Alert\Alert;
use JTL\Checkout\Kupon;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Filter;
use JTL\Pagination\Operation;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('ORDER_COUPON_VIEW', true, true);
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'kupons_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_exporter_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_importer_inc.php';

$action      = '';
$tab         = Kupon::TYPE_STANDARD;
$languages   = LanguageHelper::getAllLanguages();
$coupon      = null;
$alertHelper = Shop::Container()->getAlertService();
$errors      = [];
$res         = handleCsvImportAction('kupon', static function ($obj, &$importDeleteDone, $importType = 2) {
    $db = Shop::Container()->getDB();
    if ($importType === 0 && $importDeleteDone === false) {
        $db->query('TRUNCATE TABLE tkupon', ReturnType::AFFECTED_ROWS);
        $db->query('TRUNCATE TABLE tkuponsprache', ReturnType::AFFECTED_ROWS);
        $importDeleteDone = true;
    }

    $couponNames = [];

    foreach (get_object_vars($obj) as $key => $val) {
        if (mb_strpos($key, 'cName_') === 0) {
            $couponNames[mb_substr($key, 6)] = $val;
            unset($obj->$key);
        }
    }

    if (isset($obj->cCode) && $db->select('tkupon', 'cCode', $obj->cCode) !== null) {
        return false;
    }

    unset($obj->dLastUse);
    $couponID = $db->insert('tkupon', $obj);
    if ($couponID === 0) {
        return false;
    }

    foreach ($couponNames as $key => $val) {
        $res = $db->insert('tkuponsprache', (object)['kKupon' => $couponID, 'cISOSprache' => $key, 'cName' => $val]);
        if ($res === 0) {
            return false;
        }
    }

    return true;
}, [], null, 2, $errors);

if ($res > 0) {
    foreach ($errors as $key => $error) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, $error, 'errorImportCSV_' . $key);
    }
} elseif ($res === 0) {
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successImportCSV'), 'successImportCSV');
}

if (Form::validateToken()) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'speichern') {
            $action = 'speichern';
        } elseif ($_POST['action'] === 'loeschen') {
            $action = 'loeschen';
        }
    } elseif (Request::getInt('kKupon', -1) >= 0) {
        $action = 'bearbeiten';
    }
}

if ($action === 'bearbeiten') {
    $couponID = (int)($_GET['kKupon'] ?? $_POST['kKuponBearbeiten'] ?? 0);
    if ($couponID > 0) {
        $coupon = getCoupon($couponID);
    } else {
        $coupon = createNewCoupon($_REQUEST['cKuponTyp']);
    }
} elseif ($action === 'speichern') {
    $coupon       = createCouponFromInput();
    $couponErrors = validateCoupon($coupon);
    if (count($couponErrors) > 0) {
        // Es gab Fehler bei der Validierung => weiter bearbeiten
        $errorMessage = __('errorCheckInput') . ':<ul>';

        foreach ($couponErrors as $couponError) {
            $errorMessage .= '<li>' . $couponError . '</li>';
        }

        $errorMessage .= '</ul>';
        $action        = 'bearbeiten';
        $alertHelper->addAlert(Alert::TYPE_ERROR, $errorMessage, 'errorCheckInput');
        augmentCoupon($coupon);
    } elseif (saveCoupon($coupon, $languages) > 0) {// Validierung erfolgreich => Kupon speichern
        // erfolgreich gespeichert => evtl. Emails versenden
        if (isset($_POST['informieren'])
            && $_POST['informieren'] === 'Y'
            && ($coupon->cKuponTyp === Kupon::TYPE_STANDARD || $coupon->cKuponTyp === Kupon::TYPE_SHIPPING)
            && $coupon->cAktiv === 'Y'
        ) {
            informCouponCustomers($coupon);
        }
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successCouponSave'), 'successCouponSave');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCouponSave'), 'errorCouponSave');
    }
} elseif ($action === 'loeschen') {
    // Kupons loeschen
    if (GeneralObject::hasCount('kKupon_arr', $_POST)) {
        $couponIDs = array_map('\intval', $_POST['kKupon_arr']);
        if (loescheKupons($couponIDs)) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successCouponDelete'), 'successCouponDelete');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCouponDelete'), 'errorCouponDelete');
        }
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneCoupon'), 'errorAtLeastOneCoupon');
    }
}
if ($action === 'bearbeiten') {
    $taxClasses    = Shop::Container()->getDB()->query(
        'SELECT kSteuerklasse, cName FROM tsteuerklasse',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $manufacturers = getManufacturers($coupon->cHersteller);
    $categories    = getCategories($coupon->cKategorien);
    $customerIDs   = array_filter(
        Text::parseSSKint($coupon->cKunden),
        static function ($customerID) {
            return (int)$customerID > 0;
        }
    );
    if ($coupon->kKupon > 0) {
        $names = getCouponNames((int)$coupon->kKupon);
    } else {
        $names = [];
        foreach ($languages as $language) {
            $postVarName                = 'cName_' . $language->getIso();
            $names[$language->getIso()] = Request::postVar($postVarName, '') !== ''
                ? $_POST[$postVarName]
                : $coupon->cName;
        }
    }

    $smarty->assign('taxClasses', $taxClasses)
        ->assign('customerGroups', CustomerGroup::getGroups())
        ->assign('manufacturers', $manufacturers)
        ->assign('categories', $categories)
        ->assign('customerIDs', $customerIDs)
        ->assign('couponNames', $names)
        ->assign('oKupon', $coupon);
} else {
    // Seite: Uebersicht
    if (Request::hasGPCData('tab')) {
        $tab = Request::verifyGPDataString('tab');
    } elseif (Request::hasGPCData('cKuponTyp')) {
        $tab = Request::verifyGPDataString('cKuponTyp');
    }

    deactivateOutdatedCoupons();
    deactivateExhaustedCoupons();

    $filterStandard = new Filter(Kupon::TYPE_STANDARD);
    $filterStandard->addTextfield(__('name'), 'cName');
    $filterStandard->addTextfield(__('code'), 'cCode');
    $activeSelection = $filterStandard->addSelectfield(__('status'), 'cAktiv');
    $activeSelection->addSelectOption(__('all'), '');
    $activeSelection->addSelectOption(__('active'), 'Y', Operation::EQUALS);
    $activeSelection->addSelectOption(__('inactive'), 'N', Operation::EQUALS);
    $filterStandard->assemble();

    $filterVersand = new Filter(Kupon::TYPE_SHIPPING);
    $filterVersand->addTextfield(__('name'), 'cName');
    $filterVersand->addTextfield(__('code'), 'cCode');
    $activeSelection = $filterVersand->addSelectfield(__('status'), 'cAktiv');
    $activeSelection->addSelectOption(__('all'), '');
    $activeSelection->addSelectOption(__('active'), 'Y', Operation::EQUALS);
    $activeSelection->addSelectOption(__('inactive'), 'N', Operation::EQUALS);
    $filterVersand->assemble();

    $filterNeukunden = new Filter(Kupon::TYPE_NEWCUSTOMER);
    $filterNeukunden->addTextfield(__('name'), 'cName');
    $activeSelection = $filterNeukunden->addSelectfield(__('status'), 'cAktiv');
    $activeSelection->addSelectOption(__('all'), '');
    $activeSelection->addSelectOption(__('active'), 'Y', Operation::EQUALS);
    $activeSelection->addSelectOption(__('inactive'), 'N', Operation::EQUALS);
    $filterNeukunden->assemble();

    $sortByOptions = [
        ['cName', __('name')],
        ['cCode', __('code')],
        ['nVerwendungenBisher', __('curmaxusage')],
        ['dLastUse', __('lastUsed')]
    ];


    $nKuponStandardCount  = getCouponCount(Kupon::TYPE_STANDARD, $filterStandard->getWhereSQL());
    $nKuponVersandCount   = getCouponCount(Kupon::TYPE_SHIPPING, $filterVersand->getWhereSQL());
    $nKuponNeukundenCount = getCouponCount(Kupon::TYPE_NEWCUSTOMER, $filterNeukunden->getWhereSQL());
    $nKuponStandardTotal  = getCouponCount(Kupon::TYPE_STANDARD);
    $nKuponVersandTotal   = getCouponCount(Kupon::TYPE_SHIPPING);
    $nKuponNeukundenTotal = getCouponCount(Kupon::TYPE_NEWCUSTOMER);

    handleCsvExportAction(
        Kupon::TYPE_STANDARD,
        Kupon::TYPE_STANDARD . '.csv',
        static function () use ($filterStandard) {
            return getExportableCoupons(Kupon::TYPE_STANDARD, $filterStandard->getWhereSQL());
        },
        [],
        ['kKupon']
    );
    handleCsvExportAction(
        Kupon::TYPE_SHIPPING,
        Kupon::TYPE_SHIPPING . '.csv',
        static function () use ($filterVersand) {
            return getExportableCoupons(Kupon::TYPE_SHIPPING, $filterVersand->getWhereSQL());
        },
        [],
        ['kKupon']
    );
    handleCsvExportAction(
        Kupon::TYPE_NEWCUSTOMER,
        Kupon::TYPE_NEWCUSTOMER . '.csv',
        static function () use ($filterNeukunden) {
            return getExportableCoupons(Kupon::TYPE_NEWCUSTOMER, $filterNeukunden->getWhereSQL());
        },
        [],
        ['kKupon']
    );
    $paginationStandard  = (new Pagination(Kupon::TYPE_STANDARD))
        ->setSortByOptions($sortByOptions)
        ->setItemCount($nKuponStandardCount)
        ->assemble();
    $paginationVersand   = (new Pagination(Kupon::TYPE_SHIPPING))
        ->setSortByOptions($sortByOptions)
        ->setItemCount($nKuponVersandCount)
        ->assemble();
    $paginationNeukunden = (new Pagination(Kupon::TYPE_NEWCUSTOMER))
        ->setSortByOptions($sortByOptions)
        ->setItemCount($nKuponNeukundenCount)
        ->assemble();

    $standardCoupons    = getCoupons(
        Kupon::TYPE_STANDARD,
        $filterStandard->getWhereSQL(),
        $paginationStandard->getOrderSQL(),
        $paginationStandard->getLimitSQL()
    );
    $shippingCoupons    = getCoupons(
        Kupon::TYPE_SHIPPING,
        $filterVersand->getWhereSQL(),
        $paginationVersand->getOrderSQL(),
        $paginationVersand->getLimitSQL()
    );
    $newCustomerCoupons = getCoupons(
        Kupon::TYPE_NEWCUSTOMER,
        $filterNeukunden->getWhereSQL(),
        $paginationNeukunden->getOrderSQL(),
        $paginationNeukunden->getLimitSQL()
    );

    $smarty->assign('tab', $tab)
           ->assign('oFilterStandard', $filterStandard)
           ->assign('oFilterVersand', $filterVersand)
           ->assign('oFilterNeukunden', $filterNeukunden)
           ->assign('oPaginationStandard', $paginationStandard)
           ->assign('oPaginationVersandkupon', $paginationVersand)
           ->assign('oPaginationNeukundenkupon', $paginationNeukunden)
           ->assign('oKuponStandard_arr', $standardCoupons)
           ->assign('oKuponVersandkupon_arr', $shippingCoupons)
           ->assign('oKuponNeukundenkupon_arr', $newCustomerCoupons)
           ->assign('nKuponStandardCount', $nKuponStandardTotal)
           ->assign('nKuponVersandCount', $nKuponVersandTotal)
           ->assign('nKuponNeukundenCount', $nKuponNeukundenTotal);
}

$smarty->assign('action', $action)
       ->assign('couponTypes', Kupon::getCouponTypes())
       ->display('kupons.tpl');
