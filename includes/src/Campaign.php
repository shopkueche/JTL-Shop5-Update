<?php

namespace JTL;

use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use stdClass;

/**
 * Class Campaign
 * @package JTL
 */
class Campaign
{
    /**
     * @var int
     */
    public $kKampagne;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cParameter;

    /**
     * @var string
     */
    public $cWert;

    /**
     * @var int
     */
    public $nDynamisch;

    /**
     * @var int
     */
    public $nAktiv;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var string
     */
    public $dErstellt_DE;

    /**
     * Kampagne constructor.
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
        $campaign = Shop::Container()->getDB()->query(
            "SELECT tkampagne.*, DATE_FORMAT(tkampagne.dErstellt, '%d.%m.%Y %H:%i:%s') AS dErstellt_DE
                FROM tkampagne
                WHERE tkampagne.kKampagne = " . $id,
            ReturnType::SINGLE_OBJECT
        );

        if (isset($campaign->kKampagne) && $campaign->kKampagne > 0) {
            foreach (\array_keys(\get_object_vars($campaign)) as $member) {
                $this->$member = $campaign->$member;
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        $obj             = new stdClass();
        $obj->cName      = $this->cName;
        $obj->cParameter = $this->cParameter;
        $obj->cWert      = $this->cWert;
        $obj->nDynamisch = $this->nDynamisch;
        $obj->nAktiv     = $this->nAktiv;
        $obj->dErstellt  = $this->dErstellt;
        $this->kKampagne = Shop::Container()->getDB()->insert('tkampagne', $obj);
        if (\mb_convert_case($this->dErstellt, MB_CASE_LOWER) === 'now()') {
            $this->dErstellt = \date_format(\date_create(), 'Y-m-d H:i:s');
        }
        $this->dErstellt_DE = \date_format(\date_create($this->dErstellt), 'd.m.Y H:i:s');

        return $this->kKampagne;
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj             = new stdClass();
        $obj->cName      = $this->cName;
        $obj->cParameter = $this->cParameter;
        $obj->cWert      = $this->cWert;
        $obj->nDynamisch = $this->nDynamisch;
        $obj->nAktiv     = $this->nAktiv;
        $obj->dErstellt  = $this->dErstellt;
        $obj->kKampagne  = $this->kKampagne;

        $res = Shop::Container()->getDB()->update('tkampagne', 'kKampagne', $obj->kKampagne, $obj);
        if (\mb_convert_case($this->dErstellt, MB_CASE_LOWER) === 'now()') {
            $this->dErstellt = \date_format(\date_create(), 'Y-m-d H:i:s');
        }
        $this->dErstellt_DE = \date_format(\date_create($this->dErstellt), 'd.m.Y H:i:s');

        return $res;
    }

    /**
     * @return bool
     */
    public function deleteInDB(): bool
    {
        if ($this->kKampagne <= 0) {
            return false;
        }
        Shop::Container()->getDB()->query(
            'DELETE tkampagne, tkampagnevorgang
                FROM tkampagne
                LEFT JOIN tkampagnevorgang 
                    ON tkampagnevorgang.kKampagne = tkampagne.kKampagne
                WHERE tkampagne.kKampagne = ' . (int)$this->kKampagne,
            ReturnType::AFFECTED_ROWS
        );

        return true;
    }

    /**
     * @return array|mixed
     */
    public static function getAvailable(): array
    {
        $cacheID = 'campaigns';
        if (($campaigns = Shop::Container()->getCache()->get($cacheID)) === false) {
            $campaigns = Shop::Container()->getDB()->selectAll(
                'tkampagne',
                'nAktiv',
                1,
                '*, DATE_FORMAT(dErstellt, \'%d.%m.%Y %H:%i:%s\') AS dErstellt_DE'
            );
            Shop::Container()->getCache()->set($cacheID, $campaigns, [\CACHING_GROUP_CORE]);
        }

        return $campaigns;
    }

    /**
     * @param object $campaign
     * @return bool
     */
    private static function validateStaticParams(object $campaign): bool
    {
        $full = Shop::getURL() . '/?' . $campaign->cParameter . '=' . $campaign->cWert;
        \parse_str(\parse_url($full, \PHP_URL_QUERY), $params);
        $ok = \count($params) > 0;
        foreach ($params as $param => $value) {
            if (!self::paramMatches(Request::verifyGPDataString($param), $value)) {
                $ok = false;
                break;
            }
        }

        return $ok;
    }

    /**
     * @param string $given
     * @param string $campaignValue
     * @return bool
     */
    private static function paramMatches($given, $campaignValue): bool
    {
        return \mb_convert_case($campaignValue, \MB_CASE_LOWER) === \mb_convert_case($given, \MB_CASE_LOWER);
    }

