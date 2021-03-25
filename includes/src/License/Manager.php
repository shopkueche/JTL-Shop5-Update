<?php declare(strict_types=1);

namespace JTL\License;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use JTL\Backend\AuthToken;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\License\Struct\ExsLicense;
use JTL\Shop;
use stdClass;

/**
 * Class Manager
 * @package JTL\License
 */
class Manager
{
    private const MAX_REQUESTS = 10;

    private const CHECK_INTERVAL_HOURS = 4;

    private const API_LIVE_URL = 'https://checkout.jtl-software.com/v1/licenses';

    private const API_DEV_URL = 'https://checkout-stage.jtl-software.com/v1/licenses';

    /**
     * @var string
     */
    private $domain;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var Client
     */
    private $client;

    /**
     * Manager constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->db     = $db;
        $this->cache  = $cache;
        $this->client = new Client();
        $this->domain = \parse_url(\URL_SHOP)['host'];
    }

    /**
     * @return bool - true if data should be updated
     */
    private function checkUpdate(): bool
    {
        return ($lastItem = $this->getLicenseData()) === null
            ? true
            : (\time() - \strtotime($lastItem->timestamp)) / (60 * 60) > self::CHECK_INTERVAL_HOURS;
    }

    /**
     * @param string $url
     * @return string
     * @throws GuzzleException
     * @throws ClientException
     */
    public function setBinding(string $url): string
    {
        $res = $this->client->request(
            'POST',
            $url,
            [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . AuthToken::getInstance($this->db)->get()
                ],
                'verify'  => true,
                'body'    => \json_encode((object)['domain' => $this->domain])
            ]
        );

        return (string)$res->getBody();
    }

    /**
     * @param string $url
     * @return string
     * @throws GuzzleException
     * @throws ClientException
     */
    public function createLicense(string $url): string
    {
        $res = $this->client->request(
            'POST',
            $url,
            [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . AuthToken::getInstance($this->db)->get()
                ],
                'verify'  => true,
                'body'    => \json_encode((object)['domain' => $this->domain])
            ]
        );

        return (string)$res->getBody();
    }

    /**
     * @param string $url
     * @return string
     * @throws GuzzleException
     * @throws ClientException
     */
    public function clearBinding(string $url): string
    {
        $res = $this->client->request(
            'GET',
            $url,
            [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . AuthToken::getInstance($this->db)->get()
                ],
                'verify'  => true,
                'body'    => \json_encode((object)['domain' => $this->domain])
            ]
        );

        return (string)$res->getBody();
    }

    /**
     * @param string $url
     * @param string $exsID
     * @param string $key
     * @return string
     * @throws GuzzleException
     * @throws ClientException
     */
    public function extendUpgrade(string $url, string $exsID, string $key): string
    {
        $res = $this->client->request(
            'POST',
            $url,
            [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . AuthToken::getInstance($this->db)->get()
                ],
                'verify'  => true,
                'body'    => \json_encode((object)[
                    'exsid'         => $exsID,
                    'reference'     => (object)[
                        'license' => $key,
                        'domain'  => $this->domain
                    ],
                    'redirect_urls' => (object)[
                        'return_url' => Shop::getAdminURL() . '/licenses.php?extend=success',
                        'cancel_url' => Shop::getAdminURL() . '/licenses.php?extend=fail'
                    ],
                ])
            ]
        );

        return (string)$res->getBody();
    }

    /**
     * @param bool  $force
     * @param array $installedExtensions
     * @return int
     * @throws GuzzleException
     */
    public function update(bool $force = false, array $installedExtensions = []): int
    {
        if (!$force && !$this->checkUpdate()) {
            return 0;
        }
        $res = $this->client->request(
            'POST',
            \EXS_LIVE === true ? self::API_LIVE_URL : self::API_DEV_URL,
            [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . AuthToken::getInstance($this->db)->get()
                ],
                'verify'  => true,
                'body'    => \json_encode((object)['shop' => [
                    'domain'    => $this->domain,
                    'version'   => \APPLICATION_VERSION,
                ], 'extensions' => $installedExtensions])
            ]
        );
        $this->housekeeping();
        $this->cache->flushTags([\CACHING_GROUP_LICENSES]);

        return $this->db->insert(
            'licenses',
            (object)['data' => (string)$res->getBody(), 'returnCode' => $res->getStatusCode()]
        );
    }

    /**
     * @return stdClass|null
     */
    public function getLicenseData(): ?stdClass
    {
        $data = $this->db->query(
            'SELECT * FROM licenses
                WHERE returnCode = 200
                ORDER BY id DESC
                LIMIT 1',
            ReturnType::SINGLE_OBJECT
        );
        if ($data === false) {
            return null;
        }
        $obj             = \json_decode($data->data, false);
        $obj->timestamp  = $data->timestamp;
        $obj->returnCode = $data->returnCode;

        return $obj === null || !isset($obj->extensions) ? null : $obj;
    }

    /**
     * @param string $itemID
     * @return ExsLicense|null
     */
    public function getLicenseByItemID(string $itemID): ?ExsLicense
    {
        return (new Mapper($this))->getCollection()->getBound()->getForItemID($itemID);
    }

    /**
     * @param string $exsID
     * @return ExsLicense|null
     */
    public function getLicenseByExsID(string $exsID): ?ExsLicense
    {
        return (new Mapper($this))->getCollection()->getBound()->getForExsID($exsID);
    }

    /**
     * @param string $key
     * @return ExsLicense|null
     */
    public function getLicenseByLicenseKey(string $key): ?ExsLicense
    {
        return (new Mapper($this))->getCollection()->getBound()->getForLicenseKey($key);
    }

    /**
     * @return int
     */
    private function housekeeping(): int
    {
        return $this->db->queryPrepared(
            'DELETE a 
                FROM licenses AS a 
                JOIN ( 
                    SELECT id 
                        FROM licenses 
                        ORDER BY timestamp DESC 
                        LIMIT 99999 OFFSET :max) AS b
                ON a.id = b.id',
            ['max' => self::MAX_REQUESTS],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @return JTLCacheInterface
     */
    public function getCache(): JTLCacheInterface
    {
        return $this->cache;
    }

    /**
     * @param JTLCacheInterface $cache
     */
    public function setCache(JTLCacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }
}
