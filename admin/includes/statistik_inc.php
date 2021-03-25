<?php

use JTL\Linechart;
use JTL\Piechart;
use JTL\Statistik;

/**
 * @param int    $type
 * @param string $from
 * @param string $to
 * @param int    $intervall
 * @return array|mixed
 */
function gibBackendStatistik(int $type, $from, $to, &$intervall)
{
    $data = [];
    if ($type > 0 && $from > 0 && $to > 0) {
        $stats     = new Statistik($from, $to);
        $intervall = $stats->getAnzeigeIntervall();
        switch ($type) {
            case STATS_ADMIN_TYPE_BESUCHER:
                $data = $stats->holeBesucherStats();
                break;
            case STATS_ADMIN_TYPE_KUNDENHERKUNFT:
                $data = $stats->holeKundenherkunftStats();
                break;
            case STATS_ADMIN_TYPE_SUCHMASCHINE:
                $data = $stats->holeBotStats();
                break;
            case STATS_ADMIN_TYPE_UMSATZ:
                $data = $stats->holeUmsatzStats();
                break;
            case STATS_ADMIN_TYPE_EINSTIEGSSEITEN:
                $data = $stats->holeEinstiegsseiten();
                break;
        }
    }

    return $data;
}

/**
 * @param int $type
 * @return mixed
 */
function gibMappingDaten($type)
{
    if (!$type) {
        return [];
    }

    $mapping                                   = [];
    $mapping[STATS_ADMIN_TYPE_BESUCHER]        = [
        'nCount' => __('count'),
        'dZeit'  => __('date')
    ];
    $mapping[STATS_ADMIN_TYPE_KUNDENHERKUNFT]  = [
        'nCount'   => __('count'),
        'dZeit'    => __('date'),
        'cReferer' => __('origin')
    ];
    $mapping[STATS_ADMIN_TYPE_SUCHMASCHINE]    = [
        'nCount'     => __('count'),
        'dZeit'      => __('date'),
        'cUserAgent' => __('userAgent')
    ];
    $mapping[STATS_ADMIN_TYPE_UMSATZ]          = [
        'nCount' => __('amount'),
        'dZeit'  => __('date')
    ];
    $mapping[STATS_ADMIN_TYPE_EINSTIEGSSEITEN] = [
        'nCount'          => __('count'),
        'dZeit'           => __('date'),
        'cEinstiegsseite' => __('entryPage')
    ];

    return $mapping[$type];
}

/**
 * @param int $type
 * @return string
 */
function GetTypeNameStats($type)
{
    $names = [
        1 => __('visitor'),
        2 => __('customerHeritage'),
        3 => __('searchEngines'),
        4 => __('sales'),
        5 => __('entryPages')
    ];

    return $names[$type] ?? '';
}

/**
 * @param int $type
 * @return stdClass
 */
function getAxisNames($type)
{
    $axis    = new stdClass();
    $axis->y = 'nCount';
    switch ($type) {
        case STATS_ADMIN_TYPE_UMSATZ:
        case STATS_ADMIN_TYPE_BESUCHER:
            $axis->x = 'dZeit';
            break;
        case STATS_ADMIN_TYPE_KUNDENHERKUNFT:
            $axis->x = 'cReferer';
            break;
        case STATS_ADMIN_TYPE_SUCHMASCHINE:
            $axis->x = 'cUserAgent';
            break;
        case STATS_ADMIN_TYPE_EINSTIEGSSEITEN:
            $axis->x = 'cEinstiegsseite';
            break;
    }

    return $axis;
}

/**
 * @param array $members
 * @param array $mapping
 * @return array
 */
function mappeDatenMember($members, $mapping)
{
    if (is_array($members) && count($members) > 0) {
        foreach ($members as $i => $data) {
            foreach ($data as $j => $member) {
                $members[$i][$j]    = [];
                $members[$i][$j][0] = $member;
                $members[$i][$j][1] = $mapping[$member];
            }
        }
    }

    return $members;
}

/**
 * @param array  $stats
 * @param string $name
 * @param object $axis
 * @param int    $mod
 * @return Linechart
 */
function prepareLineChartStats($stats, $name, $axis, $mod = 1)
{
    $chart = new Linechart(['active' => false]);

    if (is_array($stats) && count($stats) > 0) {
        $chart->setActive(true);
        $data = [];
        $y    = $axis->y;
        $x    = $axis->x;
        foreach ($stats as $j => $stat) {
            $obj    = new stdClass();
            $obj->y = round((float)$stat->$y, 2, 1);

            if ($j % $mod === 0) {
                $chart->addAxis($stat->$x);
            } else {
                $chart->addAxis('|');
            }

            $data[] = $obj;
        }

        $chart->addSerie($name, $data);
        $chart->memberToJSON();
    }

    return $chart;
}

/**
 * @param array  $stats
 * @param string $name
 * @param object $axis
 * @param int    $maxEntries
 * @return Piechart
 */
function preparePieChartStats($stats, $name, $axis, $maxEntries = 6)
{
    $chart = new Piechart(['active' => false]);
    if (is_array($stats) && count($stats) > 0) {
        $chart->setActive(true);
        $data = [];

        $y = $axis->y;
        $x = $axis->x;

        // Zeige nur $maxEntries Main Member + 1 Sonstige an, sonst wird es zu unuebersichtlich
        if (count($stats) > $maxEntries) {
            $statstmp  = [];
            $other     = new stdClass();
            $other->$y = 0;
            $other->$x = __('miscellaneous');
            foreach ($stats as $i => $stat) {
                if ($i < $maxEntries) {
                    $statstmp[] = $stat;
                } else {
                    $other->$y += $stat->$y;
                }
            }

            $statstmp[] = $other;
            $stats      = $statstmp;
        }

        foreach ($stats as $stat) {
            $value  = round((float)$stat->$y, 2, 1);
            $data[] = [$stat->$x, $value];
        }

        $chart->addSerie($name, $data);
        $chart->memberToJSON();
    }

    return $chart;
}

/**
 * @param array  $series
 * @param object $axis
 * @param int    $mod
 * @return Linechart
 */
function prepareLineChartStatsMulti($series, $axis, $mod = 1)
{
    $chart = new Linechart(['active' => false]);
    if (is_array($series) && count($series) > 0) {
        $i = 0;
        foreach ($series as $Name => $Serie) {
            if (is_array($Serie) && count($Serie) > 0) {
                $chart->setActive(true);
                $data = [];
                $y    = $axis->y;
                $x    = $axis->x;
                foreach ($Serie as $j => $stat) {
                    $obj    = new stdClass();
                    $obj->y = round((float)$stat->$y, 2, 1);

                    if ($j % $mod === 0) {
                        $chart->addAxis($stat->$x);
                    } else {
                        $chart->addAxis('|');
                    }

                    $data[] = $obj;
                }

                $colors = GetLineChartColors($i);
                $chart->addSerie($Name, $data, $colors[0], $colors[1], $colors[2]);
                $chart->memberToJSON();
            }

            $i++;
        }
    }

    return $chart;
}

/**
 * @param int $number
 * @return mixed
 */
function GetLineChartColors($number)
{
    $colors = [
        ['#435a6b', '#a168f2', '#435a6b'],
        ['#5cbcf6', '#5cbcf6', '#5cbcf6']
    ];

    return $colors[$number] ?? $colors[0];
}
