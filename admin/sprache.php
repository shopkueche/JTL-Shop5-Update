<?php
/**
 * @global \JTL\Smarty\JTLSmarty $smarty
 */

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Filter;
use JTL\Pagination\Operation;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('LANGUAGE_VIEW', true, true);
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_exporter_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_importer_inc.php';

$db          = Shop::Container()->getDB();
$cache       = Shop::Container()->getCache();
$alertHelper = Shop::Container()->getAlertService();
$tab         = $_REQUEST['tab'] ?? 'variables';
$step        = 'overview';
$lang        = LanguageHelper::getInstance($db, $cache);
setzeSprache();
$langCode = $_SESSION['editLanguageCode'];
$variable = null;

if (isset($_FILES['csvfile']['tmp_name'])
    && Form::validateToken()
    && Request::verifyGPDataString('importcsv') === 'langvars'
) {
    $csvFilename = $_FILES['csvfile']['tmp_name'];
    $importType  = Request::verifyGPCDataInt('importType');
    $res         = $lang->import($csvFilename, $langCode, $importType);

    if ($res !== false) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, sprintf(__('successImport'), $res), 'successImport');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorImport'), 'errorImport');
    }
    $lang = new LanguageHelper($db, $cache);
}

$langIsoID          = $lang->getLangIDFromIso($langCode)->kSprachISO ?? 0;
$installedLanguages = $lang->getInstalled();
$availableLanguages = $lang->getAvailable();
$sections           = $lang->getSections();
$langActive         = false;
$smarty->assign('availableLanguages', $availableLanguages);

if (count($installedLanguages) !== count($availableLanguages)) {
    $alertHelper->addAlert(Alert::TYPE_NOTE, __('newLangAvailable'), 'newLangAvailable');
}

foreach ($installedLanguages as $language) {
    if ($language->getIso() === $langCode) {
        $langActive = true;
        break;
    }
}
if (isset($_REQUEST['action']) && Form::validateToken()) {
    $action = $_REQUEST['action'];

    switch ($action) {
        case 'newvar':
            // neue Variable erstellen
            $step                     = 'newvar';
            $variable                 = new stdClass();
            $variable->kSprachsektion = isset($_REQUEST['kSprachsektion']) ? (int)$_REQUEST['kSprachsektion'] : 1;
            $variable->cName          = $_REQUEST['cName'] ?? '';
            $variable->cWert_arr      = [];
            break;
        case 'delvar':
            // Variable loeschen
            $name = Request::getVar('cName');
            $lang->loesche(Request::getInt('kSprachsektion'), $name);
            $cache->flushTags([CACHING_GROUP_LANGUAGE]);
            $db->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                sprintf(__('successVarRemove'), $name),
                'successVarRemove'
            );
            break;
        case 'savevar':
            // neue Variable speichern
            $variable                 = new stdClass();
            $variable->kSprachsektion = (int)$_REQUEST['kSprachsektion'];
            $variable->cName          = $_REQUEST['cName'];
            $variable->cWert_arr      = $_REQUEST['cWert_arr'];
            $variable->cWertAlt_arr   = [];
            $variable->bOverwrite_arr = $_REQUEST['bOverwrite_arr'] ?? [];
            $errors                   = [];
            $variable->cSprachsektion = $db
                ->select(
                    'tsprachsektion',
                    'kSprachsektion',
                    (int)$variable->kSprachsektion
                )
                ->cName;

            $data = $db->queryPrepared(
                'SELECT s.cNameDeutsch AS cSpracheName, sw.cWert, si.cISO
                    FROM tsprachwerte AS sw
                        JOIN tsprachiso AS si
                            ON si.kSprachISO = sw.kSprachISO
                        JOIN tsprache AS s
                            ON s.cISO = si.cISO 
                    WHERE sw.cName = :cName
                        AND sw.kSprachsektion = :kSprachsektion',
                ['cName' => $variable->cName, 'kSprachsektion' => $variable->kSprachsektion],
                ReturnType::ARRAY_OF_OBJECTS
            );

            foreach ($data as $item) {
                $variable->cWertAlt_arr[$item->cISO] = $item->cWert;
            }

            if (!preg_match('/([\w\d]+)/', $variable->cName)) {
                $errors[] = __('errorVarFormat');
            }

            if (count($variable->bOverwrite_arr) !== count($data)) {
                $errors[] = sprintf(
                    __('errorVarExistsForLang'),
                    implode(
                        ', ',
                        array_map(static function ($oWertDB) {
                            return $oWertDB->cSpracheName;
                        }, $data)
                    )
                );
            }

            if (count($errors) > 0) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, implode('<br>', $errors), 'newVar');
                $step = 'newvar';
            } else {
                foreach ($variable->cWert_arr as $cISO => $cWert) {
                    if (isset($variable->cWertAlt_arr[$cISO])) {
                        // alter Wert vorhanden
                        if ((int)$variable->bOverwrite_arr[$cISO] === 1) {
                            // soll ueberschrieben werden
                            $lang
                                ->setzeSprache($cISO)
                                ->set($variable->kSprachsektion, $variable->cName, $cWert);
                        }
                    } else {
                        // kein alter Wert vorhanden
                        $lang->fuegeEin($cISO, $variable->kSprachsektion, $variable->cName, $cWert);
                    }
                }

                $db->delete(
                    'tsprachlog',
                    ['cSektion', 'cName'],
                    [$variable->cSprachsektion, $variable->cName]
                );
                $cache->flushTags([CACHING_GROUP_LANGUAGE]);
                $db->query(
                    'UPDATE tglobals SET dLetzteAenderung = NOW()',
                    ReturnType::DEFAULT
                );
            }

            break;
        case 'saveall':
            // geaenderte Variablen speichern
            $modified = [];
            foreach ($_REQUEST['cWert_arr'] as $kSektion => $sectionValues) {
                foreach ($sectionValues as $name => $cWert) {
                    if ((int)$_REQUEST['bChanged_arr'][$kSektion][$name] === 1) {
                        // wurde geaendert => speichern
                        $lang
                            ->setzeSprache($langCode)
                            ->set((int)$kSektion, $name, $cWert);
                        $modified[] = $name;
                    }
                }
            }

            $cache->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE]);
            $db->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);

            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                count($modified) > 0
                    ? __('successVarChange') . implode(', ', $modified)
                    : __('errorVarChangeNone'),
                'varChangeMessage'
            );

            break;
        case 'clearlog':
            // Liste nicht gefundener Variablen leeren
            $lang
                ->setzeSprache($langCode)
                ->clearLog();
            $cache->flushTags([CACHING_GROUP_LANGUAGE]);
            $db->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successListReset'), 'successListReset');
            break;
        default:
            break;
    }
}

