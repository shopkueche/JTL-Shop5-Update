<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class Uninstaller
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class Uninstaller extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getBaseNode();
        $dir  = $this->getDir();
        if (!empty($node['Uninstall']) && !\file_exists($dir . \PFAD_PLUGIN_UNINSTALL . $node['Uninstall'])) {
            return InstallCode::MISSING_UNINSTALL_FILE;
        }

        return InstallCode::OK;
    }
}
