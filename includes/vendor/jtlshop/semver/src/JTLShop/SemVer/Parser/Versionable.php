<?php

namespace JTLShop\SemVer\Parser;

use InvalidArgumentException;
use JTLShop\SemVer\Version;
use JTLShop\SemVer\Version\Build;
use JTLShop\SemVer\Version\PreRelease;

/**
 * Class Versionable
 *
 * Parses strings in form X.Y.Z into major, minor, patch of Versionable
 *
 * @package JTLShop\SemVer\Parser
 */
class Versionable
{
    /**
     * Parse a string into Versionable properties
     *
     * @param string $string
     * @param string $className
     * @return Version|PreRelease|Build
     * @throws InvalidArgumentException
     */
    public static function parse($string, $className)
    {
        // Sanity check
        if (\substr_count($string, '.') < 1 && \substr_count($string, '.') > 2) {
            throw new InvalidArgumentException(
                'part "' . $string . '" can not be parsed into a SemVer'
                . ' major.minor.patch version'
            );
        }

        // Simple explode will do here
        $parts = \explode('.', $string);

        // Instantiate
        $versionable = new $className();

        // Sanity check of class type
        if (!($versionable instanceof Version || $versionable instanceof PreRelease || $versionable instanceof Build)) {
            throw new InvalidArgumentException(
                '"' . $className . '" is not Version'
            );
        }

        // Set prefix, if it exists
        \preg_match('/^([a-z])?([0-9]+)$/', $parts[0], $m);
        $praefix = !empty($m[1]) ? $m[1] : null;
        $build   = $m[2];

        // Check if patch exists
        if (isset($parts[2])) {
            $versionable->setPatch((int)$parts[2]);
        } else {
            $versionable->setSelectLastPatch(true);
        }

        // Versionable parts
        $versionable->setPrefix($praefix)
            ->setMajor((int)$build)
            ->setMinor((int)$parts[1]);

        return $versionable;
    }
}
