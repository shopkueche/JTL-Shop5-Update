<?php declare(strict_types=1);

namespace JTL\Boxes\Items;

use JTL\Catalog\Product\ArtikelListe;
use JTL\DB\ReturnType;
use JTL\Helpers\SearchSpecial;
use JTL\Session\Frontend;
use JTL\Shop;
use function Functional\map;

/**
 * Class BestsellingProducts
 * @package JTL\Boxes\Items
 */
final class BestsellingProducts extends AbstractBox
{
    /**
     * BestsellingProducts constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(false);
        $customerGroupID = Frontend::getCustomerGroup()->getID();
        if ($customerGroupID && Frontend::getCustomerGroup()->mayViewCategories()) {
            $cached         = true;
            $cacheTags      = [\CACHING_GROUP_BOX, \CACHING_GROUP_ARTICLE];
            $stockFilterSQL = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $parentSQL      = ' AND tartikel.kVaterArtikel = 0';
            $cacheID        = 'bx_bstsl_' . $customerGroupID . '_' . \md5($parentSQL . $stockFilterSQL);
            if (($productIDs = Shop::Container()->getCache()->get($cacheID)) === false) {
                $cached   = false;
                $minCount = (int)$this->config['global']['global_bestseller_minanzahl'] > 0
                    ? (int)$this->config['global']['global_bestseller_minanzahl']
                    : 100;
                $limit    = (int)$this->config['boxen']['box_bestseller_anzahl_basis'] > 0
                    ? (int)$this->config['boxen']['box_bestseller_anzahl_basis']
                    : 10;

                $productIDs = Shop::Container()->getDB()->query(
                    'SELECT tartikel.kArtikel
                        FROM tbestseller, tartikel
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tbestseller.kArtikel = tartikel.kArtikel
                            AND ROUND(tbestseller.fAnzahl) >= ' . $minCount . ' ' .
                            $parentSQL . $stockFilterSQL . '
                        ORDER BY fAnzahl DESC LIMIT ' . $limit,
                    ReturnType::ARRAY_OF_OBJECTS
                );
                Shop::Container()->getCache()->set($cacheID, $productIDs, $cacheTags);
            }
            \shuffle($productIDs);
            $res = map(
                \array_slice($productIDs, 0, $this->config['boxen']['box_bestseller_anzahl_anzeige']),
                static function ($productID) {
                    return (int)$productID->kArtikel;
                }
            );

            if (\count($res) > 0) {
                $this->setShow(true);
                $products = new ArtikelListe();
                $products->getArtikelByKeys($res, 0, \count($res));
                $this->setProducts($products);
                $this->setURL(SearchSpecial::buildURL(\SEARCHSPECIALS_BESTSELLER));
            }

            \executeHook(\HOOK_BOXEN_INC_BESTSELLER, [
                'box'        => &$this,
                'cache_tags' => &$cacheTags,
                'cached'     => $cached
            ]);
        }
    }
}
