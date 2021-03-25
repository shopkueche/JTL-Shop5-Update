<?php declare(strict_types=1);

namespace JTL\Plugin;

use MyCLabs\Enum\Enum;

/**
 * Class InstallCode
 * @package JTL\Plugin
 */
class InstallCode extends Enum
{
    public const OK = 1;

    public const WRONG_PARAM = 2;

    public const DIR_DOES_NOT_EXIST = 3;

    public const INFO_XML_MISSING = 4;

    public const NO_PLUGIN_FOUND = 5;

    public const INVALID_NAME = 6;

    public const INVALID_PLUGIN_ID = 7;

    public const INSTALL_NODE_MISSING = 8;

    public const INVALID_XML_VERSION_NUMBER = 9;

    public const INVALID_VERSION_NUMBER = 10;

    public const INVALID_DATE = 11;

    public const MISSING_SQL_FILE = 12;

    public const MISSING_HOOKS = 13;

    public const INVALID_HOOK = 14;

    public const INVALID_CUSTOM_LINK_NAME = 15;

    public const INVALID_CUSTOM_LINK_FILE_NAME = 16;

    public const MISSING_CUSTOM_LINK_FILE = 17;

    public const INVALID_CONFIG_LINK_NAME = 18;

    public const MISSING_CONFIG = 19;

    public const INVALID_CONFIG_TYPE = 20;

    public const INVALID_CONFIG_INITIAL_VALUE = 21;

    public const INVALID_CONFIG_SORT_VALUE = 22;

    public const INVALID_CONFIG_NAME = 23;

    public const MISSING_CONFIG_SELECTBOX_OPTIONS = 24;

    public const INVALID_CONFIG_OPTION = 25;

    public const MISSING_LANG_VARS = 26;

    public const INVALID_LANG_VAR_NAME = 27;

    public const MISSING_LOCALIZED_LANG_VAR = 28;

    public const INVALID_LANG_VAR_ISO = 29;

    public const INVALID_LOCALIZED_LANG_VAR_NAME = 30;

    public const MISSING_HOOK_FILE = 31;

    public const MISSING_VERSION_DIR = 32;

    public const INVALID_CONF = 33;

    public const INVALID_CONF_VALUE_NAME = 34;

    public const INVALID_XML_VERSION = 35;

    public const INVALID_SHOP_VERSION = 36;

    public const SHOP_VERSION_COMPATIBILITY = 37;

    public const MISSING_FRONTEND_LINKS = 38;

    public const INVALID_FRONTEND_LINK_FILENAME = 39;

    public const INVALID_FRONTEND_LINK_NAME = 40;

    public const INVALID_FRONEND_LINK_VISIBILITY = 41;

    public const INVALID_FRONEND_LINK_PRINT = 42;

    public const INVALID_FRONEND_LINK_ISO = 43;

    public const INVALID_FRONEND_LINK_SEO = 44;

    public const INVALID_FRONEND_LINK_NAME = 45;

    public const INVALID_FRONEND_LINK_TITLE = 46;

    public const INVALID_FRONEND_LINK_META_TITLE = 47;

    public const INVALID_FRONEND_LINK_META_KEYWORDS = 48;

    public const INVALID_FRONEND_LINK_META_DESCRIPTION = 49;

    public const INVALID_PAYMENT_METHOD_NAME = 50;

    public const INVALID_PAYMENT_METHOD_MAIL = 51;

    public const INVALID_PAYMENT_METHOD_TSCODE = 52;

    public const INVALID_PAYMENT_METHOD_PRE_ORDER = 53;

    public const INVALID_PAYMENT_METHOD_CLASS_FILE = 54;

    public const MISSING_PAYMENT_METHOD_FILE = 55;

    public const INVALID_PAYMENT_METHOD_TEMPLATE = 56;

    public const MISSING_PAYMENT_METHOD_TEMPLATE = 57;

    public const MISSING_PAYMENT_METHOD_LANGUAGES = 58;

    public const INVALID_PAYMENT_METHOD_LANGUAGE_ISO = 59;

    public const INVALID_PAYMENT_METHOD_NAME_LOCALIZED = 60;

    public const INVALID_PAYMENT_METHOD_CHARGE_NAME = 61;

    public const INVALID_PAYMENT_METHOD_INFO_TEXT = 62;

