<?php

use JTL\Checkout\Bestellung;
use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Plugin\Helper;
use JTL\Plugin\Payment\LegacyMethod;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;

require_once __DIR__ . '/../../includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

define('NO_MODE', 0);
define('NO_PFAD', PFAD_LOGFILES . 'notify.log');

$logger              = Shop::Container()->getLogService();
$moduleId            = null;
$Sprache             = Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
$conf                = Shopsetting::getInstance()->getAll();
$cEditZahlungHinweis = '';
//Session Hash
$cPh = Request::verifyGPDataString('ph');
$cSh = Request::verifyGPDataString('sh');

executeHook(HOOK_NOTIFY_HASHPARAMETER_DEFINITION);

if (strlen(Request::verifyGPDataString('ph')) === 0
    && strlen(Request::verifyGPDataString('externalBDRID')) > 0
) {
    $cPh = Request::verifyGPDataString('externalBDRID');
    if ($cPh[0] === '_') {
        $cPh = '';
        $cSh = Request::verifyGPDataString('externalBDRID');
    }
}
// Work around Sofortüberweisung
if (strlen(Request::verifyGPDataString('key')) > 0 && strlen(Request::verifyGPDataString('sid')) > 0) {
    $cPh = Request::verifyGPDataString('sid');
    if (Request::verifyGPDataString('key') === 'sh') {
        $cPh = '';
        $cSh = Request::verifyGPDataString('sid');
    }
}

if (strlen($cSh) > 0) {
    if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
        $logger->debug('Notify SH: ' . print_r($_REQUEST, true));
    }
    // Load from Session Hash / Session Hash starts with "_"
    $sessionHash    = substr(Text::htmlentities(Text::filterXSS($cSh)), 1);
    $paymentSession = Shop::Container()->getDB()->select(
        'tzahlungsession',
        'cZahlungsID',
        $sessionHash,
        null,
        null,
        null,
        null,
        false,
        'cSID, kBestellung'
    );
    if ($paymentSession === null) {
        $logger->error('Session Hash: ' . $cSh . ' ergab keine Bestellung aus tzahlungsession');
        die();
    }
    if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
        Shop::Container()->getLogService()->debug(
            'Session Hash: ' . $cSh . ' ergab tzahlungsession ' .
            print_r($paymentSession, true)
        );
    }
    if (session_id() !== $paymentSession->cSID) {
        session_destroy();
        session_id($paymentSession->cSID);
        $session = Frontend::getInstance(true, true);
    } else {
        $session = Frontend::getInstance(false, false);
    }
    require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellabschluss_inc.php';

    $logger->debug('Session Hash ' . $cSh . ' ergab cModulId aus Session: ' . $_SESSION['Zahlungsart']->cModulId
        ?? '---');
    if (!isset($paymentSession->kBestellung) || !$paymentSession->kBestellung) {
        // Generate fake Order and ask PaymentMethod if order should be finalized
        $order         = fakeBestellung();
        $paymentMethod = isset($_SESSION['Zahlungsart']->cModulId)
            ? LegacyMethod::create($_SESSION['Zahlungsart']->cModulId)
            : null;
        if ($paymentMethod !== null) {
            if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
                $logger->debug('Session Hash: ' . $cSh . ' ergab Methode: ' . print_r($paymentMethod, true));
            }
            $pluginID = Helper::getIDByModuleID($_SESSION['Zahlungsart']->cModulId);
            if ($pluginID > 0) {
                $loader             = Helper::getLoaderByPluginID($pluginID);
                $oPlugin            = $loader->init($pluginID);
                $GLOBALS['oPlugin'] = $oPlugin;
            }

            if ($paymentMethod->finalizeOrder($order, $sessionHash, $_REQUEST)) {
                $logger->debug('Session Hash: ' . $cSh . ' ergab finalizeOrder passed');
                $order = finalisiereBestellung();
                $session->cleanUp();

                if ($order->kBestellung > 0) {
                    $logger->debug('tzahlungsession aktualisiert.');
                    $upd               = new stdClass();
                    $upd->nBezahlt     = 1;
                    $upd->dZeitBezahlt = 'NOW()';
                    $upd->kBestellung  = (int)$order->kBestellung;
                    Shop::Container()->getDB()->update('tzahlungsession', 'cZahlungsID', $sessionHash, $upd);
                    $paymentMethod->handleNotification($order, '_' . $sessionHash, $_REQUEST);
                    if ($paymentMethod->redirectOnPaymentSuccess() === true) {
                        header('Location: ' . $paymentMethod->getReturnURL($order));
                        exit();
                    }
                }
            } else {
                $logger->debug('finalizeOrder failed -> zurueck zur Zahlungsauswahl.');
                $linkHelper = Shop::Container()->getLinkService();
                if ($paymentMethod->redirectOnCancel()) {
                    // Go to 'Edit PaymentMethod' Page
                    $header = 'Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') .
                        '?editZahlungsart=1';
                    if (strlen($cEditZahlungHinweis) > 0) {
                        $header = 'Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') .
                            '?editZahlungsart=1&nHinweis=' . $cEditZahlungHinweis;
                    }
                    header($header);
                    exit();
                }
                if (strlen($cEditZahlungHinweis) > 0) {
                    echo $linkHelper->getStaticRoute('bestellvorgang.php') .
                        '?editZahlungsart=1&nHinweis=' . $cEditZahlungHinweis;
                } else {
                    echo $linkHelper->getStaticRoute('bestellvorgang.php') . '?editZahlungsart=1';
                }
            }
        }
    } else {
        $order = new Bestellung($paymentSession->kBestellung);
        $order->fuelleBestellung(false);
        $logger->debug('Session Hash ' . $cSh . ' hat kBestellung. Modul ' . $order->Zahlungsart->cModulId .
            ' wird aufgerufen');

        $paymentMethod = LegacyMethod::create($order->Zahlungsart->cModulId);
        $paymentMethod->handleNotification($order, '_' . $sessionHash, $_REQUEST);
        if ($paymentMethod->redirectOnPaymentSuccess() === true) {
            header('Location: ' . $paymentMethod->getReturnURL($order));
            exit();
        }
    }

    die();
}

