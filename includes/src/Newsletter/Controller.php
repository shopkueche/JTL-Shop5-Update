<?php declare(strict_types=1);

namespace JTL\Newsletter;

use DateTime;
use JTL\Alert\Alert;
use JTL\CheckBox;
use JTL\Customer\Customer;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class Controller
 * @package JTL\Newsletter
 */
final class Controller
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var array
     */
    private $config;

    /**
     * Manager constructor.
     * @param DbInterface $db
     * @param array       $config
     */
    public function __construct(DbInterface $db, array $config)
    {
        $this->db     = $db;
        $this->config = $config;
    }

    /**
     * @return array
     */
    private function subscriptionCheck(): array
    {
        $res = [];
        if ($this->config['newsletter']['newsletter_sicherheitscode'] !== 'N' && !Form::validateCaptcha($_POST)) {
            $res['captcha'] = 2;
        }

        return $res;
    }

    /**
     * @param int $kKunde
     * @return bool
     */
    public function checkAlreadySubscribed(int $kKunde): bool
    {
        if ($kKunde <= 0) {
            return false;
        }
        $recipient = $this->db->select('tnewsletterempfaenger', 'kKunde', $kKunde);

        return isset($recipient->kKunde) && $recipient->kKunde > 0;
    }

    /**
     * @param Customer|stdClass $customer
     * @param bool              $validate
     * @return stdClass
     */
    public function addSubscriber($customer, $validate = false): stdClass
    {
        $alertHelper         = Shop::Container()->getAlertService();
        $plausi              = new stdClass();
        $plausi->nPlausi_arr = [];
        $nlCustomer          = null;
        if (!$validate || Text::filterEmailAddress($customer->cEmail) !== false) {
            $plausi->nPlausi_arr = $this->subscriptionCheck();
            $kKundengruppe       = Frontend::getCustomerGroup()->getID();
            $checkBox            = new CheckBox();
            $plausi->nPlausi_arr = \array_merge(
                $plausi->nPlausi_arr,
                $checkBox->validateCheckBox(\CHECKBOX_ORT_NEWSLETTERANMELDUNG, $kKundengruppe, $_POST, true)
            );

            $plausi->cPost_arr['cAnrede']   = $customer->cAnrede;
            $plausi->cPost_arr['cVorname']  = $customer->cVorname;
            $plausi->cPost_arr['cNachname'] = $customer->cNachname;
            $plausi->cPost_arr['cEmail']    = $customer->cEmail;
            $plausi->cPost_arr['captcha']   = isset($_POST['captcha'])
                ? Text::htmlentities(Text::filterXSS($_POST['captcha']))
                : null;
            if (!$validate || \count($plausi->nPlausi_arr) === 0) {
                $recipient = $this->db->select(
                    'tnewsletterempfaenger',
                    'cEmail',
                    $customer->cEmail
                );
                if (!empty($recipient->dEingetragen)) {
                    $recipient->Datum = (new DateTime($recipient->dEingetragen))->format('d.m.Y H:i');
                }
                // Pruefen ob Kunde bereits eingetragen
                if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
                    $nlCustomer = $this->db->select(
                        'tnewsletterempfaenger',
                        'kKunde',
                        (int)$_SESSION['Kunde']->kKunde
                    );
                }
                if ((isset($recipient->cEmail) && \mb_strlen($recipient->cEmail) > 0)
                    || (isset($nlCustomer->kKunde) && $nlCustomer->kKunde > 0)
                ) {
                    $alertHelper->addAlert(
                        Alert::TYPE_ERROR,
                        Shop::Lang()->get('newsletterExists', 'errorMessages'),
                        'newsletterExists'
                    );
                } else {
                    $checkBox->triggerSpecialFunction(
                        \CHECKBOX_ORT_NEWSLETTERANMELDUNG,
                        $kKundengruppe,
                        true,
                        $_POST,
                        ['oKunde' => $customer]
                    );
                    $checkBox->checkLogging(\CHECKBOX_ORT_NEWSLETTERANMELDUNG, $kKundengruppe, $_POST, true);

                    unset($recipient);

                    $instance                      = new Newsletter($this->db, []);
                    $recipient                     = new stdClass();
                    $recipient->kSprache           = Shop::getLanguageID();
                    $recipient->kKunde             = isset($_SESSION['Kunde']->kKunde)
                        ? (int)$_SESSION['Kunde']->kKunde
                        : 0;
                    $recipient->nAktiv             = isset($_SESSION['Kunde']->kKunde)
                        && $_SESSION['Kunde']->kKunde > 0
                        && $this->config['newsletter']['newsletter_doubleopt'] === 'U' ? 1 : 0;
                    $recipient->cAnrede            = $customer->cAnrede;
                    $recipient->cVorname           = $customer->cVorname;
                    $recipient->cNachname          = $customer->cNachname;
                    $recipient->cEmail             = $customer->cEmail;
                    $recipient->cOptCode           = $instance->createCode('cOptCode', $customer->cEmail);
                    $recipient->cLoeschCode        = $instance->createCode('cLoeschCode', $customer->cEmail);
                    $recipient->dEingetragen       = 'NOW()';
                    $recipient->dLetzterNewsletter = '_DBNULL_';

                    \executeHook(\HOOK_NEWSLETTER_PAGE_EMPFAENGEREINTRAGEN, [
                        'oNewsletterEmpfaenger' => $recipient
                    ]);

                    $this->db->insert('tnewsletterempfaenger', $recipient);
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

                    $historyID = $this->db->insert(
                        'tnewsletterempfaengerhistory',
                        $history
                    );
                    \executeHook(\HOOK_NEWSLETTER_PAGE_HISTORYEMPFAENGEREINTRAGEN, [
                        'oNewsletterEmpfaengerHistory' => $history
                    ]);
                    $this->db->delete('tnewsletterempfaengerblacklist', 'cMail', $customer->cEmail);
                    if (($this->config['newsletter']['newsletter_doubleopt'] === 'U'
                            && empty($_SESSION['Kunde']->kKunde))
                        || $this->config['newsletter']['newsletter_doubleopt'] === 'A'
                    ) {
                        $recipient->cLoeschURL     = Shop::getURL() . '/newsletter.php?lang=' .
                            $_SESSION['cISOSprache'] . '&lc=' . $recipient->cLoeschCode;
                        $recipient->cFreischaltURL = Shop::getURL() . '/newsletter.php?lang=' .
                            $_SESSION['cISOSprache'] . '&fc=' . $recipient->cOptCode;
                        $obj                       = new stdClass();
                        $obj->tkunde               = $_SESSION['Kunde'] ?? null;
                        $obj->NewsletterEmpfaenger = $recipient;

                        $mailer = Shop::Container()->get(Mailer::class);
                        $mail   = new Mail();
                        $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_NEWSLETTERANMELDEN, $obj));
                        $this->db->update(
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
                        $plausi = new stdClass();
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

        return $plausi;
    }

    /**
     * @param stdClass $recipient
     * @param string   $optCode
     * @return int
     */
    public function activateSubscriber(stdClass $recipient, string $optCode): int
    {
        \executeHook(
            \HOOK_NEWSLETTER_PAGE_EMPFAENGERFREISCHALTEN,
            ['oNewsletterEmpfaenger' => $recipient]
        );
        $res = $this->db->update(
            'tnewsletterempfaenger',
            'kNewsletterEmpfaenger',
            (int)$recipient->kNewsletterEmpfaenger,
            (object)['nAktiv' => 1]
        );
        $this->db->query(
            'UPDATE tnewsletterempfaenger, tkunde
                SET tnewsletterempfaenger.kKunde = tkunde.kKunde
                WHERE tkunde.cMail = tnewsletterempfaenger.cEmail
                    AND tnewsletterempfaenger.kKunde = 0',
            ReturnType::DEFAULT
        );
        $upd           = new stdClass();
        $upd->dOptCode = 'NOW()';
        $upd->cOptIp   = Request::getRealIP();
        $this->db->update(
            'tnewsletterempfaengerhistory',
            ['cOptCode', 'cAktion'],
            [$optCode, 'Eingetragen'],
            $upd
        );

        return $res;
    }

    /**
     * @param $dbField
     * @param $value
     * @return int
     */
    private function deleteSubscriber($dbField, $value): int
    {
        return $this->db->delete(
            'tnewsletterempfaenger',
            $dbField,
            $value
        );
    }

    /**
     * @param stdClass $recipient
     * @param string   $code
     * @return int
     */
    public function unsubscribeByCode(stdClass $recipient, string $code): int
    {
        $this->unsubscribe($recipient);

        return $this->deleteSubscriber('cLoeschCode', $code);
    }

    /**
     * @param stdClass $recipient
     * @param string   $emailAddress
     * @return int
     */
    public function unsubscribeByEmailAddress(stdClass $recipient, string $emailAddress): int
    {
        $this->unsubscribe($recipient);

        return $this->deleteSubscriber('cEmail', $emailAddress);
    }

    /**
     * @param stdClass $recipient
     * @return int
     */
    public function unsubscribe(stdClass $recipient): int
    {
        \executeHook(
            \HOOK_NEWSLETTER_PAGE_EMPFAENGERLOESCHEN,
            ['oNewsletterEmpfaenger' => $recipient]
        );
        $hist               = new stdClass();
        $hist->kSprache     = $recipient->kSprache;
        $hist->kKunde       = $recipient->kKunde;
        $hist->cAnrede      = $recipient->cAnrede;
        $hist->cVorname     = $recipient->cVorname;
        $hist->cNachname    = $recipient->cNachname;
        $hist->cEmail       = $recipient->cEmail;
        $hist->cOptCode     = $recipient->cOptCode;
        $hist->cLoeschCode  = $recipient->cLoeschCode;
        $hist->cAktion      = 'Geloescht';
        $hist->dEingetragen = $recipient->dEingetragen;
        $hist->dAusgetragen = 'NOW()';
        $hist->dOptCode     = '_DBNULL_';
        $hist->cRegIp       = Request::getRealIP();
        $this->db->insert('tnewsletterempfaengerhistory', $hist);

        \executeHook(
            \HOOK_NEWSLETTER_PAGE_HISTORYEMPFAENGEREINTRAGEN,
            ['oNewsletterEmpfaengerHistory' => $hist]
        );
        $blacklist            = new stdClass();
        $blacklist->cMail     = $recipient->cEmail;
        $blacklist->dErstellt = 'NOW()';

        return $this->db->insert('tnewsletterempfaengerblacklist', $blacklist);
    }

    /**
     * @param int $kKundengruppe
     * @param int $id
     * @return stdClass|null
     */
    public function getHistory(int $kKundengruppe, int $id): ?stdClass
    {
        $history = $this->db->queryPrepared(
            "SELECT kNewsletterHistory, nAnzahl, cBetreff, cHTMLStatic, cKundengruppeKey,
                DATE_FORMAT(dStart, '%d.%m.%Y %H:%i') AS Datum
                FROM tnewsletterhistory
                WHERE kNewsletterHistory = :hid",
            ['hid' => $id],
            ReturnType::SINGLE_OBJECT
        );

        return $history->kNewsletterHistory > 0 && $this->checkHistory($kKundengruppe, $history->cKundengruppeKey)
            ? $history
            : null;
    }

    /**
     * @param int    $groupID
     * @param string $groupKeys
     * @return bool
     */
    private function checkHistory(int $groupID, $groupKeys): bool
    {
        if (\mb_strlen($groupKeys) > 0) {
            $groupIDs = [];
            foreach (\explode(';', $groupKeys) as $id) {
                if ((int)$id > 0 || (\mb_strlen($id) > 0 && (int)$id === 0)) {
                    $groupIDs[] = (int)$id;
                }
            }
            if (\in_array(0, $groupIDs, true)) {
                return true;
            }
            if ($groupID > 0 && \in_array($groupID, $groupIDs, true)) {
                return true;
            }
        }

        return false;
    }
}
