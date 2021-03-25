<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\Helpers\GeneralObject;
use JTL\Plugin\InstallCode;
use stdClass;

/**
 * Class AdminMenu
 * @package JTL\Plugin\Admin\Installation\Items
 */
class AdminMenu extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['Adminmenu'])
        && \is_array($this->baseNode['Install'][0]['Adminmenu'])
            ? $this->baseNode['Install'][0]['Adminmenu']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        $node = $this->getNode();
        if (isset($node[0]) && GeneralObject::hasCount('Customlink', $node[0])) {
            $sort = 0;
            foreach ($node[0]['Customlink'] as $i => $customLink) {
                $i = (string)$i;
                \preg_match('/[0-9]+\sattr/', $i, $hits1);
                \preg_match('/[0-9]+/', $i, $hits2);
                if (isset($hits1[0]) && \mb_strlen($hits1[0]) === \mb_strlen($i)) {
                    $sort = (int)$customLink['sort'];
                } elseif (\mb_strlen($hits2[0]) === \mb_strlen($i)) {
                    $menuItem             = new stdClass();
                    $menuItem->kPlugin    = $this->plugin->kPlugin;
                    $menuItem->cName      = $customLink['Name'];
                    $menuItem->cDateiname = $customLink['Filename'] ?? '';
                    $menuItem->nSort      = $sort;
                    $menuItem->nConf      = 0;
                    if (!$this->db->insert('tpluginadminmenu', $menuItem)) {
                        return InstallCode::SQL_CANNOT_SAVE_ADMIN_MENU_ITEM;
                    }
                }
            }
        }

        return InstallCode::OK;
    }
}
