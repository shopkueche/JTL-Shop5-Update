<?php declare(strict_types=1);

namespace JTL\Mail\Hydrator;

use DateTime;
use JTL\Catalog\Product\Preise;
use JTL\CheckBox;
use JTL\Checkout\Kupon;
use JTL\Checkout\Lieferschein;
use JTL\Checkout\Versand;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Helpers\Date;
use JTL\Helpers\ShippingMethod;
use JTL\Language\LanguageHelper;
use JTL\Shop;
use stdClass;

/**
 * Class TestHydrator
 * @package JTL\Mail\Hydrator
 */
class TestHydrator extends DefaultsHydrator
{
    /**
     * @inheritdoc
     */
    public function hydrate(?object $data, object $language): void
    {
        parent::hydrate($data, $language);
        $lang = Shop::Lang();
        $all  = LanguageHelper::getAllLanguages(1);
        $lang->setzeSprache($all[$language->kSprache]->cISO);

        $langID        = (int)$language->kSprache;
        $msg           = $this->getMessage();
        $customerBonus = $this->getBonus();
        $customerGroup = (new CustomerGroup())->loadDefaultGroup();
        $order         = $this->getOrder($langID);
        $customer      = $this->getCustomer($langID, $customerGroup->getID());
        $checkbox      = $this->getCheckbox();
        $oAGBWRB       = $this->db->select(
            'ttext',
            ['kKundengruppe', 'kSprache'],
            [$customer->kKundengruppe, $langID]
        );

        $this->smarty->assign('oKunde', $customer)
            ->assign('oMailObjekt', $this->getStatusMail())
            ->assign('Verfuegbarkeit_arr', ['cArtikelName_arr' => [], 'cHinweis' => ''])
            ->assign('BestandskundenBoni', (object)['fGuthaben' => Preise::getLocalizedPriceString(1.23)])
            ->assign('cAnzeigeOrt', 'Example')
            ->assign('oSprache', $language)
            ->assign('oCheckBox', $checkbox)
            ->assign('Kunde', $customer)
            ->assign('Kundengruppe', $customerGroup)
            ->assign('cAnredeLocalized', Shop::Lang()->get('salutationM'))
            ->assign('Bestellung', $order)
            ->assign('Neues_Passwort', 'geheim007')
            ->assign('passwordResetLink', Shop::getURL() . '/pass.php?fpwh=ca68b243f0c1e7e57162055f248218fd')
            ->assign('Gutschein', $this->getGift())
            ->assign('AGB', $oAGBWRB)
            ->assign('WRB', $oAGBWRB)
            ->assign('DSE', $oAGBWRB)
            ->assign('URL_SHOP', Shop::getURL() . '/')
            ->assign('Kupon', $this->getCoupon())
            ->assign('Optin', $this->getOptin())
            ->assign('couponTypes', Kupon::getCouponTypes())
            ->assign('Nachricht', $msg)
            ->assign('Artikel', $this->getProduct())
            ->assign('Wunschliste', $this->getWishlist())
            ->assign('VonKunde', $customer)
            ->assign('Benachrichtigung', $this->getAvailabilityMessage())
            ->assign('NewsletterEmpfaenger', $this->getNewsletterRecipient($langID))
            ->assign('oBewertungGuthabenBonus', $customerBonus);
    }

    /**
     * @return stdClass
     */
    private function getStatusMail(): stdClass
    {
        $mail                                           = new stdClass();
        $mail->mail                                     = new stdClass();
        $mail->oAnzahlArtikelProKundengruppe            = 1;
        $mail->nAnzahlNeukunden                         = 21;
        $mail->nAnzahlNeukundenGekauft                  = 33;
        $mail->nAnzahlBestellungen                      = 17;
        $mail->nAnzahlBestellungenNeukunden             = 13;
        $mail->nAnzahlBesucher                          = 759;
        $mail->nAnzahlBesucherSuchmaschine              = 165;
        $mail->nAnzahlBewertungen                       = 99;
        $mail->nAnzahlBewertungenNichtFreigeschaltet    = 15;
        $mail->nAnzahlVersendeterBestellungen           = 15;
        $mail->oAnzahlGezahltesGuthaben                 = -1;
        $mail->nAnzahlGeworbenerKunden                  = 11;
        $mail->nAnzahlErfolgreichGeworbenerKunden       = 0;
        $mail->nAnzahlVersendeterWunschlisten           = 0;
        $mail->nAnzahlNewskommentare                    = 21;
        $mail->nAnzahlNewskommentareNichtFreigeschaltet = 11;
        $mail->nAnzahlProduktanfrageArtikel             = 1;
        $mail->nAnzahlProduktanfrageVerfuegbarkeit      = 2;
        $mail->nAnzahlVergleiche                        = 3;
        $mail->nAnzahlGenutzteKupons                    = 4;
        $mail->nAnzahlZahlungseingaengeVonBestellungen  = 5;
        $mail->nAnzahlNewsletterAbmeldungen             = 6;
        $mail->nAnzahlNewsletterAnmeldungen             = 6;
        $mail->dVon                                     = '01.01.2020';
        $mail->dBis                                     = '31.01.2020';
        $mail->oLogEntry_arr                            = [];
        $mail->cIntervall                               = 'Monatliche Status-Email';

        return $mail;
    }

