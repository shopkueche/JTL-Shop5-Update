<?php

use JTL\DB\ReturnType;
use JTL\News\Controller;
use JTL\Session\Frontend;
use JTL\Shop;
use function Functional\map;
use function Functional\pluck;

/**
 * @param bool $bActiveOnly
 * @return stdClass
 * @deprecated since 5.0.0
 */
function baueFilterSQL($bActiveOnly = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Controller::getFilterSQL($bActiveOnly);
}

/**
 * Prüft ob eine Kunde bereits einen Kommentar zu einer News geschrieben hat.
 * Falls Ja => return false
 * Falls Nein => return true
 *
 * @param string $comment
 * @param string $name
 * @param string $email
 * @param int    $newsID
 * @param array  $conf
 * @return array
 * @deprecated since 5.0.0
 */
function pruefeKundenKommentar($comment, $name, $email, $newsID, $conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use \News\Controller::checkComment() instead.', E_USER_DEPRECATED);
    if (!isset($_POST['cEmail'])) {
        $_POST['cEmail'] = $email;
    }
    if (!isset($_POST['cName'])) {
        $_POST['cName'] = $name;
    }
    $_POST['cKommentar'] = $comment;

    return Controller::checkComment($_POST, (int)$newsID, $conf);
}

/**
 * @param array $checks
 * @return string
 * @deprecated since 5.0.0
 */
function gibNewskommentarFehler(array $checks)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use \News\Controller::getCommentErrors() instead.',
        E_USER_DEPRECATED
    );
    return Controller::getCommentErrors($checks);
}

/**
 * @param string $dateSQL
 * @param bool   $activeOnly
 * @return array
 * @deprecated since 5.0.0
 */
function holeNewsKategorien($dateSQL, $activeOnly = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $languageID   = Shop::getLanguageID();
    $sql          = '';
    $activeFilter = $activeOnly ? ' AND tnewskategorie.nAktiv = 1 ' : '';
    if (mb_strlen($dateSQL) > 0) {
        $sql = '   JOIN tnewskategorienews 
                        ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                    JOIN tnews 
                        ON tnews.kNews = tnewskategorienews.kNews
                    ' . $dateSQL;
    }

    return Shop::Container()->getDB()->query(
        "SELECT tnewskategorie.kNewsKategorie, t.languageID AS kSprache, t.name AS cName,
            t.description AS cBeschreibung, t.metaTitle AS cMetaTitle, t.metaDescription AS cMetaDescription,
            tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung, 
            tnewskategorie.cPreviewImage, tseo.cSeo,
            DATE_FORMAT(tnewskategorie.dLetzteAktualisierung, '%d.%m.%Y  %H:%i') AS dLetzteAktualisierung_de
            FROM tnewskategorie
            JOIN tnewskategoriesprache t 
                ON t.kNewsKategorie = tnewskategorie.kNewsKategorie
            " . $sql . "
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNewsKategorie'
                AND tseo.kKey = tnewskategorie.kNewsKategorie
                AND tseo.kSprache = " . $languageID . '
                AND tnewskategorie.kSprache = ' . $languageID . '
            WHERE t.languageID = ' . $languageID
            . $activeFilter . '
            GROUP BY tnewskategorie.kNewsKategorie
            ORDER BY tnewskategorie.nSort',
        ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param array $dates
 * @return array
 * @deprecated since 5.0.0
 */
function baueDatum($dates)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $res = [];
    foreach ($dates as $oDatum) {
        $oTMP        = new stdClass();
        $oTMP->cWert = $oDatum->nMonat . '-' . $oDatum->nJahr;
        $oTMP->cName = mappeDatumName((string)$oDatum->nMonat, (int)$oDatum->nJahr, Shop::getLanguageCode());
        $res[]       = $oTMP;
    }

    return $res;
}

/**
 * @param string $cMonat
 * @param string $nJahr
 * @param string $cISOSprache
 * @return string
 * @deprecated since 5.0.0
 */
function mappeDatumName($cMonat, $nJahr, $cISOSprache)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Controller::mapDateName($cMonat, $nJahr, $cISOSprache);
}

/**
 * @return string
 * @deprecated since 4.04
 */
function baueNewsMetaTitle()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return '';
}

/**
 * @return string
 * @deprecated since 4.04
 */
function baueNewsMetaDescription()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return '';
}

/**
 * @param object $unused
 * @param array  $newsOverview
 * @return string
 * @deprecated since 5.0.0
 */
function baueNewsMetaKeywords($unused, $newsOverview)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $keywords = '';
    if (is_array($newsOverview) && count($newsOverview) > 0) {
        $count = 6;
        if (count($newsOverview) < $count) {
            $count = count($newsOverview);
        }
        for ($i = 0; $i < $count; $i++) {
            if ($i > 0) {
                $keywords .= ', ' . $newsOverview[$i]->cMetaKeywords;
            } else {
                $keywords .= $newsOverview[$i]->cMetaKeywords;
            }
        }
    }

    return $keywords;
}

