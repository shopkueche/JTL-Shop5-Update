<?php
/**
 * add_cwertname_for_all_settings
 *
 * @author mh
 * @created Thu, 09 May 2019 12:12:59 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190509121259
 */
class Migration_20190509121259 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add cWertName for all settings';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "UPDATE `teinstellungenconf` 
                SET cName = REPLACE(REPLACE(cName, '&auml;', 'ä'), '&Auml;', 'Ä') 
                WHERE cName LIKE '%&auml;%'"
        );
        $this->execute(
            "UPDATE `teinstellungenconf` 
                SET cName = REPLACE(REPLACE(cName, '&uuml;', 'ü'), '&Uuml;', 'Ü') 
                WHERE cName LIKE '%&uuml;%'"
        );
        $this->execute(
            "UPDATE `teinstellungenconf` 
                SET cName = REPLACE(REPLACE(cName, '&ouml;', 'ö'), '&Ouml;', 'Ö') 
                WHERE cName LIKE '%&ouml;%'"
        );
        $this->execute(
            "UPDATE `teinstellungenconf`
                SET cName = REPLACE(cName, '&szlig;', 'ß')
                WHERE cName LIKE '%&szlig;%'"
        );

        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_1_vat_label' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 1 AND cName='UST Ausweisung (z.B. wegen Kleinunternehmerregelung)'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_1_maintenance' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 1 AND cName='Wartungsmodus'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_1_general' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 1 AND cName='Allgemein'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_1_categories' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 1 AND cName='Kategorien'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_1_products' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 1 AND cName='Artikel'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_1_wishlist' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 1 AND cName='Wunschzettel'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_1_saved_cart' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 1 AND cName='Gespeicherter Warenkorb'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_1_admin_area' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 1 AND cName='Adminbereich'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_1_shipping' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 1 AND cName='Versand Seite'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_1_cookies' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 1 AND cName='Cookie-Einstellungen (Achtung: nur ändern, wenn Sie genau wissen, was Sie tun!)'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_2_general' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 2 AND cName='Allgemein'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_3_email' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 3 AND cName='Emaileinstellungen'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_4_extended_view' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 4 AND cName='Erweiterte Darstellung'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_4_search' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 4 AND cName='Suche'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_4_livesearch' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 4 AND cName='Livesuche'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_5_product_available' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 5 AND cName='Mailbenachrichtigung, wenn Produkt wieder verfügbar'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_5_product_question' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 5 AND cName='Frage zum Produkt'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_5_general' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 5 AND cName='Allgemeines'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_5_cross_sell_xy' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 5 AND cName='X-Selling (Kunden, die X gekauft haben, haben auch Y gekauft)'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_5_cross_sell' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 5 AND cName='X-Selling (Standard)'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_5_comparelist' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 5 AND cName='Vergleichsliste'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_5_media_module' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 5 AND cName='MedienModul'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_5_product_similar' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 5 AND cName='Ähnliche Artikel'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_5_product_tagging' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 5 AND cName='Produkttagging'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_5_part_list' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 5 AND cName='Stückliste'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_5_tab_description' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 5 AND cName='Beschreibungs-Tab'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_6_vat_id' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 6 AND cName='Umsatzsteuer Identifikationsnummer'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_6_account_register' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 6 AND cName='Kundenaccounterstellung / Unregistriert bestellen'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_6_shipping_address' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 6 AND cName='Lieferadresse'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_7_cart' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 7 AND cName='Warenkorb'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_7_order_progress' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 7 AND cName='Bestellvorgang'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_7_order_final' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 7 AND cName='Bestellabschluss'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_8_box_upcoming_products' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 8 AND cName='Box: Bald erscheinende Produkte'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_8_box_last_viewed' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 8 AND cName='Box: Zuletzt angesehen'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_8_box_top_products' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 8 AND cName='Box: Top Angebot'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_8_box_new_products' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 8 AND cName='Box: Neu im Sortiment'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_8_box_special_offers' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 8 AND cName='Box: Sonderangebote'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_8_box_bestsellers' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 8 AND cName='Box: Bestseller'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_8_box_searchcloud' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 8 AND cName='Suchwolke'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_8_box_wishlist' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 8 AND cName='Wunschzettel'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_8_box_priceradar' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 8 AND cName='Box: Preisradar'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_8_box_comparelist' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 8 AND cName='Vergleichsliste'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_8_box_top_rated' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 8 AND cName='Box: Top Bewertet'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_8_box_tagcloud' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 8 AND cName='Tagwolke'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_9_image_settings' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 9 AND cName='Bildeinstellungen'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_10_livesearch_overview' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 10 AND cName='Livesuche Übersicht'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_10_gifts' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 10 AND cName='Gratisgeschenk'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_10_tagging_overview' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 10 AND cName='Tagging Übersicht'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_100_cash' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 100 AND cName='Barzahlung'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_100_debit' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 100 AND cName='Lastschrift'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_100_invoice' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 100 AND cName='Rechnung'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_100_credit_card' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 100 AND cName='Kreditkarte'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_100_cash_on_delivery' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 100 AND cName='Nachnahme'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_100_payment_advance' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 100 AND cName='Vorkasse Überweisung'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_102_settings' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 102 AND cName='Einstellungen'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_104_rss' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 104 AND cName='RSS-Feed Einstellungen'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_105_price_trend' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 105 AND cName='Preisverlauf'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_106_comparelist' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 106 AND cName='Vergleichsliste'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_107_rating' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 107 AND cName='Bewertung'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_107_rating_reminder' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 107 AND cName='Bewertungserinnerung an Kunden'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_108_newsletter' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 108 AND cName='Newsletter'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_109_customer_field' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 109 AND cName='Kundenfeld'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_110_attribute_filter' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 110 AND cName='Merkmalfilter'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_110_general' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 110 AND cName='Allgemein'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_110_rating_filter' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 110 AND cName='Bewertungsfilter'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_110_price_range_filter' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 110 AND cName='Preisspannenfilter'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_110_tag_filter' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 110 AND cName='Tagfilter'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_110_current_manufacturer' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 110 AND cName='Aktueller Hersteller'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_110_current_attribute' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 110 AND cName='Aktueller Merkmalwert'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_110_current_category' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 110 AND cName='Aktuelle Kategorie'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_110_search_filter' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 110 AND cName='Suchtrefferfilter'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_110_category_filter' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 110 AND cName='Kategoriefilter'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_111_email_blacklist' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 111 AND cName='Email Blacklist'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_112_meta' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 112 AND cName='Meta Angaben'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_113_news' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 113 AND cName='Newssystem'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_114_sitemap_settings' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 114 AND cName='Shop Sitemap Einstellungen'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_115_poll' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 115 AND cName='Umfragesystem Einstellungen'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_116_customer_recruit_customer' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 116 AND cName='Kunden werben Kunden Einstellungen'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_119_searchspecial_standard_search' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 119 AND cName='Suchspecial Standardsortierung'");
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = 'configgroup_122_returns' WHERE COALESCE(cWertName, '') = '' AND kEinstellungenSektion = 122 AND cName='Warenrücksendung'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("UPDATE `teinstellungenconf` SET cWertName = NULL WHERE cWertName LIKE 'configgroup_%'");
    }
}
