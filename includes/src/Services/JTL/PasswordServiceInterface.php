<?php

namespace JTL\Services\JTL;

/**
 * Interface PasswordServiceInterface
 * @package JTL\Services\JTL
 */
interface PasswordServiceInterface
{
    /**
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public function generate($length): string;

    /**
     * @param string $password
     * @return string
     * @throws \Exception
     */
    public function hash($password): string;

    /**
     * @param string $password
     * @param string $hash
     * @return string|bool
     * @throws \Exception
     */
    public function verify($password, $hash);

    /**
     * @param string $hash
     * @return bool
     * @throws \Exception
     */
    public function needsRehash($hash): bool;

    /**
     * @param string $hash
     * @return mixed
     */
    public function getInfo($hash): array;

    /**
     * @param string $pass
     * @param string $validCharRegex
     * @return bool
     */
    public function hasOnlyValidCharacters(string $pass, string $validCharRegex = ''): bool;
}
