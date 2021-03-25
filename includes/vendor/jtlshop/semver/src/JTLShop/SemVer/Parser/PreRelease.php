<?php

namespace JTLShop\SemVer\Parser;

use JTLShop\SemVer\Parser\Versionable as VersionableParser;
use JTLShop\SemVer\Version\PreRelease as PreReleaseVersion;

/**
 * Class PreRelease
 *
 * PreRelease part parser uses Versionable parser for most of the grunt work,
 * but parses "greek" name, etc. as well.
 *
 * @package JTLShop\SemVer\Parser
 */
class PreRelease
{
    /**
     * Parse pre release version string
     *
     * @param string
     * @return PreReleaseVersion|\JTLShop\SemVer\Version\Versionable
     */
    public static function parse($string)
    {
        // Type X.Y.Z, can be parsed as Versionable
        if (\substr_count($string, '.') === 2) {
            return VersionableParser::parse($string, PreReleaseVersion::class);
        }
        $preRelease = new PreReleaseVersion();
        $parts      = \explode('.', $string);
        // Sanity check
        if (count($parts) === 0) {
            return $preRelease;
        }
        // Set the greek name
        $preRelease->setGreek(
            $parts[0]
        );
        // If there's another part it's a release number
        if (isset($parts[1])) {
            $preRelease->setReleaseNumber(
                (int)$parts[1]
            );
        }

        return $preRelease;
    }
}
