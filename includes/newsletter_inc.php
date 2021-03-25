<?php

use JTL\Alert\Alert;
use JTL\CheckBox;
use JTL\Customer\Customer;
use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Newsletter\Helper;
use JTL\Session\Frontend;
use JTL\Shop;

require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

/**
 * @param string $dbfeld
 * @param string $email
 * @return string
 * @throws Exception
 * @deprecated since 5.0.0
 */
function create_NewsletterCode($dbfeld, $email): string
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $code = md5($email . time() . random_int(123, 456));
    while (!unique_NewsletterCode($dbfeld, $code)) {
        $code = md5($email . time() . random_int(123, 456));
    }

    return $code;
}

/**
 * @param string     $dbfeld
 * @param string|int $code
 * @return bool
 * @deprecated since 5.0.0
 */
function unique_NewsletterCode($dbfeld, $code): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $res = Shop::Container()->getDB()->select('tnewsletterempfaenger', $dbfeld, $code);

    return !(isset($res->kNewsletterEmpfaenger) && $res->kNewsletterEmpfaenger > 0);
}

/**
 * @param int $customerID
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeObBereitsAbonnent(int $customerID): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Helper::customerIsSubscriber($customerID);
}

/**
 * @param int    $groupID
 * @param string $groupKeys
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeNLHistoryKundengruppe(int $groupID, string $groupKeys): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if ($groupKeys === '') {
        return false;
    }
    $groupIDs = [];
    foreach (explode(';', $groupKeys) as $id) {
        if ((int)$id > 0 || ($id !== '' && (int)$id === 0)) {
            $groupIDs[] = (int)$id;
        }
    }

    return in_array(0, $groupIDs, true) || ($groupID > 0 && in_array($groupID, $groupIDs, true));
}

/**
 * @param Customer|stdClass $customer
 * @param bool              $validate
 * @return stdClass
 * @throws Exception
 * @deprecated since 5.0.0
 */
