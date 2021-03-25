<?php declare(strict_types=1);

namespace JTL\Plugin;

/**
 * Interface LicenseInterface
 * @package JTL\Plugin
 */
interface LicenseInterface
{
    /**
     * @param string $key
     * @return mixed
     */
    public function checkLicence($key);
}
