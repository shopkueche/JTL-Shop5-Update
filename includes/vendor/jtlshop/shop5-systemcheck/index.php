<?php declare(strict_types=1);

use Systemcheck\Environment;
use Systemcheck\Platform\Hosting;

require __DIR__ . '/vendor/autoload.php';

/**
 * @param array                    $params
 * @param Smarty_Internal_Template $smarty
 * @return mixed
 */
function getResults($params, $smarty)
{
    return $smarty->assign('test', $params['test'])->fetch('testResult.tpl');
}

$templatePath = __DIR__ . '/templates';
$smarty       = new Smarty();
$systemcheck  = new Environment();
$tests        = $systemcheck->executeTestGroup('Shop5');
$platform     = new Hosting();

header('Content-Type: text/html; charset=utf-8');
$smarty->assign('passed', $systemcheck->getIsPassed())
    ->assign('tests', $tests)
    ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getResults', 'getResults')
    ->assign('platform', $platform)
    ->setCacheDir($templatePath)
    ->setCompileDir($templatePath)
    ->display('systemcheck.tpl');
