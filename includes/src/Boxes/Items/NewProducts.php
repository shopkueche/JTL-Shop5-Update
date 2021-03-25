<?php declare(strict_types=1);

namespace JTL\Boxes\Items;

use JTL\Catalog\Product\ArtikelListe;
use JTL\DB\ReturnType;
use JTL\Helpers\SearchSpecial;
use JTL\Session\Frontend;
use JTL\Shop;
use function Functional\map;

/**
 * Class NewProducts
 * @package JTL\Boxes\Items
 */
final class NewProducts extends AbstractBox
{
    /**
     * NewProducts constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(false);
        $customerGroupID = Frontend::getCustomerGroup()->getID();
        if ($customerGroupID && Frontend::getCustomerGroup()->mayViewCategories()) {
            $cacheTags      = [\CACHING_GROUP_BOX, \CACHING_GROUP_ARTICLE];
            $cached         = true;
            $stockFilterSQL = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $parentSQL      = ' AND tartikel.kVaterArtikel = 0';
            $limit          = $config['boxen']['box_neuimsortiment_anzahl_basis'];
            $days           = $config['boxen']['box_neuimsortiment_alter_tage'] > 0
                ? (int)$config['boxen']['box_neuimsortiment_alter_tage']
                : 30;
            $cacheID        = 'bx_nw_' . $customerGroupID .
                '_' . $days . '_' .
                $limit . \md5($stockFilterSQL . $parentSQL);
            if (($productIDs = Shop::Container()->getCache()->get($cacheID)) === false) {
                $cached     = false;
                $productIDs = Shop::Container()->getDB()->query(
                    'SELECT tartikel.kArtikel
                        FROM tartikel
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . "
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikel.cNeu = 'Y' " . $stockFilterSQL . $parentSQL . '
                            AND DATE_SUB(NOW(), INTERVAL ' . $days . ' DAY) < dErstellt
                        LIMIT ' . $limit,
                    ReturnType::ARRAY_OF_OBJECTS
                );
                Shop::Container()->getCache()->set($cacheID, $productIDs, $cacheTags);
            }
            \shuffle($productIDs);
            $res = map(
                \array_slice($productIDs, 0, $config['boxen']['box_neuimsortiment_anzahl_anzeige']),
                static function ($productID) {
                    return (int)$productID->kArtikel;
                }
            );

            if (\count($res) > 0) {
                $this->setShow(true);
                $products = new ArtikelListe();
                $products->getArtikelByKeys($res, 0, \count($res));
                $this->setProducts($products);
                $this->setURL(SearchSpecial::buildURL(\SEARCHSPECIALS_NEWPRODUCTS));
                \executeHook(\HOOK_BOXEN_INC_NEUIMSORTIMENT, [
                    'box'        => &$this,
                    'cache_tags' => &$cacheTags,
                    'cached'     => $cached
                ]);
            }
        }
    }
}
