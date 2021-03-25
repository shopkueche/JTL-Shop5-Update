<?php

namespace JTL\Helpers;

use JTL\Catalog\Category\MenuItem;
use JTL\Language\LanguageHelper;
use JTL\Link\LinkInterface;
use JTL\Link\SpecialPageNotFoundException;
use JTL\News\Item;
use JTL\Shop;

/**
 * Class URL
 * @package JTL\Helpers
 */
class URL
{
    /**
     * @var string
     */
    private $url = '';

    /**
     * @var string
     */
    private $scheme = '';

    /**
     * @var string
     */
    private $host = '';

    /**
     * @var string
     */
    private $port = '';

    /**
     * @var string
     */
    private $user = '';

    /**
     * @var string
     */
    private $pass = '';

    /**
     * @var string
     */
    private $path = '';

    /**
     * @var string
     */
    private $query = '';

    /**
     * @var string
     */
    private $fragment = '';

    /**
     * @var array
     */
    private $defaultPorts = ['http' => 80, 'https' => 443];

    /**
     * @param string|null $url
     */
    public function __construct($url = null)
    {
        if ($url) {
            $this->setUrl($url);
        }
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return bool
     */
    public function setUrl(string $url): bool
    {
        $this->url = $url;
        // parse URL into respective parts
        $url_components = \parse_url($this->url);

        if (!$url_components) {
            return false;
        }
        foreach ($url_components as $key => $value) {
            if (\property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function normalize(): string
    {
        if ($this->path) {
            // case normalization
            $this->path = \preg_replace_callback(
                '/(%([0-9abcdef][0-9abcdef]))/x',
                static function ($x) {
                    return '%' . \mb_convert_case($x[2], \MB_CASE_UPPER);
                },
                $this->path
            );
            // percent-encoding normalization
            $this->path = $this->urlDecodeUnreservedChars($this->path);
            // path segment normalization
            $this->path = $this->removeDotSegments($this->path);
        }

        $scheme = '';
        if ($this->scheme) {
            $this->scheme = \mb_convert_case($this->scheme, \MB_CASE_LOWER);
            $scheme       = $this->scheme . '://';
        }

        if ($this->host) {
            $this->host = \mb_convert_case($this->host, \MB_CASE_LOWER);
        }

        $this->schemeBasedNormalization();

        // reconstruct uri
        $query = '';
        if ($this->query) {
            $query = '?' . $this->query;
        }

        $fragment = '';
        if ($this->fragment) {
            $fragment = '#' . $this->fragment;
        }

        $port = '';
        if ($this->port) {
            $port = ':' . $this->port;
        }

        $authorization = '';
        if ($this->user) {
            $authorization = $this->user . ':' . $this->pass . '@';
        }

        return $scheme . $authorization . $this->host . $port . $this->path . $query . $fragment;
    }

    /**
     * Decode unreserved characters
     *
     * @see http://www.apps.ietf.org/rfc/rfc3986.html#sec-2.3
     * @param string $string
     * @return mixed
     */
    public function urlDecodeUnreservedChars(string $string)
    {
        $unreserved = [];
        for ($octet = 65; $octet <= 90; $octet++) {
            $unreserved[] = \dechex($octet);
        }
        for ($octet = 97; $octet <= 122; $octet++) {
            $unreserved[] = \dechex($octet);
        }
        for ($octet = 48; $octet <= 57; $octet++) {
            $unreserved[] = \dechex($octet);
        }

        $unreserved[] = \dechex(\mb_ord('-'));
        $unreserved[] = \dechex(\mb_ord('.'));
        $unreserved[] = \dechex(\mb_ord('_'));
        $unreserved[] = \dechex(\mb_ord('~'));

        return \preg_replace_callback(\array_map(
            static function ($str) {
                return '/%' . \mb_convert_case($str, \MB_CASE_UPPER) . '/x';
            },
            $unreserved
        ), static function ($matches) {
            return \chr(\hexdec($matches[0]));
        }, $string);
    }

    /**
     * Path segment normalization
     *
     * @see http://www.apps.ietf.org/rfc/rfc3986.html#sec-5.2.4
     * @param string $path
     * @return string
     */
    public function removeDotSegments($path): string
    {
        $new_path = '';
        while (!empty($path)) {
            // A
            $pattern_a   = '!^(\.\./|\./)!x';
            $pattern_b_1 = '!^(/\./)!x';
            $pattern_b_2 = '!^(/\.)$!x';
            $pattern_c   = '!^(/\.\./|/\.\.)!x';
            $pattern_d   = '!^(\.|\.\.)$!x';
            $pattern_e   = '!(/*[^/]*)!x';

            if (\preg_match($pattern_a, $path)) {
                // remove prefix from $path
                $path = \preg_replace($pattern_a, '', $path);
            } elseif (\preg_match($pattern_b_1, $path, $matches) || \preg_match($pattern_b_2, $path, $matches)) {
                $path = \preg_replace('!^' . $matches[1] . '!', '/', $path);
            } elseif (\preg_match($pattern_c, $path, $matches)) {
                $path = \preg_replace('!^' . \preg_quote($matches[1], '!') . '!x', '/', $path);
                // remove the last segment and its preceding "/" (if any) from output buffer
                $new_path = \preg_replace('!/([^/]+)$!x', '', $new_path);
            } elseif (\preg_match($pattern_d, $path)) {
                $path = \preg_replace($pattern_d, '', $path);
            } elseif (\preg_match($pattern_e, $path, $matches)) {
                $first_path_segment = $matches[1];

                $path = \preg_replace('/^' . \preg_quote($first_path_segment, '/') . '/', '', $path, 1);

                $new_path .= $first_path_segment;
            }
        }

        return $new_path;
    }

    /**
     * @return $this;
     */
    private function schemeBasedNormalization(): self
    {
        if (isset($this->defaultPorts[$this->scheme]) && $this->defaultPorts[$this->scheme] == $this->port) {
            $this->port = '';
        }

        return $this;
    }

    /**
     * @param object $obj
     * @param int    $type
     * @param bool   $full
     * @return string
     * @former baueURL()
     * @since 5.0.0
     */
    public static function buildURL($obj, int $type, bool $full = false): string
    {
        if ($obj instanceof LinkInterface) {
            return $obj->getURL();
        }
        $lang   = !LanguageHelper::isDefaultLanguageActive(true)
            ? ('&lang=' . Shop::getLanguageCode())
            : '';
        $prefix = $full === false ? '' : Shop::getURL() . '/';

        if ($type && $obj) {
            \executeHook(\HOOK_TOOLSGLOBAL_INC_SWITCH_BAUEURL, ['obj' => &$obj, 'art' => &$type]);
            switch ($type) {
                case \URLART_ARTIKEL:
                    return !empty($obj->cSeo)
                        ? $prefix . $obj->cSeo
                        : $prefix . '?a=' . $obj->kArtikel . $lang;

                case \URLART_KATEGORIE:
                    if ($obj instanceof MenuItem) {
                        return !empty($obj->getURL())
                            ? $prefix . $obj->getURL()
                            : $prefix . '?k=' . $obj->getID() . $lang;
                    }
                    return !empty($obj->cSeo)
                        ? $prefix . $obj->cSeo
                        : $prefix . '?k=' . $obj->kKategorie . $lang;
                case \URLART_SEITE:
                    if (isset($_SESSION['cISOSprache'], $obj->cLocalizedSeo[$_SESSION['cISOSprache']])
                        && \mb_strlen($obj->cLocalizedSeo[$_SESSION['cISOSprache']])
                    ) {
                        return $prefix . $obj->cLocalizedSeo[$_SESSION['cISOSprache']];
                    }
                    // Hole aktuelle Spezialseite und gib den URL Dateinamen zurück
                    $oSpezialseite = Shop::Container()->getDB()->select(
                        'tspezialseite',
                        'nLinkart',
                        (int)$obj->nLinkart
                    );

                    return !empty($oSpezialseite->cDateiname)
                        ? $prefix . $oSpezialseite->cDateiname
                        : $prefix . '?s=' . $obj->kLink . $lang;

                case \URLART_HERSTELLER:
                    return !empty($obj->cSeo)
                        ? $prefix . $obj->cSeo
                        : $prefix . '?h=' . $obj->kHersteller . $lang;

                case \URLART_LIVESUCHE:
                    return !empty($obj->cSeo)
                        ? $prefix . $obj->cSeo
                        : $prefix . '?l=' . $obj->kSuchanfrage . $lang;

                case \URLART_MERKMAL:
                    return !empty($obj->cSeo)
                        ? $prefix . $obj->cSeo
                        : $prefix . '?m=' . $obj->kMerkmalWert . $lang;

                case \URLART_NEWS:
                    if ($obj instanceof Item) {
                        /** @var Item $obj */
                        return !empty($obj->getSEO())
                            ? $obj->getURL()
                            : $prefix . '?n=' . $obj->getID() . $lang;
                    }

                    return !empty($obj->cSeo)
                        ? $prefix . $obj->cSeo
                        : $prefix . '?n=' . $obj->kNews . $lang;

                case \URLART_NEWSMONAT:
                    return !empty($obj->cSeo)
                        ? $prefix . $obj->cSeo
                        : $prefix . '?nm=' . $obj->kNewsMonatsUebersicht . $lang;

                case \URLART_NEWSKATEGORIE:
                    return !empty($obj->cSeo)
                        ? $prefix . $obj->cSeo
                        : $prefix . '?nk=' . $obj->kNewsKategorie . $lang;

                case \URLART_SEARCHSPECIALS:
                    return !empty($obj->cSeo)
                        ? $prefix . $obj->cSeo
                        : $prefix . '?q=' . $obj->kSuchspecial . $lang;

                case \URLART_NEWSLETTER:
                    try {
                        $prefix = $full === false
                            ? ''
                            : Shop::Container()->getLinkService()->getSpecialPage(\LINKTYP_NEWSLETTER)->getURL();
                    } catch (SpecialPageNotFoundException $e) {
                        $prefix = '';
                        Shop::Container()->getLogService()->error($e->getMessage());
                    }
                    return !empty($obj->cSeo)
                        ? $prefix . $obj->cSeo
                        : $prefix . '?show=' . $obj->kNewsletterHistory;
            }
        }

        return '';
    }
}
