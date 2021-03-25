<?php

namespace JTL\Checkout;

use JTL\DB\ReturnType;
use JTL\Shop;
use stdClass;

/**
 * Class KuponBestellung
 * @package JTL\Checkout
 */
class KuponBestellung
{
    /**
     * @var int
     */
    public $kKupon;

    /**
     * @var int
     */
    public $kBestellung;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var string
     */
    public $cBestellNr;

    /**
     * @var float
     */
    public $fGesamtsummeBrutto;

    /**
     * @var float
     */
    public $fKuponwertBrutto;

    /**
     * @var string
     */
    public $cKuponTyp;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * KuponBestellung constructor.
     *
     * @param int $couponID
     * @param int $orderID
     */
    public function __construct(int $couponID = 0, int $orderID = 0)
    {
        if ($couponID > 0 && $orderID > 0) {
            $this->loadFromDB($couponID, $orderID);
        }
    }

    /**
     * @param int $couponID
     * @param int $orderID
     * @return $this
     */
    private function loadFromDB(int $couponID = 0, int $orderID = 0): self
    {
        $item = Shop::Container()->getDB()->select(
            'tkuponbestelllung',
            'kKupon',
            $couponID,
            'kBestellung',
            $orderID
        );
        if (isset($item->kKupon) && $item->kKupon > 0) {
            foreach (\array_keys(\get_object_vars($item)) as $member) {
                $this->$member = $item->$member;
            }
        }

        return $this;
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save(bool $bPrim = true)
    {
        $ins = new stdClass();
        foreach (\array_keys(\get_object_vars($this)) as $member) {
            $ins->$member = $this->$member;
        }

        $kPrim = Shop::Container()->getDB()->insert('tkuponbestellung', $ins);

        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $_upd                      = new stdClass();
        $_upd->kKupon              = $this->kKupon;
        $_upd->kBestellung         = $this->kBestellung;
        $_upd->kKunde              = $this->kKunde;
        $_upd->cBestellNr          = $this->cBestellNr;
        $_upd->fGesammtsummeBrutto = $this->fGesamtsummeBrutto;
        $_upd->fKuponwertBrutto    = $this->fKuponwertBrutto;
        $_upd->cKuponTyp           = $this->cKuponTyp;
        $_upd->dErstellt           = $this->dErstellt;

        return Shop::Container()->getDB()->update(
            'tkuponbestellung',
            ['kKupon', 'kBestellung'],
            [(int)$this->kKupon, (int)$this->kBestellung],
            $_upd
        );
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete(
            'tkupon',
            ['kKupon', 'kBestellung'],
            [(int)$this->kKupon, (int)$this->kBestellung]
        );
    }

    /**
     * @param int $kKupon
     * @return $this
     */
    public function setKupon(int $kKupon): self
    {
        $this->kKupon = $kKupon;

        return $this;
    }

    /**
     * @param int $orderID
     * @return $this
     */
    public function setBestellung(int $orderID): self
    {
        $this->kBestellung = $orderID;

        return $this;
    }

    /**
     * @param int $customerID
     * @return $this
     */
    public function setKunden(int $customerID): self
    {
        $this->kKunde = $customerID;

        return $this;
    }

    /**
     * @param string $cBestellNr
     * @return $this
     */
    public function setBestellNr($cBestellNr): self
    {
        $this->cBestellNr = Shop::Container()->getDB()->escape($cBestellNr);

        return $this;
    }

    /**
     * @param float $fGesamtsummeBrutto
     * @return $this
     */
    public function setGesamtsummeBrutto($fGesamtsummeBrutto): self
    {
        $this->fGesamtsummeBrutto = (float)$fGesamtsummeBrutto;

        return $this;
    }

    /**
     * @param float $fKuponwertBrutto
     * @return $this
     */
    public function setKuponwertBrutto($fKuponwertBrutto): self
    {
        $this->fKuponwertBrutto = (float)$fKuponwertBrutto;

        return $this;
    }

    /**
     * @param string $cKuponTyp
     * @return $this
     */
    public function setKuponTyp($cKuponTyp): self
    {
        $this->cKuponTyp = Shop::Container()->getDB()->escape($cKuponTyp);

        return $this;
    }

    /**
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt): self
    {
        $this->dErstellt = Shop::Container()->getDB()->escape($dErstellt);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getKupon(): ?int
    {
        return $this->kKupon;
    }

    /**
     * @return int|null
     */
    public function getBestellung(): ?int
    {
        return $this->kBestellung;
    }

    /**
     * @return int|null
     */
    public function getKunde(): ?int
    {
        return $this->kKunde;
    }

    /**
     * @return string|null
     */
    public function getBestellNr(): ?string
    {
        return $this->cBestellNr;
    }

    /**
     * @return string|float|null
     */
    public function getGesamtsummeBrutto()
    {
        return $this->fGesamtsummeBrutto;
    }

    /**
     * @return string|float|null
     */
    public function getKuponwertBrutto()
    {
        return $this->fKuponwertBrutto;
    }

    /**
     * @return string|null
     */
    public function getKuponTyp(): ?string
    {
        return $this->cKuponTyp;
    }

    /**
     * @return string|null
     */
    public function getErstellt(): ?string
    {
        return $this->dErstellt;
    }

    /**
     * Gets used coupons from orders
     *
     * @param string $start
     * @param string $end
     * @param int    $couponID
     * @return array
     */
    public static function getOrdersWithUsedCoupons($start, $end, int $couponID = 0): array
    {
        return Shop::Container()->getDB()->query(
            "SELECT kbs.*, wkp.cName, kp.kKupon
                FROM tkuponbestellung AS kbs
                LEFT JOIN tbestellung AS bs 
                   ON kbs.kBestellung = bs.kBestellung
                LEFT JOIN twarenkorbpos AS wkp 
                    ON bs.kWarenkorb = wkp.kWarenkorb
                LEFT JOIN tkupon AS kp 
                    ON kbs.kKupon = kp.kKupon
                WHERE kbs.dErstellt BETWEEN '" . $start . "'
                    AND '" . $end . "'
                    AND bs.cStatus != " . \BESTELLUNG_STATUS_STORNO . '
                    AND (wkp.nPosTyp = 3 OR wkp.nPosTyp = 7) ' .
            ($couponID > 0 ? ' AND kp.kKupon = ' . $couponID : '') . '
                ORDER BY kbs.dErstellt DESC',
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
    }
}
