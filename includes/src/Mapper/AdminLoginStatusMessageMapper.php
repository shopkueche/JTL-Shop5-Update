<?php declare(strict_types=1);

namespace JTL\Mapper;

use JTL\Backend\AdminLoginStatus;

/**
 * Class AdminLoginStatusMessageMapper
 * @package JTL\Mapper
 */
class AdminLoginStatusMessageMapper
{
    /**
     * @param int $code
     * @return string
     */
    public function map(int $code): string
    {
        switch ($code) {
            case AdminLoginStatus::LOGIN_OK:
                return 'user {user}@{ip} successfully logged in';
            case AdminLoginStatus::ERROR_NOT_AUTHORIZED:
                return 'user {user}@{ip} is not authorized';
            case AdminLoginStatus::ERROR_INVALID_PASSWORD_LOCKED:
            case AdminLoginStatus::ERROR_INVALID_PASSWORD:
                return 'invalid password for user {user}@{ip}';
            case AdminLoginStatus::ERROR_USER_NOT_FOUND:
                return 'user {user}@{ip} not found';
            case AdminLoginStatus::ERROR_USER_DISABLED:
                return 'user {user}@{ip} disabled';
            case AdminLoginStatus::ERROR_LOGIN_EXPIRED:
                return 'login for user {user}@{ip} expired';
            case AdminLoginStatus::ERROR_TWO_FACTOR_AUTH_EXPIRED:
                return 'two factor authentication token for user {user}@{ip} expired';
            case AdminLoginStatus::ERROR_UNKNOWN:
            default:
                return 'unknown error for user {user}@{ip}';
        }
    }
}