    /**
     * @return CheckBox
     */
    private function getCheckbox(): CheckBox
    {
        $id = $this->db->query('SELECT kCheckbox FROM tcheckbox LIMIT 1', ReturnType::SINGLE_OBJECT);

        return new CheckBox((int)($id->kCheckbox ?? 0));
    }

    /**
     * @return stdClass
     */
    private function getAvailabilityMessage(): stdClass
    {
        $msg            = new stdClass();
        $msg->cVorname  = 'Max';
        $msg->cNachname = 'Musterman';

        return $msg;
    }

    /**
     * @return stdClass
     */
    private function getGift(): stdClass
    {
        $gift                 = new stdClass();
        $gift->cLocalizedWert = '5,00 EUR';
        $gift->cGrund         = 'Geburtstag';

        return $gift;
    }

    /**
     * @return stdClass
     */
    private function getMessage(): stdClass
    {
        $msg                   = new stdClass();
        $msg->cNachricht       = 'Lorem ipsum dolor sit amet.';
        $msg->cAnrede          = 'm';
        $msg->cAnredeLocalized = Shop::Lang()->get('salutationM');
        $msg->cVorname         = 'Max';
        $msg->cNachname        = 'Mustermann';
        $msg->cFirma           = 'Musterfirma';
        $msg->cMail            = 'info@example.com';
        $msg->cFax             = '34782034';
        $msg->cTel             = '34782035';
        $msg->cMobil           = '34782036';
        $msg->cBetreff         = 'Allgemeine Anfrage';

        return $msg;
    }

    /**
     * @return stdClass
     */
    private function getWishlist(): stdClass
    {
        $wishlist                      = new stdClass();
        $wishlist->kWunschlsite        = 5;
        $wishlist->kKunde              = 1480;
        $wishlist->cName               = 'Wunschzettel';
        $wishlist->nStandard           = 1;
        $wishlist->nOeffentlich        = 0;
        $wishlist->cURLID              = '5686f6vv6c86v65nv6m8';
        $wishlist->dErstellt           = '2019-01-01 01:01:01';
        $wishlist->CWunschlistePos_arr = [];

        $item                                 = new stdClass();
        $item->kWunschlistePos                = 3;
        $item->kWunschliste                   = 5;
        $item->kArtikel                       = 261;
        $item->cArtikelName                   = 'Hansu Televsion';
        $item->fAnzahl                        = 2;
        $item->cKommentar                     = 'Television';
        $item->dHinzugefuegt                  = '2019-07-12 13:55:11';
        $item->Artikel                        = new stdClass();
        $item->Artikel->cName                 = 'LAN Festplatte IPDrive';
        $item->Artikel->cEinheit              = 'Stck.';
        $item->Artikel->fPreis                = 368.1069;
        $item->Artikel->fMwSt                 = 19;
        $item->Artikel->nAnzahl               = 1;
        $item->Artikel->cURL                  = 'LAN-Festplatte-IPDrive';
        $item->Artikel->Bilder                = [];
        $item->Artikel->Bilder[0]             = new stdClass();
        $item->Artikel->Bilder[0]->cPfadKlein = \BILD_KEIN_ARTIKELBILD_VORHANDEN;
        $item->CWunschlistePosEigenschaft_arr = [];

        $wishlist->CWunschlistePos_arr[] = $item;

        $item                                 = new stdClass();
        $item->kWunschlistePos                = 4;
        $item->kWunschliste                   = 5;
        $item->kArtikel                       = 262;
        $item->cArtikelName                   = 'Hansu Phone';
        $item->fAnzahl                        = 1;
        $item->cKommentar                     = 'Phone';
        $item->dHinzugefuegt                  = '2019-07-12 13:55:18';
        $item->Artikel                        = new stdClass();
        $item->Artikel->cName                 = 'USB Connector';
        $item->Artikel->cEinheit              = 'Stck.';
        $item->Artikel->fPreis                = 89.90;
        $item->Artikel->fMwSt                 = 19;
        $item->Artikel->nAnzahl               = 1;
        $item->Artikel->cURL                  = 'USB-Connector';
        $item->Artikel->Bilder                = [];
        $item->Artikel->Bilder[0]             = new stdClass();
        $item->Artikel->Bilder[0]->cPfadKlein = \BILD_KEIN_ARTIKELBILD_VORHANDEN;
        $item->CWunschlistePosEigenschaft_arr = [];

        $attr                                   = new stdClass();
        $attr->kWunschlistePosEigenschaft       = 2;
        $attr->kWunschlistePos                  = 4;
        $attr->kEigenschaft                     = 2;
        $attr->kEigenschaftWert                 = 3;
        $attr->cFreifeldWert                    = '';
        $attr->cEigenschaftName                 = 'Farbe';
        $attr->cEigenschaftWertName             = 'rot';
        $item->CWunschlistePosEigenschaft_arr[] = $attr;

        $wishlist->CWunschlistePos_arr[] = $item;

        return $wishlist;
    }

