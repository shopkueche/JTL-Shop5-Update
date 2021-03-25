<?php

namespace JTL\Helpers;

use JTL\SingletonTrait;

/**
 * Class PHPSettings
 * @package JTL\Helpers
 */
class PHPSettings
{
    use SingletonTrait;

    /**
     * @param string $shorthand
     * @return int
     */
    private function shortHandToInt($shorthand): int
    {
        switch (\mb_substr($shorthand, -1)) {
            case 'M':
            case 'm':
                return (int)$shorthand * 1048576;
            case 'K':
            case 'k':
                return (int)$shorthand * 1024;
            case 'G':
            case 'g':
                return (int)$shorthand * 1073741824;
            default:
                return (int)$shorthand;
        }
    }

    /**
     * @return int
     */
    public function limit(): int
    {
        return $this->shortHandToInt(\ini_get('memory_limit'));
    }

    /**
     * @return string
     */
    public function version(): string
    {
        return \PHP_VERSION;
    }

    /**
     * @return int
     */
    public function executionTime(): int
    {
        return (int)\ini_get('max_execution_time');
    }

    /**
     * @return int
     */
    public function postMaxSize(): int
    {
        return $this->shortHandToInt(\ini_get('post_max_size'));
    }

    /**
     * @return int
     */
    public function uploadMaxFileSize(): int
    {
        return $this->shortHandToInt(\ini_get('upload_max_filesize'));
    }

    /**
     * @return bool
     */
    public function safeMode(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function tempDir(): string
    {
        return \sys_get_temp_dir();
    }

    /**
     * @return bool
     */
    public function fopenWrapper(): bool
    {
        return (bool)\ini_get('allow_url_fopen');
    }

    /**
     * @param int $limit - in bytes
     * @return bool
     */
    public function hasMinLimit(int $limit): bool
    {
        $value = $this->limit();

        return $value === -1 || $value === 0 || $value >= $limit;
    }

    /**
     * @param int $limit - in S
     * @return bool
     */
    public function hasMinExecutionTime(int $limit): bool
    {
        return ($this->executionTime() >= $limit || $this->executionTime() === 0);
    }

    /**
     * @param int $limit - in bytes
     * @return bool
     */
    public function hasMinPostSize(int $limit): bool
    {
        return $this->postMaxSize() >= $limit;
    }

    /**
     * @param int $limit - in bytes
     * @return bool
     */
    public function hasMinUploadSize(int $limit): bool
    {
        return $this->uploadMaxFileSize() >= $limit;
    }

    /**
     * @return bool
     */
    public function isTempWriteable(): bool
    {
        return \is_writable($this->tempDir());
    }

    /**
     * @param string $url
     * @return bool
     * @former pruefeSOAP()
     * @since 5.0.0
     */
    public static function checkSOAP(string $url = ''): bool
    {
        return !(\mb_strlen($url) > 0 && !self::phpLinkCheck($url)) && \class_exists('SoapClient');
    }

    /**
     * @param string $cURL
     * @return bool
     * @former pruefeCURL()
     * @since 5.0.0
     */
    public static function checkCURL(string $cURL = ''): bool
    {
        return !(\mb_strlen($cURL) > 0 && !self::phpLinkCheck($cURL)) && \function_exists('curl_init');
    }

    /**
     * @return bool
     * @former pruefeALLOWFOPEN()
     * @since 5.0.0
     */
    public static function checkAllowFopen(): bool
    {
        return (int)\ini_get('allow_url_fopen') === 1;
    }

    /**
     * @param string $cSOCKETS
     * @return bool
     * @former pruefeSOCKETS()
     * @since 5.0.0
     */
    public static function checkSockets(string $cSOCKETS = ''): bool
    {
        return !(\mb_strlen($cSOCKETS) > 0 && !self::phpLinkCheck($cSOCKETS)) && \function_exists('fsockopen');
    }

    /**
     * @param string $url
     * @return bool
     * @former phpLinkCheck()
     * @since 5.0.0
     */
    public static function phpLinkCheck(string $url): bool
    {
        $errno  = null;
        $errstr = null;
        $url    = \parse_url(\trim($url));
        $scheme = \mb_convert_case($url['scheme'], \MB_CASE_LOWER);
        if ($scheme !== 'http' && $scheme !== 'https') {
            return false;
        }
        if (!isset($url['port'])) {
            $url['port'] = 80;
        }
        if (!isset($url['path'])) {
            $url['path'] = '/';
        }

        return !\fsockopen($url['host'], $url['port'], $errno, $errstr, 30)
            ? false
            : true;
    }
}
