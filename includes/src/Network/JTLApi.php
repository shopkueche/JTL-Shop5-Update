<?php

namespace JTL\Network;

use JTL\Helpers\Request;
use JTL\Nice;
use JTLShop\SemVer\Version;
use stdClass;

/**
 * Class JTLApi
 * @package JTL\Network
 */
final class JTLApi
{
    public const URI = 'https://api.jtl-software.de/shop';

    public const URI_VERSION = 'https://api.jtl-shop.de';

    /**
     * @var array
     */
    private $session;

    /**
     * @var Nice
     */
    private $nice;

    /**
     * JTLApi constructor.
     *
     * @param array $session
     * @param Nice  $nice
     */
    public function __construct(array &$session, Nice $nice)
    {
        $this->session = &$session;
        $this->nice    = $nice;
    }

    /**
     * @return stdClass|null
     */
    public function getSubscription(): ?stdClass
    {
        if (!isset($this->session['rs']['subscription'])) {
            $uri          = self::URI . '/check/subscription';
            $subscription = $this->call($uri, [
                'key'    => $this->nice->getAPIKey(),
                'domain' => $this->nice->getDomain(),
            ]);

            $this->session['rs']['subscription'] = (isset($subscription->kShop) && $subscription->kShop > 0)
                ? $subscription
                : null;
        }

        return $this->session['rs']['subscription'];
    }

    /**
     * @return array|null
     */
    public function getAvailableVersions()
    {
        if (!isset($this->session['rs']['versions'])) {
            $this->session['rs']['versions'] = $this->call(self::URI_VERSION . '/versions');
        }

        return $this->session['rs']['versions'];
    }

    /**
     * @return Version
     */
    public function getLatestVersion(): Version
    {
        $shopVersion       = \APPLICATION_VERSION;
        $parsedShopVersion = Version::parse($shopVersion);
        $oVersions         = $this->getAvailableVersions();

        $oNewerVersions = \array_filter((array)$oVersions, static function ($v) use ($parsedShopVersion) {
            return Version::parse($v->reference)->greaterThan($parsedShopVersion);
        });

        if (\count($oNewerVersions) > 0) {
            $reverseVersionsArr = \array_reverse($oNewerVersions);
            $version            = \end($reverseVersionsArr);
        } else {
            $oVersion = \end($oVersions);
            $version  = Version::parse($oVersion->reference);
        }

        return $version;
    }

    /**
     * @return bool
     */
    public function hasNewerVersion(): bool
    {
        return \APPLICATION_BUILD_SHA === '#DEV#'
            ? false
            : $this->getLatestVersion()->greaterThan(Version::parse(\APPLICATION_VERSION));
    }

    /**
     * @param string $uri
     * @param null   $data
     * @return string|bool|null
     */
    private function call($uri, $data = null)
    {
        $content = Request::http_get_contents($uri, 10, $data);

        return empty($content) ? null : \json_decode($content);
    }
}
