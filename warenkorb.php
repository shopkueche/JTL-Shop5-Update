<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Cart\CartHelper;
use JTL\Cart\PersistentCart;
use JTL\Catalog\Product\Preise;
use JTL\Checkout\Kupon;
use JTL\DB\ReturnType;
use JTL\Extensions\Upload\Upload;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Session\Frontend;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'warenkorb_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';

$warning = '';
$smarty  = Shop::Smarty();
$conf    = Shop::getSettings([
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KAUFABWICKLUNG,
    CONF_KUNDEN,
    CONF_ARTIKELUEBERSICHT,
    CONF_SONSTIGES
]);
Shop::setPageType(PAGE_WARENKORB);
$linkHelper      = Shop::Container()->getLinkService();
$couponCodeValid = true;
$cart            = Frontend::getCart();
$kLink           = $linkHelper->getSpecialPageID(LINKTYP_WARENKORB);
$link            = $linkHelper->getPageLink($kLink);
$alertHelper     = Shop::Container()->getAlertService();
$valid           = Form::validateToken();
// Warenkorbaktualisierung?
if ($valid) {
    CartHelper::applyCartChanges();
}
CartHelper::validateCartConfig();
pruefeGuthabenNutzen();
if ($valid && isset($_POST['land'], $_POST['plz'])
    && !ShippingMethod::getShippingCosts($_POST['land'], $_POST['plz'], $warning)
) {
    $warning = Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages');
}
if ($valid
    && $cart !== null
    && isset($_POST['Kuponcode'])
    && mb_strlen($_POST['Kuponcode']) > 0
    && $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0
) {
    // Kupon darf nicht im leeren Warenkorb eingelöst werden
    $coupon            = new Kupon();
    $coupon            = $coupon->getByCode($_POST['Kuponcode']);
    $invalidCouponCode = 11;
    if ($coupon !== false && $coupon->kKupon > 0) {
        $couponError       = Kupon::checkCoupon($coupon);
        $check             = angabenKorrekt($couponError);
        $invalidCouponCode = 0;
        executeHook(HOOK_WARENKORB_PAGE_KUPONANNEHMEN_PLAUSI, [
            'error'        => &$couponError,
            'nReturnValue' => &$check
        ]);
        if ($check) {
            if ($coupon->cKuponTyp === Kupon::TYPE_STANDARD) {
                Kupon::acceptCoupon($coupon);
                executeHook(HOOK_WARENKORB_PAGE_KUPONANNEHMEN);
            } elseif (!empty($coupon->kKupon) && $coupon->cKuponTyp === Kupon::TYPE_SHIPPING) {
                $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
                $_SESSION['oVersandfreiKupon'] = $coupon;
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    Shop::Lang()->get('couponSucc1') . ' ' .
                        trim(str_replace(';', ', ', $coupon->cLieferlaender), ', '),
                    'shippingFreeSuccess'
                );
            }
        }
    }

    $smarty->assign('invalidCouponCode', Kupon::mapCouponErrorMessage($couponError['ungueltig'] ?? $invalidCouponCode));
}
// Kupon nicht mehr verfügbar. Redirect im Bestellabschluss. Fehlerausgabe
if (isset($_SESSION['checkCouponResult'])) {
    $couponCodeValid = false;
    $couponError     = $_SESSION['checkCouponResult'];
    unset($_SESSION['checkCouponResult']);
    $smarty->assign('cKuponfehler', $couponError['ungueltig']);
}
if ($valid && isset($_POST['gratis_geschenk'], $_POST['gratisgeschenk']) && (int)$_POST['gratis_geschenk'] === 1) {
    $giftID = (int)$_POST['gratisgeschenk'];
    $gift   = Shop::Container()->getDB()->query(
        'SELECT tartikelattribut.kArtikel, tartikel.fLagerbestand, 
            tartikel.cLagerKleinerNull, tartikel.cLagerBeachten
            FROM tartikelattribut
                JOIN tartikel 
                    ON tartikel.kArtikel = tartikelattribut.kArtikel
                WHERE tartikelattribut.kArtikel = ' . $giftID . "
                AND tartikelattribut.cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
                AND CAST(tartikelattribut.cWert AS DECIMAL) <= " .
        $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true),
        ReturnType::SINGLE_OBJECT
    );
    if (isset($gift->kArtikel) && $gift->kArtikel > 0) {
        if ($gift->fLagerbestand <= 0 && $gift->cLagerKleinerNull === 'N' && $gift->cLagerBeachten === 'Y') {
            $warning = Shop::Lang()->get('freegiftsNostock', 'errorMessages');
        } else {
            executeHook(HOOK_WARENKORB_PAGE_GRATISGESCHENKEINFUEGEN);
            $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_GRATISGESCHENK)
                 ->fuegeEin($giftID, 1, [], C_WARENKORBPOS_TYP_GRATISGESCHENK);
            PersistentCart::addToCheck($giftID, 1, [], '', 0, C_WARENKORBPOS_TYP_GRATISGESCHENK);
        }
    }
}
if (($res = Request::getInt('fillOut', -1)) > -1) {
    $mbw = Frontend::getCustomerGroup()->getAttribute(KNDGRP_ATTRIBUT_MINDESTBESTELLWERT);
    if ($res === 9 && $mbw > 0 && $cart->gibGesamtsummeWaren(true, false) < $mbw) {
        $warning = Shop::Lang()->get('minordernotreached', 'checkout') . ' ' . Preise::getLocalizedPriceString($mbw);
    } elseif ($res === 8) {
        $warning = Shop::Lang()->get('orderNotPossibleNow', 'checkout');
    } elseif ($res === 3) {
        $warning = Shop::Lang()->get('yourbasketisempty', 'checkout');
    } elseif ($res === 10) {
        $warning = Shop::Lang()->get('missingProducts', 'checkout');
    } elseif ($res === UPLOAD_ERROR_NEED_UPLOAD) {
        $warning = Shop::Lang()->get('missingFilesUpload', 'checkout');
    }
}
$customerGroupID = ($id = Frontend::getCustomer()->kKundengruppe) > 0
    ? $id
    : Frontend::getCustomerGroup()->getID();
