<?php

namespace JTL;

use DateTime;
use InvalidArgumentException;
use JTL\Alert\Alert;
use JTL\Cron\LegacyCron;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Attachment;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use SmartyException;
use stdClass;
use function Functional\first;
use function Functional\map;

/**
 * Class Statusmail
 * @package JTL
 */
class Statusmail
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var string
     */
    private $dateStart;

    /**
     * @var string
     */
    private $dateEnd;

    /**
     * Statusmail constructor.
     *
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return bool
     */
    public function updateConfig(): bool
    {
        $active = Request::postInt('nAktiv') === 1;
        if (!$active
            || (Text::filterEmailAddress($_POST['cEmail']) !== false
                && \is_array($_POST['cIntervall_arr'])
                && \count($_POST['cIntervall_arr']) > 0
                && \is_array($_POST['cInhalt_arr'])
                && \count($_POST['cInhalt_arr']) > 0)
        ) {
            $this->db->query('TRUNCATE TABLE tstatusemail', ReturnType::DEFAULT);
            $this->db->query(
                "DELETE tcron, tjobqueue
                    FROM tcron
                    LEFT JOIN tjobqueue
                        ON tjobqueue.cronID = tcron.cronID
                    WHERE tcron.jobType = 'statusemail'",
                ReturnType::DEFAULT
            );
            foreach ($_POST['cIntervall_arr'] as $interval) {
                $interval              = (int)$interval;
                $statusMail            = new stdClass();
                $statusMail->cEmail    = $_POST['cEmail'];
                $statusMail->nInterval = $interval;
                $statusMail->cInhalt   = Text::createSSK($_POST['cInhalt_arr']);
                $statusMail->nAktiv    = Request::postInt('nAktiv');
                $statusMail->dLastSent = 'NOW()';

                $id = $this->db->insert('tstatusemail', $statusMail);
                if ($active) {
                    $this->createCronJob($id, $interval * 24);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param int $id
     * @param int $frequency
     * @return bool
     */
    private function createCronJob(int $id, int $frequency): bool
    {
        $types     = [
            24  => ['name' => __('intervalDay'), 'date' => 'tomorrow'],
            168 => ['name' => __('intervalWeek'), 'date' => 'next week'],
            720 => ['name' => __('intervalMonth'), 'date' => 'first day of next month']
        ];
        $startDate = \date('Y-m-d', \strtotime($types[$frequency]['date']));
        $d         = new DateTime($startDate);
        $d->setTime(0, 0);
        Shop::Container()->getAlertService()->addAlert(
            Alert::TYPE_INFO,
            \sprintf(__('nextStatusMail'), $types[$frequency]['name'], $d->format('d.m.Y')),
            'nextStatusMail' . $frequency
        );
        $cron = new LegacyCron(
            0,
            $id,
            $frequency,
            'statusemail',
            'statusemail',
            'tstatusemail',
            'id',
            $d->format('Y-m-d H:i:s'),
            $d->format('H:i:s')
        );

        return $cron->speicherInDB() !== false;
    }

    /**
     * @return stdClass
     */
    public function loadConfig(): stdClass
    {
        $data = $this->db->query(
            'SELECT * FROM tstatusemail',
            ReturnType::ARRAY_OF_OBJECTS
        );

        $first                        = first($data);
        $conf                         = new stdClass();
        $conf->cIntervallMoeglich_arr = $this->getPossibleIntervals();
        $conf->cInhaltMoeglich_arr    = $this->getPossibleContentTypes();
        $conf->nIntervall_arr         = map($data, static function ($e) {
            return (int)$e->nInterval;
        });
        $conf->nInhalt_arr            = Text::parseSSKint($first->cInhalt ?? '');
        $conf->cEmail                 = $first->cEmail ?? '';
        $conf->nAktiv                 = (int)($first->nAktiv ?? 0);

        return $conf;
    }

    /**
     * @return array
     */
    private function getPossibleIntervals(): array
    {
        return [
            __('intervalDay')   => 1,
            __('intervalWeek')  => 7,
            __('intervalMonth') => 30
        ];
    }

    /**
     * @return array
     */
    private function getPossibleContentTypes(): array
    {
        return [
            __('contentTypeCountItemCustomerGroup')         => 1,
            __('contentTypeCountNewCustomer')               => 2,
            __('contentTypeCountNewCustomerOrdered')        => 3,
            __('contentTypeCountOrders')                    => 4,
            __('contentTypeCountOrdersNewCustomers')        => 5,
            __('contentTypeCountPayments')                  => 23,
            __('contentTypeCountOrdersSent')                => 24,
            __('contentTypeCountVisitors')                  => 6,
            __('contentTypeCountVisitorsSearchEngine')      => 7,
            __('contentTypeCountRatings')                   => 8,
            __('contentTypeCountRatingsLocked')             => 9,
            __('contentTypeCountRatingDepositPayed')        => 10,
            __('contentTypeCountCustomerRecruited')         => 13,
            __('contentTypeCountCustomerRecruitedOrdered')  => 14,
            __('contentTypeCountSentWishlists')             => 15,
            __('contentTypeCountNewsComments')              => 17,
            __('contentTypeCountNewsCommentsLocked')        => 18,
            __('contentTypeCountProductQuestion')           => 19,
            __('contentTypeCountAvailabilityNotifications') => 20,
            __('contentTypeCountProductCompare')            => 21,
            __('contentTypeCountCouponsUsed')               => 22,
            __('contentTypeLastErrorLog')                   => 25,
            __('contentTypeLastNoteLog')                    => 26,
            __('contentTypeLastDebugLog')                   => 27,
            __('contentTypeCountNewsletterOptOut')          => 28,
            __('contentTypeCountNewsletterOptIn')           => 29,
        ];
    }

    /**
     * @return array
     */
    private function getProductCountPerCustomerGroup(): array
    {
        $products       = [];
        $customerGroups = $this->db->query(
            'SELECT kKundengruppe, cName FROM tkundengruppe',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($customerGroups as $customerGroup) {
            $productData            = $this->db->queryPrepared(
                'SELECT COUNT(*) AS cnt
                    FROM tartikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = :cgid
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL',
                ['cgid' => (int)$customerGroup->kKundengruppe],
                ReturnType::SINGLE_OBJECT
            );
            $product                = new stdClass();
            $product->nAnzahl       = (int)$productData->cnt;
            $product->kKundengruppe = (int)$customerGroup->kKundengruppe;
            $product->cName         = $customerGroup->cName;

            $products[] = $product;
        }

        return $products;
    }

    /**
     * @return int
     */
    private function getNewCustomersCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tkunde
                WHERE dErstellt >= :from
                    AND dErstellt < :to
                    AND nRegistriert = 1',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return int
     */
    private function getNewCustomerSalesCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(DISTINCT(tkunde.kKunde)) AS cnt
                FROM tkunde
                JOIN tbestellung
                    ON tbestellung.kKunde = tkunde.kKunde
                WHERE tbestellung.dErstellt >= :from
                    AND tbestellung.dErstellt < :to
                    AND tkunde.dErstellt >= :from
                    AND tkunde.dErstellt < :to
                    AND tkunde.nRegistriert = 1',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return int
     */
    private function getOrderCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tbestellung
                WHERE dErstellt >= :from
                    AND dErstellt < :to',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return int
     */
    private function getOrderCountForNewCustomers(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tbestellung
                JOIN tkunde
                    ON tkunde.kKunde = tbestellung.kKunde
                WHERE tbestellung.dErstellt >= :from
                    AND tbestellung.dErstellt < :to
                    AND tkunde.nRegistriert = 1',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return int
     */
    private function getIncomingPaymentsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tbestellung
                WHERE tbestellung.dErstellt >= :from
                    AND tbestellung.dErstellt < :to
                    AND tbestellung.dBezahltDatum IS NOT NULL',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return int
     */
    private function getShippedOrdersCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tbestellung
                WHERE tbestellung.dErstellt >= :from
                    AND tbestellung.dErstellt < :to
                    AND tbestellung.dVersandDatum IS NOT NULL',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return int
     */
    private function getVisitorCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tbesucherarchiv
                WHERE dZeit >= :from
                    AND dZeit < :to
                    AND kBesucherBot = 0',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return int
     */
    private function getBotVisitCount(): int
    {
        return (int)$this->db->queryPrepared(
            "SELECT COUNT(*) AS cnt
                FROM tbesucherarchiv
                WHERE dZeit >= :from
                    AND dZeit < :to
                    AND cReferer != ''",
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return int
     */
    private function getRatingsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tbewertung
                WHERE dDatum >= :from
                    AND dDatum < :to
                    AND nAktiv = 1',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return int
     */
    private function getNonApprovedRatingsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tbewertung
                WHERE dDatum >= :from
                    AND dDatum < :to
                    AND nAktiv = 0',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return stdClass
     */
    private function getRatingCreditsCount(): stdClass
    {
        $rating = $this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt, SUM(fGuthabenBonus) AS fSummeGuthaben
                FROM tbewertungguthabenbonus
                WHERE dDatum >= :from
                    AND dDatum < :to',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        );

        $res                 = new stdClass();
        $res->nAnzahl        = (int)$rating->cnt;
        $res->fSummeGuthaben = $rating->fSummeGuthaben;

        return $res;
    }

    /**
     * @return int
     */
    private function getNewsletterOptOutCount():int
    {
        $res = $this->db->queryPrepared(
            'SELECT COUNT(*) AS total
                FROM toptinhistory
                WHERE dDeActivated >= :from
                AND dDeActivated < :to
                AND kOptinClass = :class
                ',
            [
                'class' => 'JTL\\Optin\\OptinNewsletter',
                'from'  => $this->dateStart,
                'to'    => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        );

        return \is_object($res) ? $res->total : 0;
    }

    /**
     * @return int
     */
    private function getNewsletterOptInCount():int
    {
        $res = $this->db->queryPrepared(
            'SELECT COUNT(*) AS total
                FROM toptin
                WHERE dActivated >= :from
                AND dActivated < :to
                AND kOptinClass = :class
                ',
            [
                'class' => 'JTL\\Optin\\OptinNewsletter',
                'from'  => $this->dateStart,
                'to'    => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        );

        return \is_object($res) ? $res->total : 0;
    }

    /**
     * @return int
     */
    private function getSentWishlistCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                    FROM twunschlisteversand
                    WHERE dZeit >= :from
                        AND dZeit < :to',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return int
     */
    private function getNewsCommentsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tnewskommentar
                WHERE dErstellt >= :from
                    AND dErstellt < :to
                    AND nAktiv = 1',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return int
     */
    private function getNonApprovedCommentsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tnewskommentar
                WHERE dErstellt >= :from
                    AND dErstellt < :to
                    AND nAktiv = 0',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return int
     */
    private function getAvailabilityNotificationsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tverfuegbarkeitsbenachrichtigung
                WHERE dErstellt >= :from
                    AND dErstellt < :to',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return int
     */
    private function getProductInquriesCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tproduktanfragehistory
                WHERE dErstellt >= :from
                    AND dErstellt < :to',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return int
     */
    private function getComparisonsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tvergleichsliste
                WHERE dDate >= :from
                    AND dDate < :to',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return int
     */
    private function getCouponUsageCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tkuponkunde
                WHERE dErstellt >= :from
                    AND dErstellt < :to',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @param array $logLevels
     * @return array|int|object
     */
    public function getLogEntries(array $logLevels): array
    {
        return map(
            $this->db->queryPrepared(
                'SELECT *
                    FROM tjtllog
                    WHERE dErstellt >= :from
                        AND dErstellt < :to
                        AND nLevel IN (' . \implode(',', \array_map('\intval', $logLevels)) . ')
                    ORDER BY dErstellt DESC',
                [
                    'from' => $this->dateStart,
                    'to'   => $this->dateEnd
                ],
                ReturnType::ARRAY_OF_OBJECTS
            ),
            static function ($e) {
                $e->kLog   = (int)$e->kLog;
                $e->nLevel = (int)$e->nLevel;
                $e->kKey   = (int)$e->kKey;

                return $e;
            }
        );
    }

    /**
     * @param object $statusMail
     * @param string $dateStart
     * @param string $dateEnd
     * @return object|bool
     * @throws SmartyException
     */
    public function generate($statusMail, $dateStart, $dateEnd)
    {
        $this->dateStart = $dateStart;
        $this->dateEnd   = $dateEnd;
        if (!\is_array($statusMail->nInhalt_arr)
            || empty($dateStart)
            || empty($dateEnd)
            || empty($statusMail->nAktiv)
            || \count($statusMail->nInhalt_arr) === 0
        ) {
            return false;
        }

        $mailType = $this->db->select(
            'temailvorlage',
            'cModulId',
            \MAILTEMPLATE_STATUSEMAIL,
            null,
            null,
            null,
            null,
            false,
            'cMailTyp'
        )->cMailTyp;

        $mail                                           = new stdClass();
        $mail->mail                                     = new stdClass();
        $mail->oAnzahlArtikelProKundengruppe            = -1;
        $mail->nAnzahlNeukunden                         = -1;
        $mail->nAnzahlNeukundenGekauft                  = -1;
        $mail->nAnzahlBestellungen                      = -1;
        $mail->nAnzahlBestellungenNeukunden             = -1;
        $mail->nAnzahlBesucher                          = -1;
        $mail->nAnzahlBesucherSuchmaschine              = -1;
        $mail->nAnzahlBewertungen                       = -1;
        $mail->nAnzahlBewertungenNichtFreigeschaltet    = -1;
        $mail->oAnzahlGezahltesGuthaben                 = -1;
        $mail->nAnzahlTags                              = -1;
        $mail->nAnzahlGeworbenerKunden                  = -1;
        $mail->nAnzahlErfolgreichGeworbenerKunden       = -1;
        $mail->nAnzahlVersendeterWunschlisten           = -1;
        $mail->nAnzahlNewskommentare                    = -1;
        $mail->nAnzahlNewskommentareNichtFreigeschaltet = -1;
        $mail->nAnzahlProduktanfrageArtikel             = -1;
        $mail->nAnzahlProduktanfrageVerfuegbarkeit      = -1;
        $mail->nAnzahlVergleiche                        = -1;
        $mail->nAnzahlGenutzteKupons                    = -1;
        $mail->nAnzahlZahlungseingaengeVonBestellungen  = -1;
        $mail->nAnzahlVersendeterBestellungen           = -1;
        $mail->nAnzahlNewsletterAbmeldungen             = -1;
        $mail->nAnzahlNewsletterAnmeldungen             = -1;
        $mail->dVon                                     = $dateStart;
        $mail->dBis                                     = $dateEnd;
        $mail->oLogEntry_arr                            = [];
        $logLevels                                      = [];

        foreach ($statusMail->nInhalt_arr as $nInhalt) {
            switch ($nInhalt) {
                case 1:
                    $mail->oAnzahlArtikelProKundengruppe = $this->getProductCountPerCustomerGroup();
                    break;
                case 2:
                    $mail->nAnzahlNeukunden = $this->getNewCustomersCount();
                    break;
                case 3:
                    $mail->nAnzahlNeukundenGekauft = $this->getNewCustomerSalesCount();
                    break;
                case 4:
                    $mail->nAnzahlBestellungen = $this->getOrderCount();
                    break;
                case 5:
                    $mail->nAnzahlBestellungenNeukunden = $this->getOrderCountForNewCustomers();
                    break;
                case 6:
                    $mail->nAnzahlBesucher = $this->getVisitorCount();
                    break;
                case 7:
                    $mail->nAnzahlBesucherSuchmaschine = $this->getBotVisitCount();
                    break;
                case 8:
                    $mail->nAnzahlBewertungen = $this->getRatingsCount();
                    break;
                case 9:
                    $mail->nAnzahlBewertungenNichtFreigeschaltet = $this->getNonApprovedRatingsCount();
                    break;
                case 10:
                    $mail->oAnzahlGezahltesGuthaben = $this->getRatingCreditsCount();
                    break;
                case 15:
                    $mail->nAnzahlVersendeterWunschlisten = $this->getSentWishlistCount();
                    break;
                case 17:
                    $mail->nAnzahlNewskommentare = $this->getNewsCommentsCount();
                    break;
                case 18:
                    $mail->nAnzahlNewskommentareNichtFreigeschaltet = $this->getNonApprovedCommentsCount();
                    break;
                case 19:
                    $mail->nAnzahlProduktanfrageArtikel = $this->getProductInquriesCount();
                    break;
                case 20:
                    $mail->nAnzahlProduktanfrageVerfuegbarkeit = $this->getAvailabilityNotificationsCount();
                    break;
                case 21:
                    $mail->nAnzahlVergleiche = $this->getComparisonsCount();
                    break;
                case 22:
                    $mail->nAnzahlGenutzteKupons = $this->getCouponUsageCount();
                    break;
                case 23:
                    $mail->nAnzahlZahlungseingaengeVonBestellungen = $this->getIncomingPaymentsCount();
                    break;
                case 24:
                    $mail->nAnzahlVersendeterBestellungen = $this->getShippedOrdersCount();
                    break;
                case 25:
                    $logLevels[] = \JTLLOG_LEVEL_ERROR;
                    $logLevels[] = \JTLLOG_LEVEL_CRITICAL;
                    $logLevels[] = \JTLLOG_LEVEL_ALERT;
                    $logLevels[] = \JTLLOG_LEVEL_EMERGENCY;
                    break;
                case 26:
                    $logLevels[] = \JTLLOG_LEVEL_NOTICE;
                    break;
                case 27:
                    $logLevels[] = \JTLLOG_LEVEL_DEBUG;
                    break;
                case 28:
                    $mail->nAnzahlNewsletterAbmeldungen = $this->getNewsletterOptOutCount();
                    break;
                case 29:
                    $mail->nAnzahlNewsletterAnmeldungen = $this->getNewsletterOptInCount();
                    break;
            }
        }

        if (\count($logLevels) > 0) {
            $mail->oLogEntry_arr = $this->getLogEntries($logLevels);
            $logfile             = \tempnam(\sys_get_temp_dir(), 'jtl');
            $info                = \pathinfo($logfile);
            $fileStream          = \fopen($logfile, 'wb');
            $attachment          = new Attachment();
            $attachment->setFileName($info['filename']);
            $attachment->setDir($info['dirname'] . '/');
            $smarty = Shop::Smarty()->assign('oMailObjekt', $mail);
            if ($mailType === 'text') {
                \fwrite(
                    $fileStream,
                    $smarty->fetch(\PFAD_ROOT . \PFAD_EMAILVORLAGEN . 'ger/email_bericht_plain_log.tpl')
                );
                $attachment->setName('jtl-log-digest.txt');
            } else {
                \fwrite(
                    $fileStream,
                    $smarty->fetch(\PFAD_ROOT . \PFAD_EMAILVORLAGEN . 'ger/email_bericht_html_log.tpl')
                );
                $attachment->setName('jtl-log-digest.html');
            }

            \fclose($fileStream);
            $mail->mail->attachment = $attachment;
        }
        $mail->mail->toEmail = $statusMail->cEmail;

        return $mail;
    }

    /**
     * @return bool
     * @throws SmartyException
     */
    public function sendAllActiveStatusMails(): bool
    {
        $ok = true;
        foreach ($this->db->selectAll('tstatusemail', 'nAktiv', 1) as $statusMail) {
            $ok = $ok && $this->send($statusMail);
        }

        return $ok;
    }

    /**
     * @param stdClass $statusMail
     * @return bool
     * @throws SmartyException
     */
    public function send($statusMail): bool
    {
        $sent                    = false;
        $statusMail->nInhalt_arr = Text::parseSSKint($statusMail->cInhalt);
        $intervall               = (int)$statusMail->nInterval;
        switch ($intervall) {
            case 1:
                $startDate   = \date('Y-m-d', \strtotime('yesterday'));
                $endDate     = \date('Y-m-d', \strtotime('today'));
                $intervalLoc = 'Tägliche';
                break;
            case 7:
                $startDate   = \date('Y-m-d', \strtotime('last week monday'));
                $endDate     = \date('Y-m-d', \strtotime('last week sunday'));
                $intervalLoc = 'Wöchentliche';
                break;
            case 30:
                $startDate   = \date('Y-m-d', \strtotime('first day of previous month'));
                $endDate     = \date('Y-m-d', \strtotime('last day of previous month'));
                $intervalLoc = 'Monatliche';
                break;
            default:
                throw new InvalidArgumentException('Invalid interval type: ' . $intervall);
        }
        $data = $this->generate($statusMail, $startDate, $endDate);
        if ($data) {
            $data->cIntervall = $intervalLoc . ' Status-Email';

            $mailer = Shop::Container()->get(Mailer::class);
            $mail   = new Mail();
            $mail   = $mail->createFromTemplateID(\MAILTEMPLATE_STATUSEMAIL, $data);
            $mail->setToMail($statusMail->cEmail);
            if (!empty($data->mail->attachment)) {
                $mail->setAttachments([$data->mail->attachment]);
            }
            $sent = $mailer->send($mail);
            foreach ($mail->getAttachments() as $attachment) {
                \unlink($attachment->getFullPath());
            }
        }

        return $sent;
    }
}
