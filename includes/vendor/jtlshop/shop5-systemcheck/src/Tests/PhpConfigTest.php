<?php declare(strict_types=1);

namespace Systemcheck\Tests;

/**
 * Class PhpConfigTest
 * @package Systemcheck\Tests
 */
abstract class PhpConfigTest extends AbstractTest
{
    /**
     * @param string $shorthand
     * @return float|int
     */
    protected function shortHandToInt($shorthand)
    {
        switch (\substr($shorthand, -1)) {
            case 'M':
            case 'm':
                return (int)$shorthand * 1048576;

            case 'K':
            case 'k':
                return (int)$shorthand * 1024;

            case 'G':
            case 'g':
                return (int)$shorthand * 1073741824;

            default:
                return $shorthand;
        }
    }
}
