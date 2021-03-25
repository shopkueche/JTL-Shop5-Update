<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Checkout\Bestellung;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Session\Frontend;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';

Shop::setPageType(PAGE_BESTELLSTATUS);
$smarty     = Shop::Smarty();
$linkHelper = Shop::Container()->getLinkService();
$uid        = Request::verifyGPDataString('uid');
if (!empty($uid)) {
    $conf   = Shop::getSettings([CONF_KUNDEN]);
    $db     = Shop::Container()->getDB();
    $status = $db->queryPrepared(
        'SELECT kBestellung, failedAttempts
            FROM tbestellstatus 
            WHERE dDatum >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                AND cUID = :uid
                AND (failedAttempts <= :maxAttempts OR 1 = :loggedIn)',
        [
            'uid'         => $uid,
            'maxAttempts' => (int)$conf['kunden']['kundenlogin_max_loginversuche'],
            'loggedIn'    => Frontend::getCustomer()->isLoggedIn() ? 1 : 0,
        ],
        ReturnType::SINGLE_OBJECT
    );
    if (empty($status->kBestellung)) {
        Shop::Container()->getAlertService()->addAlert(
            Alert::TYPE_DANGER,
            Shop::Lang()->get('statusOrderNotFound', 'errorMessages'),
            'statusOrderNotFound',
            ['saveInSession' => true]
        );
        header('Location: ' . $linkHelper->getStaticRoute('jtl.php'), true, 303);
        exit;
    }
    $order    = new Bestellung((int)$status->kBestellung, true);
    $plzValid = false;

    if (Form::validateToken()) {
        if (isset($_POST['plz']) && $order->oRechnungsadresse->cPLZ === Text::filterXSS($_POST['plz'])) {
            $plzValid = true;
        } elseif (!empty($_POST['plz'])) {
            $db->update('tbestellstatus', 'cUID', $uid, (object)[
                'failedAttempts' => (int)$status->failedAttempts + 1,
            ]);
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_DANGER,
                Shop::Lang()->get('incorrectLogin'),
                'statusOrderincorrectLogin'
            );
        }
    }

    $smarty->assign('Bestellung', $order)
           ->assign('uid', Text::filterXSS($uid))
           ->assign('showLoginPanel', Frontend::getCustomer()->isLoggedIn());

    if ($plzValid || Frontend::getCustomer()->isLoggedIn()) {
        $db->update('tbestellstatus', 'cUID', $uid, (object)[
            'failedAttempts' => 0,
        ]);
        $smarty->assign('Kunde', new Customer($order->kKunde))
               ->assign('Lieferadresse', $order->Lieferadresse)
               ->assign('billingAddress', $order->oRechnungsadresse);
    }
} else {
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_DANGER,
        Shop::Lang()->get('uidNotFound', 'errorMessages'),
        'wrongUID',
        ['saveInSession' => true]
    );
    header('Location: ' . $linkHelper->getStaticRoute('jtl.php'), true, 303);
    exit;
}

$step = 'bestellung';
$smarty->assign('step', $step)
       ->assign('BESTELLUNG_STATUS_BEZAHLT', BESTELLUNG_STATUS_BEZAHLT)
       ->assign('BESTELLUNG_STATUS_VERSANDT', BESTELLUNG_STATUS_VERSANDT)
       ->assign('BESTELLUNG_STATUS_OFFEN', BESTELLUNG_STATUS_OFFEN);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

$smarty->display('account/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
