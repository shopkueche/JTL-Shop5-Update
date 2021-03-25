<?php

/**
 * Remove tadminrechtemodul
 *
 * @author mh
 * @created Mon, 02 Dec 2019 11:11:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191202111100
 */
class Migration_20191202111100 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove tadminrechtemodul';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS `tadminrechtemodul`');
        $this->execute('ALTER TABLE `tadminrecht` DROP COLUMN `kAdminrechtemodul`');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('
            CREATE TABLE `tadminrechtemodul` (
              `kAdminrechtemodul` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `cName` varchar(255) NOT NULL,
              `nSort` int(10) unsigned NOT NULL,
              PRIMARY KEY (`kAdminrechtemodul`)
            )
            ENGINE = InnoDB
            DEFAULT CHARSET = utf8
            COLLATE = utf8_unicode_ci
        ');
        $this->execute("INSERT INTO `tadminrechtemodul` VALUES
            (1, 'Admin', 1),
            (2, 'Einstellungen', 2),
            (3, 'Darstellung', 3),
            (4, 'Inhalt', 4),
            (5, 'Kaufabwicklung', 5),
            (6, 'Module', 6),
            (7, 'Import / Export', 7),
            (8, 'Erweiterungen', 8),
            (9, 'Plugins', 9),
            (10, 'Statistik', 10)
        ");

        $this->execute('ALTER TABLE `tadminrecht` ADD COLUMN `kAdminrechtemodul` INT(10) UNSIGNED NOT NULL;');

        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'ORDER_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'BOXES_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'CHECKBOXES_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'EMAIL_REPORTS_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'UNLOCK_CENTRAL_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'RESET_SHOP_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'SHOP_UPDATE_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'LANGUAGE_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'ACCOUNT_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'DASHBOARD_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'FILECHECK_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'SYSTEMLOG_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'DBCHECK_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'REDIRECT_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'EMAILHISTORY_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'PERMISSIONCHECK_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'OBJECTCACHE_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'PROFILER_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'WAWI_SYNC_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 1 WHERE `cRecht` = 'DISPLAY_IMAGES_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_ARTICLEDETAILS_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_ARTICLEOVERVIEW_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_SPECIALPRODUCTS_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_IMAGES_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_EMAIL_BLACKLIST_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_EMAILS_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_GLOBAL_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_GLOBAL_META_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_CONTACTFORM_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_META_KEYWORD_BLACKLIST_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_NAVIGATION_FILTER_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_SITEMAP_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_STARTPAGE_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_BASKET_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'DIAGNOSTIC_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'WAREHOUSE_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'FILESYSTEM_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_SEPARATOR_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_SEARCH_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'CRON_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 2 WHERE `cRecht` = 'SETTINGS_CUSTOMERFORM_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 3 WHERE `cRecht` = 'DISPLAY_ARTICLEOVERLAYS_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 3 WHERE `cRecht` = 'DISPLAY_OWN_LOGO_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 3 WHERE `cRecht` = 'DISPLAY_TEMPLATE_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 3 WHERE `cRecht` = 'DISPLAY_BRANDING_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 3 WHERE `cRecht` = 'OPC_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 3 WHERE `cRecht` = 'SLIDER_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 3 WHERE `cRecht` = 'DISPLAY_BANNER_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 4 WHERE `cRecht` = 'CONTENT_PAGE_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 4 WHERE `cRecht` = 'CONTENT_EMAIL_TEMPLATE_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 4 WHERE `cRecht` = 'CONTENT_NEWS_SYSTEM_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 5 WHERE `cRecht` = 'ORDER_AGB_WRB_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 5 WHERE `cRecht` = 'ORDER_COUPON_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 5 WHERE `cRecht` = 'ORDER_CUSTOMERFIELDS_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 5 WHERE `cRecht` = 'ORDER_SHIPMENT_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 5 WHERE `cRecht` = 'ORDER_PAYMENT_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 5 WHERE `cRecht` = 'ORDER_PACKAGE_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 5 WHERE `cRecht` = 'MODULE_VOTESYSTEM_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 6 WHERE `cRecht` = 'MODULE_VOTESYSTEM_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 6 WHERE `cRecht` = 'MODULE_SAVED_BASKETS_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 6 WHERE `cRecht` = 'MODULE_GIFT_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 6 WHERE `cRecht` = 'MODULE_CAC_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 6 WHERE `cRecht` = 'MODULE_LIVESEARCH_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 6 WHERE `cRecht` = 'MODULE_NEWSLETTER_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 6 WHERE `cRecht` = 'MODULE_PRICECHART_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 6 WHERE `cRecht` = 'MODULE_COMPARELIST_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 6 WHERE `cRecht` = 'MODULE_WISHLIST_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 6 WHERE `cRecht` = 'MODULE_VOTESYSTEM_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 7 WHERE `cRecht` = 'EXPORT_SCHEDULE_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 7 WHERE `cRecht` = 'EXPORT_FORMATS_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 7 WHERE `cRecht` = 'IMPORT_NEWSLETTER_RECEIVER_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 7 WHERE `cRecht` = 'IMPORT_CUSTOMER_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 7 WHERE `cRecht` = 'EXPORT_RSSFEED_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 7 WHERE `cRecht` = 'EXPORT_SHOPINFO_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 7 WHERE `cRecht` = 'EXPORT_SITEMAP_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 7 WHERE `cRecht` = 'PLZ_ORT_IMPORT_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 8 WHERE `cRecht` = 'EXTENSION_VOTE_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 8 WHERE `cRecht` = 'EXTENSION_SELECTIONWIZARD_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 9 WHERE `cRecht` = 'PLUGIN_ADMIN_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 10 WHERE `cRecht` = 'STATS_COUPON_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 10 WHERE `cRecht` = 'STATS_EXCHANGE_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 10 WHERE `cRecht` = 'STATS_CRAWLER_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 10 WHERE `cRecht` = 'STATS_VISITOR_LOCATION_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 10 WHERE `cRecht` = 'STATS_CAMPAIGN_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 10 WHERE `cRecht` = 'STATS_VISITOR_VIEW'");
        $this->execute("UPDATE `tadminrecht` SET `kAdminrechtemodul` = 10 WHERE `cRecht` = 'STATS_LANDINGPAGES_VIEW'");
    }
}
