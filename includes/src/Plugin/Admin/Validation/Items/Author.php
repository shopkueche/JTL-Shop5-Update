<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class Author
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class Author extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $baseNode = $this->getBaseNode();

        return isset($baseNode['Author']) ? InstallCode::OK : InstallCode::INVALID_AUTHOR;
    }
}