if ($step === 'newvar') {
    $smarty
        ->assign('oSektion_arr', $sections)
        ->assign('oVariable', $variable)
        ->assign('oSprache_arr', $availableLanguages);
} elseif ($step === 'overview') {
    $filter                      = new Filter('langvars');
    $selectField                 = $filter->addSelectfield(__('section'), 'sw.kSprachsektion');
    $selectField->reloadOnChange = true;
    $selectField->addSelectOption('(' . __('all') . ')', '');

    foreach ($sections as $oSektion) {
        $selectField->addSelectOption($oSektion->cName, $oSektion->kSprachsektion, Operation::EQUALS);
    }

    $filter->addTextfield(
        [__('search'), __('searchInContentAndVarName')],
        ['sw.cName', 'sw.cWert'],
        Operation::CONTAINS
    );
    $selectField = $filter->addSelectfield(__('systemOwn'), 'bSystem');
    $selectField->addSelectOption(__('both'), '');
    $selectField->addSelectOption(__('system'), '1', Operation::EQUALS);
    $selectField->addSelectOption(__('own'), '0', Operation::EQUALS);
    $filter->assemble();
    $filterSQL = $filter->getWhereSQL();

    $values = $db->query(
        'SELECT sw.cName, sw.cWert, sw.cStandard, sw.bSystem, ss.kSprachsektion, ss.cName AS cSektionName
            FROM tsprachwerte AS sw
                JOIN tsprachsektion AS ss
                    ON ss.kSprachsektion = sw.kSprachsektion
            WHERE sw.kSprachISO = ' . $langIsoID . '
                ' . ($filterSQL !== '' ? 'AND ' . $filterSQL : ''),
        ReturnType::ARRAY_OF_OBJECTS
    );

    handleCsvExportAction(
        'langvars',
        $langCode . '_' . date('YmdHis') . '.slf',
        $values,
        ['cSektionName', 'cName', 'cWert', 'bSystem'],
        [],
        ';',
        false
    );

    $pagination = (new Pagination('langvars'))
        ->setRange(4)
        ->setItemArray($values)
        ->assemble();

    $notFound = $db->query(
        'SELECT sl.*, ss.kSprachsektion
            FROM tsprachlog AS sl
                LEFT JOIN tsprachsektion AS ss
                    ON ss.cName = sl.cSektion
            WHERE kSprachISO = ' . (int)$lang->currentLanguageID,
        ReturnType::ARRAY_OF_OBJECTS
    );

    $smarty
        ->assign('oFilter', $filter)
        ->assign('pagination', $pagination)
        ->assign('oWert_arr', $pagination->getPageItems())
        ->assign('bSpracheAktiv', $langActive)
        ->assign('oSprache_arr', $availableLanguages)
        ->assign('oNotFound_arr', $notFound);
}

$smarty
    ->assign('tab', $tab)
    ->assign('step', $step)
    ->display('sprache.tpl');
