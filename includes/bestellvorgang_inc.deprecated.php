<?php

use JTL\Alert\Alert;
use JTL\Checkout\Kupon;
use JTL\Checkout\Lieferadresse;
use JTL\Checkout\Zahlungsart;
use JTL\Customer\Customer;
use JTL\Customer\CustomerFields;
use JTL\DB\ReturnType;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Language\LanguageHelper;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;

/**
 * @param array $items
 * @return string
 * @deprecated since 5.0.0
 */
function getArtikelQry(array $items): string
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $ret = '';
    foreach ($items as $item) {
        if (isset($item->Artikel->cArtNr) && mb_strlen($item->Artikel->cArtNr) > 0) {
            $ret .= " OR FIND_IN_SET('" .
                str_replace('%', '\%', Shop::Container()->getDB()->escape($item->Artikel->cArtNr))
                . "', REPLACE(cArtikel, ';', ',')) > 0";
        }
    }

    return $ret;
}

/**
 * @deprecated since 5.0.0
 */
function ladeAjaxEinKlick(): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    global $aFormValues;
    gibKunde();
    gibFormularDaten();
    gibStepLieferadresse();
    gibStepVersand();
    gibStepZahlung();
    gibStepBestaetigung($aFormValues);

    Shop::Smarty()->assign('AGB', Shop::Container()->getLinkService()->getAGBWRB(
        Shop::getLanguageID(),
        Frontend::getCustomerGroup()->getID()
    ))
        ->assign('WarensummeLocalized', Frontend::getCart()->gibGesamtsummeWarenLocalized())
        ->assign('Warensumme', Frontend::getCart()->gibGesamtsummeWaren());
}

/**
 * @param string $user
 * @param string $pass
 * @return int
 * @deprecated since 5.0.0
 */
function plausiAccountwahlLogin($user, $pass): int
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    global $Kunde;
    if (mb_strlen($user) > 0 && mb_strlen($pass) > 0) {
        $Kunde = new Customer();
        $Kunde->holLoginKunde($user, $pass);
        if ($Kunde->kKunde > 0) {
            return 10;
        }

        return 2;
    }

    return 1;
}

/**
 * @param Customer $customer
 * @return bool
 * @deprecated since 5.0.0
 */
function setzeSesssionAccountwahlLogin($customer): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (empty($customer->kKunde)) {
        return false;
    }
    if (isset($_SESSION['oBesucher']->kBesucher) && $_SESSION['oBesucher']->kBesucher > 0) {
        $upd         = new stdClass();
        $upd->kKunde = (int)$customer->kKunde;
        Shop::Container()->getDB()->update('tbesucher', 'kBesucher', (int)$_SESSION['oBesucher']->kBesucher, $upd);
    }
    Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
        ->loescheSpezialPos(C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
        ->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON)
        ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
    unset(
        $_SESSION['Zahlungsart'],
        $_SESSION['Versandart'],
        $_SESSION['Lieferadresse'],
        $_SESSION['ks'],
        $_SESSION['VersandKupon'],
        $_SESSION['oVersandfreiKupon'],
        $_SESSION['NeukundenKupon'],
        $_SESSION['Kupon']
    );
    $customer->angezeigtesLand = LanguageHelper::getCountryCodeByCountryName($customer->cLand);
    $session                   = Frontend::getInstance();
    $session->setCustomer($customer);

    return true;
}

/**
 * @deprecated since 5.0.0
 */
function setzeSmartyAccountwahl()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Shop::Smarty()->assign('untertitel', lang_warenkorb_bestellungEnthaeltXArtikel(Frontend::getCart()));
}

/**
 * @param string $errorMessage
 * @deprecated since 5.0.0
 */
function setzeFehlerSmartyAccountwahl($errorMessage)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_NOTE,
        $errorMessage,
        'smartyAccountwahlError'
    );
}

/**
 * @param array $post
 * @param array $missingData
 * @return bool
 * @deprecated since 5.0.0
 */
