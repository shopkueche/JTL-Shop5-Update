<?php

use JTL\Language\LanguageHelper;
use JTL\Shop;

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibSuchSpalten()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return JTL\Filter\States\BaseSearchQuery::getSearchRows(Shop::getSettings([CONF_ARTIKELUEBERSICHT]));
}

/**
 * @param array      $exclude
 * @param array|null $conf
 * @return string
 * @deprecated since 5.0.0
 */
function gibMaxPrioSpalte($exclude, $conf = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return JTL\Filter\States\BaseSearchQuery::getPrioritizedRows($exclude, $conf);
}

/**
 * @param array $searchColumns
 * @return array
 * @deprecated since 4.06
 */
function gibSuchspaltenKlassen($searchColumns)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->Suchanfrage->getSearchColumnClasses($searchColumns);
}

/**
 * @param array  $searchColumns
 * @param string $searchColumn
 * @param array  $nonAllowed
 * @return bool
 * @deprecated since 4.06
 */
function pruefeSuchspaltenKlassen($searchColumns, $searchColumn, $nonAllowed)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->Suchanfrage->checkColumnClasses($searchColumns, $searchColumn, $nonAllowed);
}

/**
 * @param string $search
 * @param int    $hits
 * @param bool   $realSearch
 * @param int    $langIDExt
 * @param bool   $filterSpam
 * @return bool
 * @deprecated since 4.06
 */
function suchanfragenSpeichern($search, $hits, $realSearch = false, $langIDExt = 0, $filterSpam = true)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->Suche->saveQuery($hits, $search, $realSearch, $langIDExt, $filterSpam);
}

/**
 * @param string $query
 * @param int    $languageID
 * @return mixed
 * @deprecated since 4.05
 */
function mappingBeachten($query, int $languageID = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $languageID = $languageID > 0 ? $languageID : LanguageHelper::getDefaultLanguage()->kSprache;
    if (mb_strlen($query) > 0) {
        $db      = Shop::Container()->getDB();
        $tmp     = $db->select(
            'tsuchanfragemapping',
            'kSprache',
            $languageID,
            'cSuche',
            $query,
            null,
            null,
            false,
            'cSucheNeu'
        );
        $mapping = $tmp;
        while ($tmp !== null && isset($tmp->cSucheNeu) && mb_strlen($tmp->cSucheNeu) > 0) {
            $tmp = $db->select(
                'tsuchanfragemapping',
                'kSprache',
                $languageID,
                'cSuche',
                $tmp->cSucheNeu,
                null,
                null,
                false,
                'cSucheNeu'
            );
            if (isset($tmp->cSucheNeu) && mb_strlen($tmp->cSucheNeu) > 0) {
                $mapping = $tmp;
            }
        }
        if (isset($mapping->cSucheNeu) && mb_strlen($mapping->cSucheNeu) > 0) {
            $query = $mapping->cSucheNeu;
        }
    }
    if (isset($mapping->cSucheNeu) && mb_strlen($mapping->cSucheNeu) > 0) {
        $query = $mapping->cSucheNeu;
    }

    return $query;
}

/**
 * @param string $query
 * @return array
 * @deprecated since 4.06
 */
function suchausdruckVorbereiten($query)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->Suchanfrage->prepareSearchQuery($query);
}

/**
 * @param array $search
 * @return array
 * @deprecated since 4.06 - it's never used anyways
 */
function suchausdruckAlleKombis($search): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $res = [];
    $cnt = count($search);
    if ($cnt > 3 || $cnt === 1) {
        return [];
    }

    switch ($cnt) {
        case 2:
            $res[] = $search[0] . ' ' . $search[1];
            $res[] = $search[1] . ' ' . $search[0];
            break;
        case 3:
            $res[] = $search[0] . ' ' . $search[1] . ' ' . $search[2];
            $res[] = $search[0] . ' ' . $search[2] . ' ' . $search[1];
            $res[] = $search[2] . ' ' . $search[1] . ' ' . $search[0];
            $res[] = $search[2] . ' ' . $search[0] . ' ' . $search[1];
            $res[] = $search[1] . ' ' . $search[0] . ' ' . $search[2];
            $res[] = $search[1] . ' ' . $search[2] . ' ' . $search[0];
            break;
        default:
            break;
    }

    return $res;
}
