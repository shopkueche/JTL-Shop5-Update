<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class PluginID
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class PluginID extends AbstractItem
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
        \preg_match('/[\w_]+/', $baseNode['PluginID'], $hits);
        if (empty($baseNode['PluginID']) || \mb_strlen($hits[0]) !== \mb_strlen($baseNode['PluginID'])) {
            return InstallCode::INVALID_PLUGIN_ID;
        }

        return InstallCode::OK;
    }
}