function setzeSessionRechnungsadresse(array $post, $missingData)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $customer           = getKundendaten($post, 0);
    $customerAttributes = getKundenattribute($post);
    if (count($missingData) > 0) {
        return false;
    }
    $customer->getCustomerAttributes()->assign($customerAttributes);
    $customer->nRegistriert = 0;
    $_SESSION['Kunde']      = $customer;
    if (isset($_SESSION['Warenkorb']->kWarenkorb)
        && Frontend::getCart()->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0
    ) {
        if ((int)$_SESSION['Bestellung']->kLieferadresse === 0 && $_SESSION['Lieferadresse']) {
            setzeLieferadresseAusRechnungsadresse();
        }
        Tax::setTaxRates();
        Frontend::getCart()->gibGesamtsummeWarenLocalized();
    }

    return true;
}

/**
 * @param int $nUnreg
 * @param int $nCheckout
 * @deprecated since 5.0.0
 */
function setzeSmartyRechnungsadresse($nUnreg, $nCheckout = 0): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    global $step;
    $smarty    = Shop::Smarty();
    $conf      = Shop::getSettings([CONF_KUNDEN]);
    $herkunfte = Shop::Container()->getDB()->query(
        'SELECT *
            FROM tkundenherkunft
            ORDER BY nSort',
        ReturnType::ARRAY_OF_OBJECTS
    );
    if ($nUnreg) {
        $smarty->assign('step', 'formular');
    } else {
        $_POST['editRechnungsadresse'] = 1;
        $smarty->assign('editRechnungsadresse', 1)
            ->assign('step', 'rechnungsdaten');
        $step = 'rechnungsdaten';
    }
    if (count(Frontend::getCustomer()->getCustomerAttributes()) === 0) {
        Frontend::getCustomer()->getCustomerAttributes()->assign(getKundenattribute($_POST));
    }
    $smarty->assign('untertitel', Shop::Lang()->get('fillUnregForm', 'checkout'))
        ->assign('herkunfte', $herkunfte)
        ->assign('Kunde', Frontend::getCustomer())
        ->assign(
            'laender',
            ShippingMethod::getPossibleShippingCountries(
                Frontend::getCustomerGroup()->getID(),
                false,
                true
            )
        )
        ->assign('oKundenfeld_arr', new CustomerFields(Shop::getLanguageID()))
        ->assign('customerAttributes', Frontend::getCustomer()->getCustomerAttributes())
        ->assign(
            'warning_passwortlaenge',
            lang_passwortlaenge($conf['kunden']['kundenregistrierung_passwortlaenge'])
        );
    if ((int)$nCheckout === 1) {
        $smarty->assign('checkout', 1);
    }
}

/**
 * @param array $missingData
 * @param int   $nUnreg
 * @param array $post
 * @deprecated since 5.0.0
 */
function setzeFehlerSmartyRechnungsadresse($missingData, $nUnreg = 0, $post = null): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $conf   = Shop::getSettings([CONF_KUNDEN]);
    $smarty = Shop::Smarty();
    setzeFehlendeAngaben($missingData);
    $origins = Shop::Container()->getDB()->query(
        'SELECT *
            FROM tkundenherkunft
            ORDER BY nSort',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $smarty->assign('untertitel', Shop::Lang()->get('fillUnregForm', 'checkout'))
        ->assign('herkunfte', $origins)
        ->assign('Kunde', getKundendaten($post, 0))
        ->assign(
            'laender',
            ShippingMethod::getPossibleShippingCountries(Frontend::getCustomerGroup()->getID(), false, true)
        )
        ->assign(
            'LieferLaender',
            ShippingMethod::getPossibleShippingCountries(Frontend::getCustomerGroup()->getID())
        )
        ->assign('oKundenfeld_arr', new CustomerFields(Shop::getLanguageID()))
        ->assign(
            'warning_passwortlaenge',
            lang_passwortlaenge($conf['kunden']['kundenregistrierung_passwortlaenge'])
        )
        ->assign('customerAttributes', Frontend::getCustomer()->getCustomerAttributes());
    if ($nUnreg) {
        $smarty->assign('step', 'formular');
    } else {
        $smarty->assign('editRechnungsadresse', 1);
    }
}

/**
 * @param array $post
 * @return array
 * @deprecated since 5.0.0
 */
