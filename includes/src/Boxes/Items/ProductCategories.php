<?php declare(strict_types=1);

namespace JTL\Boxes\Items;

use JTL\Helpers\Category;
use JTL\Session\Frontend;
use JTL\Catalog\Category\MenuItem;

/**
 * Class ProductCategories
 * @package JTL\Boxes\Items
 */
final class ProductCategories extends AbstractBox
{
    /**
     * ProductCategories constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $show = isset($config['global']['global_sichtbarkeit'])
            && ((int)$config['global']['global_sichtbarkeit'] !== 3 || Frontend::getCustomer()->getID() > 0);
        $this->setShow($show);
    }

    /**
     * @return array
     */
    private function getCategories(): array
    {
        $categories = Category::getInstance();
        $list       = $categories->combinedGetAll();
        $boxID      = $this->getCustomID();
        if ($boxID > 0) {
            $list2 = [];
            foreach ($list as $key => $item) {
                /** @var MenuItem $item **/
                if ($item->getFunctionalAttribute(\KAT_ATTRIBUT_KATEGORIEBOX) !== null
                    && (int)$item->getFunctionalAttribute(\KAT_ATTRIBUT_KATEGORIEBOX) === $boxID
                ) {
                    $list2[$key] = $item;
                }
            }
            $list = $list2;
        }

        return $list;
    }

    /**
     *
     */
    public function init(): void
    {
        if ($this->getShow() === true) {
            $categories = $this->getCategories();
            $this->setItems($categories);
            $this->setShow(\count($categories) > 0);
        }
    }
}
