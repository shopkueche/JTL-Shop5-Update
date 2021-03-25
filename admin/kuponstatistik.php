<?php

use JTL\Catalog\Product\Preise;
use JTL\Checkout\KuponBestellung;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('STATS_COUPON_VIEW', true, true);
$step      = 'kuponstatistik_uebersicht';
$cWhere    = '';
$coupons   = Shop::Container()->getDB()->query(
    'SELECT kKupon, cName FROM tkupon ORDER BY cName DESC',
    ReturnType::ARRAY_OF_ASSOC_ARRAYS
);
$oDateShop = Shop::Container()->getDB()->query(
    'SELECT MIN(DATE(dZeit)) AS startDate FROM tbesucherarchiv',
    ReturnType::SINGLE_OBJECT
);
$startDate = DateTime::createFromFormat('Y-m-j', $oDateShop->startDate);
$endDate   = DateTime::createFromFormat('Y-m-j', date('Y-m-j'));

if (isset($_POST['formFilter']) && $_POST['formFilter'] > 0 && Form::validateToken()) {
    if (Request::postInt('kKupon') > -1) {
        $couponID = Request::postInt('kKupon');
        $cWhere   = '(SELECT kKupon 
                        FROM tkuponbestellung 
                        WHERE tkuponbestellung.kBestellung = tbestellung.kBestellung 
                        LIMIT 0, 1
                    ) = ' . $couponID . ' AND';
        foreach ($coupons as $key => $value) {
            if ((int)$value['kKupon'] === $couponID) {
                $coupons[$key]['aktiv'] = 1;
                break;
            }
        }
    }

    $dateRanges = explode(' - ', $_POST['daterange']);
    $endDate    = (DateTime::createFromFormat('Y-m-j', $dateRanges[1])
        && (DateTime::createFromFormat('Y-m-j', $dateRanges[1]) >= DateTime::createFromFormat('Y-m-j', $dateRanges[0]))
        && (DateTime::createFromFormat('Y-m-j', $dateRanges[1]) < DateTime::createFromFormat('Y-m-j', date('Y-m-j'))))
        ? DateTime::createFromFormat('Y-m-j', $dateRanges[1])
        : DateTime::createFromFormat('Y-m-j', date('Y-m-j'));

    if (DateTime::createFromFormat('Y-m-j', $dateRanges[0])
        && (DateTime::createFromFormat('Y-m-j', $dateRanges[0]) <= $endDate)
    ) {
        $startDate = DateTime::createFromFormat('Y-m-j', $dateRanges[0]);
    } else {
        $oneMonth  = clone $endDate;
        $oneMonth  = $oneMonth->modify('-1month');
        $startDate = DateTime::createFromFormat('Y-m-j', $oneMonth->format('Y-m-d'));
    }
} else {
    $oneMonth  = $endDate;
    $oneMonth  = $oneMonth->modify('-1week');
    $startDate = DateTime::createFromFormat('Y-m-j', $oneMonth->format('Y-m-d'));
    $endDate   = DateTime::createFromFormat('Y-m-j', date('Y-m-j'));
}

$dStart = $startDate->format('Y-m-d 00:00:00');
$dEnd   = $endDate->format('Y-m-d 23:59:59');

$usedCouponsOrder = KuponBestellung::getOrdersWithUsedCoupons(
    $dStart,
    $dEnd,
    (int)Request::verifyGPDataString('kKupon')
);