    /**
     * @return stdClass
     */
    private function getCoupon(): stdClass
    {
        $now                           = (new DateTime())->format('Y-m-d H:i:s');
        $until                         = (new DateTime())->modify('+28 days')->format('Y-m-d H:i:s');
        $coupon                        = new stdClass();
        $coupon->cName                 = 'Kuponname';
        $coupon->fWert                 = 5;
        $coupon->cWertTyp              = 'festpreis';
        $coupon->dGueltigAb            = $now;
        $coupon->cGueltigAbLong        = $now;
        $coupon->GueltigAb             = $now;
        $coupon->dGueltigBis           = $until;
        $coupon->cGueltigBisLong       = $until;
        $coupon->GueltigBis            = $until;
        $coupon->cCode                 = 'geheimcode';
        $coupon->nVerwendungen         = 100;
        $coupon->nVerwendungenProKunde = 2;
        $coupon->AngezeigterName       = 'lokalisierter Name des Kupons';
        $coupon->cKuponTyp             = Kupon::TYPE_STANDARD;
        $coupon->cLocalizedWert        = '5 EUR';
        $coupon->cLocalizedMBW         = '100,00 EUR';
        $coupon->fMindestbestellwert   = 100;
        $coupon->Artikel               = [];
        $coupon->Artikel[0]            = new stdClass();
        $coupon->Artikel[1]            = new stdClass();
        $coupon->Artikel[0]->cName     = 'Artikel eins';
        $coupon->Artikel[0]->cURL      = 'http://example.com/artikel1';
        $coupon->Artikel[0]->cURLFull  = 'http://example.com/artikel1';
        $coupon->Artikel[1]->cName     = 'Artikel zwei';
        $coupon->Artikel[1]->cURL      = 'http://example.com/artikel2';
        $coupon->Artikel[1]->cURLFull  = 'http://example.com/artikel2';
        $coupon->Kategorien            = [];
        $coupon->Kategorien[0]         = new stdClass();
        $coupon->Kategorien[1]         = new stdClass();
        $coupon->Kategorien[0]->cName  = 'Kategorie eins';
        $coupon->Kategorien[0]->cURL   = 'http://example.com/kat1';
        $coupon->Kategorien[1]->cName  = 'Kategorie zwei';
        $coupon->Kategorien[1]->cURL   = 'http://example.com/kat2';

        return $coupon;
    }

