<?php declare(strict_types=1);

namespace JTL\Widgets;

use JTL\Shop;

/**
 * Class Serverinfo
 * @package JTL\Widgets
 */
class Serverinfo extends AbstractWidget
{
    /**
     *
     */
    public function init()
    {
        $parsed = \parse_url(Shop::getURL());
        $this->oSmarty->assign('phpOS', \PHP_OS)
            ->assign('phpVersion', \PHP_VERSION)
            ->assign('serverAddress', $_SERVER['SERVER_ADDR'] ?? '?')
            ->assign('serverHTTPHost', $_SERVER['HTTP_HOST'] ?? '?')
            ->assign('mySQLVersion', $this->oDB->getServerInfo())
            ->assign('mySQLStats', $this->oDB->getServerStats())
            ->assign('cShopHost', $parsed['scheme'] . '://' . $parsed['host']);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/serverinfo.tpl');
    }
}
