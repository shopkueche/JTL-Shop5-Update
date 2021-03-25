<?php declare(strict_types=1);

namespace JTL\Services\JTL;

/**
 * Interface ConsentServiceInterface
 * @package JTL\Services\JTL
 */
interface ConsentServiceInterface
{
    public function register();

    public function hasConsent(): bool;
}
