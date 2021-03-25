<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use InvalidArgumentException;
use JTL\Plugin\InstallCode;
use JTLShop\SemVer\Version as SemVer;

/**
 * Class Version
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class Version extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $baseNode = $this->getBaseNode();
        if (!isset($baseNode['Version'])) {
            return InstallCode::INVALID_VERSION_NUMBER;
        }
        try {
            SemVer::parse($baseNode['Version']);
        } catch (InvalidArgumentException $e) {
            return InstallCode::INVALID_VERSION_NUMBER;
        }

        return InstallCode::OK;
    }
}
