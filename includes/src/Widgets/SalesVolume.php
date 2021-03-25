<?php declare(strict_types=1);

namespace JTL\Widgets;

use DateTime;
use JTL\Catalog\Product\Preise;
use JTL\Linechart;

/**
 * Class SalesVolume
 * @package JTL\Widgets
 */
class SalesVolume extends AbstractWidget
{
    /**
     * @var \stdClass
     */
    public $oWaehrung;

    /**
     *
     */
    public function init()
    {
        require_once \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . 'statistik_inc.php';
        $this->oWaehrung = $this->oDB->select('twaehrung', 'cStandard', 'Y');
    }

    /**
     * @param int $month
     * @param int $year
     * @return array|mixed
     */
    public function calcVolumeOfMonth(int $month, int $year)
    {
        $interval = 0;
        $stats    = \gibBackendStatistik(
            \STATS_ADMIN_TYPE_UMSATZ,
            \firstDayOfMonth($month, $year),
            \lastDayOfMonth($month, $year),
            $interval
        );
        foreach ($stats as $stat) {
            $stat->cLocalized = Preise::getLocalizedPriceString($stat->nCount, $this->oWaehrung);
        }

        return $stats;
    }

    /**
     * @return Linechart
     */
    public function getJSON(): Linechart
    {
        $dateLastMonth = new DateTime();
        $dateLastMonth->modify('-1 month');
        $dateLastMonth = (int)$dateLastMonth->format('U');
        $currentMonth  = $this->calcVolumeOfMonth((int)\date('n'), (int)\date('Y'));
        $lastMonth     = $this->calcVolumeOfMonth((int)\date('n', $dateLastMonth), (int)\date('Y', $dateLastMonth));
        foreach ($currentMonth as $month) {
            $month->dZeit = \mb_substr($month->dZeit, 0, 2);
        }
        foreach ($lastMonth as $month) {
            $month->dZeit = \mb_substr($month->dZeit, 0, 2);
        }
        $series = [
            'Letzter Monat' => $lastMonth,
            'Dieser Monat'  => $currentMonth
        ];

        return \prepareLineChartStatsMulti($series, \getAxisNames(\STATS_ADMIN_TYPE_UMSATZ), 2);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->assign('linechart', $this->getJSON())
                             ->fetch('tpl_inc/widgets/sales_volume.tpl');
    }
}
