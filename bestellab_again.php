<?php declare(strict_types=1);

use JTL\Checkout\Bestellung;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Plugin\Helper;
use JTL\Plugin\Payment\LegacyMethod;
use JTL\Session\Frontend;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellabschluss_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';

Shop::setPageType(PAGE_BESTELLABSCHLUSS);
$orderID    = (int)$_REQUEST['kBestellung'];
$db         = Shop::Container()->getDB();
$linkHelper = Shop::Container()->getLinkService();
$order      = new Bestellung($orderID, true);
//abfragen, ob diese Bestellung dem Kunden auch gehoert
//bei Gastbestellungen ist ggf das Kundenobjekt bereits entfernt bzw nRegistriert = 0
if ($order->oKunde !== null
    && (int)$order->oKunde->nRegistriert === 1
    && (int)$order->kKunde !== Frontend::getCustomer()->getID()
) {
    header('Location: ' . $linkHelper->getStaticRoute('jtl.php'), true, 303);
    exit;
}

$bestellid         = $db->select('tbestellid', 'kBestellung', $order->kBestellung);
$successPaymentURL = Shop::getURL();
if ($bestellid->cId) {
    $orderCompleteURL  = $linkHelper->getStaticRoute('bestellabschluss.php');
    $successPaymentURL = $orderCompleteURL . '?i=' . $bestellid->cId;
}

$obj              = new stdClass();
$obj->tkunde      = $_SESSION['Kunde'];
$obj->tbestellung = $order;
$moduleID         = $order->Zahlungsart->cModulId;
Shop::Smarty()->assign('Bestellung', $order)
    ->assign('oPlugin', null)
    ->assign('plugin', null);
if (Request::verifyGPCDataInt('zusatzschritt') === 1) {
    $hasAdditionalInformation = false;
    switch ($moduleID) {
        case 'za_kreditkarte_jtl':
            if ($_POST['kreditkartennr']
                && $_POST['gueltigkeit']
                && $_POST['cvv']
                && $_POST['kartentyp']
                && $_POST['inhaber']
            ) {
                $_SESSION['Zahlungsart']->ZahlungsInfo->cKartenNr    =
                    Text::htmlentities(stripslashes($_POST['kreditkartennr']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cGueltigkeit =
                    Text::htmlentities(stripslashes($_POST['gueltigkeit']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cCVV         =
                    Text::htmlentities(stripslashes($_POST['cvv']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cKartenTyp   =
                    Text::htmlentities(stripslashes($_POST['kartentyp']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber     =
                    Text::htmlentities(stripslashes($_POST['inhaber']), ENT_QUOTES);
                $hasAdditionalInformation                            = true;
            }
            break;
        case 'za_lastschrift_jtl':
            if (($_POST['bankname']
                    && $_POST['blz']
                    && $_POST['kontonr']
                    && $_POST['inhaber'])
                || ($_POST['bankname']
                    && $_POST['iban']
                    && $_POST['bic']
                    && $_POST['inhaber'])
            ) {
                $_SESSION['Zahlungsart']->ZahlungsInfo->cBankName =
                    Text::htmlentities(stripslashes($_POST['bankname']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr  =
                    Text::htmlentities(stripslashes($_POST['kontonr']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cBLZ      =
                    Text::htmlentities(stripslashes($_POST['blz']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN     =
                    Text::htmlentities(stripslashes($_POST['iban']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cBIC      =
                    Text::htmlentities(stripslashes($_POST['bic']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber  =
                    Text::htmlentities(stripslashes($_POST['inhaber']), ENT_QUOTES);
                $hasAdditionalInformation                         = true;
            }
            break;
    }

    if ($hasAdditionalInformation) {
        if (saveZahlungsInfo($order->kKunde, $order->kBestellung)) {
            $db->update(
                'tbestellung',
                'kBestellung',
                (int)$order->kBestellung,
                (object)['cAbgeholt' => 'N']
            );
            unset($_SESSION['Zahlungsart']);
            header('Location: ' . $successPaymentURL, true, 303);
            exit();
        }
    } else {
        Shop::Smarty()->assign('ZahlungsInfo', gibPostZahlungsInfo());
    }
}
// Zahlungsart als Plugin
$pluginID = Helper::getIDByModuleID($moduleID);
if ($pluginID > 0) {
    $loader = Helper::getLoaderByPluginID($pluginID, $db);
    $plugin = $loader->init($pluginID);
    if ($plugin !== null) {
        $paymentMethod = LegacyMethod::create($moduleID, 1);
        if ($paymentMethod !== null) {
            if ($paymentMethod->validateAdditional()) {
                $paymentMethod->preparePaymentProcess($order);
            } elseif (!$paymentMethod->handleAdditional($_POST)) {
                $order->Zahlungsart = gibZahlungsart($order->kZahlungsart);
            }
        }

        Shop::Smarty()->assign('oPlugin', $plugin)
            ->assign('plugin', $plugin);
    }
} elseif ($moduleID === 'za_lastschrift_jtl') {
    $customerAccountData = gibKundenKontodaten(Frontend::getCustomer()->getID());
    if ($customerAccountData->kKunde > 0) {
        Shop::Smarty()->assign('oKundenKontodaten', $customerAccountData);
    }
}

Shop::Smarty()->assign('WarensummeLocalized', Frontend::getCart()->gibGesamtsummeWarenLocalized())
    ->assign('Bestellung', $order);

unset(
    $_SESSION['Zahlungsart'],
    $_SESSION['Versandart'],
    $_SESSION['Lieferadresse'],
    $_SESSION['VersandKupon'],
    $_SESSION['NeukundenKupon'],
    $_SESSION['Kupon']
);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
Shop::Smarty()->display('checkout/order_completed.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
