<?php

namespace JTL\Catalog\Product;

use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Category\MenuItem;
use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Session\Frontend;
use JTL\Shop;
use function Functional\map;

/**
 * Class ArtikelListe
 * @package JTL\Catalog\Product
 */
class ArtikelListe
{
    /**
     * Array mit Artikeln
     *
     * @var array
     */
    public $elemente = [];

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * Holt $anzahl an Top-Angebots Artikeln in die Liste
     *
     * @param string $topneu
     * @param int    $limit wieviele Top-Angebot Artikel geholt werden sollen
     * @param int    $customerGroupID
     * @param int    $languageID
     * @return Artikel[]
     */
    public function getTopNeuArtikel($topneu, int $limit = 3, int $customerGroupID = 0, int $languageID = 0): array
    {
        $this->elemente = [];
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $cacheID = 'jtl_tpnw_' . (\is_string($topneu) ? $topneu : '') .
            '_' . $limit .
            '_' . $languageID .
            '_' . $customerGroupID;
        $items   = Shop::Container()->getCache()->get($cacheID);
        if ($items === false) {
            $qry = ($topneu === 'neu')
                ? "cNeu = 'Y'"
                : "tartikel.cTopArtikel = 'Y'";
            if (!$customerGroupID) {
                $customerGroupID = Frontend::getCustomerGroup()->getID();
            }
            $items = Shop::Container()->getDB()->query(
                'SELECT tartikel.kArtikel
                    FROM tartikel
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND ' . $qry . '
                    ORDER BY rand() LIMIT ' . $limit,
                ReturnType::ARRAY_OF_OBJECTS
            );
            Shop::Container()->getCache()->set($cacheID, $items, [\CACHING_GROUP_CATEGORY]);
        }
        if (\is_array($items)) {
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($items as $item) {
                $product = new Artikel();
                $product->fuelleArtikel((int)$item->kArtikel, $defaultOptions);
                $this->elemente[] = $product;
            }
        }

        return $this->elemente;
    }

