<?php declare(strict_types=1);

namespace JTL\Catalog;

use Exception;
use JTL\Catalog\Product\Artikel;
use JTL\Checkout\Bestellung;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\EmptyResultSetException;
use JTL\Exceptions\InvalidSettingException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Shop;
use stdClass;

/**
 * Class ReviewReminder
 * @package JTL
 */
class ReviewReminder
{
    /**
     * @var array
     */
    private $settings;

    /**
     * @var array
     */
    private $customerGroups;

    /**
     * @var int
     */
    private $shippingDays;

    /**
     * @var int
     */
    private $maxDays;

    /**
     * @var string
     */
    private $sqlPartCustomerGroups = '';

    /**
     * @var string
     */
    private $sqlPartBundle1 = '';

    /**
     * @var string
     */
    private $sqlPartBundle2 = '';

    /**
     * @var array
     */
    private $orders = [];

    /**
     * ReviewReminder constructor.
     */
    public function __construct()
    {
        $this->settings       = Shop::getSettings([\CONF_BEWERTUNG])['bewertung'];
        $this->customerGroups = $this->settings['bewertungserinnerung_kundengruppen'];
    }

    /**
     * @return array
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function getRecipients(): array
    {
        if ($this->settings['bewertungserinnerung_nutzen'] !== 'Y'
            && $this->settings['bewertungserinnerung_nutzen'] !== 'B'
        ) {
            return[];
        }
        $this->checkNlBundle();

        try {
            $this->calculateSentTime();
            $this->getCustomerGroups();
            $this->fireSQL();
        } catch (EmptyResultSetException $e) {
            Shop::Container()->getLogService()->notice($e->getMessage());

            return [];
        } catch (Exception $e) {
            Shop::Container()->getLogService()->error($e->getMessage());

            return [];
        }
        $reciepients    = [];
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($this->orders as $orderData) {
            $openReviews = [];
            $order       = new Bestellung((int)$orderData->kBestellung);
            $order->fuelleBestellung(false);
            $customer         = new Customer($order->kKunde ?? 0);
            $obj              = new stdClass();
            $obj->tkunde      = $customer;
            $obj->tbestellung = $order;
            foreach ($order->Positionen as $item) {
                if ($item->kArtikel <= 0) {
                    continue;
                }
                $productVisible = (new Artikel())->fuelleArtikel(
                    (int)$item->kArtikel,
                    $defaultOptions,
                    (int)$customer->kKundengruppe
                );
                if ($productVisible !== null && $productVisible->kArtikel > 0) {
                    $res = Shop::Container()->getDB()->query(
                        'SELECT kBewertung
                            FROM tbewertung
                            WHERE kArtikel = ' . (int)$item->kArtikel . '
                                AND kKunde = ' . (int)$order->kKunde,
                        ReturnType::SINGLE_OBJECT
                    );
                    if ($res === false) {
                        $openReviews[] = $item;
                    }
                }
            }
            // there are no OPEN reviews for this order
            if (\count($openReviews) === 0) {
                continue;
            }
            $order->Positionen = $openReviews;
            // set the date of "review send" for the corresponding order
            Shop::Container()->getDB()->query(
                'UPDATE tbestellung
                    SET dBewertungErinnerung = NOW()
                    WHERE kBestellung = ' . (int)$orderData->kBestellung,
                ReturnType::AFFECTED_ROWS
            );
            $logger = Shop::Container()->getLogService();
            if ($logger->isHandling(\JTLLOG_LEVEL_DEBUG)) {
                $logger->withName('Bewertungserinnerung')->debug(
                    'Kunde und Bestellung aus baueBewertungsErinnerung (Mail versendet): <pre>' .
                    \print_r($obj, true) .
                    '</pre>',
                    [$orderData->kBestellung]
                );
            }
            $reciepients[] = $obj;
        }

        return $reciepients;
    }

    /**
     * building finally the SQL-query
     * @throws EmptyResultSetException
     */
    private function fireSQL(): void
    {
        $sqlString    = 'SELECT kBestellung
            FROM tbestellung
                JOIN tkunde ON tkunde.kKunde = tbestellung.kKunde
                ' . $this->sqlPartBundle1 . '
            WHERE dVersandDatum IS NOT NULL
                AND DATE_ADD(dVersandDatum, INTERVAL ' . $this->shippingDays . ' DAY) <= NOW()
                AND DATE_ADD(dVersandDatum, INTERVAL ' . $this->maxDays . ' DAY) > NOW()
                AND cStatus = 4
                AND (' . $this->sqlPartCustomerGroups . ')
                AND dBewertungErinnerung IS NULL
                ' . $this->sqlPartBundle2;
        $this->orders = Shop::Container()->getDB()->query($sqlString, ReturnType::ARRAY_OF_OBJECTS);
        if (\count($this->orders) === 0) {
            throw new EmptyResultSetException('Keine Bestellungen für Bewertungserinnerungen gefunden.');
        }
    }

    /**
     * check, if "bound to newsletter" consent is configured
     */
    private function checkNlBundle(): void // naming ?! --TODO--
    {
        if ($this->settings['bewertungserinnerung_nutzen'] === 'B') {
            $this->sqlPartBundle1 = 'LEFT JOIN tnewsletterempfaenger ON tkunde.kKunde = tnewsletterempfaenger.kKunde';
            $this->sqlPartBundle2 = 'AND tnewsletterempfaenger.nAktiv = 1';
        }
    }

    /**
     * define for which customer groups we will to send
     */
    private function getCustomerGroups(): void
    {
        $this->sqlPartCustomerGroups = '';
        if (\is_array($this->customerGroups) && \count($this->customerGroups) > 0) {
            foreach ($this->customerGroups as $i => $groupID) {
                if (\is_numeric($groupID)) {
                    if ($i > 0) {
                        $this->sqlPartCustomerGroups .= ' OR tkunde.kKundengruppe = ' . (int)$groupID;
                    } else {
                        $this->sqlPartCustomerGroups .= ' tkunde.kKundengruppe = ' . (int)$groupID;
                    }
                }
            }
        } else {
            // Hole standard Kundengruppe
            $defaultGroup = Shop::Container()->getDB()->select('tkundengruppe', 'cStandard', 'Y');
            if (isset($defaultGroup->kKundengruppe) && $defaultGroup->kKundengruppe > 0) {
                $this->sqlPartCustomerGroups = ' tkunde.kKundengruppe = ' . (int)$defaultGroup->kKundengruppe;
            }
        }
        if (empty($this->sqlPartCustomerGroups)) {
            throw new EmptyResultSetException('Keine Kundengruppe für Bewertungserinnerungen gefunden');
        }
    }

    /**
     * set the time difference from delivery date to send the review reminder
     */
    private function calculateSentTime(): void
    {
        $this->shippingDays = (int)$this->settings['bewertungserinnerung_versandtage'];
        if ($this->shippingDays <= 0) {
            throw new InvalidSettingException('Einstellung bewertungserinnerung_versandtage ist 0');
        }
        $this->maxDays = $this->shippingDays * 2;
        if ($this->shippingDays === 1) {
            $this->maxDays = 4;
        }
    }
}
