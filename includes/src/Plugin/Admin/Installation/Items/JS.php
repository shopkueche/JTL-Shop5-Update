<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\Plugin\InstallCode;
use stdClass;

/**
 * Class JS
 * @package JTL\Plugin\Admin\Installation\Items
 */
class JS extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['JS'][0]['file'])
        && \is_array($this->baseNode['Install'][0]['JS'][0]['file'])
            ? $this->baseNode['Install'][0]['JS'][0]['file']
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
            $res->type     = 'js';
            $res->path     = $file['name'];
            $res->priority = $file['priority'] ?? 5;
            $res->position = $file['position'] ?? 'head';
            $this->db->insert('tplugin_resources', $res);
        }

        return InstallCode::OK;
    }
}
