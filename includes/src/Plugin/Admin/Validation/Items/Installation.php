<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Helpers\GeneralObject;
use JTL\Plugin\InstallCode;

/**
 * Class Installation
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class Installation extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        return GeneralObject::isCountable('Install', $this->getBaseNode())
            ? InstallCode::OK
            : InstallCode::INSTALL_NODE_MISSING;
    }
}
