<?php declare(strict_types=1);

namespace JTL\Widgets;

/**
 * Class Help
 * @package JTL\Widgets
 */
class Help extends AbstractWidget
{
    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/help.tpl');
    }
}