function plausiLieferadresse(array $post): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $missingData = [];

    $_SESSION['Bestellung']->kLieferadresse = (int)$post['kLieferadresse'];
    //neue lieferadresse
    if ((int)$post['kLieferadresse'] === -1) {
        $missingData = checkLieferFormularArray($post);
        if (angabenKorrekt($missingData)) {
            return $missingData;
        }

        return $missingData;
    }
    if ((int)$post['kLieferadresse'] > 0) {
        // vorhandene lieferadresse
        $shippingAddress = Shop::Container()->getDB()->select(
            'tlieferadresse',
            'kKunde',
            Frontend::getCustomer()->kKunde,
            'kLieferadresse',
            (int)$post['kLieferadresse']
        );
        if (isset($shippingAddress->kLieferadresse) && $shippingAddress->kLieferadresse > 0) {
            $shippingAddress           = new Lieferadresse($shippingAddress->kLieferadresse);
            $_SESSION['Lieferadresse'] = $shippingAddress;
        }
    } elseif ((int)$post['kLieferadresse'] === 0) {
        //lieferadresse gleich rechnungsadresse
        setzeLieferadresseAusRechnungsadresse();
    }
    Tax::setTaxRates();
    //lieferland hat sich geändert und versandart schon gewählt?
    if ($_SESSION['Lieferadresse'] && $_SESSION['Versandart']) {
        $delVersand = (mb_stripos($_SESSION['Versandart']->cLaender, $_SESSION['Lieferadresse']->cLand) === false);
        //ist die plz im zuschlagsbereich?
        $plzData = Shop::Container()->getDB()->executeQueryPrepared(
            'SELECT kVersandzuschlagPlz
                FROM tversandzuschlagplz, tversandzuschlag
                WHERE tversandzuschlag.kVersandart = :id
                    AND tversandzuschlag.kVersandzuschlag = tversandzuschlagplz.kVersandzuschlag
                    AND ((tversandzuschlagplz.cPLZAb <= :plz
                        AND tversandzuschlagplz.cPLZBis >= :plz)
                        OR tversandzuschlagplz.cPLZ = :plz)',
            [
                'id'  => (int)$_SESSION['Versandart']->kVersandart,
                'plz' => $_SESSION['Lieferadresse']->cPLZ
            ],
            ReturnType::SINGLE_OBJECT
        );
        if (isset($plzData->kVersandzuschlagPlz) && $plzData->kVersandzuschlagPlz) {
            $delVersand = true;
        }
        if ($delVersand) {
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR);
            unset($_SESSION['Versandart'], $_SESSION['Zahlungsart']);
        }
        if (!$delVersand) {
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
        }
    }

    return $missingData;
}

/**
 * @param array $post
 * @deprecated since 5.0.0
 */
function setzeSessionLieferadresse(array $post): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $kLieferadresse = isset($post['kLieferadresse']) ? (int)$post['kLieferadresse'] : -1;

    $_SESSION['Bestellung']->kLieferadresse = $kLieferadresse;
    //neue lieferadresse
    if ($kLieferadresse === -1) {
        $_SESSION['Lieferadresse'] = getLieferdaten($post);
    } elseif ($kLieferadresse > 0) {
        // vorhandene lieferadresse
        $address = Shop::Container()->getDB()->query(
            'SELECT kLieferadresse
                FROM tlieferadresse
                WHERE kKunde = ' . Frontend::getCustomer()->getID() . '
                AND kLieferadresse = ' . (int)$post['kLieferadresse'],
            ReturnType::SINGLE_OBJECT
        );
        if ($address->kLieferadresse > 0) {
            $_SESSION['Lieferadresse'] = new Lieferadresse($address->kLieferadresse);
        }
    } elseif ($kLieferadresse === 0) { //lieferadresse gleich rechnungsadresse
        setzeLieferadresseAusRechnungsadresse();
    }
    Tax::setTaxRates();
    if ((int)$post['guthabenVerrechnen'] === 1) {
        $_SESSION['Bestellung']->GuthabenNutzen   = 1;
        $_SESSION['Bestellung']->fGuthabenGenutzt = min(
            Frontend::getCustomer()->fGuthaben,
            Frontend::getCart()->gibGesamtsummeWaren(true, false)
        );
    } else {
        unset($_SESSION['Bestellung']->GuthabenNutzen, $_SESSION['Bestellung']->fGuthabenGenutzt);
    }
}

