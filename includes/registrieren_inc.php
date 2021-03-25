<?php

use JTL\CheckBox;
use JTL\Customer\Customer;
use JTL\Customer\CustomerAttributes;
use JTL\Customer\CustomerFields;
use JTL\Customer\DataHistory;
use JTL\DB\ReturnType;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Campaign;
use JTL\Language\LanguageHelper;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * @param array $post
 * @return array|int
 */
function kundeSpeichern(array $post)
{
    global $Kunde,
           $step,
           $edit,
           $knd;

    unset($_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart']);
    $conf = Shop::getSettings([CONF_GLOBAL]);
    $cart = Frontend::getCart();
    $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART);

    $edit = (int)$post['editRechnungsadresse'];
    $step = 'formular';
    Shop::Smarty()->assign('cPost_arr', Text::filterXSS($post));
    $missingData        = (!$edit)
        ? checkKundenFormular(1)
        : checkKundenFormular(1, 0);
    $knd                = getKundendaten($post, 1, 0);
    $customerAttributes = getKundenattribute($post);
    $customerGroupID    = Frontend::getCustomerGroup()->getID();
    $checkbox           = new CheckBox();
    $missingData        = array_merge(
        $missingData,
        $checkbox->validateCheckBox(CHECKBOX_ORT_REGISTRIERUNG, $customerGroupID, $post, true)
    );

    if (isset($post['shipping_address'])) {
        if ((int)$post['shipping_address'] === 0) {
            $post['kLieferadresse'] = 0;
            $post['lieferdaten']    = 1;
            pruefeLieferdaten($post);
        } elseif (isset($post['kLieferadresse']) && (int)$post['kLieferadresse'] > 0) {
            pruefeLieferdaten($post);
        } elseif (isset($post['register']['shipping_address'])) {
            pruefeLieferdaten($post['register']['shipping_address'], $missingData);
        }
    } elseif (isset($post['lieferdaten']) && (int)$post['lieferdaten'] === 1) {
        // compatibility with older template
        pruefeLieferdaten($post, $missingData);
    }
    $nReturnValue = angabenKorrekt($missingData);

    executeHook(HOOK_REGISTRIEREN_PAGE_REGISTRIEREN_PLAUSI, [
        'nReturnValue'    => &$nReturnValue,
        'fehlendeAngaben' => &$missingData
    ]);

    if ($nReturnValue) {
        // CheckBox Spezialfunktion ausführen
        $checkbox->triggerSpecialFunction(
            CHECKBOX_ORT_REGISTRIERUNG,
            $customerGroupID,
            true,
            $post,
            ['oKunde' => $knd]
        )->checkLogging(CHECKBOX_ORT_REGISTRIERUNG, $customerGroupID, $post, true);

        if ($edit && $_SESSION['Kunde']->kKunde > 0) {
            $knd->cAbgeholt = 'N';
            $knd->updateInDB();
            $knd->cPasswort = null;
            // Kundendatenhistory
            DataHistory::saveHistory($_SESSION['Kunde'], $knd, DataHistory::QUELLE_BESTELLUNG);

            $_SESSION['Kunde'] = $knd;
            // Update Kundenattribute
            $customerAttributes->save();

            $_SESSION['Kunde'] = new Customer($_SESSION['Kunde']->kKunde);
            $_SESSION['Kunde']->getCustomerAttributes()->load($_SESSION['Kunde']->kKunde);
        } else {
            $customerGroupID = Frontend::getCustomerGroup()->getID();

            $knd->kKundengruppe     = $customerGroupID;
            $knd->kSprache          = Shop::getLanguageID();
            $knd->cAbgeholt         = 'N';
            $knd->cSperre           = 'N';
            $knd->cAktiv            = $conf['global']['global_kundenkonto_aktiv'] === 'A'
                ? 'N'
                : 'Y';
            $cPasswortKlartext      = $knd->cPasswort;
            $knd->cPasswort         = Shop::Container()->getPasswordService()->hash($cPasswortKlartext);
            $knd->dErstellt         = 'NOW()';
            $knd->nRegistriert      = 1;
            $knd->angezeigtesLand   = LanguageHelper::getCountryCodeByCountryName($knd->cLand);
            $cLand                  = $knd->cLand;
            $knd->cPasswortKlartext = $cPasswortKlartext;
            $obj                    = new stdClass();
            $obj->tkunde            = $knd;

            $mailer = Shop::Container()->get(Mailer::class);
            $mail   = new Mail();
            $mailer->send($mail->createFromTemplateID(MAILTEMPLATE_NEUKUNDENREGISTRIERUNG, $obj));

            $knd->cLand = $cLand;
            unset($knd->cPasswortKlartext, $knd->Anrede);

            $knd->kKunde = $knd->insertInDB();
            // Kampagne
            if (isset($_SESSION['Kampagnenbesucher'])) {
                Campaign::setCampaignAction(KAMPAGNE_DEF_ANMELDUNG, $knd->kKunde, 1.0); // Anmeldung
            }
            // Insert Kundenattribute
            $customerAttributes->setCustomerID((int)$knd->kKunde);
            $customerAttributes->save();
            if ($conf['global']['global_kundenkonto_aktiv'] !== 'A') {
                $_SESSION['Kunde'] = new Customer($knd->kKunde);
                $_SESSION['Kunde']->getCustomerAttributes()->load($knd->kKunde);
            } else {
                $step = 'formular eingegangen';
            }
        }
        if (isset($cart->kWarenkorb) && $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0) {
            Tax::setTaxRates();
            $cart->gibGesamtsummeWarenLocalized();
        }
        if ((int)$post['checkout'] === 1) {
            //weiterleitung zum chekout
            header('Location: ' . Shop::Container()->getLinkService()
                                      ->getStaticRoute('bestellvorgang.php', true) . '?reg=1', true, 303);
            exit;
        }
        if (isset($post['ajaxcheckout_return']) && (int)$post['ajaxcheckout_return'] === 1) {
            return 1;
        }
        if ($conf['global']['global_kundenkonto_aktiv'] !== 'A') {
            //weiterleitung zu mein Konto
            header('Location: ' . Shop::Container()->getLinkService()
                                      ->getStaticRoute('jtl.php', true) . '?reg=1', true, 303);
            exit;
        }
    } else {
        $knd->getCustomerAttributes()->assign($customerAttributes);
        if ((int)$post['checkout'] === 1) {
            //weiterleitung zum checkout
            $_SESSION['checkout.register']        = 1;
            $_SESSION['checkout.fehlendeAngaben'] = $missingData;
            $_SESSION['checkout.cPost_arr']       = $post;

            //keep shipping address on error
            if (isset($post['register']['shipping_address'])) {
                $_SESSION['Lieferadresse'] = getLieferdaten($post['register']['shipping_address']);
            }

            header('Location: ' . Shop::Container()->getLinkService()
                                      ->getStaticRoute('bestellvorgang.php', true) . '?reg=1', true, 303);
            exit;
        }
        Shop::Smarty()->assign('fehlendeAngaben', $missingData);
        $Kunde = $knd;

        return $missingData;
    }

    return [];
}

