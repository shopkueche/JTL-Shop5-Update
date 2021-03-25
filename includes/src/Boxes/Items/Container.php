<?php declare(strict_types=1);

namespace JTL\Boxes\Items;

use JTL\Boxes\Renderer\ContainerRenderer;

/**
 * Class Container
 * @package JTL\Boxes\Items
 */
class Container extends AbstractBox
{
    /**
     * Container constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->addMapping('innerHTML', 'HTML');
        $this->addMapping('oContainer_arr', 'Children');
    }

    /**
     * @return string
     */
    public function getRenderer(): string
    {
        return ContainerRenderer::class;
    }
}
