<?php

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Seo;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('MODULE_LIVESEARCH_VIEW', true, true);

setzeSprache();
$languageID  = (int)$_SESSION['editLanguageID'];
$settingsIDs = [
    'livesuche_max_ip_count',
    'sonstiges_livesuche_all_top_count',
    'sonstiges_livesuche_all_last_count',
    'boxen_livesuche_count',
    'boxen_livesuche_anzeigen'
];
$db          = Shop::Container()->getDB();
$alertHelper = Shop::Container()->getAlertService();

$cLivesucheSQL         = new stdClass();
$cLivesucheSQL->cWhere = '';
$cLivesucheSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche DESC ';
if (mb_strlen(Request::verifyGPDataString('cSuche')) > 0) {
    $cSuche = $db->escape(Text::filterXSS(Request::verifyGPDataString('cSuche')));

    if (mb_strlen($cSuche) > 0) {
        $cLivesucheSQL->cWhere = " AND tsuchanfrage.cSuche LIKE '%" . $cSuche . "%'";
        $smarty->assign('cSuche', $cSuche);
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSearchTermMissing'), 'errorSearchTermMissing');
    }
}
if (Request::verifyGPCDataInt('einstellungen') === 1) {
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSettings($settingsIDs, $_POST, [CACHING_GROUP_OPTION], true),
        'saveSettings'
    );
    $smarty->assign('tab', 'einstellungen');
}

if (Request::verifyGPCDataInt('nSort') > 0) {
    $smarty->assign('nSort', Request::verifyGPCDataInt('nSort'));

    switch (Request::verifyGPCDataInt('nSort')) {
        case 1:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.cSuche ASC ';
            break;
        case 11:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.cSuche DESC ';
            break;
        case 2:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche DESC ';
            break;
        case 22:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche ASC ';
            break;
        case 3:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAktiv DESC ';
            break;
        case 33:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAktiv ASC ';
            break;
    }
} else {
    $smarty->assign('nSort', -1);
}

