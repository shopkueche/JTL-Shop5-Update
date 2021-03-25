<?php declare(strict_types=1);

use JTL\Cart\Cart;
use JTL\Cart\CartHelper;
use JTL\Checkout\Bestellung;
use JTL\DB\ReturnType;
use JTL\Plugin\Helper;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\SimpleMail;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellabschluss_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'warenkorb_inc.php';

Shop::setPageType(PAGE_BESTELLABSCHLUSS);
$conf       = Shopsetting::getInstance()->getAll();
$linkHelper = Shop::Container()->getLinkService();
$kLink      = $linkHelper->getSpecialPageID(LINKTYP_BESTELLABSCHLUSS);
$link       = $linkHelper->getPageLink($kLink);
$cart       = Frontend::getCart();
$smarty     = Shop::Smarty();
$db         = Shop::Container()->getDB();
$bestellung = null;
if (Shop::$kLink === null && $kLink !== null) {
    Shop::$kLink = $kLink;
}
if (isset($_GET['i'])) {
    $bestellid = $db->select('tbestellid', 'cId', $_GET['i']);
    if (isset($bestellid->kBestellung) && $bestellid->kBestellung > 0) {
        $bestellid->kBestellung = (int)$bestellid->kBestellung;
        $bestellung             = new Bestellung($bestellid->kBestellung);
        $bestellung->fuelleBestellung(false);
        speicherUploads($bestellung);
        $db->delete('tbestellid', 'kBestellung', (int)$bestellid->kBestellung);
    }
    $db->query(
        'DELETE FROM tbestellid WHERE dDatum < DATE_SUB(NOW(), INTERVAL 30 DAY)',
        ReturnType::DEFAULT
    );
    $smarty->assign('abschlussseite', 1);
} else {
    if (isset($_POST['kommentar'])) {
        $_SESSION['kommentar'] = mb_substr(strip_tags($db->escape($_POST['kommentar'])), 0, 1000);
    } elseif (!isset($_SESSION['kommentar'])) {
        $_SESSION['kommentar'] = '';
    }
    if (SimpleMail::checkBlacklist($_SESSION['Kunde']->cMail)) {
        header('Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') .
            '?mailBlocked=1', true, 303);
        exit;
    }
    if (!bestellungKomplett()) {
        header('Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') .
            '?fillOut=' . gibFehlendeEingabe(), true, 303);
        exit;
    }
    $cart->pruefeLagerbestaende();
    if ($cart->checkIfCouponIsStillValid() === false) {
        $_SESSION['checkCouponResult']['ungueltig'] = 3;
        header('Location: ' . $linkHelper->getStaticRoute('warenkorb.php'), true, 303);
        exit;
    }
    if (empty($_SESSION['Zahlungsart']->nWaehrendBestellung)) {
        $cart->loescheDeaktiviertePositionen();
        $wkChecksum = Cart::getChecksum($cart);
        if (!empty($cart->cChecksumme)
            && $wkChecksum !== $cart->cChecksumme
        ) {
            if (!$cart->posTypEnthalten(C_WARENKORBPOS_TYP_ARTIKEL)) {
                CartHelper::deleteAllSpecialItems();
            }
            $_SESSION['Warenkorbhinweise'][] = Shop::Lang()->get('yourbasketismutating', 'checkout');
            header('Location: ' . $linkHelper->getStaticRoute('warenkorb.php'), true, 303);
            exit;
        }
        $bestellung = finalisiereBestellung();
        $bestellid  = $bestellung->kBestellung > 0
            ? $db->select('tbestellid', 'kBestellung', $bestellung->kBestellung)
            : false;
        if ($bestellung->Lieferadresse === null && !empty($_SESSION['Lieferadresse']->cVorname)) {
            $bestellung->Lieferadresse = gibLieferadresseAusSession();
        }
        $orderCompleteURL  = $linkHelper->getStaticRoute('bestellabschluss.php');
        $successPaymentURL = !empty($bestellid->cId)
            ? ($orderCompleteURL . '?i=' . $bestellid->cId)
            : Shop::getURL();
        $smarty->assign('Bestellung', $bestellung);
    } else {
        $bestellung = fakeBestellung();
    }
    setzeSmartyWeiterleitung($bestellung);
}
$smarty->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
    ->assign('oPlugin', null)
    ->assign('plugin', null)
    ->assign('Bestellung', $bestellung)
    ->assign('Link', $link)
    ->assign('Kunde', $_SESSION['Kunde'] ?? null)
    ->assign('bOrderConf', true)
    ->assign('C_WARENKORBPOS_TYP_ARTIKEL', C_WARENKORBPOS_TYP_ARTIKEL)
    ->assign('C_WARENKORBPOS_TYP_GRATISGESCHENK', C_WARENKORBPOS_TYP_GRATISGESCHENK);

$kPlugin = isset($bestellung->Zahlungsart->cModulId)
    ? Helper::getIDByModuleID($bestellung->Zahlungsart->cModulId)
    : 0;
if ($kPlugin > 0) {
    $loader = Helper::getLoaderByPluginID($kPlugin, $db);
    try {
        $plugin = $loader->init($kPlugin);
        $smarty->assign('oPlugin', $plugin)
               ->assign('plugin', $plugin);
    } catch (InvalidArgumentException $e) {
        Shop::Container()->getLogService()->error(
            'Associated plugin for payment method ' . $bestellung->Zahlungsart->cModulId . ' not found'
        );
    }
}
if (empty($_SESSION['Zahlungsart']->nWaehrendBestellung) || isset($_GET['i'])) {
    $session->cleanUp();
    require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
    executeHook(HOOK_BESTELLABSCHLUSS_PAGE, ['oBestellung' => $bestellung]);
    $smarty->display('checkout/order_completed.tpl');
} else {
    require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
    executeHook(HOOK_BESTELLABSCHLUSS_PAGE_ZAHLUNGSVORGANG, ['oBestellung' => $bestellung]);
    $smarty->display('checkout/step6_init_payment.tpl');
}

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
