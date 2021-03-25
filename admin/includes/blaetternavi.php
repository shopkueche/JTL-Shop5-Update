<?php

use JTL\Helpers\Request;

/**
 * This pagination implementation is deprecated. Use the Pagination admin class instead!
 */

/**
 * @param int $currentPage
 * @param int $count
 * @param int $perPage
 * @return stdClass
 * @deprecated since 4.05
 */
function baueBlaetterNavi(int $currentPage, int $count, int $perPage)
{
    trigger_error(__FUNCTION__ . ' is deprecated - use the pagination class instead.', E_USER_DEPRECATED);
    $nav         = new stdClass();
    $nav->nAktiv = 0;

    if ($count > $perPage) {
        $counts   = [];
        $pages    = ceil($count / $perPage);
        $maxItems = 5;
        $start    = 0;
        $end      = 0;
        $prev     = $currentPage - 1; // Zum zurück blättern in der Navigation
        if ($prev <= 0) {
            $prev = 1;
        }
        $next = $currentPage + 1; // Zum vorwärts blättern in der Navigation
        if ($next >= $pages) {
            $next = $pages;
        }

        if ($pages > $maxItems) {
            // Ist die aktuelle Seite nach dem abzug der Begrenzung größer oder gleich 1?
            if (($currentPage - $maxItems) >= 1) {
                $start = 1;
                $nVon  = ($currentPage - $maxItems) + 1;
            } else {
                $start = 0;
                $nVon  = 1;
            }
            // Ist die aktuelle Seite nach dem addieren der Begrenzung kleiner als die maximale Anzahl der Seiten
            if (($currentPage + $maxItems) < $pages) {
                $end  = $pages;
                $nBis = ($currentPage + $maxItems) - 1;
            } else {
                $end  = 0;
                $nBis = $pages;
            }
            // Baue die Seiten für die Navigation
            for ($i = $nVon; $i <= $nBis; $i++) {
                $counts[] = $i;
            }
        } else {
            // Baue die Seiten für die Navigation
            for ($i = 1; $i <= $pages; $i++) {
                $counts[] = $i;
            }
        }

        // Blaetter Objekt um später in Smarty damit zu arbeiten
        $nav->nSeiten             = $pages;
        $nav->nVoherige           = $prev;
        $nav->nNaechste           = $next;
        $nav->nAnfang             = $start;
        $nav->nEnde               = $end;
        $nav->nBlaetterAnzahl_arr = $counts;
        $nav->nAktiv              = 1;
        $nav->nAnzahl             = $count;
    }

    $nav->nAktuelleSeite = $currentPage;
    $nav->nVon           = (($nav->nAktuelleSeite - 1) * $perPage) + 1;
    $nav->nBis           = $nav->nAktuelleSeite * $perPage;
    if ($nav->nBis > $count) {
        $nav->nBis = $count;
    }

    return $nav;
}

/**
 * @param int $count
 * @param int $perPage
 * @return bool|stdClass
 * @deprecated since 4.05
 */
function baueBlaetterNaviGetterSetter(int $count, int $perPage)
{
    trigger_error(__FUNCTION__ . ' is deprecated - use the pagination class instead.', E_USER_DEPRECATED);
    $conf = new stdClass();
    if ($count <= 0 || $perPage <= 0) {
        return false;
    }
    for ($i = 1; $i <= $count; $i++) {
        $offset         = 'nOffset' . $i;
        $sql            = 'cSQL' . $i;
        $nAktuelleSeite = 'nAktuelleSeite' . $i;
        $cLimit         = 'cLimit' . $i;

        $conf->$offset         = 0;
        $conf->$sql            = ' LIMIT ' . $perPage;
        $conf->$nAktuelleSeite = 1;
        $conf->$cLimit         = 0;
        // GET || POST
        if (Request::verifyGPCDataInt('s' . $i) > 0) {
            $page                  = Request::verifyGPCDataInt('s' . $i);
            $conf->$offset         = (($page - 1) * $perPage);
            $conf->$sql            = ' LIMIT ' . (($page - 1) * $perPage) . ', ' . $perPage;
            $conf->$nAktuelleSeite = $page;
            $conf->$cLimit         = (($page - 1) * $perPage);
        }
    }

    return $conf;
}