/*** Payment Hash ***/

$session = Frontend::getInstance();
if (strlen($cPh) > 0) {
    if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
        $logger->debug('Notify request:' . print_r($_REQUEST, true));
    }
    $paymentId = Shop::Container()->getDB()->queryPrepared(
        'SELECT ZID.kBestellung, ZA.cModulId
            FROM tzahlungsid ZID
            LEFT JOIN tzahlungsart ZA
                ON ZA.kZahlungsart = ZID.kZahlungsart
            WHERE ZID.cId = :hash',
        ['hash' => Text::htmlentities(Text::filterXSS($cPh))],
        ReturnType::SINGLE_OBJECT
    );

    if ($paymentId === false) {
        $logger->error('Payment Hash ' . $cPh . ' ergab keine Bestellung aus tzahlungsid.');
        die(); // Payment Hash does not exist
    }
    // Load Order
    $moduleId = $paymentId->cModulId;
    $order    = new Bestellung($paymentId->kBestellung);
    $order->fuelleBestellung(false);

    if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
        $logger->debug('Payment Hash ' . $cPh . ' ergab Order ' . print_r($order, true));
    }
}
if ($moduleId !== null) {
    // Let PaymentMethod handle Notification
    $paymentMethod = LegacyMethod::create($moduleId);
    if ($paymentMethod !== null) {
        if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
            $logger->debug('Payment Hash ' . $cPh . ' ergab Order' . print_r($paymentMethod, true));
        }
        $paymentHash = Shop::Container()->getDB()->escape(Text::htmlentities(Text::filterXSS($cPh)));
        $paymentMethod->handleNotification($order, $paymentHash, $_REQUEST);
        if ($paymentMethod->redirectOnPaymentSuccess() === true) {
            header('Location: ' . $paymentMethod->getReturnURL($order));
            exit();
        }
    }
}
