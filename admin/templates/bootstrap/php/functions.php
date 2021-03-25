<?php

use AdminTemplate\Plugins;
use JTL\Helpers\Text;
use scc\DefaultComponentRegistrator;
use sccbs3\Bs3sccRenderer;

require_once __DIR__ . '/Plugins.php';

/** @global \JTL\Smarty\JTLSmarty $smarty */
$plugins = new Plugins();
$scc     = new DefaultComponentRegistrator(new Bs3sccRenderer($smarty));
$scc->registerComponents();

$smarty->registerPlugin(
            Smarty::PLUGIN_FUNCTION,
            'getCurrencyConversionSmarty',
            [$plugins, 'getCurrencyConversionSmarty']
       )
       ->registerPlugin(
            Smarty::PLUGIN_FUNCTION,
            'getCurrencyConversionTooltipButton',
            [$plugins, 'getCurrencyConversionTooltipButton']
       )
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getCurrentPage', [$plugins, 'getCurrentPage'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'SmartyConvertDate', [$plugins, 'SmartyConvertDate'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getHelpDesc', [$plugins, 'getHelpDesc'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getExtensionCategory', [$plugins, 'getExtensionCategory'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'formatVersion', [$plugins, 'formatVersion'])
       ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'formatByteSize', [Text::class, 'formatSize'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getAvatar', [$plugins, 'getAvatar'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getRevisions', [$plugins, 'getRevisions'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'captchaMarkup', [$plugins, 'captchaMarkup'])
       ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'permission', [$plugins, 'permission']);
