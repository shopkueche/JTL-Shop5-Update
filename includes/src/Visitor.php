<?php

namespace JTL;

use DateTime;
use JTL\Crawler;
use JTL\DB\ReturnType;
use JTL\GeneralDataProtection\IpAnonymizer;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Session\Frontend;
use stdClass;

/**
 * Class Visitor
 * @package JTL
 * @since 5.0.0
 */
class Visitor
{
    /**
     * @since 5.0.0
     */
    public static function generateData(): void
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $botID     = self::isSpider($userAgent);
        if ($botID > 0) {
            Shop::Container()->getDB()->queryPrepared(
                'UPDATE tbesucherbot SET dZeit = NOW() WHERE kBesucherBot = :_kBesucherBot',
                ['_kBesucherBot' => $botID],
                ReturnType::AFFECTED_ROWS
            );
        }
        self::archive();
        $visitor = self::dbLookup($userAgent, Request::getRealIP());
        if ($visitor === null) {
            if (isset($_SESSION['oBesucher'])) {
                $visitor = self::updateVisitorObject($_SESSION['oBesucher'], 0, $userAgent, $botID);
            } else {
                // create a new visitor-object
                $visitor = self::createVisitorObject($userAgent, $botID);
            }
            // get back the new ID of that visitor (and write it back into the session)
            $visitor->kBesucher = self::dbInsert($visitor);
            // store search-string from search-engine too
            if ($visitor->cReferer !== '') {
                self::analyzeReferer($visitor->kBesucher, $visitor->cReferer);
            }
            // allways increment the visitor-counter (if no bot)
            Shop::Container()->getDB()->query(
                'UPDATE tbesucherzaehler SET nZaehler = nZaehler + 1',
                ReturnType::AFFECTED_ROWS
            );
        } else {
            $visitor->kBesucher    = (int)$visitor->kBesucher;
            $visitor->kKunde       = (int)$visitor->kKunde;
            $visitor->kBestellung  = (int)$visitor->kBestellung;
            $visitor->kBesucherBot = (int)$visitor->kBesucherBot;
            // prevent counting internal redirects by counting only the next request above 3 seconds
            $diff = (new DateTime())->getTimestamp() - (new DateTime($visitor->dLetzteAktivitaet))->getTimestamp();
            if ($diff > 2) {
                $visitor = self::updateVisitorObject($visitor, $visitor->kBesucher, $userAgent, $botID);
                self::dbUpdate($visitor, $visitor->kBesucher);
            } else {
                // time-diff is to low! so we do nothing but update this "last-action"-time in the session
                $visitor->dLetzteAktivitaet = (new DateTime())->format('Y-m-d H:i:s');
            }
        }
        $_SESSION['oBesucher'] = $visitor;
    }

    /**
     * Besucher nach 3 Std in Besucherarchiv verschieben
     *
     * @former archiviereBesucher()
     * @since  5.0.0
     */
    public static function archive(): void
    {
        $interval = 3;
        Shop::Container()->getDB()->queryPrepared(
            'INSERT INTO tbesucherarchiv
            (kBesucher, cIP, kKunde, kBestellung, cReferer, cEinstiegsseite, cBrowser,
              cAusstiegsseite, nBesuchsdauer, kBesucherBot, dZeit)
            SELECT kBesucher, cIP, kKunde, kBestellung, cReferer, cEinstiegsseite, cBrowser, cAusstiegsseite,
            (UNIX_TIMESTAMP(dLetzteAktivitaet) - UNIX_TIMESTAMP(dZeit)) AS nBesuchsdauer, kBesucherBot, dZeit
              FROM tbesucher
              WHERE dLetzteAktivitaet <= DATE_SUB(NOW(), INTERVAL :interval HOUR)',
            ['interval' => $interval],
            ReturnType::AFFECTED_ROWS
        );
        Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tbesucher
                WHERE dLetzteAktivitaet <= DATE_SUB(NOW(), INTERVAL :interval HOUR)',
            ['interval' => $interval],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param string $userAgent
     * @param string $ip
     * @return stdClass|null
     * @former dbLookupVisitor()
     * @since  5.0.0
     */
    public static function dbLookup(string $userAgent, string $ip): ?stdClass
    {
        return Shop::Container()->getDB()->select('tbesucher', 'cSessID', \session_id())
            ?? Shop::Container()->getDB()->select('tbesucher', 'cID', \md5($userAgent . $ip));
    }

    /**
     * @param stdClass $vis
     * @param int      $visitorID
     * @param string   $userAgent
     * @param int      $botID
     * @return object
     * @since 5.0.0
     */
    public static function updateVisitorObject(stdClass $vis, int $visitorID, string $userAgent, int $botID)
    {
        $vis->kBesucher         = $visitorID;
        $vis->cIP               = (new IpAnonymizer(Request::getRealIP()))->anonymize();
        $vis->cSessID           = \session_id();
        $vis->cID               = \md5($userAgent . Request::getRealIP());
        $vis->kKunde            = Frontend::getCustomer()->getID();
        $vis->kBestellung       = $vis->kKunde > 0 ? self::refreshCustomerOrderId((int)$vis->kKunde) : 0;
        $vis->cReferer          = self::getReferer();
        $vis->cUserAgent        = Text::filterXSS($_SERVER['HTTP_USER_AGENT'] ?? '');
        $vis->cBrowser          = self::getBrowser();
        $vis->cAusstiegsseite   = $_SERVER['REQUEST_URI'] ?? '';
        $vis->dLetzteAktivitaet = (new DateTime())->format('Y-m-d H:i:s');
        $vis->kBesucherBot      = $botID;

        return $vis;
    }

    /**
     * @param string $userAgent
     * @param int    $botID
     * @return stdClass
     * @since 5.0.0
     */
    public static function createVisitorObject(string $userAgent, int $botID): stdClass
    {
        $vis                    = new stdClass();
        $vis->kBesucher         = 0;
        $vis->cIP               = (new IpAnonymizer(Request::getRealIP()))->anonymize();
        $vis->cSessID           = \session_id();
        $vis->cID               = \md5($userAgent . Request::getRealIP());
        $vis->kKunde            = Frontend::getCustomer()->getID();
        $vis->kBestellung       = $vis->kKunde > 0 ? self::refreshCustomerOrderId((int)$vis->kKunde) : 0;
        $vis->cEinstiegsseite   = $_SERVER['REQUEST_URI'] ?? '';
        $vis->cReferer          = self::getReferer();
        $vis->cUserAgent        = Text::filterXSS($_SERVER['HTTP_USER_AGENT'] ?? '');
        $vis->cBrowser          = self::getBrowser();
        $vis->cAusstiegsseite   = $vis->cEinstiegsseite;
        $vis->dLetzteAktivitaet = (new DateTime())->format('Y-m-d H:i:s');
        $vis->dZeit             = (new DateTime())->format('Y-m-d H:i:s');
        $vis->kBesucherBot      = $botID;

        return $vis;
    }

    /**
     * @param stdClass $visitor
     * @return int
     * @since since 5.0.0
     */
    public static function dbInsert(stdClass $visitor): int
    {
        return Shop::Container()->getDB()->insert('tbesucher', $visitor);
    }

    /**
     * @param stdClass $visitor
     * @param int      $visitorID
     * @return int
     * @since since 5.0.0
     */
    public static function dbUpdate(stdClass $visitor, int $visitorID): int
    {
        return Shop::Container()->getDB()->update('tbesucher', 'kBesucher', $visitorID, $visitor);
    }

    /**
     * @param int $customerID
     * @return int
     * @since 5.0.0
     */
    public static function refreshCustomerOrderId(int $customerID): int
    {
        $data = Shop::Container()->getDB()->queryPrepared(
            'SELECT `kBestellung`
                FROM `tbestellung`
                WHERE `kKunde` = :cid
                ORDER BY `dErstellt` DESC LIMIT 1',
            ['cid' => $customerID],
            ReturnType::SINGLE_OBJECT
        );

        return (int)($data->kBestellung ?? 0);
    }

    /**
     * @return string
     * @former gibBrowser()
     * @since  5.0.0
     */
    public static function getBrowser(): string
    {
        $agent  = \mb_convert_case($_SERVER['HTTP_USER_AGENT'] ?? '', \MB_CASE_LOWER);
        $mobile = '';
        if (\mb_stripos($agent, 'iphone') !== false
            || \mb_stripos($agent, 'ipad') !== false
            || \mb_stripos($agent, 'ipod') !== false
            || \mb_stripos($agent, 'android') !== false
            || \mb_stripos($agent, 'opera mobi') !== false
            || \mb_stripos($agent, 'blackberry') !== false
            || \mb_stripos($agent, 'playbook') !== false
            || \mb_stripos($agent, 'kindle') !== false
            || \mb_stripos($agent, 'windows phone') !== false
        ) {
            $mobile = '/Mobile';
        }
        if (\mb_strpos($agent, 'msie') !== false) {
            return 'Internet Explorer ' . (int)\mb_substr($agent, \mb_strpos($agent, 'msie') + 4) . $mobile;
        }
        if (\mb_strpos($agent, 'opera') !== false || \mb_stripos($agent, 'opr') !== false) {
            return 'Opera' . $mobile;
        }
        if (\mb_stripos($agent, 'vivaldi') !== false) {
            return 'Vivaldi' . $mobile;
        }
        if (\mb_strpos($agent, 'safari') !== false && \mb_strpos($agent, 'chrome') === false) {
            return 'Safari' . $mobile;
        }
        if (\mb_strpos($agent, 'firefox') !== false) {
            return 'Firefox' . $mobile;
        }
        if (\mb_strpos($agent, 'chrome') !== false) {
            return 'Chrome' . $mobile;
        }

        return 'Sonstige' . $mobile;
    }

    /**
     * @return string
     * @fomer gibReferer()
     * @since 5.0.0
     */
    public static function getReferer(): string
    {
        if (empty($_SERVER['HTTP_REFERER'])) {
            return '';
        }

        return Text::filterXSS(\mb_convert_case(\explode('/', $_SERVER['HTTP_REFERER'])[2], \MB_CASE_LOWER));
    }

    /**
     * @return string
     * @former gibBot()
     * @since  5.0.0
     */
    public static function getBot(): string
    {
        $agent = \mb_convert_case($_SERVER['HTTP_USER_AGENT'] ?? '', \MB_CASE_LOWER);
        if (\mb_strpos($agent, 'googlebot') !== false) {
            return 'Google';
        }
        if (\mb_strpos($agent, 'bingbot') !== false) {
            return 'Bing';
        }
        if (\mb_strpos($agent, 'inktomi.com') !== false) {
            return 'Inktomi';
        }
        if (\mb_strpos($agent, 'yahoo! slurp') !== false) {
            return 'Yahoo!';
        }
        if (\mb_strpos($agent, 'msnbot') !== false) {
            return 'MSN';
        }
        if (\mb_strpos($agent, 'teoma') !== false) {
            return 'Teoma';
        }
        if (\mb_strpos($agent, 'crawler') !== false) {
            return 'Crawler';
        }
        if (\mb_strpos($agent, 'scooter') !== false) {
            return 'Scooter';
        }
        if (\mb_strpos($agent, 'fireball') !== false) {
            return 'Fireball';
        }
        if (\mb_strpos($agent, 'ask jeeves') !== false) {
            return 'Ask';
        }

        return '';
    }

    /**
     * @param int    $visitorID
     * @param string $referer
     * @former werteRefererAus()
     * @since  5.0.0
     */
    public static function analyzeReferer(int $visitorID, $referer): void
    {
        $ref             = $_SERVER['HTTP_REFERER'] ?? '';
        $term            = new stdClass();
        $term->kBesucher = $visitorID;
        $term->cRohdaten = \mb_substr(Text::filterXSS($_SERVER['HTTP_REFERER']), 0, 255);
        $param           = '';
        if (\mb_strpos($referer, '.google.') !== false
            || \mb_strpos($referer, 'suche.t-online.') !== false
            || \mb_strpos($referer, 'search.live.') !== false
            || \mb_strpos($referer, '.aol.') !== false
            || \mb_strpos($referer, '.aolsvc.') !== false
            || \mb_strpos($referer, '.ask.') !== false
            || \mb_strpos($referer, 'search.icq.') !== false
            || \mb_strpos($referer, 'search.msn.') !== false
            || \mb_strpos($referer, '.exalead.') !== false
        ) {
            $param = 'q';
        } elseif (\mb_strpos($referer, 'suche.web') !== false) {
            $param = 'su';
        } elseif (\mb_strpos($referer, 'suche.aolsvc') !== false) {
            $param = 'query';
        } elseif (\mb_strpos($referer, 'search.yahoo') !== false) {
            $param = 'p';
        } elseif (\mb_strpos($referer, 'search.ebay') !== false) {
            $param = 'satitle';
        }
        if ($param !== '') {
            \preg_match("/(\?$param|&$param)=[^&]+/i", $ref, $treffer);
            $term->cSuchanfrage = isset($treffer[0]) ? \urldecode(\mb_substr($treffer[0], 3)) : null;
            if ($term->cSuchanfrage) {
                Shop::Container()->getDB()->insert('tbesuchersuchausdruecke', $term);
            }
        }
    }

    /**
     * @param string $referer
     * @return int
     * @former istSuchmaschine()
     * @since  5.0.0
     */
    public static function isSearchEngine($referer): int
    {
        if (!$referer) {
            return 0;
        }
        if (\mb_strpos($referer, '.google.') !== false
            || \mb_strpos($referer, '.bing.') !== false
            || \mb_strpos($referer, 'suche.') !== false
            || \mb_strpos($referer, 'search.') !== false
            || \mb_strpos($referer, '.yahoo.') !== false
            || \mb_strpos($referer, '.fireball.') !== false
            || \mb_strpos($referer, '.seekport.') !== false
            || \mb_strpos($referer, '.keywordspy.') !== false
            || \mb_strpos($referer, '.hotfrog.') !== false
            || \mb_strpos($referer, '.altavista.') !== false
            || \mb_strpos($referer, '.ask.') !== false
        ) {
            return 1;
        }

        return 0;
    }

    /**
     * @param string $userAgent
     * @return int
     * @former istSpider()
     * @since  5.0.0
     */
    public static function isSpider(string $userAgent): int
    {
        $controller = new Crawler\Controller(Shop::Container()->getDB(), Shop::Container()->getCache());
        $bot        = $controller->getByUserAgent($userAgent);

        return (int)($bot->kBesucherBot ?? 0);
    }

    /**
     * @return array
     */
    public static function getSpiders(): array
    {
        $controller = new Crawler\Controller(Shop::Container()->getDB(), Shop::Container()->getCache());

        return $controller->getAllCrawlers();
    }

    /**
     * @param string $userAgent
     * @return bool|int
     */
    private static function isMobile(string $userAgent)
    {
        return \preg_match(
            '/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile' .
                '|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker' .
                '|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',
            $userAgent,
            $matches
        )
            || \preg_match(
                '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)' .
                '|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )' .
                '|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa' .
                '|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob' .
                '|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)' .
                '|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)' .
                '|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)' .
                '|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)' .
                '|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])' .
                '|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)' .
                '|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)' .
                '|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1' .
                '|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio' .
                '|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa' .
                '(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)' .
                '|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)' .
                '|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)' .
                '|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)' .
                '|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',
                \mb_substr($userAgent, 0, 4),
                $matches
            );
    }

    /**
     * @param stdClass $browser
     * @param string   $userAgent
     * @return stdClass
     */
    private static function getBrowserData(stdClass $browser, string $userAgent): stdClass
    {
        if ($userAgent === '') {
            return $browser;
        }
        if (\preg_match('/MSIE/i', $userAgent) && !\preg_match('/Opera/i', $userAgent)) {
            $browser->nType    = \BROWSER_MSIE;
            $browser->cName    = 'Internet Explorer';
            $browser->cBrowser = 'msie';
        } elseif (\preg_match('/Firefox/i', $userAgent)) {
            $browser->nType    = \BROWSER_FIREFOX;
            $browser->cName    = 'Mozilla Firefox';
            $browser->cBrowser = 'firefox';
        } elseif (\preg_match('/Chrome/i', $userAgent)) {
            $browser->nType    = \BROWSER_CHROME;
            $browser->cName    = 'Google Chrome';
            $browser->cBrowser = 'chrome';
        } elseif (\preg_match('/Safari/i', $userAgent)) {
            $browser->nType = \BROWSER_SAFARI;
            if (\preg_match('/iPhone/i', $userAgent)) {
                $browser->cName    = 'Apple iPhone';
                $browser->cBrowser = 'iphone';
            } elseif (\preg_match('/iPad/i', $userAgent)) {
                $browser->cName    = 'Apple iPad';
                $browser->cBrowser = 'ipad';
            } elseif (\preg_match('/iPod/i', $userAgent)) {
                $browser->cName    = 'Apple iPod';
                $browser->cBrowser = 'ipod';
            } else {
                $browser->cName    = 'Apple Safari';
                $browser->cBrowser = 'safari';
            }
        } elseif (\preg_match('/Opera/i', $userAgent)) {
            $browser->nType = \BROWSER_OPERA;
            if (\preg_match('/Opera Mini/i', $userAgent)) {
                $browser->cName    = 'Opera Mini';
                $browser->cBrowser = 'opera_mini';
            } else {
                $browser->cName    = 'Opera';
                $browser->cBrowser = 'opera';
            }
        }

        return $browser;
    }

    /**
     * @param null|string $userAgent
     * @return stdClass
     */
    public static function getBrowserForUserAgent($userAgent = null): stdClass
    {
        $userAgent          = $userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? '';
        $browser            = new stdClass();
        $browser->nType     = 0;
        $browser->bMobile   = false;
        $browser->cName     = 'Unknown';
        $browser->cBrowser  = 'unknown';
        $browser->cPlatform = 'unknown';
        $browser->cVersion  = '0';
        $browser->cAgent    = $userAgent;
        $browser->bMobile   = self::isMobile($browser->cAgent);
        if (\preg_match('/linux/i', $userAgent)) {
            $browser->cPlatform = 'linux';
        } elseif (\preg_match('/macintosh|mac os x/i', $userAgent)) {
            $browser->cPlatform = 'mac';
        } elseif (\preg_match('/windows|win32/i', $userAgent)) {
            $browser->cPlatform = \preg_match('/windows mobile|wce/i', $userAgent)
                ? 'mobile'
                : 'windows';
        }
        $browser = self::getBrowserData($browser, $userAgent);
        $known   = ['version', 'other', 'mobile', $browser->cBrowser];
        $pattern = '/(?<browser>' . \implode('|', $known) . ')[\/ ]+(?<version>[0-9.|a-zA-Z.]*)/i';
        \preg_match_all($pattern, $userAgent, $browserMatches);
        if (\count($browserMatches['browser']) !== 1) {
            $browser->cVersion = '0';
            if (isset($browserMatches['version'][0])
                && \mb_strripos($userAgent, 'Version') < \mb_strripos($userAgent, $browser->cBrowser)
            ) {
                $browser->cVersion = $browserMatches['version'][0];
            } elseif (isset($browserMatches['version'][1])) {
                $browser->cVersion = $browserMatches['version'][1];
            }
        } else {
            $browser->cVersion = $browserMatches['version'][0];
        }
        if (\mb_strlen($browser->cVersion) === 0) {
            $browser->cVersion = '0';
        }

        return $browser;
    }
}
