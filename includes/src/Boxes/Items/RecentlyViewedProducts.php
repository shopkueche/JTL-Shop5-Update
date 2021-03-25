<?php declare(strict_types=1);

namespace JTL\Boxes\Items;

use JTL\Catalog\Product\Artikel;
use JTL\Helpers\GeneralObject;
use JTL\Session\Frontend;

/**
 * Class RecentlyViewedProducts
 * @package JTL\Boxes\Items
 */
final class RecentlyViewedProducts extends AbstractBox
{
    /**
     * RecentlyViewedProducts constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(false);
        if (GeneralObject::hasCount('ZuletztBesuchteArtikel', $_SESSION)
            && Frontend::getCustomerGroup()->mayViewCategories()
        ) {
            $products       = [];
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($_SESSION['ZuletztBesuchteArtikel'] as $i => $item) {
                $product = new Artikel();
                $product->fuelleArtikel($item->kArtikel, $defaultOptions);
                if ($product->kArtikel > 0) {
                    $products[$i] = $product;
                }
            }
            $this->setProducts(\array_reverse($products));
            $this->setShow(true);

            \executeHook(\HOOK_BOXEN_INC_ZULETZTANGESEHEN, ['box' => $this]);
        }
    }
}