/**
 * @param int $nCheckout
 */
function gibFormularDaten(int $nCheckout = 0)
{
    /** @var Customer $Kunde */
    global $Kunde;

    $herkunfte = Shop::Container()->getDB()->query(
        'SELECT * 
            FROM tkundenherkunft 
            ORDER BY nSort',
        ReturnType::ARRAY_OF_OBJECTS
    );

    Shop::Smarty()->assign('herkunfte', $herkunfte)
        ->assign('Kunde', $Kunde)
        ->assign('customerAttributes', is_a($Kunde, Customer::class)
            ? $Kunde->getCustomerAttributes()
            : new CustomerAttributes())
        ->assign(
            'laender',
            ShippingMethod::getPossibleShippingCountries(Frontend::getCustomerGroup()->getID(), false, true)
        )
        ->assign(
            'warning_passwortlaenge',
            lang_passwortlaenge(Shop::getSettingValue(CONF_KUNDEN, 'kundenregistrierung_passwortlaenge'))
        )
        ->assign('oKundenfeld_arr', new CustomerFields(Shop::getLanguageID()));

    if ($nCheckout === 1) {
        Shop::Smarty()->assign('checkout', 1)
            ->assign('bestellschritt', [1 => 1, 2 => 3, 3 => 3, 4 => 3, 5 => 3]); // Rechnungsadresse ändern
    }
}

/**
 *
 */
function gibKunde()
{
    global $Kunde, $titel;

    $Kunde = $_SESSION['Kunde'];
    $titel = Shop::Lang()->get('editData', 'login');
}
