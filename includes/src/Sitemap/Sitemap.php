<?php

namespace JTL\Sitemap;

use JTL\Cache\JTLCacheInterface;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Hersteller;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Category as CategoryHelper;
use JTL\Helpers\URL;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;

/**
 * Class Sitemap
 * @package JTL\Sitemap
 */
class Sitemap
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var array
     */
    private $conf;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var int
     */
    private $langID;

    /**
     * @var int
     */
    private $customerGroupID;

    /**
     * Sitemap constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param array             $conf
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache, array $conf)
    {
        $this->db              = $db;
        $this->cache           = $cache;
        $this->conf            = $conf;
        $this->langID          = Shop::getLanguageID();
        $this->customerGroupID = Frontend::getCustomerGroup()->getID();
    }

    /**
     * @param JTLSmarty $smarty
     */
    public function assignData(JTLSmarty $smarty): void
    {
        $smarty->assign('oKategorieliste', $this->getCategories())
               ->assign('oHersteller_arr', $this->getManufacturers())
               ->assign('oNewsMonatsUebersicht_arr', $this->getNews())
               ->assign('oNewsKategorie_arr', $this->getNewsCategories());
    }

    /**
     * @return KategorieListe
     */
    private function getCategories(): KategorieListe
    {
        $catList           = new KategorieListe();
        $catList->elemente = $this->conf['sitemap']['sitemap_kategorien_anzeigen'] === 'Y'
            ? CategoryHelper::getInstance()->combinedGetAll()
            : [];

        return $catList;
    }

    /**
     * @return array
     */
    public function getNewsCategories(): array
    {
        if ($this->conf['sitemap']['sitemap_newskategorien_anzeigen'] !== 'Y') {
            return [];
        }
        $cacheID = 'news_category_' . $this->langID . '_' . $this->customerGroupID;
        if (($newsCategories = $this->cache->get($cacheID)) === false) {
            $newsCategories = $this->db->queryPrepared(
                "SELECT tnewskategorie.kNewsKategorie, t.languageID AS kSprache, t.name AS cName,
                t.description AS cBeschreibung, t.metaTitle AS cMetaTitle, t.metaDescription AS cMetaDescription,
                tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung, 
                tnewskategorie.cPreviewImage, tseo.cSeo,
                COUNT(DISTINCT(tnewskategorienews.kNews)) AS nAnzahlNews
                    FROM tnewskategorie
                    JOIN tnewskategoriesprache t 
                        ON tnewskategorie.kNewsKategorie = t.kNewsKategorie
                    LEFT JOIN tnewskategorienews 
                        ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                    LEFT JOIN tnews 
                        ON tnews.kNews = tnewskategorienews.kNews
                    LEFT JOIN tseo 
                        ON tseo.cKey = 'kNewsKategorie'
                        AND tseo.kKey = tnewskategorie.kNewsKategorie
                        AND tseo.kSprache = :lid
                    WHERE t.languageID = :lid
                        AND tnewskategorie.nAktiv = 1
                        AND tnews.nAktiv = 1
                        AND tnews.dGueltigVon <= NOW()
                        AND (tnews.cKundengruppe LIKE '%;-1;%' 
                            OR FIND_IN_SET(:cgid, REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                    GROUP BY tnewskategorienews.kNewsKategorie
                    ORDER BY tnewskategorie.nSort DESC",
                [
                    'lid'  => $this->langID,
                    'cgid' => $this->customerGroupID
                ],
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($newsCategories as $newsCategory) {
                $newsCategory->cURL     = URL::buildURL($newsCategory, \URLART_NEWSKATEGORIE);
                $newsCategory->cURLFull = URL::buildURL($newsCategory, \URLART_NEWSKATEGORIE, true);

                $entries = $this->db->queryPrepared(
                    "SELECT tnews.kNews, t.languageID AS kSprache, tnews.cKundengruppe, t.title AS cBetreff, 
                    t.content AS cText, t.preview AS cVorschauText, t.metaTitle AS cMetaTitle, 
                    t.metaDescription AS cMetaDescription, t.metaKeywords AS cMetaKeywords, 
                    tnews.nAktiv, tnews.dErstellt, tseo.cSeo, 
                    DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y  %H:%i') AS dGueltigVon_de
                        FROM tnews
                        JOIN tnewssprache t 
                            ON tnews.kNews = t.kNews
                        JOIN tnewskategorienews 
                            ON tnewskategorienews.kNews = tnews.kNews
                        LEFT JOIN tseo 
                            ON tseo.cKey = 'kNews'
                            AND tseo.kKey = tnews.kNews
                            AND tseo.kSprache = :lid
                        WHERE t.languageID = :lid
                            AND tnewskategorienews.kNewsKategorie = :cid
                            AND tnews.nAktiv = 1
                            AND tnews.dGueltigVon <= NOW()
                            AND (tnews.cKundengruppe LIKE '%;-1;%' 
                                OR FIND_IN_SET(:cgid, REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                        GROUP BY tnews.kNews
                        ORDER BY tnews.dGueltigVon DESC",
                    [
                        'lid'  => $this->langID,
                        'cgid' => $this->customerGroupID,
                        'cid'  => (int)$newsCategory->kNewsKategorie
                    ],
                    ReturnType::ARRAY_OF_OBJECTS
                );
                foreach ($entries as $entry) {
                    $entry->cURL     = URL::buildURL($entry, \URLART_NEWS);
                    $entry->cURLFull = URL::buildURL($entry, \URLART_NEWS, true);
                }
                $newsCategory->oNews_arr = $entries;
            }
            $this->cache->set($cacheID, $newsCategories, [\CACHING_GROUP_NEWS]);
        }

        return $newsCategories;
    }

    /**
     * @return array
     */
    public function getNews(): array
    {
        if ($this->conf['news']['news_benutzen'] !== 'Y') {
            return [];
        }
        $cacheID = 'sitemap_news_' . $this->langID;
        if (($overview = $this->cache->get($cacheID)) === false) {
            $overview = $this->db->queryPrepared(
                "SELECT tseo.cSeo, tnewsmonatsuebersicht.cName, tnewsmonatsuebersicht.kNewsMonatsUebersicht, 
                MONTH(tnews.dGueltigVon) AS nMonat, YEAR(tnews.dGueltigVon) AS nJahr, COUNT(*) AS nAnzahl
                    FROM tnews
                    JOIN tnewssprache t 
                        ON tnews.kNews = t.kNews
                    JOIN tnewsmonatsuebersicht 
                        ON tnewsmonatsuebersicht.nMonat = MONTH(tnews.dGueltigVon)
                        AND tnewsmonatsuebersicht.nJahr = YEAR(tnews.dGueltigVon)
                        AND tnewsmonatsuebersicht.kSprache = :lid
                    LEFT JOIN tseo 
                        ON cKey = 'kNewsMonatsUebersicht'
                        AND kKey = tnewsmonatsuebersicht.kNewsMonatsUebersicht
                        AND tseo.kSprache = :lid
                    WHERE tnews.dGueltigVon < NOW()
                        AND tnews.nAktiv = 1
                        AND t.languageID = :lid
                    GROUP BY YEAR(tnews.dGueltigVon), MONTH(tnews.dGueltigVon)
                    ORDER BY tnews.dGueltigVon DESC",
                ['lid' => $this->langID],
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($overview as $news) {
                $entries = $this->db->queryPrepared(
                    "SELECT tnews.kNews, t.languageID AS kSprache, tnews.cKundengruppe, 
                    t.title AS cBetreff, t.content AS cText, t.preview AS cVorschauText, 
                    t.metaTitle AS cMetaTitle, t.metaDescription AS cMetaDescription, t.metaKeywords AS cMetaKeywords,
                    tnews.nAktiv, tnews.dErstellt, tseo.cSeo,
                    COUNT(tnewskommentar.kNewsKommentar) AS nNewsKommentarAnzahl, 
                    DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y  %H:%i') AS dGueltigVon_de
                        FROM tnews
                        JOIN tnewssprache t 
                            ON tnews.kNews = t.kNews
                        LEFT JOIN tnewskommentar 
                            ON tnews.kNews = tnewskommentar.kNews
                        LEFT JOIN tseo 
                            ON tseo.cKey = 'kNews'
                            AND tseo.kKey = tnews.kNews
                            AND tseo.kSprache = :lid
                        WHERE t.languageID = :lid
                            AND tnews.nAktiv = 1
                            AND (tnews.cKundengruppe LIKE '%;-1;%' 
                                OR FIND_IN_SET(:cgid, REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                            AND (MONTH(tnews.dGueltigVon) = :mnth)  
                            AND (tnews.dGueltigVon <= NOW())
                            AND (YEAR(tnews.dGueltigVon) = :yr) 
                            AND (tnews.dGueltigVon <= NOW())
                        GROUP BY tnews.kNews
                        ORDER BY dGueltigVon DESC",
                    [
                        'lid'  => $this->langID,
                        'cgid' => $this->customerGroupID,
                        'mnth' => $news->nMonat,
                        'yr'   => $news->nJahr
                    ],
                    ReturnType::ARRAY_OF_OBJECTS
                );
                foreach ($entries as $oNews) {
                    $oNews->cURL     = URL::buildURL($oNews, \URLART_NEWS);
                    $oNews->cURLFull = URL::buildURL($oNews, \URLART_NEWS, true);
                }
                $news->oNews_arr = $entries;
                $news->cURL      = URL::buildURL($news, \URLART_NEWSMONAT);
                $news->cURLFull  = URL::buildURL($news, \URLART_NEWSMONAT, true);
            }
            $this->cache->set($cacheID, $overview, [\CACHING_GROUP_NEWS]);
        }

        return $overview;
    }

    /**
     * @return array
     */
    public function getManufacturers(): array
    {
        return $this->conf['sitemap']['sitemap_hersteller_anzeigen'] === 'Y'
            ? Hersteller::getAll()
            : [];
    }
}
