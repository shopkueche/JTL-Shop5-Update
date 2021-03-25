<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Customer\Customer;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Newsletter\Controller;
use JTL\Newsletter\Helper;
use JTL\Optin\Optin;
use JTL\Optin\OptinNewsletter;
use JTL\Optin\OptinRefData;
use JTL\Session\Frontend;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'newsletter_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'seite_inc.php';

Shop::setPageType(PAGE_NEWSLETTER);
$db          = Shop::Container()->getDB();
$smarty      = Shop::Smarty();
$alertHelper = Shop::Container()->getAlertService();
$linkHelper  = Shop::Container()->getLinkService();
$kLink       = $linkHelper->getSpecialPageID(LINKTYP_NEWSLETTER, false);
$valid       = Form::validateToken();
$controller  = new Controller($db, Shop::getSettings([CONF_NEWSLETTER]));
if ($kLink === false) {
    $bFileNotFound       = true;
    Shop::$kLink         = $linkHelper->getSpecialPageID(LINKTYP_404);
    Shop::$bFileNotFound = true;
    Shop::$is404         = true;

    return;
}
$link          = $linkHelper->getPageLink($kLink);
$cCanonicalURL = '';
$option        = 'eintragen';
if ($valid && Request::verifyGPCDataInt('abonnieren') > 0) {
    $post = Text::filterXSS($_POST);
    if (Text::filterEmailAddress($post['cEmail']) !== false) {
        $refData = (new OptinRefData())
            ->setSalutation($post['cAnrede'] ?? '')
            ->setFirstName($post['cVorname'] ?? '')
            ->setLastName($post['cNachname'] ?? '')
            ->setEmail($post['cEmail'] ?? '')
            ->setLanguageID(Shop::getLanguageID())
            ->setRealIP(Request::getRealIP());
        try {
            (new Optin(OptinNewsletter::class))
                ->getOptinInstance()
                ->createOptin($refData)
                ->sendActivationMail();
        } catch (Exception $e) {
            Shop::Container()->getLogService()->error($e->getMessage());
        }
    } else {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            Shop::Lang()->get('newsletterWrongemail', 'errorMessages'),
            'newsletterWrongemail'
        );
    }
    $smarty->assign('cPost_arr', $post);
} elseif ($valid && Request::verifyGPCDataInt('abmelden') === 1) {
    if (Text::filterEmailAddress($_POST['cEmail']) !== false) {
        try {
            (new Optin(OptinNewsletter::class))
                ->setEmail(Text::htmlentities($_POST['cEmail']))
                ->setAction(Optin::DELETE_CODE)
                ->handleOptin();
        } catch (Exception $e) {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                Shop::Lang()->get('newsletterNoexists', 'errorMessages'),
                'newsletterNoexists'
            );
        }
    } else {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            Shop::Lang()->get('newsletterWrongemail', 'errorMessages'),
            'newsletterWrongemail'
        );
        $smarty->assign('oFehlendeAngaben', (object)['cUnsubscribeEmail' => 1]);
    }
} elseif (Request::getInt('show') > 0) {
    $option = 'anzeigen';
    if ($history = $controller->getHistory(Frontend::getCustomer()->getGroupID(), Request::getInt('show'))) {
        $smarty->assign('oNewsletterHistory', $history);
    }
}
if (($customerID = Frontend::getCustomer()->getID()) > 0) {
    $customer = new Customer($customerID);
    $smarty->assign('bBereitsAbonnent', Helper::customerIsSubscriber($customer->kKunde))
        ->assign('oKunde', $customer);
}
$cCanonicalURL = $linkHelper->getStaticRoute('newsletter.php');

$smarty->assign('cOption', $option)
    ->assign('Link', $link)
    ->assign('nAnzeigeOrt', CHECKBOX_ORT_NEWSLETTERANMELDUNG)
    ->assign('code_newsletter', false);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_NEWSLETTER_PAGE);
$smarty->display('newsletter/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
