<?php

use JTL\Alert\Alert;
use JTL\Checkout\Zahlungsart;
use JTL\Checkout\ZahlungsLog;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\PaymentMethod;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Filter;
use JTL\Pagination\Pagination;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Recommendation\Manager;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('ORDER_PAYMENT_VIEW', true, true);

require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'zahlungsarten_inc.php';

Shop::Container()->getGetText()->loadConfigLocales(true, true);

$db              = Shop::Container()->getDB();
$defaultCurrency = $db->select('twaehrung', 'cStandard', 'Y');
$step            = 'uebersicht';
$alertHelper     = Shop::Container()->getAlertService();
$recommendations = new Manager($alertHelper, Manager::SCOPE_BACKEND_PAYMENT_PROVIDER);
if (Request::verifyGPCDataInt('checkNutzbar') === 1) {
    PaymentMethod::checkPaymentMethodAvailability();
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successPaymentMethodCheck'), 'successPaymentMethodCheck');
}
// reset log
if (($action = Request::verifyGPDataString('a')) !== ''
    && $action === 'logreset'
    && ($paymentMethodID = Request::verifyGPCDataInt('kZahlungsart')) > 0
    && Form::validateToken()
) {
    $method = $db->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);

    if (isset($method->cModulId) && mb_strlen($method->cModulId) > 0) {
        (new ZahlungsLog($method->cModulId))->loeschen();
        $alertHelper->addAlert(
            Alert::TYPE_SUCCESS,
            sprintf(__('successLogReset'), $method->cName),
            'successLogReset'
        );
    }
}
if ($action !== 'logreset' && Request::verifyGPCDataInt('kZahlungsart') > 0 && Form::validateToken()) {
    $step = 'einstellen';
    if ($action === 'payments') {
        $step = 'payments';
    } elseif ($action === 'log') {
        $step = 'log';
    } elseif ($action === 'del') {
        $step = 'delete';
    }
}

