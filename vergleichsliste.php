<?php declare(strict_types=1);

use JTL\Cart\CartHelper;
use JTL\Catalog\ComparisonList;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'vergleichsliste_inc.php';

Shop::setPageType(PAGE_VERGLEICHSLISTE);
$compareList = null;
$conf        = Shop::getSettings([CONF_VERGLEICHSLISTE, CONF_ARTIKELDETAILS]);
$attrVar     = [[], []];
$linkHelper  = Shop::Container()->getLinkService();
$kLink       = $linkHelper->getSpecialPageID(LINKTYP_VERGLEICHSLISTE);
$link        = $linkHelper->getPageLink($kLink);
$compareList = new ComparisonList();
$attrVar     = $compareList->buildAttributeAndVariation();
$alertHelper = Shop::Container()->getAlertService();
$compareList->save();

if (Request::verifyGPCDataInt('addToCart') !== 0) {
    CartHelper::addProductIDToCart(
        Request::verifyGPCDataInt('addToCart'),
        Request::verifyGPDataString('anzahl')
    );
    $alertHelper->addAlert(
        Alert::TYPE_NOTE,
        Shop::Lang()->get('basketAdded', 'messages'),
        'basketAdded'
    );
}

$nBreiteAttribut = ($conf['vergleichsliste']['vergleichsliste_spaltengroesseattribut'] > 0)
    ? (int)$conf['vergleichsliste']['vergleichsliste_spaltengroesseattribut']
    : 100;
$nBreiteArtikel  = ($conf['vergleichsliste']['vergleichsliste_spaltengroesse'] > 0)
    ? (int)$conf['vergleichsliste']['vergleichsliste_spaltengroesse']
    : 200;
Shop::Smarty()->assign('nBreiteTabelle', $nBreiteArtikel * count($compareList->oArtikel_arr) + $nBreiteAttribut)
    ->assign('cPrioSpalten_arr', $compareList->getPrioRows(true, false))
    ->assign('prioRows', $compareList->getPrioRows())
    ->assign('Link', $link)
    ->assign('oMerkmale_arr', $attrVar[0])
    ->assign('oVariationen_arr', $attrVar[1])
    ->assign('print', (int)(Request::getInt('print') === 1))
    ->assign('oVergleichsliste', $compareList)
    ->assign('Einstellungen_Vergleichsliste', $conf);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_VERGLEICHSLISTE_PAGE);

Shop::Smarty()->display('comparelist/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