$orderCount            = (int)Shop::Container()->getDB()->query(
    "SELECT COUNT(*) AS nCount
        FROM tbestellung
        WHERE dErstellt BETWEEN '" . $dStart . "'
            AND '" . $dEnd . "'
            AND tbestellung.cStatus != " . BESTELLUNG_STATUS_STORNO,
    ReturnType::SINGLE_OBJECT
)->nCount;
$countUsedCouponsOrder = 0;
$countCustomers        = 0;
$shoppingCartAmountAll = 0;
$couponAmountAll       = 0;
$tmpUser               = [];
$date                  = [];
foreach ($usedCouponsOrder as $key => $usedCouponOrder) {
    $usedCouponOrder['kKunde']           = isset($usedCouponOrder['kKunde']) ? (int)$usedCouponOrder['kKunde'] : 0;
    $customer                            = new Customer($usedCouponOrder['kKunde']);
    $usedCouponsOrder[$key]['cUserName'] = $customer->cVorname . ' ' . $customer->cNachname;
    unset($customer);
    $usedCouponsOrder[$key]['nCouponValue']        =
        Preise::getLocalizedPriceWithoutFactor($usedCouponOrder['fKuponwertBrutto']);
    $usedCouponsOrder[$key]['nShoppingCartAmount'] =
        Preise::getLocalizedPriceWithoutFactor($usedCouponOrder['fGesamtsummeBrutto']);
    $usedCouponsOrder[$key]['cOrderPos_arr']       = Shop::Container()->getDB()->query(
        "SELECT CONCAT_WS(' ',wk.cName,wk.cHinweis) AS cName,
            wk.fPreis+(wk.fPreis/100*wk.fMwSt) AS nPreis, wk.nAnzahl
            FROM twarenkorbpos AS wk
            LEFT JOIN tbestellung AS bs 
                ON wk.kWarenkorb = bs.kWarenkorb
            WHERE bs.kBestellung = " . (int)$usedCouponOrder['kBestellung'],
        ReturnType::ARRAY_OF_ASSOC_ARRAYS
    );
    foreach ($usedCouponsOrder[$key]['cOrderPos_arr'] as $posKey => $value) {
        $usedCouponsOrder[$key]['cOrderPos_arr'][$posKey]['nAnzahl']      =
            str_replace('.', ',', number_format($value['nAnzahl'], 2));
        $usedCouponsOrder[$key]['cOrderPos_arr'][$posKey]['nPreis']       =
            Preise::getLocalizedPriceWithoutFactor($value['nPreis']);
        $usedCouponsOrder[$key]['cOrderPos_arr'][$posKey]['nGesamtPreis'] =
            Preise::getLocalizedPriceWithoutFactor($value['nAnzahl'] * $value['nPreis']);
    }

    $countUsedCouponsOrder++;
    $shoppingCartAmountAll += $usedCouponOrder['fGesamtsummeBrutto'];
    $couponAmountAll       += (float)$usedCouponOrder['fKuponwertBrutto'];
    if (!in_array($usedCouponOrder['kKunde'], $tmpUser)) {
        $countCustomers++;
        $tmpUser[] = $usedCouponOrder['kKunde'];
    }
    $date[$key] = $usedCouponOrder['dErstellt'];
}
array_multisort($date, SORT_DESC, $usedCouponsOrder);

$percentCountUsedCoupons = $orderCount > 0
    ? number_format(100 / $orderCount * $countUsedCouponsOrder, 2)
    : 0;
$overview                = [
    'nCountUsedCouponsOrder'   => $countUsedCouponsOrder,
    'nCountCustomers'          => $countCustomers,
    'nCountOrder'              => $orderCount,
    'nPercentCountUsedCoupons' => $percentCountUsedCoupons,
    'nShoppingCartAmountAll'   => Preise::getLocalizedPriceWithoutFactor($shoppingCartAmountAll),
    'nCouponAmountAll'         => Preise::getLocalizedPriceWithoutFactor($couponAmountAll)
];

$smarty->assign('overview_arr', $overview)
    ->assign('usedCouponsOrder', $usedCouponsOrder)
    ->assign('startDateShop', $oDateShop->startDate)
    ->assign('startDate', $startDate->format('Y-m-d'))
    ->assign('endDate', $endDate->format('Y-m-d'))
    ->assign('coupons_arr', $coupons)
    ->assign('step', $step)
    ->display('kuponstatistik.tpl');
