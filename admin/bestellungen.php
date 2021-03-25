<?php

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'bestellungen_inc.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
/** @global \JTL\Backend\AdminAccount $oAccount */
$oAccount->permission('ORDER_VIEW', true, true);

$step         = 'bestellungen_uebersicht';
$searchFilter = '';
$alertHelper  = Shop::Container()->getAlertService();
// Bestellung Wawi Abholung zuruecksetzen
if (Request::verifyGPCDataInt('zuruecksetzen') === 1 && Form::validateToken()) {
    if (isset($_POST['kBestellung'])) {
        switch (setzeAbgeholtZurueck($_POST['kBestellung'])) {
            case -1: // Alles O.K.
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successOrderReset'), 'successOrderReset');
                break;
            case 1:  // Array mit Keys nicht vorhanden oder leer
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneOrder'), 'errorAtLeastOneOrder');
                break;
        }
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneOrder'), 'errorAtLeastOneOrder');
    }
} elseif (Request::verifyGPCDataInt('Suche') === 1) { // Bestellnummer gesucht
    $query = Text::filterXSS(Request::verifyGPDataString('cSuche'));
    if (mb_strlen($query) > 0) {
        $searchFilter = $query;
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorMissingOrderNumber'), 'errorMissingOrderNumber');
    }
}

if ($step === 'bestellungen_uebersicht') {
    $pagination = (new Pagination('bestellungen'))
        ->setItemCount(gibAnzahlBestellungen($searchFilter))
        ->assemble();
    $orders     = gibBestellungsUebersicht(' LIMIT ' . $pagination->getLimitSQL(), $searchFilter);
    $smarty->assign('orders', $orders)
           ->assign('pagination', $pagination);
}

$smarty->assign('cSuche', $searchFilter)
       ->assign('step', $step)
       ->display('bestellungen.tpl');
