<?php declare(strict_types=1);

namespace JTL\Session;

use JTL\Language\LanguageHelper;

/**
 * Class CookieConfig
 * @package JTL\Session
 */
class CookieConfig
{
    /**
     * @var string
     */
    private $path = '';

    /**
     * @var string
     */
    private $domain = '';

    /**
     * @var string
     */
    private $sameSite = '';

    /**
     * @var int
     */
    private $lifetime = 0;

    /**
     * @var bool
     */
    private $httpOnly = false;

    /**
     * @var bool
     */
    private $secure = false;

    /**
     * CookieConfig constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->readDefaults();
        $this->mergeWithConfig($config);
    }

    /**
     *
     */
    private function readDefaults(): void
    {
        $defaults       = \session_get_cookie_params();
        $this->lifetime = $defaults['lifetime'] ?? 0;
        $this->path     = $defaults['path'] ?? '';
        $this->domain   = $defaults['domain'] ?? '';
        $this->secure   = $defaults['secure'] ?? false;
        $this->httpOnly = $defaults['httponly'] ?? false;
        $this->sameSite = $defaults['samesite'] ?? '';
    }

    /**
     * @param array $config
     */
    private function mergeWithConfig(array $config): void
    {
        $this->secure   = $this->secure || $config['global_cookie_secure'] === 'Y';
        $this->httpOnly = $this->httpOnly || $config['global_cookie_httponly'] === 'Y';
        if (($config['global_cookie_samesite'] ?? '') !== 'S') {
            $this->sameSite = $config['global_cookie_samesite'] ?? 'S';
            if ($this->sameSite === 'N') {
                $this->sameSite = '';
            }
        }
        if (($config['global_cookie_domain'] ?? '') !== '') {
            $this->domain = $this->experimentalMultiLangDomain($config['global_cookie_domain']);
        }
        if (\is_numeric($config['global_cookie_lifetime']) && (int)$config['global_cookie_lifetime'] > 0) {
            $this->lifetime = (int)$config['global_cookie_lifetime'];
        }
        if (!empty($config['global_cookie_path'])) {
            $this->path = $config['global_cookie_path'];
        }
        $this->secure = $this->secure && ($config['kaufabwicklung_ssl_nutzen'] === 'P'
                || \mb_strpos(\URL_SHOP, 'https://') === 0);
    }

    /**
     * @param string $domain
     * @return string
     */
    private function experimentalMultiLangDomain(string $domain): string
    {
        if (\EXPERIMENTAL_MULTILANG_SHOP !== true) {
            return $domain;
        }
        $host = $_SERVER['HTTP_HOST'] ?? ' ';
        foreach (LanguageHelper::getAllLanguages() as $language) {
            $code = \mb_convert_case($language->getCode(), \MB_CASE_UPPER);
            if (!\defined('URL_SHOP_' . $code)) {
                continue;
            }
            $localized = \constant('URL_SHOP_' . $code);
            if (\mb_strpos($localized, $host) !== false && \defined('COOKIE_DOMAIN_' . $code)) {
                return \constant('COOKIE_DOMAIN_' . $code);
            }
        }

        return $domain;
    }

    /**
     * @return array
     */
    public function getSessionConfigArray(): array
    {
        return [
            'use_cookies'     => '1',
            'cookie_domain'   => $this->getDomain(),
            'cookie_secure'   => $this->isSecure(),
            'cookie_lifetime' => $this->getLifetime(),
            'cookie_path'     => $this->getPath(),
            'cookie_httponly' => $this->isHttpOnly(),
            'cookie_samesite' => $this->getSameSite()
        ];
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getSameSite(): string
    {
        return $this->sameSite;
    }

    /**
     * @param string $sameSite
     */
    public function setSameSite(string $sameSite): void
    {
        $this->sameSite = $sameSite;
    }

    /**
     * @return int
     */
    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    /**
     * @param int $lifetime
     */
    public function setLifetime(int $lifetime): void
    {
        $this->lifetime = $lifetime;
    }

    /**
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * @param bool $httpOnly
     */
    public function setHttpOnly(bool $httpOnly): void
    {
        $this->httpOnly = $httpOnly;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @param bool $secure
     */
    public function setSecure(bool $secure): void
    {
        $this->secure = $secure;
    }
}
