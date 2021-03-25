<?php

use JTL\Alert\Alert;
use JTL\Backend\AdminFavorite;
use JTL\Backend\Notification;
use JTL\Campaign;
use JTL\Catalog\Currency;
use JTL\DB\ReturnType;
use JTL\Filter\SearchResults;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\IO\IOError;
use JTL\IO\IOResponse;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use JTL\XMLParser;

/**
 * @param int|array $configSectionID
 * @param bool $byName
 * @return array
 */
function getAdminSectionSettings($configSectionID, bool $byName = false)
{
    $gettext = Shop::Container()->getGetText();
    $gettext->loadConfigLocales();
    $db = Shop::Container()->getDB();
    if (is_array($configSectionID)) {
        $where    = $byName
            ? "WHERE cWertName IN ('" . implode("','", $configSectionID) . "')"
            : 'WHERE kEinstellungenConf IN (' . implode(',', array_map('\intval', $configSectionID)) . ')';
        $confData = $db->query(
            'SELECT *
                FROM teinstellungenconf
                ' . $where . '
                ORDER BY nSort',
            ReturnType::ARRAY_OF_OBJECTS
        );
    } else {
        $confData = $db->selectAll(
            'teinstellungenconf',
            $byName ? 'cWertName' : 'kEinstellungenSektion',
            $configSectionID,
            '*',
            'nSort'
        );
    }
    foreach ($confData as $conf) {
        $conf->kEinstellungenSektion = (int)$conf->kEinstellungenSektion;
        $conf->kEinstellungenConf    = (int)$conf->kEinstellungenConf;
        $conf->nSort                 = (int)$conf->nSort;
        $conf->nStandardAnzeigen     = (int)$conf->nStandardAnzeigen;
        $conf->nModul                = (int)$conf->nModul;

        $gettext->localizeConfig($conf);

        if ($conf->cInputTyp === 'listbox') {
            $conf->ConfWerte = $db->selectAll(
                'tkundengruppe',
                [],
                [],
                'kKundengruppe, cName',
                'cStandard DESC'
            );
        } elseif ($conf->cInputTyp === 'selectkdngrp') {
            $conf->ConfWerte = $db->query(
                'SELECT kKundengruppe, cName
                    FROM tkundengruppe
                    ORDER BY cStandard DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
        } else {
            $conf->ConfWerte = $db->selectAll(
                'teinstellungenconfwerte',
                'kEinstellungenConf',
                $conf->kEinstellungenConf,
                '*',
                'nSort'
            );
            $gettext->localizeConfigValues($conf, $conf->ConfWerte);
        }

        if ($conf->cInputTyp === 'listbox') {
            $setValue = $db->selectAll(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [$conf->kEinstellungenSektion, $conf->cWertName],
                'cWert'
            );

            $conf->gesetzterWert = $setValue;
        } elseif ($conf->cInputTyp === 'selectkdngrp') {
            $setValue            = $db->selectAll(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [$conf->kEinstellungenSektion, $conf->cWertName]
            );
            $conf->gesetzterWert = $setValue;
        } else {
            $setValue            = $db->select(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [$conf->kEinstellungenSektion, $conf->cWertName]
            );
            $conf->gesetzterWert = $setValue->cWert ?? null;
        }
    }

    return $confData;
}

/**
 * @param array $settingsIDs
 * @param array $post
 * @param array $tags
 * @param bool $byName
 * @return string
 */
function saveAdminSettings(array $settingsIDs, array $post, $tags = [CACHING_GROUP_OPTION], bool $byName = false)
{
    $db       = Shop::Container()->getDB();
    $where    = $byName
        ? "WHERE cWertName IN ('" . implode("','", $settingsIDs) . "')"
        : 'WHERE kEinstellungenConf IN (' . implode(',', array_map('\intval', $settingsIDs)) . ')';
    $confData = $db->query(
        'SELECT *
            FROM teinstellungenconf
            ' . $where . '
            ORDER BY nSort',
        ReturnType::ARRAY_OF_OBJECTS
    );
    if (count($confData) === 0) {
        return __('errorConfigSave');
    }
    foreach ($confData as $config) {
        $val                        = new stdClass();
        $val->cWert                 = $post[$config->cWertName] ?? null;
        $val->cName                 = $config->cWertName;
        $val->kEinstellungenSektion = (int)$config->kEinstellungenSektion;
        switch ($config->cInputTyp) {
            case 'kommazahl':
                $val->cWert = (float)$val->cWert;
                break;
            case 'zahl':
            case 'number':
                $val->cWert = (int)$val->cWert;
                break;
            case 'text':
                $val->cWert = mb_substr($val->cWert, 0, 255);
                break;
            case 'listbox':
                bearbeiteListBox($val->cWert, $val->cName, $val->kEinstellungenSektion);
                break;
        }
        if ($config->cInputTyp !== 'listbox') {
            $db->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [(int)$config->kEinstellungenSektion, $config->cWertName]
            );
            $db->insert('teinstellungen', $val);
        }
    }
    Shop::Container()->getCache()->flushTags($tags);

    return __('successConfigSave');
}

