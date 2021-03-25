<?php

use JTL\Alert\Alert;
use JTL\Backend\Settings\Manager;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;
use JTL\Shopsetting;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'einstellungen_inc.php';
/** @global \JTL\Smarty\JTLSmarty     $smarty */
/** @global \JTL\Backend\AdminAccount $oAccount */
$sectionID = isset($_REQUEST['kSektion']) ? (int)$_REQUEST['kSektion'] : 0;
$bSuche    = isset($_REQUEST['einstellungen_suchen']) && (int)$_REQUEST['einstellungen_suchen'] === 1;
$db        = Shop::Container()->getDB();
$getText   = Shop::Container()->getGetText();
$search    = Request::verifyGPDataString('cSuche');

$getText->loadConfigLocales(true, true);

if ($bSuche) {
    $oAccount->permission('SETTINGS_SEARCH_VIEW', true, true);
}

switch ($sectionID) {
    case 1:
        $oAccount->permission('SETTINGS_GLOBAL_VIEW', true, true);
        break;
    case 2:
        $oAccount->permission('SETTINGS_STARTPAGE_VIEW', true, true);
        break;
    case 3:
        $oAccount->permission('SETTINGS_EMAILS_VIEW', true, true);
        break;
    case 4:
        $oAccount->permission('SETTINGS_ARTICLEOVERVIEW_VIEW', true, true);
        // Sucheinstellungen haben eigene Logik
        header('Location: ' . Shop::getURL(true) . '/' . PFAD_ADMIN . 'sucheinstellungen.php');
        exit;
        break;
    case 5:
        $oAccount->permission('SETTINGS_ARTICLEDETAILS_VIEW', true, true);
        break;
    case 6:
        $oAccount->permission('SETTINGS_CUSTOMERFORM_VIEW', true, true);
        break;
    case 7:
        $oAccount->permission('SETTINGS_BASKET_VIEW', true, true);
        break;
    case 9:
        $oAccount->permission('SETTINGS_IMAGES_VIEW', true, true);
        break;
    default:
        $oAccount->redirectOnFailure();
        break;
}

$defaultCurrency = $db->select('twaehrung', 'cStandard', 'Y');
$section         = null;
$step            = 'uebersicht';
$oSections       = [];
$alertHelper     = Shop::Container()->getAlertService();
if ($sectionID > 0) {
    $step    = 'einstellungen bearbeiten';
    $section = $db->select('teinstellungensektion', 'kEinstellungenSektion', $sectionID);
    $smarty->assign('kEinstellungenSektion', $section->kEinstellungenSektion);
} else {
    $section = $db->select('teinstellungensektion', 'kEinstellungenSektion', CONF_GLOBAL);
    $smarty->assign('kEinstellungenSektion', CONF_GLOBAL);
}

$getText->localizeConfigSection($section);

if ($bSuche) {
    $step = 'einstellungen bearbeiten';
}
if (Request::postInt('einstellungen_bearbeiten') === 1 && $sectionID > 0 && Form::validateToken()) {
    // Einstellungssuche
    $sql = new stdClass();
    if ($bSuche) {
        $sql = bearbeiteEinstellungsSuche($search, true);
    }
    if (!isset($sql->cWHERE)) {
        $sql->cWHERE = '';
    }
    $step     = 'einstellungen bearbeiten';
    $confData = [];
    if (mb_strlen($sql->cWHERE) > 0) {
        $confData = $sql->oEinstellung_arr;
        $smarty->assign('cSearch', $sql->cSearch);
    } else {
        $section  = $db->select('teinstellungensektion', 'kEinstellungenSektion', $sectionID);
        $confData = $db->query(
            'SELECT *
                FROM teinstellungenconf
                WHERE kEinstellungenSektion = ' . (int)$section->kEinstellungenSektion . "
                    AND cConf = 'Y'
                    AND nModul = 0
                    AND nStandardanzeigen = 1 " . $sql->cWHERE . '
                ORDER BY nSort',
            ReturnType::ARRAY_OF_OBJECTS
        );
    }
    $settingSection = new Manager($db, $smarty);
    foreach ($confData as $i => $sectionData) {
        $value       = new stdClass();
        $sectionItem = $settingSection->getInstance((int)$sectionData->kEinstellungenSektion);
        if (isset($_POST[$confData[$i]->cWertName])) {
            $value->cWert                 = $_POST[$confData[$i]->cWertName];
            $value->cName                 = $confData[$i]->cWertName;
            $value->kEinstellungenSektion = $confData[$i]->kEinstellungenSektion;
            switch ($confData[$i]->cInputTyp) {
                case 'kommazahl':
                    $value->cWert = (float)str_replace(',', '.', $value->cWert);
                    break;
                case 'zahl':
                case 'number':
                    $value->cWert = (int)$value->cWert;
                    break;
                case 'text':
                    $value->cWert = mb_substr($value->cWert, 0, 255);
                    break;
                case 'pass':
                    break;
            }
            if ($sectionItem->validate($confData[$i], $_POST[$confData[$i]->cWertName])) {
                $db->delete(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [$confData[$i]->kEinstellungenSektion, $confData[$i]->cWertName]
                );
                if (is_array($_POST[$confData[$i]->cWertName])) {
                    foreach ($_POST[$confData[$i]->cWertName] as $cWert) {
                        $value->cWert = $cWert;
                        $db->insert('teinstellungen', $value);
                    }
                } else {
                    $db->insert('teinstellungen', $value);
                }
            }
        }
    }

    $db->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successConfigSave'), 'successConfigSave');
    $tagsToFlush = [CACHING_GROUP_OPTION];
    if ($sectionID === 1 || $sectionID === 4 || $sectionID === 5) {
        $tagsToFlush[] = CACHING_GROUP_CORE;
        $tagsToFlush[] = CACHING_GROUP_ARTICLE;
        $tagsToFlush[] = CACHING_GROUP_CATEGORY;
    } elseif ($sectionID === 8) {
        $tagsToFlush[] = CACHING_GROUP_BOX;
    }
    Shop::Container()->getCache()->flushTags($tagsToFlush);
    Shopsetting::getInstance()->reset();
}

