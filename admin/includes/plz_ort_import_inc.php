<?php

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;

defined('PLZIMPORT_HOST') || define('PLZIMPORT_HOST', 'www.fa-technik.adfc.de');
defined('PLZIMPORT_URL') || define('PLZIMPORT_URL', 'http://' . PLZIMPORT_HOST . '/code/opengeodb/');
defined('PLZIMPORT_ISO_REGEX') || define('PLZIMPORT_ISO_REGEX', '/([A-Z]{2})\.tab/');
defined('PLZIMPORT_REGEX') || define(
    'PLZIMPORT_REGEX',
    '/<td><a href="([A-Z]{2}\.tab)">([A-Z]{2})\.tab<\/a><\/td>' .
    '<td[^>]*>([0-9]{2}\-[A-Za-z]{3}\-[0-9]{4}[0-9: ]+?) *<\/td><td[^>]*> *([0-9MK\.]+)<\/td>/'
);

/**
 * @return array
 */
function plzimportGetPLZOrt(): array
{
    $items = Shop::Container()->getDB()->query(
        'SELECT tplz.cLandISO, tland.cDeutsch, tland.cKontinent, COUNT(tplz.kPLZ) AS nPLZOrte, backup.nBackup
            FROM tplz
            INNER JOIN tland ON tland.cISO = tplz.cLandISO
            LEFT JOIN (
                SELECT tplz_backup.cLandISO, COUNT(tplz_backup.kPLZ) AS nBackup
                FROM tplz_backup
                GROUP BY tplz_backup.cLandISO
            ) AS backup ON backup.cLandISO = tplz.cLandISO
            GROUP BY tplz.cLandISO, tland.cDeutsch, tland.cKontinent
            ORDER BY tplz.cLandISO',
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($items as $key => $item) {
        $fName = PFAD_UPLOADS . $item->cLandISO . '.tab';
        if (($country = Shop::Container()->getCountryService()->getCountry($item->cLandISO)) !== null) {
            $item->cDeutsch   = $country->getName();
            $item->cKontinent = $country->getContinent();
        }
        if (is_file($fName)) {
            $items[$key]->cImportFile = $item->cLandISO . '.tab';
        }
    }

    return $items;
}

/**
 * @param string $target
 * @param array  $sessData
 * @param object $result
 * @return void
 */
function plzimportDoImport($target, array $sessData, $result): void
{
    $sessData['status'] = sprintf(__('importDataFrom'), $target);
    $runtime            = (int)ini_get('max_execution_time');
    $endTime            = time() + $runtime - 5; // 5 sek. Reserve
    $fHandle            = fopen(PFAD_UPLOADS . $target, 'r');
    $fLength            = filesize(PFAD_UPLOADS . $target);
    $read               = 0;
    $oPLZOrt            = (object)[
        'cPLZ'     => '',
        'cOrt'     => '',
        'cLandISO' => 'IMP',
    ];

    if ($fHandle === false) {
        $result->type    = 'danger';
        $result->message = sprintf(__('errorImportInvalid'), $target);

        return;
    }

    plzimportWriteSession('Import', $sessData);
    $db = Shop::Container()->getDB();
    if (preg_match(PLZIMPORT_ISO_REGEX, $target, $hits)) {
        $isoLand = $hits[1];

        if (isset($sessData['currentPos'])) {
            // Import wird partiell fortgesetzt
            $data = '';
            $read = $sessData['currentPos'];
            fseek($fHandle, $sessData['currentPos']);
        } else {
            $db->delete('tplz', 'cLandISO', 'IMP');
            // Erste Zeile nur Headerinformationen
            $data = fgetcsv($fHandle, 0, "\t");
        }

        while (!feof($fHandle)) {
            $read += mb_strlen(implode(',', $data));
            $data  = fgetcsv($fHandle, 0, "\t");

            if (isset($data[13]) && in_array($data[13], [6, 8])) {
                $plzs          = explode(',', $data[7]);
                $oPLZOrt->cOrt = $data[3];

                foreach ($plzs as $plz) {
                    $oPLZOrt->cPLZ = $plz;

                    if (!empty($oPLZOrt->cPLZ) && !empty($oPLZOrt->cOrt)) {
                        $db->insert('tplz', $oPLZOrt);
                    }
                }

                if ($fLength != 0) {
                    $sessData['step'] = 50 + round(40 / $fLength * $read);
                    plzimportWriteSession('Import', $sessData);
                }

                if ($runtime > 0 && time() >= $endTime) {
                    // max_execution_time erreicht - restart
                    $sessData['currentPos'] = ftell($fHandle);
                    plzimportWriteSession('Import', $sessData);
                    fclose($fHandle);

                    $cRedirectUrl = Shop::getAdminURL() . '/io.php?io=' .
                        urlencode(
                            json_encode(
                                [
                                    'name'   => 'plzimportActionDoImport',
                                    'params' => [$target, 'import', $sessData['step']]
                                ]
                            )
                        ) . '&token=' . Text::filterXSS($_SESSION['jtl_token']);
                    header('Location: ' . $cRedirectUrl);
                    exit;
                }
            }
        }

        $sessData['step']   = 90;
        $sessData['status'] = sprintf(__('createBackupFrom'), $isoLand);
        plzimportWriteSession('Import', $sessData);

        $db->delete('tplz_backup', 'cLandISO', $isoLand);
        $db->queryPrepared(
            'INSERT INTO tplz_backup SELECT * FROM tplz WHERE cLandISO = :isoCode',
            ['isoCode' => $isoLand],
            ReturnType::AFFECTED_ROWS
        );
        $db->delete('tplz', 'cLandISO', $isoLand);

        $sessData['step']   = 95;
        $sessData['status'] = sprintf(__('importProgress'), $isoLand);
        plzimportWriteSession('Import', $sessData);

        $db->update('tplz', 'cLandISO', 'IMP', (object)['cLandISO' => $isoLand]);

        $result->type    = 'success';
        $result->message = __('successImport');
    } else {
        $result->type    = 'danger';
        $result->message = __('errorParameterInvalid');
    }

    fclose($fHandle);
    unlink(PFAD_UPLOADS . $target);

    $sessData['step'] = 100;
    plzimportWriteSession('Import', $sessData);
}

/**
 * @param string $target
 * @param array  $sessData
 * @param object $result
 * @return void
 */
function plzimportDoDownload($target, array $sessData, $result): void
{
    $sessData['status'] = sprintf(__('downloadFrom'), $target);
    $runtime            = (int)ini_get('max_execution_time');
    $endTime            = time() + $runtime - 5; // 5 sek. Reserve
    $partSize           = 8 * 1024; // 8 KBytes
    $ioFile             = PLZIMPORT_URL . $target;
    $ioHandle           = fsockopen(PLZIMPORT_HOST, 80, $errNo, $errStr);
    $fHandle            = fopen(PFAD_UPLOADS . $target, 'w');
    $ioLength           = 0;
    if ($ioHandle === false) {
        $result->type    = 'danger';
        $result->message = sprintf(__('errorDownload'), $target);

        if (!empty($errStr)) {
            $result->message .= ' ' . $errStr;
        }

        return;
    }

    if ($fHandle === false) {
        $result->type    = 'danger';
        $result->message = sprintf(
            __('errorDownloadFileCreate'),
            $target,
            PLZIMPORT_URL
        );

        return;
    }

    fwrite($ioHandle, "GET {$ioFile} HTTP/1.1\r\n" .
        'Host: ' . PLZIMPORT_HOST . "\r\n" .
        "User-Agent: Mozilla/5.0\r\n" .
        "Keep-Alive: 115\r\n" .
        "Connection: keep-alive\r\n" .
        "\r\n");

    $line = '';
    while (!feof($ioHandle) && $line !== "\r\n") {
        $line = fgets($ioHandle);
        if (preg_match('/Content-Length: ([0-9\.]+)/', $line, $hits)) {
            $ioLength = (int)$hits[1];
        }
    }

    $buf     = fread($ioHandle, $partSize);
    $written = 0;
    while (!feof($ioHandle) && $buf !== false) {
        $written += fwrite($fHandle, $buf);
        $buf      = fread($ioHandle, $partSize);
        if ($buf === false) {
            fclose($fHandle);
            fclose($ioHandle);

            $result->type    = 'danger';
            $result->message = sprintf(__('errorDownload'), $target);

            return;
        }

        if ($ioLength !== 0) {
            $sessData['step'] = round(50 / $ioLength * $written);
            plzimportWriteSession('Import', $sessData);
        }

        if ($runtime > 0 && time() >= $endTime) {
            // max_execution_time erreicht - restart
            $result->type    = 'danger';
            $result->message = sprintf(__('errorDownloadTimeLong'), $target);

            return;
        }
    }

    if ($buf !== false) {
        fwrite($fHandle, $buf);
    }

    fclose($fHandle);
    fclose($ioHandle);

    $sessData['step'] = 50;
    plzimportWriteSession('Import', $sessData);

    $result->type    = 'success';
    $result->message = sprintf(__('successDownload'), $target);
    // Download fertig - weiter mit dem Import
    $cRedirectUrl = Shop::getAdminURL() . '/io.php?io=' .
        urlencode(
            json_encode(
                [
                    'name'   => 'plzimportActionDoImport',
                    'params' => [$target, 'import', $sessData['step']]
                ]
            )
        ) . '&token=' . Text::filterXSS($_SESSION['jtl_token']);
    header('Location: ' . $cRedirectUrl);
    exit;
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 */
function plzimportActionIndex(JTLSmarty $smarty, array &$messages): void
{
    $status = plzimportActionCheckStatus();
    if (isset($status->running) && $status->running) {
        $messages['notice'] = __('errorImportRunning');
    }

    $smarty->assign('oPlzOrt_arr', plzimportGetPLZOrt());
}

/**
 * @return array
 */
function plzimportActionUpdateIndex(): array
{
    Shop::Container()->getGetText()->loadAdminLocale('pages/plz_ort_import');
    Shop::Smarty()->assign('oPlzOrt_arr', plzimportGetPLZOrt());

    return ['listHTML' => Shop::Smarty()->fetch('tpl_inc/plz_ort_import_index_list.tpl')];
}

/**
 * @param string $target
 * @param string $part
 * @param int    $step
 * @return stdClass
 */
function plzimportActionDoImport($target = '', $part = '', $step = 0): stdClass
{
    Shop::Container()->getGetText()->loadAdminLocale('pages/plz_ort_import');

    $target = Text::filterXSS($target);
    $part   = Text::filterXSS($part);
    $step   = (int)$step;

    session_write_close();
    ini_set('max_execution_time', 30);

    if (empty($part)) {
        $part = 'download';
    }

    $step   = (int)$step;
    $result = (object)[
        'type'    => 'danger',
        'message' => __('errorImportStart')
    ];

    if (!empty($target) && (plzimportOpenSession('Import') || $step > 0)) {
        if ($step === 0) {
            $sessData = [
                'running' => true,
                'start'   => time(),
                'step'    => 0,
                'status'  => sprintf(__('importPending'), $target),
            ];
        } else {
            $sessData         = plzimportReadSession('Import');
            $sessData['step'] = $step;
        }

        plzimportWriteSession('Import', $sessData);

        switch ($part) {
            case 'import':
                plzimportDoImport($target, $sessData, $result);
                break;
            case 'download':
            default:
                plzimportDoDownload($target, $sessData, $result);
                break;
        }

        plzimportCloseSession('Import');
    }

    return $result;
}

/**
 * @param string $type
 * @param string $message
 * @return stdClass
 */
function plzimportActionResetImport($type = 'success', $message = ''): stdClass
{
    session_write_close();

    $message = $message ?: __('importCancel');
    $step    = 100;
    $result  = (object)[
        'type'    => Text::filterXSS($type),
        'message' => Text::filterXSS($message),
    ];

    $sessData         = plzimportReadSession('Import');
    $sessData['step'] = $step;

    plzimportWriteSession('Import', $sessData);
    plzimportCloseSession('Import');

    return $result;
}

/**
 * @return object
 */
function plzimportActionCallStatus(): stdClass
{
    session_write_close();
    $sessData = plzimportReadSession('Import');

    if (isset($sessData)) {
        $result = (object)$sessData;
    } else {
        $result = (object)[
            'running' => false,
            'start'   => time(),
            'step'    => 0,
            'status'  => '',
        ];
    }

    return $result;
}

/**
 * @return object
 */
function plzimportActionCheckStatus(): stdClass
{
    session_write_close();

    if (plzimportOpenSession('Import')) {
        plzimportCloseSession('Import');

        $impData = Shop::Container()->getDB()->query(
            "SELECT COUNT(*) AS nAnzahl
                FROM tplz
                WHERE cLandISO = 'IMP'",
            ReturnType::SINGLE_OBJECT
        );

        $result = (object)[
            'running' => false,
            'start'   => time(),
            'tmp'     => $impData->nAnzahl,
        ];
    } else {
        $sessData = plzimportReadSession('Import');
        $result   = (object)[
            'running' => $sessData['running'],
            'start'   => $sessData['start'] * 1000,
            'tmp'     => 0,
        ];
    }

    return $result;
}

/**
 * @return array
 */
function plzimportActionDelTempImport(): array
{
    Shop::Container()->getGetText()->loadAdminLocale('pages/plz_ort_import');
    Shop::Container()->getDB()->delete('tplz', 'cLandISO', 'IMP');

    return [
        'type'    => 'success',
        'message' => __('successImportTempDelete')
    ];
}

/**
 * @return array
 */
function plzimportActionLoadAvailableDownloads(): array
{
    Shop::Container()->getGetText()->loadAdminLocale('pages/plz_ort_import');
    $countries = $_SESSION['plzimport.oLand_arr'] ?? Shop::Container()->getCache()->get('plzimport.oLand_arr');
    if ($countries === false) {
        $ch = curl_init();
        @curl_setopt($ch, CURLOPT_URL, PLZIMPORT_URL);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        @curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        @curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $cContent = @curl_exec($ch);
        curl_close($ch);

        if (preg_match_all(PLZIMPORT_REGEX, $cContent, $hits, PREG_PATTERN_ORDER)) {
            $quotedHits = array_map(
                static function ($hit) {
                    return trim(Shop::Container()->getDB()->getPDO()->quote($hit), "'");
                },
                $hits[2]
            );
            $countries  = Shop::Container()->getCountryService()->getFilteredCountryList($quotedHits)->toArray();

            foreach ($countries as $country) {
                $idx = array_search($country->getISO(), $hits[2], true);
                if ($idx !== false) {
                    $date           = date_create_from_format('d-M-Y H:i', $hits[3][$idx]);
                    $country->cURL  = urlencode($hits[1][$idx]);
                    $country->cDate = $date !== false ? $date->format('d.m.Y') : $hits[3][$idx];
                    $country->cSize = $hits[4][$idx];
                }
            }

            Shop::Container()->getCache()->set('plzimport.oLand_arr', $countries);
            $_SESSION['plzimport.oLand_arr'] = $countries;
        } else {
            $countries = [];
        }
    }

    Shop::Smarty()->assign('oLand_arr', countriesPreventXss($countries));

    return ['dialogHTML' => Shop::Smarty()->fetch('tpl_inc/plz_ort_import_auswahl.tpl')];
}

/**
 * @param stdClass $country
 * @return stdClass
 */
function countryPreventXss($country): stdClass
{
    if (Shop::Smarty()->escape_html) {
        return $country;
    }

    return (object)[
        'cISO'     => htmlspecialchars($country->cISO, ENT_QUOTES, JTL_CHARSET, false),
        'cDeutsch' => htmlspecialchars($country->cDeutsch, ENT_QUOTES, JTL_CHARSET, false),
        'cDate'    => htmlspecialchars($country->cDate, ENT_QUOTES, JTL_CHARSET, false),
        'cSize'    => htmlspecialchars($country->cSize, ENT_QUOTES, JTL_CHARSET, false),
        'cURL'     => htmlspecialchars($country->cURL, ENT_QUOTES, JTL_CHARSET, false),
    ];
}

/**
 * @param stdClass[] $countries
 * @return stdClass[]
 */
function countriesPreventXss($countries)
{
    if (Shop::Smarty()->escape_html) {
        return $countries;
    }

    return array_map('\countryPreventXss', $countries);
}

/**
 * @param string $target
 * @return stdClass
 */
function plzimportActionRestoreBackup($target = ''): stdClass
{
    $target = Text::filterXSS($target);

    if (!empty($target)) {
        Shop::Container()->getDB()->delete('tplz', 'cLandISO', $target);
        Shop::Container()->getDB()->queryPrepared(
            'INSERT INTO tplz SELECT * FROM tplz_backup WHERE cLandISO = :target',
            ['target' => $target],
            ReturnType::AFFECTED_ROWS
        );
        Shop::Container()->getDB()->delete('tplz_backup', 'cLandISO', $target);

        $result = (object)['result' => 'success'];
    } else {
        $result = (object)['result' => 'failure'];
    }

    return $result;
}

/**
 * @param JTLSmarty $smarty
 * @param array                 $messages
 */
function plzimportFinalize(JTLSmarty $smarty, array &$messages): void
{
    if (isset($_SESSION['plzimport.notice'])) {
        $messages['notice'] = $_SESSION['plzimport.notice'];
        unset($_SESSION['plzimport.notice']);
    }
    if (isset($_SESSION['plzimport.error'])) {
        $messages['error'] = $_SESSION['plzimport.error'];
        unset($_SESSION['plzimport.error']);
    }

    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_NOTE, $messages['notice'], 'noticeZIPCity');
    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_ERROR, $messages['error'], 'errorZIPCity');

    $smarty->display('plz_ort_import.tpl');
}

