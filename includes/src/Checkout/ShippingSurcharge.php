<?php declare(strict_types=1);

namespace JTL\Checkout;

use JTL\Catalog\Product\Preise;
use JTL\DB\ReturnType;
use JTL\MagicCompatibilityTrait;
use JTL\Shop;
use stdClass;

/**
 * Class Surcharge
 * @package JTL\Checkout
 */
class ShippingSurcharge
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    protected static $mapping = [
        'kVersandzuschlag' => 'ID',
        'cISO'             => 'ISO',
        'cName'            => 'Title',
        'fZuschlag'        => 'Surcharge',
        'kVersandart'      => 'ShippingMethod',
        'cPreisLocalized'  => 'PriceLocalized'
    ];

    /**
     * @var int
     */
    public $ID;

    /**
     * @var string
     */
    public $ISO;

    /**
     * @var string
     */
    public $title;

    /**
     * @var float
     */
    public $surcharge;

    /**
     * @var int
     */
    public $shippingMethod;

    /**
     * @var array
     */
    public $ZIPCodes;

    /**
     * @var array
     */
    public $ZIPAreas = [];

    /**
     * @var array
     */
    public $names;

    /**
     * @var string
     */
    public $priceLocalized;

    /**
     * Surcharge constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->setID($id)
                 ->loadFromDB($id);
        }
    }

    /**
     * @param int $id
     */
    public function loadFromDB(int $id): void
    {
        $db        = Shop::Container()->getDB();
        $surcharge = $db->queryPrepared(
            'SELECT * 
                FROM tversandzuschlag
                WHERE kVersandzuschlag = :id',
            ['id' => $id],
            ReturnType::SINGLE_OBJECT
        );
        if (!\is_object($surcharge)) {
            return;
        }

        $this->setTitle($surcharge->cName)
             ->setISO($surcharge->cISO)
             ->setSurcharge((float)$surcharge->fZuschlag)
             ->setShippingMethod((int)$surcharge->kVersandart)
             ->setPriceLocalized();

        $zips = $db->queryPrepared(
            'SELECT vzp.cPLZ, vzp.cPLZAb, vzp.cPLZBis 
                FROM tversandzuschlag AS vz
                JOIN tversandzuschlagplz AS vzp USING(kVersandzuschlag) 
                WHERE vz.kVersandzuschlag = :id',
            ['id' => $id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($zips as $zip) {
            if (!empty($zip->cPLZ)) {
                $this->setZIPCode($zip->cPLZ);
            } elseif (!empty($zip->cPLZAb) && !empty($zip->cPLZBis)) {
                $this->setZIPArea($zip->cPLZAb, $zip->cPLZBis);
            }
        }

        $names = $db->queryPrepared(
            'SELECT vzs.cName, s.kSprache 
                FROM tversandzuschlag AS vz
                JOIN tversandzuschlagsprache AS vzs USING(kVersandzuschlag) 
                JOIN tsprache as s ON s.cISO = vzs.cISOSprache
                WHERE vz.kVersandzuschlag = :id',
            ['id' => $id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($names as $name) {
            $this->setName($name->cName, (int)$name->kSprache);
        }
    }

    /**
     * update or insert new surcharge
     */
    public function save(): void
    {
        $db                          = Shop::Container()->getDB();
        $surcharge                   = new stdClass();
        $surcharge->cName            = $this->getTitle();
        $surcharge->kVersandart      = $this->getShippingMethod();
        $surcharge->cIso             = $this->getISO();
        $surcharge->fZuschlag        = $this->getSurcharge();
        $surcharge->kVersandzuschlag = $this->getID();

        if (($newInsertID = $db->upsert('tversandzuschlag', $surcharge)) > 0) {
            $this->setID($newInsertID);
        }
        if ($this->getID() > 0) {
            foreach ($this->getNames() as $key => $name) {
                $surchargeLang                   = new stdClass();
                $surchargeLang->cName            = $name;
                $surchargeLang->cISOSprache      = Shop::Lang()->getIsoFromLangID($key)->cISO;
                $surchargeLang->kVersandzuschlag = $this->getID();
                $db->upsert('tversandzuschlagsprache', $surchargeLang);
            }
        }
    }

    /**
     * @param null|string $zip
     * @return bool
     */
    public function hasZIPCode(?string $zip): bool
    {
        if ($zip === null) {
            return false;
        }
        foreach ($this->getZIPCodes() ?? [] as $zipTMP) {
            if ($zip === $zipTMP) {
                return true;
            }
        }

        foreach ($this->getZIPAreas() ?? [] as $zipArea) {
            if ($zipArea->isInArea($zip)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param null|string $zipFrom
     * @param null|string $zipTo
     * @return bool
     */
    public function areaOverlapsWithZIPCode(?string $zipFrom, ?string $zipTo): bool
    {
        if ($zipFrom === null || $zipTo === null) {
            return false;
        }
        $area = new ShippingSurchargeArea($zipFrom, $zipTo);

        foreach ($this->getZIPCodes() ?? [] as $zipTMP) {
            if ($area->isInArea($zipTMP)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return int|null
     */
    public function getID(): ?int
    {
        return $this->ID;
    }

    /**
     * @param int $id
     * @return ShippingSurcharge
     */
    public function setID(int $id): self
    {
        $this->ID = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getISO(): string
    {
        return $this->ISO;
    }

    /**
     * @param string $ISO
     * @return ShippingSurcharge
     */
    public function setISO(string $ISO): self
    {
        $this->ISO = $ISO;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return ShippingSurcharge
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return float
     */
    public function getSurcharge(): float
    {
        return $this->surcharge;
    }

    /**
     * @param float $surcharge
     * @return ShippingSurcharge
     */
    public function setSurcharge(float $surcharge): self
    {
        $this->surcharge = $surcharge;

        return $this;
    }

    /**
     * @return int
     */
    public function getShippingMethod(): int
    {
        return $this->shippingMethod;
    }

    /**
     * @param int $shippingMethod
     * @return ShippingSurcharge
     */
    public function setShippingMethod(int $shippingMethod): self
    {
        $this->shippingMethod = $shippingMethod;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getZIPCodes(): ?array
    {
        return $this->ZIPCodes;
    }

    /**
     * @param array $ZIPCodes
     * @return ShippingSurcharge
     */
    public function setZIPCodes(array $ZIPCodes): self
    {
        $this->ZIPCodes = $ZIPCodes;

        return $this;
    }

    /**
     * @param string $ZIPCode
     * @return ShippingSurcharge
     */
    public function setZIPCode(string $ZIPCode): self
    {
        $this->ZIPCodes[] = $ZIPCode;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getZIPAreas(): ?array
    {
        return $this->ZIPAreas;
    }

    /**
     * @param array $ZIPAreas
     * @return ShippingSurcharge
     */
    public function setZIPAreas(array $ZIPAreas): self
    {
        $this->ZIPAreas = $ZIPAreas;

        return $this;
    }

    /**
     * @param string $ZIPFrom
     * @param string $ZIPTo
     * @return ShippingSurcharge
     */
    public function setZIPArea(string $ZIPFrom, string $ZIPTo): self
    {
        $this->ZIPAreas[] = new ShippingSurchargeArea($ZIPFrom, $ZIPTo);

        return $this;
    }


    /**
     * @param int|null $idx
     * @return string
     */
    public function getName(int $idx = null): string
    {
        $idx = $idx ?? Shop::getLanguageID();

        return $this->names[$idx] ?? '';
    }

    /**
     * @return array
     */
    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * @param string $name
     * @param int|null $idx
     */
    public function setName(string $name, int $idx = null): void
    {
        $this->names[$idx ?? Shop::getLanguageID()] = $name;
    }

    /**
     * @param array $names
     */
    public function setNames(array $names): void
    {
        $this->names = $names;
    }

    /**
     * @return string
     */
    public function getPriceLocalized(): string
    {
        return $this->priceLocalized;
    }

    /**
     * @param string|null $priceLocalized
     * @return ShippingSurcharge
     */
    public function setPriceLocalized(string $priceLocalized = null): self
    {
        $this->priceLocalized = Preise::getLocalizedPriceString($priceLocalized ?? $this->getSurcharge());

        return $this;
    }
}