/**
 * @deprecated since 5.0.0
 */
function setzeSmartyLieferadresse(): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $customerGroupID = Frontend::getCustomerGroup()->getID();
    if (Frontend::getCustomer()->getID() > 0) {
        $shippingAddresses = [];
        $deliveryData      = Shop::Container()->getDB()->selectAll(
            'tlieferadresse',
            'kKunde',
            Frontend::getCustomer()->getID(),
            'kLieferadresse'
        );
        foreach ($deliveryData as $item) {
            if ($item->kLieferadresse > 0) {
                $shippingAddresses[] = new Lieferadresse($item->kLieferadresse);
            }
        }
        $customerGroupID = Frontend::getCustomer()->kKundengruppe;
        Shop::Smarty()->assign('Lieferadressen', $shippingAddresses)
            ->assign('GuthabenLocalized', Frontend::getCustomer()->gibGuthabenLocalized());
    }
    Shop::Smarty()->assign('LieferLaender', ShippingMethod::getPossibleShippingCountries($customerGroupID))
        ->assign('Kunde', Frontend::getCustomer())
        ->assign('KuponMoeglich', Kupon::couponsAvailable())
        ->assign('kLieferadresse', $_SESSION['Bestellung']->kLieferadresse);
    if ($_SESSION['Bestellung']->kLieferadresse == -1) {
        Shop::Smarty()->assign('Lieferadresse', null);
    }
}

/**
 * @param array $missingData
 * @param array $post
 * @deprecated since 5.0.0
 */
function setzeFehlerSmartyLieferadresse($missingData, array $post): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    /** @var array('Kunde' => Kunde) $_SESSION */
    $customerGroupID = Frontend::getCustomerGroup()->getID();
    if (Frontend::getCustomer()->getID() > 0) {
        $shippingAddresses = [];
        $deliveryData      = Shop::Container()->getDB()->selectAll(
            'tlieferadresse',
            'kKunde',
            Frontend::getCustomer()->kKunde,
            'kLieferadresse'
        );
        foreach ($deliveryData as $item) {
            if ($item->kLieferadresse > 0) {
                $shippingAddresses[] = new Lieferadresse($item->kLieferadresse);
            }
        }
        $customerGroupID = Frontend::getCustomer()->kKundengruppe;
        Shop::Smarty()->assign('Lieferadressen', $shippingAddresses)
            ->assign('GuthabenLocalized', Frontend::getCustomer()->gibGuthabenLocalized());
    }
    setzeFehlendeAngaben($missingData, 'shipping_address');
    Shop::Smarty()->assign('laender', ShippingMethod::getPossibleShippingCountries($customerGroupID, false, true))
        ->assign('LieferLaender', ShippingMethod::getPossibleShippingCountries($customerGroupID))
        ->assign('Kunde', Frontend::getCustomer())
        ->assign('KuponMoeglich', Kupon::couponsAvailable())
        ->assign('kLieferadresse', $_SESSION['Bestellung']->kLieferadresse)
        ->assign('kLieferadresse', $post['kLieferadresse']);
    if ($_SESSION['Bestellung']->kLieferadresse == -1) {
        Shop::Smarty()->assign('Lieferadresse', mappeLieferadresseKontaktdaten($post));
    }
}

/**
 * @param array $shippingAddress
 * @return stdClass
 * @deprecated since 5.0.0
 */
