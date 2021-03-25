<?php

use JTL\Campaign;
use JTL\Catalog\Product\Preise;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Linechart;
use JTL\Session\Frontend;
use JTL\Shop;
use function Functional\reindex;

/**
 * @return array
 */
function holeAlleKampagnenDefinitionen()
{
    return reindex(
        Shop::Container()->getDB()->query(
            'SELECT *
                FROM tkampagnedef
                ORDER BY kKampagneDef',
            ReturnType::ARRAY_OF_OBJECTS
        ),
        static function ($e) {
            return (int)$e->kKampagneDef;
        }
    );
}

/**
 * @param int $id
 * @return mixed
 */
function holeKampagne(int $id)
{
    return Shop::Container()->getDB()->query(
        "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i:%s') AS dErstellt_DE
            FROM tkampagne
            WHERE kKampagne = " . $id,
        ReturnType::SINGLE_OBJECT
    );
}

/**
 * @param int $definitionID
 * @return mixed
 */
function holeKampagneDef(int $definitionID)
{
    return Shop::Container()->getDB()->select('tkampagnedef', 'kKampagneDef', $definitionID);
}

/**
 * @param array $campaigns
 * @param array $definitions
 * @return array
 */
function holeKampagneGesamtStats($campaigns, $definitions)
{
    $stats = [];
    $sql   = '';
    $date  = date_create($_SESSION['Kampagne']->cStamp);
    switch ((int)$_SESSION['Kampagne']->nAnsicht) {
        case 1:    // Monat
            $sql = "WHERE '" . date_format($date, 'Y-m') . "' = DATE_FORMAT(dErstellt, '%Y-%m')";
            break;
        case 2:    // Woche
            $dateParts = ermittleDatumWoche(date_format($date, 'Y-m-d'));
            $sql       = 'WHERE dErstellt BETWEEN FROM_UNIXTIME(' .
                $dateParts[0] . ", '%Y-%m-%d %H:%i:%s') AND FROM_UNIXTIME(" .
                $dateParts[1] . ", '%Y-%m-%d %H:%i:%s')";
            break;
        case 3:    // Tag
            $sql = "WHERE '" . date_format($date, 'Y-m-d') . "' = DATE_FORMAT(dErstellt, '%Y-%m-%d')";
            break;
    }
    if (GeneralObject::hasCount($campaigns) && GeneralObject::hasCount($definitions)) {
        foreach ($campaigns as $campaign) {
            foreach ($definitions as $definition) {
                $stats[$campaign->kKampagne][$definition->kKampagneDef] = 0;
                $stats['Gesamt'][$definition->kKampagneDef]             = 0;
            }
        }
    }

    $data = Shop::Container()->getDB()->query(
        'SELECT kKampagne, kKampagneDef, SUM(fWert) AS fAnzahl
            FROM tkampagnevorgang
            ' . $sql . '
            GROUP BY kKampagne, kKampagneDef',
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($data as $item) {
        $stats[$item->kKampagne][$item->kKampagneDef] = $item->fAnzahl;
    }
    if (isset($_SESSION['Kampagne']->nSort) && $_SESSION['Kampagne']->nSort > 0) {
        $sort = [];
        if ((int)$_SESSION['Kampagne']->nSort > 0 && count($stats) > 0) {
            foreach ($stats as $i => $stat) {
                $sort[$i] = $stat[$_SESSION['Kampagne']->nSort];
            }
        }
        if ($_SESSION['Kampagne']->cSort === 'ASC') {
            uasort($sort, 'kampagneSortASC');
        } else {
            uasort($sort, 'kampagneSortDESC');
        }
        $tmpStats = [];
        foreach ($sort as $i => $tmp) {
            $tmpStats[$i] = $stats[$i];
        }
        $stats = $tmpStats;
    }
    foreach ($data as $item) {
        $stats['Gesamt'][$item->kKampagneDef] += $item->fAnzahl;
    }

    return $stats;
}

/**
 * @param int $a
 * @param int $b
 * @return int
 */
function kampagneSortDESC($a, $b)
{
    if ($a == $b) {
        return 0;
    }

    return ($a > $b) ? -1 : 1;
}

/**
 * @param int $a
 * @param int $b
 * @return int
 */
function kampagneSortASC($a, $b)
{
    if ($a == $b) {
        return 0;
    }

    return ($a < $b) ? -1 : 1;
}

/**
 * @param int   $campaignID
 * @param array $definitions
 * @return array
 */
function holeKampagneDetailStats($campaignID, $definitions)
{
    // Zeitraum
    $whereSQL     = '';
    $daysPerMonth = date(
        't',
        mktime(
            0,
            0,
            0,
            $_SESSION['Kampagne']->cFromDate_arr['nMonat'],
            1,
            $_SESSION['Kampagne']->cFromDate_arr['nJahr']
        )
    );
    // Int String Work Around
    $month = $_SESSION['Kampagne']->cFromDate_arr['nMonat'];
    if ($month < 10) {
        $month = '0' . $month;
    }
    $day = $_SESSION['Kampagne']->cFromDate_arr['nTag'];
    if ($day < 10) {
        $day = '0' . $day;
    }

    switch ((int)$_SESSION['Kampagne']->nDetailAnsicht) {
        case 1:    // Jahr
            $whereSQL = " WHERE dErstellt BETWEEN '" . $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' .
                $_SESSION['Kampagne']->cFromDate_arr['nMonat'] . "-01' AND '" .
                $_SESSION['Kampagne']->cToDate_arr['nJahr'] . '-' .
                $_SESSION['Kampagne']->cToDate_arr['nMonat'] . '-' . $daysPerMonth . "'";
            if ($_SESSION['Kampagne']->cFromDate_arr['nJahr'] == $_SESSION['Kampagne']->cToDate_arr['nJahr']) {
                $whereSQL = " WHERE DATE_FORMAT(dErstellt, '%Y') = '" .
                    $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . "'";
            }
            break;
        case 2:    // Monat
            $whereSQL = " WHERE dErstellt BETWEEN '" . $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' .
                $_SESSION['Kampagne']->cFromDate_arr['nMonat'] .
                "-01' AND '" . $_SESSION['Kampagne']->cToDate_arr['nJahr'] . '-' .
                $_SESSION['Kampagne']->cToDate_arr['nMonat'] . '-' . $daysPerMonth . "'";
            if ($_SESSION['Kampagne']->cFromDate_arr['nJahr'] == $_SESSION['Kampagne']->cToDate_arr['nJahr']
                && $_SESSION['Kampagne']->cFromDate_arr['nMonat'] == $_SESSION['Kampagne']->cToDate_arr['nMonat']
            ) {
                $whereSQL = " WHERE DATE_FORMAT(dErstellt, '%Y-%m') = '" .
                    $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' . $month . "'";
            }
            break;
        case 3:    // Woche
            $weekStart = ermittleDatumWoche($_SESSION['Kampagne']->cFromDate);
            $weekEnd   = ermittleDatumWoche($_SESSION['Kampagne']->cToDate);
            $whereSQL  = " WHERE dErstellt BETWEEN '" .
                date('Y-m-d H:i:s', $weekStart[0]) . "' AND '" .
                date('Y-m-d H:i:s', $weekEnd[1]) . "'";
            break;
        case 4:    // Tag
            $whereSQL = " WHERE dErstellt BETWEEN '" . $_SESSION['Kampagne']->cFromDate .
                "' AND '" . $_SESSION['Kampagne']->cToDate . "'";
            if ($_SESSION['Kampagne']->cFromDate == $_SESSION['Kampagne']->cToDate) {
                $whereSQL = " WHERE DATE_FORMAT(dErstellt, '%Y-%m-%d') = '" .
                    $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' . $month . '-' . $day . "'";
            }
            break;
    }

    switch ((int)$_SESSION['Kampagne']->nDetailAnsicht) {
        case 1:    // Jahr
            $selectSQL = "DATE_FORMAT(dErstellt, '%Y') AS cDatum";
            $groupSQL  = 'GROUP BY YEAR(dErstellt)';
            break;
        case 2:    // Monat
            $selectSQL = "DATE_FORMAT(dErstellt, '%Y-%m') AS cDatum";
            $groupSQL  = 'GROUP BY MONTH(dErstellt), YEAR(dErstellt)';
            break;
        case 3:    // Woche
            $selectSQL = 'WEEK(dErstellt, 1) AS cDatum';
            $groupSQL  = 'GROUP BY WEEK(dErstellt, 1), YEAR(dErstellt)';
            break;
        case 4:    // Tag
            $selectSQL = "DATE_FORMAT(dErstellt, '%Y-%m-%d') AS cDatum";
            $groupSQL  = 'GROUP BY DAY(dErstellt), YEAR(dErstellt), MONTH(dErstellt)';
            break;
        default:
            return [];
    }
    // Zeitraum
    $timeSpans = gibDetailDatumZeitraum();
    $stats     = Shop::Container()->getDB()->query(
        'SELECT kKampagne, kKampagneDef, SUM(fWert) AS fAnzahl, ' . $selectSQL . '
            FROM tkampagnevorgang
            ' . $whereSQL . '
                AND kKampagne = ' . $campaignID . '
            ' . $groupSQL . ', kKampagneDef',
        ReturnType::ARRAY_OF_OBJECTS
    );
    // Vorbelegen
    $statsAssoc = [];
    if (is_array($definitions)
        && is_array($timeSpans['cDatum'])
        && count($definitions) > 0
        && count($timeSpans['cDatum']) > 0
    ) {
        foreach ($timeSpans['cDatum'] as $i => $timeSpan) {
            if (!isset($statsAssoc[$timeSpan]['cDatum'])) {
                $statsAssoc[$timeSpan]['cDatum'] = $timeSpans['cDatumFull'][$i];
            }

            foreach ($definitions as $definition) {
                $statsAssoc[$timeSpan][$definition->kKampagneDef] = 0;
            }
        }
    }
    // Finde den maximalen Wert heraus, um die Höhe des Graphen zu ermitteln
    $graphMax = []; // Assoc Array key = kKampagneDef
    if (GeneralObject::hasCount($stats) && GeneralObject::hasCount($definitions)) {
        foreach ($stats as $stat) {
            foreach ($definitions as $definition) {
                if (isset($statsAssoc[$stat->cDatum][$definition->kKampagneDef])) {
                    $statsAssoc[$stat->cDatum][$stat->kKampagneDef] = $stat->fAnzahl;
                    if (!isset($graphMax[$stat->kKampagneDef])) {
                        $graphMax[$stat->kKampagneDef] = $stat->fAnzahl;
                    } elseif ($graphMax[$stat->kKampagneDef] < $stat->fAnzahl) {
                        $graphMax[$stat->kKampagneDef] = $stat->fAnzahl;
                    }
                }
            }
        }
    }
    if (!isset($_SESSION['Kampagne']->oKampagneDetailGraph)) {
        $_SESSION['Kampagne']->oKampagneDetailGraph = new stdClass();
    }
    $_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDetailGraph_arr = $statsAssoc;
    $_SESSION['Kampagne']->oKampagneDetailGraph->nGraphMaxAssoc_arr       = $graphMax;

    // Maximal 31 Einträge pro Graph
    if (count($_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDetailGraph_arr) > 31) {
        $key     = count($_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDetailGraph_arr) - 31;
        $tmpData = [];
        foreach ($_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDetailGraph_arr as $i => $graph) {
            if ($key <= 0) {
                $tmpData[$i] = $graph;
            }
            $key--;
        }

        $_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDetailGraph_arr = $tmpData;
    }
    // Gesamtstats
    if (is_array($statsAssoc) && count($statsAssoc) > 0) {
        foreach ($statsAssoc as $statDefinitionsAssoc) {
            foreach ($statDefinitionsAssoc as $definitionID => $item) {
                if ($definitionID !== 'cDatum') {
                    if (!isset($statsAssoc['Gesamt'][$definitionID])) {
                        $statsAssoc['Gesamt'][$definitionID] = $item;
                    } else {
                        $statsAssoc['Gesamt'][$definitionID] += $item;
                    }
                }
            }
        }
    }

    return $statsAssoc;
}

/**
 * @param int    $campaignID
 * @param object $definition
 * @param string $cStamp
 * @param string $text
 * @param array  $members
 * @param string $sql
 * @return array
 */
function holeKampagneDefDetailStats($campaignID, $definition, $cStamp, &$text, &$members, $sql)
{
    $cryptoService = Shop::Container()->getCryptoService();
    $data          = [];
    if ((int)$campaignID <= 0 || (int)$definition->kKampagneDef <= 0 || mb_strlen($cStamp) === 0) {
        return $data;
    }
    $select = '';
    $where  = '';
    baueDefDetailSELECTWHERE($select, $where, $cStamp);

    $stats = Shop::Container()->getDB()->query(
        'SELECT kKampagne, kKampagneDef, kKey ' . $select . '
            FROM tkampagnevorgang
            ' . $where . '
                AND kKampagne = ' . (int)$campaignID . '
                AND kKampagneDef = ' . (int)$definition->kKampagneDef . $sql,
        ReturnType::ARRAY_OF_OBJECTS
    );
    // Stamp Text
    switch ((int)$_SESSION['Kampagne']->nDetailAnsicht) {
        case 1:    // Jahr
            $text = $stats[0]->cStampText;
            break;
        case 2:    // Monat
            $textParts = isset($stats[0]->cStampText)
                ? explode('.', $stats[0]->cStampText)
                : [];
            $month     = $textParts [0] ?? '';
            $year      = $textParts [1] ?? '';
            $text      = mappeENGMonat($month) . ' ' . $year;
            break;
        case 3:    // Woche
            $dates = ermittleDatumWoche($stats[0]->cStampText);
            $text  = date('d.m.Y', $dates[0]) . ' - ' . date('d.m.Y', $dates[1]);
            break;
        case 4:    // Tag
            $text = $stats[0]->cStampText;
            break;
    }

    // Kampagnendefinitionen
    switch ((int)$definition->kKampagneDef) {
        case KAMPAGNE_DEF_HIT:    // HIT
            $data = Shop::Container()->getDB()->query(
                'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                    $select . ", tkampagnevorgang.cCustomData, 
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tbesucher.cIP IS NULL, tbesucherarchiv.cIP, tbesucher.cIP) AS cIP,
                    IF(tbesucher.cReferer IS NULL, tbesucherarchiv.cReferer, tbesucher.cReferer) AS cReferer,
                    IF(tbesucher.cEinstiegsseite IS NULL, 
                        tbesucherarchiv.cEinstiegsseite, 
                        tbesucher.cEinstiegsseite
                    ) AS cEinstiegsseite,
                    IF(tbesucher.cBrowser IS NULL, tbesucherarchiv.cBrowser, tbesucher.cBrowser) AS cBrowser,
                    DATE_FORMAT(IF(tbesucher.dZeit IS NULL,
                        tbesucherarchiv.dZeit, 
                        tbesucher.dZeit
                    ), '%d.%m.%Y %H:%i') AS dErstellt_DE,
                    tbesucherbot.cUserAgent
                    FROM tkampagnevorgang
                    LEFT JOIN tbesucher ON tbesucher.kBesucher = tkampagnevorgang.kKey
                    LEFT JOIN tbesucherarchiv ON tbesucherarchiv.kBesucher = tkampagnevorgang.kKey
                    LEFT JOIN tbesucherbot ON tbesucherbot.kBesucherBot = tbesucher.kBesucherBot
                    " . $where . '
                        AND kKampagne = ' . (int)$campaignID . '
                        AND kKampagneDef = ' . (int)$definition->kKampagneDef . '
                    ORDER BY tkampagnevorgang.dErstellt DESC' . $sql,
                ReturnType::ARRAY_OF_OBJECTS
            );

            if (is_array($data) && count($data) > 0) {
                foreach ($data as $i => $oDaten) {
                    $customDataParts = explode(';', $oDaten->cCustomData);
                    $cEinstiegsseite = $customDataParts [0] ?? '';
                    $referer         = $customDataParts [1] ?? '';

                    $data[$i]->cEinstiegsseite = $cEinstiegsseite;
                    $data[$i]->cReferer        = $referer;
                }

                $members = [
                    'cIP'                 => 'IP-Adresse',
                    'cReferer'            => 'Referer',
                    'cEinstiegsseite'     => 'Einstiegsseite',
                    'cBrowser'            => 'Browser',
                    'cUserAgent'          => 'Suchmaschine',
                    'dErstellt_DE'        => 'Datum',
                    'dErstelltVorgang_DE' => 'Vorgangsdatum'
                ];
            }
            break;
        case KAMPAGNE_DEF_VERKAUF:    // VERKAUF
            $data = Shop::Container()->getDB()->query(
                'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                    $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tkunde.cVorname IS NULL, 'n.v.', tkunde.cVorname) AS cVorname,
                    IF(tkunde.cNachname IS NULL, 'n.v.', tkunde.cNachname) AS cNachname,
                    IF(tkunde.cFirma IS NULL, 'n.v.', tkunde.cFirma) AS cFirma,
                    IF(tkunde.cMail IS NULL, 'n.v.', tkunde.cMail) AS cMail,
                    IF(tkunde.nRegistriert IS NULL, 'n.v.', tkunde.nRegistriert) AS nRegistriert,
                    IF(tbestellung.cZahlungsartName IS NULL,
                        'n.v.',
                         tbestellung.cZahlungsartName
                     ) AS cZahlungsartName,
                    IF(tbestellung.cVersandartName IS NULL,
                        'n.v.', 
                        tbestellung.cVersandartName
                    ) AS cVersandartName,
                    IF(tbestellung.fGesamtsumme IS NULL, 'n.v.', tbestellung.fGesamtsumme) AS fGesamtsumme,
                    IF(tbestellung.cBestellNr IS NULL, 'n.v.', tbestellung.cBestellNr) AS cBestellNr,
                    IF(tbestellung.cStatus IS NULL, 'n.v.', tbestellung.cStatus) AS cStatus,
                    DATE_FORMAT(tbestellung.dErstellt, '%d.%m.%Y') AS dErstellt_DE
                    FROM tkampagnevorgang
                    LEFT JOIN tbestellung ON tbestellung.kBestellung = tkampagnevorgang.kKey
                    LEFT JOIN tkunde ON tkunde.kKunde = tbestellung.kKunde
                    " . $where . '
                        AND kKampagne = ' . (int)$campaignID . '
                        AND kKampagneDef = ' . (int)$definition->kKampagneDef . '
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );

            if (is_array($data) && count($data) > 0) {
                $dCount = count($data);
                for ($i = 0; $i < $dCount; $i++) {
                    if ($data[$i]->cNachname !== 'n.v.') {
                        $data[$i]->cNachname = trim($cryptoService->decryptXTEA($data[$i]->cNachname));
                    }
                    if ($data[$i]->cFirma !== 'n.v.') {
                        $data[$i]->cFirma = trim($cryptoService->decryptXTEA($data[$i]->cFirma));
                    }
                    if ($data[$i]->nRegistriert !== 'n.v.') {
                        $data[$i]->nRegistriert = (int)$data[$i]->nRegistriert === 1
                            ? __('yes')
                            : __('no');
                    }
                    if ($data[$i]->fGesamtsumme !== 'n.v.') {
                        $data[$i]->fGesamtsumme = Preise::getLocalizedPriceString($data[$i]->fGesamtsumme);
                    }
                    if ($data[$i]->cStatus !== 'n.v.') {
                        $data[$i]->cStatus = lang_bestellstatus($data[$i]->cStatus);
                    }
                }

                $members = [
                    'cZahlungsartName'    => 'Zahlungsart',
                    'cVersandartName'     => 'Versandart',
                    'nRegistriert'        => 'Registrierter Kunde',
                    'cVorname'            => 'Vorname',
                    'cNachname'           => 'Nachname',
                    'cStatus'             => 'Status',
                    'cBestellNr'          => 'BestellNr',
                    'fGesamtsumme'        => 'Bestellwert',
                    'dErstellt_DE'        => 'Bestelldatum',
                    'dErstelltVorgang_DE' => 'Vorgangsdatum'
                ];
            }
            break;
        case KAMPAGNE_DEF_ANMELDUNG:    // ANMELDUNG
            $data = Shop::Container()->getDB()->query(
                'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                    $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tkunde.cVorname IS NULL, 'n.v.', tkunde.cVorname) AS cVorname,
                    IF(tkunde.cNachname IS NULL, 'n.v.', tkunde.cNachname) AS cNachname,
                    IF(tkunde.cFirma IS NULL, 'n.v.', tkunde.cFirma) AS cFirma,
                    IF(tkunde.cMail IS NULL, 'n.v.', tkunde.cMail) AS cMail,
                    IF(tkunde.nRegistriert IS NULL, 'n.v.', tkunde.nRegistriert) AS nRegistriert,
                    DATE_FORMAT(tkunde.dErstellt, '%d.%m.%Y') AS dErstellt_DE
                    FROM tkampagnevorgang
                    LEFT JOIN tkunde ON tkunde.kKunde = tkampagnevorgang.kKey
                    " . $where . '
                        AND kKampagne = ' . (int)$campaignID . '
                        AND kKampagneDef = ' . (int)$definition->kKampagneDef . '
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );

            if (is_array($data) && count($data) > 0) {
                $count = count($data);
                for ($i = 0; $i < $count; $i++) {
                    if ($data[$i]->cNachname !== 'n.v.') {
                        $data[$i]->cNachname = trim($cryptoService->decryptXTEA($data[$i]->cNachname));
                    }
                    if ($data[$i]->cFirma !== 'n.v.') {
                        $data[$i]->cFirma = trim($cryptoService->decryptXTEA($data[$i]->cFirma));
                    }
                    if ($data[$i]->nRegistriert !== 'n.v.') {
                        $data[$i]->nRegistriert = ((int)$data[$i]->nRegistriert === 1)
                            ? __('yes')
                            : __('no');
                    }
                }

                $members = [
                    'cVorname'            => 'Vorname',
                    'cNachname'           => 'Nachname',
                    'cFirma'              => 'Firma',
                    'cMail'               => 'eMail',
                    'nRegistriert'        => 'Registriert',
                    'dErstellt_DE'        => 'Anmeldedatum',
                    'dErstelltVorgang_DE' => 'Vorgangsdatum'
                ];
            }
            break;
        case KAMPAGNE_DEF_VERKAUFSSUMME:    // VERKAUFSSUMME
            $data   = Shop::Container()->getDB()->query(
                'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                    $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tkunde.cVorname IS NULL, 'n.v.', tkunde.cVorname) AS cVorname,
                    IF(tkunde.cNachname IS NULL, 'n.v.', tkunde.cNachname) AS cNachname,
                    IF(tkunde.cFirma IS NULL, 'n.v.', tkunde.cFirma) AS cFirma,
                    IF(tkunde.cMail IS NULL, 'n.v.', tkunde.cMail) AS cMail,
                    IF(tkunde.nRegistriert IS NULL, 'n.v.', tkunde.nRegistriert) AS nRegistriert,
                    IF(tbestellung.cZahlungsartName IS NULL,
                        'n.v.', 
                        tbestellung.cZahlungsartName
                    ) AS cZahlungsartName,
                    IF(tbestellung.cVersandartName IS NULL, 'n.v.', tbestellung.cVersandartName) AS cVersandartName,
                    IF(tbestellung.fGesamtsumme IS NULL, 'n.v.', tbestellung.fGesamtsumme) AS fGesamtsumme,
                    IF(tbestellung.cBestellNr IS NULL, 'n.v.', tbestellung.cBestellNr) AS cBestellNr,
                    IF(tbestellung.cStatus IS NULL, 'n.v.', tbestellung.cStatus) AS cStatus,
                    DATE_FORMAT(tbestellung.dErstellt, '%d.%m.%Y') AS dErstellt_DE
                    FROM tkampagnevorgang
                    LEFT JOIN tbestellung ON tbestellung.kBestellung = tkampagnevorgang.kKey
                    LEFT JOIN tkunde ON tkunde.kKunde = tbestellung.kKunde
                    " . $where . '
                        AND kKampagne = ' . (int)$campaignID . '
                        AND kKampagneDef = ' . (int)$definition->kKampagneDef . '
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
            $dCount = count($data);
            if (is_array($data) && $dCount > 0) {
                for ($i = 0; $i < $dCount; $i++) {
                    if ($data[$i]->cNachname !== 'n.v.') {
                        $data[$i]->cNachname = trim($cryptoService->decryptXTEA($data[$i]->cNachname));
                    }
                    if ($data[$i]->cFirma !== 'n.v.') {
                        $data[$i]->cFirma = trim($cryptoService->decryptXTEA($data[$i]->cFirma));
                    }
                    if ($data[$i]->nRegistriert !== 'n.v.') {
                        $data[$i]->nRegistriert = ((int)$data[$i]->nRegistriert === 1)
                            ? __('yes')
                            : __('no');
                    }
                    if ($data[$i]->fGesamtsumme !== 'n.v.') {
                        $data[$i]->fGesamtsumme = Preise::getLocalizedPriceString($data[$i]->fGesamtsumme);
                    }
                    if ($data[$i]->cStatus !== 'n.v.') {
                        $data[$i]->cStatus = lang_bestellstatus($data[$i]->cStatus);
                    }
                }

                $members = [
                    'cZahlungsartName'    => 'Zahlungsart',
                    'cVersandartName'     => 'Versandart',
                    'nRegistriert'        => 'Registrierter Kunde',
                    'cVorname'            => 'Vorname',
                    'cNachname'           => 'Nachname',
                    'cStatus'             => 'Status',
                    'cBestellNr'          => 'BestellNr',
                    'fGesamtsumme'        => 'Bestellwert',
                    'dErstellt_DE'        => 'Bestelldatum',
                    'dErstelltVorgang_DE' => 'Vorgangsdatum'
                ];
            }
            break;
        case KAMPAGNE_DEF_FRAGEZUMPRODUKT:    // FRAGEZUMPRODUKT
            $data = Shop::Container()->getDB()->query(
                'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                    $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tproduktanfragehistory.cVorname IS NULL,
                        'n.v.',
                        tproduktanfragehistory.cVorname
                    ) AS cVorname,
                    IF(tproduktanfragehistory.cNachname IS NULL, 
                        'n.v.', 
                        tproduktanfragehistory.cNachname
                    ) AS cNachname,
                    IF(tproduktanfragehistory.cFirma IS NULL, 'n.v.', tproduktanfragehistory.cFirma) AS cFirma,
                    IF(tproduktanfragehistory.cTel IS NULL, 'n.v.', tproduktanfragehistory.cTel) AS cTel,
                    IF(tproduktanfragehistory.cMail IS NULL, 'n.v.', tproduktanfragehistory.cMail) AS cMail,
                    IF(tproduktanfragehistory.cNachricht IS NULL,
                        'n.v.', 
                        tproduktanfragehistory.cNachricht
                    ) AS cNachricht,
                    IF(tartikel.cName IS NULL, 'n.v.', tartikel.cName) AS cArtikelname,
                    IF(tartikel.cArtNr IS NULL, 'n.v.', tartikel.cArtNr) AS cArtNr,
                    DATE_FORMAT(tproduktanfragehistory.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
                    FROM tkampagnevorgang
                    LEFT JOIN tproduktanfragehistory 
                        ON tproduktanfragehistory.kProduktanfrageHistory = tkampagnevorgang.kKey
                    LEFT JOIN tartikel ON tartikel.kArtikel = tproduktanfragehistory.kArtikel
                    " . $where . '
                        AND kKampagne = ' . (int)$campaignID . '
                        AND kKampagneDef = ' . (int)$definition->kKampagneDef . '
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );

            if (is_array($data) && count($data) > 0) {
                $members = [
                    'cArtikelname'        => 'Artikel',
                    'cArtNr'              => 'Artikelnummer',
                    'cVorname'            => 'Vorname',
                    'cNachname'           => 'Nachname',
                    'cFirma'              => 'Firma',
                    'cTel'                => 'Telefon',
                    'cMail'               => 'eMail',
                    'cNachricht'          => 'Nachricht',
                    'dErstellt_DE'        => 'Erstellt am',
                    'dErstelltVorgang_DE' => 'Vorgangsdatum'
                ];
            }

            break;
        case KAMPAGNE_DEF_VERFUEGBARKEITSANFRAGE:    // VERFUEGBARKEITSANFRAGE
            $data = Shop::Container()->getDB()->query(
                'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                    $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tverfuegbarkeitsbenachrichtigung.cVorname IS NULL,
                        'n.v.',
                         tverfuegbarkeitsbenachrichtigung.cVorname
                    ) AS cVorname,
                    IF(tverfuegbarkeitsbenachrichtigung.cNachname IS NULL,
                        'n.v.',
                         tverfuegbarkeitsbenachrichtigung.cNachname
                    ) AS cNachname,
                    IF(tverfuegbarkeitsbenachrichtigung.cMail IS NULL, 
                        'n.v.',
                        tverfuegbarkeitsbenachrichtigung.cMail
                    ) AS cMail,
                    IF(tverfuegbarkeitsbenachrichtigung.cAbgeholt IS NULL,
                        'n.v.',
                        tverfuegbarkeitsbenachrichtigung.cAbgeholt
                    ) AS cAbgeholt,
                    IF(tartikel.cName IS NULL, 'n.v.', tartikel.cName) AS cArtikelname,
                    IF(tartikel.cArtNr IS NULL, 'n.v.', tartikel.cArtNr) AS cArtNr,
                    DATE_FORMAT(tverfuegbarkeitsbenachrichtigung.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
                    FROM tkampagnevorgang
                    LEFT JOIN tverfuegbarkeitsbenachrichtigung 
                            ON tverfuegbarkeitsbenachrichtigung.kVerfuegbarkeitsbenachrichtigung =
                                tkampagnevorgang.kKey
                    LEFT JOIN tartikel 
                            ON tartikel.kArtikel = tverfuegbarkeitsbenachrichtigung.kArtikel
                    " . $where . '
                        AND kKampagne = ' . (int)$campaignID . '
                        AND kKampagneDef = ' . (int)$definition->kKampagneDef . '
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );

            if (is_array($data) && count($data) > 0) {
                $members = [
                    'cArtikelname'        => 'Artikel',
                    'cArtNr'              => 'Artikelnummer',
                    'cVorname'            => 'Vorname',
                    'cNachname'           => 'Nachname',
                    'cMail'               => 'eMail',
                    'cAbgeholt'           => 'Abgeholt durch Wawi',
                    'dErstellt_DE'        => 'Erstellt am',
                    'dErstelltVorgang_DE' => 'Vorgangsdatum'
                ];
            }

            break;
        case KAMPAGNE_DEF_LOGIN:    // LOGIN
            $data   = Shop::Container()->getDB()->query(
                'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tkunde.cVorname IS NULL, 'n.v.', tkunde.cVorname) AS cVorname,
                    IF(tkunde.cNachname IS NULL, 'n.v.', tkunde.cNachname) AS cNachname,
                    IF(tkunde.cFirma IS NULL, 'n.v.', tkunde.cFirma) AS cFirma,
                    IF(tkunde.cMail IS NULL, 'n.v.', tkunde.cMail) AS cMail,
                    IF(tkunde.nRegistriert IS NULL, 'n.v.', tkunde.nRegistriert) AS nRegistriert,
                    DATE_FORMAT(tkunde.dErstellt, '%d.%m.%Y') AS dErstellt_DE
                    FROM tkampagnevorgang
                    LEFT JOIN tkunde 
                            ON tkunde.kKunde = tkampagnevorgang.kKey
                    " . $where . '
                        AND kKampagne = ' . (int)$campaignID . '
                        AND kKampagneDef = ' . (int)$definition->kKampagneDef . '
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
            $dCount = count($data);
            if (is_array($data) && $dCount > 0) {
                for ($i = 0; $i < $dCount; $i++) {
                    if ($data[$i]->cNachname !== 'n.v.') {
                        $data[$i]->cNachname = trim($cryptoService->decryptXTEA($data[$i]->cNachname));
                    }
                    if ($data[$i]->cFirma !== 'n.v.') {
                        $data[$i]->cFirma = trim($cryptoService->decryptXTEA($data[$i]->cFirma));
                    }

                    if ($data[$i]->nRegistriert !== 'n.v.') {
                        $data[$i]->nRegistriert = ((int)$data[$i]->nRegistriert === 1)
                            ? __('yes')
                            : __('no');
                    }
                }

                $members = [
                    'cVorname'            => 'Vorname',
                    'cNachname'           => 'Nachname',
                    'cFirma'              => 'Firma',
                    'cMail'               => 'eMail',
                    'nRegistriert'        => 'Registriert',
                    'dErstellt_DE'        => 'Anmeldedatum',
                    'dErstelltVorgang_DE' => 'Vorgangsdatum'
                ];
            }
            break;
        case KAMPAGNE_DEF_WUNSCHLISTE:    // WUNSCHLISTE
            $data   = Shop::Container()->getDB()->query(
                'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tkunde.cVorname IS NULL, 'n.v.', tkunde.cVorname) AS cVorname,
                    IF(tkunde.cNachname IS NULL, 'n.v.', tkunde.cNachname) AS cNachname,
                    IF(tkunde.cFirma IS NULL, 'n.v.', tkunde.cFirma) AS cFirma,
                    IF(tkunde.cMail IS NULL, 'n.v.', tkunde.cMail) AS cMail,
                    IF(tkunde.nRegistriert IS NULL, 'n.v.', tkunde.nRegistriert) AS nRegistriert,
                    IF(tartikel.cName IS NULL, 'n.v.', tartikel.cName) AS cArtikelname,
                    IF(tartikel.cArtNr IS NULL, 'n.v.', tartikel.cArtNr) AS cArtNr,
                    DATE_FORMAT(twunschlistepos.dHinzugefuegt, '%d.%m.%Y') AS dErstellt_DE
                    FROM tkampagnevorgang
                    LEFT JOIN twunschlistepos ON twunschlistepos.kWunschlistePos = tkampagnevorgang.kKey
                    LEFT JOIN twunschliste ON twunschliste.kWunschliste = twunschlistepos.kWunschliste
                    LEFT JOIN tkunde ON tkunde.kKunde = twunschliste.kKunde
                    LEFT JOIN tartikel ON tartikel.kArtikel = twunschlistepos.kArtikel
                    " . $where . '
                        AND kKampagne = ' . (int)$campaignID . '
                        AND kKampagneDef = ' . (int)$definition->kKampagneDef . '
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
            $dCount = count($data);
            if (is_array($data) && $dCount > 0) {
                for ($i = 0; $i < $dCount; $i++) {
                    if ($data[$i]->cNachname !== 'n.v.') {
                        $data[$i]->cNachname = trim($cryptoService->decryptXTEA($data[$i]->cNachname));
                    }
                    if ($data[$i]->cFirma !== 'n.v.') {
                        $data[$i]->cFirma = trim($cryptoService->decryptXTEA($data[$i]->cFirma));
                    }

                    if ($data[$i]->nRegistriert !== 'n.v.') {
                        $data[$i]->nRegistriert = ((int)$data[$i]->nRegistriert === 1)
                            ? __('yes')
                            : __('no');
                    }
                }

                $members = [
                    'cArtikelname'        => 'Artikel',
                    'cArtNr'              => 'Artikelnummer',
                    'cVorname'            => 'Vorname',
                    'cNachname'           => 'Nachname',
                    'cFirma'              => 'Firma',
                    'cMail'               => 'eMail',
                    'nRegistriert'        => 'Registriert',
                    'dErstellt_DE'        => 'Anmeldedatum',
                    'dErstelltVorgang_DE' => 'Vorgangsdatum'
                ];
            }
            break;
        case KAMPAGNE_DEF_WARENKORB:    // WARENKORB
            $customerGroupID = CustomerGroup::getDefaultGroupID();

            $data = Shop::Container()->getDB()->query(
                'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tartikel.kArtikel IS NULL, 'n.v.', tartikel.kArtikel) AS kArtikel,
                    if(tartikel.cName IS NULL, 'n.v.', tartikel.cName) AS cName,
                    IF(tartikel.fLagerbestand IS NULL, 'n.v.', tartikel.fLagerbestand) AS fLagerbestand,
                    IF(tartikel.cArtNr IS NULL, 'n.v.', tartikel.cArtNr) AS cArtNr,
                    IF(tartikel.fMwSt IS NULL, 'n.v.', tartikel.fMwSt) AS fMwSt,
                    IF(tpreisdetail.fVKNetto IS NULL, 'n.v.', tpreisdetail.fVKNetto) AS fVKNetto,
                    DATE_FORMAT(tartikel.dLetzteAktualisierung, '%d.%m.%Y %H:%i') AS dLetzteAktualisierung_DE
                    FROM tkampagnevorgang
                    LEFT JOIN tartikel ON tartikel.kArtikel = tkampagnevorgang.kKey
                    LEFT JOIN tpreis ON tpreis.kArtikel = tartikel.kArtikel
                        AND tpreis.kKundengruppe = " . $customerGroupID . '
                    LEFT JOIN tpreisdetail ON tpreisdetail.kPreis = tpreis.kPreis
                        AND tpreisdetail.nAnzahlAb = 0
                    ' . $where . '
                        AND tkampagnevorgang.kKampagne = ' . (int)$campaignID . '
                        AND tkampagnevorgang.kKampagneDef = ' . (int)$definition->kKampagneDef . '
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
            if (is_array($data) && count($data) > 0) {
                Frontend::getCustomerGroup()->setMayViewPrices(1);
                $count = count($data);
                for ($i = 0; $i < $count; $i++) {
                    if (isset($data[$i]->fVKNetto) && $data[$i]->fVKNetto > 0) {
                        $data[$i]->fVKNetto = Preise::getLocalizedPriceString($data[$i]->fVKNetto);
                    }
                    if (isset($data[$i]->fMwSt) && $data[$i]->fMwSt > 0) {
                        $data[$i]->fMwSt = number_format($data[$i]->fMwSt, 2) . '%';
                    }
                }

                $members = [
                    'cName'                    => 'Artikel',
                    'cArtNr'                   => 'Artikelnummer',
                    'fVKNetto'                 => 'Netto Preis',
                    'fMwSt'                    => 'MwSt',
                    'fLagerbestand'            => 'Lagerbestand',
                    'dLetzteAktualisierung_DE' => 'Letzte Aktualisierung',
                    'dErstelltVorgang_DE'      => 'Vorgangsdatum'
                ];
            }
            break;
        case KAMPAGNE_DEF_NEWSLETTER:    // NEWSLETTER
            $data = Shop::Container()->getDB()->query(
                'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tnewsletter.cName IS NULL, 'n.v.', tnewsletter.cName) AS cName,
                    IF(tnewsletter.cBetreff IS NULL, 'n.v.', tnewsletter.cBetreff) AS cBetreff,
                    DATE_FORMAT(tnewslettertrack.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltTrack_DE,
                    IF(tnewsletterempfaenger.cVorname IS NULL, 'n.v.', tnewsletterempfaenger.cVorname) AS cVorname,
                    IF(tnewsletterempfaenger.cNachname IS NULL,
                        'n.v.',
                        tnewsletterempfaenger.cNachname
                    ) AS cNachname,
                    IF(tnewsletterempfaenger.cEmail IS NULL, 'n.v.', tnewsletterempfaenger.cEmail) AS cEmail
                    FROM tkampagnevorgang
                    LEFT JOIN tnewslettertrack ON tnewslettertrack.kNewsletterTrack = tkampagnevorgang.kKey
                    LEFT JOIN tnewsletter ON tnewsletter.kNewsletter = tnewslettertrack.kNewsletter
                    LEFT JOIN tnewsletterempfaenger
                        ON tnewsletterempfaenger.kNewsletterEmpfaenger = tnewslettertrack.kNewsletterEmpfaenger
                    " . $where . '
                        AND tkampagnevorgang.kKampagne = ' . (int)$campaignID . '
                        AND tkampagnevorgang.kKampagneDef = ' . (int)$definition->kKampagneDef . '
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );

            if (is_array($data) && count($data) > 0) {
                $members = [
                    'cName'               => 'Newsletter',
                    'cBetreff'            => 'Betreff',
                    'cVorname'            => 'Vorname',
                    'cNachname'           => 'Nachname',
                    'cEmail'              => 'eMail',
                    'dErstelltTrack_DE'   => 'Datum der Öffnung',
                    'dErstelltVorgang_DE' => 'Vorgangsdatum'
                ];
            }
            break;
    }

    return $data;
}

/**
 * @param string $select
 * @param string $where
 * @param string $stamp
 */
function baueDefDetailSELECTWHERE(&$select, &$where, $stamp)
{
    $stamp = Shop::Container()->getDB()->escape($stamp);
    switch ((int)$_SESSION['Kampagne']->nDetailAnsicht) {
        case 1:    // Jahr
            $select = ", DATE_FORMAT(tkampagnevorgang.dErstellt, '%Y') AS cStampText";
            $where  = " WHERE DATE_FORMAT(tkampagnevorgang.dErstellt, '%Y') = '" . $stamp . "'";
            break;
        case 2:    // Monat
            $select = ", DATE_FORMAT(tkampagnevorgang.dErstellt, '%m.%Y') AS cStampText";
            $where  = " WHERE DATE_FORMAT(tkampagnevorgang.dErstellt, '%Y-%m') = '" . $stamp . "'";
            break;
        case 3:    // Woche
            $select = ", DATE_FORMAT(tkampagnevorgang.dErstellt, '%Y-%m-%d') AS cStampText";
            $where  = " WHERE DATE_FORMAT(tkampagnevorgang.dErstellt, '%u') = '" . $stamp . "'";
            break;
        case 4:    // Tag
            $select = ", DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y') AS cStampText";
            $where  = " WHERE DATE_FORMAT(tkampagnevorgang.dErstellt, '%Y-%m-%d') = '" . $stamp . "'";
            break;
        default:
            break;
    }
}

/**
 * @return array
 */
function gibDetailDatumZeitraum()
{
    $timeSpan               = [];
    $timeSpan['cDatum']     = [];
    $timeSpan['cDatumFull'] = [];
    switch ((int)$_SESSION['Kampagne']->nDetailAnsicht) {
        case 1:    // Jahr
            $nFromStamp  = mktime(
                0,
                0,
                0,
                $_SESSION['Kampagne']->cFromDate_arr['nMonat'],
                1,
                $_SESSION['Kampagne']->cFromDate_arr['nJahr']
            );
            $daysPerWeek = date('t', mktime(
                0,
                0,
                0,
                $_SESSION['Kampagne']->cToDate_arr['nMonat'],
                1,
                $_SESSION['Kampagne']->cToDate_arr['nJahr']
            ));
            $nToStamp    = mktime(
                0,
                0,
                0,
                $_SESSION['Kampagne']->cToDate_arr['nMonat'],
                (int)$daysPerWeek,
                $_SESSION['Kampagne']->cToDate_arr['nJahr']
            );
            $nTMPStamp   = $nFromStamp;
            while ($nTMPStamp <= $nToStamp) {
                $timeSpan['cDatum'][]     = date('Y', $nTMPStamp);
                $timeSpan['cDatumFull'][] = date('Y', $nTMPStamp);
                $nDiff                    = mktime(
                    0,
                    0,
                    0,
                    (int)date('m', $nTMPStamp),
                    (int)date('d', $nTMPStamp),
                    (int)date('Y', $nTMPStamp) + 1
                ) - $nTMPStamp;
                $nTMPStamp               += $nDiff;
            }
            break;
        case 2:    // Monat
            $nFromStamp  = mktime(
                0,
                0,
                0,
                $_SESSION['Kampagne']->cFromDate_arr['nMonat'],
                1,
                $_SESSION['Kampagne']->cFromDate_arr['nJahr']
            );
            $daysPerWeek = date(
                't',
                mktime(
                    0,
                    0,
                    0,
                    $_SESSION['Kampagne']->cToDate_arr['nMonat'],
                    1,
                    $_SESSION['Kampagne']->cToDate_arr['nJahr']
                )
            );
            $nToStamp    = mktime(
                0,
                0,
                0,
                $_SESSION['Kampagne']->cToDate_arr['nMonat'],
                (int)$daysPerWeek,
                $_SESSION['Kampagne']->cToDate_arr['nJahr']
            );
            $nTMPStamp   = $nFromStamp;
            while ($nTMPStamp <= $nToStamp) {
                $timeSpan['cDatum'][]     = date('Y-m', $nTMPStamp);
                $timeSpan['cDatumFull'][] = mappeENGMonat(date('m', $nTMPStamp)) . ' ' . date('Y', $nTMPStamp);
                $month                    = (int)date('m', $nTMPStamp) + 1;
                $year                     = (int)date('Y', $nTMPStamp);
                if ($month > 12) {
                    $month = 1;
                    $year++;
                }

                $nDiff = mktime(0, 0, 0, $month, (int)date('d', $nTMPStamp), $year) - $nTMPStamp;

                $nTMPStamp += $nDiff;
            }
            break;
        case 3:    // Woche
            $weekStamp  = ermittleDatumWoche($_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' .
                $_SESSION['Kampagne']->cFromDate_arr['nMonat'] . '-' .
                $_SESSION['Kampagne']->cFromDate_arr['nTag']);
            $nFromStamp = $weekStamp[0];
            $nToStamp   = mktime(
                0,
                0,
                0,
                $_SESSION['Kampagne']->cToDate_arr['nMonat'],
                $_SESSION['Kampagne']->cToDate_arr['nTag'],
                $_SESSION['Kampagne']->cToDate_arr['nJahr']
            );
            $nTMPStamp  = $nFromStamp;
            while ($nTMPStamp <= $nToStamp) {
                $weekStamp                = ermittleDatumWoche(date('Y-m-d', $nTMPStamp));
                $timeSpan['cDatum'][]     = date('Y-W', $nTMPStamp);
                $timeSpan['cDatumFull'][] = date('d.m.Y', $weekStamp[0]) .
                    ' - ' . date('d.m.Y', $weekStamp[1]);
                $daysPerWeek              = date('t', $nTMPStamp);

                $day   = (int)date('d', $weekStamp[1]) + 1;
                $month = (int)date('m', $weekStamp[1]);
                $year  = (int)date('Y', $weekStamp[1]);

                if ($day > $daysPerWeek) {
                    $day = 1;
                    $month++;

                    if ($month > 12) {
                        $month = 1;
                        $year++;
                    }
                }

                $nDiff = mktime(0, 0, 0, $month, $day, $year) - $nTMPStamp;

                $nTMPStamp += $nDiff;
            }
            break;
        case 4:    // Tag
            $nFromStamp = mktime(
                0,
                0,
                0,
                $_SESSION['Kampagne']->cFromDate_arr['nMonat'],
                $_SESSION['Kampagne']->cFromDate_arr['nTag'],
                $_SESSION['Kampagne']->cFromDate_arr['nJahr']
            );
            $nToStamp   = mktime(
                0,
                0,
                0,
                $_SESSION['Kampagne']->cToDate_arr['nMonat'],
                $_SESSION['Kampagne']->cToDate_arr['nTag'],
                $_SESSION['Kampagne']->cToDate_arr['nJahr']
            );
            $nTMPStamp  = $nFromStamp;
            while ($nTMPStamp <= $nToStamp) {
                $timeSpan['cDatum'][]     = date('Y-m-d', $nTMPStamp);
                $timeSpan['cDatumFull'][] = date('d.m.Y', $nTMPStamp);
                $daysPerWeek              = date('t', $nTMPStamp);
                $day                      = date('d', $nTMPStamp) + 1;
                $month                    = date('m', $nTMPStamp);
                $year                     = date('Y', $nTMPStamp);

                if ($day > $daysPerWeek) {
                    $day = 1;
                    $month++;

                    if ($month > 12) {
                        $month = 1;
                        $year++;
                    }
                }

                $nDiff = mktime(0, 0, 0, $month, $day, $year) - $nTMPStamp;

                $nTMPStamp += $nDiff;
            }
            break;
    }

    return $timeSpan;
}

/**
 * @param string $oldStamp
 * @param int    $direction - -1 = Vergangenheit, 1 = Zukunft
 * @param int    $view
 * @return string
 */
function gibStamp($oldStamp, int $direction, int $view): string
{
    if (mb_strlen($oldStamp) === 0 || !in_array($direction, [1, -1], true) || !in_array($view, [1, 2, 3], true)) {
        return $oldStamp;
    }

    switch ($view) {
        case 1:
            $interval = 'month';
            break;
        case 2:
            $interval = 'week';
            break;
        case 3:
        default:
            $interval = 'day';
            break;
    }
    $now     = date_create();
    $newDate = date_create($oldStamp)->modify(($direction === 1 ? '+' : '-') . '1 ' . $interval);

    return $newDate > $now
        ? $now->format('Y-m-d')
        : $newDate->format('Y-m-d');
}

/**
 * @param Campaign $campaign
 * @return int
 *
 * Returncodes:
 * 1 = Alles O.K.
 * 2 = Kampagne konnte nicht gespeichert werden
 * 3 = Kampagnenname ist leer
 * 4 = Kampagnenparamter ist leer
 * 5 = Kampagnenwert ist leer
 * 6 = Kampagnennamen schon vergeben
 * 7 = Kampagnenparameter schon vergeben
 */
function speicherKampagne($campaign)
{
    // Standardkampagnen (Interne) Werte herstellen
    if (isset($campaign->kKampagne) && ($campaign->kKampagne < 1000 && $campaign->kKampagne > 0)) {
        $data = Shop::Container()->getDB()->query(
            'SELECT *
                FROM tkampagne
                WHERE kKampagne = ' . (int)$campaign->kKampagne,
            ReturnType::SINGLE_OBJECT
        );

        if (isset($data->kKampagne)) {
            $campaign->cName      = $data->cName;
            $campaign->cWert      = $data->cWert;
            $campaign->nDynamisch = $data->nDynamisch;
        }
    }

    // Plausi
    if (mb_strlen($campaign->cName) === 0) {
        return 3;// Kampagnenname ist leer
    }
    if (mb_strlen($campaign->cParameter) === 0) {
        return 4;// Kampagnenparamter ist leer
    }
    if (mb_strlen($campaign->cWert) === 0 && $campaign->nDynamisch != 1) {
        return 5;//  Kampagnenwert ist leer
    }
    // Name schon vorhanden?
    $data = Shop::Container()->getDB()->queryPrepared(
        'SELECT kKampagne
            FROM tkampagne
            WHERE cName = :cName',
        ['cName' => $campaign->cName],
        ReturnType::SINGLE_OBJECT
    );

    if (isset($data->kKampagne)
        && $data->kKampagne > 0
        && (!isset($campaign->kKampagne) || (int)$campaign->kKampagne === 0)
    ) {
        return 6;// Kampagnennamen schon vergeben
    }
    // Parameter schon vorhanden?
    if (isset($campaign->nDynamisch) && (int)$campaign->nDynamisch === 1) {
        $data = Shop::Container()->getDB()->queryPrepared(
            'SELECT kKampagne
                FROM tkampagne
                WHERE cParameter = :param',
            ['param' => $campaign->cParameter],
            ReturnType::SINGLE_OBJECT
        );

        if (isset($data->kKampagne)
            && $data->kKampagne > 0
            && (!isset($campaign->kKampagne) || (int)$campaign->kKampagne === 0)
        ) {
            return 7;// Kampagnenparameter schon vergeben
        }
    }
    // Editieren?
    if (isset($campaign->kKampagne) && $campaign->kKampagne > 0) {
        $campaign->updateInDB();
    } else {
        $campaign->insertInDB();
    }
    Shop::Container()->getCache()->flush('campaigns');

    return 1;
}

/**
 * @param int $code
 * @return string
 */
function mappeFehlerCodeSpeichern(int $code)
{
    $msg = '';
    switch ($code) {
        case 2:
            $msg = __('errorCampaignSave');
            break;
        case 3:
            $msg = __('errorCampaignNameMissing');
            break;
        case 4:
            $msg = __('errorCampaignParameterMissing');
            break;
        case 5:
            $msg = __('errorCampaignValueMissing');
            break;
        case 6:
            $msg = __('errorCampaignNameDuplicate');
            break;
        case 7:
            $msg = __('errorCampaignParameterDuplicate');
            break;
        default:
            break;
    }

    return $msg;
}

/**
 * @param array $campaignIDs
 * @return int
 */
function loescheGewaehlteKampagnen(array $campaignIDs)
{
    if (count($campaignIDs) === 0) {
        return 0;
    }
    foreach (array_map('\intval', $campaignIDs) as $campaignID) {
        if ($campaignID < 1000) {
            // Nur externe Kampagnen sind löschbar
            continue;
        }
        (new Campaign($campaignID))->deleteInDB();
    }
    Shop::Container()->getCache()->flush('campaigns');

    return 1;
}

/**
 * @param DateTimeImmutable $date
 */
function setzeDetailZeitraum(DateTimeImmutable $date): void
{
    // 1 = Jahr
    // 2 = Monat
    // 3 = Woche
    // 4 = Tag
    if (!isset($_SESSION['Kampagne']->nDetailAnsicht)) {
        $_SESSION['Kampagne']->nDetailAnsicht = 2;
    }
    if (!isset($_SESSION['Kampagne']->cFromDate_arr)) {
        $_SESSION['Kampagne']->cFromDate_arr['nJahr']  = (int)$date->format('Y');
        $_SESSION['Kampagne']->cFromDate_arr['nMonat'] = (int)$date->format('n');
        $_SESSION['Kampagne']->cFromDate_arr['nTag']   = (int)$date->format('j');
    }
    if (!isset($_SESSION['Kampagne']->cToDate_arr)) {
        $_SESSION['Kampagne']->cToDate_arr['nJahr']  = (int)$date->format('Y');
        $_SESSION['Kampagne']->cToDate_arr['nMonat'] = (int)$date->format('n');
        $_SESSION['Kampagne']->cToDate_arr['nTag']   = (int)$date->format('j');
    }
    if (!isset($_SESSION['Kampagne']->cFromDate)) {
        $_SESSION['Kampagne']->cFromDate = $date->format('Y-m-d');
    }
    if (!isset($_SESSION['Kampagne']->cToDate)) {
        $_SESSION['Kampagne']->cToDate = $date->format('Y-m-d');
    }
    // Ansicht und Zeitraum
    if (Request::verifyGPCDataInt('zeitraum') === 1) {
        // Ansicht
        if (Request::postInt('nAnsicht') > 0) {
            $_SESSION['Kampagne']->nDetailAnsicht = Request::postInt('nAnsicht');
        }
        // Zeitraum
        if (Request::postInt('cFromDay') > 0
            && Request::postInt('cFromMonth') > 0
            && Request::postInt('cFromYear') > 0
        ) {
            $_SESSION['Kampagne']->cFromDate_arr['nJahr']  = Request::postInt('cFromYear');
            $_SESSION['Kampagne']->cFromDate_arr['nMonat'] = Request::postInt('cFromMonth');
            $_SESSION['Kampagne']->cFromDate_arr['nTag']   = Request::postInt('cFromDay');
            $_SESSION['Kampagne']->cFromDate               = Request::postInt('cFromYear') . '-' .
                Request::postInt('cFromMonth') . '-' .
                Request::postInt('cFromDay');
        }
        if (Request::postInt('cToDay') > 0 && Request::postInt('cToMonth') > 0 && Request::postInt('cToYear') > 0) {
            $_SESSION['Kampagne']->cToDate_arr['nJahr']  = Request::postInt('cToYear');
            $_SESSION['Kampagne']->cToDate_arr['nMonat'] = Request::postInt('cToMonth');
            $_SESSION['Kampagne']->cToDate_arr['nTag']   = Request::postInt('cToDay');
            $_SESSION['Kampagne']->cToDate               = Request::postInt('cToYear') . '-' .
                Request::postInt('cToMonth') . '-' . Request::postInt('cToDay');
        }
    }

    checkGesamtStatZeitParam();
}

/**
 * @return bool|string
 */
function checkGesamtStatZeitParam()
{
    $stamp = '';
    if (mb_strlen(Request::verifyGPDataString('cZeitParam')) === 0) {
        return $stamp;
    }
    $span      = base64_decode(Request::verifyGPDataString('cZeitParam'));
    $spanParts = explode(' - ', $span);
    $dateStart = $spanParts[0] ?? '';
    $dateEnd   = $spanParts[1] ?? '';
    if (mb_strlen($dateEnd) === 0) {
        [$startDay, $startMonth, $startYear] = explode('.', $dateStart);
        [$endDay, $endMonth, $endYear]       = explode('.', $dateStart);
    } else {
        [$startDay, $startMonth, $startYear] = explode('.', $dateStart);
        [$endDay, $endMonth, $endYear]       = explode('.', $dateEnd);
    }
    $_SESSION['Kampagne']->cToDate_arr['nJahr']    = (int)$endYear;
    $_SESSION['Kampagne']->cToDate_arr['nMonat']   = (int)$endMonth;
    $_SESSION['Kampagne']->cToDate_arr['nTag']     = (int)$endDay;
    $_SESSION['Kampagne']->cToDate                 = (int)$endYear . '-' .
        (int)$endMonth . '-' .
        (int)$endDay;
    $_SESSION['Kampagne']->cFromDate_arr['nJahr']  = (int)$startYear;
    $_SESSION['Kampagne']->cFromDate_arr['nMonat'] = (int)$startMonth;
    $_SESSION['Kampagne']->cFromDate_arr['nTag']   = (int)$startDay;
    $_SESSION['Kampagne']->cFromDate               = (int)$startYear . '-' .
        (int)$startMonth . '-' .
        (int)$startDay;
    // Int String Work Around
    $month = $_SESSION['Kampagne']->cFromDate_arr['nMonat'];
    if ($month < 10) {
        $month = '0' . $month;
    }

    $day = $_SESSION['Kampagne']->cFromDate_arr['nTag'];
    if ($day < 10) {
        $day = '0' . $day;
    }

    switch ((int)$_SESSION['Kampagne']->nAnsicht) {
        case 1:    // Monat
            $_SESSION['Kampagne']->nDetailAnsicht = 2;
            $stamp                                = $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' . $month;
            break;
        case 2: // Woche
            $_SESSION['Kampagne']->nDetailAnsicht = 3;
            $stamp                                = date(
                'W',
                mktime(
                    0,
                    0,
                    0,
                    $_SESSION['Kampagne']->cFromDate_arr['nMonat'],
                    $_SESSION['Kampagne']->cFromDate_arr['nTag'],
                    $_SESSION['Kampagne']->cFromDate_arr['nJahr']
                )
            );
            break;
        case 3: // Tag
            $_SESSION['Kampagne']->nDetailAnsicht = 4;
            $stamp                                = $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' .
                $month . '-' . $day;
            break;
    }

    return $stamp;
}

/**
 * @param string $month
 * @return string
 */
function mappeENGMonat($month)
{
    $translation = '';
    if (mb_strlen($month) > 0) {
        switch ($month) {
            case '01':
                $translation .= Shop::Lang()->get('january', 'news');
                break;
            case '02':
                $translation .= Shop::Lang()->get('february', 'news');
                break;
            case '03':
                $translation .= Shop::Lang()->get('march', 'news');
                break;
            case '04':
                $translation .= Shop::Lang()->get('april', 'news');
                break;
            case '05':
                $translation .= Shop::Lang()->get('may', 'news');
                break;
            case '06':
                $translation .= Shop::Lang()->get('june', 'news');
                break;
            case '07':
                $translation .= Shop::Lang()->get('july', 'news');
                break;
            case '08':
                $translation .= Shop::Lang()->get('august', 'news');
                break;
            case '09':
                $translation .= Shop::Lang()->get('september', 'news');
                break;
            case '10':
                $translation .= Shop::Lang()->get('october', 'news');
                break;
            case '11':
                $translation .= Shop::Lang()->get('november', 'news');
                break;
            case '12':
                $translation .= Shop::Lang()->get('december', 'news');
                break;
        }
    }

    return $translation;
}

/**
 * @return array
 */
function GetTypes()
{
    return [
        1  => __('Hit'),
        2  => __('Verkauf'),
        3  => __('Anmeldung'),
        4  => __('Verkaufssumme'),
        5  => __('Frage zum Produkt'),
        6  => __('Verfügbarkeits-Anfrage'),
        7  => __('Login'),
        8  => __('Produkt auf Wunschliste'),
        9  => __('Produkt in den Warenkorb'),
        10 => __('Angeschaute Newsletter')
    ];
}

/**
 * @param int $type
 * @return string
 */
function GetKampTypeName($type)
{
    $types = GetTypes();

    return $types[$type] ?? '';
}

/**
 * @param array $stats
 * @param mixed $type
 * @return Linechart
 */
function PrepareLineChartKamp($stats, $type)
{
    $chart = new Linechart(['active' => false]);
    if (!is_array($stats) || count($stats) === 0) {
        return $chart;
    }
    $chart->setActive(true);
    $data = [];
    foreach ($stats as $Date => $Dates) {
        if (mb_strpos($Date, 'Gesamt') === false) {
            $x = '';
            foreach ($Dates as $Key => $Stat) {
                if (mb_strpos($Key, 'cDatum') !== false) {
                    $x = $Dates[$Key];
                }

                if ($Key == $type) {
                    $obj    = new stdClass();
                    $obj->y = (float)$Stat;

                    $chart->addAxis((string)$x);
                    $data[] = $obj;
                }
            }
        }
    }
    $chart->addSerie(GetKampTypeName($type), $data);
    $chart->memberToJSON();

    return $chart;
}
