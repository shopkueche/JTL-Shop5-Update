<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Product\ArtikelListe;
use JTL\Catalog\Product\Bestseller;
use JTL\Extensions\SelectionWizard\Wizard;
use JTL\Filter\Metadata;
use JTL\Filter\Pagination\ItemFactory;
use JTL\Filter\Pagination\Pagination;
use JTL\Filter\ProductFilter;
use JTL\Helpers\Category;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;

if (!defined('PFAD_ROOT')) {
    http_response_code(400);
    exit();
}
require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';
Shop::setPageType(PAGE_ARTIKELLISTE);
/** @global JTLSmarty $smarty */
/** @global ProductFilter $NaviFilter*/
$conf               = Shopsetting::getInstance()->getAll();
$bestsellers        = [];
$doSearch           = true;
$categoryContent    = null;
$AktuelleKategorie  = new Kategorie();
$expandedCategories = new KategorieListe();
$hasError           = false;
$params             = Shop::getParameters();
/** @var ProductFilter $NaviFilter */
if ($NaviFilter->hasCategory()) {
    $categoryID                  = $NaviFilter->getCategory()->getValue();
    $_SESSION['LetzteKategorie'] = $categoryID;
    if ($AktuelleKategorie->kKategorie === null) {
        // temp. workaround: do not return 404 when non-localized existing category is loaded
        if (Category::categoryExists($categoryID)) {
            $AktuelleKategorie->loadFromDB($categoryID);
        } else {
            Shop::$is404     = true;
            $params['is404'] = true;

            return;
        }
    }
    $expandedCategories->getOpenCategories($AktuelleKategorie);
}
$NaviFilter->setUserSort($AktuelleKategorie);
$oSuchergebnisse = $NaviFilter->generateSearchResults($AktuelleKategorie);
$pages           = $oSuchergebnisse->getPages();
if ($oSuchergebnisse->getProductCount() === 0) {
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_NOTE,
        Shop::Lang()->get('noFilterResults'),
        'noFilterResults',
        ['showInAlertListTemplate' => false]
    );
}
if ($conf['navigationsfilter']['allgemein_weiterleitung'] === 'Y'
    && $oSuchergebnisse->getVisibleProductCount() === 1
    && !Request::isAjaxRequest()
) {
    $hasSubCategories = ($categoryID = $NaviFilter->getCategory()->getValue()) > 0
        ? (new Kategorie(
            $categoryID,
            $NaviFilter->getFilterConfig()->getLanguageID(),
            $NaviFilter->getFilterConfig()->getCustomerGroupID()
        ))->existierenUnterkategorien()
        : false;
    if ($NaviFilter->getFilterCount() > 0
        || $NaviFilter->getRealSearch() !== null
        || ($NaviFilter->getCategory()->getValue() > 0 && !$hasSubCategories)
    ) {
        http_response_code(301);
        $product = $oSuchergebnisse->getProducts()->pop();
        $url     = empty($product->cURL)
            ? (Shop::getURL() . '/?a=' . $product->kArtikel)
            : (Shop::getURL() . '/' . $product->cURL);
        header('Location: ' . $url);
        exit;
    }
}
if ($pages->getCurrentPage() > 0
    && $pages->getTotalPages() > 0
    && !Request::isAjaxRequest()
    && ($oSuchergebnisse->getVisibleProductCount() === 0 || ($pages->getCurrentPage() > $pages->getTotalPages()))
) {
    http_response_code(301);
    header('Location: ' . $NaviFilter->getFilterURL()->getURL());
    exit;
}
if ($conf['artikeluebersicht']['artikelubersicht_bestseller_gruppieren'] === 'Y') {
    $productsIDs = $oSuchergebnisse->getProducts()->map(static function ($product) {
        return (int)$product->kArtikel;
    });
    $bestsellers = Bestseller::buildBestsellers(
        $productsIDs,
        Frontend::getCustomerGroup()->getID(),
        Frontend::getCustomerGroup()->mayViewCategories(),
        false,
        (int)$conf['artikeluebersicht']['artikeluebersicht_bestseller_anzahl'],
        (int)$conf['global']['global_bestseller_minanzahl']
    );
    $products    = $oSuchergebnisse->getProducts()->all();
    Bestseller::ignoreProducts($products, $bestsellers);
}
if (Request::verifyGPCDataInt('zahl') > 0) {
    $_SESSION['ArtikelProSeite'] = Request::verifyGPCDataInt('zahl');
}
if (!isset($_SESSION['ArtikelProSeite']) && $conf['artikeluebersicht']['artikeluebersicht_erw_darstellung'] === 'N') {
    $_SESSION['ArtikelProSeite'] = min(
        (int)$conf['artikeluebersicht']['artikeluebersicht_artikelproseite'],
        ARTICLES_PER_PAGE_HARD_LIMIT
    );
}
$oSuchergebnisse->getProducts()->transform(static function ($product) use ($conf) {
    $product->verfuegbarkeitsBenachrichtigung = Product::showAvailabilityForm(
        $product,
        $conf['artikeldetails']['benachrichtigung_nutzen']
    );

    return $product;
});
if ($oSuchergebnisse->getProducts()->count() === 0) {
    if ($NaviFilter->hasCategory()) {
        $categoryContent                  = new stdClass();
        $categoryContent->Unterkategorien = new KategorieListe();
        $h                                = Category::getInstance();
        $children                         = $h->getCategoryById($NaviFilter->getCategory()->getValue());
        $tb                               = $conf['artikeluebersicht']['topbest_anzeigen'];
        if ($children !== null && $children->hasChildren()) {
            $categoryContent->Unterkategorien->elemente = $children->getChildren();
        }
        if ($tb === 'Top' || $tb === 'TopBest') {
            $categoryContent->TopArtikel = new ArtikelListe();
            $categoryContent->TopArtikel->holeTopArtikel($categoryContent->Unterkategorien);
        }
        if ($tb === 'Bestseller' || $tb === 'TopBest') {
            $categoryContent->BestsellerArtikel = new ArtikelListe();
            $categoryContent->BestsellerArtikel->holeBestsellerArtikel(
                $categoryContent->Unterkategorien,
                $categoryContent->TopArtikel ?? null
            );
        }
    } else {
        $oSuchergebnisse->setSearchUnsuccessful(true);
    }
}
$oNavigationsinfo = $NaviFilter->getMetaData()->getNavigationInfo($AktuelleKategorie, $expandedCategories);
if (mb_strpos(basename($NaviFilter->getFilterURL()->getURL()), '.php') === false) {
    $cCanonicalURL = $NaviFilter->getFilterURL()->getURL(null, true) . ($pages->getCurrentPage() > 1
        ? SEP_SEITE . $pages->getCurrentPage()
        : '');
}
Wizard::startIfRequired(
    AUSWAHLASSISTENT_ORT_KATEGORIE,
    $params['kKategorie'],
    Shop::getLanguageID(),
    $smarty,
    [],
    $NaviFilter
);
$pagination = new Pagination($NaviFilter, new ItemFactory());
$pagination->create($pages);

