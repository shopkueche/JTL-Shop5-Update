<?php

use JTL\Boxes\Items\AbstractBox;
use JTL\DB\ReturnType;
use JTL\Filter\Metadata;
use JTL\Filter\NavigationURLs;
use JTL\Filter\Pagination\Info;
use JTL\Filter\ProductFilter;
use JTL\Filter\SearchResults;
use JTL\Filter\SearchResultsInterface;
use JTL\Helpers\Text;
use JTL\Mapper\SortingType;
use JTL\Session\Frontend;
use JTL\Shop;

require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return SearchResultsInterface
 * @deprecated since 5.0.0
 */
function buildSearchResults($FilterSQL, $NaviFilter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->generateSearchResults();
}

/**
 * @deprecated since 5.0.0
 */
function buildSearchResultPage()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
}

/**
 * @param object   $FilterSQL
 * @param int      $nArtikelProSeite
 * @param object   $NaviFilter
 * @return SearchResultsInterface
 * @deprecated since 5.0.0
 */
function gibArtikelKeys($FilterSQL, $nArtikelProSeite, $NaviFilter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->generateSearchResults(null, true, (int)$nArtikelProSeite);
}

/**
 * @param stdClass|ProductFilter $NaviFilter
 * @return ProductFilter
 */
function updateNaviFilter($NaviFilter)
{
    if (get_class($NaviFilter) === 'stdClass') {
        $NaviFilter = Shop::buildProductFilter(extractParameters($NaviFilter), $NaviFilter);
    }

    return $NaviFilter;
}

/**
 * @param stdClass $NaviFilter
 * @return array
 */
function extractParameters($NaviFilter)
{
    $params = [];
    if (!empty($NaviFilter->Kategorie->kKategorie)) {
        $params['kKategorie'] = (int)$NaviFilter->Kategorie->kKategorie;
    }
    if (!empty($NaviFilter->KategorieFilter->kKategorie)) {
        $params['kKategorieFilter'] = (int)$NaviFilter->Kategorie->kKategorie;
    }
    if (!empty($NaviFilter->Hersteller->kHersteller)) {
        $params['kHersteller'] = (int)$NaviFilter->Hersteller->kHersteller;
    }
    if (!empty($NaviFilter->HerstellerFilter->kHersteller)) {
        $params['kHerstellerFilter'] = (int)$NaviFilter->HerstellerFilter->kHersteller;
    }
    if (!empty($NaviFilter->kSeite)) {
        $params['kSeite'] = (int)$NaviFilter->kSeite;
    }
    if (!empty($NaviFilter->kSuchanfrage)) {
        $params['kSuchanfrage'] = (int)$NaviFilter->kSuchanfrage;
    }
    if (!empty($NaviFilter->MerkmalWert->kMerkmalWert)) {
        $params['kMerkmalWert'] = (int)$NaviFilter->MerkmalWert->kMerkmalWert;
    }
    if (!empty($NaviFilter->PreisspannenFilter->fVon) && !empty($NaviFilter->PreisspannenFilter->fBis)) {
        $params['cPreisspannenFilter'] = $NaviFilter->PreisspannenFilter->fVon .
            '_' . $NaviFilter->PreisspannenFilter->fBis;
    }
    if (!empty($NaviFilter->SuchspecialFilter->kKey)) {
        $params['kSuchspecialFilter'] = (int)$NaviFilter->SuchspecialFilter->kKey;
    }
    if (!empty($NaviFilter->Suchspecial->kKey)) {
        $params['kSuchspecial'] = (int)$NaviFilter->Suchspecial->kKey;
    }
    if (!empty($NaviFilter->nSortierung)) {
        $params['nSortierung'] = (int)$NaviFilter->nSortierung;
    }
    if (!empty($NaviFilter->MerkmalFilter) && is_array($NaviFilter->MerkmalFilter)) {
        foreach ($NaviFilter->MerkmalFilter as $mf) {
            $params['MerkmalFilter_arr'] = (int)$mf->kMerkmalWert;
        }
    }
    if (!empty($NaviFilter->SuchFilter) && is_array($NaviFilter->SuchFilter)) {
        foreach ($NaviFilter->SuchFilter as $sf) {
            $params['SuchFilter_arr'] = (int)$sf->kSuchanfrage;
        }
    }
    if (!empty($NaviFilter->nAnzahlProSeite)) {
        $params['nArtikelProSeite'] = (int)$NaviFilter->nAnzahlProSeite;
    }
    if (!empty($NaviFilter->Suche->cSuche)) {
        $params['cSuche'] = $NaviFilter->Suche->cSuche;
    }

    return $params;
}

