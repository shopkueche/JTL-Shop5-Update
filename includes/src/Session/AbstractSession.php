<?php

namespace JTL\Session;

use JTL\Helpers\Request;
use JTL\Session\Handler\JTLHandlerInterface;
use JTL\Shop;
use function Functional\last;

/**
 * Class AbstractSession
 * @package JTL\Session
 */
abstract class AbstractSession
{
    /**
     * @var JTLHandlerInterface
     */
    protected static $handler;

    /**
     * @var string
     */
    protected static $sessionName;

    /**
     * AbstractSession constructor.
     * @param bool   $start
     * @param string $sessionName
     */
    public function __construct(bool $start, string $sessionName)
    {
        self::$sessionName = $sessionName;
        \session_name(self::$sessionName);
        self::$handler = (new Storage())->getHandler();
        $this->initCookie(Shop::getSettings([\CONF_GLOBAL])['global'], $start);
        self::$handler->setSessionData($_SESSION);
    }

    /**
     * pre-calculate all the localized shop base URLs
     */
    protected function initLanguageURLs(): void
    {
        if (\EXPERIMENTAL_MULTILANG_SHOP !== true) {
            return;
        }
        $urls      = [];
        $sslStatus = Request::checkSSL();
        foreach ($_SESSION['Sprachen'] ?? [] as $language) {
            $code    = \mb_convert_case($language->getCode(), \MB_CASE_UPPER);
            $shopURL = \defined('URL_SHOP_' . $code) ? \constant('URL_SHOP_' . $code) : \URL_SHOP;
            foreach ([0, 1] as $forceSSL) {
                if ($sslStatus === 2) {
                    $shopURL = \str_replace('http://', 'https://', $shopURL);
                } elseif ($sslStatus === 4 || ($sslStatus === 3 && $forceSSL)) {
                    $shopURL = \str_replace('http://', 'https://', $shopURL);
                }
                $urls[$language->getId()][$forceSSL] = \rtrim($shopURL, '/');
            }
        }
        Shop::setURLs($urls);
    }

    /**
     * @return string
     */
    public static function getSessionName(): string
    {
        return self::$sessionName;
    }

    /**
     * @param array $conf
     * @param bool  $start
     * @return bool
     */
    protected function initCookie(array $conf, bool $start = true): bool
    {
        $cookieConfig = new CookieConfig($conf);
        if ($start) {
            $this->start($cookieConfig);
        }
        $this->setCookie($cookieConfig);
        $this->clearDuplicateCookieHeaders();

        return true;
    }

    /**
     * @param CookieConfig $cookieConfig
     * @return bool
     */
    private function setCookie(CookieConfig $cookieConfig): bool
    {
        return \setcookie(
            \session_name(),
            \session_id(),
            [
                'expires'  => ($cookieConfig->getLifetime() === 0) ? 0 : \time() + $cookieConfig->getLifetime(),
                'path'     => $cookieConfig->getPath(),
                'domain'   => $cookieConfig->getDomain(),
                'secure'   => $cookieConfig->isSecure(),
                'httponly' => $cookieConfig->isHttpOnly(),
                'samesite' => $cookieConfig->getSameSite()
            ]
        );
    }

    /**
     * @param CookieConfig $cookieConfig
     * @return bool
     */
    private function start(CookieConfig $cookieConfig): bool
    {
        return \session_start($cookieConfig->getSessionConfigArray());
    }

    /**
     * session_start() and setcookie both create Set-Cookie headers
     */
    private function clearDuplicateCookieHeaders(): void
    {
        if (\headers_sent()) {
            return;
        }
        $cookies = [];
        foreach (\headers_list() as $header) {
            // Identify cookie headers
            if (\strpos($header, 'Set-Cookie:') === 0) {
                $cookies[] = $header;
            }
        }
        if (\count($cookies) > 1) {
            \header_remove('Set-Cookie');
            \header(last($cookies), false);
        }
    }

    /**
     * @param string     $key
     * @param null|mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return self::$handler->get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return mixed
     */
    public static function set($key, $value)
    {
        return self::$handler->set($key, $value);
    }

    /**
     * @param array  $allowed
     * @param string $default
     * @return string
     */
    protected function getBrowserLanguage(array $allowed, string $default): string
    {
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
        if (empty($acceptLanguage)) {
            return $default;
        }
        $accepted = \preg_split('/,\s*/', $acceptLanguage);
        $current  = $default;
        $quality  = 0;
        foreach ($accepted as $lang) {
            $res = \preg_match(
                '/^([a-z]{1,8}(?:-[a-z]{1,8})*)' .
                '(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i',
                $lang,
                $matches
            );
            if (!$res) {
                continue;
            }
            $codes       = \explode('-', $matches[1]);
            $langQuality = isset($matches[2])
                ? (float)$matches[2]
                : 1.0;
            while (\count($codes)) {
                if ($langQuality > $quality
                    && \in_array(\mb_convert_case(\implode('-', $codes), \MB_CASE_LOWER), $allowed, true)
                ) {
                    $current = \mb_convert_case(\implode('-', $codes), \MB_CASE_LOWER);
                    $quality = $langQuality;
                    break;
                }
                \array_pop($codes);
            }
        }

        return $current;
    }
}
