<?php

use JTL\Catalog\Product\Preisverlauf;
use JTL\Helpers\Request;
use JTL\Session\Frontend;
use JTL\Shop;

require_once __DIR__ . '/globalinclude.php';

[$_GET['kArtikel'], $_GET['kKundengruppe'], $_GET['kSteuerklasse'], $_GET['fMwSt']] = explode(';', $_GET['cOption']);

if (!isset($_GET['kKundengruppe'])) {
    $_GET['kKundengruppe'] = 1;
}
if (!isset($_GET['kSteuerklasse'])) {
    $_GET['kSteuerklasse'] = 1;
}

/**
 * @param array $data
 * @param int   $max
 * @return mixed
 * @deprecated since 5.0.0
 */
function expandPriceArray($data, $max)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    for ($i = 1; $i <= $max; $i++) {
        if ($i > 1 && !isset($data[$i])) {
            $data[$i] = $data[$i - 1];
        }
    }

    return $data;
}

if (isset($_GET['kArtikel'])) {
    $session         = Frontend::getInstance();
    $conf            = Shop::getSettings([CONF_PREISVERLAUF]);
    $productID       = Request::getInt('kArtikel');
    $customerGroupID = Request::getInt('kKundengruppe');
    $taxClassID      = Request::getInt('kSteuerklasse');
    $month           = (int)$conf['preisverlauf']['preisverlauf_anzahl_monate'];
    if (count($conf) > 0) {
        $priceConfig           = new stdClass();
        $priceConfig->Waehrung = Frontend::getCurrency()->getName();
        $priceConfig->Netto    = Frontend::getCustomerGroup()->isMerchant()
            ? 0
            : $_GET['fMwSt'];

        $history = (new Preisverlauf())->gibPreisverlauf($productID, $customerGroupID, $month);
        $history = array_reverse($history);
        $data    = [];
        foreach ($history as $item) {
            $fPreis = round((float)($item->fVKNetto + ($item->fVKNetto * ($priceConfig->Netto / 100.0))), 2);
            $data[] = $fPreis;
        }
        $d = new solid_dot();
        $d->size(3);
        $d->halo_size(1);
        $d->colour('#000');
        $d->tooltip('#val# ' . $priceConfig->Waehrung);

        $bar = new bar();
        $bar->set_values($data);
        $bar->set_colour('#8cb9fd');
        $bar->set_tooltip('#val# ' . $priceConfig->Waehrung);

        // min und max berechnen @todo: $data must contain at least one element
        $fMaxPreis = round((float)max($data), 2);
        $fMinPreis = round((float)min($data), 2);

        // x achse
        $x = new x_axis();
        $x->set_colour('#bfbfbf');
        $x->set_grid_colour('#f0f0f0');
        $x_labels = [];

        foreach ($history as $item) {
            $x_labels[] = date('d.m.', $item->timestamp);
        }
        $x->labels         = new stdClass();
        $x->labels->labels = $x_labels;

        // y achse
        $y = new y_axis();
        $y->set_colour('#bfbfbf');
        $y->set_grid_colour('#f0f0f0');
        $fMinPreis -= 10.00;
        $fMaxPreis += 10.00;
        if ($fMinPreis < 0) {
            $fMinPreis = 0;
        }
        $y->set_range((int)$fMinPreis, (int)$fMaxPreis, 10);

        // chart
        $chart = new open_flash_chart();
        $chart->add_element($bar);
        $chart->set_x_axis($x);
        $chart->set_y_axis($y);
        $chart->set_bg_colour('#ffffff');
        $chart->set_number_format(2, true, true, false);

        echo $chart->toPrettyString();
    }
}