    /**
     * @param int $langID
     * @param int $customerGroupID
     * @return stdClass
     */
    private function getCustomer(int $langID, int $customerGroupID): stdClass
    {
        $customer                    = new stdClass();
        $customer->fRabatt           = 0.00;
        $customer->fGuthaben         = 0.00;
        $customer->cAnrede           = 'm';
        $customer->Anrede            = 'Herr';
        $customer->cAnredeLocalized  = Shop::Lang()->get('salutationM');
        $customer->cTitel            = 'Dr.';
        $customer->cVorname          = 'Max';
        $customer->cNachname         = 'Mustermann';
        $customer->cFirma            = 'Musterfirma';
        $customer->cZusatz           = 'Musterfirma-Zusatz';
        $customer->cStrasse          = 'Musterstrasse';
        $customer->cHausnummer       = '123';
        $customer->cPLZ              = '12345';
        $customer->cOrt              = 'Musterstadt';
        $customer->cLand             = 'Musterland';
        $customer->cTel              = '12345678';
        $customer->cFax              = '98765432';
        $customer->cMail             = $this->settings['emails']['email_master_absender'];
        $customer->cUSTID            = 'ust234';
        $customer->cBundesland       = 'NRW';
        $customer->cAdressZusatz     = 'Linker Hof';
        $customer->cMobil            = '01772322234';
        $customer->dGeburtstag       = '1981-10-10';
        $customer->cWWW              = 'http://example.com';
        $customer->kKundengruppe     = $customerGroupID;
        $customer->kSprache          = $langID;
        $customer->cPasswortKlartext = 'superGeheim';

        return $customer;
    }

