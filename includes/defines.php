<?php

// Charset
ifndef('JTL_CHARSET', 'utf-8');
ifndef('DB_CHARSET', 'utf8');
ifndef('DB_COLLATE', 'utf8_unicode_ci');
ini_set('default_charset', JTL_CHARSET);
mb_internal_encoding(strtoupper(JTL_CHARSET));
ifndef('SHOP_TIMEZONE', 'Europe/Berlin');
date_default_timezone_set(SHOP_TIMEZONE);
ifndef('DS', DIRECTORY_SEPARATOR);
// Log-Levels
ifndef('SYNC_LOG_LEVEL', E_ERROR | E_PARSE);
ifndef('ADMIN_LOG_LEVEL', E_ERROR | E_PARSE);
ifndef('SHOP_LOG_LEVEL', E_ERROR | E_PARSE);
ifndef('SMARTY_LOG_LEVEL', E_ERROR | E_PARSE);
error_reporting(SHOP_LOG_LEVEL);
ifndef('TEMPLATE_COMPATIBILITY', false);
ifndef('EVO_COMPATIBILITY', true);
// Image compatibility level 0 => disabled, 1 => referenced in history table, 2 => automatic detection
ifndef('IMAGE_COMPATIBILITY_LEVEL', 1);
ifndef('KEEP_SYNC_FILES', false);
ifndef('PROFILE_PLUGINS', false);
ifndef('PROFILE_SHOP', false);
ifndef('PLUGIN_DEV_MODE', false);
/**
 * Lieferschwellen-Option: Gleichbleibende Bruttopreise (SHOP-2633)
 * @since 5.0.0
 */
ifndef('CONSISTENT_GROSS_PRICES', true);

ifndef('DB_DEFAULT_SQL_MODE', false);

/**
 * WARNING !!! DO NOT USE PROFILE_QUERIES IN PRODUCTION ENVIRONMENT OR PUBLIC AVAILABLE SITES. THE PROFILER CANNOT USE
 * PREPARED STATEMENTS WHEN QUERIES ARE ANALYZED. THEREFORE A LESS SECURE FALLBACK (ESCAPING) IS USED TO ANALYZE
 * QUERIES.
 */
ifndef('PROFILE_QUERIES', false);
ifndef('PROFILE_QUERIES_ECHO', false);

ifndef('ADMIN_MIGRATION', false);

ifndef('IO_LOG_CONSOLE', false);
ifndef('DEFAULT_CURL_OPT_VERIFYPEER', true);
ifndef('DEFAULT_CURL_OPT_VERIFYHOST', 2);
ini_set('session.use_trans_sid', '0');
// Logging (in logs/) 0 => aus, 1 => nur errors, 2 => errors, notifications, 3 => errors, notifications, debug
ifndef('ES_LOGGING', 1);
ifndef('ES_DB_LOGGING', 0);
ifndef('DEBUG_LEVEL', 0);
ifndef('NICEDB_DEBUG_STMT_LEN', 500);
ifndef('NICEDB_EXCEPTION_ECHO', false);
ifndef('NICEDB_EXCEPTION_BACKTRACE', false);
// PHP Error Handler
ifndef('PHP_ERROR_HANDLER', false);
ifndef('DEBUG_FRAME', false);
ifndef('SMARTY_DEBUG_CONSOLE', false);
ifndef('SMARTY_SHOW_LANGKEY', false);
ifndef('SMARTY_FORCE_COMPILE', false);
ifndef('SMARTY_USE_SUB_DIRS', false);
ifndef('JTL_INCLUDE_ONLY_DB', 0);
ifndef('SOCKET_TIMEOUT', 30);
ifndef('ARTICLES_PER_PAGE_HARD_LIMIT', 100);
ifndef('MAX_CORRUPTED_IMAGES', 50);
ifndef('MAX_IMAGES_PER_STEP', 50000);

