<?php declare(strict_types=1);

namespace JTL\Boxes\Items;

use JTL\Catalog\Product\ArtikelListe;
use JTL\DB\ReturnType;
use JTL\Helpers\SearchSpecial;
use JTL\Session\Frontend;
use JTL\Shop;
use function Functional\map;

/**
 * Class TopOffers
 * @package JTL\Boxes\Items
 */
final class TopOffers extends AbstractBox
{
    /**
     * TopOffers constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(false);
        $customerGroupID = Frontend::getCustomerGroup()->getID();
        if ($customerGroupID > 0 && Frontend::getCustomerGroup()->mayViewCategories()) {
            $cacheTags      = [\CACHING_GROUP_BOX, \CACHING_GROUP_ARTICLE];
            $cached         = true;
            $limit          = $config['boxen']['box_topangebot_anzahl_basis'];
            $stockFilterSQL = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $parentSQL      = ' AND tartikel.kVaterArtikel = 0';
            $cacheID        = 'box_top_offer_' . $customerGroupID . '_' .
                $limit . \md5($stockFilterSQL . $parentSQL);
            if (($productIDs = Shop::Container()->getCache()->get($cacheID)) === false) {
                $cached     = false;
                $productIDs = Shop::Container()->getDB()->queryPrepared(
                    "SELECT tartikel.kArtikel
                        FROM tartikel
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = :cid
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikel.cTopArtikel = 'Y' " .
                        $stockFilterSQL .
                        $parentSQL . '
                        LIMIT ' . $limit,
                    ['cid' => $customerGroupID],
                    ReturnType::ARRAY_OF_OBJECTS
                );
                Shop::Container()->getCache()->set($cacheID, $productIDs, $cacheTags);
            }
            \shuffle($productIDs);
            $res = map(
                \array_slice($productIDs, 0, $config['boxen']['box_topangebot_anzahl_anzeige']),
                static function ($productID) {
                    return (int)$productID->kArtikel;
                }
            );

            if (\count($res) > 0) {
                $this->setShow(true);
                $products = new ArtikelListe();
                $products->getArtikelByKeys($res, 0, \count($res));
                $this->setProducts($products);
                $this->setURL(SearchSpecial::buildURL(\SEARCHSPECIALS_TOPOFFERS));
                \executeHook(\HOOK_BOXEN_INC_TOPANGEBOTE, [
                    'box'        => &$this,
                    'cache_tags' => &$cacheTags,
                    'cached'     => $cached
                ]);
            }
        }
    }
}