    /**
     * @param int $languageID
     * @return stdClass
     */
    private function getOrder(int $languageID): stdClass
    {
        $order                   = new stdClass();
        $order->kWaehrung        = $languageID;
        $order->kSprache         = 1;
        $order->fGuthaben        = 5;
        $order->fGesamtsumme     = 433;
        $order->cBestellNr       = 'Prefix-3432-Suffix';
        $order->cVersandInfo     = 'Optionale Information zum Versand';
        $order->cTracking        = 'Track232837';
        $order->cKommentar       = 'Kundenkommentar zur Bestellung';
        $order->cVersandartName  = 'DHL bis 10kg';
        $order->cZahlungsartName = 'Nachnahme';
        $order->cStatus          = 1;
        $order->dVersandDatum    = '2020-10-21';
        $order->dErstellt        = '2020-10-12 09:28:38';
        $order->dBezahltDatum    = '2020-10-20';

        $order->cLogistiker            = 'DHL';
        $order->cTrackingURL           = 'http://dhl.de/linkzudhl.php';
        $order->dVersanddatum_de       = '21.10.2020';
        $order->dBezahldatum_de        = '20.10.2020';
        $order->dErstelldatum_de       = '12.10.2020';
        $order->dVersanddatum_en       = '21st October 2020';
        $order->dBezahldatum_en        = '20th October 2020';
        $order->dErstelldatum_en       = '12th October 2020';
        $order->cBestellwertLocalized  = '511,00 EUR';
        $order->GuthabenNutzen         = 1;
        $order->GutscheinLocalized     = '5,00 EUR';
        $order->fWarensumme            = 433.004004;
        $order->fVersand               = 0;
        $order->nZahlungsTyp           = 0;
        $order->WarensummeLocalized[0] = '511,00 EUR';
        $order->WarensummeLocalized[1] = '429,41 EUR';
        $order->oEstimatedDelivery     = (object)[
            'localized'  => '',
            'longestMin' => 3,
            'longestMax' => 6,
        ];
        $order->cEstimatedDelivery     = &$order->oEstimatedDelivery->localized;

        $order->Positionen = [];

        $item                           = new stdClass();
        $item->kArtikel                 = 1;
        $item->cName                    = 'LAN Festplatte IPDrive';
        $item->cArtNr                   = 'AF8374';
        $item->cEinheit                 = 'Stck.';
        $item->cLieferstatus            = '3-4 Tage';
        $item->fPreisEinzelNetto        = 111.2069;
        $item->fPreis                   = 368.1069;
        $item->fMwSt                    = 19;
        $item->nAnzahl                  = 2;
        $item->nPosTyp                  = 1;
        $item->cHinweis                 = 'Hinweistext zum Artikel';
        $item->cGesamtpreisLocalized[0] = '278,00 EUR';
        $item->cGesamtpreisLocalized[1] = '239,66 EUR';
        $item->cEinzelpreisLocalized[0] = '139,00 EUR';
        $item->cEinzelpreisLocalized[1] = '119,83 EUR';

        $item->WarenkorbPosEigenschaftArr                           = [];
        $item->WarenkorbPosEigenschaftArr[0]                        = new stdClass();
        $item->WarenkorbPosEigenschaftArr[0]->cEigenschaftName      = 'Kapazität';
        $item->WarenkorbPosEigenschaftArr[0]->cEigenschaftWertName  = '400GB';
        $item->WarenkorbPosEigenschaftArr[0]->fAufpreis             = 128.45;
        $item->WarenkorbPosEigenschaftArr[0]->cAufpreisLocalized[0] = '149,00 EUR';
        $item->WarenkorbPosEigenschaftArr[0]->cAufpreisLocalized[1] = '128,45 EUR';

        $item->nAusgeliefert       = 1;
        $item->nAusgeliefertGesamt = 1;
        $item->nOffenGesamt        = 1;
        $item->dMHD                = '2025-01-01';
        $item->dMHD_de             = '01.01.2025';
        $item->cChargeNr           = 'A2100698.b12';
        $item->cSeriennummer       = '465798132756';
        $order->Positionen[]       = $item;

        $item                           = new stdClass();
        $item->kArtikel                 = 2;
        $item->cName                    = 'Klappstuhl';
        $item->cArtNr                   = 'KS332';
        $item->cEinheit                 = 'Stck.';
        $item->cLieferstatus            = '1 Woche';
        $item->fPreisEinzelNetto        = 100;
        $item->fPreis                   = 200;
        $item->fMwSt                    = 19;
        $item->nAnzahl                  = 1;
        $item->nPosTyp                  = 2;
        $item->cHinweis                 = 'Hinweistext zum Artikel';
        $item->cGesamtpreisLocalized[0] = '238,00 EUR';
        $item->cGesamtpreisLocalized[1] = '200,00 EUR';
        $item->cEinzelpreisLocalized[0] = '238,00 EUR';
        $item->cEinzelpreisLocalized[1] = '200,00 EUR';

        $item->nAusgeliefert       = 1;
        $item->nAusgeliefertGesamt = 1;
        $item->nOffenGesamt        = 0;
        $order->Positionen[]       = $item;

        $order->Steuerpositionen                     = [];
        $order->Steuerpositionen[0]                  = new stdClass();
        $order->Steuerpositionen[0]->cName           = 'inkl. 19% USt.';
        $order->Steuerpositionen[0]->fUst            = 19;
        $order->Steuerpositionen[0]->fBetrag         = 98.04;
        $order->Steuerpositionen[0]->cPreisLocalized = '98,04 EUR';

        $order->Waehrung                       = new stdClass();
        $order->Waehrung->cISO                 = 'EUR';
        $order->Waehrung->cName                = 'EUR';
        $order->Waehrung->cNameHTML            = '&euro;';
        $order->Waehrung->fFaktor              = 1;
        $order->Waehrung->cStandard            = 'Y';
        $order->Waehrung->cVorBetrag           = 'N';
        $order->Waehrung->cTrennzeichenCent    = ',';
        $order->Waehrung->cTrennzeichenTausend = '.';

        $order->Zahlungsart           = new stdClass();
        $order->Zahlungsart->cName    = 'Rechnung';
        $order->Zahlungsart->cModulId = 'za_rechnung_jtl';

        $order->Zahlungsinfo               = new stdClass();
        $order->Zahlungsinfo->cBankName    = 'Bankname';
        $order->Zahlungsinfo->cBLZ         = '3443234';
        $order->Zahlungsinfo->cKontoNr     = 'Kto12345';
        $order->Zahlungsinfo->cIBAN        = 'IB239293';
        $order->Zahlungsinfo->cBIC         = 'BIC3478';
        $order->Zahlungsinfo->cKartenNr    = 'KNR4834';
        $order->Zahlungsinfo->cGueltigkeit = '20.10.2010';
        $order->Zahlungsinfo->cCVV         = '1234';
        $order->Zahlungsinfo->cKartenTyp   = 'VISA';
        $order->Zahlungsinfo->cInhaber     = 'Max Mustermann';

        $order->Lieferadresse                   = new stdClass();
        $order->Lieferadresse->kLieferadresse   = 1;
        $order->Lieferadresse->cAnrede          = 'm';
        $order->Lieferadresse->cAnredeLocalized = Shop::Lang()->get('salutationM');
        $order->Lieferadresse->cVorname         = 'John';
        $order->Lieferadresse->cNachname        = 'Doe';
        $order->Lieferadresse->cStrasse         = 'Musterlieferstr.';
        $order->Lieferadresse->cHausnummer      = '77';
        $order->Lieferadresse->cAdressZusatz    = '2. Etage';
        $order->Lieferadresse->cPLZ             = '12345';
        $order->Lieferadresse->cOrt             = 'Musterlieferstadt';
        $order->Lieferadresse->cBundesland      = 'Lieferbundesland';
        $order->Lieferadresse->cLand            = 'Lieferland';
        $order->Lieferadresse->cTel             = '112345678';
        $order->Lieferadresse->cMobil           = '123456789';
        $order->Lieferadresse->cFax             = '12345678909';
        $order->Lieferadresse->cMail            = 'john.doe@example.com';

        $order->fWaehrungsFaktor  = 1;
        $order->oLieferschein_arr = [];

        $deliveryNote = new Lieferschein();
        $deliveryNote->setEmailVerschickt(false);
        $deliveryNote->oVersand_arr = [];
        $shipping                   = new Versand();
        $shipping->setLogistikURL('http://nolp.dhl.de/nextt-online-public/' .
            'report_popup.jsp?lang=de&zip=#PLZ#&idc=#IdentCode#');
        $shipping->setIdentCode('123456');
        $deliveryNote->oVersand_arr[]  = $shipping;
        $deliveryNote->oPosition_arr   = [];
        $deliveryNote->oPosition_arr[] = $item;
        $deliveryNote->oPosition_arr[] = $item;

        $order->oLieferschein_arr[] = $deliveryNote;

        $order->oEstimatedDelivery->localized = ShippingMethod::getDeliverytimeEstimationText(
            $order->oEstimatedDelivery->longestMin,
            $order->oEstimatedDelivery->longestMax
        );
        $order->cEstimatedDeliveryEx          = Date::dateAddWeekday(
            $order->dErstellt,
            $order->oEstimatedDelivery->longestMin
        )->format('d.m.Y') . ' - ' .
            Date::dateAddWeekday(
                $order->dErstellt,
                $order->oEstimatedDelivery->longestMax
            )->format('d.m.Y');

        return $order;
    }

