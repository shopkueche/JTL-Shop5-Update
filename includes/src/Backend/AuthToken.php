<?php declare(strict_types=1);

namespace JTL\Backend;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\xtea\XTEA;

/**
 * Class AuthToken
 * @package JTL\Backend
 */
class AuthToken
{
    private const AUTH_SERVER = 'https://oauth2.api.jtl-software.com/link';

    /** @var self */
    private static $instance;

    /** @var string */
    private $authCode;

    /** @var string */
    private $token;

    /** @var string */
    private $hash;

    /** @var string */
    private $verified;

    /** @var DbInterface */
    private $db;

    /**
     * AuthToken constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
        $this->load();
        self::$instance = $this;
    }

    /**
     * @param DbInterface $db
     * @return static
     */
    public static function getInstance(DbInterface $db): self
    {
        return self::$instance ?? new self($db);
    }

    /**
     * @return void
     */
    private function load(): void
    {
        $this->authCode = null;
        $this->token    = null;
        $this->hash     = null;
        $this->verified = null;

        $token = $this->db->query(
            'SELECT tstoreauth.auth_code, tstoreauth.access_token,
                tadminlogin.cPass AS hash, tstoreauth.verified
                FROM tstoreauth
                INNER JOIN tadminlogin 
                    ON tadminlogin.kAdminlogin = tstoreauth.owner
                LIMIT 1',
            ReturnType::SINGLE_OBJECT
        );
        if ($token) {
            $this->authCode = $token->auth_code;
            $this->token    = $token->access_token;
            $this->hash     = \sha1($token->hash);
            $this->verified = $token->verified;
        }
    }

    /**
     * @return string
     */
    private function salt(): string
    {
        return \BLOWFISH_KEY . '.' . $this->hash ?? '';
    }

    /**
     * @return XTEA
     */
    private function getCrypto(): XTEA
    {
        return new XTEA(\sha1(\BLOWFISH_KEY . '.' . $this->salt()));
    }

    /**
     * @param string $authCode
     * @param string $token
     */
    public function set(string $authCode, string $token): void
    {
        $this->db->queryPrepared(
            'UPDATE tstoreauth SET
                access_token = :token,
                verified     = :verified,
                created_at   = NOW()
                WHERE auth_code = :authCode',
            [
                'token'    => $this->getCrypto()->encrypt($token),
                'verified' => \sha1($token),
                'authCode' => $authCode,
            ],
            ReturnType::DEFAULT
        );
        $this->load();
    }

    /**
     * @return bool
     */
    public static function isEditable(): bool
    {
        $user = Shop::Container()->getAdminAccount()->account();

        return $user && $user->oGroup->kAdminlogingruppe === \ADMINGROUP;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $token = \rtrim($this->getCrypto()->decrypt($this->token ?? ''));

        return ($token !== '') && (\sha1($token) === $this->verified);
    }

    /**
     * @return string
     */
    public function get(): string
    {
        return $this->isValid() ? \rtrim($this->getCrypto()->decrypt($this->token ?? '')) : '';
    }

    /**
     * @return void
     */
    public function revoke(): void
    {
        if (!self::isEditable()) {
            return;
        }

        $this->db->query('TRUNCATE TABLE tstoreauth', ReturnType::DEFAULT);
        $this->load();
    }

    /**
     * @param string $authCode
     */
    public function reset(string $authCode): void
    {
        if (!self::isEditable()) {
            return;
        }

        $owner = Shop::Container()->getAdminAccount()->account()->kAdminlogin ?? 0;

        if ($owner > 0) {
            $this->db->queryPrepared(
                "INSERT INTO tstoreauth (owner, auth_code, access_token, created_at, verified)
                    VALUES (:owner, :authCode, '', NOW(), '')
                    ON DUPLICATE KEY UPDATE
                        auth_code    = :authCode,
                        access_token = '',
                        verified     = '',
                        created_at = NOW()",
                [
                    'owner'    => $owner,
                    'authCode' => $authCode,
                ],
                ReturnType::DEFAULT
            );
            $this->db->queryPrepared(
                'DELETE FROM tstoreauth WHERE owner != :owner',
                ['owner' => $owner],
                ReturnType::DEFAULT
            );
            $this->load();
        }
    }

    /**
     * @param string $authCode
     * @param string $returnURL
     */
    public function requestToken(string $authCode, string $returnURL): void
    {
        if (!self::isEditable()) {
            return;
        }
        $this->reset($authCode);
        \header('Location: ' . self::AUTH_SERVER . '?' . \http_build_query([
                'url'  => $returnURL,
                'code' => $authCode
            ], '', '&'));

        exit;
    }

    /**
     * @return void
     */
    public function responseToken(): void
    {
        $authCode = (string)Request::postVar('code');
        $token    = (string)Request::postVar('token');
        $logger   = null;
        try {
            $logger = Shop::Container()->getLogService();
        } catch (ServiceNotFoundException | CircularReferenceException $e) {
            $logger = null;
        }

        if ($authCode === null || $authCode !== $this->authCode) {
            $logger !== null && $logger->error('Call responseToken with invalid authcode!');
            \http_response_code(404);
            exit;
        }

        if ($token === null || $token === '') {
            \http_response_code(200);
            exit;
        }

        $this->set($authCode, $token);
        \http_response_code($this->isValid() ? 200 : 404);
        exit;
    }
}