// Pfade
ifndef('PFAD_CLASSES', 'classes/'); // DEPRECATED
ifndef('PFAD_CONFIG', 'config/');
ifndef('PFAD_INCLUDES', 'includes/');
ifndef('PFAD_TEMPLATES', 'templates/');
ifndef('PFAD_COMPILEDIR', 'templates_c/');
ifndef('PATH_STATIC_MINIFY', PFAD_COMPILEDIR . 'min/');
ifndef('PFAD_EMAILPDFS', 'emailpdfs/');
ifndef('PFAD_NEWSLETTERBILDER', 'newsletter/');
ifndef('PFAD_LINKBILDER', 'links/');
ifndef('PFAD_INCLUDES_LIBS', PFAD_INCLUDES . 'libs/');
ifndef('PFAD_MINIFY', PFAD_INCLUDES . 'vendor/mrclay/minify');
ifndef('PFAD_CKEDITOR', PFAD_INCLUDES_LIBS . 'ckeditor/');
ifndef('PFAD_CODEMIRROR', PFAD_INCLUDES_LIBS . 'codemirror/');
ifndef('PFAD_INCLUDES_TOOLS', PFAD_INCLUDES . 'tools/');
ifndef('PFAD_INCLUDES_EXT', PFAD_INCLUDES . 'ext/');
ifndef('PFAD_INCLUDES_MODULES', PFAD_INCLUDES . 'modules/');
ifndef('PFAD_SMARTY', PFAD_INCLUDES . 'vendor/smarty/smarty/libs/');
ifndef('SMARTY_DIR', PFAD_ROOT . PFAD_SMARTY);
/**
 * @deprecated since 5.0.0
 */
