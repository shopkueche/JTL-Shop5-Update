<?php

\JTL\Shop::Container()->getGetText()->loadAdminLocale('menu');

/** @var array $adminMenu */
$adminMenu = [
    __('Marketing')      => (object)[
        'icon'  => 'marketing',
        'items' => [
            __('Orders')     => (object)[
                'link'        => 'bestellungen.php',
                'permissions' => 'ORDER_VIEW',
            ],
            __('Promotions') => [
                __('Newsletter') => (object)[
                    'link'        => 'newsletter.php',
                    'permissions' => 'MODULE_NEWSLETTER_VIEW',
                    'section'     => CONF_NEWSLETTER,
                ],
                __('Blog posts') => (object)[
                    'link'        => 'news.php',
                    'permissions' => 'CONTENT_NEWS_SYSTEM_VIEW',
                    'section'     => CONF_NEWS,
                ],
                __('Coupons')    => (object)[
                    'link'        => 'kupons.php',
                    'permissions' => 'ORDER_COUPON_VIEW',
                ],
                __('Free gifts') => (object)[
                    'link'        => 'gratisgeschenk.php',
                    'permissions' => 'MODULE_GIFT_VIEW',
                    'section'     => CONF_SONSTIGES,
                ],
            ],
            __('Statistics') => [
                __('Sales')             => (object)[
                    'link'        => 'statistik.php?s=4',
                    'permissions' => 'STATS_EXCHANGE_VIEW',
                ],
                __('Campaigns')         => (object)[
                    'link'        => 'kampagne.php#globalestats',
                    'permissions' => 'STATS_CAMPAIGN_VIEW',
                ],
                __('Baskets')           => (object)[
                    'link'        => 'warenkorbpers.php',
                    'permissions' => 'MODULE_SAVED_BASKETS_VIEW',
                ],
                __('Coupon statistics') => (object)[
                    'link'        => 'kuponstatistik.php',
                    'permissions' => 'STATS_COUPON_VIEW',
                ],
                __('Visitors')          => (object)[
                    'link'        => 'statistik.php?s=1',
                    'permissions' => 'STATS_VISITOR_VIEW',
                ],
                __('Referrer pages')    => (object)[
                    'link'        => 'statistik.php?s=2',
                    'permissions' => 'STATS_VISITOR_LOCATION_VIEW',
                ],
                __('Entry pages')       => (object)[
                    'link'        => 'statistik.php?s=5',
                    'permissions' => 'STATS_LANDINGPAGES_VIEW',
                ],
                __('Search engines')    => (object)[
                    'link'        => 'statistik.php?s=3',
                    'permissions' => 'STATS_CRAWLER_VIEW',
                ],
                __('Search queries')    => (object)[
                    'link'        => 'livesuche.php',
                    'permissions' => 'MODULE_LIVESEARCH_VIEW',
                ],
            ],
            __('Reports')    => (object)[
                'link'        => 'statusemail.php',
                'permissions' => 'EMAIL_REPORTS_VIEW',
            ],
        ]
    ],
    __('Appearance')     => (object)[
        'icon'  => 'styling',
        'items' => [
            __('OnPage Composer')  => (object)[
                'link'        => 'opc-controlcenter.php',
                'permissions' => 'OPC_VIEW',
            ],
            __('Default views')    => [
                __('Home page')        => (object)[
                    'link'        => 'einstellungen.php?kSektion=' . CONF_STARTSEITE,
                    'permissions' => 'SETTINGS_STARTPAGE_VIEW',
                    'section'     => CONF_STARTSEITE,
                ],
                __('Item overview')    => (object)[
                    'link'        => 'navigationsfilter.php',
                    'permissions' => 'SETTINGS_NAVIGATION_FILTER_VIEW',
                    'section'     => CONF_NAVIGATIONSFILTER,
                ],
                __('Item detail page') => (object)[
                    'link'        => 'einstellungen.php?kSektion=' . CONF_ARTIKELDETAILS,
                    'permissions' => 'SETTINGS_ARTICLEDETAILS_VIEW',
                    'section'     => CONF_ARTIKELDETAILS,
                ],
                __('Checkout')         => (object)[
                    'link'        => 'einstellungen.php?kSektion=' . CONF_KAUFABWICKLUNG,
                    'permissions' => 'SETTINGS_BASKET_VIEW',
                    'section'     => CONF_KAUFABWICKLUNG,
                ],
                __('Comparison list')  => (object)[
                    'link'        => 'vergleichsliste.php',
                    'permissions' => 'MODULE_COMPARELIST_VIEW',
                    'section'     => CONF_VERGLEICHSLISTE,
                ],
                __('Wish list')        => (object)[
                    'link'        => 'wunschliste.php',
                    'permissions' => 'MODULE_WISHLIST_VIEW',
                ],
                __('Contact form')     => (object)[
                    'link'        => 'kontaktformular.php',
                    'permissions' => 'SETTINGS_CONTACTFORM_VIEW',
                    'section'     => CONF_KONTAKTFORMULAR,
                ],
                __('Registration')     => (object)[
                    'link'        => 'einstellungen.php?kSektion=' . CONF_KUNDEN,
                    'permissions' => 'SETTINGS_CUSTOMERFORM_VIEW',
                    'section'     => CONF_KUNDEN,
                ],
            ],
            __('Default elements') => [
                __('Shop logo')                  => (object)[
                    'link'        => 'shoplogouploader.php',
                    'permissions' => 'DISPLAY_OWN_LOGO_VIEW',
                ],
                __('Search')                     => (object)[
                    'link'        => 'sucheinstellungen.php',
                    'permissions' => 'SETTINGS_ARTICLEOVERVIEW_VIEW',
                    'section'     => CONF_ARTIKELUEBERSICHT,
                ],
                __('Price history')              => (object)[
                    'link'        => 'preisverlauf.php',
                    'permissions' => 'MODULE_PRICECHART_VIEW',
                    'section'     => CONF_PREISVERLAUF,
                ],
                __('Question on item')           => (object)[
                    'link'                  => 'einstellungen.php?kSektion=' . CONF_ARTIKELDETAILS .
                        '&group=configgroup_5_product_question',
                    'permissions'           => 'SETTINGS_ARTICLEDETAILS_VIEW',
                    'excludeFromAccessView' => true,
                    'section'               => CONF_ARTIKELDETAILS,
                ],
                __('Availability notifications') => (object)[
                    'link'                  => 'einstellungen.php?kSektion=' . CONF_ARTIKELDETAILS .
                        '&group=configgroup_5_product_available',
                    'permissions'           => 'SETTINGS_ARTICLEDETAILS_VIEW',
                    'excludeFromAccessView' => true,
                    'section'               => CONF_ARTIKELDETAILS,
                ],
                __('Item badges')                => (object)[
                    'link'        => 'suchspecialoverlay.php',
                    'permissions' => 'DISPLAY_ARTICLEOVERLAYS_VIEW',
                ],
                __('Footer / Boxes')             => (object)[
                    'link'        => 'boxen.php',
                    'permissions' => 'BOXES_VIEW',
                ],
                __('Selection wizard')           => (object)[
                    'link'        => 'auswahlassistent.php',
                    'permissions' => 'EXTENSION_SELECTIONWIZARD_VIEW',
                    'section'     => CONF_AUSWAHLASSISTENT,
                ],
                __('Warehouse display')          => (object)[
                    'link'        => 'warenlager.php',
                    'permissions' => 'WAREHOUSE_VIEW',
                ],
                __('Reviews')                    => (object)[
                    'link'        => 'bewertung.php',
                    'permissions' => 'MODULE_VOTESYSTEM_VIEW',
                    'section'     => CONF_BEWERTUNG,
                ],
                __('Consent manager')            => (object)[
                    'link'        => 'consent.php',
                    'permissions' => 'CONSENT_MANAGER'
                ],
            ],
            __('Custom contents')  => [
                __('Pages')                  => (object)[
                    'link'        => 'links.php',
                    'permissions' => 'CONTENT_PAGE_VIEW',
                ],
                __('Terms / Withdrawal')     => (object)[
                    'link'        => 'agbwrb.php',
                    'permissions' => 'ORDER_AGB_WRB_VIEW',
                ],
                __('Extended customer data') => (object)[
                    'link'        => 'kundenfeld.php',
                    'permissions' => 'ORDER_CUSTOMERFIELDS_VIEW',
                    'section'     => CONF_KUNDENFELD,
                ],
                __('Check boxes')            => (object)[
                    'link'        => 'checkbox.php',
                    'permissions' => 'CHECKBOXES_VIEW',
                ],
                __('Banners')                => (object)[
                    'link'        => 'banner.php',
                    'permissions' => 'DISPLAY_BANNER_VIEW',
                ],
                __('Sliders')                => (object)[
                    'link'        => 'slider.php',
                    'permissions' => 'SLIDER_VIEW',
                ],
            ],
            __('Settings')         => [
                __('Global')         => (object)[
                    'link'        => 'einstellungen.php?kSektion=' . CONF_GLOBAL,
                    'permissions' => 'SETTINGS_GLOBAL_VIEW',
                    'section'     => CONF_GLOBAL,
                ],
                __('Templates')      => (object)[
                    'link'        => 'shoptemplate.php',
                    'permissions' => 'DISPLAY_TEMPLATE_VIEW',
                ],
                __('Images')         => (object)[
                    'link'        => 'bilder.php',
                    'permissions' => 'SETTINGS_IMAGES_VIEW',
                    'section'     => CONF_BILDER,
                ],
                __('Watermark')      => (object)[
                    'link'        => 'branding.php',
                    'permissions' => 'DISPLAY_BRANDING_VIEW',
                ],
                __('Number formats') => (object)[
                    'link'        => 'trennzeichen.php',
                    'permissions' => 'SETTINGS_SEPARATOR_VIEW',
                ],
            ]
        ]
    ],
    __('Plug-ins')       => (object)[
        'icon'  => 'plugins',
        'items' => [
            __('Plug-in manager')    => (object)[
                'link'        => 'pluginverwaltung.php',
                'permissions' => 'PLUGIN_ADMIN_VIEW',
            ],
            __('My purchases')       => (object)[
                'link'        => 'licenses.php',
                'permissions' => 'LICENSE_MANAGER',
            ],
            //        __('Plug-in marketplace') => (object)[
            //            'link' => 'marktplatz.php',
            //            'permissions' => 'PLUGIN_ADMIN_VIEW',
            //        ],
            //        __('My Purchases') => (object)[
            //            'link' => 'dummy.php',
            //            'permissions' => 'PLUGIN_ADMIN_VIEW',
            //        ],
            __('Installed plug-ins') => 'DYNAMIC_PLUGINS',
        ],
    ],
    __('Administration') => (object)[
        'icon'  => 'administration',
        'items' => [
            __('Approvals')       => (object)[
                'link'        => 'freischalten.php',
                'permissions' => 'UNLOCK_CENTRAL_VIEW',
            ],
            __('Import')          => [
                __('Newsletters')  => (object)[
                    'link'        => 'newsletterimport.php',
                    'permissions' => 'IMPORT_NEWSLETTER_RECEIVER_VIEW',
                ],
                __('Customers')    => (object)[
                    'link'        => 'kundenimport.php',
                    'permissions' => 'IMPORT_CUSTOMER_VIEW',
                ],
                __('Postal codes') => (object)[
                    'link'        => 'plz_ort_import.php',
                    'permissions' => 'PLZ_ORT_IMPORT_VIEW',
                ],
            ],
            __('Export')          => [
                __('Site map')       => (object)[
                    'link'        => 'sitemapexport.php',
                    'permissions' => 'EXPORT_SITEMAP_VIEW',
                    'section'     => CONF_SITEMAP,
                ],
                __('RSS feed')       => (object)[
                    'link'        => 'rss.php',
                    'permissions' => 'EXPORT_RSSFEED_VIEW',
                    'section'     => CONF_RSS,
                ],
                __('Other formats')  => (object)[
                    'link'        => 'exportformate.php',
                    'permissions' => 'EXPORT_FORMATS_VIEW',
                ],
                __('Export manager') => (object)[
                    'link'        => 'exportformat_queue.php',
                    'permissions' => 'EXPORT_SCHEDULE_VIEW',
                ],
            ],
//            __('Payments')        => [
                __('Payment methods') => (object)[
                    'link'        => 'zahlungsarten.php',
                    'permissions' => 'ORDER_PAYMENT_VIEW',
                ],
//                __('More payment methods') => (object)[
//                    'link'   => 'zahlungsarten.php',
//                    'permissions' => 'ORDER_PAYMENT_VIEW',
//                ],
//            ],
            __('Shipments')       => [
                __('Shipping methods')     => (object)[
                    'link'        => 'versandarten.php',
                    'permissions' => 'ORDER_SHIPMENT_VIEW',
                ],
                __('Additional packaging') => (object)[
                    'link'        => 'zusatzverpackung.php',
                    'permissions' => 'ORDER_PACKAGE_VIEW',
                ],
            ],
            __('Email')           => [
                __('Server')          => (object)[
                    'link'        => 'einstellungen.php?kSektion=' . CONF_EMAILS,
                    'permissions' => 'SETTINGS_EMAILS_VIEW',
                    'section'     => CONF_EMAILS,
                ],
                __('Email templates') => (object)[
                    'link'        => 'emailvorlagen.php',
                    'permissions' => 'CONTENT_EMAIL_TEMPLATE_VIEW',
                ],
                __('Blacklist')       => (object)[
                    'link'        => 'emailblacklist.php',
                    'permissions' => 'SETTINGS_EMAIL_BLACKLIST_VIEW',
                    'section'     => CONF_EMAILBLACKLIST,
                ],
                __('Log')             => (object)[
                    'link'        => 'emailhistory.php',
                    'permissions' => 'EMAILHISTORY_VIEW',
                ],
            ],
            __('SEO')             => [
                __('Meta data')  => (object)[
                    'link'        => 'globalemetaangaben.php',
                    'permissions' => 'SETTINGS_GLOBAL_META_VIEW',
                    'section'     => CONF_METAANGABEN,
                ],
                __('Forwarding') => (object)[
                    'link'        => 'redirect.php',
                    'permissions' => 'REDIRECT_VIEW',
                ],
                __('Site map')   => (object)[
                    'link'        => 'shopsitemap.php',
                    'permissions' => 'SETTINGS_SITEMAP_VIEW',
                ],
                __('SEO path')   => (object)[
                    'link'        => 'suchspecials.php',
                    'permissions' => 'SETTINGS_SPECIALPRODUCTS_VIEW',
                    'section'     => CONF_SUCHSPECIAL,
                ],
            ],
            __('Languages')       => (object)[
                'link'        => 'sprache.php',
                'permissions' => 'LANGUAGE_VIEW',
            ],
            __('Accounts')        => [
                __('Users')                    => (object)[
                    'link'        => 'benutzerverwaltung.php',
                    'permissions' => 'ACCOUNT_VIEW',
                ],
                __('JTL-Wawi synchronisation') => (object)[
                    'link'        => 'wawisync.php',
                    'permissions' => 'WAWI_SYNC_VIEW',
                ],
            ],
            __('Troubleshooting') => [
                __('System diagnostics') => (object)[
                    'link'        => 'status.php',
                    'permissions' => 'DIAGNOSTIC_VIEW',
                ],
                __('Log')                => (object)[
                    'link'        => 'systemlog.php',
                    'permissions' => 'SYSTEMLOG_VIEW',
                ],
                __('Item images')        => (object)[
                    'link'        => 'bilderverwaltung.php',
                    'permissions' => 'DISPLAY_IMAGES_VIEW',
                ],
                __('Plug-in profiler')   => (object)[
                    'link'        => 'profiler.php',
                    'permissions' => 'PROFILER_VIEW',
                ],

            ],
            __('System')          => [
                __('Cache')      => (object)[
                    'link'        => 'cache.php',
                    'permissions' => 'OBJECTCACHE_VIEW',
                ],
                __('Cron')       => (object)[
                    'link'        => 'cron.php',
                    'permissions' => 'CRON_VIEW',
                ],
                __('Filesystem') => (object)[
                    'link'        => 'filesystem.php',
                    'permissions' => 'FILESYSTEM_VIEW',
                ],
                __('Update')     => (object)[
                    'link'        => 'dbupdater.php',
                    'permissions' => 'SHOP_UPDATE_VIEW',
                ],
                __('Reset')      => (object)[
                    'link'        => 'shopzuruecksetzen.php',
                    'permissions' => 'RESET_SHOP_VIEW',
                ],
                __('Set up') => (object)[
                    'link'        => 'wizard.php',
                    'permissions' => 'WIZARD_VIEW',
                ],
            ],
        ]
    ],
];

$sectionMenuMapping = [];

foreach ($adminMenu as $menuName => $menu) {
    foreach ($menu->items as $subMenuName => $subMenu) {
        if (!is_array($subMenu)) {
            continue;
        }
        foreach ($subMenu as $itemName => $item) {
            if (isset($item->section)) {
                $sectionMenuMapping[$item->section] = (object)[
                    'path' => $menuName . ' -&gt; ' . $subMenuName . ' -&gt; ' . $itemName,
                    'url'  => $item->link,
                ];
            }
        }
    }
}
