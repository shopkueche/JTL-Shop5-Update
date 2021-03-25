<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Helpers\GeneralObject;
use JTL\Plugin\InstallCode;

/**
 * Class Boxes
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class Boxes extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode()['Boxes'] ?? null;
        $dir  = $this->getDir();
        if (!GeneralObject::isCountable($node)) {
            return InstallCode::OK;
        }
        $node = $node[0]['Box'] ?? null;
        $base = $dir . \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_BOXEN;
        if (!GeneralObject::hasCount($node)) {
            return InstallCode::MISSING_BOX;
        }
        foreach ($node as $i => $box) {
            $i = (string)$i;
            \preg_match('/[0-9]+/', $i, $hits3);
            if (\mb_strlen($hits3[0]) !== \mb_strlen($i)) {
                continue;
            }
            if (empty($box['Name'])) {
                return InstallCode::INVALID_BOX_NAME;
            }
            if (empty($box['TemplateFile'])) {
                return InstallCode::INVALID_BOX_TEMPLATE;
            }
            $new = $dir . \PFAD_PLUGIN_FRONTEND . 'boxes/' . $box['TemplateFile'];
            $old = $base . $box['TemplateFile'];
            if (!\file_exists($new) && !\file_exists($old)) {
                return InstallCode::MISSING_BOX_TEMPLATE_FILE;
            }
        }

        return InstallCode::OK;
    }
}
