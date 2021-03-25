<?php

namespace JTL\Plugin\Payment;

use InvalidArgumentException;
use JTL\Cart\Cart;
use JTL\Checkout\Bestellung;
use JTL\Checkout\ZahlungsLog;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class Method
 * @package JTL\Plugin\Payment
 */
class Method implements MethodInterface
{
    /**
     * i.e. za_mbqc_visa_jtl
     *
     * @var string
     */
    public $moduleID;

    /**
     * i.e. mbqc_visa for za_mbqc_visa_jtl
     *
     * @var string
     */
    public $moduleAbbr;

    /**
     * Internal Name w/o whitespace, e.g. 'MoneybookersQC'.
     *
     * @var string
     */
    public $name;

    /**
     * E.g. 'Moneybookers Quick Connect'.
     *
     * @var string
     */
    public $caption;

    /**
     * @var bool
     */
    public $duringCheckout;

    /**
     * @var string
     */
    public $cModulId;

    /**
     * @var bool
     * @deprecated since 5.0.0 - use self::canPayAgain
     */
    public $bPayAgain;

    /**
     * @var array
     */
    public $paymentConfig;

    /**
     * @var int|null
     */
    public $kZahlungsart;

    /**
     * @param string $moduleID
     * @param int    $nAgainCheckout
     */
    public function __construct(string $moduleID, int $nAgainCheckout = 0)
    {
        $this->moduleID = $moduleID;
        // extract: za_mbqc_visa_jtl => myqc_visa
        $pattern = '&za_(.*)_jtl&is';
        \preg_match($pattern, $moduleID, $subpattern);
        $this->moduleAbbr = $subpattern[1] ?? null;

        $this->loadSettings();
        $this->init($nAgainCheckout);
    }

