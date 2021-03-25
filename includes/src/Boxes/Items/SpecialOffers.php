<?php declare(strict_types=1);

namespace JTL\Boxes\Items;

use JTL\Catalog\Product\ArtikelListe;
use JTL\DB\ReturnType;
use JTL\Helpers\SearchSpecial;
use JTL\Session\Frontend;
use JTL\Shop;
use function Functional\map;

/**
 * Class SpecialOffers
 * @package JTL\Boxes\Items
 */
final class SpecialOffers extends AbstractBox
{
    /**
     * SpecialOffers constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(false);
        $customerGroupID = Frontend::getCustomerGroup()->getID();
        if ($customerGroupID && Frontend::getCustomerGroup()->mayViewCategories()) {
            $cached         = true;
            $stockFilterSQL = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $parentSQL      = ' AND tartikel.kVaterArtikel = 0';
            $limit          = $config['boxen']['box_sonderangebote_anzahl_basis'];
            $cacheTags      = [\CACHING_GROUP_BOX, \CACHING_GROUP_ARTICLE];
            $cacheID        = 'box_special_offer_' . $customerGroupID . '_' .
                $limit . \md5($stockFilterSQL . $parentSQL);
            if (($productIDs = Shop::Container()->getCache()->get($cacheID)) === false) {
                $cached     = false;
                $productIDs = Shop::Container()->getDB()->queryPrepared(
                    "SELECT tartikel.kArtikel
                        FROM tartikel
                        JOIN tartikelsonderpreis 
                            ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                        JOIN tsonderpreise 
                            ON tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = :cgid
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikelsonderpreis.kArtikel = tartikel.kArtikel
                            AND tsonderpreise.kKundengruppe = :cgid
                            AND tartikelsonderpreis.cAktiv = 'Y'
                            AND tartikelsonderpreis.dStart <= NOW()
                            AND (tartikelsonderpreis.dEnde IS NULL OR tartikelsonderpreis.dEnde >= CURDATE()) " .
                            $stockFilterSQL . $parentSQL . '
                        LIMIT :lmt',
                    ['lmt' => $limit, 'cgid' => $customerGroupID],
                    ReturnType::ARRAY_OF_OBJECTS
                );
                Shop::Container()->getCache()->set($cacheID, $productIDs, $cacheTags);
            }
            \shuffle($productIDs);
            $res = map(
                \array_slice($productIDs, 0, $config['boxen']['box_sonderangebote_anzahl_anzeige']),
                static function ($productID) {
                    return (int)$productID->kArtikel;
                }
            );

            if (\count($res) > 0) {
                $this->setShow(true);
                $products = new ArtikelListe();
                $products->getArtikelByKeys($res, 0, \count($res));
                $this->setProducts($products);
                $this->setURL(SearchSpecial::buildURL(\SEARCHSPECIALS_SPECIALOFFERS));
                \executeHook(\HOOK_BOXEN_INC_SONDERANGEBOTE, [
                    'box'        => &$this,
                    'cache_tags' => &$cacheTags,
                    'cached'     => $cached
                ]);
            }
        }
    }
}
