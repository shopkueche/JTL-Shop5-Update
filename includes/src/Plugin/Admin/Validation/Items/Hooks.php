<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Helpers\GeneralObject;
use JTL\Plugin\InstallCode;

/**
 * Class Hooks
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class Hooks extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        $dir  = $this->getDir();
        if (!GeneralObject::isCountable('Hooks', $node)) {
            return InstallCode::OK;
        }
        if (\count($node['Hooks'][0]) === 1) {
            foreach ($node['Hooks'][0]['Hook'] as $i => $hook) {
                $i = (string)$i;
                \preg_match('/[0-9]+\sattr/', $i, $hits1);
                \preg_match('/[0-9]+/', $i, $hits2);
                if (isset($hits1[0]) && \mb_strlen($hits1[0]) === \mb_strlen($i)) {
                    if (\mb_strlen($hook['id']) === 0) {
                        return InstallCode::INVALID_HOOK;
                    }
                } elseif (isset($hits2[0]) && \mb_strlen($hits2[0]) === \mb_strlen($i)) {
                    if (\mb_strlen($hook) === 0) {
                        return InstallCode::INVALID_HOOK;
                    }
                    if (!\file_exists($dir . \PFAD_PLUGIN_FRONTEND . $hook)) {
                        return InstallCode::MISSING_HOOK_FILE;
                    }
                }
            }
        } elseif (\count($node['Hooks'][0]) > 1) {
            $hook = $node['Hooks'][0];
            if ((int)$hook['Hook attr']['id'] === 0 || \mb_strlen($hook['Hook']) === 0) {
                return InstallCode::INVALID_HOOK;
            }
            if (!\file_exists($dir . \PFAD_PLUGIN_FRONTEND . $hook['Hook'])) {
                return InstallCode::MISSING_HOOK_FILE;
            }
        }

        return InstallCode::OK;
    }
}
