<?php

namespace JTL\Services\JTL;

/**
 * Class PasswordService
 * @package JTL\Services\JTL
 */
class PasswordService implements PasswordServiceInterface
{
    /**
     * The lowest allowed ascii character in decimal representation
     */
    public const ASCII_MIN = 33;

    /**
     * The highest allowed ascii character in decimal representation
     */
    public const ASCII_MAX = 127;

    /**
     * @var CryptoServiceInterface
     */
    protected $cryptoService;

    /**
     * PasswordService constructor.
     * @param CryptoServiceInterface $cryptoService
     */
    public function __construct(CryptoServiceInterface $cryptoService)
    {
        $this->cryptoService = $cryptoService;
    }

    /**
     * @inheritdoc
     */
    public function generate($length): string
    {
        /**
         * I have chosen to not use random_bytes, because using special characters in passwords is recommended. It is
         * therefore better to generate a password with random_int using a char whitelist.
         * Note: random_int is cryptographically secure
         */
        $result = '';
        for ($x = 0; $x < $length; $x++) {
            $no      = $this->cryptoService->randomInt(self::ASCII_MIN, self::ASCII_MAX);
            $result .= \chr($no);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function hash($password): string
    {
        return \password_hash($password, \PASSWORD_DEFAULT);
    }

    /**
     * @inheritdoc
     */
    public function verify($password, $hash)
    {
        $length = \mb_strlen($hash);
        if ($length === 32) {
            // very old md5 hashes
            return \md5($password) === $hash;
        }
        if ($length === 40) {
            return \cryptPasswort($password, $hash) !== false;
        }

        return \password_verify($password, $hash);
    }

    /**
     * @inheritdoc
     */
    public function needsRehash($hash): bool
    {
        $length = \mb_strlen($hash);

        return $length === 32 || $length === 40
            ? true
            : \password_needs_rehash($hash, \PASSWORD_DEFAULT);
    }

    /**
     * @inheritdoc
     */
    public function getInfo($hash): array
    {
        return \password_get_info($hash);
    }

    /**
     * @inheritdoc
     */
    public function hasOnlyValidCharacters(string $pass, string $validCharRegex = ''): bool
    {
        return !\preg_match(
            $validCharRegex ?: '/[^A-Za-z0-9\!"\#\$%&\'\(\)\*\+,-\.\/:;\=\>\?@\[\\\\\]\^_`\|\}~]/',
            $pass
        );
    }
}