function fuegeNewsletterEmpfaengerEin($customer, $validate = false): stdClass
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $alertHelper         = Shop::Container()->getAlertService();
    $conf                = Shop::getSettings([CONF_NEWSLETTER]);
    $checks              = new stdClass();
    $checks->nPlausi_arr = [];
    $nlCustomer          = null;
    if (!$validate || Text::filterEmailAddress($customer->cEmail) !== false) {
        $checks->nPlausi_arr = newsletterAnmeldungPlausi();
        $customerGroupID     = Frontend::getCustomerGroup()->getID();
        $checkBox            = new CheckBox();
        $checks->nPlausi_arr = array_merge(
            $checks->nPlausi_arr,
            $checkBox->validateCheckBox(CHECKBOX_ORT_NEWSLETTERANMELDUNG, $customerGroupID, $_POST, true)
        );

        $checks->cPost_arr['cAnrede']   = $customer->cAnrede;
        $checks->cPost_arr['cVorname']  = $customer->cVorname;
        $checks->cPost_arr['cNachname'] = $customer->cNachname;
        $checks->cPost_arr['cEmail']    = $customer->cEmail;
        $checks->cPost_arr['captcha']   = isset($_POST['captcha'])
            ? Text::htmlentities(Text::filterXSS($_POST['captcha']))
            : null;
        if (!$validate || count($checks->nPlausi_arr) === 0) {
            $recipient = Shop::Container()->getDB()->select(
                'tnewsletterempfaenger',
                'cEmail',
                $customer->cEmail
            );
            if (!empty($recipient->dEingetragen)) {
                $recipient->Datum = (new DateTime($recipient->dEingetragen))->format('d.m.Y H:i');
            }
            // Pruefen ob Kunde bereits eingetragen
            if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
                $nlCustomer = Shop::Container()->getDB()->select(
                    'tnewsletterempfaenger',
                    'kKunde',
                    (int)$_SESSION['Kunde']->kKunde
                );
            }
            if (!empty($recipient->cEmail) || (isset($nlCustomer->kKunde) && $nlCustomer->kKunde > 0)) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    Shop::Lang()->get('newsletterExists', 'errorMessages'),
                    'newsletterExists'
                );
            } else {
                $checkBox->triggerSpecialFunction(
                    CHECKBOX_ORT_NEWSLETTERANMELDUNG,
                    $customerGroupID,
                    true,
                    $_POST,
                    ['oKunde' => $customer]
                );
                $checkBox->checkLogging(CHECKBOX_ORT_NEWSLETTERANMELDUNG, $customerGroupID, $_POST, true);
                unset($recipient);
                $recipient                     = new stdClass();
                $recipient->kSprache           = Shop::getLanguageID();
                $recipient->kKunde             = isset($_SESSION['Kunde']->kKunde)
                    ? (int)$_SESSION['Kunde']->kKunde
                    : 0;
                $recipient->nAktiv             = isset($_SESSION['Kunde']->kKunde)
                && $_SESSION['Kunde']->kKunde > 0
                && $conf['newsletter']['newsletter_doubleopt'] === 'U' ? 1 : 0;
                $recipient->cAnrede            = $customer->cAnrede;
                $recipient->cVorname           = $customer->cVorname;
                $recipient->cNachname          = $customer->cNachname;
                $recipient->cEmail             = $customer->cEmail;
                $recipient->cOptCode           = create_NewsletterCode('cOptCode', $customer->cEmail);
                $recipient->cLoeschCode        = create_NewsletterCode('cLoeschCode', $customer->cEmail);
                $recipient->dEingetragen       = 'NOW()';
                $recipient->dLetzterNewsletter = '_DBNULL_';

                executeHook(HOOK_NEWSLETTER_PAGE_EMPFAENGEREINTRAGEN, [
                    'oNewsletterEmpfaenger' => $recipient
                ]);

                Shop::Container()->getDB()->insert('tnewsletterempfaenger', $recipient);
                $history               = new stdClass();
                $history->kSprache     = Shop::getLanguageID();
                $history->kKunde       = (int)($_SESSION['Kunde']->kKunde ?? 0);
                $history->cAnrede      = $customer->cAnrede;
                $history->cVorname     = $customer->cVorname;
                $history->cNachname    = $customer->cNachname;
                $history->cEmail       = $customer->cEmail;
                $history->cOptCode     = $recipient->cOptCode;
                $history->cLoeschCode  = $recipient->cLoeschCode;
                $history->cAktion      = 'Eingetragen';
                $history->dEingetragen = 'NOW()';
                $history->dAusgetragen = '_DBNULL_';
                $history->dOptCode     = '_DBNULL_';
                $history->cRegIp       = $customer->cRegIp;

                $historyID = Shop::Container()->getDB()->insert(
                    'tnewsletterempfaengerhistory',
                    $history
                );
                executeHook(HOOK_NEWSLETTER_PAGE_HISTORYEMPFAENGEREINTRAGEN, [
                    'oNewsletterEmpfaengerHistory' => $history
                ]);
                if (($conf['newsletter']['newsletter_doubleopt'] === 'U' && empty($_SESSION['Kunde']->kKunde))
                    || $conf['newsletter']['newsletter_doubleopt'] === 'A'
                ) {
                    $base                      = Shop::getURL() . '/newsletter.php?lang=' . $_SESSION['cISOSprache'];
                    $recipient->cLoeschURL     = $base . '&lc=' . $recipient->cLoeschCode;
                    $recipient->cFreischaltURL = $base . '&fc=' . $recipient->cOptCode;

                    $obj    = (object)['tkunde' => $_SESSION['Kunde'] ?? null, 'NewsletterEmpfaenger' => $recipient];
                    $mailer = Shop::Container()->get(Mailer::class);
                    $mail   = new Mail();
                    $mailer->send($mail->createFromTemplateID(MAILTEMPLATE_NEWSLETTERANMELDEN, $obj));
                    Shop::Container()->getDB()->update(
                        'tnewsletterempfaengerhistory',
                        'kNewsletterEmpfaengerHistory',
                        $historyID,
                        (object)['cEmailBodyHtml' => $mail->getBodyHTML()]
                    );
                    $alertHelper->addAlert(
                        Alert::TYPE_NOTE,
                        Shop::Lang()->get('newsletterAdd', 'messages'),
                        'newsletterAdd'
                    );
                    $checks = new stdClass();
                } else {
                    $alertHelper->addAlert(
                        Alert::TYPE_NOTE,
                        Shop::Lang()->get('newsletterNomailAdd', 'messages'),
                        'newsletterNomailAdd'
                    );
                }
            }
        }
    } else {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            Shop::Lang()->get('newsletterWrongemail', 'errorMessages'),
            'newsletterWrongemail'
        );
    }

    return $checks;
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function newsletterAnmeldungPlausi(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $res = [];
    if (Shop::getConfigValue(CONF_NEWSLETTER, 'newsletter_sicherheitscode') !== 'N' && !Form::validateCaptcha($_POST)) {
        $res['captcha'] = 2;
    }

    return $res;
}