/**
 * @param array  $listBoxes
 * @param string $valueName
 * @param int    $configSectionID
 */
function bearbeiteListBox($listBoxes, $valueName, int $configSectionID)
{
    $db = Shop::Container()->getDB();
    if (is_array($listBoxes) && count($listBoxes) > 0) {
        $db->delete(
            'teinstellungen',
            ['kEinstellungenSektion', 'cName'],
            [$configSectionID, $valueName]
        );
        foreach ($listBoxes as $listBox) {
            $newConf                        = new stdClass();
            $newConf->cWert                 = $listBox;
            $newConf->cName                 = $valueName;
            $newConf->kEinstellungenSektion = $configSectionID;

            $db->insert('teinstellungen', $newConf);
        }
    } elseif ($valueName === 'bewertungserinnerung_kundengruppen') {
        // Leere Kundengruppen Work Around
        $customerGroup = $db->select('tkundengruppe', 'cStandard', 'Y');
        if ($customerGroup->kKundengruppe > 0) {
            $db->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [$configSectionID, $valueName]
            );
            $newConf                        = new stdClass();
            $newConf->cWert                 = $customerGroup->kKundengruppe;
            $newConf->cName                 = $valueName;
            $newConf->kEinstellungenSektion = CONF_BEWERTUNG;

            $db->insert('teinstellungen', $newConf);
        }
    }
}

/**
 * @param int   $configSectionID
 * @param array $post
 * @param array $tags
 * @return string
 */
function saveAdminSectionSettings(int $configSectionID, array $post, $tags = [CACHING_GROUP_OPTION])
{
    Shop::Container()->getGetText()->loadAdminLocale('configs/configs');
    if (!Form::validateToken()) {
        return __('errorCSRF');
    }
    $db       = Shop::Container()->getDB();
    $invalid  = 0;
    $confData = $db->selectAll(
        'teinstellungenconf',
        ['kEinstellungenSektion', 'cConf'],
        [$configSectionID, 'Y'],
        '*',
        'nSort'
    );
    foreach ($confData as $config) {
        $val                        = new stdClass();
        $val->cWert                 = $post[$config->cWertName] ?? null;
        $val->cName                 = $config->cWertName;
        $val->kEinstellungenSektion = $configSectionID;
        $valid                      = true;
        switch ($config->cInputTyp) {
            case 'kommazahl':
                $val->cWert = (float)str_replace(',', '.', $val->cWert);
                break;
            case 'zahl':
            case 'number':
                $val->cWert = (int)$val->cWert;
                $valid      = validateSetting($val);
                break;
            case 'text':
                $val->cWert = mb_substr($val->cWert, 0, 255);
                break;
            case 'listbox':
            case 'selectkdngrp':
                bearbeiteListBox($val->cWert, $config->cWertName, $configSectionID);
                break;
        }

        if ($valid && $config->cInputTyp !== 'listbox' && $config->cInputTyp !== 'selectkdngrp') {
            $db->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [$configSectionID, $config->cWertName]
            );
            $db->insert('teinstellungen', $val);
        }
        if (!$valid) {
            $invalid++;
        }
    }
    Shop::Container()->getCache()->flushTags($tags);

    if ($invalid > 0 || count($confData) === 0) {
        return __('errorConfigSave');
    }

    return __('successConfigSave');
}

/**
 * @param $setting
 * @return bool
 */
function validateSetting($setting): bool
{
    $valid = true;
    switch ($setting->cName) {
        case 'bilder_jpg_quali':
            $valid = validateNumberRange(0, 100, $setting);
            break;
        default:
            break;
    }

    return $valid;
}

/**
 * @param int $min
 * @param int $max
 * @param $setting
 * @return bool
 */
function validateNumberRange(int $min, int $max, $setting): bool
{
    $valid = $min <= $setting->cWert && $setting->cWert <= $max;

    if (!$valid) {
        Shop::Container()->getAlertService()->addAlert(
            Alert::TYPE_DANGER,
            sprintf(__('errrorNumberRange'), __($setting->cName . '_name'), $min, $max),
            'errrorNumberRange'
        );
    }

    return $valid;
}

