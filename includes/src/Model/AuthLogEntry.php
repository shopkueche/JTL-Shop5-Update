<?php declare(strict_types=1);

namespace JTL\Model;

use JTL\Backend\AdminLoginStatus;

/**
 * Class AuthLogEntry
 * @package JTL\Model
 */
class AuthLogEntry
{
    /**
     * @var string
     */
    private $ip = '0.0.0.0';

    /**
     * @var string
     */
    private $user = 'Unknown user';

    /**
     * @var int
     */
    public $code = AdminLoginStatus::ERROR_UNKNOWN;

    /**
     * @return array
     */
    public function asArray(): array
    {
        return [
            'ip'   => $this->getIP(),
            'code' => $this->getCode(),
            'user' => $this->getUser(),
        ];
    }

    /**
     * @return string
     */
    public function getIP(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIP($ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }
}
