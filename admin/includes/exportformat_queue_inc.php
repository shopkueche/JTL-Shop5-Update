<?php

use JTL\Alert\Alert;
use JTL\Catalog\Currency;
use JTL\Cron\LegacyCron;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Exportformat;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;

/**
 * @return array
 */
function holeExportformatCron(): array
{
    $db      = Shop::Container()->getDB();
    $exports = $db->query(
        "SELECT texportformat.*, tcron.cronID, tcron.frequency, tcron.startDate, 
            DATE_FORMAT(tcron.startDate, '%d.%m.%Y %H:%i') AS dStart_de, tcron.lastStart, 
            DATE_FORMAT(tcron.lastStart, '%d.%m.%Y %H:%i') AS dLetzterStart_de,
            DATE_FORMAT(DATE_ADD(tcron.lastStart, INTERVAL tcron.frequency HOUR), '%d.%m.%Y %H:%i') 
            AS dNaechsterStart_de
            FROM texportformat
            JOIN tcron 
                ON tcron.jobType = 'exportformat'
                AND tcron.foreignKeyID = texportformat.kExportformat
            ORDER BY tcron.startDate DESC",
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($exports as $export) {
        $export->cAlleXStdToDays = rechneUmAlleXStunden((int)$export->frequency);
        $export->Sprache         = Shop::Lang()->getLanguageByID((int)$export->kSprache);
        $export->Waehrung        = $db->select(
            'twaehrung',
            'kWaehrung',
            (int)$export->kWaehrung
        );
        $export->Kundengruppe    = $db->select(
            'tkundengruppe',
            'kKundengruppe',
            (int)$export->kKundengruppe
        );
        $export->oJobQueue       = $db->queryPrepared(
            "SELECT *, DATE_FORMAT(lastStart, '%d.%m.%Y %H:%i') AS dZuletztGelaufen_de 
                FROM tjobqueue 
                WHERE cronID = :id",
            ['id' => (int)$export->cronID],
            ReturnType::SINGLE_OBJECT
        );
        $exportFormat            = new Exportformat($export->kExportformat, $db);
        $export->nAnzahlArtikel  = (object)[
            'nAnzahl' => $exportFormat->getExportProductCount(),
        ];
    }

    return $exports;
}

/**
 * @param int $cronID
 * @return int|stdClass
 */
function holeCron(int $cronID)
{
    if ($cronID > 0) {
        $cron = Shop::Container()->getDB()->query(
            "SELECT *, DATE_FORMAT(tcron.startDate, '%d.%m.%Y %H:%i') AS dStart_de
                FROM tcron
                WHERE cronID = " . $cronID,
            ReturnType::SINGLE_OBJECT
        );
        if (!empty($cron->cronID) && $cron->cronID > 0) {
            $cron->cronID       = (int)$cron->cronID;
            $cron->frequency    = (int)$cron->frequency;
            $cron->foreignKeyID = (int)($cron->foreignKeyID ?? 0);

            return $cron;
        }
    }

    return 0;
}

/**
 * @param int $hours
 * @return bool|string
 */
function rechneUmAlleXStunden(int $hours)
{
    if ($hours <= 0) {
        return false;
    }
    if ($hours > 24) {
        $hours = round($hours / 24);
        if ($hours >= 365) {
            $hours /= 365;
            if ($hours == 1) {
                $hours .= __('year');
            } else {
                $hours .= __('years');
            }
        } elseif ($hours == 1) {
            $hours .= __('day');
        } else {
            $hours .= __('days');
        }
    } elseif ($hours > 1) {
        $hours .= __('hour');
    } else {
        $hours .= __('hours');
    }

    return $hours;
}

/**
 * @return array
 */
function holeAlleExportformate(): array
{
    $formats = Shop::Container()->getDB()->selectAll(
        'texportformat',
        [],
        [],
        '*',
        'cName, kSprache, kKundengruppe, kWaehrung'
    );
    foreach ($formats as $format) {
        $format->Sprache      = Shop::Lang()->getLanguageByID((int)$format->kSprache);
        $format->Waehrung     = new Currency((int)$format->kWaehrung);
        $format->Kundengruppe = new CustomerGroup((int)$format->kKundengruppe);
    }

    return $formats;
}

/**
 * @param int    $exportID
 * @param string $start
 * @param int    $freq
 * @param int    $cronID
 * @return int
 */
function erstelleExportformatCron(int $exportID, $start, int $freq, int $cronID = 0): int
{
    if ($exportID <= 0 || $freq < 1 || !dStartPruefen($start)) {
        return 0;
    }
    if ($cronID > 0) {
        // Editieren
        Shop::Container()->getDB()->queryPrepared(
            'DELETE tcron, tjobqueue
                FROM tcron
                LEFT JOIN tjobqueue 
                    ON tjobqueue.cronID = tcron.cronID
                WHERE tcron.cronID = :id',
            ['id' => $cronID],
            ReturnType::DEFAULT
        );
        $cron = new LegacyCron(
            $cronID,
            $exportID,
            $freq,
            $start . '_' . $exportID,
            'exportformat',
            'texportformat',
            'kExportformat',
            baueENGDate($start),
            baueENGDate($start, 1)
        );
        $cron->speicherInDB();

        return 1;
    }
    // Pruefe ob Exportformat nicht bereits vorhanden
    $cron = Shop::Container()->getDB()->select(
        'tcron',
        'foreignKey',
        'kExportformat',
        'foreignKeyID',
        $exportID
    );
    if (isset($cron->cronID) && $cron->cronID > 0) {
        return -1;
    }
    $cron = new LegacyCron(
        0,
        $exportID,
        $freq,
        $start . '_' . $exportID,
        'exportformat',
        'texportformat',
        'kExportformat',
        baueENGDate($start),
        baueENGDate($start, 1)
    );
    $cron->speicherInDB();

    return 1;
}

/**
 * @param string $start
 * @return bool
 */
function dStartPruefen($start): bool
{
    if (preg_match(
        '/^([0-3]{1}[0-9]{1}[.]{1}[0-1]{1}[0-9]{1}[.]{1}[0-9]{4}[ ]{1}[0-2]{1}[0-9]{1}[:]{1}[0-6]{1}[0-9]{1})/',
        $start
    )) {
        return true;
    }

    return false;
}

/**
 * @param string $dateStart
 * @param bool   $asTime
 * @return string
 */
function baueENGDate($dateStart, $asTime = false): string
{
    [$date, $time]        = explode(' ', $dateStart);
    [$day, $month, $year] = explode('.', $date);

    return $asTime ? $time : $year . '-' . $month . '-' . $day . ' ' . $time;
}

/**
 * @param int[] $cronIDs
 * @return bool
 */
function loescheExportformatCron(array $cronIDs): bool
{
    $db      = Shop::Container()->getDB();
    $cronIDs = array_map('\intval', $cronIDs);
    foreach ($cronIDs as $cronID) {
        $db->delete('tjobqueue', 'cronID', $cronID);
        $db->delete('tcron', 'cronID', $cronID);
    }

    return true;
}

/**
 * @param int $hours
 * @return array|bool
 */
function holeExportformatQueueBearbeitet(int $hours = 24)
{
    $languageID = (int)($_SESSION['kSprache'] ?? 0);
    if (!$languageID) {
        $tmp = Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
        if (isset($tmp->kSprache) && $tmp->kSprache > 0) {
            $languageID = (int)$tmp->kSprache;
        } else {
            return false;
        }
    }
    $languages = Shop::Lang()->getAllLanguages(1);
    $queues    = Shop::Container()->getDB()->queryPrepared(
        "SELECT texportformat.cName, texportformat.cDateiname, texportformatqueuebearbeitet.*, 
            DATE_FORMAT(texportformatqueuebearbeitet.dZuletztGelaufen, '%d.%m.%Y %H:%i') AS dZuletztGelaufen_DE, 
            tsprache.cNameDeutsch AS cNameSprache, tkundengruppe.cName AS cNameKundengruppe, 
            twaehrung.cName AS cNameWaehrung
            FROM texportformatqueuebearbeitet
            JOIN texportformat 
                ON texportformat.kExportformat = texportformatqueuebearbeitet.kExportformat
                AND texportformat.kSprache = :lid
            JOIN tsprache 
                ON tsprache.kSprache = texportformat.kSprache
            JOIN tkundengruppe 
                ON tkundengruppe.kKundengruppe = texportformat.kKundengruppe
            JOIN twaehrung 
                ON twaehrung.kWaehrung = texportformat.kWaehrung
            WHERE DATE_SUB(NOW(), INTERVAL :hrs HOUR) < texportformatqueuebearbeitet.dZuletztGelaufen
            ORDER BY texportformatqueuebearbeitet.dZuletztGelaufen DESC",
        ['lid' => $languageID, 'hrs' => $hours],
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($queues as $exportFormat) {
        $exportFormat->name = $languages[$languageID]->getLocalizedName();
    }

    return $queues;
}

/**
 * @param JTLSmarty $smarty
 * @return string
 */
function exportformatQueueActionErstellen(JTLSmarty $smarty): string
{
    $smarty->assign('oExportformat_arr', holeAlleExportformate());

    return 'erstellen';
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return string
 */
function exportformatQueueActionEditieren(JTLSmarty $smarty, array &$messages): string
{
    $id   = Request::verifyGPCDataInt('kCron');
    $cron = $id > 0 ? holeCron($id) : 0;
    if (is_object($cron) && $cron->cronID > 0) {
        $step = 'erstellen';
        $smarty->assign('oCron', $cron)
               ->assign('oExportformat_arr', holeAlleExportformate());
    } else {
        $messages['error'] .= __('errorWrongQueue');
        $step               = 'uebersicht';
    }

    return $step;
}

/**
 * @param array $messages
 * @return string
 */
function exportformatQueueActionLoeschen(array &$messages): string
{
    $cronIDs = $_POST['kCron'];
    if (is_array($cronIDs) && count($cronIDs) > 0) {
        if (loescheExportformatCron($cronIDs)) {
            $messages['notice'] .= __('successQueueDelete');
        } else {
            $messages['error'] .= __('errorUnknownLong') . '<br />';
        }
    } else {
        $messages['error'] .= __('errorWrongQueue');
    }

    return 'loeschen_result';
}

/**
 * @param array $messages
 * @return string
 */
function exportformatQueueActionTriggern(array &$messages): string
{
    global $bCronManuell, $oCron_arr, $oJobQueue_arr;
    $bCronManuell = true;

    require_once PFAD_ROOT . PFAD_INCLUDES . 'cron_inc.php';

    if (is_array($oCron_arr) && is_array($oJobQueue_arr)) {
        $cronCount = count($oCron_arr);
        $jobCount  = count($oJobQueue_arr);

        if ($cronCount === 0 && $jobCount === 0) {
            $messages['error'] .= __('errorCronStart') . '<br />';
        } elseif ($cronCount === 1) {
            $messages['notice'] .= __('successCronStart') . '<br />';
        } elseif ($cronCount > 1) {
            $messages['notice'] .= sprintf(__('successCronsStart'), $cronCount) . '<br />';
        }

        if ($jobCount === 1) {
            $messages['notice'] .= __('successQueueDone') . '<br />';
        } elseif ($jobCount > 1) {
            $messages['notice'] .= sprintf(__('successQueuseDone'), $jobCount) . '<br />';
        }
    }

    return 'triggern';
}

/**
 * @param JTLSmarty $smarty
 * @return string
 */
function exportformatQueueActionFertiggestellt(JTLSmarty $smarty): string
{
    $hours = Request::verifyGPCDataInt('nStunden');
    if ($hours <= 0) {
        $hours = 24;
    }

    $_SESSION['exportformatQueue.nStunden'] = $hours;
    $smarty->assign('cTab', 'fertig');

    return 'fertiggestellt';
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return string
 */
function exportformatQueueActionErstellenEintragen(JTLSmarty $smarty, array &$messages): string
{
    $id                    = Request::postInt('kExportformat');
    $start                 = $_POST['dStart'];
    $freq                  = !empty($_POST['nAlleXStundenCustom'])
        ? (int)$_POST['nAlleXStundenCustom']
        : (int)$_POST['nAlleXStunden'];
    $values                = new stdClass();
    $values->kExportformat = $id;
    $values->dStart        = Text::filterXSS($_POST['dStart']);
    $values->nAlleXStunden = Text::filterXSS($_POST['nAlleXStunden']);
    if ($id > 0) {
        if (dStartPruefen($start)) {
            if ($freq >= 1) {
                $state = erstelleExportformatCron($id, $start, $freq, Request::postInt('kCron'));
                if ($state === 1) {
                    $messages['notice'] .= __('successQueueCreate');
                    $step                = 'erstellen_success';
                } elseif ($state === -1) {
                    $messages['error'] .= __('errorFormatInQueue') . '<br />';
                    $step               = 'erstellen';
                } else {
                    $messages['error'] .= __('errorUnknownLong') . '<br />';
                    $step               = 'erstellen';
                }
            } else { // Alle X Stunden ist entweder leer oder kleiner als 6
                $messages['error'] .= __('errorGreaterEqualOne') . '<br />';
                $step               = 'erstellen';
                $smarty->assign('oFehler', $values);
            }
        } else { // Kein gueltiges Datum + Uhrzeit
            $messages['error'] .= __('errorEnterValidDate') . '<br />';
            $step               = 'erstellen';
            $smarty->assign('oFehler', $values);
        }
    } else { // Kein gueltiges Exportformat
        $messages['error'] .= __('errorFormatSelect') . '<br />';
        $step               = 'erstellen';
        $smarty->assign('oFehler', $values);
    }

    return $step;
}

/**
 * @param string     $tab
 * @param array|null $messages
 */
function exportformatQueueRedirect($tab = '', array $messages = null): void
{
    if (isset($messages['notice']) && !empty($messages['notice'])) {
        $_SESSION['exportformatQueue.notice'] = $messages['notice'];
    } else {
        unset($_SESSION['exportformatQueue.notice']);
    }
    if (isset($messages['error']) && !empty($messages['error'])) {
        $_SESSION['exportformatQueue.error'] = $messages['error'];
    } else {
        unset($_SESSION['exportformatQueue.error']);
    }

    $urlParams = null;
    if (!empty($tab)) {
        $urlParams['tab'] = Text::filterXSS($tab);
    }

    header('Location: exportformat_queue.php' .
        (is_array($urlParams) ? '?' . http_build_query($urlParams, '', '&') : ''));
    exit;
}

/**
 * @param string    $step
 * @param JTLSmarty $smarty
 * @param array     $messages
 */
function exportformatQueueFinalize($step, JTLSmarty $smarty, array &$messages): void
{
    if (isset($_SESSION['exportformatQueue.notice'])) {
        $messages['notice'] = $_SESSION['exportformatQueue.notice'];
        unset($_SESSION['exportformatQueue.notice']);
    }
    if (isset($_SESSION['exportformatQueue.error'])) {
        $messages['error'] = $_SESSION['exportformatQueue.error'];
        unset($_SESSION['exportformatQueue.error']);
    }

    switch ($step) {
        case 'uebersicht':
            $freq = $_SESSION['exportformatQueue.nStunden'] ?? 24;
            $smarty->assign('oExportformatCron_arr', holeExportformatCron())
                   ->assign('oExportformatQueueBearbeitet_arr', holeExportformatQueueBearbeitet($freq))
                   ->assign('nStunden', $freq);
            break;
        case 'erstellen_success':
        case 'loeschen_result':
        case 'triggern':
            exportformatQueueRedirect('aktiv', $messages);
            break;
        case 'fertiggestellt':
            exportformatQueueRedirect('fertig', $messages);
            break;
        case 'erstellen':
            if (!empty($messages['error'])) {
                $freq = $_SESSION['exportformatQueue.nStunden'] ?? 24;
                $smarty->assign('oExportformatCron_arr', holeExportformatCron())
                       ->assign('oExportformatQueueBearbeitet_arr', holeExportformatQueueBearbeitet($freq))
                       ->assign('oExportformat_arr', holeAlleExportformate())
                       ->assign('nStunden', $freq);
            }
            break;
        default:
            break;
    }

    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_ERROR, $messages['error'], 'expoFormatError');
    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_NOTE, $messages['notice'], 'expoFormatNote');

    $smarty->assign('step', $step)
           ->display('exportformat_queue.tpl');
}