/**
 * Holt alle vorhandenen Kampagnen
 * Wenn $bInterneKampagne false ist, werden keine Interne Shop Kampagnen geholt
 * Wenn $bAktivAbfragen true ist, werden nur Aktive Kampagnen geholt
 *
 * @param bool $internalOnly
 * @param bool $activeOnly
 * @return array
 */
function holeAlleKampagnen(bool $internalOnly = false, bool $activeOnly = true)
{
    $activeSQL  = $activeOnly ? ' WHERE nAktiv = 1' : '';
    $interalSQL = '';
    if (!$internalOnly && $activeOnly) {
        $interalSQL = ' AND kKampagne >= 1000';
    } elseif (!$internalOnly) {
        $interalSQL = ' WHERE kKampagne >= 1000';
    }
    $campaigns = [];
    $items     = Shop::Container()->getDB()->query(
        'SELECT kKampagne
            FROM tkampagne
            ' . $activeSQL . '
            ' . $interalSQL . '
            ORDER BY kKampagne',
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($items as $item) {
        $campaign = new Campaign((int)$item->kKampagne);
        if (isset($campaign->kKampagne) && $campaign->kKampagne > 0) {
            $campaigns[$campaign->kKampagne] = $campaign;
        }
    }

    return $campaigns;
}

/**
 * @param array $xml
 * @param int   $level
 * @return array
 * @deprecated since 5.0.0
 */
function getArrangedArray($xml, int $level = 1)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $parser = new XMLParser();

    return $parser->getArrangedArray($xml, $level);
}

/**
 *
 */
function setzeSprache()
{
    if (Form::validateToken() && Request::verifyGPCDataInt('sprachwechsel') === 1) {
        // Wähle explizit gesetzte Sprache als aktuelle Sprache
        $language = Shop::Container()->getDB()->select('tsprache', 'kSprache', Request::postInt('kSprache'));
        if ((int)$language->kSprache > 0) {
            $_SESSION['editLanguageID']   = (int)$language->kSprache;
            $_SESSION['editLanguageCode'] = $language->cISO;
        }
    }

    if (!isset($_SESSION['editLanguageID'])) {
        // Wähle Standardsprache als aktuelle Sprache
        $language = Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
        if ((int)$language->kSprache > 0) {
            $_SESSION['editLanguageID']   = (int)$language->kSprache;
            $_SESSION['editLanguageCode'] = $language->cISO;
        }
    }
    if (isset($_SESSION['editLanguageID']) && empty($_SESSION['editLanguageCode'])) {
        // Fehlendes cISO ergänzen
        $language = Shop::Container()->getDB()->select('tsprache', 'kSprache', (int)$_SESSION['editLanguageID']);
        if ((int)$language->kSprache > 0) {
            $_SESSION['editLanguageCode'] = $language->cISO;
        }
    }
}

/**
 * @param int $month
 * @param int $year
 * @return int
 */
function firstDayOfMonth(int $month = -1, int $year = -1)
{
    return mktime(
        0,
        0,
        0,
        $month > -1 ? $month : (int)date('m'),
        1,
        $year > -1 ? $year : (int)date('Y')
    );
}

/**
 * @param int $month
 * @param int $year
 * @return int
 */
function lastDayOfMonth(int $month = -1, int $year = -1)
{
    return mktime(
        23,
        59,
        59,
        $month > -1 ? $month : (int)date('m'),
        (int)date('t', firstDayOfMonth($month, $year)),
        $year > -1 ? $year : (int)date('Y')
    );
}

/**
 * Ermittelt den Wochenstart und das Wochenende
 * eines Datums im Format YYYY-MM-DD
 * und gibt ein Array mit Start als Timestamp zurück
 * Array[0] = Start
 * Array[1] = Ende
 * @param string $dateString
 * @return array
 */
function ermittleDatumWoche(string $dateString)
{
    if (mb_strlen($dateString) < 0) {
        return [];
    }
    [$year, $month, $day] = explode('-', $dateString);
    // So = 0, SA = 6
    $weekDay = (int)date('w', mktime(0, 0, 0, (int)$month, (int)$day, (int)$year));
    // Woche soll Montag starten - also So = 6, Mo = 0
    if ($weekDay === 0) {
        $weekDay = 6;
    } else {
        $weekDay--;
    }
    // Wochenstart ermitteln
    $dayOld = (int)$day;
    $day    = $dayOld - $weekDay;
    $month  = (int)$month;
    $year   = (int)$year;
    if ($day <= 0) {
        --$month;
        if ($month === 0) {
            $month = 12;
            ++$year;
        }

        $daysPerMonth = (int)date('t', mktime(0, 0, 0, $month, 1, $year));
        $day          = $daysPerMonth - $weekDay + $dayOld;
    }
    $stampStart   = mktime(0, 0, 0, $month, $day, $year);
    $days         = 6;
    $daysPerMonth = (int)date('t', mktime(0, 0, 0, $month, 1, $year));
    $day         += $days;
    if ($day > $daysPerMonth) {
        $day -= $daysPerMonth;
        ++$month;
        if ($month > 12) {
            $month = 1;
            ++$year;
        }
    }

    $stampEnd = mktime(23, 59, 59, $month, $day, $year);

    return [$stampStart, $stampEnd];
}

