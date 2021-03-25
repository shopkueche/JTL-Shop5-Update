<?php declare(strict_types=1);

namespace JTL\Widgets;

use JTL\Statistik;

/**
 * Class Bots
 * @package JTL\Widgets
 */
class Bots extends AbstractWidget
{
    /**
     * @var array
     */
    public $bots;

    /**
     *
     */
    public function init()
    {
        $this->bots = $this->getBotsOfMonth((int)\date('Y'), (int)\date('m'));
    }

    /**
     * @param int $year
     * @param int $month
     * @param int $limit
     * @return array
     */
    public function getBotsOfMonth(int $year, int $month, int $limit = 10)
    {
        return (new Statistik(\firstDayOfMonth($month, $year), \time()))->holeBotStats($limit);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->assign('oBots_arr', $this->bots)->fetch('tpl_inc/widgets/bots.tpl');
    }
}
