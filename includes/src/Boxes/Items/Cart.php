<?php declare(strict_types=1);

namespace JTL\Boxes\Items;

/**
 * Class Cart
 *
 * @package JTL\Boxes\Items
 */
final class Cart extends AbstractBox
{
    /**
     * Cart constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->addMapping('elemente', 'Items');
        if (isset($_SESSION['Warenkorb']->PositionenArr)) {
            $products = [];
            foreach ($_SESSION['Warenkorb']->PositionenArr as $item) {
                $products[] = $item;
            }
            $this->setItems(\array_reverse($products));
        }
        $this->setShow(true);
    }
}