if (Request::postInt('livesuche') === 1) { //Formular wurde abgeschickt
    // Suchanfragen aktualisieren
    if (isset($_POST['suchanfragenUpdate'])) {
        if (GeneralObject::hasCount('kSuchanfrageAll', $_POST)) {
            foreach ($_POST['kSuchanfrageAll'] as $searchQueryID) {
                if (mb_strlen($_POST['nAnzahlGesuche_' . $searchQueryID]) > 0
                    && (int)$_POST['nAnzahlGesuche_' . $searchQueryID] > 0
                ) {
                    $_upd                 = new stdClass();
                    $_upd->nAnzahlGesuche = (int)$_POST['nAnzahlGesuche_' . $searchQueryID];
                    $db->update('tsuchanfrage', 'kSuchanfrage', (int)$searchQueryID, $_upd);
                }
            }
        }
        // Eintragen in die Mapping Tabelle
        $searchQueries = $db->selectAll(
            'tsuchanfrage',
            'kSprache',
            $languageID,
            '*',
            'nAnzahlGesuche DESC'
        );
        // Wurde ein Mapping durchgefuehrt
        $mappingExists = 0;
        if (is_array($_POST['kSuchanfrageAll']) && count($_POST['kSuchanfrageAll']) > 0) {
            $whereIn   = ' IN (';
            $deleteIDs = [];
            // nAktiv Reihe updaten
            foreach ($_POST['kSuchanfrageAll'] as $i => $searchQueryID) {
                $searchQueryID = (int)$searchQueryID;
                $db->update('tsuchanfrage', 'kSuchanfrage', $searchQueryID, (object)['nAktiv' => 0]);
                $deleteIDs[] = $searchQueryID;
            }
            $whereIn .= implode(',', $deleteIDs);
            $whereIn .= ')';
            // Deaktivierte Suchanfragen aus tseo loeschen
            $db->query(
                "DELETE FROM tseo
                    WHERE cKey = 'kSuchanfrage'
                        AND kKey" . $whereIn,
                ReturnType::AFFECTED_ROWS
            );
            // Deaktivierte Suchanfragen in tsuchanfrage updaten
            $db->query(
                "UPDATE tsuchanfrage
                    SET cSeo = ''
                    WHERE kSuchanfrage" . $whereIn,
                ReturnType::AFFECTED_ROWS
            );
            foreach (Request::verifyGPDataIntegerArray('nAktiv') as $active) {
                $query = $db->select('tsuchanfrage', 'kSuchanfrage', $active);
                $db->delete(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kSuchanfrage', $active, $languageID]
                );
                // Aktivierte Suchanfragen in tseo eintragen
                $ins           = new stdClass();
                $ins->cSeo     = Seo::checkSeo(Seo::getSeo($query->cSuche));
                $ins->cKey     = 'kSuchanfrage';
                $ins->kKey     = $active;
                $ins->kSprache = $languageID;
                $db->insert('tseo', $ins);
                // Aktivierte Suchanfragen in tsuchanfrage updaten
                $upd         = new stdClass();
                $upd->nAktiv = 1;
                $upd->cSeo   = $ins->cSeo;
                $db->update('tsuchanfrage', 'kSuchanfrage', $active, $upd);
            }
        }
        foreach ($searchQueries as $sucheanfrage) {
            $index = 'mapping_' . $sucheanfrage->kSuchanfrage;
            if (!isset($_POST[$index])
                || mb_convert_case($sucheanfrage->cSuche, MB_CASE_LOWER) !==
                mb_convert_case($_POST[$index], MB_CASE_LOWER)
            ) {
                if (!empty($_POST[$index])) {
                    $mappingExists           = 1;
                    $mapping                 = new stdClass();
                    $mapping->kSprache       = $languageID;
                    $mapping->cSuche         = $sucheanfrage->cSuche;
                    $mapping->cSucheNeu      = $_POST[$index];
                    $mapping->nAnzahlGesuche = $sucheanfrage->nAnzahlGesuche;
                    $Neuesuche               = $db->select(
                        'tsuchanfrage',
                        'cSuche',
                        $mapping->cSucheNeu
                    );
                    if (isset($Neuesuche->kSuchanfrage) && $Neuesuche->kSuchanfrage > 0) {
                        $db->insert('tsuchanfragemapping', $mapping);
                        $db->queryPrepared(
                            'UPDATE tsuchanfrage
                                SET nAnzahlGesuche = nAnzahlGesuche + :cnt
                                WHERE kSprache = :lid
                                    AND cSuche = :src',
                            [
                                'cnt' => $sucheanfrage->nAnzahlGesuche,
                                'lid' => $languageID,
                                'src' => $_POST[$index]
                            ],
                            ReturnType::DEFAULT
                        );
                        $db->delete(
                            'tsuchanfrage',
                            'kSuchanfrage',
                            (int)$sucheanfrage->kSuchanfrage
                        );
                        $upd       = new stdClass();
                        $upd->kKey = (int)$Neuesuche->kSuchanfrage;
                        $db->update(
                            'tseo',
                            ['cKey', 'kKey'],
                            ['kSuchanfrage', (int)$sucheanfrage->kSuchanfrage],
                            $upd
                        );

                        $succesMapMessage .= sprintf(
                            __('successSearchMap'),
                            $mapping->cSuche,
                            $mapping->cSucheNeu
                        ) . '<br />';
                    }
                }
            } else {
                $errorMapMessage .= sprintf(__('errorSearchMapSelf'), $_POST[$index]);
            }
        }
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, $succesMapMessage ?? '', 'successSearchMap');
        $alertHelper->addAlert(Alert::TYPE_ERROR, $errorMapMessage ?? '', 'errorSearchMap');
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSearchRefresh'), 'successSearchRefresh');
    } elseif (isset($_POST['submitMapping'])) { // Auswahl mappen
        $mapping = Request::verifyGPDataString('cMapping');

        if (mb_strlen($mapping) > 0) {
            $mappingQueryIDs = Request::verifyGPDataIntegerArray('kSuchanfrage');
            if (count($mappingQueryIDs) > 0) {
                foreach ($mappingQueryIDs as $searchQueryID) {
                    $query = $db->select('tsuchanfrage', 'kSuchanfrage', $searchQueryID);
                    if ($query->kSuchanfrage > 0) {
                        if (mb_convert_case($query->cSuche, MB_CASE_LOWER) !==
                            mb_convert_case($mapping, MB_CASE_LOWER)
                        ) {
                            $oSuchanfrageNeu = $db->select('tsuchanfrage', 'cSuche', $mapping);
                            if (isset($oSuchanfrageNeu->kSuchanfrage) && $oSuchanfrageNeu->kSuchanfrage > 0) {
                                $queryMapping                 = new stdClass();
                                $queryMapping->kSprache       = $languageID;
                                $queryMapping->cSuche         = $query->cSuche;
                                $queryMapping->cSucheNeu      = $mapping;
                                $queryMapping->nAnzahlGesuche = $query->nAnzahlGesuche;

                                $mappingID = $db->insert(
                                    'tsuchanfragemapping',
                                    $queryMapping
                                );
                                if ($mappingID > 0) {
                                    $db->queryPrepared(
                                        'UPDATE tsuchanfrage
                                            SET nAnzahlGesuche = nAnzahlGesuche + :cnt
                                            WHERE kSprache = :lid
                                                AND kSuchanfrage = :sid',
                                        [
                                            'cnt' => $query->nAnzahlGesuche,
                                            'lid' => $languageID,
                                            'sid' => $oSuchanfrageNeu->kSuchanfrage
                                        ],
                                        ReturnType::DEFAULT
                                    );
                                    $db->delete(
                                        'tsuchanfrage',
                                        'kSuchanfrage',
                                        (int)$query->kSuchanfrage
                                    );
                                    $db->queryPrepared(
                                        "UPDATE tseo
                                            SET kKey = :kid
                                            WHERE cKey = 'kSuchanfrage'
                                                AND kKey = :sid",
                                        [
                                            'kid' => (int)$oSuchanfrageNeu->kSuchanfrage,
                                            'sid' => (int)$query->kSuchanfrage
                                        ],
                                        ReturnType::DEFAULT
                                    );

                                    $alertHelper->addAlert(
                                        Alert::TYPE_SUCCESS,
                                        sprintf(__('successSearchMapMultiple'), $queryMapping->cSucheNeu),
                                        'successSearchMapMultiple'
                                    );
                                }
                            } else {
                                $alertHelper->addAlert(
                                    Alert::TYPE_ERROR,
                                    __('errorSearchMapToNotExist'),
                                    'errorSearchMapToNotExist'
                                );
                                break;
                            }
                        } else {
                            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSearchMapSelf'), 'errorSearchMapSelf');
                            break;
                        }
                    } else {
                        $alertHelper->addAlert(
                            Alert::TYPE_ERROR,
                            __('errorSearchMapNotExist'),
                            'errorSearchMapNotExist'
                        );
                        break;
                    }
                }
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
            }
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorMapNameMissing'), 'errorMapNameMissing');
        }
    } elseif (isset($_POST['delete'])) { // Auswahl loeschen
        $deleteQueryIDs = Request::verifyGPDataIntegerArray('kSuchanfrage');
        if (count($deleteQueryIDs) > 0) {
            foreach ($deleteQueryIDs as $searchQueryID) {
                $data          = $db->select(
                    'tsuchanfrage',
                    'kSuchanfrage',
                    $searchQueryID
                );
                $obj           = new stdClass();
                $obj->kSprache = (int)$data->kSprache;
                $obj->cSuche   = $data->cSuche;

                $db->delete('tsuchanfrage', 'kSuchanfrage', $searchQueryID);
                $db->insert('tsuchanfrageblacklist', $obj);
                // Aus tseo loeschen
                $db->delete('tseo', ['cKey', 'kKey'], ['kSuchanfrage', $searchQueryID]);
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    sprintf(__('successSearchDelete'), $data->cSuche),
                    'successSearchDelete'
                );
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    sprintf(__('successSearchBlacklist'), $data->cSuche),
                    'successSearchBlacklist'
                );
            }
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
        }
    }
} elseif (Request::postInt('livesuche') === 2) { // Erfolglos mapping
    if (isset($_POST['erfolglosEdit'])) { // Editieren
        $smarty->assign('nErfolglosEditieren', 1);
    } elseif (isset($_POST['erfolglosUpdate'])) { // Update
        $failedQueries = $db->selectAll(
            'tsuchanfrageerfolglos',
            'kSprache',
            $languageID,
            '*',
            'nAnzahlGesuche DESC'
        );
        foreach ($failedQueries as $failedQuery) {
            $idx = 'mapping_' . $failedQuery->kSuchanfrageErfolglos;
            if (mb_strlen(Request::postVar($idx, '')) > 0) {
                if (mb_convert_case($failedQuery->cSuche, MB_CASE_LOWER) !==
                    mb_convert_case($_POST[$idx], MB_CASE_LOWER)
                ) {
                    $mapping                 = new stdClass();
                    $mapping->kSprache       = $languageID;
                    $mapping->cSuche         = $failedQuery->cSuche;
                    $mapping->cSucheNeu      = $_POST[$idx];
                    $mapping->nAnzahlGesuche = $failedQuery->nAnzahlGesuche;

                    $oldQuery = $db->select(
                        'tsuchanfrageerfolglos',
                        'cSuche',
                        $mapping->cSuche
                    );
                    //check if loops would be created with mapping
                    $bIsLoop           = false;
                    $sSearchMappingTMP = $mapping->cSucheNeu;
                    while (!empty($sSearchMappingTMP)) {
                        if ($sSearchMappingTMP === $mapping->cSuche) {
                            $bIsLoop = true;
                            break;
                        }
                        $oSearchMappingNextTMP = $db->select(
                            'tsuchanfragemapping',
                            'kSprache',
                            $languageID,
                            'cSuche',
                            $sSearchMappingTMP
                        );
                        if (!empty($oSearchMappingNextTMP->cSucheNeu)) {
                            $sSearchMappingTMP = $oSearchMappingNextTMP->cSucheNeu;
                        } else {
                            $sSearchMappingTMP = null;
                        }
                    }

                    if (!$bIsLoop) {
                        if (isset($oldQuery->kSuchanfrageErfolglos) && $oldQuery->kSuchanfrageErfolglos > 0) {
                            $oCheckMapping = $db->select(
                                'tsuchanfrageerfolglos',
                                'cSuche',
                                $mapping->cSuche
                            );
                            $db->insert('tsuchanfragemapping', $mapping);
                            $db->delete(
                                'tsuchanfrageerfolglos',
                                'kSuchanfrageErfolglos',
                                (int)$oldQuery->kSuchanfrageErfolglos
                            );

                            $alertHelper->addAlert(
                                Alert::TYPE_SUCCESS,
                                sprintf(
                                    __('successSearchMap'),
                                    $mapping->cSuche,
                                    $mapping->cSucheNeu
                                ),
                                'successSearchMap'
                            );
                        }
                    } else {
                        $alertHelper->addAlert(
                            Alert::TYPE_ERROR,
                            sprintf(
                                __('errorSearchMapLoop'),
                                $mapping->cSuche,
                                $mapping->cSucheNeu
                            ),
                            'errorSearchMapLoop'
                        );
                    }
                } else {
                    $alertHelper->addAlert(
                        Alert::TYPE_ERROR,
                        sprintf(__('errorSearchMapSelf'), $failedQuery->cSuche),
                        'errorSearchMapSelf'
                    );
                }
            } elseif (Request::postInt('nErfolglosEditieren') === 1) {
                $idx = 'cSuche_' . $failedQuery->kSuchanfrageErfolglos;

                $failedQuery->cSuche = Text::filterXSS($_POST[$idx]);
                $upd                 = new stdClass();
                $upd->cSuche         = $failedQuery->cSuche;
                $db->update(
                    'tsuchanfrageerfolglos',
                    'kSuchanfrageErfolglos',
                    (int)$failedQuery->kSuchanfrageErfolglos,
                    $upd
                );
            }
        }
    } elseif (isset($_POST['erfolglosDelete'])) { // Loeschen
        $queryIDs = $_POST['kSuchanfrageErfolglos'];
        if (is_array($queryIDs) && count($queryIDs) > 0) {
            foreach ($queryIDs as $queryID) {
                $db->delete(
                    'tsuchanfrageerfolglos',
                    'kSuchanfrageErfolglos',
                    (int)$queryID
                );
            }
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                __('successSearchDeleteMultiple'),
                'successSearchDeleteMultiple'
            );
        } else {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                __('errorAtLeastOneSearch'),
                'errorAtLeastOneSearch'
            );
        }
    }
    $smarty->assign('tab', 'erfolglos');
} elseif (Request::postInt('livesuche') === 3) { // Blacklist
    $blacklist = $_POST['suchanfrageblacklist'];
    $blacklist = explode(';', $blacklist);
    $count     = count($blacklist);

    $db->delete('tsuchanfrageblacklist', 'kSprache', $languageID);
    for ($i = 0; $i < $count; $i++) {
        if (!empty($blacklist[$i])) {
            $ins           = new stdClass();
            $ins->cSuche   = $blacklist[$i];
            $ins->kSprache = $languageID;
            $db->insert('tsuchanfrageblacklist', $ins);
        }
    }
    $smarty->assign('tab', 'blacklist');
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successBlacklistRefresh'), 'successBlacklistRefresh');
} elseif (Request::postInt('livesuche') === 4) { // Mappinglist
    if (isset($_POST['delete'])) {
        if (is_array($_POST['kSuchanfrageMapping'])) {
            foreach ($_POST['kSuchanfrageMapping'] as $mappingID) {
                $queryMapping = $db->select(
                    'tsuchanfragemapping',
                    'kSuchanfrageMapping',
                    (int)$mappingID
                );
                if (isset($queryMapping->cSuche) && mb_strlen($queryMapping->cSuche) > 0) {
                    $db->delete(
                        'tsuchanfragemapping',
                        'kSuchanfrageMapping',
                        (int)$mappingID
                    );
                    $alertHelper->addAlert(
                        Alert::TYPE_SUCCESS,
                        sprintf(__('successSearchMapDelete'), $queryMapping->cSuche),
                        'successSearchMapDelete'
                    );
                } else {
                    $alertHelper->addAlert(
                        Alert::TYPE_ERROR,
                        sprintf(__('errorSearchMapNotFound'), $mappingID),
                        'errorSearchMapNotFound'
                    );
                }
            }
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneSearchMap'), 'errorAtLeastOneSearchMap');
        }
    }
    $smarty->assign('tab', 'mapping');
}

