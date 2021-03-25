<?php

use JTL\Shop;

/**
 * Stellt alle Werte die fuer das Update in der DB wichtig sind zurueck
 *
 * @return bool
 * @deprecated since 5.0.0
 */
function resetteUpdateDB(): bool
{
    return false;
}

/**
 * @param string $path
 * @return bool
 * @deprecated since 5.0.0
 */
function loescheVerzeichnisUpdater(string $path): bool
{
    return false;
}

/**
 * @param string $file
 * @return bool
 * @deprecated since 5.0.0
 */
function updateZeilenBis($file): bool
{
    return false;
}

/**
 * @param int $version
 * @deprecated since 5.0.0
 */
function updateFertig(int $version): void
{
    header('Location: ' . Shop::getAdminURL() . '/dbupdater.php?nErrorCode=100');
    exit();
}
