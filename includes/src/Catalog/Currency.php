<?php

namespace JTL\Catalog;

use JTL\Helpers\Tax;
use JTL\MagicCompatibilityTrait;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class Currency
 * @package JTL\Catalog
 */
class Currency
{
    use MagicCompatibilityTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $htmlEntity;

    /**
     * @var float
     */
    private $conversionFactor;

    /**
     * @var bool
     */
    private $isDefault = false;

    /**
     * @var bool
     */
    private $forcePlacementBeforeNumber = false;

    /**
     * @var string
     */
    private $decimalSeparator;

    /**
     * @var string
     */
    private $thousandsSeparator;

    /**
     * @var string
     */
    private $cURL;

    /**
     * @var string
     */
    private $cURLFull;

    /**
     * @var array
     */
    protected static $mapping = [
        'kWaehrung'            => 'ID',
        'cISO'                 => 'Code',
        'cName'                => 'Name',
        'cNameHTML'            => 'HtmlEntity',
        'fFaktor'              => 'ConversionFactor',
        'cStandard'            => 'IsDefault',
        'cVorBetrag'           => 'forcePlacementBeforeNumber',
        'cTrennzeichenCent'    => 'DecimalSeparator',
        'cTrennzeichenTausend' => 'ThousandsSeparator',
        'cURL'                 => 'URL',
        'cURLFull'             => 'URLFull'
    ];

    /**
     * Currency constructor.
     *
     * @param int|null $id
     */
    public function __construct(int $id = null)
    {
        if ($id > 0) {
            $data = Shop::Container()->getDB()->select('twaehrung', 'kWaehrung', $id);
            if ($data !== null) {
                $data->kWaehrung = (int)$data->kWaehrung;
                $this->extract($data);
            }
        }
    }

    /**
     * @param string $iso
     * @return static
     */
    public static function fromISO(string $iso): self
    {
        $data     = Shop::Container()->getDB()->select('twaehrung', 'cISO', $iso);
        $instance = new static();

        if ($data !== null) {
            $data->kWaehrung = (int)$data->kWaehrung;
            $instance->extract($data);
        } else {
            $instance->getDefault();
        }

        return $instance;
    }