$queryCount        = (int)$db->query(
    'SELECT COUNT(*) AS cnt
        FROM tsuchanfrage
        WHERE kSprache = ' . $languageID . $cLivesucheSQL->cWhere,
    ReturnType::SINGLE_OBJECT
)->cnt;
$failedQueryCount  = (int)$db->query(
    'SELECT COUNT(*) AS cnt
        FROM tsuchanfrageerfolglos
        WHERE kSprache = ' . $languageID,
    ReturnType::SINGLE_OBJECT
)->cnt;
$mappingCount      = (int)$db->query(
    'SELECT COUNT(*) AS cnt
        FROM tsuchanfragemapping
        WHERE kSprache = ' . $languageID,
    ReturnType::SINGLE_OBJECT
)->cnt;
$paginationQueries = (new Pagination('suchanfragen'))
    ->setItemCount($queryCount)
    ->assemble();
$paginationFailed  = (new Pagination('erfolglos'))
    ->setItemCount($failedQueryCount)
    ->assemble();
$paginationMapping = (new Pagination('mapping'))
    ->setItemCount($mappingCount)
    ->assemble();

$searchQueries = $db->query(
    "SELECT tsuchanfrage.*, tseo.cSeo AS tcSeo
        FROM tsuchanfrage
        LEFT JOIN tseo ON tseo.cKey = 'kSuchanfrage'
            AND tseo.kKey = tsuchanfrage.kSuchanfrage
            AND tseo.kSprache = " . $languageID . '
        WHERE tsuchanfrage.kSprache = ' . $languageID . '
            ' . $cLivesucheSQL->cWhere . '
        GROUP BY tsuchanfrage.kSuchanfrage
        ORDER BY ' . $cLivesucheSQL->cOrder . '
        LIMIT ' . $paginationQueries->getLimitSQL(),
    ReturnType::ARRAY_OF_OBJECTS
);

