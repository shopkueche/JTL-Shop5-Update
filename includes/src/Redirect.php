<?php

namespace JTL;

use JTL\DB\ReturnType;
use JTL\Filter\FilterInterface;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Helpers\URL;
use JTL\Language\LanguageHelper;
use stdClass;

/**
 * Class Redirect
 * @package JTL
 */
class Redirect
{
    /**
     * @var int
     */
    public $kRedirect;

    /**
     * @var string
     */
    public $cFromUrl;

    /**
     * @var string
     */
    public $cToUrl;

    /**
     * @var string
     */
    public $cAvailable;

    /**
     * @var int
     */
    public $nCount = 0;

    /**
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @param int $id
     * @return $this
     */
    public function loadFromDB(int $id): self
    {
        $obj = Shop::Container()->getDB()->select('tredirect', 'kRedirect', $id);
        if ($obj !== null && $obj->kRedirect > 0) {
            $members = \array_keys(\get_object_vars($obj));
            foreach ($members as $member) {
                $this->$member = $obj->$member;
            }
        }

        return $this;
    }

    /**
     * @param int $id
     * @return $this
     * @deprecated since 4.06 - use Redirect::deleteRedirect() instead
     */
    public function delete(int $id): self
    {
        self::deleteRedirect($id);

        return $this;
    }

    /**
     * @return int
     * @deprecated since 4.06 - use Redirect::deleteUnassigned() instead
     */
    public function deleteAll(): int
    {
        return self::deleteUnassigned();
    }

    /**
     * @param string $url
     * @return null|stdClass
     */
    public function find(string $url): ?stdClass
    {
        return Shop::Container()->getDB()->select(
            'tredirect',
            'cFromUrl',
            \mb_substr($this->normalize($url), 0, 255)
        );
    }

    /**
     * Get a redirect by target
     *
     * @param string $targetURL target to search for
     * @return null|stdClass
     */
    public function getRedirectByTarget(string $targetURL): ?stdClass
    {
        return Shop::Container()->getDB()->select('tredirect', 'cToUrl', $this->normalize($targetURL));
    }

    /**
     * @param string $source
     * @param string $destination
     * @return bool
     */
    public function isDeadlock(string $source, string $destination): bool
    {
        $parsed      = \parse_url(Shop::getURL());
        $destination = isset($parsed['path']) ? $parsed['path'] . '/' . $destination : $destination;
        $redirect    = Shop::Container()->getDB()->select('tredirect', 'cFromUrl', $destination, 'cToUrl', $source);

        return $redirect !== null && (int)$redirect->kRedirect > 0;
    }

