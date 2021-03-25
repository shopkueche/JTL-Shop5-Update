<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Helpers\GeneralObject;
use JTL\Plugin\InstallCode;

/**
 * Class Checkboxes
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class Checkboxes extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode()['CheckBoxFunction'][0] ?? null;
        if (!GeneralObject::hasCount('Function', $node)) {
            return InstallCode::OK;
        }
        foreach ($node['Function'] as $i => $cb) {
            $i = (string)$i;
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) === \mb_strlen($i)) {
                if (!isset($cb['Name']) || \mb_strlen($cb['Name']) === 0) {
                    return InstallCode::INVALID_CHECKBOX_FUNCTION_NAME;
                }
                if (!isset($cb['ID']) || \mb_strlen($cb['ID']) === 0) {
                    return InstallCode::INVALID_CHECKBOX_FUNCTION_ID;
                }
            }
        }

        return InstallCode::OK;
    }
}
