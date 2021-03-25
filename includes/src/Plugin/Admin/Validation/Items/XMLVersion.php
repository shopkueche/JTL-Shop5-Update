<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class XMLVersion
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class XMLVersion extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $baseNode = $this->getBaseNode();
        if (!isset($baseNode['XMLVersion'])) {
            return InstallCode::INVALID_XML_VERSION;
        }
        \preg_match('/[0-9]{3}/', $baseNode['XMLVersion'], $hits);
        if (\count($hits) === 0
            || (\mb_strlen($hits[0]) !== \mb_strlen($baseNode['XMLVersion']) && (int)$baseNode['XMLVersion'] >= 100)
        ) {
            return InstallCode::INVALID_XML_VERSION;
        }

        return InstallCode::OK;
    }
}
