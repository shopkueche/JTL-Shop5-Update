<?php

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'news_inc.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('RESET_SHOP_VIEW', true, true);
$alertHelper = Shop::Container()->getAlertService();
$db          = Shop::Container()->getDB();
if (Request::postInt('zuruecksetzen') === 1 && Form::validateToken()) {
    $options = $_POST['cOption_arr'];
    if (is_array($options) && count($options) > 0) {
        foreach ($options as $option) {
            switch ($option) {
                // JTL-Wawi Inhalte
                case 'artikel':
                    $db->query('SET FOREIGN_KEY_CHECKS = 0;', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikel', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelabnahme', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelattribut', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelkategorierabatt', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelkonfiggruppe', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelmerkmal', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelpict', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelsichtbarkeit', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelsonderpreis', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelsprache', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelwarenlager', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tattribut', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tattributsprache', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tbild', ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaft', ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftkombiwert', ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftsichtbarkeit', ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftsprache', ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftwert', ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftwertabhaengigkeit', ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftwertaufpreis', ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftwertpict', ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftwertsichtbarkeit', ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftwertsprache', ReturnType::DEFAULT);
                    $db->query('TRUNCATE teinheit', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategorie', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategorieartikel', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategorieattribut', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategorieattributsprache', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategoriekundengruppe', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategoriemapping', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategoriepict', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategoriesichtbarkeit', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategoriesprache', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmediendatei', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmediendateiattribut', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmediendateisprache', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmerkmal', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmerkmalsprache', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmerkmalwert', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmerkmalwertbild', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmerkmalwertsprache', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tpreis', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tpreisdetail', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsonderpreise', ReturnType::DEFAULT);
                    $db->query('TRUNCATE txsell', ReturnType::DEFAULT);
                    $db->query('TRUNCATE txsellgruppe', ReturnType::DEFAULT);
                    $db->query('TRUNCATE thersteller', ReturnType::DEFAULT);
                    $db->query('TRUNCATE therstellersprache', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tlieferstatus', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkonfiggruppe', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkonfigitem', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkonfiggruppesprache', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkonfigitempreis', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkonfigitemsprache', ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenlager', ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenlagersprache', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tuploadschema', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tuploadschemasprache', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmasseinheit', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmasseinheitsprache', ReturnType::DEFAULT);
                    $db->query('SET FOREIGN_KEY_CHECKS = 1;', ReturnType::DEFAULT);

                    $db->query(
                        "DELETE FROM tseo
                            WHERE cKey = 'kArtikel'
                            OR cKey = 'kKategorie'
                            OR cKey = 'kMerkmalWert'
                            OR cKey = 'kHersteller'",
                        ReturnType::DEFAULT
                    );
                    break;

                case 'steuern':
                    $db->query('TRUNCATE tsteuerklasse', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsteuersatz', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsteuerzone', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsteuerzoneland', ReturnType::DEFAULT);
                    break;

                case 'revisions':
                    $db->query('TRUNCATE trevisions', ReturnType::DEFAULT);
                    break;

                // Shopinhalte
                case 'news':
                    $_index = $db->query(
                        'SELECT kNews FROM tnews',
                        ReturnType::ARRAY_OF_OBJECTS
                    );
                    foreach ($_index as $i) {
                        loescheNewsBilderDir($i->kNews, PFAD_ROOT . PFAD_NEWSBILDER);
                    }
                    $db->query('TRUNCATE tnews', ReturnType::DEFAULT);
                    $db->delete('trevisions', 'type', 'news');
                    $db->query('TRUNCATE tnewskategorie', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tnewskategorienews', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tnewskommentar', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tnewsmonatsuebersicht', ReturnType::DEFAULT);

                    $db->query(
                        "DELETE FROM tseo
                            WHERE cKey = 'kNews'
                              OR cKey = 'kNewsKategorie'
                              OR cKey = 'kNewsMonatsUebersicht'",
                        ReturnType::DEFAULT
                    );
                    break;

                case 'bestseller':
                    $db->query('TRUNCATE tbestseller', ReturnType::DEFAULT);
                    break;

                case 'besucherstatistiken':
                    $db->query('TRUNCATE tbesucher', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tbesucherarchiv', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tbesuchteseiten', ReturnType::DEFAULT);
                    break;

                case 'preisverlaeufe':
                    $db->query('TRUNCATE tpreisverlauf', ReturnType::DEFAULT);
                    break;

                case 'verfuegbarkeitsbenachrichtigungen':
                    $db->query(
                        'TRUNCATE tverfuegbarkeitsbenachrichtigung',
                        ReturnType::DEFAULT
                    );
                    break;

                // Benutzergenerierte Inhalte
                case 'suchanfragen':
                    $db->query('TRUNCATE tsuchanfrage', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsuchanfrageerfolglos', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsuchanfragemapping', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsuchanfragencache', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsuchcache', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsuchcachetreffer', ReturnType::DEFAULT);

                    $db->delete('tseo', 'cKey', 'kSuchanfrage');
                    break;

                case 'bewertungen':
                    $db->query('TRUNCATE tartikelext', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tbewertung', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tbewertungguthabenbonus', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tbewertunghilfreich', ReturnType::DEFAULT);
                    break;

                // Shopkunden & Kunden werben Kunden & Bestellungen & Kupons
                case 'shopkunden':
                    $db->query('TRUNCATE tkunde', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkundenattribut', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkundendatenhistory', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkundenfeld', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkundenfeldwert', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkundenherkunft', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkundenkontodaten', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tlieferadresse', ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorbpers', ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorbperspos', ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorbpersposeigenschaft', ReturnType::DEFAULT);
                    $db->query('TRUNCATE twunschliste', ReturnType::DEFAULT);
                    $db->query('TRUNCATE twunschlistepos', ReturnType::DEFAULT);
                    $db->query('TRUNCATE twunschlisteposeigenschaft', ReturnType::DEFAULT);
                    break;
                case 'bestellungen':
                    $db->query('TRUNCATE tbestellid', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tbestellstatus', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tbestellung', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tlieferschein', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tlieferscheinpos', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tlieferscheinposinfo', ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorb', ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorbpers', ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorbperspos', ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorbpersposeigenschaft', ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorbpos', ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorbposeigenschaft', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tuploaddatei', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tuploadqueue', ReturnType::DEFAULT);

                    $uploadfiles = glob(PFAD_UPLOADS . '*');

                    foreach ($uploadfiles as $file) {
                        if (is_file($file) && mb_strpos($file, '.') !== 0) {
                            unlink($file);
                        }
                    }

                    break;
                case 'kupons':
                    $db->query('TRUNCATE tkupon', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkuponbestellung', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkuponkunde', ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkuponsprache', ReturnType::DEFAULT);
                    break;
            }
        }
        Shop::Container()->getCache()->flushAll();
        $db->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successShopReturn'), 'successShopReturn');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorChooseOption'), 'errorChooseOption');
    }

    executeHook(HOOK_BACKEND_SHOP_RESET_AFTER);
}

$smarty->display('shopzuruecksetzen.tpl');
