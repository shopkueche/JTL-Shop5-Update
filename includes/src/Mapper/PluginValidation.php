<?php declare(strict_types=1);

namespace JTL\Mapper;

use JTL\Plugin\InstallCode;

/**
 * Class PluginValidation
 * @package JTL\Mapper
 */
class PluginValidation
{
    /**
     * @param int     $code
     * @param string|null $pluginID
     * @return string
     */
    public function map(int $code, string $pluginID = null): string
    {
        if ($code === 0) {
            return '';
        }
        switch ($code) {
            case InstallCode::WRONG_PARAM:
                $return = __('WRONG_PARAM');
                break;
            case InstallCode::DIR_DOES_NOT_EXIST:
                $return = __('DIR_DOES_NOT_EXIST');
                break;
            case InstallCode::INFO_XML_MISSING:
                $return = __('INFO_XML_MISSING');
                break;
            case InstallCode::NO_PLUGIN_FOUND:
                $return = __('NO_PLUGIN_FOUND');
                break;
            case InstallCode::INVALID_NAME:
                $return = __('INVALID_NAME');
                break;
            case InstallCode::INVALID_PLUGIN_ID:
                $return = __('INVALID_PLUGIN_ID');
                break;
            case InstallCode::INSTALL_NODE_MISSING:
                $return = __('INSTALL_NODE_MISSING');
                break;
            case InstallCode::INVALID_XML_VERSION_NUMBER:
                $return = __('INVALID_XML_VERSION_NUMBER');
                break;
            case InstallCode::INVALID_VERSION_NUMBER:
                $return = __('INVALID_VERSION_NUMBER');
                break;
            case InstallCode::INVALID_DATE:
                $return = __('INVALID_DATE');
                break;
            case InstallCode::MISSING_SQL_FILE:
                $return = __('MISSING_SQL_FILE');
                break;
            case InstallCode::MISSING_HOOKS:
                $return = __('MISSING_HOOKS');
                break;
            case InstallCode::INVALID_HOOK:
                $return = __('INVALID_HOOK');
                break;
            case InstallCode::INVALID_CUSTOM_LINK_NAME:
                $return = __('INVALID_CUSTOM_LINK_NAME');
                break;
            case InstallCode::INVALID_CUSTOM_LINK_FILE_NAME:
                $return = __('INVALID_CUSTOM_LINK_FILE_NAME');
                break;
            case InstallCode::MISSING_CUSTOM_LINK_FILE:
                $return = __('MISSING_CUSTOM_LINK_FILE');
                break;
            case InstallCode::INVALID_CONFIG_LINK_NAME:
                $return = __('INVALID_CONFIG_LINK_NAME');
                break;
            case InstallCode::MISSING_CONFIG:
                $return = __('MISSING_CONFIG');
                break;
            case InstallCode::INVALID_CONFIG_TYPE:
                $return = __('INVALID_CONFIG_TYPE');
                break;
            case InstallCode::INVALID_CONFIG_INITIAL_VALUE:
                $return = __('INVALID_CONFIG_INITIAL_VALUE');
                break;
            case InstallCode::INVALID_CONFIG_SORT_VALUE:
                $return = __('INVALID_CONFIG_SORT_VALUE');
                break;
            case InstallCode::INVALID_CONFIG_NAME:
                $return = __('INVALID_CONFIG_NAME');
                break;
            case InstallCode::MISSING_CONFIG_SELECTBOX_OPTIONS:
            case InstallCode::MISSING_PAYMENT_METHOD_SELECTBOX_OPTIONS:
                $return = __('MISSING_CONFIG_SELECTBOX_OPTIONS');
                break;
            case InstallCode::INVALID_CONFIG_OPTION:
            case InstallCode::INVALID_PAYMENT_METHOD_OPTION:
                $return = __('INVALID_CONFIG_OPTION');
                break;
            case InstallCode::MISSING_LANG_VARS:
                $return = __('MISSING_LANG_VARS');
                break;
            case InstallCode::INVALID_LANG_VAR_NAME:
                $return = __('INVALID_LANG_VAR_NAME');
                break;
            case InstallCode::MISSING_LOCALIZED_LANG_VAR:
                $return = __('MISSING_LOCALIZED_LANG_VAR');
                break;
            case InstallCode::INVALID_LANG_VAR_ISO:
                $return = __('INVALID_LANG_VAR_ISO');
                break;
            case InstallCode::INVALID_LOCALIZED_LANG_VAR_NAME:
                $return = __('INVALID_LOCALIZED_LANG_VAR_NAME');
                break;
            case InstallCode::MISSING_HOOK_FILE:
                $return = __('MISSING_HOOK_FILE');
                break;
            case InstallCode::MISSING_VERSION_DIR:
                $return = __('MISSING_VERSION_DIR');
                break;
            case InstallCode::INVALID_CONF:
                $return = __('INVALID_CONF');
                break;
            case InstallCode::INVALID_CONF_VALUE_NAME:
                $return = __('INVALID_CONF_VALUE_NAME');
                break;
            case InstallCode::INVALID_XML_VERSION:
                $return = __('INVALID_XML_VERSION');
                break;
            case InstallCode::INVALID_SHOP_VERSION:
                $return = __('INVALID_SHOP_VERSION');
                break;
            case InstallCode::SHOP_VERSION_COMPATIBILITY:
                $return = __('SHOP_VERSION_COMPATIBILITY');
                break;
            case InstallCode::MISSING_FRONTEND_LINKS:
                $return = __('MISSING_FRONTEND_LINKS');
                break;
            case InstallCode::INVALID_FRONTEND_LINK_FILENAME:
                $return = __('INVALID_FRONTEND_LINK_FILENAME');
                break;
            case InstallCode::INVALID_FRONTEND_LINK_NAME:
                $return = __('INVALID_FRONTEND_LINK_NAME');
                break;
            case InstallCode::INVALID_FRONEND_LINK_VISIBILITY:
                $return = __('INVALID_FRONEND_LINK_VISIBILITY');
                break;
            case InstallCode::INVALID_FRONEND_LINK_PRINT:
                $return = __('INVALID_FRONEND_LINK_PRINT');
                break;
            case InstallCode::INVALID_FRONEND_LINK_ISO:
                $return = __('INVALID_FRONEND_LINK_ISO');
                break;
            case InstallCode::INVALID_FRONEND_LINK_SEO:
                $return = __('INVALID_FRONEND_LINK_SEO');
                break;
            case InstallCode::INVALID_FRONEND_LINK_NAME:
                $return = __('INVALID_FRONEND_LINK_NAME');
                break;
            case InstallCode::INVALID_FRONEND_LINK_TITLE:
                $return = __('INVALID_FRONEND_LINK_TITLE');
                break;
            case InstallCode::INVALID_FRONEND_LINK_META_TITLE:
                $return = __('INVALID_FRONEND_LINK_META_TITLE');
                break;
            case InstallCode::INVALID_FRONEND_LINK_META_KEYWORDS:
                $return = __('INVALID_FRONEND_LINK_META_KEYWORDS');
                break;
            case InstallCode::INVALID_FRONEND_LINK_META_DESCRIPTION:
                $return = __('INVALID_FRONEND_LINK_META_DESCRIPTION');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_NAME:
                $return = __('INVALID_PAYMENT_METHOD_NAME');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_MAIL:
                $return = __('INVALID_PAYMENT_METHOD_MAIL');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_TSCODE:
                $return = __('INVALID_PAYMENT_METHOD_TSCODE');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_PRE_ORDER:
                $return = __('INVALID_PAYMENT_METHOD_PRE_ORDER');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CLASS_FILE:
                $return = __('INVALID_PAYMENT_METHOD_CLASS_FILE');
                break;
            case InstallCode::MISSING_PAYMENT_METHOD_FILE:
                $return = __('MISSING_PAYMENT_METHOD_FILE');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_TEMPLATE:
                $return = __('INVALID_PAYMENT_METHOD_TEMPLATE');
                break;
            case InstallCode::MISSING_PAYMENT_METHOD_TEMPLATE:
                $return = __('MISSING_PAYMENT_METHOD_TEMPLATE');
                break;
            case InstallCode::MISSING_PAYMENT_METHOD_LANGUAGES:
                $return = __('MISSING_PAYMENT_METHOD_LANGUAGES');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_LANGUAGE_ISO:
                $return = __('INVALID_PAYMENT_METHOD_LANGUAGE_ISO');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_NAME_LOCALIZED:
                $return = __('INVALID_PAYMENT_METHOD_NAME_LOCALIZED');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CHARGE_NAME:
                $return = __('INVALID_PAYMENT_METHOD_CHARGE_NAME');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_INFO_TEXT:
                $return = __('INVALID_PAYMENT_METHOD_INFO_TEXT');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CONFIG_TYPE:
                $return = __('INVALID_PAYMENT_METHOD_CONFIG_TYPE');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CONFIG_INITITAL_VALUE:
                $return = __('INVALID_PAYMENT_METHOD_CONFIG_INITITAL_VALUE');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CONFIG_SORT:
                $return = __('INVALID_PAYMENT_METHOD_CONFIG_SORT');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CONFIG_CONF:
                $return = __('INVALID_PAYMENT_METHOD_CONFIG_CONF');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CONFIG_NAME:
                $return = __('INVALID_PAYMENT_METHOD_CONFIG_NAME');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_VALUE_NAME:
                $return = __('INVALID_PAYMENT_METHOD_VALUE_NAME');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_SORT:
                $return = __('INVALID_PAYMENT_METHOD_SORT');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_SOAP:
                $return = __('INVALID_PAYMENT_METHOD_SOAP');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CURL:
                $return = __('INVALID_PAYMENT_METHOD_CURL');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_SOCKETS:
                $return = __('INVALID_PAYMENT_METHOD_SOCKETS');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CLASS_NAME:
                $return = __('INVALID_PAYMENT_METHOD_CLASS_NAME');
                break;
            case InstallCode::INVALID_FULLSCREEN_TEMPLATE:
                $return = __('INVALID_FULLSCREEN_TEMPLATE');
                break;
            case InstallCode::MISSING_FRONTEND_LINK_TEMPLATE:
                $return = __('MISSING_FRONTEND_LINK_TEMPLATE');
                break;
            case InstallCode::TOO_MANY_FULLSCREEN_TEMPLATE_NAMES:
                $return = __('TOO_MANY_FULLSCREEN_TEMPLATE_NAMES');
                break;
            case InstallCode::INVALID_FULLSCREEN_TEMPLATE_NAME:
                $return = __('INVALID_FULLSCREEN_TEMPLATE_NAME');
                break;
            case InstallCode::MISSING_FULLSCREEN_TEMPLATE_FILE:
                $return = __('MISSING_FULLSCREEN_TEMPLATE_FILE');
                break;
            case InstallCode::INVALID_FRONTEND_LINK_TEMPLATE_FULLSCREEN_TEMPLATE:
                $return = __('INVALID_FRONTEND_LINK_TEMPLATE_FULLSCREEN_TEMPLATE');
                break;
            case InstallCode::MISSING_BOX:
                $return = __('MISSING_BOX');
                break;
            case InstallCode::INVALID_BOX_NAME:
                $return = __('INVALID_BOX_NAME');
                break;
            case InstallCode::INVALID_BOX_TEMPLATE:
                $return = __('INVALID_BOX_TEMPLATE');
                break;
            case InstallCode::MISSING_BOX_TEMPLATE_FILE:
                $return = __('MISSING_BOX_TEMPLATE_FILE');
                break;
            case InstallCode::MISSING_LICENCE_FILE:
                $return = __('MISSING_LICENCE_FILE');
                break;
            case InstallCode::INVALID_LICENCE_FILE_NAME:
                $return = __('INVALID_LICENCE_FILE_NAME');
                break;
            case InstallCode::MISSING_LICENCE:
                $return = __('MISSING_LICENCE');
                break;
            case InstallCode::MISSING_LICENCE_CHECKLICENCE_METHOD:
                $return = __('MISSING_LICENCE_CHECKLICENCE_METHOD');
                break;
            case InstallCode::DUPLICATE_PLUGIN_ID:
                $return = __('DUPLICATE_PLUGIN_ID');
                break;
            case InstallCode::MISSING_EMAIL_TEMPLATES:
                $return = __('MISSING_EMAIL_TEMPLATES');
                break;
            case InstallCode::INVALID_TEMPLATE_NAME:
                $return = __('INVALID_TEMPLATE_NAME');
                break;
            case InstallCode::INVALID_TEMPLATE_TYPE:
                $return = __('INVALID_TEMPLATE_TYPE');
                break;
            case InstallCode::INVALID_TEMPLATE_MODULE_ID:
                $return = __('INVALID_TEMPLATE_MODULE_ID');
                break;
            case InstallCode::INVALID_TEMPLATE_ACTIVE:
                $return = __('INVALID_TEMPLATE_ACTIVE');
                break;
            case InstallCode::INVALID_TEMPLATE_AKZ:
                $return = __('INVALID_TEMPLATE_AKZ');
                break;
            case InstallCode::INVALID_TEMPLATE_AGB:
                $return = __('INVALID_TEMPLATE_AGB');
                break;
            case InstallCode::INVALID_TEMPLATE_WRB:
                $return = __('INVALID_TEMPLATE_WRB');
                break;
            case InstallCode::INVALID_EMAIL_TEMPLATE_ISO:
                $return = __('INVALID_EMAIL_TEMPLATE_ISO');
                break;
            case InstallCode::INVALID_EMAIL_TEMPLATE_SUBJECT:
                $return = __('INVALID_EMAIL_TEMPLATE_SUBJECT');
                break;
            case InstallCode::MISSING_EMAIL_TEMPLATE_LANGUAGE:
                $return = __('MISSING_EMAIL_TEMPLATE_LANGUAGE');
                break;
            case InstallCode::INVALID_CHECKBOX_FUNCTION_NAME:
                $return = __('INVALID_CHECKBOX_FUNCTION_NAME');
                break;
            case InstallCode::INVALID_CHECKBOX_FUNCTION_ID:
                $return = __('INVALID_CHECKBOX_FUNCTION_ID');
                break;
            case InstallCode::INVALID_FRONTEND_LINK_NO_FOLLOW:
                $return = __('INVALID_FRONTEND_LINK_NO_FOLLOW');
                break;
            case InstallCode::MISSING_WIDGETS:
                $return = __('MISSING_WIDGETS');
                break;
            case InstallCode::INVALID_WIDGET_TITLE:
                $return = __('INVALID_WIDGET_TITLE');
                break;
            case InstallCode::INVALID_WIDGET_CLASS:
                $return = __('INVALID_WIDGET_CLASS');
                break;
            case InstallCode::MISSING_WIDGET_CLASS_FILE:
                $return = __('MISSING_WIDGET_CLASS_FILE');
                break;
            case InstallCode::INVALID_WIDGET_CONTAINER:
                $return = __('INVALID_WIDGET_CONTAINER');
                break;
            case InstallCode::INVALID_WIDGET_POS:
                $return = __('INVALID_WIDGET_POS');
                break;
            case InstallCode::INVALID_WIDGET_EXPANDED:
                $return = __('INVALID_WIDGET_EXPANDED');
                break;
            case InstallCode::INVALID_WIDGET_ACTIVE:
                $return = __('INVALID_WIDGET_ACTIVE');
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_ADDITIONAL_STEP_TEMPLATE_FILE:
                $return = __('INVALID_PAYMENT_METHOD_ADDITIONAL_STEP_TEMPLATE_FILE');
                break;
            case InstallCode::MISSING_PAYMENT_METHOD_ADDITIONAL_STEP_FILE:
                $return = __('MISSING_PAYMENT_METHOD_ADDITIONAL_STEP_FILE');
                break;
            case InstallCode::MISSING_FORMATS:
                $return = __('MISSING_FORMATS');
                break;
            case InstallCode::INVALID_FORMAT_NAME:
                $return = __('INVALID_FORMAT_NAME');
                break;
            case InstallCode::INVALID_FORMAT_FILE_NAME:
                $return = __('INVALID_FORMAT_FILE_NAME');
                break;
            case InstallCode::MISSING_FORMAT_CONTENT:
                $return = __('MISSING_FORMAT_CONTENT');
                break;
            case InstallCode::INVALID_FORMAT_ENCODING:
                $return = __('INVALID_FORMAT_ENCODING');
                break;
            case InstallCode::INVALID_FORMAT_SHIPPING_COSTS_DELIVERY_COUNTRY:
                $return = __('INVALID_FORMAT_SHIPPING_COSTS_DELIVERY_COUNTRY');
                break;
            case InstallCode::INVALID_FORMAT_CONTENT_FILE:
                $return = __('INVALID_FORMAT_CONTENT_FILE');
                break;
            case InstallCode::MISSING_EXTENDED_TEMPLATE:
                $return = __('MISSING_EXTENDED_TEMPLATE');
                break;
            case InstallCode::INVALID_EXTENDED_TEMPLATE_FILE_NAME:
                $return = __('INVALID_EXTENDED_TEMPLATE_FILE_NAME');
                break;
            case InstallCode::MISSING_EXTENDED_TEMPLATE_FILE:
                $return = __('MISSING_EXTENDED_TEMPLATE_FILE');
                break;
            case InstallCode::MISSING_UNINSTALL_FILE:
                $return = __('MISSING_UNINSTALL_FILE');
                break;
            case InstallCode::IONCUBE_REQUIRED:
                $return = __('IONCUBE_REQUIRED');
                break;
            case InstallCode::INVALID_OPTIONS_SOURE_FILE:
                $return = __('INVALID_OPTIONS_SOURE_FILE');
                break;
            case InstallCode::MISSING_OPTIONS_SOURE_FILE:
                $return = __('MISSING_OPTIONS_SOURE_FILE');
                break;
            case InstallCode::MISSING_BOOTSTRAP_CLASS:
                $return = __('MISSING_BOOTSTRAP_CLASS');
                break;
            case InstallCode::INVALID_BOOTSTRAP_IMPLEMENTATION:
                $return = __('INVALID_BOOTSTRAP_IMPLEMENTATION');
                break;
            case InstallCode::INVALID_AUTHOR:
                $return = __('INVALID_AUTHOR');
                break;
            case InstallCode::MISSING_PORTLETS:
                $return = __('MISSING_PORTLETS');
                break;
            case InstallCode::INVALID_PORTLET_TITLE:
                $return = __('INVALID_PORTLET_TITLE');
                break;
            case InstallCode::INVALID_PORTLET_CLASS:
                $return = __('INVALID_PORTLET_CLASS');
                break;
            case InstallCode::INVALID_PORTLET_CLASS_FILE:
                $return = __('INVALID_PORTLET_CLASS_FILE');
                break;
            case InstallCode::INVALID_PORTLET_GROUP:
                $return = __('INVALID_PORTLET_GROUP');
                break;
            case InstallCode::INVALID_PORTLET_ACTIVE:
                $return = __('INVALID_PORTLET_ACTIVE');
                break;
            case InstallCode::MISSING_BLUEPRINTS:
                $return = __('MISSING_BLUEPRINTS');
                break;
            case InstallCode::INVALID_BLUEPRINT_NAME:
                $return = __('INVALID_BLUEPRINT_NAME');
                break;
            case InstallCode::INVALID_BLUEPRINT_FILE:
                $return = __('INVALID_BLUEPRINT_FILE');
                break;
            case InstallCode::EXT_MUST_NOT_HAVE_UNINSTALLER:
                $return = __('EXT_MUST_NOT_HAVE_UNINSTALLER');
                break;
            case InstallCode::WRONG_EXT_DIR:
                $return = __('WRONG_EXT_DIR');
                break;
            case InstallCode::MISSING_PLUGIN_NODE:
                $return = __('MISSING_PLUGIN_NODE');
                break;
            case InstallCode::OK_LEGACY:
                $return = __('OK_LEGACY');
                break;
            case InstallCode::SQL_MISSING_DATA:
                $return = __('SQL_MISSING_DATA');
                break;
            case InstallCode::SQL_ERROR:
                $return = __('SQL_ERROR');
                break;
            case InstallCode::SQL_WRONG_TABLE_NAME_DELETE:
                $return = __('SQL_WRONG_TABLE_NAME_DELETE');
                break;
            case InstallCode::SQL_WRONG_TABLE_NAME_CREATE:
                $return = __('SQL_WRONG_TABLE_NAME_CREATE');
                break;
            case InstallCode::SQL_INVALID_FILE_CONTENT:
                $return = __('SQL_INVALID_FILE_CONTENT');
                break;
            case InstallCode::SQL_CANNOT_SAVE_HOOK:
                $return = __('SQL_CANNOT_SAVE_HOOK');
                break;
            case InstallCode::SQL_CANNOT_SAVE_UNINSTALL:
                $return = __('SQL_CANNOT_SAVE_UNINSTALL');
                break;
            case InstallCode::SQL_CANNOT_SAVE_ADMIN_MENU_ITEM:
                $return = __('SQL_CANNOT_SAVE_ADMIN_MENU_ITEM');
                break;
            case InstallCode::SQL_CANNOT_SAVE_SETTINGS_ITEM:
                $return = __('SQL_CANNOT_SAVE_SETTINGS_ITEM');
                break;
            case InstallCode::SQL_CANNOT_SAVE_SETTING:
                $return = __('SQL_CANNOT_SAVE_SETTING');
                break;
            case InstallCode::SQL_CANNOT_FIND_LINK_GROUP:
                $return = __('SQL_CANNOT_FIND_LINK_GROUP');
                break;
            case InstallCode::SQL_CANNOT_SAVE_LINK:
                $return = __('SQL_CANNOT_SAVE_LINK');
                break;
            case InstallCode::SQL_CANNOT_SAVE_PAYMENT_METHOD:
                $return = __('SQL_CANNOT_SAVE_PAYMENT_METHOD');
                break;
            case InstallCode::SQL_CANNOT_SAVE_PAYMENT_METHOD_LOCALIZATION:
                $return = __('SQL_CANNOT_SAVE_PAYMENT_METHOD_LOCALIZATION');
                break;
            case InstallCode::SQL_CANNOT_SAVE_PAYMENT_METHOD_LANGUAGE:
                $return = __('SQL_CANNOT_SAVE_PAYMENT_METHOD_LANGUAGE');
                break;
            case InstallCode::SQL_CANNOT_SAVE_PAYMENT_METHOD_SETTING:
                $return = __('SQL_CANNOT_SAVE_PAYMENT_METHOD_SETTING');
                break;
            case InstallCode::SQL_CANNOT_SAVE_BOX_TEMPLATE:
                $return = __('SQL_CANNOT_SAVE_BOX_TEMPLATE');
                break;
            case InstallCode::SQL_CANNOT_SAVE_TEMPLATE:
                $return = __('SQL_CANNOT_SAVE_TEMPLATE');
                break;
            case InstallCode::SQL_CANNOT_SAVE_EMAIL_TEMPLATE:
                $return = __('SQL_CANNOT_SAVE_EMAIL_TEMPLATE');
                break;
            case InstallCode::SQL_CANNOT_SAVE_LANG_VAR:
                $return = __('SQL_CANNOT_SAVE_LANG_VAR');
                break;
            case InstallCode::SQL_CANNOT_SAVE_LANG_VAR_LOCALIZATION:
                $return = __('SQL_CANNOT_SAVE_LANG_VAR_LOCALIZATION');
                break;
            case InstallCode::SQL_CANNOT_SAVE_WIDGET:
                $return = __('SQL_CANNOT_SAVE_WIDGET');
                break;
            case InstallCode::SQL_CANNOT_SAVE_PORTLET:
                $return = __('SQL_CANNOT_SAVE_PORTLET');
                break;
            case InstallCode::SQL_CANNOT_SAVE_BLUEPRINT:
                $return = __('SQL_CANNOT_SAVE_BLUEPRINT');
                break;
            case InstallCode::SQL_CANNOT_SAVE_EXPORT:
                $return = __('SQL_CANNOT_SAVE_EXPORT');
                break;
            case InstallCode::INVALID_STORE_ID:
                $return = __('INVALID_STORE_ID');
                break;
            case InstallCode::SQL_CANNOT_SAVE_VENDOR:
                $return = __('SQL_CANNOT_SAVE_VENDOR');
                break;
            case InstallCode::MISSING_CONSENT_VENDOR:
                $return = __('MISSING_CONSENT_VENDOR');
                break;
            case InstallCode::INVALID_CONSENT_VENDOR_NAME:
                $return = __('INVALID_CONSENT_VENDOR_NAME');
                break;
            case InstallCode::INVALID_CONSENT_VENDOR_PURPOSE:
                $return = __('INVALID_CONSENT_VENDOR_PURPOSE');
                break;
            case InstallCode::INVALID_CONSENT_VENDOR_LOCALIZATION:
                $return = __('INVALID_CONSENT_VENDOR_LOCALIZATION');
                break;
            case InstallCode::INVALID_CONSENT_VENDOR_LOCALIZATION_ISO:
                $return = __('INVALID_CONSENT_VENDOR_LOCALIZATION_ISO');
                break;
            case InstallCode::INVALID_CONSENT_VENDOR_DESCRIPTION:
                $return = __('INVALID_CONSENT_VENDOR_DESCRIPTION');
                break;
            case InstallCode::INVALID_CONSENT_VENDOR_PRIV_POL:
                $return = __('INVALID_CONSENT_VENDOR_PRIV_POL');
                break;
            case InstallCode::INVALID_CONSENT_VENDOR_ID:
                $return = __('INVALID_CONSENT_VENDOR_ID');
                break;
            case InstallCode::INVALID_CONSENT_VENDOR_COMPANY:
                $return = __('INVALID_CONSENT_VENDOR_COMPANY');
                break;
            default:
                $return = __('unknownError');
                break;
        }

        return \str_replace('%cPluginID%', $pluginID ?? '', $return);
    }
}
