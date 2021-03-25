<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class Name
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class Name extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $baseNode = $this->getBaseNode();
        if (!isset($baseNode['Name'])) {
            return InstallCode::INVALID_NAME;
        }
        \preg_match(
            '/[\w()\- ]+/u',
            $baseNode['Name'],
            $hits
        );

        return !isset($hits[0]) || \mb_strlen($hits[0]) !== \mb_strlen($baseNode['Name'])
            ? InstallCode::INVALID_NAME
            : InstallCode::OK;
    }
}