    public const INVALID_PAYMENT_METHOD_CONFIG_TYPE = 63;

    public const INVALID_PAYMENT_METHOD_CONFIG_INITITAL_VALUE = 64;

    public const INVALID_PAYMENT_METHOD_CONFIG_SORT = 65;

    public const INVALID_PAYMENT_METHOD_CONFIG_CONF = 66;

    public const INVALID_PAYMENT_METHOD_CONFIG_NAME = 67;

    public const INVALID_PAYMENT_METHOD_VALUE_NAME = 68;

    public const MISSING_PAYMENT_METHOD_SELECTBOX_OPTIONS = 69;

    public const INVALID_PAYMENT_METHOD_OPTION = 70;

    public const INVALID_PAYMENT_METHOD_SORT = 71;

    public const INVALID_PAYMENT_METHOD_SOAP = 72;

    public const INVALID_PAYMENT_METHOD_CURL = 73;

    public const INVALID_PAYMENT_METHOD_SOCKETS = 74;

    public const INVALID_PAYMENT_METHOD_CLASS_NAME = 75;

    public const INVALID_FULLSCREEN_TEMPLATE = 76;

    public const MISSING_FRONTEND_LINK_TEMPLATE = 77;

    public const TOO_MANY_FULLSCREEN_TEMPLATE_NAMES = 78;

    public const INVALID_FULLSCREEN_TEMPLATE_NAME = 79;

    public const MISSING_FULLSCREEN_TEMPLATE_FILE = 80;

    public const INVALID_FRONTEND_LINK_TEMPLATE_FULLSCREEN_TEMPLATE = 81;

    public const MISSING_BOX = 82;

    public const INVALID_BOX_NAME = 83;

    public const INVALID_BOX_TEMPLATE = 84;

    public const MISSING_BOX_TEMPLATE_FILE = 85;

    public const MISSING_LICENCE_FILE = 86;

    public const INVALID_LICENCE_FILE_NAME = 87;

    public const MISSING_LICENCE = 88;

    public const MISSING_LICENCE_CHECKLICENCE_METHOD = 89;

    public const DUPLICATE_PLUGIN_ID = 90;

    public const MISSING_EMAIL_TEMPLATES = 91;

    public const INVALID_TEMPLATE_NAME = 92;

    public const INVALID_TEMPLATE_TYPE = 93;

    public const INVALID_TEMPLATE_MODULE_ID = 94;

    public const INVALID_TEMPLATE_ACTIVE = 95;

    public const INVALID_TEMPLATE_AKZ = 96;

    public const INVALID_TEMPLATE_AGB = 97;

    public const INVALID_TEMPLATE_WRB = 98;

    public const INVALID_EMAIL_TEMPLATE_ISO = 99;

    public const INVALID_EMAIL_TEMPLATE_SUBJECT = 100;

    public const MISSING_EMAIL_TEMPLATE_LANGUAGE = 101;

    public const INVALID_CHECKBOX_FUNCTION_NAME = 102;

    public const INVALID_CHECKBOX_FUNCTION_ID = 103;

    public const INVALID_FRONTEND_LINK_NO_FOLLOW = 104;

    public const MISSING_WIDGETS = 105;

    public const INVALID_WIDGET_TITLE = 106;

    public const INVALID_WIDGET_CLASS = 107;

    public const MISSING_WIDGET_CLASS_FILE = 108;

    public const INVALID_WIDGET_CONTAINER = 109;

    public const INVALID_WIDGET_POS = 110;

    public const INVALID_WIDGET_EXPANDED = 111;

    public const INVALID_WIDGET_ACTIVE = 112;

    public const INVALID_PAYMENT_METHOD_ADDITIONAL_STEP_TEMPLATE_FILE = 113;

    public const MISSING_PAYMENT_METHOD_ADDITIONAL_STEP_FILE = 114;

    public const MISSING_FORMATS = 115;

    public const INVALID_FORMAT_NAME = 116;

    public const INVALID_FORMAT_FILE_NAME = 117;

    public const MISSING_FORMAT_CONTENT = 118;

    public const INVALID_FORMAT_ENCODING = 119;

    public const INVALID_FORMAT_SHIPPING_COSTS_DELIVERY_COUNTRY = 120;

