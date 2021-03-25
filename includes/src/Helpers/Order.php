<?php

namespace JTL\Helpers;

use JTL\Cart\Cart;
use JTL\Cart\CartHelper;
use JTL\Catalog\Currency;
use JTL\Checkout\Bestellung;
use JTL\Checkout\Lieferadresse;
use JTL\Checkout\Rechnungsadresse;
use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Shop;
use stdClass;

/**
 * Class Order
 * @package JTL\Helpers
 */
class Order extends CartHelper
{
    /**
     * @var Bestellung
     */
    protected $order;

    /**
     * @param Bestellung $order
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * @inheritDoc
     */
    protected function calculateCredit(stdClass $cartInfo): void
    {
        if ((float)$this->order->fGuthaben !== 0.0) {
            $amountGross = $this->order->fGuthaben;

            $cartInfo->discount[self::NET]   += $amountGross;
            $cartInfo->discount[self::GROSS] += $amountGross;
        }
        // positive discount
        $cartInfo->discount[self::NET]   *= -1;
        $cartInfo->discount[self::GROSS] *= -1;
    }

    /**
     * @return Cart|Bestellung|null
     */
    public function getObject()
    {
        return $this->order;
    }

    /**
     * @return Lieferadresse|Rechnungsadresse
     */
    public function getShippingAddress()
    {
        if ((int)$this->order->kLieferadresse > 0 && \is_object($this->order->Lieferadresse)) {
            return $this->order->Lieferadresse;
        }

        return $this->getBillingAddress();
    }

    /**
     * @return Rechnungsadresse|null
     */
    public function getBillingAddress(): ?Rechnungsadresse
    {
        return $this->order->oRechnungsadresse;
    }

    /**
     * @inheritDoc
     */
    public function getPositions(): array
    {
        return $this->order->Positionen;
    }

    /**
     * @return Customer
     */
    public function getCustomer(): ?Customer
    {
        return $this->order->oKunde;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerGroup(): CustomerGroup
    {
        return new CustomerGroup($this->order->oKunde->getGroupID());
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->order->Waehrung;
    }

    /**
     * @return string iso
     */
    public function getLanguage(): string
    {
        return Shop::Lang()->getIsoFromLangID($this->order->kSprache);
    }

    /**
     * @return string
     */
    public function getInvoiceID(): string
    {
        return $this->order->cBestellNr;
    }

    /**
     * @return int
     */
    public function getIdentifier(): int
    {
        return (int)$this->order->kBestellung;
    }

    /**
     * @param int $customerID
     * @return object|null
     * @since 5.0.0
     */
    public static function getLastOrderRefIDs(int $customerID): ?object
    {
        $order = Shop::Container()->getDB()->queryPrepared(
            'SELECT kBestellung, kWarenkorb, kLieferadresse, kRechnungsadresse, kZahlungsart, kVersandart
                FROM tbestellung
                WHERE kKunde = :customerID
                ORDER BY dErstellt DESC
                LIMIT 1',
            ['customerID' => $customerID],
            ReturnType::SINGLE_OBJECT
        );

        return \is_object($order)
            ? (object)[
                'kBestellung'       => (int)$order->kBestellung,
                'kWarenkorb'        => (int)$order->kWarenkorb,
                'kLieferadresse'    => (int)$order->kLieferadresse,
                'kRechnungsadresse' => (int)$order->kRechnungsadresse,
                'kZahlungsart'      => (int)$order->kZahlungsart,
                'kVersandart'       => (int)$order->kVersandart,
            ]
            : (object)[
                'kBestellung'       => 0,
                'kWarenkorb'        => 0,
                'kLieferadresse'    => 0,
                'kRechnungsadresse' => 0,
                'kZahlungsart'      => 0,
                'kVersandart'       => 0,
            ];
    }
}
