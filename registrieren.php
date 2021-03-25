<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Session\Frontend;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';

$linkHelper = Shop::Container()->getLinkService();
if (Request::verifyGPCDataInt('editRechnungsadresse') === 0 && Frontend::getCustomer()->getID() > 0) {
    header('Location: ' . $linkHelper->getStaticRoute('jtl.php'), true, 301);
}

require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'registrieren_inc.php';

Shop::setPageType(PAGE_REGISTRIERUNG);
$conf  = Shop::getSettings([
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KUNDEN,
    CONF_KUNDENFELD,
    CONF_NEWSLETTER
]);
$kLink = $linkHelper->getSpecialPageID(LINKTYP_REGISTRIEREN);
$link  = $linkHelper->getPageLink($kLink);
$step  = 'formular';
$titel = Shop::Lang()->get('newAccount', 'login');
$edit  = Request::getInt('editRechnungsadresse');
if (isset($_POST['editRechnungsadresse'])) {
    $edit = (int)$_POST['editRechnungsadresse'];
}
if (Form::validateToken() && Request::postInt('form') === 1) {
    kundeSpeichern($_POST);
}
if (Request::getInt('editRechnungsadresse') === 1) {
    gibKunde();
}
if ($step === 'formular') {
    gibFormularDaten(Request::verifyGPCDataInt('checkout'));
}
Shop::Smarty()->assign('editRechnungsadresse', $edit)
    ->assign('Ueberschrift', $titel)
    ->assign('Link', $link)
    ->assign('step', $step)
    ->assign('nAnzeigeOrt', CHECKBOX_ORT_REGISTRIERUNG)
    ->assign('code_registrieren', false)
    ->assign('unregForm', 0);

$cCanonicalURL = $linkHelper->getStaticRoute('registrieren.php');

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
if (isset($conf['kunden']['kundenregistrierung_pruefen_zeit'])
    && $conf['kunden']['kundenregistrierung_pruefen_zeit'] === 'Y'
) {
    $_SESSION['dRegZeit'] = time();
}

if (Request::verifyGPCDataInt('accountDeleted') === 1) {
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_SUCCESS,
        Shop::Lang()->get('accountDeleted', 'messages'),
        'accountDeleted'
    );
}

executeHook(HOOK_REGISTRIEREN_PAGE);

Shop::Smarty()->display('register/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
