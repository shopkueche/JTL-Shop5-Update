<?php declare(strict_types=1);

namespace JTL\Widgets;

use JTL\Shop;

/**
 * Class Shopinfo
 * @package JTL\Widgets
 */
class Shopinfo extends AbstractWidget
{
    /**
     *
     */
    public function init()
    {
        $strTplVersion   = Shop::Container()->getTemplateService()->getActiveTemplate()->getVersion();
        $strFileVersion  = Shop::getApplicationVersion();
        $strDBVersion    = Shop::getShopDatabaseVersion();
        $strUpdated      = \date_format(\date_create(\getJTLVersionDB(true)), 'd.m.Y, H:i:m');
        $strMinorVersion = \APPLICATION_BUILD_SHA === '#DEV#' ? 'DEV' : '';

        $this->oSmarty->assign('strFileVersion', $strFileVersion)
            ->assign('strDBVersion', $strDBVersion)
            ->assign('strTplVersion', $strTplVersion)
            ->assign('strUpdated', $strUpdated)
            ->assign('strMinorVersion', $strMinorVersion);
    }

    /**
     * @return string
     * @throws \SmartyException
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/shopinfo.tpl');
    }
}
