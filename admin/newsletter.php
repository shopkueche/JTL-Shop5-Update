<?php

use JTL\Alert\Alert;
use JTL\Campaign;
use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Newsletter\Admin;
use JTL\Newsletter\Newsletter;
use JTL\Newsletter\NewsletterCronDAO;
use JTL\Pagination\Pagination;
use JTL\Session\Frontend;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('MODULE_NEWSLETTER_VIEW', true, true);

$db            = Shop::Container()->getDB();
$conf          = Shop::getSettings([CONF_NEWSLETTER]);
$alertHelper   = Shop::Container()->getAlertService();
$newsletterTPL = null;
$step          = 'uebersicht';
$option        = '';
$admin         = new Admin($db);

$inactiveSearchSQL         = new stdClass();
$inactiveSearchSQL->cJOIN  = '';
$inactiveSearchSQL->cWHERE = '';
$activeSearchSQL           = new stdClass();
$activeSearchSQL->cJOIN    = '';
$activeSearchSQL->cWHERE   = '';
$customerGroup             = $db->select('tkundengruppe', 'cStandard', 'Y');
$_SESSION['Kundengruppe']  = new CustomerGroup((int)$customerGroup->kKundengruppe);
setzeSprache();
$languageID = (int)$_SESSION['editLanguageID'];
$instance   = new Newsletter($db, $conf);
if (Form::validateToken()) {
    if (Request::postInt('einstellungen') === 1) {
        if (isset($_POST['speichern'])) {
            $step = 'uebersicht';
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                saveAdminSectionSettings(CONF_NEWSLETTER, $_POST),
                'saveSettings'
            );
            $admin->setNewsletterCheckboxStatus();
        }
    } elseif (Request::postInt('newsletterabonnent_loeschen') === 1
        || (Request::verifyGPCDataInt('inaktiveabonnenten') === 1 && isset($_POST['abonnentloeschenSubmit']))
    ) {
        if ($admin->deleteSubscribers($_POST['kNewsletterEmpfaenger'])) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successNewsletterAboDelete'), 'successNewsletterAboDelete');
        } else {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                __('errorAtLeastOneNewsletterAbo'),
                'errorAtLeastOneNewsletterAbo'
            );
        }
    } elseif (isset($_POST['abonnentfreischaltenSubmit']) && Request::verifyGPCDataInt('inaktiveabonnenten') === 1) {
        if ($admin->activateSubscribers($_POST['kNewsletterEmpfaenger'])) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successNewsletterAbounlock'), 'successNewsletterAbounlock');
        } else {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                __('errorAtLeastOneNewsletterAbo'),
                'errorAtLeastOneNewsletterAbo'
            );
        }
    } elseif (Request::postInt('newsletterabonnent_neu') === 1) {
        // Newsletterabonnenten hinzufuegen
        $newsletter               = new stdClass();
        $newsletter->cAnrede      = $_POST['cAnrede'] ?? '';
        $newsletter->cVorname     = $_POST['cVorname'];
        $newsletter->cNachname    = $_POST['cNachname'];
        $newsletter->cEmail       = $_POST['cEmail'];
        $newsletter->kSprache     = Request::postInt('kSprache');
        $newsletter->dEingetragen = 'NOW()';
        $newsletter->cOptCode     = $instance->createCode('cOptCode', $newsletter->cEmail);
        $newsletter->cLoeschCode  = $instance->createCode('cLoeschCode', $newsletter->cEmail);
        $newsletter->kKunde       = 0;

        if (empty($newsletter->cEmail)) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillEmail'), 'errorFillEmail');
            $smarty->assign('oNewsletter', $newsletter);
        } else {
            $oNewsTmp = $db->select('tnewsletterempfaenger', 'cEmail', $newsletter->cEmail);
            if ($oNewsTmp) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorEmailExists'),
                    'errorEmailExists'
                );
                $smarty->assign('oNewsletter', $newsletter);
            } else {
                $db->insert('tnewsletterempfaenger', $newsletter);
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successNewsletterAboAdd'), 'successNewsletterAboAdd');
            }
        }
    } elseif (Request::postInt('newsletterqueue') === 1) { // Queue
        if (isset($_POST['loeschen'])) {
            if (!empty($_POST['kNewsletterQueue']) && is_array($_POST['kNewsletterQueue'])) {
                $noticeTMP = '';
                foreach ($_POST['kNewsletterQueue'] as $kNewsletterQueue) {
                    $entry = $db->queryPrepared(
                        'SELECT c.foreignKeyID AS newsletterID, c.cronID AS cronID, l.cBetreff
                            FROM tcron c
                            LEFT JOIN tjobqueue j 
                                ON j.cronID = c.cronID
                            LEFT JOIN tnewsletter l 
                                ON c.foreignKeyID = l.kNewsletter
                            WHERE c.cronID = :cronID',
                        ['cronID' => $kNewsletterQueue],
                        ReturnType::SINGLE_OBJECT
                    );
                    $db->delete('tnewsletter', 'kNewsletter', (int)$entry->newsletterID);
                    $db->delete('tcron', 'cronID', $entry->cronID);
                    if (!empty($entry->foreignKeyID)) {
                        $db->delete(
                            'tjobqueue',
                            ['foreignKey', 'foreignKeyID'],
                            ['kNewsletter', (int)$entry->foreignKeyID]
                        );
                    }
                    $noticeTMP .= $entry->cBetreff . '", ';
                }
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    sprintf(__('successNewsletterQueueDelete'), mb_substr($noticeTMP, 0, -2)),
                    'successDeleteQueue'
                );
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneNewsletter'), 'errorAtLeastOneNewsletter');
            }
        }
    } elseif (Request::postInt('newsletterhistory') === 1 || Request::getInt('newsletterhistory') === 1) {
        if (isset($_POST['loeschen'])) {
            if (is_array($_POST['kNewsletterHistory'])) {
                $noticeTMP = '';
                foreach ($_POST['kNewsletterHistory'] as $historyID) {
                    $db->delete('tnewsletterhistory', 'kNewsletterHistory', (int)$historyID);
                    $noticeTMP .= $historyID . ', ';
                }
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    sprintf(__('successNewsletterHistoryDelete'), mb_substr($noticeTMP, 0, -2)),
                    'successDeleteHistory'
                );
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneHistory'), 'errorAtLeastOneHistory');
            }
        } elseif (isset($_GET['anzeigen'])) {
            $step      = 'history_anzeigen';
            $historyID = (int)$_GET['anzeigen'];
            $hist      = $db->queryPrepared(
                "SELECT kNewsletterHistory, cBetreff, cHTMLStatic, cKundengruppe,
                    DATE_FORMAT(dStart, '%d.%m.%Y %H:%i') AS Datum
                    FROM tnewsletterhistory
                    WHERE kNewsletterHistory = :hid
                        AND kSprache = :lid",
                ['hid' => $historyID, 'lid' => $languageID],
                ReturnType::SINGLE_OBJECT
            );

            if (isset($hist->kNewsletterHistory) && $hist->kNewsletterHistory > 0) {
                $smarty->assign('oNewsletterHistory', $hist);
            }
        }
    } elseif (mb_strlen(Request::verifyGPDataString('cSucheInaktiv')) > 0) { // Inaktive Abonnentensuche
        $cSuche = $db->escape(Text::filterXSS(Request::verifyGPDataString('cSucheInaktiv')));

        if (mb_strlen($cSuche) > 0) {
            $inactiveSearchSQL->cWHERE = " AND (tnewsletterempfaenger.cVorname LIKE '%" . $cSuche .
                "%' OR tnewsletterempfaenger.cNachname LIKE '%" . $cSuche .
                "%' OR tnewsletterempfaenger.cEmail LIKE '%" . $cSuche . "%')";
        }

        $smarty->assign('cSucheInaktiv', $cSuche);
    } elseif (mb_strlen(Request::verifyGPDataString('cSucheAktiv')) > 0) { // Aktive Abonnentensuche
        $cSuche = $db->escape(Text::filterXSS(Request::verifyGPDataString('cSucheAktiv')));

        if (mb_strlen($cSuche) > 0) {
            $activeSearchSQL->cWHERE = " AND (tnewsletterempfaenger.cVorname LIKE '%" . $cSuche .
                "%' OR tnewsletterempfaenger.cNachname LIKE '%" . $cSuche .
                "%' OR tnewsletterempfaenger.cEmail LIKE '%" . $cSuche . "%')";
        }

        $smarty->assign('cSucheAktiv', $cSuche);
    } elseif (Request::verifyGPCDataInt('vorschau') > 0) { // Vorschau
        $nlTemplateID = Request::verifyGPCDataInt('vorschau');
        // Infos der Vorlage aus DB holen
        $newsletterTPL = $db->query(
            "SELECT *, DATE_FORMAT(dStartZeit, '%d.%m.%Y %H:%i') AS Datum
                FROM tnewslettervorlage
                WHERE kNewsletterVorlage = " . $nlTemplateID,
            ReturnType::SINGLE_OBJECT
        );
        $preview       = null;
        if (Request::verifyGPCDataInt('iframe') === 1) {
            $step = 'vorlage_vorschau_iframe';
            $smarty->assign(
                'cURL',
                'newsletter.php?vorschau=' . $nlTemplateID . '&token=' . $_SESSION['jtl_token']
            );
            $preview = $instance->getPreview($newsletterTPL);
        } elseif (isset($newsletterTPL->kNewsletterVorlage) && $newsletterTPL->kNewsletterVorlage > 0) {
            $step                 = 'vorlage_vorschau';
            $newsletterTPL->oZeit = $admin->getDateData($newsletterTPL->dStartZeit);
            $preview              = $instance->getPreview($newsletterTPL);
        }
        if (is_string($preview)) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, $preview, 'errorNewsletterPreview');
        }
        $smarty->assign('oNewsletterVorlage', $newsletterTPL)
            ->assign('NettoPreise', Frontend::getCustomerGroup()->getIsMerchant());
    } elseif (Request::verifyGPCDataInt('newslettervorlagenstd') === 1) { // Vorlagen Std
        $productNos       = $_POST['cArtNr'] ?? null;
        $customerGroupIDs = $_POST['kKundengruppe'] ?? null;
        $groupString      = '';
        // Kundengruppen in einen String bauen
        if (is_array($customerGroupIDs) && count($customerGroupIDs) > 0) {
            foreach ($customerGroupIDs as $customerGroupID) {
                $groupString .= ';' . $customerGroupID . ';';
            }
        }
        $smarty->assign('oKampagne_arr', holeAlleKampagnen(false, true))
            ->assign('cTime', time());
        // Vorlage speichern
        if (Request::verifyGPCDataInt('vorlage_std_speichern') === 1) {
            $defaultTplID = Request::verifyGPCDataInt('kNewslettervorlageStd');
            if ($defaultTplID > 0) {
                $step       = 'vorlage_std_erstellen';
                $templateID = 0;
                if (Request::verifyGPCDataInt('kNewsletterVorlage') > 0) {
                    $templateID = Request::verifyGPCDataInt('kNewsletterVorlage');
                }
                $tpl    = $admin->getDefaultTemplate($defaultTplID, $templateID);
                $checks = $admin->saveDefaultTemplate(
                    $tpl,
                    $defaultTplID,
                    $_POST,
                    $templateID
                );
                if (is_array($checks) && count($checks) > 0) {
                    $smarty->assign('cPlausiValue_arr', $checks)
                        ->assign('cPostVar_arr', Text::filterXSS($_POST))
                        ->assign('oNewslettervorlageStd', $tpl);
                } else {
                    $step = 'uebersicht';
                    $smarty->assign('cTab', 'newslettervorlagen');
                    if ($templateID > 0) {
                        $alertHelper->addAlert(
                            Alert::TYPE_SUCCESS,
                            sprintf(
                                __('successNewsletterTemplateEdit'),
                                Text::filterXSS($_POST['cName'])
                            ),
                            'successNewsletterTemplateEdit'
                        );
                    } else {
                        $alertHelper->addAlert(
                            Alert::TYPE_SUCCESS,
                            sprintf(
                                __('successNewsletterTemplateSave'),
                                Text::filterXSS($_POST['cName'])
                            ),
                            'successNewsletterTemplateSave'
                        );
                    }
                }
            }
        } elseif (Request::verifyGPCDataInt('editieren') > 0) { // Editieren
            $templateID   = Request::verifyGPCDataInt('editieren');
            $step         = 'vorlage_std_erstellen';
            $tpl          = $admin->getDefaultTemplate(0, $templateID);
            $productData  = $admin->getProductData($tpl->cArtikel);
            $cgroup       = $admin->getCustomerGroupData($tpl->cKundengruppe);
            $revisionData = [];
            foreach ($tpl->oNewslettervorlageStdVar_arr as $item) {
                $revisionData[$item->kNewslettervorlageStdVar] = $item;
            }
            $smarty->assign('oNewslettervorlageStd', $tpl)
                ->assign('kArtikel_arr', $productData->kArtikel_arr)
                ->assign('cArtNr_arr', $productData->cArtNr_arr)
                ->assign('revisionData', $revisionData)
                ->assign('kKundengruppe_arr', $cgroup);
        }
        // Vorlage Std erstellen
        if (Request::verifyGPCDataInt('vorlage_std_erstellen') === 1
            && Request::verifyGPCDataInt('kNewsletterVorlageStd') > 0
        ) {
            $step                  = 'vorlage_std_erstellen';
            $kNewsletterVorlageStd = Request::verifyGPCDataInt('kNewsletterVorlageStd');
            // Hole Std Vorlage
            $tpl = $admin->getDefaultTemplate($kNewsletterVorlageStd);
            $smarty->assign('oNewslettervorlageStd', $tpl);
        }
    } elseif (Request::verifyGPCDataInt('newslettervorlagen') === 1) {
        // Vorlagen
        $smarty->assign('oKampagne_arr', holeAlleKampagnen(false, true));
        $productNos       = $_POST['cArtNr'] ?? null;
        $customerGroupIDs = $_POST['kKundengruppe'] ?? null;
        $groupString      = '';
        // Kundengruppen in einen String bauen
        if (is_array($customerGroupIDs) && count($customerGroupIDs) > 0) {
            foreach ($customerGroupIDs as $customerGroupID) {
                $groupString .= ';' . (int)$customerGroupID . ';';
            }
        }
        // Vorlage hinzufuegen
        if (isset($_POST['vorlage_erstellen'])) {
            $step   = 'vorlage_erstellen';
            $option = 'erstellen';
        } elseif (Request::getInt('editieren') > 0 || Request::getInt('vorbereiten') > 0) {
            // Vorlage editieren/vorbereiten
            $step         = 'vorlage_erstellen';
            $nlTemplateID = Request::verifyGPCDataInt('vorbereiten');
            if ($nlTemplateID === 0) {
                $nlTemplateID = Request::verifyGPCDataInt('editieren');
            }
            // Infos der Vorlage aus DB holen
            $newsletterTPL = $db->query(
                "SELECT *, DATE_FORMAT(dStartZeit, '%d.%m.%Y %H:%i') AS Datum
                    FROM tnewslettervorlage
                    WHERE kNewsletterVorlage = " . $nlTemplateID,
                ReturnType::SINGLE_OBJECT
            );

            $newsletterTPL->oZeit = $admin->getDateData($newsletterTPL->dStartZeit);

            if ($newsletterTPL->kNewsletterVorlage > 0) {
                $productData                = $admin->getProductData($newsletterTPL->cArtikel);
                $newsletterTPL->cArtikel    = mb_substr(
                    mb_substr($newsletterTPL->cArtikel, 1),
                    0,
                    -1
                );
                $newsletterTPL->cHersteller = mb_substr(
                    mb_substr($newsletterTPL->cHersteller, 1),
                    0,
                    -1
                );
                $newsletterTPL->cKategorie  = mb_substr(
                    mb_substr($newsletterTPL->cKategorie, 1),
                    0,
                    -1
                );
                $smarty->assign('kArtikel_arr', $productData->kArtikel_arr)
                    ->assign('cArtNr_arr', $productData->cArtNr_arr)
                    ->assign('kKundengruppe_arr', $admin->getCustomerGroupData($newsletterTPL->cKundengruppe));
            }

            $smarty->assign('oNewsletterVorlage', $newsletterTPL);
            if (isset($_GET['editieren'])) {
                $option = 'editieren';
            }
        } elseif (isset($_POST['speichern'])) { // Vorlage speichern
            $checks = $admin->saveTemplate($_POST);
            if (is_array($checks) && count($checks) > 0) {
                $step = 'vorlage_erstellen';
                $smarty->assign('cPlausiValue_arr', $checks)
                    ->assign('cPostVar_arr', Text::filterXSS($_POST))
                    ->assign('oNewsletterVorlage', $newsletterTPL);
            }
        } elseif (isset($_POST['speichern_und_senden'])) { // Vorlage speichern und senden
            unset($newsletter, $oKunde, $mailRecipient);
            $checks = $admin->saveTemplate($_POST);
            if (is_array($checks) && count($checks) > 0) {
                $step = 'vorlage_erstellen';
                $smarty->assign('cPlausiValue_arr', $checks)
                    ->assign('cPostVar_arr', Text::filterXSS($_POST))
                    ->assign('oNewsletterVorlage', $newsletterTPL);
            } elseif ($checks !== false) {
                // baue tnewsletter Objekt
                $newsletter                = new stdClass();
                $newsletter->kSprache      = $checks->kSprache;
                $newsletter->kKampagne     = $checks->kKampagne;
                $newsletter->cName         = $checks->cName;
                $newsletter->cBetreff      = $checks->cBetreff;
                $newsletter->cArt          = $checks->cArt;
                $newsletter->cArtikel      = $checks->cArtikel;
                $newsletter->cHersteller   = $checks->cHersteller;
                $newsletter->cKategorie    = $checks->cKategorie;
                $newsletter->cKundengruppe = $checks->cKundengruppe;
                $newsletter->cInhaltHTML   = $checks->cInhaltHTML;
                $newsletter->cInhaltText   = $checks->cInhaltText;
                $newsletter->dStartZeit    = $checks->dStartZeit;
                $newsletter->kNewsletter   = $db->insert('tnewsletter', $newsletter);
                // create a crontab entry
                $dao = new NewsletterCronDAO();
                $dao->setForeignKeyID($newsletter->kNewsletter);
                $db->insert('tcron', $dao->getData());
                // Baue Arrays mit kKeys
                $productIDs      = $instance->getKeys($checks->cArtikel, true);
                $manufacturerIDs = $instance->getKeys($checks->cHersteller);
                $categoryIDs     = $instance->getKeys($checks->cKategorie);
                // Baue Kampagnenobjekt, falls vorhanden in der Newslettervorlage
                $campaign = new Campaign($checks->kKampagne);
                // Baue Arrays von Objekten
                $products      = $instance->getProducts($productIDs, $campaign);
                $manufacturers = $instance->getManufacturers($manufacturerIDs, $campaign);
                $categories    = $instance->getCategories($categoryIDs, $campaign);
                // Kunden Dummy bauen
                $customer            = new stdClass();
                $customer->cAnrede   = 'm';
                $customer->cVorname  = 'Max';
                $customer->cNachname = 'Mustermann';
                // Emailempfaenger dummy bauen
                $mailRecipient              = new stdClass();
                $mailRecipient->cEmail      = $conf['newsletter']['newsletter_emailtest'];
                $mailRecipient->cLoeschCode = 'dc1338521613c3cfeb1988261029fe3058';
                $mailRecipient->cLoeschURL  = Shop::getURL() . '/?oc=' . $mailRecipient->cLoeschCode;

                $mailSmarty  = $instance->initSmarty();
                $recipient   = $instance->getRecipients($newsletter->kNewsletter);
                $groupString = '';
                $cgroupKey   = '';
                if (is_array($recipient->cKundengruppe_arr) && count($recipient->cKundengruppe_arr) > 0) {
                    $cgCount    = [];
                    $cgCount[0] = 0;     // Count Kundengruppennamen
                    $cgCount[1] = 0;     // Count Kundengruppenkeys
                    foreach ($recipient->cKundengruppe_arr as $cKundengruppeTMP) {
                        if (!empty($cKundengruppeTMP)) {
                            $oKundengruppeTMP = $db->select('tkundengruppe', 'kKundengruppe', (int)$cKundengruppeTMP);
                            if (mb_strlen($oKundengruppeTMP->cName) > 0) {
                                if ($cgCount[0] > 0) {
                                    $groupString .= ', ' . $oKundengruppeTMP->cName;
                                } else {
                                    $groupString .= $oKundengruppeTMP->cName;
                                }
                                $cgCount[0]++;
                            }
                            if ((int)$oKundengruppeTMP->kKundengruppe > 0) {
                                if ($cgCount[1] > 0) {
                                    $cgroupKey .= ';' . $oKundengruppeTMP->kKundengruppe;
                                } else {
                                    $cgroupKey .= $oKundengruppeTMP->kKundengruppe;
                                }
                                $cgCount[1]++;
                            }
                        } else {
                            if ($cgCount[0] > 0) {
                                $groupString .= ', Newsletterempfänger ohne Kundenkonto';
                            } else {
                                $groupString .= 'Newsletterempfänger ohne Kundenkonto';
                            }
                            if ($cgCount[1] > 0) {
                                $cgroupKey .= ';0';
                            } else {
                                $cgroupKey .= '0';
                            }
                            $cgCount[0]++;
                            $cgCount[1]++;
                        }
                    }
                }
                if (mb_strlen($groupString) > 0) {
                    $groupString = mb_substr($groupString, 0, -2);
                }
                $hist                   = new stdClass();
                $hist->kSprache         = $newsletter->kSprache;
                $hist->nAnzahl          = $recipient->nAnzahl;
                $hist->cBetreff         = $newsletter->cBetreff;
                $hist->cHTMLStatic      = $instance->getStaticHtml(
                    $newsletter,
                    $products,
                    $manufacturers,
                    $categories,
                    $campaign,
                    $mailRecipient,
                    $customer
                );
                $hist->cKundengruppe    = $groupString;
                $hist->cKundengruppeKey = ';' . $cgroupKey . ';';
                $hist->dStart           = $checks->dStartZeit;
                $db->insert('tnewsletterhistory', $hist);                // --TODO-- why already history here ?!?!

                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    sprintf(__('successNewsletterPrepared'), $newsletter->cName),
                    'successNewsletterPrepared'
                );
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('newsletterCronjobNotFound'), 'errorNewsletter');
            }
        } elseif (isset($_POST['speichern_und_testen'])) { // Vorlage speichern und testen
            $instance->initSmarty();

            $checks = $admin->saveTemplate($_POST);
            if (is_array($checks) && count($checks) > 0) {
                $step = 'vorlage_erstellen';
                $smarty->assign('cPlausiValue_arr', $checks)
                    ->assign('cPostVar_arr', Text::filterXSS($_POST))
                    ->assign('oNewsletterVorlage', $newsletterTPL);
            } else {
                $productIDs      = $instance->getKeys($checks->cArtikel, true);
                $manufacturerIDs = $instance->getKeys($checks->cHersteller);
                $categoryIDs     = $instance->getKeys($checks->cKategorie);
                $campaign        = new Campaign($checks->kKampagne);
                $products        = $instance->getProducts($productIDs, $campaign);
                $manufacturers   = $instance->getManufacturers($manufacturerIDs, $campaign);
                $categories      = $instance->getCategories($categoryIDs, $campaign);
                // dummy customer
                $customer            = new stdClass();
                $customer->cAnrede   = 'm';
                $customer->cVorname  = 'Max';
                $customer->cNachname = 'Mustermann';
                // dummy recipient
                $mailRecipient              = new stdClass();
                $mailRecipient->cEmail      = $conf['newsletter']['newsletter_emailtest'];
                $mailRecipient->cLoeschCode = 'dc1338521613c3cfeb1988261029fe3058';
                $mailRecipient->cLoeschURL  = Shop::getURL() . '/?oc=' . $mailRecipient->cLoeschCode;
                if (empty($mailRecipient->cEmail)) {
                    $result = __('errorTestTemplateEmpty');
                } else {
                    $result = $instance->send(
                        $checks,
                        $mailRecipient,
                        $products,
                        $manufacturers,
                        $categories,
                        $campaign,
                        $customer
                    );
                }
                if ($result !== true) {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, $result, 'errorNewsletter');
                } else {
                    $alertHelper->addAlert(
                        Alert::TYPE_SUCCESS,
                        sprintf(__('successTestEmailTo'), $checks->cName, $mailRecipient->cEmail),
                        'successNewsletterPrepared'
                    );
                }
            }
        } elseif (isset($_POST['loeschen'])) { // Vorlage loeschen
            $step = 'uebersicht';
            if (is_array($_POST['kNewsletterVorlage'])) {
                foreach ($_POST['kNewsletterVorlage'] as $nlTemplateID) {
                    $nlTPL = $db->query(
                        'SELECT kNewsletterVorlage, kNewslettervorlageStd
                            FROM tnewslettervorlage
                            WHERE kNewsletterVorlage = ' . (int)$nlTemplateID,
                        ReturnType::SINGLE_OBJECT
                    );
                    if (isset($nlTPL->kNewsletterVorlage) && $nlTPL->kNewsletterVorlage > 0) {
                        if (isset($nlTPL->kNewslettervorlageStd) && $nlTPL->kNewslettervorlageStd > 0) {
                            $db->query(
                                'DELETE tnewslettervorlage, tnewslettervorlagestdvarinhalt
                                    FROM tnewslettervorlage
                                    LEFT JOIN tnewslettervorlagestdvarinhalt
                                        ON tnewslettervorlagestdvarinhalt.kNewslettervorlage =
                                           tnewslettervorlage.kNewsletterVorlage
                                    WHERE tnewslettervorlage.kNewsletterVorlage = ' . (int)$nlTemplateID,
                                ReturnType::AFFECTED_ROWS
                            );
                        } else {
                            $db->delete(
                                'tnewslettervorlage',
                                'kNewsletterVorlage',
                                (int)$nlTemplateID
                            );
                        }
                    }
                }
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    __('successNewsletterTemplateDelete'),
                    'successNewsletterTemplateDelete'
                );
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneNewsletter'), 'errorAtLeastOneNewsletter');
            }
        }
        $smarty->assign('cOption', $option);
    }
}
if ($step === 'uebersicht') {
    $recipientsCount   = (int)$db->query(
        'SELECT COUNT(*) AS cnt
            FROM tnewsletterempfaenger
            WHERE tnewsletterempfaenger.nAktiv = 0' . $inactiveSearchSQL->cWHERE,
        ReturnType::SINGLE_OBJECT
    )->cnt;
    $queueCount        = (int)$db->query(
        "SELECT COUNT(*) AS cnt
            FROM tjobqueue
            WHERE jobType = 'newsletter'",
        ReturnType::SINGLE_OBJECT
    )->cnt;
    $templateCount     = (int)$db->query(
        'SELECT COUNT(*) AS cnt
            FROM tnewslettervorlage
            WHERE kSprache = ' . $languageID,
        ReturnType::SINGLE_OBJECT
    )->cnt;
    $historyCount      = (int)$db->query(
        'SELECT COUNT(*) AS cnt
            FROM tnewsletterhistory
            WHERE kSprache = ' . $languageID,
        ReturnType::SINGLE_OBJECT
    )->cnt;
    $pagiInactive      = (new Pagination('inaktive'))
        ->setItemCount($recipientsCount)
        ->assemble();
    $pagiQueue         = (new Pagination('warteschlange'))
        ->setItemCount($queueCount)
        ->assemble();
    $pagiTemplates     = (new Pagination('vorlagen'))
        ->setItemCount($templateCount)
        ->assemble();
    $pagiHistory       = (new Pagination('history'))
        ->setItemCount($historyCount)
        ->assemble();
    $pagiSubscriptions = (new Pagination('alle'))
        ->setItemCount($admin->getSubscriberCount($activeSearchSQL))
        ->assemble();
    $queue             = $db->queryPrepared(
        "SELECT
            l.cBetreff,
            q.tasksExecuted,
            c.cronID,
            c.foreignKeyID,
            c.startDate as 'Datum'
        FROM
            tcron c
            LEFT JOIN tjobqueue q ON c.cronID = q.cronID
            LEFT JOIN tnewsletter l ON c.foreignKeyID = l.kNewsletter
        WHERE
            c.jobType = 'newsletter'
        ORDER BY
            c.startDate DESC
        LIMIT " . $pagiQueue->getLimitSQL(),
        [
            'langID' => $languageID,
        ],
        ReturnType::ARRAY_OF_OBJECTS
    );
    if (!($instance instanceof Newsletter)) {
        $instance = new Newsletter($db, $conf);
    }
    foreach ($queue as $entry) {
        $entry->kNewsletter       = (int)$entry->foreignKeyID;
        $entry->nLimitN           = (int)$entry->tasksExecuted;
        $entry->kNewsletterQueue  = (int)$entry->cronID;
        $recipient                = $instance->getRecipients($entry->kNewsletter);
        $entry->nAnzahlEmpfaenger = $recipient->nAnzahl;
        $entry->cKundengruppe_arr = $recipient->cKundengruppe_arr;
    }
    $templates   = $db->query(
        'SELECT kNewsletterVorlage, kNewslettervorlageStd, cBetreff, cName
            FROM tnewslettervorlage
            WHERE kSprache = ' . $languageID . '
            ORDER BY kNewsletterVorlage DESC LIMIT ' . $pagiTemplates->getLimitSQL(),
        ReturnType::ARRAY_OF_OBJECTS
    );
    $defaultData = $db->query(
        'SELECT *
            FROM tnewslettervorlagestd
            WHERE kSprache = ' . $languageID . '
            ORDER BY cName',
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($defaultData as $tpl) {
        $tpl->oNewsletttervorlageStdVar_arr = $db->query(
            'SELECT *
                FROM tnewslettervorlagestdvar
                WHERE kNewslettervorlageStd = ' . (int)$tpl->kNewslettervorlageStd,
            ReturnType::ARRAY_OF_OBJECTS
        );
    }
    $inactiveRecipients = $db->query(
        "SELECT tnewsletterempfaenger.kNewsletterEmpfaenger, tnewsletterempfaenger.cVorname AS newsVorname,
            tnewsletterempfaenger.cNachname AS newsNachname, tkunde.cVorname, tkunde.cNachname,
            tnewsletterempfaenger.cEmail, tnewsletterempfaenger.nAktiv, tkunde.kKundengruppe, tkundengruppe.cName,
            DATE_FORMAT(tnewsletterempfaenger.dEingetragen, '%d.%m.%Y %H:%i') AS Datum
            FROM tnewsletterempfaenger
            LEFT JOIN tkunde
                ON tkunde.kKunde = tnewsletterempfaenger.kKunde
            LEFT JOIN tkundengruppe
                ON tkundengruppe.kKundengruppe = tkunde.kKundengruppe
            WHERE tnewsletterempfaenger.nAktiv = 0
            " . $inactiveSearchSQL->cWHERE . '
            ORDER BY tnewsletterempfaenger.dEingetragen DESC
            LIMIT ' . $pagiInactive->getLimitSQL(),
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($inactiveRecipients as $recipient) {
        $customer             = new Customer(isset($recipient->kKunde) ? (int)$recipient->kKunde : null);
        $recipient->cNachname = $customer->cNachname;
    }

    $history              = $db->queryPrepared(
        "SELECT kNewsletterHistory, nAnzahl, cBetreff, cKundengruppe,
            DATE_FORMAT(dStart, '%d.%m.%Y %H:%i') AS Datum
            FROM tnewsletterhistory
            WHERE kSprache = :lid
            ORDER BY dStart DESC
            LIMIT " . $pagiHistory->getLimitSQL(),
        ['lid' => $languageID],
        ReturnType::ARRAY_OF_OBJECTS
    );
    $customerGroupsByName = $db->query(
        'SELECT *
            FROM tkundengruppe
            ORDER BY cName',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $smarty->assign('kundengruppen', $customerGroupsByName)
        ->assign('oNewsletterQueue_arr', $queue)
        ->assign('oNewsletterVorlage_arr', $templates)
        ->assign('oNewslettervorlageStd_arr', $defaultData)
        ->assign('oNewsletterEmpfaenger_arr', $inactiveRecipients)
        ->assign('oNewsletterHistory_arr', $history)
        ->assign('oConfig_arr', getAdminSectionSettings(CONF_NEWSLETTER))
        ->assign('oAbonnenten_arr', $admin->getSubscribers(
            ' LIMIT ' . $pagiSubscriptions->getLimitSQL(),
            $activeSearchSQL
        ))
        ->assign('nMaxAnzahlAbonnenten', $admin->getSubscriberCount($activeSearchSQL))
        ->assign('oPagiInaktiveAbos', $pagiInactive)
        ->assign('oPagiWarteschlange', $pagiQueue)
        ->assign('oPagiVorlagen', $pagiTemplates)
        ->assign('oPagiHistory', $pagiHistory)
        ->assign('oPagiAlleAbos', $pagiSubscriptions);
}
if (isset($checks) && is_array($checks) && count($checks) > 0) {
    $alertHelper->addAlert(
        Alert::TYPE_ERROR,
        __('errorFillRequired'),
        'plausiErrorFillRequired'
    );
}
$smarty->assign('step', $step)
    ->assign('customerGroups', CustomerGroup::getGroups())
    ->assign('nRand', time())
    ->display('newsletter.tpl');
