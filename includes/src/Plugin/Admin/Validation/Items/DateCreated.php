<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class DateCreated
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class DateCreated extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $baseNode = $this->getBaseNode();
        if (!isset($baseNode['CreateDate'])) {
            return InstallCode::INVALID_DATE;
        }
        \preg_match(
            '/[0-9]{4}-[0-1]{1}[0-9]{1}-[0-3]{1}[0-9]{1}/',
            $baseNode['CreateDate'],
            $hits
        );

        return !isset($hits[0]) || \mb_strlen($hits[0]) !== \mb_strlen($baseNode['CreateDate'])
            ? InstallCode::INVALID_DATE
            : InstallCode::OK;
    }
}
