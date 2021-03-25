<?php declare(strict_types=1);

namespace JTL\Widgets;

/**
 * Class Patch
 * @package JTL\Widgets
 */
class Patch extends AbstractWidget
{
    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->assign('version', \getJTLVersionDB())->fetch('tpl_inc/widgets/patch.tpl');
    }
}
