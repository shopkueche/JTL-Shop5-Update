<?php

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

Shop::Container()->getGetText()->loadConfigLocales(true, true);

$oAccount->permission('MODULE_COMPARELIST_VIEW', true, true);
$db          = Shop::Container()->getDB();
$settingIDs  = '(469, 470)';
$alertHelper = Shop::Container()->getAlertService();
if (!isset($_SESSION['Vergleichsliste'])) {
    $_SESSION['Vergleichsliste'] = new stdClass();
}
$_SESSION['Vergleichsliste']->nZeitFilter = 1;
$_SESSION['Vergleichsliste']->nAnzahl     = 10;
if (Request::postInt('zeitfilter') === 1) {
    $_SESSION['Vergleichsliste']->nZeitFilter = Request::postInt('nZeitFilter');
    $_SESSION['Vergleichsliste']->nAnzahl     = Request::postInt('nAnzahl');
}

if (Request::postInt('einstellungen') === 1 && Form::validateToken()) {
    $configData  = $db->query(
        'SELECT *
            FROM teinstellungenconf
            WHERE (
                kEinstellungenConf IN ' . $settingIDs . ' 
                OR kEinstellungenSektion = ' . CONF_VERGLEICHSLISTE . "
                )
                AND cConf = 'Y'
            ORDER BY nSort",
        ReturnType::ARRAY_OF_OBJECTS
    );
    $configCount = count($configData);
    for ($i = 0; $i < $configCount; $i++) {
        $currentValue                        = new stdClass();
        $currentValue->cWert                 = $_POST[$configData[$i]->cWertName];
        $currentValue->cName                 = $configData[$i]->cWertName;
        $currentValue->kEinstellungenSektion = $configData[$i]->kEinstellungenSektion;
        switch ($configData[$i]->cInputTyp) {
            case 'kommazahl':
                $currentValue->cWert = (float)$currentValue->cWert;
                break;
            case 'zahl':
            case 'number':
                $currentValue->cWert = (int)$currentValue->cWert;
                break;
            case 'text':
                $currentValue->cWert = mb_substr($currentValue->cWert, 0, 255);
                break;
        }
        $db->delete(
            'teinstellungen',
            ['kEinstellungenSektion', 'cName'],
            [(int)$configData[$i]->kEinstellungenSektion, $configData[$i]->cWertName]
        );
        $db->insert('teinstellungen', $currentValue);
    }

    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successConfigSave'), 'successConfigSave');
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);
}

$configData  = $db->query(
    'SELECT *
        FROM teinstellungenconf
        WHERE (
                kEinstellungenConf IN ' . $settingIDs . ' 
                OR kEinstellungenSektion = ' . CONF_VERGLEICHSLISTE . '
               )
        ORDER BY nSort',
    ReturnType::ARRAY_OF_OBJECTS
);
$configCount = count($configData);
for ($i = 0; $i < $configCount; $i++) {
    if ($configData[$i]->cInputTyp === 'selectbox') {
        $configData[$i]->ConfWerte = $db->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$configData[$i]->kEinstellungenConf,
            '*',
            'nSort'
        );
        Shop::Container()->getGetText()->localizeConfigValues($configData[$i], $configData[$i]->ConfWerte);
    }
    $setValue                      = $db->select(
        'teinstellungen',
        'kEinstellungenSektion',
        (int)$configData[$i]->kEinstellungenSektion,
        'cName',
        $configData[$i]->cWertName
    );
    $configData[$i]->gesetzterWert = $setValue->cWert ?? null;
    Shop::Container()->getGetText()->localizeConfig($configData[$i]);
}

$listCount  = (int)$db->query(
    'SELECT COUNT(*) AS cnt
        FROM tvergleichsliste',
    ReturnType::SINGLE_OBJECT
)->cnt;
$pagination = (new Pagination())
    ->setItemCount($listCount)
    ->assemble();
