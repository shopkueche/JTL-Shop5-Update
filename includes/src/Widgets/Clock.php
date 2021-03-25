<?php declare(strict_types=1);

namespace JTL\Widgets;

/**
 * Class Clock
 * @package JTL\Widgets
 */
class Clock extends AbstractWidget
{
    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/clock.tpl');
    }
}
