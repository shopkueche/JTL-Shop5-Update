<?php

namespace JTL\Extensions\Config;

use JTL\Nice;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class ItemPrice
 * @package JTL\Extensions\Config
 */
class ItemPrice
{
    public const PRICE_TYPE_PERCENTAGE = 1;

    public const PRICE_TYPE_SUM = 0;

    /**
     * @var int
     */
    protected $kKonfigitem;

    /**
     * @var int
     */
    protected $kKundengruppe;

    /**
     * @var int
     */
    protected $kSteuerklasse;

    /**
     * @var float
     */
    protected $fPreis;

    /**
     * @var int
     */
    protected $nTyp;

    /**
     * ItemPrice constructor.
     * @param int $configItemID
     * @param int $customerGroupID
     */
    public function __construct(int $configItemID = 0, int $customerGroupID = 0)
    {
        if ($configItemID > 0 && $customerGroupID > 0) {
            $this->loadFromDB($configItemID, $customerGroupID);
        }
    }

    /**
     * @return bool
     */
    public static function checkLicense(): bool
    {
        return Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_KONFIGURATOR);
    }

    /**
     * @param int $configItemID
     * @param int $customerGroupID
     */
    private function loadFromDB(int $configItemID = 0, int $customerGroupID = 0): void
    {
        $item = Shop::Container()->getDB()->select(
            'tkonfigitempreis',
            'kKonfigitem',
            $configItemID,
            'kKundengruppe',
            $customerGroupID
        );

        if (isset($item->kKonfigitem, $item->kKundengruppe)
            && $item->kKonfigitem > 0
            && $item->kKundengruppe > 0
        ) {
            foreach (\array_keys(\get_object_vars($item)) as $member) {
                $this->$member = $item->$member;
            }
            $this->kKonfigitem   = (int)$this->kKonfigitem;
            $this->kKundengruppe = (int)$this->kKundengruppe;
            $this->kSteuerklasse = (int)$this->kSteuerklasse;
            $this->nTyp          = (int)$this->nTyp;
        }
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function save(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @return int
     * @deprecated since 5.0.0
     */
    public function update(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return 0;
    }

    /**
     * @return int
     * @deprecated since 5.0.0
     */
    public function delete(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return 0;
    }

    /**
     * @param int $kKonfigitem
     * @return $this
     */
    public function setKonfigitem(int $kKonfigitem): self
    {
        $this->kKonfigitem = $kKonfigitem;

        return $this;
    }

    /**
     * @param int $customerGroupID
     * @return $this
     */
    public function setKundengruppe(int $customerGroupID):self
    {
        $this->kKundengruppe = $customerGroupID;

        return $this;
    }

    /**
     * @param int $kSteuerklasse
     * @return $this
     */
    public function setSteuerklasse(int $kSteuerklasse): self
    {
        $this->kSteuerklasse = $kSteuerklasse;

        return $this;
    }

    /**
     * @param float $fPreis
     * @return $this
     */
    public function setPreis($fPreis): self
    {
        $this->fPreis = (float)$fPreis;

        return $this;
    }

    /**
     * @return int
     */
    public function getKonfigitem(): int
    {
        return (int)$this->kKonfigitem;
    }

    /**
     * @return int
     */
    public function getKundengruppe(): int
    {
        return (int)$this->kKundengruppe;
    }

    /**
     * @return int
     */
    public function getSteuerklasse(): int
    {
        return (int)$this->kSteuerklasse;
    }

    /**
     * @param bool $convertCurrency
     * @return float|null
     */
    public function getPreis(bool $convertCurrency = false)
    {
        $price = $this->fPreis;
        if ($convertCurrency && $price > 0) {
            $price *= Frontend::getCurrency()->getConversionFactor();
        }

        return $price;
    }

    /**
     * @return int|null
     */
    public function getTyp(): ?int
    {
        return $this->nTyp;
    }
}
