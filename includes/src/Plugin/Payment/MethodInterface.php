<?php

namespace JTL\Plugin\Payment;

use JTL\Cart\Cart;
use JTL\Checkout\Bestellung;

/**
 * Interface MethodInterface - Represents a Method of Payment the customer can pay his order with.
 * @package JTL\Plugin\Payment
 */
interface MethodInterface
{
    /**
     * Set Members Variables
     *
     * @param int $nAgainCheckout
     * @return static
     */
    public function init(int $nAgainCheckout = 0);

    /**
     * @param Bestellung $order
     * @return string|null
     */
    public function getOrderHash(Bestellung $order): ?string;

    /**
     * Payment Provider redirects customer to this URL when Payment is complete
     *
     * @param Bestellung $order
     * @return string
     */
    public function getReturnURL(Bestellung $order): string;

    /**
     * @param string $hash
     * @return string
     */
    public function getNotificationURL(string $hash): string;

    /**
     * @param int    $orderID
     * @param string $cNotifyID
     * @return static
     */
    public function updateNotificationID(int $orderID, string $cNotifyID);

    /**
     * @return string
     */
    public function getShopTitle(): string;

    /**
     * Prepares everything so that the Customer can start the Payment Process.
     * Tells Template Engine.
     *
     * @param Bestellung $order
     * @return void
     */
    public function preparePaymentProcess(Bestellung $order): void;

    /**
     * Sends Error Mail to Master
     *
     * @param string $body
     * @return static
     */
    public function sendErrorMail(string $body);

    /**
     * Generates Hash (Payment oder Session Hash) and saves it to DB
     *
     * @param Bestellung $order
     * @return string
     */
    public function generateHash(Bestellung $order): string;

    /**
     * @param string $paymentHash
     * @return static
     */
    public function deletePaymentHash(string $paymentHash);

    /**
     * @param Bestellung $order
     * @param Object     $payment (Key, Zahlungsanbieter, Abgeholt, Zeit is set here)
     * @return static
     */
    public function addIncomingPayment(Bestellung $order, object $payment);

    /**
     * @param Bestellung $order
     * @return static
     */
    public function setOrderStatusToPaid(Bestellung $order);

    /**
     * Sends a Mail to the Customer if Payment was recieved
     *
     * @param Bestellung $order
     * @return static
     */
    public function sendConfirmationMail(Bestellung $order);

    /**
     * @param Bestellung $order
     * @param string     $hash
     * @param array      $args
     * @return void
     */
    public function handleNotification(Bestellung $order, string $hash, array $args): void;

    /**
     * @param Bestellung $order
     * @param string     $hash
     * @param array      $args
     *
     * @return bool - true, if $order should be finalized
     */
    public function finalizeOrder(Bestellung $order, string $hash, array $args): bool;

    /**
     * @return bool
     */
    public function redirectOnCancel(): bool;

    /**
     * @return bool
     */
    public function redirectOnPaymentSuccess(): bool;

    /**
     * @param string $msg
     * @param int    $level
     * @return static
     */
    public function doLog(string $msg, int $level = \LOGLEVEL_NOTICE);

    /**
     * @param int $customerID
     * @return int
     */
    public function getCustomerOrderCount(int $customerID): int;

    /**
     * @return static
     */
    public function loadSettings();

    /**
     * @param string $key
     * @return mixed
     */
    public function getSetting(string $key);

    /**
     *
     * @param object $customer
     * @param Cart   $cart
     * @return bool - true, if $customer with $cart may use Payment Method
     */
    public function isValid(object $customer, Cart $cart): bool;

    /**
     * @param array $args_arr
     * @return bool
     */
    public function isValidIntern(array $args_arr = []): bool;

    /**
     * determines, if the payment method can be selected in the checkout process
     *
     * @return bool
     */
    public function isSelectable(): bool;

    /**
     * @param array $post
     * @return bool
     */
    public function handleAdditional(array $post): bool;

    /**
     * @return bool
     */
    public function validateAdditional(): bool;

    /**
     *
     * @param string $cKey
     * @param string $cValue
     * @return static
     */
    public function addCache(string $cKey, string $cValue);

    /**
     * @param string|null $cKey
     * @return static
     */
    public function unsetCache(?string $cKey = null);

    /**
     * @param null|string $cKey
     * @return mixed|null
     */
    public function getCache(?string $cKey = null);

    /**
     * @param int $orderID
     * @param int $languageID
     * @return object
     */
    public function createInvoice(int $orderID, int $languageID): object;

    /**
     * @param int $orderID
     * @return static
     */
    public function reactivateOrder(int $orderID);

    /**
     * @param int  $orderID
     * @param bool $delete
     * @return static
     */
    public function cancelOrder(int $orderID, bool $delete = false);

    /**
     * @return bool
     */
    public function canPayAgain(): bool;

    /**
     * @param int    $orderID
     * @param string $type
     * @param mixed  $additional
     * @return static
     */
    public function sendMail(int $orderID, string $type, $additional = null);
}