/**
 * @param object $NaviFilter
 * @return int
 * @deprecated since 5.0.0
 */
function gibAnzahlFilter($NaviFilter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getFilterCount();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibHerstellerFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getManufacturerFilter()->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibKategorieFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getCategoryFilter()->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibSuchFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->searchFilterCompat->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibBewertungSterneFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getRatingFilter()->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibPreisspannenFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getPriceRangeFilter()->getOptions();
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibTagFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Functionalitiy of product tags was removed in 5.0.0',
        E_USER_DEPRECATED
    );
    return [];
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return string
 * @deprecated since 5.0.0
 */
function gibSuchFilterJSONOptionen($FilterSQL, $NaviFilter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $searchFilters = gibSuchFilterOptionen($FilterSQL, $NaviFilter); // cURL
    foreach ($searchFilters as $key => $sf) {
        $searchFilters[$key]->cURL = Text::htmlentitydecode($sf->cURL);
    }

    return AbstractBox::getJSONString($searchFilters);
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return string
 * @deprecated since 5.0.0
 */
function gibTagFilterJSONOptionen($FilterSQL, $NaviFilter)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Functionalitiy of product tags was removed in 5.0.0',
        E_USER_DEPRECATED
    );
    return '';
}

/**
 * @param object         $FilterSQL
 * @param object         $NaviFilter
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibMerkmalFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getCharacteristicFilterCollection()->getOptions();
}

/**
 * @deprecated since 5.0.0
 * @param object $a
 * @param object $b
 * @return int
 */
function sortierMerkmalWerteNumerisch($a, $b)
{
    if ($a == $b) {
        return 0;
    }

    return ($a->cWert < $b->cWert) ? -1 : 1;
}

/**
 * @deprecated since 5.0.0
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 */
function gibSuchspecialFilterOptionen($FilterSQL, $NaviFilter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->searchFilterCompat->getOptions();
}

/**
 * @deprecated since 5.0.0
 * @param object $NaviFilter
 * @param int    $kSpracheExt
 * @return int
 */
function bearbeiteSuchCache($NaviFilter, $kSpracheExt = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getSearchQuery()->editSearchCache((int)$kSpracheExt);
}

/**
 * @deprecated since 5.0.0
 */
