<?php

use JTL\Alert\Alert;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('MODULE_WISHLIST_VIEW', true, true);
$alertHelper = Shop::Container()->getAlertService();
$settingsIDs = [
    'boxen_wunschzettel_anzahl',
    'boxen_wunschzettel_bilder',
    'global_wunschliste_weiterleitung',
    'global_wunschliste_anzeigen',
    'global_wunschliste_freunde_aktiv',
    'global_wunschliste_max_email',
    'global_wunschliste_artikel_loeschen_nach_kauf'
];
if (Request::verifyGPCDataInt('einstellungen') === 1) {
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSettings($settingsIDs, $_POST, [CACHING_GROUP_OPTION], true),
        'saveSettings'
    );
}
$itemCount     = (int)Shop::Container()->getDB()->query(
    'SELECT COUNT(DISTINCT twunschliste.kWunschliste) AS cnt
         FROM twunschliste
         JOIN twunschlistepos
             ON twunschliste.kWunschliste = twunschlistepos.kWunschliste',
    ReturnType::SINGLE_OBJECT
)->cnt;
$productCount  = (int)Shop::Container()->getDB()->query(
    'SELECT COUNT(*) AS cnt
        FROM twunschlistepos',
    ReturnType::SINGLE_OBJECT
)->cnt;
$friends       = (int)Shop::Container()->getDB()->query(
    'SELECT COUNT(*) AS cnt
        FROM twunschliste
        JOIN twunschlisteversand 
            ON twunschliste.kWunschliste = twunschlisteversand.kWunschliste',
    ReturnType::SINGLE_OBJECT
)->cnt;
$oPagiPos      = (new Pagination('pos'))
    ->setItemCount($itemCount)
    ->assemble();
$oPagiArtikel  = (new Pagination('artikel'))
    ->setItemCount($productCount)
    ->assemble();
$oPagiFreunde  = (new Pagination('freunde'))
    ->setItemCount($friends)
    ->assemble();
$sentWishLists = Shop::Container()->getDB()->query(
    "SELECT tkunde.kKunde, tkunde.cNachname, tkunde.cVorname, twunschlisteversand.nAnzahlArtikel, 
        twunschliste.kWunschliste, twunschliste.cName, twunschliste.cURLID, 
        twunschlisteversand.nAnzahlEmpfaenger, DATE_FORMAT(twunschlisteversand.dZeit, '%d.%m.%Y  %H:%i') AS Datum
        FROM twunschliste
        JOIN twunschlisteversand 
            ON twunschliste.kWunschliste = twunschlisteversand.kWunschliste
        LEFT JOIN tkunde 
            ON twunschliste.kKunde = tkunde.kKunde
        ORDER BY twunschlisteversand.dZeit DESC
        LIMIT " . $oPagiFreunde->getLimitSQL(),
    ReturnType::ARRAY_OF_OBJECTS
);
foreach ($sentWishLists as $wishList) {
    if ($wishList->kKunde !== null) {
        $customer            = new Customer((int)$wishList->kKunde);
        $wishList->cNachname = $customer->cNachname;
    }
}
$wishLists = Shop::Container()->getDB()->query(
    "SELECT tkunde.kKunde, tkunde.cNachname, tkunde.cVorname, twunschliste.kWunschliste, twunschliste.cName,
        twunschliste.cURLID, DATE_FORMAT(twunschliste.dErstellt, '%d.%m.%Y %H:%i') AS Datum, 
        twunschliste.nOeffentlich, COUNT(twunschlistepos.kWunschliste) AS Anzahl
        FROM twunschliste
        JOIN twunschlistepos 
            ON twunschliste.kWunschliste = twunschlistepos.kWunschliste
        LEFT JOIN tkunde 
            ON twunschliste.kKunde = tkunde.kKunde
        GROUP BY twunschliste.kWunschliste
        ORDER BY twunschliste.dErstellt DESC
        LIMIT " . $oPagiPos->getLimitSQL(),
    ReturnType::ARRAY_OF_OBJECTS
);
foreach ($wishLists as $wishList) {
    if ($wishList->kKunde !== null) {
        $customer            = new Customer((int)$wishList->kKunde);
        $wishList->cNachname = $customer->cNachname;
    }
}
$wishListPositions = Shop::Container()->getDB()->query(
    "SELECT kArtikel, cArtikelName, count(kArtikel) AS Anzahl,
        DATE_FORMAT(dHinzugefuegt, '%d.%m.%Y %H:%i') AS Datum
        FROM twunschlistepos
        GROUP BY kArtikel
        ORDER BY Anzahl DESC
        LIMIT " . $oPagiArtikel->getLimitSQL(),
    ReturnType::ARRAY_OF_OBJECTS
);

$smarty->assign('oConfig_arr', getAdminSectionSettings($settingsIDs, true))
    ->assign('oPagiPos', $oPagiPos)
    ->assign('oPagiArtikel', $oPagiArtikel)
    ->assign('oPagiFreunde', $oPagiFreunde)
    ->assign('CWunschlisteVersand_arr', $sentWishLists)
    ->assign('CWunschliste_arr', $wishLists)
    ->assign('CWunschlistePos_arr', $wishListPositions)
    ->display('wunschliste.tpl');