/**
 * @param string $sessID
 * @return bool
 */
function plzimportOpenSession($sessID): bool
{
    $dbSess = Shop::Container()->getDB()->select('tadminsession', 'cSessionId', 'plzimport.' . $sessID);

    if (!isset($dbSess->nSessionExpires) || $dbSess->nSessionExpires < time()) {
        Shop::Container()->getDB()->query(
            "INSERT INTO tadminsession (cSessionId, nSessionExpires, cSessionData)
                VALUES ('plzimport." . $sessID . "', " . (time() + 2 * 60) . ", '')
                ON DUPLICATE KEY UPDATE
                nSessionExpires = " . (time() + 2 * 60) . ",
                cSessionData    = ''",
            ReturnType::AFFECTED_ROWS
        );

        return true;
    }

    return false;
}

/**
 * @param string $sessID
 */
function plzimportCloseSession($sessID): void
{
    Shop::Container()->getDB()->delete('tadminsession', 'cSessionId', 'plzimport.' . $sessID);
}

/**
 * @param string $sessID
 * @param array  $data
 */
function plzimportWriteSession($sessID, array $data): void
{
    Shop::Container()->getDB()->update('tadminsession', 'cSessionId', 'plzimport.' . $sessID, (object)[
        'cSessionData'    => serialize($data),
        'nSessionExpires' => time() + 2 * 60
    ]);
}

/**
 * @param string $sessID
 * @return array
 */
function plzimportReadSession($sessID)
{
    $dbSess = Shop::Container()->getDB()->select('tadminsession', 'cSessionId', 'plzimport.' . $sessID);

    return !empty($dbSess->cSessionData)
        ? unserialize($dbSess->cSessionData)
        : [];
}

/**
 * @param mixed       $data
 * @param string|null $error
 * @return void
 */
function plzimportMakeResponse($data, $error = null)
{
    ob_end_clean();

    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Content-type: application/json');

    if ($error !== null) {
        header(Request::makeHTTPHeader(500), true, $error);
    }

    $result = (object)[
        'error' => $error,
        'data'  => $data
    ];

    $json = json_encode($result);

    echo $json;
    exit;
}