    public const INVALID_FORMAT_CONTENT_FILE = 121;

    public const MISSING_EXTENDED_TEMPLATE = 122;

    public const INVALID_EXTENDED_TEMPLATE_FILE_NAME = 123;

    public const MISSING_EXTENDED_TEMPLATE_FILE = 124;

    public const MISSING_UNINSTALL_FILE = 125;

    public const OK_LEGACY = 126;

    public const IONCUBE_REQUIRED = 127;

    public const INVALID_OPTIONS_SOURE_FILE = 128;

    public const MISSING_OPTIONS_SOURE_FILE = 129;

    public const MISSING_BOOTSTRAP_CLASS = 130;

    public const INVALID_BOOTSTRAP_IMPLEMENTATION = 131;

    public const INVALID_AUTHOR = 132;

    public const MISSING_PORTLETS = 200;

    public const INVALID_PORTLET_TITLE = 201;

    public const INVALID_PORTLET_CLASS = 202;

    public const INVALID_PORTLET_CLASS_FILE = 203;

    public const INVALID_PORTLET_GROUP = 204;

    public const INVALID_PORTLET_ACTIVE = 205;

    public const MISSING_BLUEPRINTS = 206;

    public const INVALID_BLUEPRINT_NAME = 207;

    public const INVALID_BLUEPRINT_FILE = 208;

    public const SQL_MISSING_DATA = 2;

    public const SQL_ERROR = 3;

    public const SQL_WRONG_TABLE_NAME_DELETE = 4;

    public const SQL_WRONG_TABLE_NAME_CREATE = 5;

    public const SQL_INVALID_FILE_CONTENT = 6;

    public const SQL_CANNOT_SAVE_HOOK = 300;

    public const SQL_CANNOT_SAVE_UNINSTALL = 301;

    public const SQL_CANNOT_SAVE_ADMIN_MENU_ITEM = 302;

    public const SQL_CANNOT_SAVE_SETTINGS_ITEM = 303;

    public const SQL_CANNOT_SAVE_SETTING = 304;

    public const SQL_CANNOT_FIND_LINK_GROUP = 305;

    public const SQL_CANNOT_SAVE_LINK = 306;

    public const SQL_CANNOT_SAVE_PAYMENT_METHOD = 307;

    public const SQL_CANNOT_SAVE_PAYMENT_METHOD_LOCALIZATION = 308;

    public const SQL_CANNOT_SAVE_PAYMENT_METHOD_LANGUAGE = 309;

    public const SQL_CANNOT_SAVE_PAYMENT_METHOD_SETTING = 310;

    public const SQL_CANNOT_SAVE_BOX_TEMPLATE = 311;

    public const SQL_CANNOT_SAVE_TEMPLATE = 312;

    public const SQL_CANNOT_SAVE_EMAIL_TEMPLATE = 313;

    public const SQL_CANNOT_SAVE_LANG_VAR = 314;

    public const SQL_CANNOT_SAVE_LANG_VAR_LOCALIZATION = 315;

    public const SQL_CANNOT_SAVE_WIDGET = 316;

    public const SQL_CANNOT_SAVE_PORTLET = 317;

    public const SQL_CANNOT_SAVE_BLUEPRINT = 318;

    public const SQL_CANNOT_SAVE_EXPORT = 319;

    public const EXT_MUST_NOT_HAVE_UNINSTALLER = 400;

    public const WRONG_EXT_DIR = 401;

    public const INVALID_STORE_ID = 402;

    public const MISSING_PLUGIN_NODE = 403;

    public const SQL_CANNOT_SAVE_VENDOR = 410;

    public const MISSING_CONSENT_VENDOR = 411;

    public const INVALID_CONSENT_VENDOR_NAME = 412;

    public const INVALID_CONSENT_VENDOR_PURPOSE = 413;

    public const INVALID_CONSENT_VENDOR_LOCALIZATION = 414;

    public const INVALID_CONSENT_VENDOR_LOCALIZATION_ISO = 415;

    public const INVALID_CONSENT_VENDOR_DESCRIPTION = 416;

    public const INVALID_CONSENT_VENDOR_PRIV_POL = 417;

    public const INVALID_CONSENT_VENDOR_ID = 418;

    public const INVALID_CONSENT_VENDOR_COMPANY = 419;
}
