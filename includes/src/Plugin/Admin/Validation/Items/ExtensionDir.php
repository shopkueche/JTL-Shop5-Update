<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class ExtensionDir
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class ExtensionDir extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $baseNode = $this->getBaseNode();
        if (!isset($baseNode['PluginID'])) {
            return InstallCode::INVALID_PLUGIN_ID;
        }

        return $baseNode['PluginID'] === \pathinfo($this->getDir())['basename']
            ? InstallCode::OK
            : InstallCode::WRONG_EXT_DIR;
    }
}
