<?php

use JTL\Customer\CustomerGroup;
use JTL\Language\LanguageHelper;
use JTL\Shop;
use JTL\Sitemap\Config\DefaultConfig;
use JTL\Sitemap\Export;
use JTL\Sitemap\ItemRenderers\DefaultRenderer;
use JTL\Sitemap\SchemaRenderers\DefaultSchemaRenderer;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'sitemapexport.php';
/** @global \JTL\Backend\AdminAccount $oAccount */

@ini_set('max_execution_time', '0');

$oAccount->permission('EXPORT_SITEMAP_VIEW', true, true);

$db           = Shop::Container()->getDB();
$config       = Shop::getSettings([CONF_GLOBAL, CONF_SITEMAP]);
$exportConfig = new DefaultConfig($db, $config, Shop::getURL() . '/', Shop::getImageBaseURL());
$exporter     = new Export(
    $db,
    Shop::Container()->getLogService(),
    new DefaultRenderer(),
    new DefaultSchemaRenderer(),
    $config
);
$exporter->generate(
    [CustomerGroup::getDefaultGroupID()],
    LanguageHelper::getAllLanguages(),
    $exportConfig->getFactories()
);

if (isset($_REQUEST['update']) && (int)$_REQUEST['update'] === 1) {
    header('Location: sitemapexport.php?update=1');
} else {
    header('Cache-Control: no-cache, must-revalidate');
    header('Content-type: application/xml');
    header('Content-Disposition: attachment; filename="sitemap_index.xml"');
    readfile(PFAD_ROOT . 'sitemap.xml');
}