    /**
     * @param string $source
     * @param string $destination
     * @param bool   $force
     * @return bool
     */
    public function saveExt(string $source, string $destination, bool $force = false): bool
    {
        if (\mb_strlen($source) > 0 && $source[0] !== '/') {
            $source = '/' . $source;
        }

        if ($force
            || (self::checkAvailability($destination)
                && \mb_strlen($source) > 1
                && \mb_strlen($destination) > 1
                && $source !== $destination)
        ) {
            if ($this->isDeadlock($source, $destination)) {
                Shop::Container()->getDB()->delete('tredirect', ['cToUrl', 'cFromUrl'], [$source, $destination]);
            }
            $target = $this->getRedirectByTarget($source);
            if (!empty($target)) {
                $this->saveExt($target->cFromUrl, $destination);
                $ins             = new stdClass();
                $ins->cToUrl     = Text::convertUTF8($destination);
                $ins->cAvailable = 'y';
                Shop::Container()->getDB()->update('tredirect', 'cToUrl', $source, $ins);
            }

            $redirect = $this->find($source);
            if (empty($redirect)) {
                $ins             = new stdClass();
                $ins->cFromUrl   = Text::convertUTF8($source);
                $ins->cToUrl     = Text::convertUTF8($destination);
                $ins->cAvailable = 'y';

                $kRedirect = Shop::Container()->getDB()->insert('tredirect', $ins);
                if ($kRedirect > 0) {
                    return true;
                }
            } elseif ($this->normalize($redirect->cFromUrl) === $this->normalize($source)
                && empty($redirect->cToUrl)
                && Shop::Container()->getDB()->update(
                    'tredirect',
                    'cFromUrl',
                    $this->normalize($source),
                    (object)['cToUrl' => Text::convertUTF8($destination)]
                ) > 0
            ) {
                // the redirect already exists but has an empty cToUrl => update it
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $file
     * @return array
     * @deprecated since 5.0.0 - \handleCsvImportAction() in /admin/includes/in csv_import__inc.php is used instead
     */
    public function doImport(string $file): array
    {
        $errors = [];
        if (\file_exists($file)) {
            $handle = \fopen($file, 'r');
            if ($handle) {
                $language = LanguageHelper::getDefaultLanguage();
                $mapping  = [];
                $i        = 0;
                while (($csv = \fgetcsv($handle, 30000, ';')) !== false) {
                    if ($i > 0) {
                        if ($mapping !== null) {
                            $this->import($csv, $i, $errors, $mapping, $language);
                        } else {
                            $errors[] = 'Die Kopfzeile entspricht nicht der Konvention!';
                            break;
                        }
                    } else {
                        $mapping = $this->readHeadRow($csv);
                    }
                    $i++;
                }
                \fclose($handle);
            } else {
                $errors[] = 'Datei konnte nicht gelesen werden';
            }
        } else {
            $errors[] = 'Datei konnte nicht gefunden werden';
        }

        return $errors;
    }

    /**
     * @param string $csv
     * @param int    $row
     * @param array  $errors
     * @param array  $mapping
     * @param object $language
     * @return $this
     * @deprecated since 5.0.0 - \handleCsvImportAction() in /admin/includes/in csv_import_inc.php is used instead
     */
    protected function import($csv, $row, &$errors, $mapping, $language): self
    {
        $parsed = \parse_url($csv[$mapping['sourceurl']]);
        $from   = $parsed['path'];
        if (isset($parsed['query'])) {
            $from .= '?' . $parsed['query'];
        }
        $options           = ['cFromUrl' => $from];
        $options['cArtNr'] = $csv[$mapping['articlenumber']] ?? null;
        $options['cToUrl'] = $csv[$mapping['destinationurl']] ?? null;
        $options['cIso']   = $csv[$mapping['languageiso']] ?? $language->cISO;
        if ($options['cArtNr'] === null && $options['cToUrl'] === null) {
            $errors[] = 'Row ' . $row . ': articlenumber und destinationurl sind nicht vorhanden oder fehlerhaft';
        } elseif ($options['cArtNr'] !== null && $options['cToUrl'] !== null) {
            $errors[] = 'Row ' . $row . ': Nur articlenumber und destinationurl darf vorhanden sein';
        } elseif ($options['cToUrl'] !== null) {
            if (!$this->saveExt($options['cFromUrl'], $options['cToUrl'])) {
                $errors[] = 'Row ' . $row . ': Konnte nicht gespeichert werden (Vielleicht bereits vorhanden?)';
            }
        } else {
            $cUrl = $this->getArtNrUrl($options['cArtNr'], $options['cIso']);
            if ($cUrl !== null) {
                if (!$this->saveExt($options['cFromUrl'], $cUrl)) {
                    $errors[] = 'Row ' . $row . ': Konnte nicht gespeichert werden (Vielleicht bereits vorhanden?)';
                }
            } else {
                $errors[] = 'Row ' . $row . ': Artikelnummer (' .
                    $options['cArtNr'] . ') konnte nicht im Shop gefunden werden';
            }
        }

        return $this;
    }

    /**
     * @param string $artNo
     * @param string $iso
     * @return null|string
     * @deprecated since 5.0.0 - \getArtNrUrl() in /admin/includes/in csv_import_inc.php is used instead
     */
    public function getArtNrUrl($artNo, string $iso): ?string
    {
        if (\mb_strlen($artNo) === 0) {
            return null;
        }
        $item = Shop::Container()->getDB()->executeQueryPrepared(
            "SELECT tartikel.kArtikel, tseo.cSeo
                FROM tartikel
                LEFT JOIN tsprache
                    ON tsprache.cISO = :iso
                LEFT JOIN tseo
                    ON tseo.kKey = tartikel.kArtikel
                    AND tseo.cKey = 'kArtikel'
                    AND tseo.kSprache = tsprache.kSprache
                WHERE tartikel.cArtNr = :artno
                LIMIT 1",
            ['iso' => \mb_convert_case($iso, \MB_CASE_LOWER), 'artno' => $artNo],
            ReturnType::SINGLE_OBJECT
        );

        return URL::buildURL($item, \URLART_ARTIKEL);
    }

    /**
     * Parse head row from import file
     *
     * @param array $rows
     * @return array|null
     * @deprecated since 5.0.0 - \handleCsvImportAction() in /admin/includes/in csv_import_inc.php is used instead
     */
    public function readHeadRow($rows): ?array
    {
        $mapping = ['sourceurl' => null];
        // Must not be present in the file
        $options = ['articlenumber', 'destinationurl', 'languageiso'];
        if (\is_array($rows) && \count($rows) > 0) {
            $members = \array_keys($mapping);
            foreach ($rows as $i => $row) {
                $exist = false;
                if (\in_array($row, $options, true)) {
                    $mapping[$row] = $i;
                    $exist         = true;
                } else {
                    foreach ($members as $cMember) {
                        if ($cMember === $row) {
                            $mapping[$cMember] = $i;
                            $exist             = true;
                            break;
                        }
                    }
                }

                if (!$exist) {
                    return null;
                }
            }

            return $mapping;
        }

        return null;
    }

    /**
     * @param string $cUrl
     * @return bool|string
     */
    public function checkFallbackRedirect(string $cUrl)
    {
        $exploded = \explode('/', \trim($cUrl, '/'));
        if (\count($exploded) > 0) {
            $lastPath = $exploded[\count($exploded) - 1];
            $filename = \strtok($lastPath, '?');
            $seoPath  = Shop::Container()->getDB()->select('tseo', 'cSeo', $lastPath);
            if ($filename === 'jtl.php'
                || $filename === 'warenkorb.php'
                || $filename === 'kontakt.php'
                || $filename === 'news.php'
                || (isset($seoPath->cSeo) && \mb_strlen($seoPath->cSeo) > 0)
            ) {
                return $lastPath;
            }
        }

        return false;
    }

    /**
     * @param string $url
     * @return bool|string
     */
    public function test(string $url)
    {
        // Fallback e.g. if last URL-Path exists in tseo --> do not track 404 hit, instant redirect!
        if (($fallbackPath = $this->checkFallbackRedirect($url)) !== false) {
            return $fallbackPath;
        }
        $redirectUrl = false;
        $url         = $this->normalize($url);
        if (\is_string($url) && \mb_strlen($url) > 0 && $this->isValid($url)) {
            $parsedUrl   = \parse_url($url);
            $queryString = null;
            if (isset($parsedUrl['query'], $parsedUrl['path'])) {
                $url         = $parsedUrl['path'];
                $queryString = $parsedUrl['query'];
            }
            $foundRedirectWithQuery = false;
            if (!empty($queryString)) {
                $item = $this->find($url . '?' . $queryString);
                if ($item !== null) {
                    $url                   .= '?' . $queryString;
                    $foundRedirectWithQuery = true;
                }
            } else {
                $item = $this->find($url);
            }
            if ($item === null) {
                $conf = Shop::getSettings([\CONF_GLOBAL]);
                if (!isset($_GET['notrack'])
                    && (!isset($conf['global']['redirect_save_404']) || $conf['global']['redirect_save_404'] === 'Y')
                ) {
                    $item           = new self();
                    $item->cFromUrl = $url . (!empty($queryString) ? '?' . $queryString : '');
                    $item->cToUrl   = '';
                    unset($item->kRedirect);
                    $item->kRedirect = Shop::Container()->getDB()->insert('tredirect', $item);
                }
            } elseif (\mb_strlen($item->cToUrl) > 0) {
                $redirectUrl  = $item->cToUrl;
                $redirectUrl .= $queryString !== null && !$foundRedirectWithQuery
                    ? '?' . $queryString
                    : '';
            }
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            if (\mb_strlen($referer) > 0) {
                $referer = $this->normalize($referer);
            }
            $ip = Request::getRealIP();
            // Eintrag für diese IP bereits vorhanden?
            $entry = Shop::Container()->getDB()->queryPrepared(
                'SELECT *
                    FROM tredirectreferer tr
                    LEFT JOIN tredirect t
                        ON t.kRedirect = tr.kRedirect
                    WHERE tr.cIP = :ip
                    AND t.cFromUrl = :frm LIMIT 1',
                ['ip' => $ip, 'frm' => $url],
                ReturnType::SINGLE_OBJECT
            );
            if ($entry === false || $entry === null || (\is_object($entry) && (int)$entry->nCount === 0)) {
                $ins               = new stdClass();
                $ins->kRedirect    = $item !== null ? $item->kRedirect : 0;
                $ins->kBesucherBot = isset($_SESSION['oBesucher']->kBesucherBot)
                    ? (int)$_SESSION['oBesucher']->kBesucherBot
                    : 0;
                $ins->cRefererUrl  = \is_string($referer) ? $referer : '';
                $ins->cIP          = $ip;
                $ins->dDate        = \time();
                Shop::Container()->getDB()->insert('tredirectreferer', $ins);
                // this counts only how many different referrers are hitting that url
                if ($item !== null) {
                    ++$item->nCount;
                    Shop::Container()->getDB()->update('tredirect', 'kRedirect', $item->kRedirect, $item);
                }
            }
        }

        return $redirectUrl;
    }

    /**
     * @param string $cUrl
     * @return bool
     */
    public function isValid(string $cUrl): bool
    {
        $pathInfo          = \pathinfo($cUrl);
        $invalidExtensions = [
            'jpg',
            'gif',
            'bmp',
            'xml',
            'ico',
            'txt',
            'png'
        ];
        if (isset($pathInfo['extension']) && \mb_strlen($pathInfo['extension']) > 0) {
            $extension = \mb_convert_case($pathInfo['extension'], \MB_CASE_LOWER);
            if (\in_array($extension, $invalidExtensions, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $cUrl
     * @return bool
     * @deprecated since 4.05 - use Redirect::checkAvailability()
     */
    public function isAvailable(string $cUrl): bool
    {
        return self::checkAvailability($cUrl);
    }

    /**
     * @param string $cUrl
     * @return string
     */
    public function normalize(string $cUrl): string
    {
        $url = new URL();
        $url->setUrl($cUrl);

        return '/' . \trim($url->normalize(), '\\/');
    }

    /**
     * @param int    $redirectedURLs
     * @param string $query
     * @return int
     */
    public function getCount($redirectedURLs, $query): int
    {
        $redirectedURLs = (int)$redirectedURLs;
        $qry            = 'SELECT COUNT(*) AS nCount FROM tredirect ';
        $prep           = [];
        if ($redirectedURLs === 1 || !empty($query)) {
            $qry .= 'WHERE ';
        }
        if ($redirectedURLs === 1) {
            $qry .= ' cToUrl != ""';
        }
        if (!empty($query) && $redirectedURLs === 1) {
            $qry .= ' AND ';
        }
        if (!empty($query)) {
            $qry .= 'cFromUrl LIKE :search';
            $prep = ['search' => '%' . $query . '%'];
        }

        return (int)Shop::Container()->getDB()->queryPrepared($qry, $prep, ReturnType::SINGLE_OBJECT)->nCount;
    }

    /**
     * @param int        $start
     * @param int|string $limit
     * @param string     $redirURLs
     * @param string     $sortBy
     * @param string     $dir
     * @param string     $search
     * @return mixed
     * @deprecated since 4.05 - use Redirect::getRedirects()
     */
    public function getList($start, $limit, $redirURLs, $sortBy, $dir, $search)
    {
        $where = [];
        $order = $sortBy . ' ' . $dir;
        $limit = (int)$start . ',' . (int)$limit;

        if ($search !== '') {
            $where[] = "cFromUrl LIKE '%" . $search . "%'";
        }

        if ($redirURLs === '1') {
            $where[] = "cToUrl != ''";
            if ($search !== '') {
                $where[] = "cToUrl LIKE '%" . $search . "%'";
            }
        } elseif ($redirURLs === '2') {
            $where[] = "cToUrl = ''";
        }

        return self::getRedirects(\implode(' AND ', $where), $order, $limit);
    }

    /**
     * @param int $kRedirect
     * @return array
     * @deprecated since 4.05 - use Redirect::getReferers()
     */
    public function getVerweise(int $kRedirect): array
    {
        return self::getReferers($kRedirect);
    }

    /**
     * @param string $cWhereSQL
     * @param string $cOrderSQL
     * @param string $cLimitSQL
     * @return array
     */
    public static function getRedirects($cWhereSQL = '', $cOrderSQL = '', $cLimitSQL = ''): array
    {
        $redirects = Shop::Container()->getDB()->query(
            'SELECT *
                FROM tredirect' .
            ($cWhereSQL !== '' ? ' WHERE ' . $cWhereSQL : '') .
            ($cOrderSQL !== '' ? ' ORDER BY ' . $cOrderSQL : '') .
            ($cLimitSQL !== '' ? ' LIMIT ' . $cLimitSQL : ''),
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($redirects as $redirect) {
            $redirect->kRedirect            = (int)$redirect->kRedirect;
            $redirect->nCount               = (int)$redirect->nCount;
            $redirect->cFromUrl             = Text::filterXSS($redirect->cFromUrl);
            $redirect->oRedirectReferer_arr = self::getReferers($redirect->kRedirect);

            foreach ($redirect->oRedirectReferer_arr as $referer) {
                $referer->cRefererUrl = Text::filterXSS($referer->cRefererUrl);
            }
        }

        return $redirects;
    }

    /**
     * @param string $cWhereSQL
     * @return int
     */
    public static function getRedirectCount($cWhereSQL = ''): int
    {
        return (int)Shop::Container()->getDB()->query(
            'SELECT COUNT(kRedirect) AS nCount
                FROM tredirect' .
            ($cWhereSQL !== '' ? ' WHERE ' . $cWhereSQL : ''),
            ReturnType::SINGLE_OBJECT
        )->nCount;
    }

    /**
     * @param int $kRedirect
     * @param int $nLimit
     * @return array
     */
    public static function getReferers(int $kRedirect, int $nLimit = 100): array
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT tredirectreferer.*, tbesucherbot.cName AS cBesucherBotName,
                    tbesucherbot.cUserAgent AS cBesucherBotAgent
                FROM tredirectreferer
                LEFT JOIN tbesucherbot
                    ON tredirectreferer.kBesucherBot = tbesucherbot.kBesucherBot
                    WHERE kRedirect = :kr
                ORDER BY dDate ASC
                LIMIT :lmt',
            ['kr' => $kRedirect, 'lmt' => $nLimit],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @return int
     */
    public static function getTotalRedirectCount(): int
    {
        return (int)Shop::Container()->getDB()->query(
            'SELECT COUNT(kRedirect) AS nCount
                FROM tredirect',
            ReturnType::SINGLE_OBJECT
        )->nCount;
    }

    /**
     * @param string $url - one of
     *                    * full URL (must be inside the same shop) e.g. http://www.shop.com/path/to/page
     *                    * url path e.g. /path/to/page
     *                    * path relative to the shop root url
     * @return bool
     */
    public static function checkAvailability(string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        $parsedUrl     = \parse_url($url);
        $parsedShopUrl = \parse_url(Shop::getURL() . '/');
        $fullUrlParts  = $parsedUrl;

        if (!isset($parsedUrl['host'])) {
            $fullUrlParts['scheme'] = $parsedShopUrl['scheme'];
            $fullUrlParts['host']   = $parsedShopUrl['host'];
        } elseif ($parsedUrl['host'] !== $parsedShopUrl['host']) {
            return false;
        }

        if (!isset($parsedUrl['path'])) {
            $fullUrlParts['path'] = $parsedShopUrl['path'];
        } elseif (\mb_strpos($parsedUrl['path'], $parsedShopUrl['path']) !== 0) {
            if (isset($parsedUrl['host'])) {
                return false;
            }
            $fullUrlParts['path'] = $parsedShopUrl['path'] . \ltrim($parsedUrl['path'], '/');
        }

        if (isset($parsedUrl['query'])) {
            $fullUrlParts['query'] .= '&notrack';
        } else {
            $fullUrlParts['query'] = 'notrack';
        }
        $headers = \get_headers(Text::buildUrl($fullUrlParts));
        if ($headers !== false) {
            foreach ($headers as $header) {
                if (\preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $header)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param int $kRedirect
     */
    public static function deleteRedirect(int $kRedirect): void
    {
        Shop::Container()->getDB()->delete('tredirect', 'kRedirect', $kRedirect);
        Shop::Container()->getDB()->delete('tredirectreferer', 'kRedirect', $kRedirect);
    }

    /**
     * @return int
     */
    public static function deleteUnassigned(): int
    {
        return Shop::Container()->getDB()->query(
            "DELETE tredirect, tredirectreferer
                FROM tredirect
                LEFT JOIN tredirectreferer
                    ON tredirect.kRedirect = tredirectreferer.kRedirect
                WHERE tredirect.cToUrl = ''",
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param array|null $hookInfos
     * @param bool       $forceExit
     * @return array
     */
    public static function urlNotFoundRedirect(array $hookInfos = null, bool $forceExit = false): array
    {
        $shopSubPath = \parse_url(Shop::getURL(), \PHP_URL_PATH) ?? '';
        $url         = \preg_replace('/^' . \preg_quote($shopSubPath, '/') . '/', '', $_SERVER['REQUEST_URI'] ?? '', 1);
        $redirect    = new self;
        $redirectUrl = $redirect->test($url);
        if ($redirectUrl !== false && $redirectUrl !== $url && '/' . $redirectUrl !== $url) {
            if (!\array_key_exists('scheme', \parse_url($redirectUrl))) {
                $redirectUrl = \mb_strpos($redirectUrl, '/') === 0
                    ? Shop::getURL() . $redirectUrl
                    : Shop::getURL() . '/' . $redirectUrl;
            }
            \http_response_code(301);
            \header('Location: ' . $redirectUrl);
            exit;
        }
        \http_response_code(404);

        if ($forceExit || !$redirect->isValid($url)) {
            exit;
        }
        $isFileNotFound = true;
        \executeHook(\HOOK_PAGE_NOT_FOUND_PRE_INCLUDE, [
            'isFileNotFound'  => &$isFileNotFound,
            $hookInfos['key'] => &$hookInfos['value']
        ]);
        $hookInfos['isFileNotFound'] = $isFileNotFound;

        return $hookInfos;
    }

    /**
     * @param object $productFilter
     * @param int    $count
     * @param bool   $seo
     */
    public static function doMainwordRedirect($productFilter, int $count, bool $seo = false): void
    {
        $main       = [
            'getCategory'            => [
                'cKey'   => 'kKategorie',
                'cParam' => 'k'
            ],
            'getManufacturer'        => [
                'cKey'   => 'kHersteller',
                'cParam' => 'h'
            ],
            'getSearchQuery'         => [
                'cKey'   => 'kSuchanfrage',
                'cParam' => 'l'
            ],
            'getCharacteristicValue' => [
                'cKey'   => 'kMerkmalWert',
                'cParam' => 'm'
            ],
            'getSearchSpecial'       => [
                'cKey'   => 'kKey',
                'cParam' => 'q'
            ]
        ];
        $languageID = Shop::getLanguageID();
        if ($count === 0 && Shop::getProductFilter()->getFilterCount() > 0) {
            foreach ($main as $function => $info) {
                $data = \method_exists($productFilter, $function)
                    ? $productFilter->$function()
                    : null;
                if ($data !== null && \method_exists($data, 'getValue') && $data->getValue() > 0) {
                    /** @var FilterInterface $data */
                    $url = '?' . $info['cParam'] . '=' . $data->getValue();
                    if ($seo && !empty($data->getSeo($languageID))) {
                        $url = $data->getSeo($languageID);
                    }
                    if (\mb_strlen($url) > 0) {
                        \header('Location: ' . $url, true, 301);
                        exit();
                    }
                }
            }
        }
    }
}