    /**
     * @param int $languageID
     * @return stdClass
     */
    private function getNewsletterRecipient(int $languageID): stdClass
    {
        $recipient                     = new stdClass();
        $recipient->kSprache           = $languageID;
        $recipient->kKunde             = null;
        $recipient->nAktiv             = 0;
        $recipient->cAnrede            = 'w';
        $recipient->cVorname           = 'Erika';
        $recipient->cNachname          = 'Mustermann';
        $recipient->cEmail             = 'test@example.com';
        $recipient->cOptCode           = 'acc4cedb690aed6161d6034417925b97f2';
        $recipient->cLoeschCode        = 'dc1338521613c3cfeb1988261029fe3058';
        $recipient->dEingetragen       = 'NOW()';
        $recipient->dLetzterNewsletter = '_DBNULL_';
        $recipient->cLoeschURL         = Shop::getURL() . '/?oc=' . $recipient->cLoeschCode;
        $recipient->cFreischaltURL     = Shop::getURL() . '/?oc=' . $recipient->cOptCode;

        return $recipient;
    }

    /**
     * @return stdClass
     */
    private function getProduct(): stdClass
    {
        $product                    = new stdClass();
        $product->cName             = 'LAN Festplatte IPDrive';
        $product->cArtNr            = 'AF8374';
        $product->cEinheit          = 'Stck.';
        $product->cLieferstatus     = '3-4 Tage';
        $product->fPreisEinzelNetto = 111.2069;
        $product->fPreis            = 368.1069;
        $product->fMwSt             = 19;
        $product->nAnzahl           = 1;
        $product->cURL              = 'LAN-Festplatte-IPDrive';

        return $product;
    }

    /**
     * @return stdClass
     */
    private function getBonus(): stdClass
    {
        $bonus                          = new stdClass();
        $bonus->kKunde                  = 1379;
        $bonus->fGuthaben               = '2,00 &euro';
        $bonus->nBonuspunkte            = 0;
        $bonus->dErhalten               = 'NOW()';
        $bonus->fGuthabenBonusLocalized = Preise::getLocalizedPriceString(2.00);

        return $bonus;
    }

    /**
     * @return stdClass
     */
    private function getOptin(): stdClass
    {
        $optin                  = new stdClass();
        $optin->activationURL   = 'http://example.com/testproduct?oc=ac123456789';
        $optin->deactivationURL = 'http://example.com/testproduct?oc=dc123456789';

        return $optin;
    }
}