if (Request::postInt('einstellungen_bearbeiten') === 1
    && Request::postInt('kZahlungsart') > 0
    && Form::validateToken()
) {
    $step              = 'uebersicht';
    $paymentMethod     = $db->select(
        'tzahlungsart',
        'kZahlungsart',
        Request::postInt('kZahlungsart')
    );
    $nMailSenden       = Request::postInt('nMailSenden');
    $nMailSendenStorno = Request::postInt('nMailSendenStorno');
    $nMailBits         = 0;
    if (is_array($_POST['kKundengruppe'])) {
        $cKundengruppen = Text::createSSK($_POST['kKundengruppe']);
        if (in_array(0, $_POST['kKundengruppe'])) {
            unset($cKundengruppen);
        }
    }
    if ($nMailSenden) {
        $nMailBits |= ZAHLUNGSART_MAIL_EINGANG;
    }
    if ($nMailSendenStorno) {
        $nMailBits |= ZAHLUNGSART_MAIL_STORNO;
    }
    if (!isset($cKundengruppen)) {
        $cKundengruppen = '';
    }

    $nWaehrendBestellung = Request::postInt('nWaehrendBestellung', $paymentMethod->nWaehrendBestellung);

    $upd                      = new stdClass();
    $upd->cKundengruppen      = $cKundengruppen;
    $upd->nSort               = Request::postInt('nSort');
    $upd->nMailSenden         = $nMailBits;
    $upd->cBild               = $_POST['cBild'];
    $upd->nWaehrendBestellung = $nWaehrendBestellung;
    $db->update('tzahlungsart', 'kZahlungsart', (int)$paymentMethod->kZahlungsart, $upd);
    // Weiche fuer eine normale Zahlungsart oder eine Zahlungsart via Plugin
    if (mb_strpos($paymentMethod->cModulId, 'kPlugin_') !== false) {
        $kPlugin     = PluginHelper::getIDByModuleID($paymentMethod->cModulId);
        $Conf        = $db->query(
            "SELECT *
                FROM tplugineinstellungenconf
                WHERE cWertName LIKE '" . $paymentMethod->cModulId . "\_%'
                AND cConf = 'Y' ORDER BY nSort",
            ReturnType::ARRAY_OF_OBJECTS
        );
        $configCount = count($Conf);
        for ($i = 0; $i < $configCount; $i++) {
            $aktWert          = new stdClass();
            $aktWert->kPlugin = $kPlugin;
            $aktWert->cName   = $Conf[$i]->cWertName;
            $aktWert->cWert   = $_POST[$Conf[$i]->cWertName];

            switch ($Conf[$i]->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = (float)str_replace(',', '.', $aktWert->cWert);
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = (int)$aktWert->cWert;
                    break;
                case 'text':
                    $aktWert->cWert = mb_substr($aktWert->cWert, 0, 255);
                    break;
            }
            $db->delete(
                'tplugineinstellungen',
                ['kPlugin', 'cName'],
                [$kPlugin, $Conf[$i]->cWertName]
            );
            $db->insert('tplugineinstellungen', $aktWert);
        }
    } else {
        $Conf        = $db->selectAll(
            'teinstellungenconf',
            ['cModulId', 'cConf'],
            [$paymentMethod->cModulId, 'Y'],
            '*',
            'nSort'
        );
        $configCount = count($Conf);
        for ($i = 0; $i < $configCount; ++$i) {
            $aktWert                        = new stdClass();
            $aktWert->cWert                 = $_POST[$Conf[$i]->cWertName];
            $aktWert->cName                 = $Conf[$i]->cWertName;
            $aktWert->kEinstellungenSektion = CONF_ZAHLUNGSARTEN;
            $aktWert->cModulId              = $paymentMethod->cModulId;

            switch ($Conf[$i]->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = (float)str_replace(',', '.', $aktWert->cWert);
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = (int)$aktWert->cWert;
                    break;
                case 'text':
                    $aktWert->cWert = mb_substr($aktWert->cWert, 0, 255);
                    break;
            }
            $db->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [CONF_ZAHLUNGSARTEN, $Conf[$i]->cWertName]
            );
            $db->insert('teinstellungen', $aktWert);
            Shop::Container()->getGetText()->localizeConfig($Conf[$i]);
        }
    }

    if (!isset($localized)) {
        $localized = new stdClass();
    }
    $localized->kZahlungsart = Request::postInt('kZahlungsart');
    foreach (LanguageHelper::getAllLanguages() as $lang) {
        $langCode               = $lang->getCode();
        $localized->cISOSprache = $langCode;
        $localized->cName       = $paymentMethod->cName;
        if ($_POST['cName_' . $langCode]) {
            $localized->cName = $_POST['cName_' . $langCode];
        }
        $localized->cGebuehrname     = $_POST['cGebuehrname_' . $langCode];
        $localized->cHinweisText     = $_POST['cHinweisText_' . $langCode];
        $localized->cHinweisTextShop = $_POST['cHinweisTextShop_' . $langCode];

        $db->delete(
            'tzahlungsartsprache',
            ['kZahlungsart', 'cISOSprache'],
            [Request::postInt('kZahlungsart'), $langCode]
        );
        $db->insert('tzahlungsartsprache', $localized);
    }

    Shop::Container()->getCache()->flushAll();
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successPaymentMethodSave'), 'successSave');
    $step = 'uebersicht';
}