function mappeLieferadresseKontaktdaten(array $shippingAddress): stdClass
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $form                = new stdClass();
    $form->cAnrede       = $shippingAddress['anrede'];
    $form->cTitel        = $shippingAddress['titel'];
    $form->cVorname      = $shippingAddress['vorname'];
    $form->cNachname     = $shippingAddress['nachname'];
    $form->cFirma        = $shippingAddress['firma'];
    $form->cZusatz       = $shippingAddress['firmazusatz'];
    $form->cStrasse      = $shippingAddress['strasse'];
    $form->cHausnummer   = $shippingAddress['hausnummer'];
    $form->cAdressZusatz = $shippingAddress['adresszusatz'];
    $form->cPLZ          = $shippingAddress['plz'];
    $form->cOrt          = $shippingAddress['ort'];
    $form->cBundesland   = $shippingAddress['bundesland'];
    $form->cLand         = $shippingAddress['land'];
    $form->cMail         = $shippingAddress['email'];
    $form->cTel          = $shippingAddress['tel'];
    $form->cMobil        = $shippingAddress['mobil'];
    $form->cFax          = $shippingAddress['fax'];

    return $form;
}

/**
 * @deprecated since 5.0.0
 */
function setzeSmartyVersandart(): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    gibStepVersand();
}

/**
 * @deprecated since 5.0.0
 */
function setzeFehlerSmartyVersandart(): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_NOTE,
        Shop::Lang()->get('fillShipping', 'checkout'),
        'fillShipping'
    );
}

/**
 * @param array     $post
 * @param int|array $missingData
 * @deprecated since 5.0.0
 */
function setzeSmartyZahlungsartZusatz($post, $missingData = 0): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $paymentMethod = gibZahlungsart($post['Zahlungsart']);
    // Wenn Zahlungsart = Lastschrift ist => versuche Kundenkontodaten zu holen
    $customerAccountData = gibKundenKontodaten(Frontend::getCustomer()->kKunde);
    if (!empty($customerAccountData->kKunde)) {
        Shop::Smarty()->assign('oKundenKontodaten', $customerAccountData);
    }
    if (empty($post['zahlungsartzusatzschritt'])) {
        Shop::Smarty()->assign('ZahlungsInfo', $_SESSION['Zahlungsart']->ZahlungsInfo);
    } else {
        setzeFehlendeAngaben($missingData);
        Shop::Smarty()->assign('ZahlungsInfo', gibPostZahlungsInfo());
    }
    Shop::Smarty()->assign('Zahlungsart', $paymentMethod)
        ->assign('Kunde', Frontend::getCustomer())
        ->assign('Lieferadresse', $_SESSION['Lieferadresse']);
}

/**
 * @deprecated since 5.0.0
 */
function setzeFehlerSmartyZahlungsart()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    gibStepZahlung();
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_NOTE,
        Shop::Lang()->get('fillPayment', 'checkout'),
        'fillPayment'
    );
}

/**
 * @deprecated since 5.0.0
 */
function setzeSmartyBestaetigung()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Shop::Smarty()->assign('Kunde', Frontend::getCustomer())
        ->assign('Lieferadresse', $_SESSION['Lieferadresse'])
        ->assign('AGB', Shop::Container()->getLinkService()->getAGBWRB(
            Shop::getLanguageID(),
            Frontend::getCustomerGroup()->getID()
        ))
        ->assign('WarensummeLocalized', Frontend::getCart()->gibGesamtsummeWarenLocalized())
        ->assign('Warensumme', Frontend::getCart()->gibGesamtsummeWaren());
}

/**
 * @deprecated since 5.0.0
 */
function globaleAssigns()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    global $step;
    Shop::Smarty()->assign(
        'AGB',
        Shop::Container()->getLinkService()->getAGBWRB(
            Shop::getLanguageID(),
            Frontend::getCustomerGroup()->getID()
        )
    )
        ->assign('Ueberschrift', Shop::Lang()->get('orderStep0Title', 'checkout'))
        ->assign('UeberschriftKlein', Shop::Lang()->get('orderStep0Title2', 'checkout'))
        ->assign('Einstellungen', Shopsetting::getInstance()->getAll())
        ->assign('alertNote', Shop::Container()->getAlertService()->alertTypeExists(Alert::TYPE_NOTE))
        ->assign('step', $step)
        ->assign('WarensummeLocalized', Frontend::getCart()->gibGesamtsummeWarenLocalized())
        ->assign('Warensumme', Frontend::getCart()->gibGesamtsummeWaren())
        ->assign('Steuerpositionen', Frontend::getCart()->gibSteuerpositionen())
        ->assign('bestellschritt', gibBestellschritt($step))
        ->assign('sess', $_SESSION);
}

