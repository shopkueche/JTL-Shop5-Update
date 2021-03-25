<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\Plugin\InstallCode;
use stdClass;

/**
 * Class Uninstall
 * @package JTL\Plugin\Admin\Installation\Items
 */
class Uninstall extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return !empty($base['Uninstall'])
            ? (array)$base['Uninstall']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        foreach ($this->getNode() as $node) {
            $uninstall             = new stdClass();
            $uninstall->kPlugin    = $this->plugin->kPlugin;
            $uninstall->cDateiname = $node;
            if (!$this->db->insert('tpluginuninstall', $uninstall)) {
                return InstallCode::SQL_CANNOT_SAVE_UNINSTALL;
            }
        }

        return InstallCode::OK;
    }
}
