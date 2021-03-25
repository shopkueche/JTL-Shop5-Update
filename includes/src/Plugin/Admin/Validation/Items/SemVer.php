<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use InvalidArgumentException;
use JTL\Plugin\InstallCode;
use JTLShop\SemVer\Version;

/**
 * Class SemVer
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class SemVer extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $baseNode = $this->getBaseNode();
        try {
            Version::parse($baseNode['Version']);
            Version::parse($baseNode['MinShopVersion'] ?? $baseNode['ShopVersion']);
            Version::parse($baseNode['MaxShopVersion'] ?? '1.0.0');
        } catch (InvalidArgumentException $e) {
            return InstallCode::INVALID_VERSION_NUMBER;
        }

        return InstallCode::OK;
    }
}