if ($step === 'uebersicht') {
    $sections     = $db->query(
        'SELECT *
            FROM teinstellungensektion
            ORDER BY kEinstellungenSektion',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $sectionCount = count($sections);
    for ($i = 0; $i < $sectionCount; $i++) {
        $confCount = $db->queryPrepared(
            "SELECT COUNT(*) AS anz
                FROM teinstellungenconf
                WHERE kEinstellungenSektion = :sid
                    AND cConf = 'Y'
                    AND nStandardAnzeigen = 1
                    AND nModul = 0",
            ['sid' => (int)$sections[$i]->kEinstellungenSektion],
            ReturnType::SINGLE_OBJECT
        );

        $sections[$i]->anz = $confCount->anz;
        $getText->localizeConfigSection($sections[$i]);
    }
    $smarty->assign('Sektionen', $sections);
}
if ($step === 'einstellungen bearbeiten') {
    $confData = [];
    $sql      = new stdClass();
    if ($bSuche) {
        $sql = bearbeiteEinstellungsSuche($search);
    }
    if (!isset($sql->cWHERE)) {
        $sql->cWHERE = '';
    }
    if (mb_strlen($sql->cWHERE) > 0) {
        $confData = $sql->oEinstellung_arr;
        $smarty->assign('cSearch', $sql->cSearch)
               ->assign('cSuche', $sql->cSuche);
    } else {
        $confData = $db->query(
            'SELECT *
                FROM teinstellungenconf
                WHERE nModul = 0
                    AND nStandardAnzeigen = 1
                    AND kEinstellungenSektion = ' . (int)$section->kEinstellungenSektion . ' ' .
                $sql->cWHERE . '
                ORDER BY nSort',
            ReturnType::ARRAY_OF_OBJECTS
        );
    }
    $settingSection = new Manager($db, $smarty);
    foreach ($confData as $config) {
        $config->kEinstellungenConf    = (int)$config->kEinstellungenConf;
        $config->kEinstellungenSektion = (int)$config->kEinstellungenSektion;
        $config->nStandardAnzeigen     = (int)$config->nStandardAnzeigen;
        $config->nSort                 = (int)$config->nSort;
        $config->nModul                = (int)$config->nModul;
        $sectionItem                   = $settingSection->getInstance((int)$config->kEinstellungenSektion);
        $getText->localizeConfig($config);
        //@ToDo: Setting 492 is the only one listbox at the moment.
        //But In special case of setting 492 values come from kKundengruppe instead of teinstellungenconfwerte
        if ($config->cInputTyp === 'listbox' && $config->kEinstellungenConf === 492) {
            $config->ConfWerte = $db->query(
                'SELECT kKundengruppe AS cWert, cName
                    FROM tkundengruppe
                    ORDER BY cStandard DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
        } elseif (in_array($config->cInputTyp, ['selectbox', 'listbox'], true)) {
            $config->ConfWerte = $db->selectAll(
                'teinstellungenconfwerte',
                'kEinstellungenConf',
                (int)$config->kEinstellungenConf,
                '*',
                'nSort'
            );

            $getText->localizeConfigValues($config, $config->ConfWerte);
        }
        if ($config->cInputTyp === 'listbox') {
            $setValue              = $db->selectAll(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [(int)$config->kEinstellungenSektion, $config->cWertName]
            );
            $config->gesetzterWert = $setValue;
        } else {
            $setValue              = $db->select(
                'teinstellungen',
                'kEinstellungenSektion',
                (int)$config->kEinstellungenSektion,
                'cName',
                $config->cWertName
            );
            $config->gesetzterWert = isset($setValue->cWert)
                ? Text::htmlentities($setValue->cWert)
                : null;
        }
        $sectionItem->setValue($config, $setValue);
        $oSections[(int)$config->kEinstellungenSektion] = $sectionItem;
    }

    $smarty->assign('Sektion', $section)
           ->assign('Conf', mb_strlen($search) > 0
               ? $confData
               : filteredConfData($confData, Request::verifyGPDataString('group')))
           ->assign(
               'title',
               __('settings') . ': ' . (Request::verifyGPDataString('group') !== ''
                   ? __(Request::verifyGPDataString('group'))
                   : __($section->cName)
               )
           )
           ->assign('oSections', $oSections);
}

$smarty->assign('cPrefDesc', filteredConfDescription($sectionID))
       ->assign('cPrefURL', __('prefURL' . $sectionID))
       ->assign('step', $step)
       ->assign('waehrung', $defaultCurrency->cName)
       ->display('einstellungen.tpl');
