<?php declare(strict_types=1);

namespace JTL\Boxes\Items;

use JTL\Shop;

/**
 * Class Plain
 *
 * @package JTL\Boxes\Items
 */
final class Plain extends AbstractBox
{
    /**
     * Plain constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->supportsRevisions = true;
    }

    /**
     * @inheritdoc
     */
    public function map(array $boxData): void
    {
        parent::map($boxData);
        $this->setShow(!empty($this->getContent(Shop::getLanguageID())));
    }
}
