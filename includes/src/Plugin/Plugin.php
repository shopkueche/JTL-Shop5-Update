<?php declare(strict_types=1);

namespace JTL\Plugin;

use JTL\XMLParser;
use JTLShop\SemVer\Version;

/**
 * Class Plugin
 * @package JTL\Plugin
 */
class Plugin extends AbstractPlugin
{
    /**
     * @inheritdoc
     */
    public function getCurrentVersion(): Version
    {
        $path = $this->getPaths()->getBasePath();
        if (!\is_dir($path) || !\file_exists($path . '/' . \PLUGIN_INFO_FILE)) {
            return Version::parse('0.0.0');
        }
        $parser = new XMLParser();
        $xml    = $parser->parse($path . '/' . \PLUGIN_INFO_FILE);

        return Version::parse($xml['jtlshopplugin'][0]['Version'] ?? 0);
    }
}