$priceRanges = $NaviFilter->getPriceRangeFilter()->getOptions();
if (($priceRangesCount = count($priceRanges)) > 0) {
    $priceRangeMax = end($priceRanges)->getData('nBis');
}

$smarty->assign('NaviFilter', $NaviFilter)
       ->assign('priceRangeMax', $priceRangeMax ?? 0)
       ->assign('KategorieInhalt', $categoryContent)
       ->assign('oErweiterteDarstellung', $NaviFilter->getMetaData()->getExtendedView($params['nDarstellung']))
       ->assign('oBestseller_arr', $bestsellers)
       ->assign('oNaviSeite_arr', $pagination->getItemsCompat())
       ->assign('filterPagination', $pagination)
       ->assign('Suchergebnisse', $oSuchergebnisse)
       ->assign('oNavigationsinfo', $oNavigationsinfo)
       ->assign('priceRange', $NaviFilter->getPriceRangeFilter()->getValue())
       ->assign('nMaxAnzahlArtikel', (int)($oSuchergebnisse->getProductCount() >=
           (int)$conf['artikeluebersicht']['suche_max_treffer']));

executeHook(HOOK_FILTER_PAGE);
require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
$globalMetaData = Metadata::getGlobalMetaData();
$smarty->assign(
    'meta_title',
    $oNavigationsinfo->generateMetaTitle(
        $oSuchergebnisse,
        $globalMetaData,
        $AktuelleKategorie
    )
)->assign(
    'meta_description',
    $oNavigationsinfo->generateMetaDescription(
        $oSuchergebnisse->getProducts()->all(),
        $oSuchergebnisse,
        $globalMetaData,
        $AktuelleKategorie
    )
)->assign(
    'meta_keywords',
    $oNavigationsinfo->generateMetaKeywords(
        $oSuchergebnisse->getProducts()->all(),
        $AktuelleKategorie
    )
);
executeHook(HOOK_FILTER_ENDE);

if (Request::verifyGPCDataInt('useMobileFilters')) {
    $smarty->assign('NaviFilters', $NaviFilter)
        ->assign('show_filters', true)
        ->assign('itemCount', $oSuchergebnisse->getProductCount())
        ->display('snippets/filter/mobile.tpl');
} else {
    $smarty->display('productlist/index.tpl');
}
require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
