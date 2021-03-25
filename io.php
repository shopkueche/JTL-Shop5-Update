<?php declare(strict_types=1);

use JTL\Helpers\Request;
use JTL\IO\IO;
use JTL\IO\IOMethods;
use JTL\Shop;

ob_start();

require_once __DIR__ . '/includes/globalinclude.php';

$io        = IO::getInstance();
$ioMethods = new IOMethods($io);
$ioMethods->registerMethods();
Shop::Smarty()->setCaching(false)
    ->assign('BILD_KEIN_KATEGORIEBILD_VORHANDEN', BILD_KEIN_KATEGORIEBILD_VORHANDEN)
    ->assign('BILD_KEIN_ARTIKELBILD_VORHANDEN', BILD_KEIN_ARTIKELBILD_VORHANDEN)
    ->assign('BILD_KEIN_HERSTELLERBILD_VORHANDEN', BILD_KEIN_HERSTELLERBILD_VORHANDEN)
    ->assign('BILD_KEIN_MERKMALBILD_VORHANDEN', BILD_KEIN_MERKMALBILD_VORHANDEN)
    ->assign('BILD_KEIN_MERKMALWERTBILD_VORHANDEN', BILD_KEIN_MERKMALWERTBILD_VORHANDEN)
    ->assign('nSeitenTyp', PAGE_IO)
    ->assign('imageBaseURL', Shop::getImageBaseURL())
    ->assign('ShopURL', Shop::getURL());
Shop::setPageType(PAGE_IO);

if (!isset($_REQUEST['io'])) {
    header(Request::makeHTTPHeader(400));
    exit;
}

$request = $_REQUEST['io'];

executeHook(HOOK_IO_HANDLE_REQUEST, [
    'io'      => &$io,
    'request' => &$request
]);

try {
    $data = $io->handleRequest($request);
} catch (Exception $e) {
    $data = $e->getMessage();
    header(Request::makeHTTPHeader(500));
}

ob_end_clean();

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-type: application/json');

echo $data === null ? '{}' : json_encode($data);
