<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\Plugin\InstallCode;
use stdClass;

/**
 * Class CSS
 * @package JTL\Plugin\Admin\Installation\Items
 */
class CSS extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['CSS'][0]['file'])
        && \is_array($this->baseNode['Install'][0]['CSS'][0]['file'])
            ? $this->baseNode['Install'][0]['CSS'][0]['file']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        foreach ($this->getNode() as $file) {
            if (!isset($file['name'])) {
                continue;
            }
            $res           = new stdClass();
            $res->kPlugin  = $this->plugin->kPlugin;
            $res->type     = 'css';
            $res->path     = $file['name'];
            $res->priority = (int)($file['priority'] ?? 5);
            $this->db->insert('tplugin_resources', $res);
        }

        return InstallCode::OK;
    }
}
