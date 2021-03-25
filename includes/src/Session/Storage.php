<?php

namespace JTL\Session;

use JTL\Session\Handler\Bot;
use JTL\Session\Handler\DB;
use JTL\Session\Handler\JTLDefault;
use JTL\Session\Handler\JTLHandlerInterface;
use JTL\Shop;

/**
 * Class Storage
 * @package JTL\Session
 */
class Storage
{
    /**
     * @var \SessionHandlerInterface
     */
    protected $handler;

    /**
     * Storage constructor.
     */
    public function __construct()
    {
        \session_register_shutdown();
        $this->handler = $this->initHandler();
        $res           = \get_class($this->handler) === JTLDefault::class
            ? true
            : \session_set_save_handler($this->handler, true);
        if ($res !== true) {
            throw new \RuntimeException('Failed to set session handler');
        }
    }

    /**
     * @param string $userAgent
     * @return bool
     */
    public static function getIsCrawler(string $userAgent): bool
    {
        return \preg_match(
            '/Google|ApacheBench|sqlmap|loader.io|bot|Rambler|Yahoo|AbachoBOT|accoona' .
            '|spider|AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|GeonaBot' .
            '|Gigabot|Lycos|alexa|AltaVista|IDBot|Scrubby/',
            $userAgent
        ) > 0;
    }

    /**
     * @return JTLHandlerInterface
     */
    private function initHandler(): JTLHandlerInterface
    {
        $bot           = \SAVE_BOT_SESSION !== 0 && isset($_SERVER['HTTP_USER_AGENT'])
            ? self::getIsCrawler($_SERVER['HTTP_USER_AGENT'])
            : false;
        $this->handler = $bot === false || \SAVE_BOT_SESSION === Behaviour::DEFAULT
            ? $this->initDefaultHandler()
            : $this->initBotHandler();

        return $this->handler;
    }

    /**
     * @return JTLHandlerInterface
     */
    private function initDefaultHandler(): JTLHandlerInterface
    {
        return \ES_SESSIONS === 1
            ? new DB(Shop::Container()->getDB())
            : new JTLDefault();
    }

    /**
     * @return JTLHandlerInterface
     */
    private function initBotHandler(): JTLHandlerInterface
    {
        if (\SAVE_BOT_SESSION === Behaviour::COMBINE || \SAVE_BOT_SESSION === Behaviour::CACHE) {
            \session_id('jtl-bot');
        }
        if (\SAVE_BOT_SESSION === Behaviour::CACHE || \SAVE_BOT_SESSION === Behaviour::NO_SAVE) {
            $save = \SAVE_BOT_SESSION === Behaviour::CACHE
                && Shop::Container()->getCache()->isAvailable()
                && Shop::Container()->getCache()->isActive();

            return new Bot($save);
        }

        return new JTLDefault();
    }

    /**
     * @return JTLHandlerInterface
     */
    public function getHandler(): JTLHandlerInterface
    {
        return $this->handler;
    }

    /**
     * @param JTLHandlerInterface $handler
     */
    public function setHandler(JTLHandlerInterface $handler): void
    {
        $this->handler = $handler;
    }
}