    /**
     * @inheritDoc
     */
    public function init(int $nAgainCheckout = 0)
    {
        $this->name           = '';
        $result               = Shop::Container()->getDB()->select('tzahlungsart', 'cModulId', $this->moduleID);
        $this->caption        = $result->cName ?? null;
        $this->duringCheckout = isset($result->nWaehrendBestellung)
            ? (int)$result->nWaehrendBestellung
            : 0;

        if ($nAgainCheckout === 1) {
            $this->duringCheckout = 0;
        }
        if ($this->cModulId === 'za_null_jtl' || $this->moduleID === 'za_null_jtl') {
            $this->kZahlungsart = (int)$result->kZahlungsart;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOrderHash(Bestellung $order): ?string
    {
        $orderId = isset($order->kBestellung)
            ? Shop::Container()->getDB()->query(
                'SELECT cId FROM tbestellid WHERE kBestellung = ' . (int)$order->kBestellung,
                ReturnType::SINGLE_OBJECT
            )
            : null;

        return $orderId->cId ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getReturnURL(Bestellung $order): string
    {
        if (isset($_SESSION['Zahlungsart']->nWaehrendBestellung)
            && (int)$_SESSION['Zahlungsart']->nWaehrendBestellung > 0
        ) {
            return Shop::getURL() . '/bestellvorgang.php';
        }
        if (Shop::getSettings([\CONF_KAUFABWICKLUNG])['kaufabwicklung']['bestellabschluss_abschlussseite'] === 'A') {
            // Abschlussseite
            $oZahlungsID = Shop::Container()->getDB()->query(
                'SELECT cId
                    FROM tbestellid
                    WHERE kBestellung = ' . (int)$order->kBestellung,
                ReturnType::SINGLE_OBJECT
            );
            if (\is_object($oZahlungsID)) {
                return Shop::getURL() . '/bestellabschluss.php?i=' . $oZahlungsID->cId;
            }
        }

        return $order->BestellstatusURL;
    }

    /**
     * @inheritDoc
     */
    public function getNotificationURL(string $hash): string
    {
        $key = $this->duringCheckout ? 'sh' : 'ph';

        return Shop::getURL() . '/includes/modules/notify.php?' . $key . '=' . $hash;
    }

    /**
     * @inheritDoc
     */
    public function updateNotificationID(int $orderID, string $cNotifyID)
    {
        if ($orderID > 0) {
            $upd            = new stdClass();
            $upd->cNotifyID = Shop::Container()->getDB()->escape($cNotifyID);
            $upd->dNotify   = 'NOW()';
            Shop::Container()->getDB()->update('tzahlungsession', 'kBestellung', $orderID, $upd);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getShopTitle(): string
    {
        return Shop::getConfigValue(\CONF_GLOBAL, 'global_shopname');
    }

    /**
     * @inheritDoc
     */
    public function preparePaymentProcess(Bestellung $order): void
    {
        // overwrite!
    }

    /**
     * @inheritDoc
     */
    public function sendErrorMail(string $body)
    {
        $mail = new Mail();

        Shop::Container()->get(Mailer::class)->send(
            $mail->setLanguage(LanguageHelper::getDefaultLanguage())
                ->setToName(Shop::getSettingValue(\CONF_EMAILS, 'email_master_absender_name'))
                ->setToMail(Shop::getSettingValue(\CONF_EMAILS, 'email_master_absender'))
                ->setSubject(\sprintf(
                    Shop::Lang()->get('errorMailSubject', 'paymentMethods'),
                    Shop::getSettingValue(\CONF_GLOBAL, 'global_shopname')
                ))
                ->setBodyText($body)
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function generateHash(Bestellung $order): string
    {
        $hash = null;
        if ((int)$this->duringCheckout === 1) {
            if (!isset($_SESSION['IP'])) {
                $_SESSION['IP'] = new stdClass();
            }
            $_SESSION['IP']->cIP = Request::getRealIP();
        }

        if ($order->kBestellung !== null) {
            $oBestellID              = Shop::Container()->getDB()->select(
                'tbestellid',
                'kBestellung',
                (int)$order->kBestellung
            );
            $hash                    = $oBestellID->cId;
            $paymentID               = new stdClass();
            $paymentID->kBestellung  = $order->kBestellung;
            $paymentID->kZahlungsart = $order->kZahlungsart;
            $paymentID->cId          = $hash;
            $paymentID->txn_id       = '';
            $paymentID->dDatum       = 'NOW()';
            Shop::Container()->getDB()->insert('tzahlungsid', $paymentID);
        } else {
            Shop::Container()->getDB()->delete('tzahlungsession', ['cSID', 'kBestellung'], [\session_id(), 0]);
            $paymentSession               = new stdClass();
            $paymentSession->cSID         = \session_id();
            $paymentSession->cNotifyID    = '';
            $paymentSession->dZeitBezahlt = 'NOW()';
            $paymentSession->cZahlungsID  = \uniqid('', true);
            $paymentSession->dZeit        = 'NOW()';
            Shop::Container()->getDB()->insert('tzahlungsession', $paymentSession);
            $hash = '_' . $paymentSession->cZahlungsID;
        }

        return $hash;
    }

    /**
     * @inheritDoc
     */
    public function deletePaymentHash(string $paymentHash)
    {
        Shop::Container()->getDB()->delete('tzahlungsid', 'cId', $paymentHash);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addIncomingPayment(Bestellung $order, object $payment)
    {
        $model = (object)\array_merge([
            'kBestellung'       => (int)$order->kBestellung,
            'cZahlungsanbieter' => empty($order->cZahlungsartName) ? $this->name : $order->cZahlungsartName,
            'fBetrag'           => 0,
            'fZahlungsgebuehr'  => 0,
            'cISO'              => Frontend::getCurrency()->getCode(),
            'cEmpfaenger'       => '',
            'cZahler'           => '',
            'dZeit'             => 'NOW()',
            'cHinweis'          => '',
            'cAbgeholt'         => 'N'
        ], (array)$payment);
        Shop::Container()->getDB()->insert('tzahlungseingang', $model);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOrderStatusToPaid(Bestellung $order)
    {
        $_upd                = new stdClass();
        $_upd->cStatus       = \BESTELLUNG_STATUS_BEZAHLT;
        $_upd->dBezahltDatum = 'NOW()';
        Shop::Container()->getDB()->update('tbestellung', 'kBestellung', (int)$order->kBestellung, $_upd);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sendConfirmationMail(Bestellung $order)
    {
        $this->sendMail($order->kBestellung, \MAILTEMPLATE_BESTELLUNG_BEZAHLT);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function handleNotification(Bestellung $order, string $hash, array $args): void
    {
        // overwrite!
    }

    /**
     * @inheritDoc
     */
    public function finalizeOrder(Bestellung $order, string $hash, array $args): bool
    {
        // overwrite!
        return false;
    }

    /**
     * @inheritDoc
     */
    public function redirectOnCancel(): bool
    {
        // overwrite!
        return false;
    }

    /**
     * @inheritDoc
     */
    public function redirectOnPaymentSuccess(): bool
    {
        // overwrite!
        return false;
    }

    /**
     * @inheritDoc
     */
    public function doLog(string $msg, int $level = \LOGLEVEL_NOTICE)
    {
        ZahlungsLog::add($this->moduleID, $msg, null, $level);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerOrderCount(int $customerID): int
    {
        if ($customerID > 0) {
            $order = Shop::Container()->getDB()->query(
                "SELECT COUNT(*) AS nAnzahl
                    FROM tbestellung
                    WHERE (cStatus = '2' || cStatus = '3' || cStatus = '4')
                        AND kKunde = " . $customerID,
                ReturnType::SINGLE_OBJECT
            );

            if (isset($order->nAnzahl) && count($order->nAnzahl) > 0) {
                return (int)$order->nAnzahl;
            }
        }

        return 0;
    }

    /**
     * @inheritDoc
     */
    public function loadSettings()
    {
        $this->paymentConfig = Shop::getSettings([\CONF_ZAHLUNGSARTEN])['zahlungsarten'];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSetting(string $key)
    {
        $conf = Shop::getSettings([\CONF_ZAHLUNGSARTEN, \CONF_PLUGINZAHLUNGSARTEN]);

        return $conf['zahlungsarten']['zahlungsart_' . $this->moduleAbbr . '_' . $key]
            ?? ($conf['pluginzahlungsarten'][$this->moduleID . '_' . $key] ?? null);
    }

    /**
     *
     * @inheritDoc
     */
    public function isValid(object $customer, Cart $cart): bool
    {
        if (!$this->isValidIntern([$customer, $cart])) {
            return false;
        }

        if ($this->getSetting('min_bestellungen') > 0) {
            if (isset($customer->kKunde) && $customer->kKunde > 0) {
                $res   = Shop::Container()->getDB()->executeQueryPrepared(
                    'SELECT COUNT(*) AS cnt
                        FROM tbestellung
                        WHERE kKunde = :cid
                        AND (cStatus = :stp OR cStatus = :sts)',
                    [
                        'cid' => (int)$customer->kKunde,
                        'stp' => \BESTELLUNG_STATUS_BEZAHLT,
                        'sts' => \BESTELLUNG_STATUS_VERSANDT
                    ],
                    ReturnType::SINGLE_OBJECT
                );
                $count = (int)$res->cnt;
                if ($count < $this->getSetting('min_bestellungen')) {
                    ZahlungsLog::add(
                        $this->moduleID,
                        'Bestellanzahl ' . $count . ' ist kleiner als die Mindestanzahl von ' .
                        $this->getSetting('min_bestellungen'),
                        null,
                        \LOGLEVEL_NOTICE
                    );

                    return false;
                }
            } else {
                ZahlungsLog::add($this->moduleID, 'Es ist kein kKunde vorhanden', null, \LOGLEVEL_NOTICE);

                return false;
            }
        }

        $cartTotal = $cart->gibGesamtsummeWarenOhne([\C_WARENKORBPOS_TYP_VERSANDPOS], true);
        $min       = (float)$this->getSetting('min');
        if ($min > 0 && $cartTotal < $min) {
            ZahlungsLog::add(
                $this->moduleID,
                'Bestellwert ' . $cartTotal .
                ' ist kleiner als der Mindestbestellwert von ' . $min,
                null,
                \LOGLEVEL_NOTICE
            );

            return false;
        }

        $max = (float)$this->getSetting('max');
        if ($max > 0 && $cartTotal >= $max) {
            ZahlungsLog::add(
                $this->moduleID,
                'Bestellwert ' . $cartTotal .
                ' ist groesser als der maximale Bestellwert von ' . $max,
                null,
                \LOGLEVEL_NOTICE
            );

            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function isValidIntern(array $args_arr = []): bool
    {
        // Overwrite
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isSelectable(): bool
    {
        // Overwrite
        return $this->isValid(Frontend::getCustomer(), Frontend::getCart());
    }

    /**
     * @inheritDoc
     */
    public function handleAdditional(array $post): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function validateAdditional(): bool
    {
        return true;
    }

    /**
     *
     * @inheritDoc
     */
    public function addCache(string $cKey, string $cValue)
    {
        $_SESSION[$this->moduleID][$cKey] = $cValue;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unsetCache(?string $cKey = null)
    {
        if ($cKey === null) {
            unset($_SESSION[$this->moduleID]);
        } else {
            unset($_SESSION[$this->moduleID][$cKey]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCache(?string $cKey = null)
    {
        if ($cKey === null) {
            return $_SESSION[$this->moduleID] ?? null;
        }

        return $_SESSION[$this->moduleID][$cKey] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function createInvoice(int $orderID, int $languageID): object
    {
        return (object)[
            'nType' => 0,
            'cInfo' => '',
        ];
    }

    /**
     * @inheritDoc
     */
    public function reactivateOrder(int $orderID)
    {
        $this->sendMail($orderID, \MAILTEMPLATE_BESTELLUNG_RESTORNO);
        $upd                = new stdClass();
        $upd->cStatus       = \BESTELLUNG_STATUS_IN_BEARBEITUNG;
        $upd->dBezahltDatum = 'NOW()';
        Shop::Container()->getDB()->update('tbestellung', 'kBestellung', $orderID, $upd);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function cancelOrder(int $orderID, bool $delete = false)
    {
        if (!$delete) {
            $this->sendMail($orderID, \MAILTEMPLATE_BESTELLUNG_STORNO);
            $upd                = new stdClass();
            $upd->cStatus       = \BESTELLUNG_STATUS_STORNO;
            $upd->dBezahltDatum = 'NOW()';
            Shop::Container()->getDB()->update('tbestellung', 'kBestellung', $orderID, $upd);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function canPayAgain(): bool
    {
        // overwrite
        return false;
    }

    /**
     * @inheritDoc
     */
    public function sendMail(int $orderID, string $type, $additional = null)
    {
        $order = new Bestellung($orderID);
        $order->fuelleBestellung(false);
        $customer = new Customer($order->kKunde);
        $data     = new stdClass();
        $mailer   = Shop::Container()->get(Mailer::class);
        $mail     = new Mail();

        switch ($type) {
            case \MAILTEMPLATE_BESTELLBESTAETIGUNG:
            case \MAILTEMPLATE_BESTELLUNG_TEILVERSANDT:
            case \MAILTEMPLATE_BESTELLUNG_AKTUALISIERT:
            case \MAILTEMPLATE_BESTELLUNG_VERSANDT:
                $data->tkunde      = $customer;
                $data->tbestellung = $order;
                if ($customer->cMail !== '') {
                    $mailer->send($mail->createFromTemplateID($type, $data));
                }
                break;

            case \MAILTEMPLATE_BESTELLUNG_BEZAHLT:
                $data->tkunde      = $customer;
                $data->tbestellung = $order;
                if (($order->Zahlungsart->nMailSenden & \ZAHLUNGSART_MAIL_EINGANG) && $customer->cMail !== '') {
                    $mailer->send($mail->createFromTemplateID($type, $data));
                }
                break;

            case \MAILTEMPLATE_BESTELLUNG_STORNO:
                $data->tkunde      = $customer;
                $data->tbestellung = $order;
                if (($order->Zahlungsart->nMailSenden & \ZAHLUNGSART_MAIL_STORNO) && $customer->cMail !== '') {
                    $mailer->send($mail->createFromTemplateID($type, $data));
                }
                break;

            case \MAILTEMPLATE_BESTELLUNG_RESTORNO:
                $data->tkunde      = $customer;
                $data->tbestellung = $order;
                if (($order->Zahlungsart->nMailSenden & \ZAHLUNGSART_MAIL_RESTORNO) && $customer->cMail !== '') {
                    $mailer->send($mail->createFromTemplateID($type, $data));
                }
                break;

            default:
                break;
        }

        return $this;
    }

    /**
     * @param string $moduleID
     * @param int    $nAgainCheckout
     * @return MethodInterface|null
     */
    public static function create(string $moduleID, int $nAgainCheckout = 0): ?MethodInterface
    {
        if ($moduleID === 'za_null_jtl') {
            return new FallbackMethod('za_null_jtl');
        }

        $paymentMethod = null;
        $pluginID      = PluginHelper::getIDByModuleID($moduleID);

        if ($pluginID > 0 && \SAFE_MODE === false) {
            $loader = PluginHelper::getLoaderByPluginID($pluginID);
            try {
                $plugin = $loader->init($pluginID);
            } catch (InvalidArgumentException $e) {
                $plugin = null;
            }
            if ($plugin !== null) {
                $pluginPaymentMethod = $plugin->getPaymentMethods()->getMethodByID($moduleID);
                if ($pluginPaymentMethod === null) {
                    return null;
                }
                $className = $pluginPaymentMethod->getClassName();
                if (\class_exists($className)) {
                    $paymentMethod = new $className($moduleID, $nAgainCheckout);
                    if (!\is_a($paymentMethod, MethodInterface::class)) {
                        unset($paymentMethod);
                        $paymentMethod = null;
                    }
                }
            }
        }

        return $paymentMethod;
    }
}
