<?php
/**
 * remove_tadminmenu
 *
 * @author mh
 * @created Thu, 11 Apr 2019 14:39:55 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190411143955
 */
class Migration_20190411143955 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove tadminmenu, tadminmenugruppe';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS `tadminmenu`;');
        $this->execute('DROP TABLE IF EXISTS `tadminmenugruppe`;');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'CREATE TABLE `tadminmenu` (                                                                                                                                                                                                           
                `kAdminmenu` int(10) unsigned NOT NULL AUTO_INCREMENT,                                                                                                                                                                              
                `kAdminmenueGruppe` int(10) unsigned DEFAULT NULL,                                                                                                                                                                                  
                `cModulId` varchar(255) DEFAULT NULL,                                                                                                                                                                                               
                `cLinkname` varchar(255) DEFAULT NULL,                                                                                                                                                                                              
                `cURL` varchar(255) DEFAULT NULL,                                                                                                                                                                                                   
                `cRecht` varchar(255) NOT NULL,                                                                                                                                                                                                     
                `nSort` int(10) unsigned DEFAULT NULL,                                                                                                                                                                                              
                PRIMARY KEY (`kAdminmenu`)                                                                                                                                                                                                          
          ) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
        $this->execute("INSERT INTO `tadminmenu` VALUES
              (2,4,'core_jtl','Eigene Seiten','links.php','CONTENT_PAGE_VIEW',110),
              (3,4,'core_jtl','AGB/WRB','agbwrb.php','ORDER_AGB_WRB_VIEW',120),
              (4,18,'core_jtl','Übersicht','zahlungsarten.php','ORDER_PAYMENT_VIEW',100),
              (5,16,'core_jtl','Versandarten','versandarten.php','ORDER:_SHIPMENT_VIEW',200),
              (6,9,'core_jtl','Vorlagen','emailvorlagen.php','CONTENT_EMAIL_TEMPLATE_VIEW',70),
              (7,16,'core_jtl','Kupons','kupons.php','ORDER_COUPON_VIEW',300),
              (88,4,'core_jtl','OnPage Composer','opc-controlcenter.php','CONTENT_PAGE_VIEW',115),
              (9,12,'core_jtl','Exportformate','exportformate.php','EXPORT_FORMATS_VIEW',20),
              (10,3,'core_jtl','Template','shoptemplate.php','DISPLAY_TEMPLATE_VIEW',180),
              (11,11,'core_jtl','Update','dbupdater.php','SHOP_UPDATE_VIEW',50),
              (13,8,'core_jtl','Kundenimport','kundenimport.php','IMPORT_CUSTOMER_VIEW',20),
              (14,11,'core_jtl','Zurücksetzen','shopzuruecksetzen.php','RESET_SHOP_VIEW',70),
              (15,15,'core_jtl','Kontaktformular','kontaktformular.php','SETTINGS_CONTACTFORM_VIEW',170),
              (16,10,'core_jtl','Umsätze','statistik.php?s=4','STATS_EXCHANGE_VIEW',70),
              (17,10,'core_jtl','Kundenherkunft','statistik.php?s=2','STATS_VISITOR_LOCATION_VIEW',40),
              (18,10,'core_jtl','Besucher','statistik.php?s=1','STATS_VISITOR_VIEW',10),
              (60,10,'core_jtl','Suchmaschinen','statistik.php?s=3','STATS_CRAWLER_VIEW',60),
              (21,8,'core_jtl','Backend-Benutzer','benutzerverwaltung.php','ACCOUNT_VIEW',10),
              (24,12,'core_jtl','Sitemap','sitemapexport.php','EXPORT_SITEMAP_VIEW',50),
              (12,12,'core_jtl','RSS Feed','rss.php','EXPORT_RSSFEED_VIEW',30),
              (26,17,'core_jtl','Suchanfragen','livesuche.php','MODULE_LIVESEARCH_VIEW',140),
              (27,13,'core_jtl','Tags','tagging.php','MODULE_PRODUCTTAGS_VIEW',70),
              (28,13,'core_jtl','Wunschzettel','wunschliste.php','MODULE_WISHLIST_VIEW',110),
              (29,13,'core_jtl','Bewertungen','bewertung.php','MODULE_VOTESYSTEM_VIEW',50),
              (30,13,'core_jtl','Preisverlauf','preisverlauf.php','MODULE_PRICECHART_VIEW',60),
              (31,13,'core_jtl','Vergleichsliste','vergleichsliste.php','MODULE_COMPARELIST_VIEW',80),
              (32,9,'core_jtl','Newsletter','newsletter.php','MODULE_NEWSLETTER_VIEW',50),
              (33,15,'core_jtl','Eigene Formularfelder','kundenfeld.php','ORDER_CUSTOMERFIELDS_VIEW',166),
              (34,17,'core_jtl','Filter','navigationsfilter.php','SETTINGS_NAVIGATION_FILTER_VIEW',167),
              (35,13,'core_jtl','Besondere Produkte','suchspecials.php','SETTINGS_SPECIALPRODUCTS_VIEW',40),
              (36,4,'core_jtl','News','news.php','CONTENT_NEWS_SYSTEM_VIEW',180),
              (37,16,'core_jtl','Gespeicherte Warenk?rbe','warenkorbpers.php','MODULE_SAVED_BASKETS_VIEW',177),
              (38,16,'core_jtl','Zusatzverpackung','zusatzverpackung.php','ORDER_PACKAGE_VIEW',310),
              (39,9,'core_jtl','Blacklist','emailblacklist.php','SETTINGS_EMAIL_BLACKLIST_VIEW',30),
              (40,7,'core_jtl','Globale Meta-Angaben','globalemetaangaben.php','SETTINGS_GLOBAL_META_VIEW',10),
              (41,4,'core_jtl','Sitemapaufbau','shopsitemap.php','SETTINGS_SITEMAP_VIEW',60),
              (42,4,'core_jtl','Umfragen','umfrage.php','EXTENSION_VOTE_VIEW',190),
              (43,8,'core_jtl','Kunden werben Kunden','kundenwerbenkunden.php','MODULE_CAC_VIEW',30),
              (44,14,'core_jtl','Wasserzeichen','branding.php','DISPLAY_BRANDING_VIEW',100),
              (45,7,'core_jtl','Freischaltzentrale','freischalten.php','UNLOCK_CENTRAL_VIEW',125),
              (47,12,'core_jtl','Aufgabenplaner','exportformat_queue.php','EXPORT_SCHEDULE_VIEW',10),
              (48,14,'core_jtl','Artikeloverlays','suchspecialoverlay.php','DISPLAY_ARTICLEOVERLAYS_VIEW',20),
              (49,9,'core_jtl','Berichte','statusemail.php','EMAIL_REPORTS_VIEW',10),
              (50,16,'core_jtl','Trusted Shops','trustedshops.php','ORDER_TRUSTEDSHOPS_VIEW',230),
              (51,5,'core_jtl','Pluginverwaltung','pluginverwaltung.php','PLUGIN_ADMIN_VIEW',110),
              (64,13,'core_jtl','Auswahlassistent','auswahlassistent.php','EXTENSION_SELECTIONMWIZARD_VIEW',30),
              (53,16,'core_jtl','Gratisgeschenk','gratisgeschenk.php','MODULE_GIFT_VIEW',210),
              (54,4,'core_jtl','Boxenverwaltung','boxen.php','BOXES_VIEW',120),
              (55,10,'core_jtl','Kampagnen','kampagne.php','STATS_CAMPAIGN_VIEW',30),
              (56,7,'core_jtl','Sprachverwaltung','sprache.php','LANGUAGE_VIEW',118),
              (57,14,'core_jtl','Shoplogo','shoplogouploader.php','DISPLAY_OWN_LOGO_VIEW',123),
              (58,10,'core_jtl','Bestellhistorie','bestellungen.php','ORDER_VIEW',150),
              (59,9,'core_jtl','Newsletterempfänger-Import','newsletterimport.php','IMPORT_NEWSLETTER_RECEIVER_VIEW',60),
              (61,15,'core_jtl','Checkboxenverwaltung','checkbox.php','CHECKBOXES_VIEW',310),
              (83,18,'core_jtl','Amazon Pay','premiumplugin.php?plugin_id=s360_amazon_lpa_shop4','PLUGIN_ADMIN_VIEW',315),
              (65,14,'core_jtl','Einstellungen','bilder.php','SETTINGS_IMAGES_VIEW',0),
              (66,11,'core_jtl','Log','systemlog.php','SYSTEMLOG_VIEW',30),
              (67,7,'core_jtl','Zahlenformate','trennzeichen.php','SETTINGS_SEPARATOR_VIEW',30),
              (72,7,'core_jtl','Weiterleitungen','redirect.php','REDIRECT_VIEW',322),
              (69,10,'core_jtl','Kupons','kuponstatistik.php','STATS_COUPON_VIEW',50),
              (86,11,'core_jtl','PLZ-Import','plz_ort_import.php','PLZ_ORT_IMPORT_VIEW',60),
              (71,4,'core_jtl','Banner','banner.php','DISPLAY_BANNER_VIEW',120),
              (73,4,'core_jtl','Slider','slider.php','SLIDER_VIEW',190),
              (74,9,'core_jtl','Log','emailhistory.php','EMAILHISTORY_VIEW',40),
              (75,10,'core_jtl','Einstiegsseiten','statistik.php?s=5','STATS_LANDINGPAGES_VIEW',20),
              (76,13,'core_jtl','Warenlager','warenlager.php','WAREHOUSE_VIEW',90),
              (78,11,'core_jtl','Cache','cache.php','OBJECTCACHE_VIEW',20),
              (79,11,'core_jtl','Profiler','profiler.php','PROFILER_VIEW',40),
              (80,7,'core_jtl','WAWI-Synchronisierung','wawisync.php','WAWI_SYNC_VIEW',351),
              (82,14,'core_jtl','?bersicht','bilderverwaltung.php','DISPLAY_IMAGES_VIEW',1),
              (84,16,'core_jtl','TrustedShops Trustbadge Reviews','premiumplugin.php?plugin_id=agws_ts_features','PLUGIN_ADMIN_VIEW',315),
              (85,11,'core_jtl','Status','status.php','FILECHECK_VIEW|DBCHECK_VIEW|PERMISSIONCHECK_VIEW',10)"
        );
        $this->execute(
            "CREATE TABLE `tadminmenugruppe` (                                                                                                                                                                                                     
                 `kAdminmenueGruppe` int(10) unsigned NOT NULL AUTO_INCREMENT,                                                                                                                                                                       
                 `kAdminmenueOberGruppe` int(10) unsigned DEFAULT '0',                                                                                                                                                                               
                 `cModulId` varchar(255) DEFAULT NULL,                                                                                                                                                                                               
                 `cName` varchar(255) DEFAULT NULL,                                                                                                                                                                                                  
                 `nSort` int(10) unsigned NOT NULL,                                                                                                                                                                                                  
                 PRIMARY KEY (`kAdminmenueGruppe`),                                                                                                                                                                                                  
                 KEY `kAdminmenueOberGruppe` (`kAdminmenueOberGruppe`)                                                                                                                                                                               
           ) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"
        );

        $this->execute(
            "INSERT INTO `tadminmenugruppe` VALUES
              (1,0,'core_jtl','System',1),
              (2,0,'core_jtl','Storefront',200),
              (3,0,'core_jtl','Templates',300),
              (4,0,'core_jtl','Inhalte',400),
              (5,0,'core_jtl','Plugins',500),
              (11,1,'core_jtl','Wartung',6),
              (10,1,'core_jtl','Statistiken',5),
              (9,1,'core_jtl','E-Mails',4),
              (7,1,'core_jtl','Globale Einstellungen',2),
              (12,1,'core_jtl','Export',7),
              (8,1,'core_jtl','Benutzer- & Kundenverwaltung',3),
              (13,2,'core_jtl','Artikel',201),
              (14,2,'core_jtl','Bilder',210),
              (15,2,'core_jtl','Formulare',220),
              (16,2,'core_jtl','Kaufabwicklung',230),
              (17,2,'core_jtl','Suche',240),
              (18,2,'core_jtl','Zahlungsarten',250)"
        );
    }
}
