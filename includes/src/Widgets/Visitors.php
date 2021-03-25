<?php declare(strict_types=1);

namespace JTL\Widgets;

use JTL\Linechart;
use JTL\Statistik;

/**
 * Class Visitors
 * @package JTL\Widgets
 */
class Visitors extends AbstractWidget
{
    /**
     *
     */
    public function init()
    {
    }

    /**
     * @return array
     */
    public function getVisitorsOfCurrentMonth(): array
    {
        $oStatistik = new Statistik(\firstDayOfMonth(), \time());

        return $oStatistik->holeBesucherStats(2);
    }

    /**
     * @return array
     */
    public function getVisitorsOfLastMonth(): array
    {
        $month = \date('m') - 1;
        $year  = (int)\date('Y');
        if ($month <= 0) {
            $month = 12;
            $year  = \date('Y') - 1;
        }
        $oStatistik = new Statistik(\firstDayOfMonth($month, $year), \lastDayOfMonth($month, $year));

        return $oStatistik->holeBesucherStats(2);
    }

    /**
     * @return Linechart
     */
    public function getJSON(): Linechart
    {
        require_once \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . 'statistik_inc.php';
        $currentMonth = $this->getVisitorsOfCurrentMonth();
        $lastMonth    = $this->getVisitorsOfLastMonth();
        foreach ($currentMonth as $oCurrentMonth) {
            $oCurrentMonth->dZeit = \mb_substr($oCurrentMonth->dZeit, 0, 2);
        }
        foreach ($lastMonth as $oLastMonth) {
            $oLastMonth->dZeit = \mb_substr($oLastMonth->dZeit, 0, 2);
        }

        $series = [
            'Letzter Monat' => $lastMonth,
            'Dieser Monat'  => $currentMonth
        ];

        return \prepareLineChartStatsMulti($series, \getAxisNames(\STATS_ADMIN_TYPE_BESUCHER), 2);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->assign('linechart', $this->getJSON())
                             ->fetch('tpl_inc/widgets/visitors.tpl');
    }
}
