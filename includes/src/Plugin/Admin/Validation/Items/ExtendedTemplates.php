<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Helpers\GeneralObject;
use JTL\Plugin\InstallCode;

/**
 * Class ExtendedTemplates
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class ExtendedTemplates extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        $dir  = $this->getDir();
        if (!GeneralObject::isCountable('ExtendedTemplates', $node)) {
            return InstallCode::OK;
        }
        $node = $node['ExtendedTemplates'][0]['Template'] ?? null;
        if (!GeneralObject::hasCount($node)) {
            return InstallCode::MISSING_EXTENDED_TEMPLATE;
        }
        foreach ($node as $template) {
            \preg_match('/[a-zA-Z0-9\/_\-]+\.tpl/', $template, $hits3);
            if (\mb_strlen($hits3[0]) !== \mb_strlen($template)) {
                return InstallCode::INVALID_EXTENDED_TEMPLATE_FILE_NAME;
            }
            if (!\file_exists($dir . \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_TEMPLATE . $template)) {
                return InstallCode::MISSING_EXTENDED_TEMPLATE_FILE;
            }
        }

        return InstallCode::OK;
    }
}