$last20     = $db->query(
    "SELECT kVergleichsliste, DATE_FORMAT(dDate, '%d.%m.%Y  %H:%i') AS Datum
        FROM tvergleichsliste
        ORDER BY dDate DESC
        LIMIT " . $pagination->getLimitSQL(),
    ReturnType::ARRAY_OF_OBJECTS
);

if (is_array($last20) && count($last20) > 0) {
    $positions = [];
    foreach ($last20 as $list) {
        $positions                              = $db->selectAll(
            'tvergleichslistepos',
            'kVergleichsliste',
            (int)$list->kVergleichsliste,
            'kArtikel, cArtikelName'
        );
        $list->oLetzten20VergleichslistePos_arr = $positions;
    }
}
$topComparisons = $db->query(
    'SELECT tvergleichsliste.dDate, tvergleichslistepos.kArtikel, 
        tvergleichslistepos.cArtikelName, COUNT(tvergleichslistepos.kArtikel) AS nAnzahl
        FROM tvergleichsliste
        JOIN tvergleichslistepos 
            ON tvergleichsliste.kVergleichsliste = tvergleichslistepos.kVergleichsliste
        WHERE DATE_SUB(NOW(), INTERVAL ' . (int)$_SESSION['Vergleichsliste']->nZeitFilter . ' DAY) 
            < tvergleichsliste.dDate
        GROUP BY tvergleichslistepos.kArtikel
        ORDER BY nAnzahl DESC
        LIMIT ' . (int)$_SESSION['Vergleichsliste']->nAnzahl,
    ReturnType::ARRAY_OF_OBJECTS
);
if (is_array($topComparisons) && count($topComparisons) > 0) {
    erstelleDiagrammTopVergleiche($topComparisons);
}

$smarty->assign('Letzten20Vergleiche', $last20)
       ->assign('TopVergleiche', $topComparisons)
       ->assign('pagination', $pagination)
       ->assign('oConfig_arr', $configData)
       ->display('vergleichsliste.tpl');

/**
 * @param array $topCompareLists
 */
function erstelleDiagrammTopVergleiche($topCompareLists)
{
    unset($_SESSION['oGraphData_arr'], $_SESSION['nYmax'], $_SESSION['nDiagrammTyp']);

    $graphData = [];
    if (is_array($topCompareLists) && count($topCompareLists) > 0) {
        $yMax                     = []; // Y-Achsen Werte um spaeter den Max Wert zu erlangen
        $_SESSION['nDiagrammTyp'] = 4;

        foreach ($topCompareLists as $i => $list) {
            $top               = new stdClass();
            $top->nAnzahl      = $list->nAnzahl;
            $top->cArtikelName = checkName($list->cArtikelName);
            $graphData[]       = $top;
            $yMax[]            = $list->nAnzahl;
            unset($top);

            if ($i >= (int)$_SESSION['Vergleichsliste']->nAnzahl) {
                break;
            }
        }
        // Naechst hoehere Zahl berechnen fuer die Y-Balkenbeschriftung
        if (count($yMax) > 0) {
            $fMax = (float)max($yMax);
            if ($fMax > 10) {
                $temp  = 10 ** floor(log10($fMax));
                $nYmax = ceil($fMax / $temp) * $temp;
            } else {
                $nYmax = 10;
            }

            $_SESSION['nYmax'] = $nYmax;
        }

        $_SESSION['oGraphData_arr'] = $graphData;
    }
}

/**
 * Hilfsfunktion zur Regulierung der X-Achsen Werte
 *
 * @param string $name
 * @return string
 */
function checkName($name)
{
    $name = stripslashes(trim(str_replace([';', '_', '#', '%', '$', ':', '"'], '', $name)));

    if (mb_strlen($name) > 20) {
        // Wenn der String laenger als 20 Zeichen ist
        $name = mb_substr($name, 0, 20) . '...';
    }

    return $name;
}