    /**
     * Holt (max) $anzahl an Artikeln aus der angegebenen Kategorie in die Liste
     *
     * @param int    $categoryID  Kategorie Key
     * @param int    $limitStart
     * @param int    $limitAnzahl - wieviele Artikel geholt werden sollen. Sind nicht genug in der entsprechenden
     *                            Kategorie enthalten, wird die Maximalanzahl geholt.
     * @param string $order
     * @param int    $customerGroupID
     * @param int    $languageID
     * @return Artikel[]
     */
    public function getArtikelFromKategorie(
        int $categoryID,
        int $limitStart,
        int $limitAnzahl,
        string $order,
        int $customerGroupID = 0,
        int $languageID = 0
    ): array {
        $this->elemente = [];
        if (!$categoryID || !Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        if (!$customerGroupID) {
            $customerGroupID = Frontend::getCustomerGroup()->getID();
        }
        if (!$languageID) {
            $languageID = Shop::getLanguageID();
        }
        $cacheID = 'jtl_top_' . \md5($categoryID . $limitStart . $limitAnzahl . $customerGroupID . $languageID);
        if (($res = Shop::Container()->getCache()->get($cacheID)) !== false) {
            $this->elemente = $res;
        } else {
            $productFilter = Shop::getProductFilter();
            $conditionSQL  = '';
            if ($productFilter !== null && $productFilter->hasManufacturer()) {
                $conditionSQL = ' AND tartikel.kHersteller = ' . $productFilter->getManufacturer()->getValue() . ' ';
            }
            $stockFilterSQL = $productFilter->getFilterSQL()->getStockFilterSQL();
            $items          = Shop::Container()->getDB()->query(
                'SELECT tartikel.kArtikel
                    FROM tkategorieartikel, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . ' ' .
                    Preise::getPriceJoinSql($customerGroupID) . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel ' . $conditionSQL . ' 
                        AND tkategorieartikel.kKategorie = ' . $categoryID . ' ' . $stockFilterSQL . '
                    ORDER BY ' . $order . ', nSort
                    LIMIT ' . $limitStart . ', ' . $limitAnzahl,
                ReturnType::ARRAY_OF_OBJECTS
            );
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($items as $item) {
                $this->elemente[] = (new Artikel())->fuelleArtikel((int)$item->kArtikel, $defaultOptions);
            }
            Shop::Container()->getCache()->set(
                $cacheID,
                $this->elemente,
                [\CACHING_GROUP_CATEGORY, \CACHING_GROUP_CATEGORY . '_' . $categoryID]
            );
        }

        return $this->elemente;
    }

    /**
     * @param array $productIDs
     * @param int   $start
     * @param int   $limit
     * @return Artikel[]
     */
    public function getArtikelByKeys(array $productIDs, int $start, int $limit): array
    {
        $this->elemente = [];
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $cnt            = \count($productIDs);
        $total          = 0;
        $defaultOptions = Artikel::getDefaultOptions();
        for ($i = $start; $i < $cnt; $i++) {
            $product = (new Artikel())->fuelleArtikel($productIDs[$i], $defaultOptions);
            if ($product !== null && $product->kArtikel > 0) {
                ++$total;
                $this->elemente[] = $product;
            }
            if ($total >= $limit) {
                break;
            }
        }

        return $this->elemente;
    }

    /**
     * @param KategorieListe $categoryList
     * @return Artikel[]
     */
    public function holeTopArtikel($categoryList): array
    {
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $categoryIDs = [];
        if (!empty($categoryList->elemente)) {
            foreach ($categoryList->elemente as $i => $category) {
                /** @var MenuItem $category */
                $categoryIDs[] = $category->getID();
                if ($category->hasChildren()) {
                    foreach ($category->getChildren() as $level2) {
                        /** @var MenuItem $level2 */
                        $categoryIDs[] = $level2->getID();
                    }
                }
            }
        }
        $cacheID = 'hTA_' . \md5(\json_encode($categoryIDs));
        $items   = Shop::Container()->getCache()->get($cacheID);
        if ($items === false && \count($categoryIDs) > 0) {
            $conf            = Shop::getSettings([\CONF_ARTIKELUEBERSICHT]);
            $customerGroupID = Frontend::getCustomerGroup()->getID();
            $limitSql        = isset($conf['artikeluebersicht']['artikelubersicht_topbest_anzahl'])
                ? ('LIMIT ' . (int)$conf['artikeluebersicht']['artikelubersicht_topbest_anzahl'])
                : 'LIMIT 6';
            $stockFilterSQL  = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $items           = Shop::Container()->getDB()->query(
                'SELECT DISTINCT (tartikel.kArtikel)
                    FROM tkategorieartikel, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . ' ' .
                    Preise::getPriceJoinSql($customerGroupID) . " 
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        AND tartikel.cTopArtikel = 'Y'
                        AND (tkategorieartikel.kKategorie IN (" . \implode(', ', $categoryIDs) . ')) ' .
                        $stockFilterSQL . '  ORDER BY rand() ' . $limitSql,
                ReturnType::ARRAY_OF_OBJECTS
            );
            $cacheTags       = [\CACHING_GROUP_CATEGORY, \CACHING_GROUP_OPTION];
            foreach ($categoryIDs as $id) {
                $cacheTags[] = \CACHING_GROUP_CATEGORY . '_' . $id;
            }
            Shop::Container()->getCache()->set($cacheID, $items, $cacheTags);
        }
        if ($items === false) {
            return $this->elemente;
        }
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($items as $obj) {
            $this->elemente[] = (new Artikel())->fuelleArtikel((int)$obj->kArtikel, $defaultOptions);
        }

        return $this->elemente;
    }

    /**
     * @param Kategorieliste    $categoryList
     * @param ArtikelListe|null $topProductsList
     * @return Artikel[]
     */
    public function holeBestsellerArtikel($categoryList, $topProductsList = null): array
    {
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $categoryIDs = [];
        if (GeneralObject::isCountable('elemente', $categoryList)) {
            foreach ($categoryList->elemente as $i => $category) {
                /** @var MenuItem $category */
                $categoryIDs[] = $category->getID();
                if ($category->hasChildren()) {
                    foreach ($category->getChildren() as $level2) {
                        /** @var MenuItem $level2 */
                        $categoryIDs[] = $level2->getID();
                    }
                }
            }
        }
        $keys = null;
        if ($topProductsList instanceof self) {
            $keys = map($topProductsList->elemente, static function ($e) {
                return $e->cacheID ?? 0;
            });
        }
        $cacheID = 'hBsA_' . \md5(\json_encode($categoryIDs) . \json_encode($keys));
        $items   = Shop::Container()->getCache()->get($cacheID);
        if ($items === false && \count($categoryIDs) > 0) {
            $customerGroupID = Frontend::getCustomerGroup()->getID();
            // top artikel nicht nochmal in den bestsellen vorkommen lassen
            $excludes = '';
            if (GeneralObject::isCountable('elemente', $topProductsList)) {
                $exclude  = map($topProductsList->elemente, static function ($e) {
                    return (int)$e->kArtikel;
                });
                $excludes = \count($exclude) > 0
                    ? ' AND tartikel.kArtikel NOT IN (' . \implode(',', $exclude) . ') '
                    : '';
            }
            $conf           = Shop::getSettings([\CONF_ARTIKELUEBERSICHT]);
            $limitSQL       = isset($conf['artikeluebersicht']['artikelubersicht_topbest_anzahl'])
                ? ('LIMIT ' . (int)$conf['artikeluebersicht']['artikelubersicht_topbest_anzahl'])
                : 'LIMIT 6';
            $stockFilterSQL = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $items          = Shop::Container()->getDB()->query(
                'SELECT DISTINCT (tartikel.kArtikel)
                    FROM tkategorieartikel, tbestseller, tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . ' ' .
                    Preise::getPriceJoinSql($customerGroupID) . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL' . $excludes . '
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        AND tartikel.kArtikel = tbestseller.kArtikel
                        AND (tkategorieartikel.kKategorie IN (' . \implode(', ', $categoryIDs) . ')) ' .
                        $stockFilterSQL . '
                    ORDER BY tbestseller.fAnzahl DESC ' . $limitSQL,
                ReturnType::ARRAY_OF_OBJECTS
            );
            $cacheTags      = [\CACHING_GROUP_CATEGORY, \CACHING_GROUP_OPTION];
            foreach ($categoryIDs as $id) {
                $cacheTags[] = \CACHING_GROUP_CATEGORY . '_' . $id;
            }
            Shop::Container()->getCache()->set($cacheID, $items, $cacheTags);
        }
        if (\is_array($items)) {
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($items as $item) {
                $this->elemente[] = (new Artikel())->fuelleArtikel((int)$item->kArtikel, $defaultOptions);
            }
        }

        return $this->elemente;
    }
}