if ($step === 'einstellen') {
    $paymentMethod = new Zahlungsart(Request::verifyGPCDataInt('kZahlungsart'));
    if ($paymentMethod->getZahlungsart() === null) {
        $step = 'uebersicht';
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorPaymentMethodNotFound'), 'errorNotFound');
    } else {
        PaymentMethod::activatePaymentMethod($paymentMethod);
        // Weiche fuer eine normale Zahlungsart oder eine Zahlungsart via Plugin
        if (mb_strpos($paymentMethod->cModulId, 'kPlugin_') !== false) {
            $Conf        = $db->query(
                "SELECT *
                    FROM tplugineinstellungenconf
                    WHERE cWertName LIKE '" . $paymentMethod->cModulId . "\_%'
                    ORDER BY nSort",
                ReturnType::ARRAY_OF_OBJECTS
            );
            $configCount = count($Conf);
            for ($i = 0; $i < $configCount; ++$i) {
                if ($Conf[$i]->cInputTyp === 'selectbox') {
                    $Conf[$i]->ConfWerte = $db->selectAll(
                        'tplugineinstellungenconfwerte',
                        'kPluginEinstellungenConf',
                        (int)$Conf[$i]->kPluginEinstellungenConf,
                        '*',
                        'nSort'
                    );
                    foreach (array_keys($Conf[$i]->ConfWerte) as $confKey) {
                        $Conf[$i]->ConfWerte[$confKey]->cName = __($Conf[$i]->ConfWerte[$confKey]->cName);
                    }
                }
                $setValue                = $db->select(
                    'tplugineinstellungen',
                    'kPlugin',
                    (int)$Conf[$i]->kPlugin,
                    'cName',
                    $Conf[$i]->cWertName
                );
                $Conf[$i]->gesetzterWert = $setValue->cWert;
                $Conf[$i]->cName         = __($Conf[$i]->cName);
                $Conf[$i]->cBeschreibung = __($Conf[$i]->cBeschreibung);
            }
        } else {
            $Conf        = $db->selectAll(
                'teinstellungenconf',
                'cModulId',
                $paymentMethod->cModulId,
                '*',
                'nSort'
            );
            $configCount = count($Conf);
            for ($i = 0; $i < $configCount; ++$i) {
                if ($Conf[$i]->cInputTyp === 'selectbox') {
                    $Conf[$i]->ConfWerte = $db->selectAll(
                        'teinstellungenconfwerte',
                        'kEinstellungenConf',
                        (int)$Conf[$i]->kEinstellungenConf,
                        '*',
                        'nSort'
                    );
                    Shop::Container()->getGetText()->localizeConfigValues($Conf[$i], $Conf[$i]->ConfWerte);
                }
                $setValue                = $db->select(
                    'teinstellungen',
                    'kEinstellungenSektion',
                    CONF_ZAHLUNGSARTEN,
                    'cName',
                    $Conf[$i]->cWertName
                );
                $Conf[$i]->gesetzterWert = $setValue->cWert ?? null;
                Shop::Container()->getGetText()->localizeConfig($Conf[$i]);
            }
        }

        $customerGroups = $db->query(
            'SELECT *
                FROM tkundengruppe
                ORDER BY cName',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $smarty->assign('Conf', $Conf)
               ->assign('zahlungsart', $paymentMethod)
               ->assign('kundengruppen', $customerGroups)
               ->assign('gesetzteKundengruppen', getGesetzteKundengruppen($paymentMethod))
               ->assign('Zahlungsartname', getNames($paymentMethod->kZahlungsart))
               ->assign('Gebuehrname', getshippingTimeNames($paymentMethod->kZahlungsart))
               ->assign('cHinweisTexte_arr', getHinweisTexte($paymentMethod->kZahlungsart))
               ->assign('cHinweisTexteShop_arr', getHinweisTexteShop($paymentMethod->kZahlungsart))
               ->assign('ZAHLUNGSART_MAIL_EINGANG', ZAHLUNGSART_MAIL_EINGANG)
               ->assign('ZAHLUNGSART_MAIL_STORNO', ZAHLUNGSART_MAIL_STORNO);
    }
} elseif ($step === 'log') {
    $paymentMethodID = Request::verifyGPCDataInt('kZahlungsart');
    $method          = $db->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);

    $filterStandard = new Filter('standard');
    $filterStandard->addDaterangefield('Zeitraum', 'dDatum');
    $filterStandard->assemble();

    if (isset($method->cModulId) && mb_strlen($method->cModulId) > 0) {
        $paginationPaymentLog = (new Pagination('standard'))
            ->setItemCount(ZahlungsLog::count($method->cModulId, -1, $filterStandard->getWhereSQL()))
            ->assemble();
        $paymentLogs          = (new ZahlungsLog($method->cModulId))->holeLog(
            $paginationPaymentLog->getLimitSQL(),
            -1,
            $filterStandard->getWhereSQL()
        );

        $smarty->assign('paymentLogs', $paymentLogs)
               ->assign('paymentData', $method)
               ->assign('filterStandard', $filterStandard)
               ->assign('paginationPaymentLog', $paginationPaymentLog);
    }
} elseif ($step === 'payments') {
    if (isset($_POST['action'], $_POST['kEingang_arr'])
        && $_POST['action'] === 'paymentwawireset'
        && Form::validateToken()
    ) {
        $incomingIDs = $_POST['kEingang_arr'];
        array_walk($incomingIDs, static function (&$i) {
            $i = (int)$i;
        });
        $db->query(
            "UPDATE tzahlungseingang
                SET cAbgeholt = 'N'
                WHERE kZahlungseingang IN (" . implode(',', $incomingIDs) . ')',
            ReturnType::QUERYSINGLE
        );
    }

    $paymentMethodID = Request::verifyGPCDataInt('kZahlungsart');

    $filter = new Filter('payments-' . $paymentMethodID);
    $filter->addTextfield(
        ['Suchbegriff', 'Sucht in Bestell-Nr., Betrag, Kunden-Vornamen, E-Mail-Adresse, Hinweis'],
        ['cBestellNr', 'fBetrag', 'cVorname', 'cMail', 'cHinweis']
    );
    $filter->addDaterangefield('Zeitraum', 'dZeit');
    $filter->assemble();

    $method        = $db->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);
    $incoming      = $db->query(
        'SELECT ze.*, b.kZahlungsart, b.cBestellNr, k.kKunde, k.cVorname, k.cNachname, k.cMail
            FROM tzahlungseingang AS ze
                JOIN tbestellung AS b
                    ON ze.kBestellung = b.kBestellung
                JOIN tkunde AS k
                    ON b.kKunde = k.kKunde
            WHERE b.kZahlungsart = ' . $paymentMethodID . ' ' .
        ($filter->getWhereSQL() !== '' ? 'AND ' . $filter->getWhereSQL() : '') . '
            ORDER BY dZeit DESC',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $pagination    = (new Pagination('payments' . $paymentMethodID))
        ->setItemArray($incoming)
        ->assemble();
    $cryptoService = Shop::Container()->getCryptoService();
    foreach ($incoming as $item) {
        $item->cNachname = $cryptoService->decryptXTEA($item->cNachname);
        $item->dZeit     = date_create($item->dZeit)->format('d.m.Y\<\b\r\>H:i');
    }
    $smarty->assign('oZahlungsart', $method)
           ->assign('oZahlunseingang_arr', $pagination->getPageItems())
           ->assign('pagination', $pagination)
           ->assign('oFilter', $filter);
} elseif ($step === 'delete') {
    $paymentMethodID = Request::verifyGPCDataInt('kZahlungsart');
    $method          = $db->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);
    $pluginID        = PluginHelper::getIDByModuleID($method->cModulId);
    if ($pluginID > 0) {
        try {
            Shop::Container()->getGetText()->loadPluginLocale(
                'base',
                PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID)
            );
            $alertHelper->addAlert(
                Alert::TYPE_WARNING,
                sprintf(__('Payment method can not been deleted'), __($method->cName)),
                'paymentcantdel',
                ['saveInSession' => true]
            );
        } catch (InvalidArgumentException $e) {
            // Only delete if plugin is not installed
            $db->delete('tversandartzahlungsart', 'kZahlungsart', $paymentMethodID);
            $db->delete('tzahlungsartsprache', 'kZahlungsart', $paymentMethodID);
            $db->delete('tzahlungsart', 'kZahlungsart', $paymentMethodID);
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                sprintf(__('Payment method has been deleted'), $method->cName),
                'paymentdeleted',
                ['saveInSession' => true]
            );
        }
    }
    header('Location: ' . Shop::getAdminURL(true) . '/zahlungsarten.php');
    exit;
}