/**
 * Return version of files
 *
 * @param bool $date
 * @return int|string
 */
function getJTLVersionDB(bool $date = false)
{
    $ret = 0;
    if ($date) {
        $latestUpdate = Shop::Container()->getDB()->query(
            'SELECT max(dExecuted) as date FROM tmigration',
            ReturnType::SINGLE_OBJECT
        );
        $ret          = $latestUpdate->date;
    } else {
        $versionData = Shop::Container()->getDB()->query(
            'SELECT nVersion FROM tversion',
            ReturnType::SINGLE_OBJECT
        );
        if (isset($versionData->nVersion)) {
            $ret = $versionData->nVersion;
        }
    }

    return $ret;
}

/**
 * @param string $size
 * @return mixed
 */
function getMaxFileSize($size)
{
    switch (mb_substr($size, -1)) {
        case 'M':
        case 'm':
            return (int)$size * 1048576;
        case 'K':
        case 'k':
            return (int)$size * 1024;
        case 'G':
        case 'g':
            return (int)$size * 1073741824;
        default:
            return $size;
    }
}

/**
 * @param float  $netPrice
 * @param float  $grossPrice
 * @param string $targetID
 * @return IOResponse
 */
function getCurrencyConversionIO($netPrice, $grossPrice, $targetID)
{
    $response = new IOResponse();
    $response->assignDom($targetID, 'innerHTML', Currency::getCurrencyConversion($netPrice, $grossPrice));

    return $response;
}

/**
 * @param float  $netPrice
 * @param float  $grossPrice
 * @param string $tooltipID
 * @return IOResponse
 */
function setCurrencyConversionTooltipIO($netPrice, $grossPrice, $tooltipID)
{
    $response = new IOResponse();
    $response->assignVar('originalTilte', Currency::getCurrencyConversion($netPrice, $grossPrice));

    return $response;
}

/**
 * @param string $title
 * @param string $url
 * @return array|IOError
 */
function addFav($title, $url)
{
    $success     = false;
    $kAdminlogin = Shop::Container()->getAdminAccount()->getID();

    if (!empty($title) && !empty($url)) {
        $success = AdminFavorite::add($kAdminlogin, $title, $url);
    }

    if ($success) {
        $result = [
            'title' => $title,
            'url'   => $url
        ];
    } else {
        $result = new IOError('Unauthorized', 401);
    }

    return $result;
}

/**
 * @return array
 */
function reloadFavs()
{
    global $oAccount;

    $tpl = Shop::Smarty()->assign('favorites', $oAccount->favorites())
               ->fetch('tpl_inc/favs_drop.tpl');

    return ['tpl' => $tpl];
}

/**
 * @return array
 */
function getNotifyDropIO()
{
    return [
        'tpl'  => JTLSmarty::getInstance(false, ContextType::BACKEND)
            ->assign('notifications', Notification::getInstance())
            ->fetch('tpl_inc/notify_drop.tpl'),
        'type' => 'notify'
    ];
}

/**
 * @param string $filename
 * @return string delimiter guess
 * @former guessCsvDelimiter()
 */
function getCsvDelimiter(string $filename)
{
    $file      = fopen($filename, 'r');
    $firstLine = fgets($file);

    foreach ([';', ',', '|', '\t'] as $delim) {
        if (mb_strpos($firstLine, $delim) !== false) {
            fclose($file);

            return $delim;
        }
    }
    fclose($file);

    return ';';
}

/**
 * @return JTLSmarty
 */
function getFrontendSmarty()
{
    static $frontendSmarty = null;

    if ($frontendSmarty === null) {
        $frontendSmarty = new JTLSmarty();
        $frontendSmarty->assign('imageBaseURL', Shop::getImageBaseURL())
            ->assign('NettoPreise', Frontend::getCustomerGroup()->getIsMerchant())
            ->assign('ShopURL', Shop::getURL())
            ->assign('Suchergebnisse', new SearchResults())
            ->assign('NaviFilter', Shop::getProductFilter())
            ->assign('Einstellungen', Shopsetting::getInstance()->getAll());
    }

    return $frontendSmarty;
}
