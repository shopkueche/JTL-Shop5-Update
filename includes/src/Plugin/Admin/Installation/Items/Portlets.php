<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class Portlets
 * @package JTL\Plugin\Admin\Installation\Items
 */
class Portlets extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['Portlets'][0]['Portlet'])
        && \is_array($this->baseNode['Install'][0]['Portlets'][0]['Portlet'])
            ? $this->baseNode['Install'][0]['Portlets'][0]['Portlet']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        foreach ($this->getNode() as $i => $portlet) {
            $i = (string)$i;
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($i)) {
                continue;
            }
            $opcPortlet = (object)[
                'kPlugin' => $this->plugin->kPlugin,
                'cTitle'  => $portlet['Title'],
                'cClass'  => $portlet['Class'],
                'cGroup'  => $portlet['Group'],
                'bActive' => (int)$portlet['Active'],
            ];
            if (!$this->db->insert('topcportlet', $opcPortlet)) {
                return InstallCode::SQL_CANNOT_SAVE_PORTLET;
            }
        }

        return InstallCode::OK;
    }
}
