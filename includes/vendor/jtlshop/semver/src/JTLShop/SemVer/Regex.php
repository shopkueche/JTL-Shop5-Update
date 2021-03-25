<?php

namespace JTLShop\SemVer;

use InvalidArgumentException;

/**
 * Class Regex
 *
 * Regex matching and parsing for SemVer strings
 *
 * @package JTLShop\SemVer
 */
class Regex
{
    /**
     * Single SemVer expression
     *
     * @var string
     */
    private static $versionStr = '/^(?<version>v?[0-9]+\.[0-9]+(?:\.[0-9]+)?)'
    . '(?<prerelease>-[0-9a-zA-Z.]+)?(?<build>\+[0-9a-zA-Z.]+)?$/';

    /**
     * @var string
     */
    private static $versionNum = '/^(?<version>(\d)(\d{2})(\d{1})?)$/';

    /**
     * Match a SemVer using a regex
     *
     * Array wil at least have a key `version`
     *
     * But might also contain:
     *
     *  - prerelease
     *  - build
     *
     * @param string $string
     * @return array[string]string
     * @throws InvalidArgumentException
     *
     */
    public static function matchSemVer($string)
    {
        // Array of matches for PCRE
        $matches = array();

        // Match the possible parts of a SemVer
        $matchedStr = \preg_match(
            self::$versionStr,
            $string,
            $matches
        );

        if (!$matchedStr) {
            // Match the possible parts of a SemVer
            $matchedNum = \preg_match(
                self::$versionNum,
                $string,
                $matches
            );

            if (!$matchedNum) {
                throw new InvalidArgumentException(
                    '"' . $string . '" is not a valid SemVer'
                );
            }

            $fullString = $matches[2] . '.' . $matches[3];

            if (isset($matches[4])) {
                $fullString .= '.' . $matches[4];
            }

            return ['version' => $matches['version'], $matches[0], $fullString];
        }

        // Return matched array
        return $matches;
    }
}