if ($step === 'uebersicht') {
    $methods = $db->selectAll(
        'tzahlungsart',
        ['nActive', 'nNutzbar'],
        [1, 1],
        '*',
        'cAnbieter, cName, nSort, kZahlungsart, cModulId'
    );
    foreach ($methods as $method) {
        $method->markedForDelete = false;

        $pluginID = PluginHelper::getIDByModuleID($method->cModulId);
        if ($pluginID > 0) {
            try {
                Shop::Container()->getGetText()->loadPluginLocale(
                    'base',
                    PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID)
                );
            } catch (InvalidArgumentException $e) {
                $method->markedForDelete = true;
                $alertHelper->addAlert(
                    Alert::TYPE_WARNING,
                    sprintf(__('Plugin for payment method not found'), $method->cName, $method->cAnbieter),
                    'notfound_' . $pluginID
                );
            }
        }
        $method->nEingangAnzahl = (int)$db->executeQueryPrepared(
            'SELECT COUNT(*) AS `cnt`
                FROM `tzahlungseingang` AS ze
                    JOIN `tbestellung` AS b ON ze.`kBestellung` = b.`kBestellung`
                WHERE b.`kZahlungsart` = :kzahlungsart',
            ['kzahlungsart' => $method->kZahlungsart],
            ReturnType::SINGLE_OBJECT
        )->cnt;
        $method->nLogCount      = ZahlungsLog::count($method->cModulId);
        $method->nErrorLogCount = ZahlungsLog::count($method->cModulId, JTLLOG_LEVEL_ERROR);
        $method->cName          = __($method->cName);
        $method->cAnbieter      = __($method->cAnbieter);
    }
    $smarty->assign('zahlungsarten', $methods);
}
$smarty->assign('step', $step)
       ->assign('waehrung', $defaultCurrency->cName)
       ->assign('recommendations', $recommendations)
       ->display('zahlungsarten.tpl');