    /**
     * @former pruefeKampagnenParameter()
     */
    public static function checkCampaignParameters(): void
    {
        $campaigns = self::getAvailable();
        if (empty($_SESSION['oBesucher']->kBesucher) || \count($campaigns) === 0) {
            return;
        }
        $db  = Shop::Container()->getDB();
        $hit = false;
        foreach ($campaigns as $campaign) {
            // Wurde für die aktuelle Kampagne der Parameter via GET oder POST uebergeben?
            $given = Request::verifyGPDataString($campaign->cParameter);
            if ($given !== '' && ((int)$campaign->nDynamisch === 1 || self::validateStaticParams($campaign))) {
                $hit      = true;
                $referrer = Visitor::getReferer();
                // wurde der HIT für diesen Besucher schon gezaehlt?
                $event = $db->select(
                    'tkampagnevorgang',
                    ['kKampagneDef', 'kKampagne', 'kKey', 'cCustomData'],
                    [
                        \KAMPAGNE_DEF_HIT,
                        (int)$campaign->kKampagne,
                        (int)$_SESSION['oBesucher']->kBesucher,
                        Text::filterXSS($_SERVER['REQUEST_URI']) . ';' . $referrer
                    ]
                );

                if (!isset($event->kKampagneVorgang)) {
                    $event               = new stdClass();
                    $event->kKampagne    = $campaign->kKampagne;
                    $event->kKampagneDef = \KAMPAGNE_DEF_HIT;
                    $event->kKey         = $_SESSION['oBesucher']->kBesucher;
                    $event->fWert        = 1.0;
                    $event->cParamWert   = $given;
                    $event->cCustomData  = Text::filterXSS($_SERVER['REQUEST_URI']) . ';' . $referrer;
                    if ((int)$campaign->nDynamisch === 0) {
                        $event->cParamWert = $campaign->cWert;
                    }
                    $event->dErstellt = 'NOW()';
                    $db->insert('tkampagnevorgang', $event);
                    $_SESSION['Kampagnenbesucher']        = $campaign;
                    $_SESSION['Kampagnenbesucher']->cWert = $event->cParamWert;
                    break;
                }
            }

            if (!$hit && \mb_strpos($_SERVER['HTTP_REFERER'] ?? '', '.google.') !== false) {
                // Besucher kommt von Google und hat vorher keine Kampagne getroffen
                $event = $db->select(
                    'tkampagnevorgang',
                    ['kKampagneDef', 'kKampagne', 'kKey'],
                    [\KAMPAGNE_DEF_HIT, \KAMPAGNE_INTERN_GOOGLE, (int)$_SESSION['oBesucher']->kBesucher]
                );

                if (!isset($event->kKampagneVorgang)) {
                    $campaign            = new self(\KAMPAGNE_INTERN_GOOGLE);
                    $event               = new stdClass();
                    $event->kKampagne    = \KAMPAGNE_INTERN_GOOGLE;
                    $event->kKampagneDef = \KAMPAGNE_DEF_HIT;
                    $event->kKey         = $_SESSION['oBesucher']->kBesucher;
                    $event->fWert        = 1.0;
                    $event->cParamWert   = $campaign->cWert;
                    $event->dErstellt    = 'NOW()';
                    if ((int)$campaign->nDynamisch === 1) {
                        $event->cParamWert = $given;
                    }
                    $db->insert('tkampagnevorgang', $event);
                    $_SESSION['Kampagnenbesucher']        = $campaign;
                    $_SESSION['Kampagnenbesucher']->cWert = $event->cParamWert;
                }
            }
        }
    }

    /**
     * @param int         $id
     * @param int         $kKey
     * @param float       $fWert
     * @param string|null $customData
     * @return int
     * @former setzeKampagnenVorgang()
     */
    public static function setCampaignAction(int $id, int $kKey, $fWert, $customData = null): int
    {
        if ($id > 0 && $kKey > 0 && $fWert > 0 && isset($_SESSION['Kampagnenbesucher'])) {
            $event               = new stdClass();
            $event->kKampagne    = $_SESSION['Kampagnenbesucher']->kKampagne;
            $event->kKampagneDef = $id;
            $event->kKey         = $kKey;
            $event->fWert        = $fWert;
            $event->cParamWert   = $_SESSION['Kampagnenbesucher']->cWert;
            $event->dErstellt    = 'NOW()';

            if ($customData !== null) {
                $event->cCustomData = \mb_substr($customData, 0, 255);
            }

            return Shop::Container()->getDB()->insert('tkampagnevorgang', $event);
        }

        return 0;
    }
}
