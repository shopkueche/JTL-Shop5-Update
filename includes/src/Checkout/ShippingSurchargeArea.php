<?php declare(strict_types=1);

namespace JTL\Checkout;

/**
 * Class SurchargeArea
 * @package JTL\Checkout
 */
class ShippingSurchargeArea
{
    /**
     * @var string
     */
    public $ZIPFrom;

    /**
     * @var string
     */
    public $ZIPTo;

    /**
     * SurchargeArea constructor.
     * @param string $ZIPFrom
     * @param string $ZIPTo
     */
    public function __construct(string $ZIPFrom, string $ZIPTo)
    {
        $this->setZIPFrom($ZIPFrom)
             ->setZIPTo($ZIPTo);
    }

    /**
     * @return string
     */
    public function getZIPFrom(): string
    {
        return $this->ZIPFrom;
    }

    /**
     * @param string $ZIPFrom
     * @return ShippingSurchargeArea
     */
    public function setZIPFrom(string $ZIPFrom): self
    {
        $this->ZIPFrom = $ZIPFrom;

        return $this;
    }

    /**
     * @return string
     */
    public function getZIPTo(): string
    {
        return $this->ZIPTo;
    }

    /**
     * @param string $ZIPTo
     * @return ShippingSurchargeArea
     */
    public function setZIPTo(string $ZIPTo): self
    {
        $this->ZIPTo = $ZIPTo;

        return $this;
    }

    /**
     * @param string $zip
     * @return bool
     */
    public function isInArea(string $zip): bool
    {
        return ($this->getZIPFrom() <= $zip && $this->getZIPTo() >= $zip);
    }

    /**
     * @return string
     */
    public function getArea(): string
    {
        return $this->getZIPFrom() . ' - ' . $this->getZIPTo();
    }
}