ifndef('PFAD_PHPQUERY', PFAD_INCLUDES . 'vendor/jtlshop/phpquery/src/');
ifndef('PFAD_PCLZIP', PFAD_INCLUDES . 'vendor/pclzip/pclzip/');
ifndef('PFAD_PHPMAILER', PFAD_INCLUDES . 'vendor/phpmailer/phpmailer/');
ifndef('PFAD_BLOWFISH', PFAD_INCLUDES_LIBS . 'vendor/jtlshop/xtea/');
ifndef('PFAD_CLASSES_CORE', PFAD_CLASSES . 'core/');  // DEPRECATED
ifndef('PFAD_OBJECT_CACHING', 'caching/');
ifndef('PFAD_GFX', 'gfx/');
ifndef('PFAD_GFX_AMPEL', PFAD_GFX . 'ampel/');
ifndef('PFAD_DBES', 'dbeS/');
ifndef('PFAD_DBES_TMP', PFAD_DBES . 'tmp/');
ifndef('PFAD_BILDER', 'bilder/');
ifndef('PFAD_BILDER_SLIDER', PFAD_BILDER . 'slider/');
ifndef('PFAD_CRON', 'cron/');
ifndef('PFAD_FONTS', PFAD_INCLUDES . 'fonts/');
ifndef('PFAD_BILDER_INTERN', PFAD_BILDER . 'intern/');
ifndef('PFAD_BILDER_BANNER', PFAD_BILDER . 'banner/');
ifndef('PFAD_NEWSBILDER', PFAD_BILDER . 'news/');
ifndef('PFAD_NEWSKATEGORIEBILDER', PFAD_BILDER . 'newskategorie/');
ifndef('PFAD_SHOPLOGO', PFAD_BILDER_INTERN . 'shoplogo/');
ifndef('PFAD_ADMIN', 'admin/');
ifndef('PFAD_EMAILVORLAGEN', PFAD_ADMIN . 'mailtemplates/');
ifndef('PFAD_MEDIAFILES', 'mediafiles/');
ifndef('IMAGE_SIZE_XS', 'xs');
ifndef('IMAGE_SIZE_SM', 'sm');
ifndef('IMAGE_SIZE_MD', 'md');
ifndef('IMAGE_SIZE_LG', 'lg');
ifndef('PFAD_PRODUKTBILDER', PFAD_BILDER . 'produkte/');
ifndef('PFAD_PRODUKTBILDER_MINI', PFAD_PRODUKTBILDER . 'mini/');
ifndef('PFAD_PRODUKTBILDER_KLEIN', PFAD_PRODUKTBILDER . 'klein/');
ifndef('PFAD_PRODUKTBILDER_NORMAL', PFAD_PRODUKTBILDER . 'normal/');
ifndef('PFAD_PRODUKTBILDER_GROSS', PFAD_PRODUKTBILDER . 'gross/');
ifndef('PFAD_KATEGORIEBILDER', PFAD_BILDER . 'kategorien/');
ifndef('PFAD_VARIATIONSBILDER', PFAD_BILDER . 'variationen/');
ifndef('PFAD_VARIATIONSBILDER_MINI', PFAD_VARIATIONSBILDER . 'mini/');
ifndef('PFAD_VARIATIONSBILDER_NORMAL', PFAD_VARIATIONSBILDER . 'normal/');
ifndef('PFAD_VARIATIONSBILDER_GROSS', PFAD_VARIATIONSBILDER . 'gross/');
ifndef('PFAD_HERSTELLERBILDER', PFAD_BILDER . 'hersteller/');
ifndef('PFAD_HERSTELLERBILDER_NORMAL', PFAD_HERSTELLERBILDER . 'normal/');
ifndef('PFAD_HERSTELLERBILDER_KLEIN', PFAD_HERSTELLERBILDER . 'klein/');
ifndef('PFAD_MERKMALBILDER', PFAD_BILDER . 'merkmale/');
ifndef('PFAD_MERKMALBILDER_NORMAL', PFAD_MERKMALBILDER . 'normal/');
ifndef('PFAD_MERKMALBILDER_KLEIN', PFAD_MERKMALBILDER . 'klein/');
ifndef('PFAD_MERKMALWERTBILDER', PFAD_BILDER . 'merkmalwerte/');
ifndef('PFAD_MERKMALWERTBILDER_NORMAL', PFAD_MERKMALWERTBILDER . 'normal/');
ifndef('PFAD_MERKMALWERTBILDER_KLEIN', PFAD_MERKMALWERTBILDER . 'klein/');
ifndef('PFAD_BRANDINGBILDER', PFAD_BILDER . 'brandingbilder/');
ifndef('PFAD_SUCHSPECIALOVERLAY', PFAD_BILDER . 'suchspecialoverlay/');
ifndef('PFAD_SUCHSPECIALOVERLAY_KLEIN', PFAD_SUCHSPECIALOVERLAY . 'klein/');
ifndef('PFAD_SUCHSPECIALOVERLAY_NORMAL', PFAD_SUCHSPECIALOVERLAY . 'normal/');
ifndef('PFAD_SUCHSPECIALOVERLAY_GROSS', PFAD_SUCHSPECIALOVERLAY . 'gross/');
ifndef('PFAD_SUCHSPECIALOVERLAY_RETINA', PFAD_SUCHSPECIALOVERLAY . 'retina/');
ifndef('PFAD_OVERLAY_TEMPLATE', '/images/overlay/');
ifndef('PFAD_KONFIGURATOR_KLEIN', PFAD_BILDER . 'konfigurator/klein/');
ifndef('PFAD_LOGFILES', PFAD_ROOT . 'jtllogs/');
ifndef('PFAD_EXPORT', 'export/');
ifndef('PFAD_EXPORT_BACKUP', PFAD_EXPORT . 'backup/');
ifndef('PFAD_UPDATE', 'update/');
ifndef('PFAD_WIDGETS', 'widgets/');
ifndef('PFAD_PORTLETS', 'portlets/');
ifndef('PFAD_INSTALL', 'install/');
ifndef('PFAD_SHOPMD5', 'shopmd5files/');
ifndef('PFAD_UPLOADS', PFAD_ROOT . 'uploads/');
ifndef('PFAD_DOWNLOADS_REL', 'downloads/');
ifndef('PFAD_DOWNLOADS_PREVIEW_REL', PFAD_DOWNLOADS_REL . 'vorschau/');
ifndef('PFAD_DOWNLOADS', PFAD_ROOT . PFAD_DOWNLOADS_REL);
ifndef('PFAD_DOWNLOADS_PREVIEW', PFAD_ROOT . PFAD_DOWNLOADS_PREVIEW_REL);
ifndef('PFAD_UPLOAD_CALLBACK', PFAD_INCLUDES_EXT . 'uploads_cb.php');
ifndef('PFAD_IMAGEMAP', PFAD_BILDER . 'banner/');
ifndef('PFAD_EMAILTEMPLATES', 'templates_mail/');
ifndef('PFAD_MEDIA_VIDEO', 'media/video/');
ifndef('PFAD_MEDIA_IMAGE', 'media/image/');
ifndef('PFAD_MEDIA_IMAGE_STORAGE', PFAD_MEDIA_IMAGE . 'storage/');
ifndef('STORAGE_VARIATIONS', PFAD_MEDIA_IMAGE_STORAGE . 'variations/');
ifndef('STORAGE_CONFIGGROUPS', PFAD_MEDIA_IMAGE_STORAGE . 'configgroups/');
ifndef('STORAGE_MANUFACTURERS', PFAD_MEDIA_IMAGE_STORAGE . 'manufacturers/');
ifndef('STORAGE_CATEGORIES', PFAD_MEDIA_IMAGE_STORAGE . 'categories/');
ifndef('STORAGE_CHARACTERISTICS', PFAD_MEDIA_IMAGE_STORAGE . 'characteristics/');
ifndef('STORAGE_CHARACTERISTIC_VALUES', PFAD_MEDIA_IMAGE_STORAGE . 'characteristicvalues/');
ifndef('STORAGE_OPC', PFAD_MEDIA_IMAGE_STORAGE . 'opc/');
ifndef('STORAGE_VIDEO_THUMBS', PFAD_MEDIA_IMAGE_STORAGE . 'videothumbs/');
// Plugins
ifndef('PFAD_PLUGIN', PFAD_INCLUDES . 'plugins/');
// dbeS
ifndef('PFAD_SYNC_TMP', 'tmp/'); //rel zu dbeS
ifndef('PFAD_SYNC_LOGS', PFAD_ROOT . PFAD_DBES . 'logs/');
// Dateien
ifndef('FILE_RSS_FEED', 'rss.xml');
ifndef('FILE_SHOP_FEED', 'shopinfo.xml');
ifndef('FILE_PHPFEHLER', PFAD_LOGFILES . 'phperror.log');
// StandardBilder
ifndef('BILD_KEIN_KATEGORIEBILD_VORHANDEN', PFAD_GFX . 'keinBild.gif');
ifndef('BILD_KEIN_ARTIKELBILD_VORHANDEN', PFAD_GFX . 'keinBild.gif');
ifndef('BILD_KEIN_HERSTELLERBILD_VORHANDEN', PFAD_GFX . 'keinBild.gif');
ifndef('BILD_KEIN_MERKMALBILD_VORHANDEN', PFAD_GFX . 'keinBild.gif');
ifndef('BILD_KEIN_MERKMALWERTBILD_VORHANDEN', PFAD_GFX . 'keinBild_kl.gif');
ifndef('BILD_UPLOAD_ZUGRIFF_VERWEIGERT', PFAD_GFX . 'keinBild.gif');
//MediaImage Regex
ifndef('MEDIAIMAGE_REGEX', '/^media\/image\/(?P<type>product)' .
    '\/(?P<id>\d+)\/(?P<size>xs|sm|md|lg|xl|os)\/(?P<name>[a-zA-Z0-9\-_\.]+)' .
    '(?:(?:~(?P<number>\d+))?)\.(?P<ext>jpg|jpeg|png|gif|webp)$/');
