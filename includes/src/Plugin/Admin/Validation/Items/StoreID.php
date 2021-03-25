<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class StoreID
 * @package Plugin\Admin\Validation\Items
 */
final class StoreID extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $baseNode = $this->getBaseNode();
        if (isset($baseNode['StoreID']) && \preg_match('/\\w+/', $baseNode['StoreID']) !== 1) {
            return InstallCode::INVALID_STORE_ID;
        }

        return InstallCode::OK;
    }
}