$cCanonicalURL   = $linkHelper->getStaticRoute('warenkorb.php');
$uploads         = Upload::gibWarenkorbUploads($cart);
$maxSize         = Upload::uploadMax();

//alerts
if (($quickBuyNote = CartHelper::checkQuickBuy()) !== '') {
    $alertHelper->addAlert(Alert::TYPE_INFO, $quickBuyNote, 'quickBuyNote');
}
if (!empty($_SESSION['Warenkorbhinweise'])) {
    foreach ($_SESSION['Warenkorbhinweise'] as $key => $cartNotice) {
        $alertHelper->addAlert(Alert::TYPE_WARNING, $cartNotice, 'cartNotice' . $key);
    }
    unset($_SESSION['Warenkorbhinweise']);
}
if ($warning !== '') {
    $alertHelper->addAlert(Alert::TYPE_DANGER, $warning, 'cartWarning', ['id' => 'msgWarning']);
}
if (($orderAmountStock = CartHelper::checkOrderAmountAndStock($conf)) !== '') {
    $alertHelper->addAlert(Alert::TYPE_WARNING, $orderAmountStock, 'orderAmountStock');
}

CartHelper::addVariationPictures($cart);
$smarty->assign('MsgWarning', $warning)
       ->assign('nMaxUploadSize', $maxSize)
       ->assign('cMaxUploadSize', Upload::formatGroesse($maxSize))
       ->assign('oUploadSchema_arr', $uploads)
       ->assign('Link', $link)
       ->assign('laender', ShippingMethod::getPossibleShippingCountries($customerGroupID))
       ->assign('KuponMoeglich', Kupon::couponsAvailable())
       ->assign('currentCoupon', Shop::Lang()->get('currentCoupon', 'checkout'))
       ->assign('currentCouponName', (!empty($_SESSION['Kupon']->translationList)
           ? $_SESSION['Kupon']->translationList
           : null))
       ->assign('currentShippingCouponName', (!empty($_SESSION['oVersandfreiKupon']->translationList)
           ? $_SESSION['oVersandfreiKupon']->translationList
           : null))
       ->assign('xselling', CartHelper::getXSelling())
       ->assign('oArtikelGeschenk_arr', CartHelper::getFreeGifts($conf))
       ->assign('C_WARENKORBPOS_TYP_ARTIKEL', C_WARENKORBPOS_TYP_ARTIKEL)
       ->assign('C_WARENKORBPOS_TYP_GRATISGESCHENK', C_WARENKORBPOS_TYP_GRATISGESCHENK)
       ->assign('KuponcodeUngueltig', !$couponCodeValid)
       ->assign('Warenkorb', $cart);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_WARENKORB_PAGE);

$smarty->display('basket/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
