<?php declare(strict_types=1);

namespace JTL\Helpers;

use JTL\Cache\JTLCacheInterface;
use JTL\Customer\CustomerGroup;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Media\Image\Overlay;
use JTL\Shop;
use stdClass;
use function Functional\map;

/**
 * Class SearchSpecial
 * @package JTL\Helpers
 * @since 5.0.0
 */
class SearchSpecial
{
    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * SearchSpecial constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->db    = $db;
        $this->cache = $cache;
    }

    /**
     * @param int $langID
     * @return Overlay[]
     * @former holeAlleSuchspecialOverlays()
     * @since 5.0.0
     */
    public static function getAll(int $langID = 0): array
    {
        static $allOverlays = [];

        $langID  = $langID > 0 ? $langID : Shop::getLanguageID();
        $cacheID = 'haso_' . $langID;
        if (isset($allOverlays[$cacheID])) {
            return $allOverlays[$cacheID];
        }
        if (($overlays = Shop::Container()->getCache()->get($cacheID)) === false) {
            $overlays = [];
            $types    = Shop::Container()->getDB()->query(
                'SELECT kSuchspecialOverlay
                    FROM tsuchspecialoverlay',
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($types as $type) {
                $overlay = Overlay::getInstance((int)$type->kSuchspecialOverlay, $langID);
                if ($overlay->getActive() === 1) {
                    $overlays[] = $overlay;
                }
            }
            $overlays = \Functional\sort($overlays, static function (Overlay $left, Overlay $right) {
                return $left->getPriority() <=> $right->getPriority();
            });
            Shop::Container()->getCache()->set($cacheID, $overlays, [\CACHING_GROUP_OPTION]);
        }
        $allOverlays[$cacheID] = $overlays;

        return $overlays;
    }

    /**
     * @return array
     * @former baueAlleSuchspecialURLs
     * @since 5.0.0
     */
    public static function buildAllURLs(): array
    {
        $overlays = [];
        $lang     = Shop::Lang();
        $self     = new self(Shop::Container()->getDB(), Shop::Container()->getCache());

        $overlays[\SEARCHSPECIALS_BESTSELLER]        = new stdClass();
        $overlays[\SEARCHSPECIALS_BESTSELLER]->cName = $lang->get('bestseller');
        $overlays[\SEARCHSPECIALS_BESTSELLER]->cURL  = $self->getURL(\SEARCHSPECIALS_BESTSELLER);

        $overlays[\SEARCHSPECIALS_SPECIALOFFERS]        = new stdClass();
        $overlays[\SEARCHSPECIALS_SPECIALOFFERS]->cName = $lang->get('specialOffers');
        $overlays[\SEARCHSPECIALS_SPECIALOFFERS]->cURL  = $self->getURL(\SEARCHSPECIALS_SPECIALOFFERS);

        $overlays[\SEARCHSPECIALS_NEWPRODUCTS]        = new stdClass();
        $overlays[\SEARCHSPECIALS_NEWPRODUCTS]->cName = $lang->get('newProducts');
        $overlays[\SEARCHSPECIALS_NEWPRODUCTS]->cURL  = $self->getURL(\SEARCHSPECIALS_NEWPRODUCTS);

        $overlays[\SEARCHSPECIALS_TOPOFFERS]        = new stdClass();
        $overlays[\SEARCHSPECIALS_TOPOFFERS]->cName = $lang->get('topOffers');
        $overlays[\SEARCHSPECIALS_TOPOFFERS]->cURL  = $self->getURL(\SEARCHSPECIALS_TOPOFFERS);

        $overlays[\SEARCHSPECIALS_UPCOMINGPRODUCTS]        = new stdClass();
        $overlays[\SEARCHSPECIALS_UPCOMINGPRODUCTS]->cName = $lang->get('upcomingProducts');
        $overlays[\SEARCHSPECIALS_UPCOMINGPRODUCTS]->cURL  = $self->getURL(\SEARCHSPECIALS_UPCOMINGPRODUCTS);

        $overlays[\SEARCHSPECIALS_TOPREVIEWS]        = new stdClass();
        $overlays[\SEARCHSPECIALS_TOPREVIEWS]->cName = $lang->get('topReviews');
        $overlays[\SEARCHSPECIALS_TOPREVIEWS]->cURL  = $self->getURL(\SEARCHSPECIALS_TOPREVIEWS);

        return $overlays;
    }

    /**
     * @param int $key
     * @return mixed|string
     * @former baueSuchSpecialURL()
     * @since 5.0.0
     */
    public static function buildURL(int $key)
    {
        $self = new self(Shop::Container()->getDB(), Shop::Container()->getCache());

        return $self->getURL($key);
    }

    /**
     * @param int $type
     * @return string
     */
    public function getURL(int $type): string
    {
        $cacheID = 'bsurl_' . $type . '_' . Shop::getLanguageID();
        if (($url = $this->cache->get($cacheID)) !== false) {
            \executeHook(\HOOK_BOXEN_INC_SUCHSPECIALURL);

            return $url;
        }
        $seo = $this->db->select(
            'tseo',
            'kSprache',
            Shop::getLanguageID(),
            'cKey',
            'suchspecial',
            'kKey',
            $type,
            false,
            'cSeo'
        ) ?? new stdClass();

        $seo->kSuchspecial = $type;
        \executeHook(\HOOK_BOXEN_INC_SUCHSPECIALURL);
        $url = URL::buildURL($seo, \URLART_SEARCHSPECIALS);
        $this->cache->set($cacheID, $url, [\CACHING_GROUP_CATEGORY]);

        return $url;
    }

    /**
     * @return string
     * @former gibVaterSQL()
     * @since 5.0.0
     */
    public static function getParentSQL(): string
    {
        return ' AND tartikel.kVaterArtikel = 0';
    }

    /**
     * @param array $arr
     * @param int   $limit
     * @return array
     * @former randomizeAndLimit()
     * @since 5.0.0
     */
    public static function randomizeAndLimit(array $arr, int $limit = 1): array
    {
        if ($limit < 0) {
            $limit = 0;
        }

        \shuffle($arr);

        return \array_slice($arr, 0, $limit);
    }

    /**
     * @param int $limit
     * @param int $customerGroupID
     * @return int[]
     * @former gibTopAngebote()
     * @since 5.0.0
     */
    public function getTopOffers(int $limit = 20, int $customerGroupID = 0): array
    {
        if (!$customerGroupID) {
            $customerGroupID = CustomerGroup::getDefaultGroupID();
        }
        $cacheID = 'ssp_top_offers_' . $customerGroupID;
        $top     = $this->cache->get($cacheID);
        if ($top === false || !\is_countable($top)) {
            $top = map($this->db->query(
                'SELECT tartikel.kArtikel
                    FROM tartikel
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . "
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.cTopArtikel = 'Y'
                        " . self::getParentSQL() . '
                        ' . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL(),
                ReturnType::ARRAY_OF_OBJECTS
            ), static function ($e) {
                return (int)$e->kArtikel;
            });
            $this->cache->set($cacheID, $top, $this->getCacheTags($top));
        }

        return self::randomizeAndLimit($top, \min(\count($top), $limit));
    }

    /**
     * @param int $limit
     * @param int $customerGroupID
     * @return int[]
     * @former gibBestseller()
     * @since 5.0.0
     */
    public function getBestsellers(int $limit = 20, int $customerGroupID = 0): array
    {
        if (!$customerGroupID) {
            $customerGroupID = CustomerGroup::getDefaultGroupID();
        }
        $config      = Shop::getSettings([\CONF_GLOBAL]);
        $minAmount   = isset($config['global']['global_bestseller_minanzahl'])
            ? (float)$config['global']['global_bestseller_minanzahl']
            : 10;
        $cacheID     = 'ssp_bestsellers_' . $customerGroupID . '_' . $minAmount;
        $bestsellers = $this->cache->get($cacheID);
        if ($bestsellers === false || !\is_countable($bestsellers)) {
            $bestsellers = map($this->db->query(
                'SELECT tartikel.kArtikel, tbestseller.fAnzahl
                    FROM tbestseller, tartikel
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tbestseller.kArtikel = tartikel.kArtikel
                        AND ROUND(tbestseller.fAnzahl) >= ' . $minAmount . '
                        ' . self::getParentSQL() . '
                        ' . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL() . '
                    ORDER BY fAnzahl DESC',
                ReturnType::ARRAY_OF_OBJECTS
            ), static function ($e) {
                return (int)$e->kArtikel;
            });
            $this->cache->set($cacheID, $bestsellers, $this->getCacheTags($bestsellers));
        }

        return self::randomizeAndLimit($bestsellers, \min(\count($bestsellers), $limit));
    }

    /**
     * @param int $limit
     * @param int $customerGroupID
     * @return int[]
     * @former gibSonderangebote()
     * @since 5.0.0
     */
    public function getSpecialOffers(int $limit = 20, int $customerGroupID = 0): array
    {
        if (!$customerGroupID) {
            $customerGroupID = CustomerGroup::getDefaultGroupID();
        }
        $cacheID       = 'ssp_special_offers_' . $customerGroupID;
        $specialOffers = $this->cache->get($cacheID);
        if ($specialOffers === false || !\is_countable($specialOffers)) {
            $specialOffers = map($this->db->query(
                'SELECT tartikel.kArtikel, tsonderpreise.fNettoPreis
                    FROM tartikel
                    JOIN tartikelsonderpreis 
                        ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                    JOIN tsonderpreise 
                        ON tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikelsonderpreis.kArtikel = tartikel.kArtikel
                        AND tsonderpreise.kKundengruppe = ' . $customerGroupID . "
                        AND tartikelsonderpreis.cAktiv = 'Y'
                        AND tartikelsonderpreis.dStart <= NOW()
                        AND (tartikelsonderpreis.dEnde IS NULL OR tartikelsonderpreis.dEnde >= CURDATE())
                        AND (tartikelsonderpreis.nAnzahl < tartikel.fLagerbestand OR tartikelsonderpreis.nIstAnzahl = 0)
                        " . self::getParentSQL() . '
                        ' . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL(),
                ReturnType::ARRAY_OF_OBJECTS
            ), static function ($e) {
                return (int)$e->kArtikel;
            });
            $this->cache->set($cacheID, $specialOffers, $this->getCacheTags($specialOffers), 3600);
        }

        return self::randomizeAndLimit($specialOffers, \min(\count($specialOffers), $limit));
    }

    /**
     * @param int $limit
     * @param int $customerGroupID
     * @return int[]
     * @former gibNeuImSortiment()
     * @since 5.0.0
     */
    public function getNewProducts(int $limit, int $customerGroupID = 0): array
    {
        if (!$customerGroupID) {
            $customerGroupID = CustomerGroup::getDefaultGroupID();
        }
        $config  = Shop::getSettings([\CONF_BOXEN]);
        $days    = ($config['boxen']['box_neuimsortiment_alter_tage'] > 0)
            ? (int)$config['boxen']['box_neuimsortiment_alter_tage']
            : 30;
        $cacheID = 'ssp_new_' . $customerGroupID . '_days';
        $new     = $this->cache->get($cacheID);
        if ($new === false || !\is_countable($new)) {
            $new = map($this->db->query(
                'SELECT tartikel.kArtikel
                    FROM tartikel
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . "
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.cNeu = 'Y'
                        AND DATE_SUB(NOW(), INTERVAL " . $days . ' DAY) < tartikel.dErstellt
                        ' . self::getParentSQL() . '
                        ' . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL(),
                ReturnType::ARRAY_OF_OBJECTS
            ), static function ($e) {
                return (int)$e->kArtikel;
            });
            $this->cache->set($cacheID, $new, $this->getCacheTags($new), 3600);
        }

        return self::randomizeAndLimit($new, \min(\count($new), $limit));
    }

    /**
     * @param int[] $productIDs
     * @return string[]
     */
    private function getCacheTags(array $productIDs): array
    {
        $tags   = map($productIDs, static function (int $id) {
            return \CACHING_GROUP_PRODUCT . '_' . $id;
        });
        $tags[] = \CACHING_GROUP_PRODUCT;
        $tags[] = 'jtl_ssp';

        return $tags;
    }
}