if (isset($searchQueries->tcSeo) && mb_strlen($searchQueries->tcSeo) > 0) {
    $searchQueries->cSeo = $searchQueries->tcSeo;
}
unset($searchQueries->tcSeo);

$failedQueries  = $db->query(
    'SELECT *
        FROM tsuchanfrageerfolglos
        WHERE kSprache = ' . $languageID . '
        ORDER BY nAnzahlGesuche DESC
        LIMIT ' . $paginationFailed->getLimitSQL(),
    ReturnType::ARRAY_OF_OBJECTS
);
$queryBlacklist = $db->query(
    'SELECT *
        FROM tsuchanfrageblacklist
        WHERE kSprache = ' . $languageID . '
        ORDER BY kSuchanfrageBlacklist',
    ReturnType::ARRAY_OF_OBJECTS
);
$queryMapping   = $db->query(
    'SELECT *
        FROM tsuchanfragemapping
        WHERE kSprache = ' . $languageID . '
        LIMIT ' . $paginationMapping->getLimitSQL(),
    ReturnType::ARRAY_OF_OBJECTS
);
$smarty->assign('oConfig_arr', getAdminSectionSettings($settingsIDs, true))
    ->assign('Suchanfragen', $searchQueries)
    ->assign('Suchanfragenerfolglos', $failedQueries)
    ->assign('Suchanfragenblacklist', $queryBlacklist)
    ->assign('Suchanfragenmapping', $queryMapping)
    ->assign('oPagiSuchanfragen', $paginationQueries)
    ->assign('oPagiErfolglos', $paginationFailed)
    ->assign('oPagiMapping', $paginationMapping)
    ->display('livesuche.tpl');