// Suchcache Lebensdauer in Minuten nach letzter Artikeländerung durch JTL-Wawi
ifndef('SUCHCACHE_LEBENSDAUER', 60);
// Steuersatz Standardland OVERRIDE - setzt ein anderes Steuerland, als im Shop angegeben (upper case, ISO 3166-2)
// ifndef('STEUERSATZ_STANDARD_LAND', 'DE')
ifndef('JTLLOG_MAX_LOGSIZE', 200000);
// temp dir for pclzip extension
ifndef('PCLZIP_TEMPORARY_DIR', PFAD_ROOT . PFAD_COMPILEDIR);

ifndef('IMAGE_PRELOAD_LIMIT', 10);
ifndef('FORCE_IMAGEDRIVER_GD', false);
//when the shop has up to n categories, all category data will be loaded by KategorieHelper::combinedGetAll()
//with more then n categories, some db fields will only be selected if the corresponding options are active
ifndef('CATEGORY_FULL_LOAD_LIMIT', 10000);
ifndef('CATEGORY_FULL_LOAD_MAX_LEVEL', 3);
//maximum number of entries in category filter, -1 for no limit
ifndef('CATEGORY_FILTER_ITEM_LIMIT', -1);
ifndef('PRODUCT_LIST_SHOW_RATINGS', false);
ifndef('IMAGE_CLEANUP_LIMIT', 50);
ifndef('OBJECT_CACHE_DIR', PFAD_ROOT . PFAD_COMPILEDIR . 'filecache/');

ifndef('SITEMAP_ITEMS_LIMIT', 25000);
// show child products in product listings? 0 - never, 1 - only when at least 1 filter is active, 2 - always
ifndef('SHOW_CHILD_PRODUCTS', 0);
// redis connect timeout in seconds
ifndef('REDIS_CONNECT_TIMEOUT', 3);

ifndef('SAVE_BOT_SESSION', 0);
ifndef('ES_SESSIONS', 0);

ifndef('MAX_REVISIONS', 5);

ifndef('SHOW_DEBUG_BAR', false);

ifndef('EXS_LIVE', true);

ifndef('ART_MATRIX_MAX', 250);

ifndef('QUEUE_MAX_STUCK_HOURS', 1);

// multi-domain support for different languages
ifndef('EXPERIMENTAL_MULTILANG_SHOP', false);
// slug language does not have to match shop base url language if enabled
ifndef('MULTILANG_URL_FALLBACK', false);