/**
 * @param int $step
 * @deprecated since 5.0.0
 */
function loescheSession(int $step)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    switch ($step) {
        case 0:
            unset(
                $_SESSION['Kunde'],
                $_SESSION['Lieferadresse'],
                $_SESSION['Versandart'],
                $_SESSION['oVersandfreiKupon'],
                $_SESSION['Zahlungsart']
            );
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
            break;

        case 1:
            unset(
                $_SESSION['Lieferadresse'],
                $_SESSION['Versandart'],
                $_SESSION['oVersandfreiKupon'],
                $_SESSION['Zahlungsart']
            );
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
            break;

        case 2:
            unset($_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['oVersandfreiKupon']);
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
            unset($_SESSION['Zahlungsart']);
            break;

        case 3:
            unset(
                $_SESSION['Versandart'],
                $_SESSION['oVersandfreiKupon'],
                $_SESSION['Zahlungsart']
            );
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
            break;

        case 4:
            unset($_SESSION['Zahlungsart']);
            Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
            break;

        default:
            break;
    }
}

/**
 * @param string $datum
 * @return string
 * @deprecated since 5.0.0
 */
function convertDate2German($datum)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (is_string($datum)) {
        [$tag, $monat, $jahr] = explode('.', $datum);
        if ($tag && $monat && $jahr) {
            return $jahr . '-' . $monat . '-' . $tag;
        }
    }

    return $datum;
}

/**
 * @param string $name
 * @param mixed $obj
 * @deprecated since 4.06
 */
function setzeInSession($name, $obj)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    //an die Session anhängen
    unset($_SESSION[$name]);
    $_SESSION[$name] = $obj;
}

/**
 * @param string $str
 * @return string
 * @deprecated since 5.0.0
 */
function umlauteUmschreibenA2AE(string $str): string
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $src = ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü'];
    $rpl = ['ae', 'oe', 'ue', 'ss', 'Ae', 'Oe', 'Ue'];

    return str_replace($src, $rpl, $str);
}

/**
 * @param string $str
 * @return string
 * @deprecated since 5.0.0
 */
function umlauteUmschreibenAE2A(string $str): string
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $rpl = ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü'];
    $src = ['ae', 'oe', 'ue', 'ss', 'Ae', 'Oe', 'Ue'];

    return str_replace($src, $rpl, $str);
}

/**
 * @param object|Kupon $coupon
 * @return array
 * @deprecated since 5.0.0
 */
function checkeKupon($coupon): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Kupon::checkCoupon($coupon);
}

/**
 * @param Kupon|object $coupon
 * @deprecated since 5.0.0
 */
function kuponAnnehmen($coupon)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Kupon::acceptCoupon($coupon);
}

/**
 * @return array
 * @deprecated since 5.0.0 - use @see CustomerFields::getNonEditableFields instead
 */
function getKundenattributeNichtEditierbar(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return (new CustomerFields())->getNonEditableFields();
}

/**
 * @return array - non editable customer fields
 * @deprecated since 5.0.0 - use @see CustomerFields::getNonEditableFields instead
 */
function getNonEditableCustomerFields(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return (new CustomerFields())->getNonEditableFields();
}

/**
 * @return int
 * @deprecated since 5.0.0
 */
function kuponMoeglich()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Kupon::couponsAvailable();
}

/**
 * @return CustomerFields
 * @deprecated since 5.0.0 - use @see CustomerFields class instead
 */
function gibSelbstdefKundenfelder(): CustomerFields
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return new CustomerFields(Shop::getLanguageID());
}

/**
 * @param Zahlungsart $paymentMethod
 * @param array       $post
 * @return array
 * @deprecated since 5.0.0
 */
function plausiZahlungsartZusatz($paymentMethod, array $post)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return checkAdditionalPayment($paymentMethod);
}

/**
 * @param array|null $post
 * @return array
 * @deprecated since 5.0.0
 */
function checkLieferFormular($post = null): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return checkLieferFormularArray($post ?? $_POST);
}