    /**
     * @return int|null
     */
    public function getID(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Currency
     */
    public function setID(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Currency
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Currency
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getHtmlEntity(): ?string
    {
        return $this->htmlEntity;
    }

    /**
     * @param string $htmlEntity
     * @return Currency
     */
    public function setHtmlEntity(string $htmlEntity): self
    {
        $this->htmlEntity = $htmlEntity;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getConversionFactor(): ?float
    {
        return $this->conversionFactor;
    }

    /**
     * @param float $conversionFactor
     * @return Currency
     */
    public function setConversionFactor($conversionFactor): self
    {
        $this->conversionFactor = (float)$conversionFactor;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * @param bool|string $isDefault
     * @return Currency
     */
    public function setIsDefault($isDefault): self
    {
        if (\is_string($isDefault)) {
            $isDefault = $isDefault === 'Y';
        }
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * @return bool
     */
    public function getForcePlacementBeforeNumber(): bool
    {
        return $this->forcePlacementBeforeNumber;
    }

    /**
     * @param bool|string $forcePlacementBeforeNumber
     * @return Currency
     */
    public function setForcePlacementBeforeNumber($forcePlacementBeforeNumber): self
    {
        if (\is_string($forcePlacementBeforeNumber)) {
            $forcePlacementBeforeNumber = $forcePlacementBeforeNumber === 'Y';
        }
        $this->forcePlacementBeforeNumber = $forcePlacementBeforeNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDecimalSeparator(): ?string
    {
        return $this->decimalSeparator;
    }

    /**
     * @param string $decimalSeparator
     * @return Currency
     */
    public function setDecimalSeparator($decimalSeparator): self
    {
        $this->decimalSeparator = $decimalSeparator;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getThousandsSeparator(): ?string
    {
        return $this->thousandsSeparator;
    }

    /**
     * @param string $thousandsSeparator
     * @return Currency
     */
    public function setThousandsSeparator(string $thousandsSeparator): self
    {
        $this->thousandsSeparator = $thousandsSeparator;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getURL(): ?string
    {
        return $this->cURL;
    }

    /**
     * @param string $url
     * @return Currency
     */
    public function setURL(string $url): self
    {
        $this->cURL = $url;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getURLFull(): ?string
    {
        return $this->cURLFull;
    }

    /**
     * @param string $url
     * @return Currency
     */
    public function setURLFull(string $url): self
    {
        $this->cURLFull = $url;

        return $this;
    }

    /**
     * @return Currency
     */
    public function getDefault(): self
    {
        $data = Shop::Container()->getDB()->select('twaehrung', 'cStandard', 'Y');
        if ($data !== null) {
            $data->kWaehrung = (int)$data->kWaehrung;
            $this->extract($data);
        }

        return $this;
    }

    /**
     * @param stdClass $obs
     * @return $this
     */
    private function extract(stdClass $obs): self
    {
        foreach (\get_object_vars($obs) as $var => $value) {
            if (($mapped = self::getMapping($var)) !== null) {
                $method = 'set' . $mapped;
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * @param float  $priceNet
     * @param float  $priceGross
     * @param string $class
     * @param bool   $forceTax
     * @return string
     */
    public static function getCurrencyConversion($priceNet, $priceGross, $class = '', bool $forceTax = true): string
    {
        self::setCurrencies();

        $res        = '';
        $currencies = Frontend::getCurrencies();
        if (\count($currencies) > 0) {
            $priceNet   = (float)\str_replace(',', '.', $priceNet ?? 0);
            $priceGross = (float)\str_replace(',', '.', $priceGross ?? 0);
            $taxClass   = Shop::Container()->getDB()->select('tsteuerklasse', 'cStandard', 'Y');
            $taxClassID = $taxClass !== null ? (int)$taxClass->kSteuerklasse : 1;
            if ((float)$priceNet > 0) {
                $priceNet   = (float)$priceNet;
                $priceGross = Tax::getGross((float)$priceNet, Tax::getSalesTax($taxClassID));
            } elseif ((float)$priceGross > 0) {
                $priceNet   = Tax::getNet((float)$priceGross, Tax::getSalesTax($taxClassID));
                $priceGross = (float)$priceGross;
            }

            $res = '<span class="preisstring ' . $class . '">';
            foreach ($currencies as $i => $currency) {
                $priceLocalized = \number_format(
                    $priceNet * $currency->getConversionFactor(),
                    2,
                    $currency->getDecimalSeparator(),
                    $currency->getThousandsSeparator()
                );
                $grossLocalized = \number_format(
                    $priceGross * $currency->getConversionFactor(),
                    2,
                    $currency->getDecimalSeparator(),
                    $currency->getThousandsSeparator()
                );
                if ($currency->getForcePlacementBeforeNumber() === true) {
                    $priceLocalized = $currency->getHtmlEntity() . ' ' . $priceLocalized;
                    $grossLocalized = $currency->getHtmlEntity() . ' ' . $grossLocalized;
                } else {
                    $priceLocalized .= ' ' . $currency->getHtmlEntity();
                    $grossLocalized .= ' ' . $currency->getHtmlEntity();
                }
                // Wurde geändert weil der Preis nun als Betrag gesehen wird
                // und die Steuer direkt in der Versandart als eSteuer Flag eingestellt wird
                if ($i > 0) {
                    $res .= $forceTax
                        ? ('<br><strong>' . $grossLocalized . '</strong>' .
                            ' (<em>' . $priceLocalized . ' ' .
                            Shop::Lang()->get('net') . '</em>)')
                        : ('<br> ' . $grossLocalized);
                } else {
                    $res .= $forceTax
                        ? ('<strong>' . $grossLocalized . '</strong>' .
                            ' (<em>' . $priceLocalized . ' ' .
                            Shop::Lang()->get('net') . '</em>)')
                        : '<strong>' . $grossLocalized . '</strong>';
                }
            }
            $res .= '</span>';
        }

        return $res;
    }

    /**
     * Converts price into given currency
     *
     * @param float       $price
     * @param string|null $iso - EUR / USD
     * @param int|null    $id - kWaehrung
     * @param bool        $round
     * @param int         $precision
     * @return float|bool
     */
    public static function convertCurrency(
        $price,
        string $iso = null,
        $id = null,
        bool $round = true,
        int $precision = 2
    ) {
        self::setCurrencies();

        foreach (Frontend::getCurrencies() as $currency) {
            if (($iso !== null && $currency->getCode() === $iso) || ($id !== null && $currency->getID() === (int)$id)) {
                $newprice = $price * $currency->getConversionFactor();

                return $round ? \round($newprice, $precision) : $newprice;
            }
        }

        return false;
    }

    /**
     * @param bool $update
     * @return void
     */
    public static function setCurrencies(bool $update = false): void
    {
        if ($update || \count(Frontend::getCurrencies()) === 0) {
            $currencies    = [];
            $allCurrencies = Shop::Container()->getDB()->selectAll('twaehrung', [], [], 'kWaehrung');
            foreach ($allCurrencies as $currency) {
                $currencies[] = new self((int)$currency->kWaehrung);
            }
            $_SESSION['Waehrungen'] = $currencies;
        }
    }
}