// security
ifndef('EXPORTFORMAT_ALLOW_PHP', false);
ifndef('NEWSLETTER_USE_SECURITY', true);
ifndef('MAILTEMPLATE_USE_SECURITY', true);
ifndef('EXPORTFORMAT_USE_SECURITY', true);
ifndef('EXPORTFORMAT_ALLOWED_FORMATS', 'txt,csv,xml,html,htm,json,yaml,yml,zip,gz');
ifndef('PASSWORD_DEFAULT_LENGTH', 12);
ifndef('SECURE_PHP_FUNCTIONS', '
    addcslashes, addslashes, bin2hex, chop, chr, chunk_split, count_chars, crypt, explode, html_entity_decode,
    htmlentities, htmlspecialchars_decode, htmlspecialchars, implode, join, lcfirst, levenshtein, ltrim, md5, metaphone,
    money_format, nl2br, number_format, ord, rtrim, sha1, similar_text, soundex, sprintf, str_ireplace, str_pad,
    str_repeat, str_replace, str_rot13, str_shuffle, str_split, str_word_count, strcasecmp, strchr, strcmp, strcoll,
    strcspn, strip_tags, stripslashes, stristr, strlen, strnatcasecmp, strnatcmp, strncasecmp, strncmp, strpbrk, strpos,
    strrchr, strrev, strripos, strrpos, strspn, strstr, strtok, strtolower, strtoupper, strtr, substr_compare,
    substr_count, substr_replace, substr, trim, ucfirst, ucwords, vsprintf, var_dump, print_r, printf, wordwrap,
    
    checkdate, date_add, date_create_from_format, date_create_immutable_from_format, date_create_immutable, date_create,
    date_date_set, date_diff, date_format, date_get_last_errors, date_interval_create_from_date_string,
    date_interval_format, date_isodate_set, date_modify, date_offset_get, date_parse_from_format, date_parse, date_sub,
    date_sun_info, date_sunrise, date_sunset, date_time_set, date_timestamp_get, date_timespamp_set, date_timezone_get,
    date_timezone_set, date, getdate, gettimeofday, gmdate, gmmktime, gmstrftime, idate, localtime, microtime, mktime,
    strftime, strptime, strtotime, time, timezone_abbreviations_list, timezone_identifiers_list, timezone_location_get,
    timezone_name_from_abbr, timezone_name_get, timezone_offset_get, timzone_open, timezone_transitions_get,
    timezone_version_get,
    
    preg_filter, preg_quote, preg_replace, preg_split,
    
    bcadd, bccomp, bcdiv, bcmod, bcmul, bcpow, bcpowmod, bcsqrt, bcsub,
    
    abs, acos, acosh, asin, asinh, atan2, atan, atanh, base_convert, bindex, ceil, cos, cosh, decbin, dexhex, decoct,
    deg2rad, exp, expm1, floor, fmod, getrandmax, hexdec, hypot, intdiv, is_finite, is_infinite, is_nan, lcg_value,
    log10, log1p, log, max, min, mt_getrandmax, mt_rand, mt_srand, octdec, pi, pow, rad2deg, rand, round, sin, sinh,
    sqrt, srand, tan, tanh,
    
    json_decode, json_encode, json_last_error_msg, json_last_error,
    
    yaml_emit, yaml_parse,
');

// 0 => off, 1 => html comments, 2 => static badges, 3 => scrolling badges with borders
ifndef('SHOW_TEMPLATE_HINTS', 0);

ifndef('SEO_SLUG_LOWERCASE', false);

ifndef('SAFE_MODE', $GLOBALS['plgSafeMode'] ?? file_exists(PFAD_ROOT. PFAD_ADMIN . PFAD_COMPILEDIR . 'safemode.lck'));

/**
 * @param string     $constant
 * @param string|int $value
 */
function ifndef($constant, $value)
{
    defined($constant) || define($constant, $value);
}

/**
 * @deprecated
 * @return array
function shop_writeable_paths()
{
    trigger_error('The function "shop_writeable_paths()" is removed in a future version!', E_USER_DEPRECATED);

    global $shop_writeable_paths;

    return array_map(function ($v) {
        if (mb_strpos($v, PFAD_ROOT) === 0) {
            $v = mb_substr($v, mb_strlen(PFAD_ROOT));
        }

        return trim($v, '/\\');
    }, $paths);
}
 */

// Static defines (do not edit)
require_once __DIR__ . '/defines_inc.php';
require_once __DIR__ . '/hooks_inc.php';
