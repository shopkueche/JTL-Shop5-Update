<?php

namespace JTL\Checkout;

use JTL\Language\LanguageHelper;
use JTL\Shop;

/**
 * Class Adresse
 * @package JTL
 */
class Adresse
{
    /**
     * @var string
     */
    public $cAnrede;

    /**
     * @var string
     */
    public $cVorname;

    /**
     * @var string
     */
    public $cNachname;

    /**
     * @var string
     */
    public $cTitel;

    /**
     * @var string
     */
    public $cFirma;

    /**
     * @var string
     */
    public $cStrasse;

    /**
     * @var string
     */
    public $cAdressZusatz;

    /**
     * @var string
     */
    public $cPLZ;

    /**
     * @var string
     */
    public $cOrt;

    /**
     * @var string
     */
    public $cBundesland;

    /**
     * @var string
     */
    public $cLand;

    /**
     * @var string
     */
    public $cTel;

    /**
     * @var string
     */
    public $cMobil;

    /**
     * @var string
     */
    public $cFax;

    /**
     * @var string
     */
    public $cMail;

    /**
     * @var string
     */
    public $cHausnummer;

    /**
     * @var string
     */
    public $cZusatz;

    /**
     * @var array
     */
    protected static $encodedProperties = [
        'cNachname',
        'cFirma',
        'cZusatz',
        'cStrasse'
    ];

    /**
     * Adresse constructor.
     */
    public function __construct()
    {
    }

    /**
     * encrypt shipping address
     *
     * @return $this
     */
    public function encrypt(): self
    {
        $cyptoService = Shop::Container()->getCryptoService();
        foreach (self::$encodedProperties as $property) {
            $this->$property = $cyptoService->encryptXTEA(\trim((string)($this->$property ?? '')));
        }

        return $this;
    }

    /**
     * decrypt shipping address
     *
     * @return $this
     */
    public function decrypt(): self
    {
        $cryptoService = Shop::Container()->getCryptoService();
        foreach (self::$encodedProperties as $property) {
            if ($this->$property !== null) {
                $this->$property = \trim($cryptoService->decryptXTEA($this->$property));
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return \get_object_vars($this);
    }

    /**
     * @return object
     */
    public function toObject()
    {
        return (object)$this->toArray();
    }

    /**
     * @param array $array
     * @return $this
     */
    public function fromArray(array $array): self
    {
        foreach ($array as $key => $value) {
            if (\property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    /**
     * @param object $object
     * @return $this
     */
    public function fromObject($object): self
    {
        return $this->fromArray((array)$object);
    }

    /**
     * @param null|string $anrede
     * @return string
     */
    public function mappeAnrede(?string $anrede): string
    {
        switch (\mb_convert_case($anrede, \MB_CASE_LOWER)) {
            case 'm':
                return Shop::Lang()->get('salutationM');
            case 'w':
                return Shop::Lang()->get('salutationW');
            default:
                return '';
        }
    }

    /**
     * @param string $iso
     * @return string
     * @deprecated since 4.06.16 - use static checkISOCountryCode instead
     */
    public function pruefeLandISO(string $iso): string
    {
        return self::checkISOCountryCode($iso);
    }

    /**
     * @param string $iso
     * @return string
     */
    public static function checkISOCountryCode(string $iso): string
    {
        \preg_match('/[a-zA-Z]{2}/', $iso, $matches);
        if (\mb_strlen($matches[0]) !== \mb_strlen($iso)) {
            $o = LanguageHelper::getIsoCodeByCountryName($iso);
            if ($o !== 'noISO' && $o !== '') {
                $iso = $o;
            }
        }

        return $iso;
    }
}
