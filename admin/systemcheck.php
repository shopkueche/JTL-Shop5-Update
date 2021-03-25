<?php

use JTL\phpQuery\phpQuery;
use Systemcheck\Environment;
use Systemcheck\Platform\Hosting;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->redirectOnFailure();

$phpInfo = '';
if (isset($_GET['phpinfo'])) {
    if (in_array('phpinfo', explode(',', ini_get('disable_functions')), true)) {
        return;
    }
    ob_start();
    phpinfo();
    $content = ob_get_contents();
    ob_end_clean();

    $doc     = phpQuery::newDocumentHTML($content, JTL_CHARSET);
    $phpInfo = pq('body', $doc)->html();
}

$systemcheck = new Environment();
$platform    = new Hosting();

$smarty->assign('tests', $systemcheck->executeTestGroup('Shop5'))
       ->assign('platform', $platform)
       ->assign('passed', $systemcheck->getIsPassed())
       ->assign('phpinfo', $phpInfo)
       ->display('systemcheck.tpl');
