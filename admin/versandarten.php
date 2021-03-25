<?php

use Illuminate\Support\Collection;
use JTL\Alert\Alert;
use JTL\Checkout\Versandart;
use JTL\Country\Country;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Pagination;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('ORDER_SHIPMENT_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'versandarten_inc.php';
Tax::setTaxRates();
$db              = Shop::Container()->getDB();
$defaultCurrency = $db->select('twaehrung', 'cStandard', 'Y');
$shippingType    = null;
$step            = 'uebersicht';
$shippingMethod  = null;
$taxRateKeys     = array_keys($_SESSION['Steuersatz']);
$alertHelper     = Shop::Container()->getAlertService();
$countryHelper   = Shop::Container()->getCountryService();
$languages       = LanguageHelper::getAllLanguages();
$getText         = Shop::Container()->getGetText();

$missingShippingClassCombis = getMissingShippingClassCombi();
$smarty->assign('missingShippingClassCombis', $missingShippingClassCombis);

if (Form::validateToken()) {
    if (Request::postInt('neu') === 1 && Request::postInt('kVersandberechnung') > 0) {
        $step = 'neue Versandart';
    }
    if (Request::postInt('kVersandberechnung') > 0) {
        $shippingType = getShippingTypes(Request::verifyGPCDataInt('kVersandberechnung'));
    }

    if (Request::postInt('del') > 0 && Versandart::deleteInDB((int)$_POST['del'])) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successShippingMethodDelete'), 'successShippingMethodDelete');
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
    }
    if (Request::postInt('edit') > 0) {
        $step                            = 'neue Versandart';
        $shippingMethod                  = $db->select('tversandart', 'kVersandart', Request::postInt('edit'));
        $VersandartZahlungsarten         = $db->selectAll(
            'tversandartzahlungsart',
            'kVersandart',
            Request::postInt('edit'),
            '*',
            'kZahlungsart'
        );
        $VersandartStaffeln              = $db->selectAll(
            'tversandartstaffel',
            'kVersandart',
            Request::postInt('edit'),
            '*',
            'fBis'
        );
        $shippingType                    = getShippingTypes((int)$shippingMethod->kVersandberechnung);
        $shippingMethod->cVersandklassen = trim($shippingMethod->cVersandklassen);

        $smarty->assign('VersandartZahlungsarten', reorganizeObjectArray($VersandartZahlungsarten, 'kZahlungsart'))
            ->assign('VersandartStaffeln', $VersandartStaffeln)
            ->assign('Versandart', $shippingMethod)
            ->assign('gewaehlteLaender', explode(' ', $shippingMethod->cLaender));
    }

    if (Request::postInt('clone') > 0) {
        $step = 'uebersicht';
        if (Versandart::cloneShipping($_POST['clone'])) {
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                __('successShippingMethodDuplicated'),
                'successShippingMethodDuplicated'
            );
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);
        } else {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                __('errorShippingMethodDuplicated'),
                'errorShippingMethodDuplicated'
            );
        }
    }

    if (isset($_GET['cISO']) && Request::getInt('zuschlag') === 1 && Request::getInt('kVersandart') > 0) {
        $step = 'Zuschlagsliste';

        $pagination = (new Pagination('surchargeList'))
            ->setRange(4)
            ->setItemArray((new Versandart($_GET['kVersandart']))->getShippingSurchargesForCountry($_GET['cISO']))
            ->assemble();

        $smarty->assign('surcharges', $pagination->getPageItems())
            ->assign('pagination', $pagination);
    }

    if (Request::postInt('neueVersandart') > 0) {
        $shippingMethod                           = new stdClass();
        $shippingMethod->cName                    = htmlspecialchars(
            $_POST['cName'],
            ENT_COMPAT | ENT_HTML401,
            JTL_CHARSET
        );
        $shippingMethod->kVersandberechnung       = Request::postInt('kVersandberechnung');
        $shippingMethod->cAnzeigen                = $_POST['cAnzeigen'];
        $shippingMethod->cBild                    = $_POST['cBild'];
        $shippingMethod->nSort                    = Request::postInt('nSort');
        $shippingMethod->nMinLiefertage           = Request::postInt('nMinLiefertage');
        $shippingMethod->nMaxLiefertage           = Request::postInt('nMaxLiefertage');
        $shippingMethod->cNurAbhaengigeVersandart = $_POST['cNurAbhaengigeVersandart'];
        $shippingMethod->cSendConfirmationMail    = $_POST['cSendConfirmationMail'] ?? 'Y';
        $shippingMethod->cIgnoreShippingProposal  = $_POST['cIgnoreShippingProposal'] ?? 'N';
        $shippingMethod->eSteuer                  = $_POST['eSteuer'];
        $shippingMethod->fPreis                   = (float)str_replace(',', '.', $_POST['fPreis'] ?? 0);
        // Versandkostenfrei ab X
        $shippingMethod->fVersandkostenfreiAbX = Request::postInt('versandkostenfreiAktiv') === 1
            ? (float)$_POST['fVersandkostenfreiAbX']
            : 0;
        // Deckelung
        $shippingMethod->fDeckelung = Request::postInt('versanddeckelungAktiv') === 1
            ? (float)$_POST['fDeckelung']
            : 0;

        $shippingMethod->cLaender = '';
        $Laender                  = array_unique($_POST['land']);
        if (is_array($Laender)) {
            foreach ($Laender as $Land) {
                $shippingMethod->cLaender .= $Land . ' ';
            }
        }

        $VersandartZahlungsarten = [];
        foreach (Request::verifyGPDataIntegerArray('kZahlungsart') as $kZahlungsart) {
            $versandartzahlungsart               = new stdClass();
            $versandartzahlungsart->kZahlungsart = $kZahlungsart;
            if ($_POST['fAufpreis_' . $kZahlungsart] != 0) {
                $versandartzahlungsart->fAufpreis    = (float)str_replace(
                    ',',
                    '.',
                    $_POST['fAufpreis_' . $kZahlungsart]
                );
                $versandartzahlungsart->cAufpreisTyp = $_POST['cAufpreisTyp_' . $kZahlungsart];
            }
            $VersandartZahlungsarten[] = $versandartzahlungsart;
        }

        $VersandartStaffeln       = [];
        $upperLimits              = []; // Haelt alle fBis der Staffel
        $staffelDa                = true;
        $shippingFreeValid        = true;
        $fMaxVersandartStaffelBis = 0;
        if ($shippingType->cModulId === 'vm_versandberechnung_gewicht_jtl'
            || $shippingType->cModulId === 'vm_versandberechnung_warenwert_jtl'
            || $shippingType->cModulId === 'vm_versandberechnung_artikelanzahl_jtl'
        ) {
            $staffelDa = false;
            if (count($_POST['bis']) > 0 && count($_POST['preis']) > 0) {
                $staffelDa = true;
            }
            //preisstaffel beachten
            if (!isset($_POST['bis'][0], $_POST['preis'][0])
                || mb_strlen($_POST['bis'][0]) === 0
                || mb_strlen($_POST['preis'][0]) === 0
            ) {
                $staffelDa = false;
            }
            if (is_array($_POST['bis']) && is_array($_POST['preis'])) {
                foreach ($_POST['bis'] as $i => $fBis) {
                    if (isset($_POST['preis'][$i]) && mb_strlen($fBis) > 0) {
                        unset($oVersandstaffel);
                        $oVersandstaffel         = new stdClass();
                        $oVersandstaffel->fBis   = (float)str_replace(',', '.', $fBis);
                        $oVersandstaffel->fPreis = (float)str_replace(',', '.', $_POST['preis'][$i]);

                        $VersandartStaffeln[] = $oVersandstaffel;
                        $upperLimits[]        = $oVersandstaffel->fBis;
                    }
                }
            }
            // Dummy Versandstaffel hinzufuegen, falls Versandart nach Warenwert und Versandkostenfrei ausgewaehlt wurde
            if ($shippingType->cModulId === 'vm_versandberechnung_warenwert_jtl'
                && $shippingMethod->fVersandkostenfreiAbX > 0
            ) {
                $oVersandstaffel         = new stdClass();
                $oVersandstaffel->fBis   = 999999999;
                $oVersandstaffel->fPreis = 0.0;
                $VersandartStaffeln[]    = $oVersandstaffel;
            }
        }
        // Kundengruppe
        $shippingMethod->cKundengruppen = '';
        if (!$_POST['kKundengruppe']) {
            $_POST['kKundengruppe'] = [-1];
        }
        if (is_array($_POST['kKundengruppe'])) {
            if (in_array(-1, $_POST['kKundengruppe'])) {
                $shippingMethod->cKundengruppen = '-1';
            } else {
                $shippingMethod->cKundengruppen = ';' . implode(';', $_POST['kKundengruppe']) . ';';
            }
        }
        //Versandklassen
        $shippingMethod->cVersandklassen = ((!empty($_POST['kVersandklasse']) && $_POST['kVersandklasse'] !== '-1')
            ? ' ' . $_POST['kVersandklasse'] . ' '
            : '-1');

        if (count($_POST['land']) >= 1
            && count($_POST['kZahlungsart']) >= 1
            && $shippingMethod->cName
            && $staffelDa
            && $shippingFreeValid
        ) {
            $kVersandart = 0;
            if (Request::postInt('kVersandart') === 0) {
                $kVersandart = $db->insert('tversandart', $shippingMethod);
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    sprintf(__('successShippingMethodCreate'), $shippingMethod->cName),
                    'successShippingMethodCreate'
                );
            } else {
                //updaten
                $kVersandart = Request::postInt('kVersandart');
                $db->update('tversandart', 'kVersandart', $kVersandart, $shippingMethod);
                $db->delete('tversandartzahlungsart', 'kVersandart', $kVersandart);
                $db->delete('tversandartstaffel', 'kVersandart', $kVersandart);
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    sprintf(__('successShippingMethodChange'), $shippingMethod->cName),
                    'successShippingMethodChange'
                );
            }
            if ($kVersandart > 0) {
                foreach ($VersandartZahlungsarten as $versandartzahlungsart) {
                    $versandartzahlungsart->kVersandart = $kVersandart;
                    $db->insert('tversandartzahlungsart', $versandartzahlungsart);
                }

                foreach ($VersandartStaffeln as $versandartstaffel) {
                    $versandartstaffel->kVersandart = $kVersandart;
                    $db->insert('tversandartstaffel', $versandartstaffel);
                }
                $versandSprache = new stdClass();

                $versandSprache->kVersandart = $kVersandart;
                foreach ($languages as $language) {
                    $code = $language->getCode();

                    $versandSprache->cISOSprache = $code;
                    $versandSprache->cName       = $shippingMethod->cName;
                    if ($_POST['cName_' . $code]) {
                        $versandSprache->cName = htmlspecialchars(
                            $_POST['cName_' . $code],
                            ENT_COMPAT | ENT_HTML401,
                            JTL_CHARSET
                        );
                    }
                    $versandSprache->cLieferdauer = '';
                    if ($_POST['cLieferdauer_' . $code]) {
                        $versandSprache->cLieferdauer = htmlspecialchars(
                            $_POST['cLieferdauer_' . $code],
                            ENT_COMPAT | ENT_HTML401,
                            JTL_CHARSET
                        );
                    }
                    $versandSprache->cHinweistext = '';
                    if ($_POST['cHinweistext_' . $code]) {
                        $versandSprache->cHinweistext = $_POST['cHinweistext_' . $code];
                    }
                    $versandSprache->cHinweistextShop = '';
                    if ($_POST['cHinweistextShop_' . $code]) {
                        $versandSprache->cHinweistextShop = $_POST['cHinweistextShop_' . $code];
                    }
                    $db->delete('tversandartsprache', ['kVersandart', 'cISOSprache'], [$kVersandart, $code]);
                    $db->insert('tversandartsprache', $versandSprache);
                }
                $step = 'uebersicht';
            }
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
        } else {
            $step = 'neue Versandart';
            if (!$shippingMethod->cName) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorShippingMethodNameMissing'),
                    'errorShippingMethodNameMissing'
                );
            }
            if (count($_POST['land']) < 1) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorShippingMethodCountryMissing'),
                    'errorShippingMethodCountryMissing'
                );
            }
            if (count($_POST['kZahlungsart']) < 1) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorShippingMethodPaymentMissing'),
                    'errorShippingMethodPaymentMissing'
                );
            }
            if (!$staffelDa) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorShippingMethodPriceMissing'),
                    'errorShippingMethodPriceMissing'
                );
            }
            if (!$shippingFreeValid) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorShippingFreeMax'), 'errorShippingFreeMax');
            }
            if (Request::postInt('kVersandart') > 0) {
                $shippingMethod = $db->select('tversandart', 'kVersandart', Request::postInt('kVersandart'));
            }
            $smarty->assign('VersandartZahlungsarten', reorganizeObjectArray($VersandartZahlungsarten, 'kZahlungsart'))
                ->assign('VersandartStaffeln', $VersandartStaffeln)
                ->assign('Versandart', $shippingMethod)
                ->assign('gewaehlteLaender', explode(' ', $shippingMethod->cLaender));
        }
    }
}
if ($step === 'neue Versandart') {
    $versandlaender = $countryHelper->getCountrylist();
    if ($shippingType->cModulId === 'vm_versandberechnung_gewicht_jtl') {
        $smarty->assign('einheit', 'kg');
    }
    if ($shippingType->cModulId === 'vm_versandberechnung_warenwert_jtl') {
        $smarty->assign('einheit', $defaultCurrency->cName);
    }
    if ($shippingType->cModulId === 'vm_versandberechnung_artikelanzahl_jtl') {
        $smarty->assign('einheit', 'Stück');
    }
    // prevent "unusable" payment methods from displaying them in the config section (mainly the null-payment)
    $zahlungsarten = $db->selectAll(
        'tzahlungsart',
        ['nActive', 'nNutzbar'],
        [1, 1],
        '*',
        'cAnbieter, nSort, cName, cModulId'
    );
    foreach ($zahlungsarten as $zahlungsart) {
        $pluginID = PluginHelper::getIDByModuleID($zahlungsart->cModulId);
        if ($pluginID > 0) {
            try {
                Shop::Container()->getGetText()->loadPluginLocale(
                    'base',
                    PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID)
                );
            } catch (InvalidArgumentException $e) {
                $getText->loadAdminLocale('pages/zahlungsarten');
                $alertHelper->addAlert(
                    Alert::TYPE_WARNING,
                    sprintf(
                        __('Plugin for payment method not found'),
                        $zahlungsart->cName,
                        $zahlungsart->cAnbieter
                    ),
                    'notfound_' . $pluginID,
                    [
                        'linkHref' => Shop::getAdminURL(true) . '/zahlungsarten.php',
                        'linkText' => __('paymentTypesOverview')
                    ]
                );
                continue;
            }
        }
        $zahlungsart->cName     = __($zahlungsart->cName);
        $zahlungsart->cAnbieter = __($zahlungsart->cAnbieter);
    }
    $tmpID = (int)($shippingMethod->kVersandart ?? 0);
    $smarty->assign('versandKlassen', $db->selectAll('tversandklasse', [], [], '*', 'kVersandklasse'))
        ->assign('zahlungsarten', $zahlungsarten)
        ->assign('versandlaender', $versandlaender)
        ->assign('continents', $countryHelper->getCountriesGroupedByContinent(
            true,
            explode(' ', $shippingMethod->cLaender ?? '')
        ))
        ->assign('versandberechnung', $shippingType)
        ->assign('waehrung', $defaultCurrency->cName)
        ->assign('customerGroups', CustomerGroup::getGroups())
        ->assign('oVersandartSpracheAssoc_arr', getShippingLanguage($tmpID, $languages))
        ->assign('gesetzteVersandklassen', isset($shippingMethod->cVersandklassen)
            ? gibGesetzteVersandklassen($shippingMethod->cVersandklassen)
            : null)
        ->assign('gesetzteKundengruppen', isset($shippingMethod->cKundengruppen)
            ? gibGesetzteKundengruppen($shippingMethod->cKundengruppen)
            : null);
}
if ($step === 'uebersicht') {
    $customerGroups  = $db->query(
        'SELECT kKundengruppe, cName FROM tkundengruppe ORDER BY kKundengruppe',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $shippingMethods = $db->query(
        'SELECT * FROM tversandart ORDER BY nSort, cName',
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($shippingMethods as $method) {
        $method->versandartzahlungsarten = $db->query(
            'SELECT tversandartzahlungsart.*
                FROM tversandartzahlungsart
                JOIN tzahlungsart
                    ON tzahlungsart.kZahlungsart = tversandartzahlungsart.kZahlungsart
                WHERE tversandartzahlungsart.kVersandart = ' . (int)$method->kVersandart . '
                ORDER BY tzahlungsart.cAnbieter, tzahlungsart.nSort, tzahlungsart.cName',
            ReturnType::ARRAY_OF_OBJECTS
        );

        foreach ($method->versandartzahlungsarten as $smp) {
            $smp->zahlungsart  = $db->select(
                'tzahlungsart',
                'kZahlungsart',
                (int)$smp->kZahlungsart,
                'nActive',
                1
            );
            $smp->cAufpreisTyp = $smp->cAufpreisTyp === 'prozent' ? '%' : '';
            $pluginID          = PluginHelper::getIDByModuleID($smp->zahlungsart->cModulId);
            if ($pluginID > 0) {
                try {
                    $getText->loadPluginLocale(
                        'base',
                        PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID)
                    );
                } catch (InvalidArgumentException $e) {
                    $getText->loadAdminLocale('pages/zahlungsarten');
                    $alertHelper->addAlert(
                        Alert::TYPE_WARNING,
                        sprintf(
                            __('Plugin for payment method not found'),
                            $smp->zahlungsart->cName,
                            $smp->zahlungsart->cAnbieter
                        ),
                        'notfound_' . $pluginID,
                        [
                            'linkHref' => Shop::getAdminURL(true) . '/zahlungsarten.php',
                            'linkText' => __('paymentTypesOverview')
                        ]
                    );
                    continue;
                }
            }
            $smp->zahlungsart->cName     = __($smp->zahlungsart->cName);
            $smp->zahlungsart->cAnbieter = __($smp->zahlungsart->cAnbieter);
        }
        $method->versandartstaffeln         = $db->selectAll(
            'tversandartstaffel',
            'kVersandart',
            (int)$method->kVersandart,
            '*',
            'fBis'
        );
        $method->fPreisBrutto               = berechneVersandpreisBrutto(
            $method->fPreis,
            $_SESSION['Steuersatz'][$taxRateKeys[0]]
        );
        $method->fVersandkostenfreiAbXNetto = berechneVersandpreisNetto(
            $method->fVersandkostenfreiAbX,
            $_SESSION['Steuersatz'][$taxRateKeys[0]]
        );
        $method->fDeckelungBrutto           = berechneVersandpreisBrutto(
            $method->fDeckelung,
            $_SESSION['Steuersatz'][$taxRateKeys[0]]
        );
        foreach ($method->versandartstaffeln as $j => $oVersandartstaffeln) {
            $method->versandartstaffeln[$j]->fPreisBrutto = berechneVersandpreisBrutto(
                $oVersandartstaffeln->fPreis,
                $_SESSION['Steuersatz'][$taxRateKeys[0]]
            );
        }

        $method->versandberechnung = getShippingTypes((int)$method->kVersandberechnung);
        $method->versandklassen    = gibGesetzteVersandklassenUebersicht($method->cVersandklassen);
        if ($method->versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl') {
            $method->einheit = 'kg';
        }
        if ($method->versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl') {
            $method->einheit = $defaultCurrency->cName;
        }
        if ($method->versandberechnung->cModulId === 'vm_versandberechnung_artikelanzahl_jtl') {
            $method->einheit = 'Stück';
        }
        $method->countries                  = new Collection();
        $method->shippingSurchargeCountries = array_column($db->queryPrepared(
            'SELECT DISTINCT cISO FROM tversandzuschlag WHERE kVersandart = :shippingMethodID',
            ['shippingMethodID' => (int)$method->kVersandart],
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        ), 'cISO');
        foreach (explode(' ', trim($method->cLaender)) as $item) {
            if (($country = $countryHelper->getCountry($item)) !== null) {
                $method->countries->push($country);
            }
        }
        $method->countries               = $method->countries->sortBy(static function (Country $country) {
            return $country->getName();
        });
        $method->cKundengruppenName_arr  = [];
        $method->oVersandartSprachen_arr = $db->selectAll(
            'tversandartsprache',
            'kVersandart',
            (int)$method->kVersandart,
            'cName',
            'cISOSprache'
        );
        foreach (Text::parseSSKint($method->cKundengruppen) as $customerGroupID) {
            if ($customerGroupID === -1) {
                $method->cKundengruppenName_arr[] = __('allCustomerGroups');
            } else {
                foreach ($customerGroups as $customerGroup) {
                    if ((int)$customerGroup->kKundengruppe === $customerGroupID) {
                        $method->cKundengruppenName_arr[] = $customerGroup->cName;
                    }
                }
            }
        }
    }

    $missingShippingClassCombis = getMissingShippingClassCombi();
    if (!empty($missingShippingClassCombis)) {
        $errorMissingShippingClassCombis = $smarty->assign('missingShippingClassCombis', $missingShippingClassCombis)
            ->fetch('tpl_inc/versandarten_fehlende_kombis.tpl');
        $alertHelper->addAlert(Alert::TYPE_ERROR, $errorMissingShippingClassCombis, 'errorMissingShippingClassCombis');
    }

    $smarty->assign('versandberechnungen', getShippingTypes())
        ->assign('versandarten', $shippingMethods)
        ->assign('waehrung', $defaultCurrency->cName);
}
if ($step === 'Zuschlagsliste') {
    $cISO        = $_GET['cISO'] ?? $_POST['cISO'] ?? null;
    $kVersandart = Request::getInt('kVersandart');
    if (isset($_POST['kVersandart'])) {
        $kVersandart = Request::postInt('kVersandart');
    }
    $shippingMethod = $db->select('tversandart', 'kVersandart', $kVersandart);
    $fees           = $db->selectAll(
        'tversandzuschlag',
        ['kVersandart', 'cISO'],
        [(int)$shippingMethod->kVersandart, $cISO],
        '*',
        'fZuschlag'
    );
    foreach ($fees as $item) {
        $item->zuschlagplz     = $db->selectAll(
            'tversandzuschlagplz',
            'kVersandzuschlag',
            $item->kVersandzuschlag
        );
        $item->angezeigterName = getZuschlagNames($item->kVersandzuschlag);
    }
    $smarty->assign('Versandart', $shippingMethod)
        ->assign('Zuschlaege', $fees)
        ->assign('waehrung', $defaultCurrency->cName)
        ->assign('Land', $countryHelper->getCountry($cISO));
}

$smarty->assign('fSteuersatz', $_SESSION['Steuersatz'][$taxRateKeys[0]])
    ->assign('oWaehrung', $db->select('twaehrung', 'cStandard', 'Y'))
    ->assign('step', $step)
    ->display('versandarten.tpl');
