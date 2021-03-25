<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Catalog\Product\Preisverlauf;
use JTL\Extensions\Upload\Upload;
use JTL\Helpers\Form;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;

if (!defined('PFAD_ROOT')) {
    http_response_code(400);
    exit();
}
require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
Shop::setPageType(PAGE_ARTIKEL);
$oPreisverlauf  = null;
$bPreisverlauf  = false;
$rated          = false;
$productNotices = [];
$nonAllowed     = [];
$conf           = Shopsetting::getInstance()->getAll();
$shopURL        = Shop::getURL() . '/';
$alertHelper    = Shop::Container()->getAlertService();
$valid          = Form::validateToken();
if ($productNote = Product::mapErrorCode(
    Request::verifyGPDataString('cHinweis'),
    ((float)Request::getVar('fB', 0) > 0) ? (float)$_GET['fB'] : 0.0
)) {
    $alertHelper->addAlert(Alert::TYPE_NOTE, $productNote, 'productNote', ['showInAlertListTemplate' => false]);
}
if ($productError = Product::mapErrorCode(Request::verifyGPDataString('cFehler'))) {
    $alertHelper->addAlert(Alert::TYPE_ERROR, $productError, 'productError');
}
if ($valid && isset($_POST['a'])
    && Request::verifyGPCDataInt('addproductbundle') === 1
    && Product::addProductBundleToCart(Request::verifyGPCDataInt('a'))
) {
    $alertHelper->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('basketAllAdded', 'messages'), 'allAdded');
    Shop::$kArtikel = Request::postInt('aBundle');
}
$AktuellerArtikel = (new Artikel())->fuelleArtikel(Shop::$kArtikel, Artikel::getDetailOptions());
// Warenkorbmatrix Anzeigen auf Artikel Attribut pruefen und falls vorhanden setzen
if (isset($AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigen'])
    && mb_strlen($AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigen']) > 0
) {
    $conf['artikeldetails']['artikeldetails_warenkorbmatrix_anzeige'] =
        $AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigen'];
}
// Warenkorbmatrix Anzeigeformat auf Artikel Attribut pruefen und falls vorhanden setzen
if (isset($AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigeformat'])
    && mb_strlen($AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigeformat']) > 0
) {
    $conf['artikeldetails']['artikeldetails_warenkorbmatrix_anzeigeformat'] =
        $AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigeformat'];
}
// 404
if (empty($AktuellerArtikel->kArtikel)) {
    Shop::$is404    = true;
    Shop::$kLink    = 0;
    Shop::$kArtikel = 0;

    return;
}
$similarProducts = (int)$conf['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'] > 0
    ? $AktuellerArtikel->holeAehnlicheArtikel()
    : [];
if (Shop::$kVariKindArtikel > 0) {
    $oVariKindArtikel = (new Artikel())->fuelleArtikel(Shop::$kVariKindArtikel, Artikel::getDetailOptions());
    if ($oVariKindArtikel !== null && $oVariKindArtikel->kArtikel > 0) {
        $oVariKindArtikel->verfuegbarkeitsBenachrichtigung = Product::showAvailabilityForm(
            $oVariKindArtikel,
            $conf['artikeldetails']['benachrichtigung_nutzen']
        );

        $AktuellerArtikel = Product::combineParentAndChild($AktuellerArtikel, $oVariKindArtikel);
    } else {
        Shop::$is404    = true;
        Shop::$kLink    = 0;
        Shop::$kArtikel = 0;

        return;
    }
    $bCanonicalURL = $conf['artikeldetails']['artikeldetails_canonicalurl_varkombikind'] !== 'N';
    $cCanonicalURL = $AktuellerArtikel->baueVariKombiKindCanonicalURL($AktuellerArtikel, $bCanonicalURL);
}
if ($conf['preisverlauf']['preisverlauf_anzeigen'] === 'Y' && Frontend::getCustomerGroup()->mayViewPrices()) {
    Shop::$kArtikel = Shop::$kVariKindArtikel > 0
        ? Shop::$kVariKindArtikel
        : $AktuellerArtikel->kArtikel;
    $oPreisverlauf  = new Preisverlauf();
    $oPreisverlauf  = $oPreisverlauf->gibPreisverlauf(
        Shop::$kArtikel,
        $AktuellerArtikel->Preise->kKundengruppe,
        (int)$conf['preisverlauf']['preisverlauf_anzahl_monate']
    );
}
// Canonical bei non SEO Shops oder wenn SEO kein Ergebnis geliefert hat
if (empty($cCanonicalURL)) {
    $cCanonicalURL = $shopURL . $AktuellerArtikel->cSeo;
}
$AktuellerArtikel->berechneSieSparenX($conf['artikeldetails']['sie_sparen_x_anzeigen']);
$productNotices = Product::getProductMessages();

if ($conf['artikeldetails']['artikeldetails_fragezumprodukt_anzeigen'] !== 'N') {
    $smarty->assign('Anfrage', Product::getProductQuestionFormDefaults());
}
if ($conf['artikeldetails']['benachrichtigung_nutzen'] !== 'N') {
    $smarty->assign('Benachrichtigung', Product::getAvailabilityFormDefaults());
}
if ($valid && Request::postInt('fragezumprodukt') === 1) {
    $productNotices = Product::checkProductQuestion($productNotices, $conf);
} elseif ($valid && Request::postInt('benachrichtigung_verfuegbarkeit') === 1) {
    $productNotices = Product::checkAvailabilityMessage($productNotices);
}
foreach ($productNotices as $productNoticeKey => $productNotice) {
    $alertHelper->addAlert(Alert::TYPE_DANGER, $productNotice, 'productNotice' . $productNoticeKey);
}
$AktuelleKategorie  = new Kategorie($AktuellerArtikel->gibKategorie());
$expandedCategories = new KategorieListe();
$expandedCategories->getOpenCategories($AktuelleKategorie);
$ratingPage   = Request::verifyGPCDataInt('btgseite');
$ratingStars  = Request::verifyGPCDataInt('btgsterne');
$sorting      = Request::verifyGPCDataInt('sortierreihenfolge');
$showRatings  = Request::verifyGPCDataInt('bewertung_anzeigen');
$allLanguages = Request::verifyGPCDataInt('moreRating');
if ($ratingPage === 0) {
    $ratingPage = 1;
}
if ($AktuellerArtikel->Bewertungen === null || $ratingStars > 0) {
    $AktuellerArtikel->holeBewertung(
        $conf['bewertung']['bewertung_anzahlseite'],
        $ratingPage,
        $ratingStars,
        $conf['bewertung']['bewertung_freischalten'],
        $sorting
    );
    $AktuellerArtikel->holehilfreichsteBewertung();
}

if (isset($AktuellerArtikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich)
    && (int)$AktuellerArtikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich > 0
) {
    $ratings = array_filter(
        $AktuellerArtikel->Bewertungen->oBewertung_arr,
        static function ($oBewertung) use (&$AktuellerArtikel) {
            return (int)$AktuellerArtikel->HilfreichsteBewertung->oBewertung_arr[0]->kBewertung
                !== (int)$oBewertung->kBewertung;
        }
    );
} else {
    $ratings = $AktuellerArtikel->Bewertungen->oBewertung_arr;
}
if (Frontend::getCustomer()->getID() > 0) {
    $rated = Product::getRatedByCurrentCustomer(
        (int)$AktuellerArtikel->kArtikel,
        (int)$AktuellerArtikel->kVaterArtikel
    );
}

$pagination = (new Pagination('ratings'))
    ->setItemArray($ratings)
    ->setItemsPerPageOptions([(int)$conf['bewertung']['bewertung_anzahlseite']])
    ->setDefaultItemsPerPage($conf['bewertung']['bewertung_anzahlseite'])
    ->setSortByOptions([
        ['dDatum', Shop::Lang()->get('paginationOrderByDate')],
        ['nSterne', Shop::Lang()->get('paginationOrderByRating')],
        ['nHilfreich', Shop::Lang()->get('paginationOrderUsefulness')]
    ])
    ->setDefaultSortByDir((int)$conf['bewertung']['bewertung_sortierung'])
    ->assemble();

$AktuellerArtikel->Bewertungen->Sortierung = $sorting;

$ratingsCount = $ratingStars === 0
    ? $AktuellerArtikel->Bewertungen->nAnzahlSprache
    : $AktuellerArtikel->Bewertungen->nSterne_arr[5 - $ratingStars];
$ratingNav    = Product::getRatingNavigation(
    $ratingPage,
    $ratingStars,
    $ratingsCount,
    $conf['bewertung']['bewertung_anzahlseite']
);
if (Request::hasGPCData('ek')) {
    Product::getEditConfigMode(Request::verifyGPCDataInt('ek'), $smarty);
}
foreach ($AktuellerArtikel->Variationen as $Variation) {
    if (!$Variation->Werte || $Variation->cTyp === 'FREIFELD' || $Variation->cTyp === 'PFLICHT-FREIFELD') {
        continue;
    }
    foreach ($Variation->Werte as $value) {
        $nonAllowed[$value->kEigenschaftWert] = Product::getNonAllowedAttributeValues($value->kEigenschaftWert);
    }
}
$nav = $conf['artikeldetails']['artikeldetails_navi_blaettern'] === 'Y'
    ? Product::getProductNavigation($AktuellerArtikel->kArtikel ?? 0, $AktuelleKategorie->kKategorie ?? 0)
    : null;

if (($AktuellerArtikel->kVariKindArtikel ?? 0) === 0 && $AktuellerArtikel->nIstVater === 0 && Upload::checkLicense()) {
    $maxSize = Upload::uploadMax();
    $smarty->assign('nMaxUploadSize', $maxSize)
           ->assign('cMaxUploadSize', Upload::formatGroesse($maxSize))
           ->assign('oUploadSchema_arr', Upload::gibArtikelUploads(!empty($AktuellerArtikel->kVariKindArtikel)
               ? $AktuellerArtikel->kVariKindArtikel
               : $AktuellerArtikel->kArtikel));
}
$smarty->assign('showMatrix', $AktuellerArtikel->showMatrix())
       ->assign('arNichtErlaubteEigenschaftswerte', $nonAllowed)
       ->assign('oAehnlicheArtikel_arr', $similarProducts)
       ->assign('UVPlocalized', $AktuellerArtikel->cUVPLocalized)
       ->assign('UVPBruttolocalized', Preise::getLocalizedPriceString($AktuellerArtikel->fUVPBrutto))
       ->assign('Artikel', $AktuellerArtikel)
       ->assign('Xselling', !empty($AktuellerArtikel->kVariKindArtikel)
           ? Product::getXSelling($AktuellerArtikel->kVariKindArtikel)
           : Product::getXSelling($AktuellerArtikel->kArtikel, $AktuellerArtikel->nIstVater > 0))
       ->assign('Artikelhinweise', $productNotices)
       ->assign(
           'verfuegbarkeitsBenachrichtigung',
           Product::showAvailabilityForm(
               $AktuellerArtikel,
               $conf['artikeldetails']['benachrichtigung_nutzen']
           )
       )
       ->assign('BlaetterNavi', $ratingNav)
       ->assign('BewertungsTabAnzeigen', ($ratingPage || $ratingStars || $showRatings || $allLanguages) ? 1 : 0)
       ->assign('alertNote', $alertHelper->alertTypeExists(Alert::TYPE_NOTE))
       ->assign('PFAD_MEDIAFILES', $shopURL . PFAD_MEDIAFILES)
       ->assign('PFAD_BILDER', PFAD_BILDER)
       ->assign('FKT_ATTRIBUT_ATTRIBUTEANHAENGEN', FKT_ATTRIBUT_ATTRIBUTEANHAENGEN)
       ->assign('FKT_ATTRIBUT_WARENKORBMATRIX', FKT_ATTRIBUT_WARENKORBMATRIX)
       ->assign('FKT_ATTRIBUT_INHALT', FKT_ATTRIBUT_INHALT)
       ->assign('FKT_ATTRIBUT_MAXBESTELLMENGE', FKT_ATTRIBUT_MAXBESTELLMENGE)
       ->assign('KONFIG_ITEM_TYP_ARTIKEL', KONFIG_ITEM_TYP_ARTIKEL)
       ->assign('KONFIG_ITEM_TYP_SPEZIAL', KONFIG_ITEM_TYP_SPEZIAL)
       ->assign('KONFIG_ANZEIGE_TYP_CHECKBOX', KONFIG_ANZEIGE_TYP_CHECKBOX)
       ->assign('KONFIG_ANZEIGE_TYP_RADIO', KONFIG_ANZEIGE_TYP_RADIO)
       ->assign('KONFIG_ANZEIGE_TYP_DROPDOWN', KONFIG_ANZEIGE_TYP_DROPDOWN)
       ->assign('KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI', KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI)
       ->assign('ratingPagination', $pagination)
       ->assign('bewertungSterneSelected', $ratingStars)
       ->assign('bPreisverlauf', is_array($oPreisverlauf) && count($oPreisverlauf) > 1)
       ->assign('preisverlaufData', $oPreisverlauf)
       ->assign('NavigationBlaettern', $nav)
       ->assign('bereitsBewertet', $rated);

$cMetaTitle       = $AktuellerArtikel->getMetaTitle();
$cMetaDescription = $AktuellerArtikel->getMetaDescription($expandedCategories);
$cMetaKeywords    = $AktuellerArtikel->getMetaKeywords();

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_ARTIKEL_PAGE, ['oArtikel' => $AktuellerArtikel]);

if (Request::isAjaxRequest()) {
    $smarty->assign('listStyle', isset($_GET['isListStyle']) ? Text::filterXSS($_GET['isListStyle']) : '');
}

$smarty->display('productdetails/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