/**
 * @return string
 * @deprecated since 4.04
 */
function baueNewsMetaStart()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return '';
}

/**
 * @deprecated since 5.0.0
 */
function baueNewsKruemel()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
}

/**
 * @param int  $newsID
 * @param bool $activeOnly
 * @return stdClass|null
 * @deprecated since 5.0.0
 */
function getNewsArchive(int $newsID, bool $activeOnly = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $activeFilter = $activeOnly ? ' AND tnews.nAktiv = 1 ' : '';

    return Shop::Container()->getDB()->query(
        "SELECT tnews.kNews, t.languageID AS kSprache, tnews.cKundengruppe, t.title AS cBetreff, 
        t.content AS cText, t.preview AS cVorschauText, tnews.cPreviewImage, t.metaTitle AS cMetaTitle, 
        t.metaDescription AS cMetaDescription, t.metaKeywords AS cMetaKeywords, tnews.nAktiv, 
        tnews.dErstellt, tnews.dGueltigVon, tseo.cSeo,
            DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS Datum, 
            DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de
            FROM tnews
            JOIN tnewssprache t 
                ON tnews.kNews = t.kNews
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNews'
                AND tseo.kKey = tnews.kNews
                AND tseo.kSprache = " . Shop::getLanguageID() . '
            WHERE tnews.kNews = ' . $newsID . " 
                AND (tnews.cKundengruppe LIKE '%;-1;%' 
                    OR FIND_IN_SET('" . Frontend::getCustomerGroup()->getID()
                        . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                AND t.languageID = " . Shop::getLanguageID()
                . $activeFilter,
        ReturnType::SINGLE_OBJECT
    );
}

/**
 * @param int  $newsCategoryID
 * @param bool $activeOnly
 * @return stdClass|null
 * @deprecated since 5.0.0
 */
function getCurrentNewsCategory(int $newsCategoryID, bool $activeOnly = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $activeFilter = $activeOnly ? ' AND tnewskategorie.nAktiv = 1 ' : '';

    return Shop::Container()->getDB()->queryPrepared(
        "SELECT tnewskategorie.cName, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription, tseo.cSeo
            FROM tnewskategorie
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNewsKategorie'
                AND tseo.kKey = :cat
                AND tseo.kSprache = :lid
            WHERE tnewskategorie.kNewsKategorie = :cat" . $activeFilter,
        [
            'cat' => $newsCategoryID,
            'lid' => Shop::getLanguageID()
        ],
        ReturnType::SINGLE_OBJECT
    );
}

/**
 * @param int $newsID
 * @return array
 * @deprecated since 5.0.0
 */
function getNewsCategory(int $newsID)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $newsCategories = map(
        pluck(Shop::Container()->getDB()->selectAll(
            'tnewskategorienews',
            'kNews',
            $newsID,
            'kNewsKategorie'
        ), 'kNewsKategorie'),
        static function ($e) {
            return (int)$e;
        }
    );

    return Shop::Container()->getDB()->query(
        "SELECT tnewskategorie.kNewsKategorie, tnewskategorie.kSprache, tnewskategorie.cName,
            tnewskategorie.cBeschreibung, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription,
            tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung,
            tnewskategorie.cPreviewImage, tseo.cSeo,
            DATE_FORMAT(tnewskategorie.dLetzteAktualisierung, '%d.%m.%Y %H:%i') AS dLetzteAktualisierung_de
            FROM tnewskategorie
            LEFT JOIN tnewskategorienews 
                ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNewsKategorie'
                AND tseo.kKey = tnewskategorie.kNewsKategorie
                AND tseo.kSprache = " . Shop::getLanguageID() . '
            WHERE tnewskategorie.kSprache = ' . Shop::getLanguageID() . '
                AND tnewskategorienews.kNewsKategorie IN (' . implode(',', $newsCategories) . ')
                AND tnewskategorie.nAktiv = 1
            GROUP BY tnewskategorie.kNewsKategorie
            ORDER BY tnewskategorie.nSort DESC',
        ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param int    $newsID
 * @param string $cLimitSQL
 * @return array
 * @deprecated since 5.0.0
 */
function getNewsComments(int $newsID, $cLimitSQL)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::Container()->getDB()->query(
        "SELECT *, DATE_FORMAT(tnewskommentar.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de
            FROM tnewskommentar
            WHERE tnewskommentar.kNews = " . $newsID . '
                AND tnewskommentar.nAktiv = 1
            ORDER BY tnewskommentar.dErstellt DESC
            LIMIT ' . $cLimitSQL,
        ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param int $newsID
 * @return stdClass
 * @deprecated since 5.0.0
 */
function getCommentCount(int $newsID)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::Container()->getDB()->queryPrepared(
        'SELECT COUNT(*) AS nAnzahl
            FROM tnewskommentar
            WHERE kNews = :nid
            AND nAktiv = 1',
        ['nid' => $newsID],
        ReturnType::SINGLE_OBJECT
    );
}

/**
 * @param int $overviewID
 * @return stdClass|null
 * @deprecated since 5.0.0
 */
function getMonthOverview(int $overviewID)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::Container()->getDB()->queryPrepared(
        "SELECT tnewsmonatsuebersicht.*, tseo.cSeo
            FROM tnewsmonatsuebersicht
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNewsMonatsUebersicht'
                AND tseo.kKey = :nmi
                AND tseo.kSprache = :lid
            WHERE tnewsmonatsuebersicht.kNewsMonatsUebersicht = :nmi",
        [
            'nmi' => $overviewID,
            'lid' => Shop::getLanguageID()
        ],
        ReturnType::SINGLE_OBJECT
    );
}

/**
 * @param object $sql
 * @param string $limitSQL
 * @return array
 * @deprecated since 5.0.0
 */
function getNewsOverview($sql, $limitSQL)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::Container()->getDB()->query(
        "SELECT tseo.cSeo, tnews.*, DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS dErstellt_de, 
            COUNT(*) AS nAnzahl, COUNT(DISTINCT(tnewskommentar.kNewsKommentar)) AS nNewsKommentarAnzahl
            FROM tnews
            JOIN tnewssprache t 
                ON tnews.kNews = t.kNews
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNews'
                AND tseo.kKey = tnews.kNews
                AND tseo.kSprache = " . Shop::getLanguageID() . '
            LEFT JOIN tnewskommentar 
                ON tnewskommentar.kNews = tnews.kNews 
                AND tnewskommentar.nAktiv = 1
            ' . $sql->cNewsKatSQL . "
            WHERE tnews.nAktiv = 1
                AND tnews.dGueltigVon <= NOW()
                AND (tnews.cKundengruppe LIKE '%;-1;%' 
                    OR FIND_IN_SET('" . Frontend::getCustomerGroup()->getID()
                        . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                AND t.languageID = " . Shop::getLanguageID() . '
                ' . $sql->cDatumSQL . '
            GROUP BY tnews.kNews
            ' . $sql->cSortSQL . '
            LIMIT ' . $limitSQL,
        ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param object $sql
 * @return stdClass
 * @deprecated since 5.0.0
 */
function getFullNewsOverview($sql)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::Container()->getDB()->query(
        'SELECT COUNT(DISTINCT(tnews.kNews)) AS nAnzahl
            FROM tnews
            JOIN tnewssprache t
                ON t.kNews = tnews.kNews
            ' . $sql->cNewsKatSQL . "
            WHERE tnews.nAktiv = 1
                AND tnews.dGueltigVon <= NOW()
                AND (tnews.cKundengruppe LIKE '%;-1;%' 
                    OR FIND_IN_SET('" . Frontend::getCustomerGroup()->getID()
                        . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                " . $sql->cDatumSQL . '
                AND t.languageID = ' . Shop::getLanguageID(),
        ReturnType::SINGLE_OBJECT
    );
}

/**
 * @param object $sql
 * @return array
 * @deprecated since 5.0.0
 */
function getNewsDateArray($sql)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::Container()->getDB()->query(
        'SELECT MONTH(tnews.dGueltigVon) AS nMonat, YEAR(tnews.dGueltigVon) AS nJahr
            FROM tnews
            JOIN tnewssprache t
                ON tnews.kNews = t.kNews
            ' . $sql->cNewsKatSQL . "
            WHERE tnews.nAktiv = 1
                AND tnews.dGueltigVon <= NOW()
                AND (tnews.cKundengruppe LIKE '%;-1;%' 
                    OR FIND_IN_SET('" . Frontend::getCustomerGroup()->getID()
                        . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                AND t.languageID = " . Shop::getLanguageID() . '
            GROUP BY nJahr, nMonat
            ORDER BY dGueltigVon DESC',
        ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param object $a
 * @param object $b
 * @return int
 * @deprecated since 5.0.0
 */
function cmp_obj($a, $b)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return strcmp($a->cName, $b->cName);
}

/**
 * @param int    $newsID
 * @param string $uploadDir
 * @return array
 * @deprecated since 5.0.0
 */
function holeNewsBilder(int $newsID, $uploadDir)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $images = [];
    if ($newsID > 0 && is_dir($uploadDir . $newsID)) {
        $handle  = opendir($uploadDir . $newsID);
        $baseURL = Shop::getURL() . '/';
        while (($file = readdir($handle)) !== false) {
            if ($file !== '.' && $file !== '..') {
                $image           = new stdClass();
                $image->cName    = mb_substr($file, 0, mb_strpos($file, '.'));
                $image->cURL     = PFAD_NEWSBILDER . $newsID . '/' . $file;
                $image->cURLFull = $baseURL . PFAD_NEWSBILDER . $newsID . '/' . $file;
                $image->cDatei   = $file;

                $images[] = $image;
            }
        }

        usort($images, 'cmp_obj');
    }

    return $images;
}
