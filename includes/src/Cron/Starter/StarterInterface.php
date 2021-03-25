<?php

namespace JTL\Cron\Starter;

/**
 * Interface StarterInterface
 * @package JTL\Cron\Starter
 */
interface StarterInterface
{
    /**
     * @return bool
     */
    public function start() : bool;

    /**
     * @return int
     */
    public function getTimeout(): int;

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout): void;

    /**
     * @return string
     */
    public function getURL(): string;

    /**
     * @param string $url
     */
    public function setURL(string $url): void;
}
