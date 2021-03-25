<?php

use JTL\Shop;

/**
 * @param string $subject
 * @param string $text
 * @param array  $customerGroupIDs
 * @param array  $newsCategoryIDs
 * @return array
 * @deprecated since 5.0.0
 */
function pruefeNewsPost($subject, $text, $customerGroupIDs, $newsCategoryIDs)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    $checks = [];
    // Betreff prüfen
    if (mb_strlen($subject) === 0) {
        $checks['cBetreff'] = 1;
    }
    // Text prüfen
    if (mb_strlen($text) === 0) {
        $checks['cText'] = 1;
    }
    // Kundengruppe prüfen
    if (!is_array($customerGroupIDs) || count($customerGroupIDs) === 0) {
        $checks['kKundengruppe_arr'] = 1;
    }
    // Newskategorie prüfen
    if (!is_array($newsCategoryIDs) || count($newsCategoryIDs) === 0) {
        $checks['kNewsKategorie_arr'] = 1;
    }

    return $checks;
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function pruefeNewsKategorie()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string $string
 * @return string
 * @deprecated since 4.06
 */
function convertDate($string)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    [$dDatum, $dZeit]        = explode(' ', $string);
    [$nStunde, $nMinute]     = explode(':', $dZeit);
    [$nTag, $nMonat, $nJahr] = explode('.', $dDatum);

    return $nJahr . '-' . $nMonat . '-' . $nTag . ' ' . $nStunde . ':' . $nMinute . ':00';
}

/**
 * @param string $a
 * @param string $b
 * @return int
 * @deprecated since 5.0.0
 */
function cmp($a, $b)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return strcmp($a, $b);
}

/**
 * @param object $a
 * @param object $b
 * @return int
 */
function cmp_obj($a, $b)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return strcmp($a->cName, $b->cName);
}

/**
 * @param string $month
 * @param int    $year
 * @param string $iso
 * @return string
 * @deprecated since 5.0.0
 */
function mappeDatumName($month, $year, $iso)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    $name = '';

    if ($iso === 'ger') {
        switch ($month) {
            case '01':
                $name .= Shop::Lang()->get('january', 'news') . ', ' . $year;
                break;
            case '02':
                $name .= Shop::Lang()->get('february', 'news') . ', ' . $year;
                break;
            case '03':
                $name .= Shop::Lang()->get('march', 'news') . ', ' . $year;
                break;
            case '04':
                $name .= Shop::Lang()->get('april', 'news') . ', ' . $year;
                break;
            case '05':
                $name .= Shop::Lang()->get('may', 'news') . ', ' . $year;
                break;
            case '06':
                $name .= Shop::Lang()->get('june', 'news') . ', ' . $year;
                break;
            case '07':
                $name .= Shop::Lang()->get('july', 'news') . ', ' . $year;
                break;
            case '08':
                $name .= Shop::Lang()->get('august', 'news') . ', ' . $year;
                break;
            case '09':
                $name .= Shop::Lang()->get('september', 'news') . ', ' . $year;
                break;
            case '10':
                $name .= Shop::Lang()->get('october', 'news') . ', ' . $year;
                break;
            case '11':
                $name .= Shop::Lang()->get('november', 'news') . ', ' . $year;
                break;
            case '12':
                $name .= Shop::Lang()->get('december', 'news') . ', ' . $year;
                break;
        }
    } else {
        $name .= date('F', mktime(0, 0, 0, (int)$month, 1, $year)) . ', ' . $year;
    }

    return $name;
}

/**
 * @param string $dateTimeStr
 * @return stdClass
 * @deprecated since 4.06
 */
function gibJahrMonatVonDateTime($dateTimeStr)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    [$date,]              = explode(' ', $dateTimeStr);
    [$year, $month, $day] = explode('-', $date);
    $res                  = new stdClass();
    $res->Jahr            = (int)$year;
    $res->Monat           = (int)$month;
    $res->Tag             = (int)$day;

    return $res;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function speicherNewsKommentar()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function holeNewskategorie()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function holeNewsBilder()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function holeNewsKategorieBilder()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int    $kNews
 * @param string $uploadDir
 * @return bool
 */
function loescheNewsBilderDir($kNews, $uploadDir)
{
    if (!is_dir($uploadDir . $kNews)) {
        return false;
    }
    $handle = opendir($uploadDir . $kNews);
    while (($file = readdir($handle)) !== false) {
        if ($file !== '.' && $file !== '..') {
            unlink($uploadDir . $kNews . '/' . $file);
        }
    }
    rmdir($uploadDir . $kNews);

    return true;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function loescheNewsKategorie(): bool
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @return stdClass
 * @deprecated since 5.0.0
 */
function editiereNewskategorie()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return new stdClass();
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function parseText()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return '';
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function loescheNewsBild()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @deprecated since 5.0.0
 */
function newsRedirect()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
}