function gibSuchFilterSQL()
{
    trigger_error(__FUNCTION__ . ' is deprecated and will do nothing.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 */
function gibHerstellerFilterSQL()
{
    trigger_error(__FUNCTION__ . ' is deprecated and will do nothing.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 */
function gibKategorieFilterSQL()
{
    trigger_error(__FUNCTION__ . ' is deprecated and will do nothing.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 */
function gibBewertungSterneFilterSQL()
{
    trigger_error(__FUNCTION__ . ' is deprecated and will do nothing.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 */
function gibPreisspannenFilterSQL()
{
    trigger_error(__FUNCTION__ . ' is deprecated and will do nothing.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 */
function gibTagFilterSQL()
{
    trigger_error(__FUNCTION__ . ' is deprecated and will do nothing.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 */
function gibMerkmalFilterSQL()
{
    trigger_error(__FUNCTION__ . ' is deprecated and will do nothing.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 */
function gibSuchspecialFilterSQL()
{
    trigger_error(__FUNCTION__ . ' is deprecated and will do nothing.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 */
function gibArtikelAttributFilterSQL()
{
    trigger_error(__FUNCTION__ . ' is deprecated and will do nothing.', E_USER_DEPRECATED);
}

/**
 * @return int
 * @deprecated since 5.0.0
 */
function gibMerkmalPosition()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return -1;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function checkMerkmalWertVorhanden()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param object $NaviFilter
 * @return string
 * @deprecated since 5.0.0
 */
function gibArtikelsortierung($NaviFilter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getFilterSQL()->getOrder()->orderBy;
}

/**
 * @param string|int $nUsersortierung
 * @return int
 * @deprecated since 5.0.0
 */
function mappeUsersortierung($nUsersortierung)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $mapper = new SortingType();
    return $mapper->mapUserSorting($nUsersortierung);
}

/**
 * @param object $NaviFilter
 * @param bool   $bSeo
 * @param object $oZusatzFilter
 * @param int    $languageID
 * @param bool   $bCanonical
 * @return string
 */
function gibNaviURL($NaviFilter, $bSeo, $oZusatzFilter, $languageID = 0, $bCanonical = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return updateNaviFilter($NaviFilter)->getFilterURL()->getURL($oZusatzFilter, $bCanonical);
}

/**
 * @param object            $oPreis
 * @param object|array|null $priceRangeFilter
 * @return string
 * @deprecated since 5.0.0
 */
function berechnePreisspannenSQL($oPreis, $priceRangeFilter = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()
               ->getPriceRangeFilter()
               ->getPriceRangeSQL($oPreis, Frontend::getCurrency(), $priceRangeFilter);
}

/**
 * @param float $fMax
 * @param float $fMin
 * @return stdClass
 */
function berechneMaxMinStep($fMax, $fMin)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getPriceRangeFilter()->calculateSteps($fMax, $fMin);
}

/**
 * @return null|string
 * @deprecated since 5.0.0
 */
function gibBrotNaviName()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $md = Shop::getProductFilter()->getMetaData();
    $md->getHeader();

    return $md->getBreadCrumb();
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function gibHeaderAnzeige()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getMetaData()->getHeader();
}

/**
 * @deprecated since 5.0.0
 * @param bool   $bSeo
 * @param object $oSuchergebnisse
 */
function erstelleFilterLoesenURLs($bSeo, $oSuchergebnisse)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $sr = new SearchResults();
    $sr->convert($oSuchergebnisse);
    Shop::getProductFilter()->getFilterURL()->createUnsetFilterURLs(
        new NavigationURLs(),
        $sr
    );
}

/**
 * @deprecated since 5.0.0
 * @param string $cTitle
 * @return string
 * @deprecated since 5.0.0
 */
function truncateMetaTitle($cTitle)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return (new Metadata(Shop::getProductFilter()))->truncateMetaTitle($cTitle);
}

/**
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @param array  $globalMeta
 * @return string
 * @deprecated since 5.0.0
 */
function gibNaviMetaTitle($NaviFilter, $oSuchergebnisse, $globalMeta)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $sr = new SearchResults();
    $sr->convert($oSuchergebnisse);

    return (new Metadata(updateNaviFilter($NaviFilter)))->generateMetaTitle(
        $sr,
        $globalMeta
    );
}

/**
 * @param array  $products
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @param array  $globalMeta
 * @return string
 * @deprecated since 5.0.0
 */
function gibNaviMetaDescription($products, $NaviFilter, $oSuchergebnisse, $globalMeta)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $sr = new SearchResults();
    $sr->convert($oSuchergebnisse);

    return (new Metadata(updateNaviFilter($NaviFilter)))->generateMetaDescription(
        $products,
        $sr,
        $globalMeta
    );
}

/**
 * @param array  $products
 * @param object $NaviFilter
 * @return mixed|string
 * @deprecated since 5.0.0
 */
function gibNaviMetaKeywords($products, $NaviFilter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return (new Metadata(updateNaviFilter($NaviFilter)))->generateMetaKeywords($products);
}

/**
 * Baut für die NaviMetas die gesetzten Mainwords + Filter und stellt diese vor jedem Meta vorne an.
 *
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @return string
 * @deprecated since 5.0.0
 */
function gibMetaStart($NaviFilter, $oSuchergebnisse)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $pf = updateNaviFilter($NaviFilter);
    $sr = new SearchResults();
    $sr->convert($oSuchergebnisse);
    return (new Metadata($pf))->getMetaStart($sr);
}

/**
 * @deprecated since 5.0.0
 * @return int
 */
function gibSuchanfrageKey()
{
    trigger_error(__FUNCTION__ . ' is deprecated and will do nothing.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @deprecated since 5.0.0
 * @param object $NaviFilter
 */
function setzeUsersortierung($NaviFilter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    global $AktuelleKategorie;
    updateNaviFilter($NaviFilter)->setUserSort($AktuelleKategorie);
}

/**
 * @deprecated since 5.0.0
 * @param array  $conf
 * @param object $NaviFilter
 * @param int    $nDarstellung
 */
function gibErweiterteDarstellung($conf, $NaviFilter, $nDarstellung = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    updateNaviFilter($NaviFilter)->getMetaData()->getExtendedView($nDarstellung);
    if (isset($_SESSION['oErweiterteDarstellung'])) {
        Shop::Smarty()->assign('oErweiterteDarstellung', $_SESSION['oErweiterteDarstellung']);
    }
}

/**
 * @deprecated since 5.0.0
 * @param object productFilter
 * @param bool   $seo
 * @param object $pages
 * @param int    $maxPages
 * @param string $filterURL
 * @return array
 */
function baueSeitenNaviURL($NaviFilter, $seo, $pages, $maxPages = 7, $filterURL = '')
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $productFilter = updateNaviFilter($NaviFilter);
    if (is_a($pages, 'stdClass')) {
        $p = new Info();
        $p->setMaxPage($pages->maxSeite);
        $p->setMinPage($pages->minSeite);
        $p->setTotalPages($pages->maxSeiten);
        $p->setCurrentPage($pages->AktuelleSeite);
        $pages = $p;
    }
    if (mb_strlen($filterURL) > 0) {
        $seo = false;
    }
    $res     = [];
    $naviURL = $productFilter->getFilterURL()->getURL();
    $seo     = $seo && mb_strpos($naviURL, '?') === false;
    if ($pages->getTotalPages() > 0 && $pages->getCurrentPage() > 0) {
        $nMax = (int)floor($maxPages / 2);
        if ($pages->getTotalPages() > $maxPages) {
            if ($pages->getCurrentPage() - $nMax >= 1) {
                $nDiff = 0;
                $nVon  = $pages->getCurrentPage() - $nMax;
            } else {
                $nVon  = 1;
                $nDiff = $nMax - $pages->getCurrentPage() + 1;
            }
            if ($pages->getCurrentPage() + $nMax + $nDiff <= $pages->getTotalPages()) {
                $nBis = $pages->getCurrentPage() + $nMax + $nDiff;
            } else {
                $nDiff = $pages->getCurrentPage() + $nMax - $pages->getTotalPages();
                if ($nDiff === 0) {
                    $nVon -= ($maxPages - ($nMax + 1));
                } elseif ($nDiff > 0) {
                    $nVon = $pages->getCurrentPage() - $nMax - $nDiff;
                }
                $nBis = $pages->getTotalPages();
            }
            // Laufe alle Seiten durch und baue URLs + Seitenzahl
            for ($i = $nVon; $i <= $nBis; ++$i) {
                $oSeite         = new stdClass();
                $oSeite->nSeite = $i;
                if ($i === $pages->getCurrentPage()) {
                    $oSeite->cURL = '';
                } elseif ($oSeite->nSeite === 1) {
                    $oSeite->cURL = $naviURL . $filterURL;
                } elseif ($seo) {
                    $cURL         = $naviURL;
                    $oSeite->cURL = mb_strpos(basename($cURL), 'index.php') !== false
                        ? $cURL . '&amp;seite=' . $oSeite->nSeite . $filterURL
                        : $cURL . SEP_SEITE . $oSeite->nSeite;
                } else {
                    $oSeite->cURL = $naviURL . '&amp;seite=' . $oSeite->nSeite . $filterURL;
                }
                $res[] = $oSeite;
            }
        } else {
            // Laufe alle Seiten durch und baue URLs + Seitenzahl
            for ($i = 0; $i < $pages->getTotalPages(); ++$i) {
                $oSeite         = new stdClass();
                $oSeite->nSeite = $i + 1;

                if ($i + 1 === $pages->getCurrentPage()) {
                    $oSeite->cURL = '';
                } elseif ($oSeite->nSeite === 1) {
                    $oSeite->cURL = $naviURL . $filterURL;
                } elseif ($seo) {
                    $cURL         = $naviURL;
                    $oSeite->cURL = mb_strpos(basename($cURL), 'index.php') !== false
                        ? $cURL . '&amp;seite=' . $oSeite->nSeite . $filterURL
                        : $cURL . SEP_SEITE . $oSeite->nSeite;
                } else {
                    $oSeite->cURL = $naviURL . '&amp;seite=' . $oSeite->nSeite . $filterURL;
                }
                $res[] = $oSeite;
            }
        }
        // Baue Zurück-URL
        $res['zurueck']       = new stdClass();
        $res['zurueck']->nBTN = 1;
        if ($pages->getCurrentPage() > 1) {
            $res['zurueck']->nSeite = $pages->getCurrentPage() - 1;
            if ($res['zurueck']->nSeite === 1) {
                $res['zurueck']->cURL = $naviURL . $filterURL;
            } elseif ($seo) {
                $cURL = $naviURL;
                if (mb_strpos(basename($cURL), 'index.php') !== false) {
                    $res['zurueck']->cURL = $cURL . '&amp;seite=' .
                        $res['zurueck']->nSeite . $filterURL;
                } else {
                    $res['zurueck']->cURL = $cURL . SEP_SEITE .
                        $res['zurueck']->nSeite;
                }
            } else {
                $res['zurueck']->cURL = $naviURL . '&amp;seite=' .
                    $res['zurueck']->nSeite . $filterURL;
            }
        }
        // Baue Vor-URL
        $res['vor']       = new stdClass();
        $res['vor']->nBTN = 1;
        if ($pages->getCurrentPage() < $pages->getMaxPage()) {
            $res['vor']->nSeite = $pages->getCurrentPage() + 1;
            if ($seo) {
                $cURL = $naviURL;
                if (mb_strpos(basename($cURL), 'index.php') !== false) {
                    $res['vor']->cURL = $cURL . '&amp;seite=' . $res['vor']->nSeite . $filterURL;
                } else {
                    $res['vor']->cURL = $cURL . SEP_SEITE . $res['vor']->nSeite;
                }
            } else {
                $res['vor']->cURL = $naviURL . '&amp;seite=' . $res['vor']->nSeite . $filterURL;
            }
        }
    }

    return $res;
}

/**
 * @throws Exception
 * @deprecated since 5.0.0
 */
function bearbeiteSuchCacheFulltext()
{
    trigger_error(__FUNCTION__ . ' is deprecated and will do nothing.', E_USER_DEPRECATED);
}

/**
 * @throws Exception
 * @deprecated since 5.0.0
 */
function isFulltextIndexActive()
{
    trigger_error(__FUNCTION__ . ' is deprecated and will do nothing.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 * @param object $a
 * @param object $b
 * @return int
 */
function sortierKategoriepfade($a, $b)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return strcmp($a->cName, $b->cName);
}

/**
 * @param null|array $conf
 * @param bool $bExtendedJTLSearch
 * @return array
 * @deprecated since 5.0.0
 */
function gibSortierliste($conf = null, $bExtendedJTLSearch = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $conf           = $conf ?? Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
    $sortingOptions = [];
    $search         = [];
    if ($bExtendedJTLSearch !== false) {
        static $names     = [
            'suche_sortierprio_name',
            'suche_sortierprio_name_ab',
            'suche_sortierprio_preis',
            'suche_sortierprio_preis_ab'
        ];
        static $values    = [
            SEARCH_SORT_NAME_ASC,
            SEARCH_SORT_NAME_DESC,
            SEARCH_SORT_PRICE_ASC,
            SEARCH_SORT_PRICE_DESC
        ];
        static $languages = ['sortNameAsc', 'sortNameDesc', 'sortPriceAsc', 'sortPriceDesc'];
        foreach ($names as $i => $name) {
            $obj                  = new stdClass();
            $obj->name            = $name;
            $obj->value           = $values[$i];
            $obj->angezeigterName = Shop::Lang()->get($languages[$i]);

            $sortingOptions[] = $obj;
        }

        return $sortingOptions;
    }
    while (($obj = gibNextSortPrio($search, $conf)) !== null) {
        $search[] = $obj->name;
        unset($obj->name);
        $sortingOptions[] = $obj;
    }

    return $sortingOptions;
}

/**
 * @deprecated since 5.0.0
 * @param array $search
 * @param null|array $conf
 * @return null|stdClass
 */
function gibNextSortPrio($search, $conf = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $conf = $conf ?? Shop::getConfig([CONF_ARTIKELUEBERSICHT]);
    $max  = 0;
    $obj  = null;
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_name']
        && !in_array('suche_sortierprio_name', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_name';
        $obj->value           = SEARCH_SORT_NAME_ASC;
        $obj->angezeigterName = Shop::Lang()->get('sortNameAsc');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_name'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_name_ab']
        && !in_array('suche_sortierprio_name_ab', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_name_ab';
        $obj->value           = SEARCH_SORT_NAME_DESC;
        $obj->angezeigterName = Shop::Lang()->get('sortNameDesc');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_name_ab'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_preis']
        && !in_array('suche_sortierprio_preis', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_preis';
        $obj->value           = SEARCH_SORT_PRICE_ASC;
        $obj->angezeigterName = Shop::Lang()->get('sortPriceAsc');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_preis'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_preis_ab']
        && !in_array('suche_sortierprio_preis_ab', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_preis_ab';
        $obj->value           = SEARCH_SORT_PRICE_DESC;
        $obj->angezeigterName = Shop::Lang()->get('sortPriceDesc');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_preis_ab'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_ean']
        && !in_array('suche_sortierprio_ean', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_ean';
        $obj->value           = SEARCH_SORT_EAN;
        $obj->angezeigterName = Shop::Lang()->get('sortEan');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_ean'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_erstelldatum']
        && !in_array('suche_sortierprio_erstelldatum', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_erstelldatum';
        $obj->value           = SEARCH_SORT_NEWEST_FIRST;
        $obj->angezeigterName = Shop::Lang()->get('sortNewestFirst');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_erstelldatum'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_artikelnummer']
        && !in_array('suche_sortierprio_artikelnummer', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_artikelnummer';
        $obj->value           = SEARCH_SORT_PRODUCTNO;
        $obj->angezeigterName = Shop::Lang()->get('sortProductno');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_artikelnummer'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_gewicht']
        && !in_array('suche_sortierprio_gewicht', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_gewicht';
        $obj->value           = SEARCH_SORT_WEIGHT;
        $obj->angezeigterName = Shop::Lang()->get('sortWeight');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_gewicht'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_erscheinungsdatum']
        && !in_array('suche_sortierprio_erscheinungsdatum', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_erscheinungsdatum';
        $obj->value           = SEARCH_SORT_DATEOFISSUE;
        $obj->angezeigterName = Shop::Lang()->get('sortDateofissue');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_erscheinungsdatum'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_bestseller']
        && !in_array('suche_sortierprio_bestseller', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_bestseller';
        $obj->value           = SEARCH_SORT_BESTSELLER;
        $obj->angezeigterName = Shop::Lang()->get('bestseller');
        $max                  = $conf['artikeluebersicht']['suche_sortierprio_bestseller'];
    }
    if ($max < $conf['artikeluebersicht']['suche_sortierprio_bewertung']
        && !in_array('suche_sortierprio_bewertung', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_bewertung';
        $obj->value           = SEARCH_SORT_RATING;
        $obj->angezeigterName = Shop::Lang()->get('rating');
    }

    return $obj;
}

/**
 * @param object $NaviFilter
 * @return mixed|stdClass
 * @deprecated since 5.0.0
 */
function bauFilterSQL($NaviFilter)
{
    trigger_error(__FUNCTION__ . ' is deprecated and will do nothing.', E_USER_DEPRECATED);
    $filterSQL                            = new stdClass();
    $filterSQL->oHerstellerFilterSQL      = new stdClass();
    $filterSQL->oKategorieFilterSQL       = new stdClass();
    $filterSQL->oMerkmalFilterSQL         = new stdClass();
    $filterSQL->oBewertungSterneFilterSQL = new stdClass();
    $filterSQL->oPreisspannenFilterSQL    = new stdClass();
    $filterSQL->oSuchFilterSQL            = new stdClass();
    $filterSQL->oSuchspecialFilterSQL     = new stdClass();
    $filterSQL->oArtikelAttributFilterSQL = new stdClass();

    return $filterSQL;
}

/**
 * @deprecated since 5.0.0
 * @return array
 * @throws Exception
 */
function gibArtikelKeysExtendedJTLSearch()
{
    trigger_error(__FUNCTION__ . ' is deprecated and will do nothing.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param object $filterSQL
 * @param object $oSuchergebnisse
 * @param int    $productsPerPage
 * @param int    $limit
 * @deprecated since 5.0.0
 */
function baueArtikelAnzahl($filterSQL, &$oSuchergebnisse, $productsPerPage = 20, $limit = 20)
{
    trigger_error(__FUNCTION__ . ' is deprecated and will do nothing.', E_USER_DEPRECATED);
    $qty = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nGesamtAnzahl
            FROM(
                SELECT tartikel.kArtikel
                FROM tartikel ' .
                ($filterSQL->oSuchspecialFilterSQL->cJoin ?? '') . ' ' .
                ($filterSQL->oKategorieFilterSQL->cJoin ?? '') . ' ' .
                ($filterSQL->oSuchFilterSQL->cJoin ?? '') . ' ' .
                ($filterSQL->oMerkmalFilterSQL->cJoin ?? '') . ' ' .
                ($filterSQL->oBewertungSterneFilterSQL->cJoin ?? '') . ' ' .
                ($filterSQL->oPreisspannenFilterSQL->cJoin ?? '') .
            ' LEFT JOIN tartikelsichtbarkeit 
                ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = ' . Frontend::getCustomerGroup()->getID() . '
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikel.kVaterArtikel = 0 ' .
                gibLagerfilter() . ' ' .
                ($filterSQL->oSuchspecialFilterSQL->cWhere ?? '') . ' ' .
                ($filterSQL->oSuchFilterSQL->cWhere ?? '') . ' ' .
                ($filterSQL->oHerstellerFilterSQL->cWhere ?? '') . ' ' .
                ($filterSQL->oKategorieFilterSQL->cWhere ?? '') . ' ' .
                ($filterSQL->oMerkmalFilterSQL->cWhere ?? '') . ' ' .
                ($filterSQL->oBewertungSterneFilterSQL->cWhere ?? '') . ' ' .
                ($filterSQL->oPreisspannenFilterSQL->cWhere ?? '') .
                ' GROUP BY tartikel.kArtikel ' .
                ($filterSQL->oMerkmalFilterSQL->cHaving ?? '') .
                ') AS tAnzahl',
        ReturnType::SINGLE_OBJECT
    );
    executeHook(HOOK_FILTER_INC_BAUEARTIKELANZAHL, [
        'oAnzahl'          => &$qty,
        'FilterSQL'        => &$filterSQL,
        'oSuchergebnisse'  => &$oSuchergebnisse,
        'nArtikelProSeite' => &$productsPerPage,
        'nLimitN'          => &$limit
    ]);
    $conf                 = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
    $page                 = $GLOBALS['NaviFilter']->nSeite ?? 1;
    $nSettingMaxPageCount = (int)$conf['artikeluebersicht']['artikeluebersicht_max_seitenzahl'];

    $oSuchergebnisse->GesamtanzahlArtikel = $qty->nGesamtAnzahl;
    $oSuchergebnisse->ArtikelVon          = $limit + 1;
    $oSuchergebnisse->ArtikelBis          = min($limit + $productsPerPage, $oSuchergebnisse->GesamtanzahlArtikel);

    if (!isset($oSuchergebnisse->Seitenzahlen)) {
        $oSuchergebnisse->Seitenzahlen = new stdClass();
    }
    $oSuchergebnisse->Seitenzahlen->AktuelleSeite = $page;
    $oSuchergebnisse->Seitenzahlen->MaxSeiten     = ceil($oSuchergebnisse->GesamtanzahlArtikel / $productsPerPage);
    $oSuchergebnisse->Seitenzahlen->minSeite      = min(
        $oSuchergebnisse->Seitenzahlen->AktuelleSeite - $nSettingMaxPageCount / 2,
        0
    );
    $oSuchergebnisse->Seitenzahlen->maxSeite      = max(
        $oSuchergebnisse->Seitenzahlen->MaxSeiten,
        $oSuchergebnisse->Seitenzahlen->minSeite + $nSettingMaxPageCount - 1
    );
    if ($oSuchergebnisse->Seitenzahlen->maxSeite > $oSuchergebnisse->Seitenzahlen->MaxSeiten) {
        $oSuchergebnisse->Seitenzahlen->maxSeite = $oSuchergebnisse->Seitenzahlen->MaxSeiten;
    }
    $sr              = new SearchResults();
    $oSuchergebnisse = $sr->convert($oSuchergebnisse);
}
