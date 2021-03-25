<?php

namespace JTL\Customer;

use JTL\DB\ReturnType;
use JTL\MagicCompatibilityTrait;
use JTL\Shop;
use stdClass;

/**
 * Class CustomerGroup
 * @package JTL\Customer
 */
class CustomerGroup
{
    use MagicCompatibilityTrait;

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var float
     */
    protected $discount = 0.0;

    /**
     * @var string
     */
    protected $default;

    /**
     * @var string
     */
    protected $cShopLogin;

    /**
     * @var int
     */
    protected $isMerchant = 0;

    /**
     * @var int
     */
    protected $mayViewPrices = 1;

    /**
     * @var int
     */
    protected $mayViewCategories = 1;

    /**
     * @var int
     */
    protected $languageID = 0;

    /**
     * @var array
     */
    protected $Attribute;

    /**
     * @var string
     */
    private $nameLocalized;

    /**
     * @var array
     */
    protected static $mapping = [
        'kKundengruppe'              => 'ID',
        'kSprache'                   => 'LanguageID',
        'nNettoPreise'               => 'IsMerchant',
        'darfPreiseSehen'            => 'MayViewPrices',
        'darfArtikelKategorienSehen' => 'MayViewCategories',
        'cName'                      => 'Name',
        'cStandard'                  => 'Default',
        'fRabatt'                    => 'Discount',
        'cNameLocalized'             => 'nameLocalized'
    ];

    /**
     * CustomerGroup constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @return $this
     */
    public function loadDefaultGroup(): self
    {
        $item = Shop::Container()->getDB()->select('tkundengruppe', 'cStandard', 'Y');
        if ($item !== null) {
            $conf = Shop::getSettings([\CONF_GLOBAL]);
            $this->setID((int)$item->kKundengruppe)
                 ->setName($item->cName)
                 ->setDiscount($item->fRabatt)
                 ->setDefault($item->cStandard)
                 ->setShopLogin($item->cShopLogin)
                 ->setIsMerchant((int)$item->nNettoPreise);
            if ($this->isDefault()) {
                if ((int)$conf['global']['global_sichtbarkeit'] === 2) {
                    $this->mayViewPrices = 0;
                } elseif ((int)$conf['global']['global_sichtbarkeit'] === 3) {
                    $this->mayViewPrices     = 0;
                    $this->mayViewCategories = 0;
                }
            }
            $this->localize()->initAttributes();
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function localize(): self
    {
        if ($this->id > 0 && $this->languageID > 0) {
            $localized = Shop::Container()->getDB()->select(
                'tkundengruppensprache',
                'kKundengruppe',
                (int)$this->id,
                'kSprache',
                (int)$this->languageID
            );
            if (isset($localized->cName)) {
                $this->nameLocalized = $localized->cName;
            }
        }

        return $this;
    }

    /**
     * @param int $id
     * @return $this
     */
    private function loadFromDB(int $id = 0): self
    {
        $item = Shop::Container()->getDB()->select('tkundengruppe', 'kKundengruppe', $id);
        if (isset($item->kKundengruppe) && $item->kKundengruppe > 0) {
            $this->setID((int)$item->kKundengruppe)
                 ->setName($item->cName)
                 ->setDiscount($item->fRabatt)
                 ->setDefault($item->cStandard)
                 ->setShopLogin($item->cShopLogin)
                 ->setIsMerchant((int)$item->nNettoPreise);
        }

        return $this;
    }

    /**
     * @param bool $primary
     * @return bool|int
     */
    public function save(bool $primary = true)
    {
        $ins               = new stdClass();
        $ins->cName        = $this->name;
        $ins->fRabatt      = $this->discount;
        $ins->cStandard    = \mb_convert_case($this->default, \MB_CASE_UPPER);
        $ins->cShopLogin   = $this->cShopLogin;
        $ins->nNettoPreise = (int)$this->isMerchant;
        $kPrim             = Shop::Container()->getDB()->insert('tkundengruppe', $ins);
        if ($kPrim > 0) {
            return $primary ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $upd               = new stdClass();
        $upd->cName        = $this->name;
        $upd->fRabatt      = $this->discount;
        $upd->cStandard    = $this->default;
        $upd->cShopLogin   = $this->cShopLogin;
        $upd->nNettoPreise = $this->isMerchant;

        return Shop::Container()->getDB()->update('tkundengruppe', 'kKundengruppe', (int)$this->id, $upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tkundengruppe', 'kKundengruppe', (int)$this->id);
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setID(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param int $id
     * @return $this
     * @deprecated since 4.06
     */
    public function setKundengruppe(int $id): self
    {
        \trigger_error(__METHOD__ . ' is deprecated - use setID() instead', \E_USER_DEPRECATED);
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param float $fRabatt
     * @return $this
     * @deprecated since 4.06
     */
    public function setRabatt($fRabatt): self
    {
        \trigger_error(__METHOD__ . ' is deprecated - use setDiscount() instead', \E_USER_DEPRECATED);

        return $this->setDiscount($fRabatt);
    }

    /**
     * @param float $discount
     * @return $this
     */
    public function setDiscount($discount): self
    {
        $this->discount = (float)$discount;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @param string $cStandard
     * @return $this
     * @deprecated since 4.06
     */
    public function setStandard($cStandard): self
    {
        \trigger_error(__METHOD__ . ' is deprecated - use setDefault() instead', \E_USER_DEPRECATED);

        return $this->setDefault($cStandard);
    }

    /**
     * @param string $default
     * @return $this
     */
    public function setDefault($default): self
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @param string $cShopLogin
     * @return $this
     */
    public function setShopLogin($cShopLogin): self
    {
        $this->cShopLogin = $cShopLogin;

        return $this;
    }

    /**
     * @param int $nNettoPreise
     * @return $this
     */
    public function setNettoPreise($nNettoPreise): self
    {
        \trigger_error(__METHOD__ . ' is deprecated - use setIsMerchant() instead', \E_USER_DEPRECATED);

        return $this->setIsMerchant($nNettoPreise);
    }

    /**
     * @param int $is
     * @return $this
     */
    public function setIsMerchant(int $is): self
    {
        $this->isMerchant = $is;

        return $this;
    }

    /**
     * @param int $n
     * @return $this
     */
    public function setMayViewPrices(int $n): self
    {
        $this->mayViewPrices = $n;

        return $this;
    }

    /**
     * @return bool
     */
    public function mayViewPrices(): bool
    {
        return (int)$this->mayViewPrices === 1;
    }

    /**
     * @return int
     */
    public function getMayViewPrices(): int
    {
        return $this->mayViewPrices;
    }

    /**
     * @param int $n
     * @return $this
     */
    public function setMayViewCategories(int $n): self
    {
        $this->mayViewCategories = $n;

        return $this;
    }

    /**
     * @return int
     */
    public function getMayViewCategories(): int
    {
        return $this->mayViewCategories;
    }

    /**
     * @return bool
     */
    public function mayViewCategories(): bool
    {
        return (int)$this->mayViewCategories === 1;
    }

    /**
     * @return int
     * @deprecated since 4.06
     */
    public function getKundengruppe(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated - use getID() instead', \E_USER_DEPRECATED);

        return $this->getID();
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return float
     * @deprecated since 4.06
     */
    public function getRabatt(): float
    {
        \trigger_error(__METHOD__ . ' is deprecated - use getDiscount() instead', \E_USER_DEPRECATED);

        return $this->getDiscount();
    }

    /**
     * @return string|null
     */
    public function getStandard(): ?string
    {
        \trigger_error(__METHOD__ . ' is deprecated - use getDefault() instead', \E_USER_DEPRECATED);

        return $this->getIsDefault();
    }

    /**
     * @return string|null
     */
    public function getIsDefault(): ?string
    {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->default === 'Y';
    }

    /**
     * @return string|null
     */
    public function getShopLogin(): ?string
    {
        return $this->cShopLogin;
    }

    /**
     * @return int
     */
    public function getIsMerchant(): int
    {
        return $this->isMerchant;
    }

    /**
     * @return bool
     */
    public function isMerchant(): bool
    {
        return $this->isMerchant > 0;
    }

    /**
     * @return int
     */
    public function getNettoPreise(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated - use getIsMerchant() instead', \E_USER_DEPRECATED);

        return $this->getIsMerchant();
    }

    /**
     * Static helper
     *
     * @return CustomerGroup[]
     */
    public static function getGroups(): array
    {
        $groups = [];
        $items  = Shop::Container()->getDB()->query(
            'SELECT kKundengruppe 
                FROM tkundengruppe',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($items as $item) {
            if (isset($item->kKundengruppe) && $item->kKundengruppe > 0) {
                $groups[] = new self((int)$item->kKundengruppe);
            }
        }

        return $groups;
    }

    /**
     * @return stdClass|false
     */
    public static function getDefault()
    {
        return Shop::Container()->getDB()->select('tkundengruppe', 'cStandard', 'Y');
    }

    /**
     * @return int
     */
    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    /**
     * @param int $languageID
     * @return $this
     */
    public function setLanguageID($languageID): self
    {
        $this->languageID = (int)$languageID;

        return $this;
    }

    /**
     * @return int
     */
    public static function getCurrent(): int
    {
        $id = 0;
        if (isset($_SESSION['Kundengruppe']->kKundengruppe)) {
            $id = $_SESSION['Kundengruppe']->getID();
        } elseif (isset($_SESSION['Kunde']->kKundengruppe)) {
            $id = $_SESSION['Kunde']->kKundengruppe;
        }

        return (int)$id;
    }

    /**
     * @return int
     */
    public static function getDefaultGroupID(): int
    {
        if (isset($_SESSION['Kundengruppe'])
            && $_SESSION['Kundengruppe'] instanceof self
            && $_SESSION['Kundengruppe']->getID() > 0
        ) {
            return $_SESSION['Kundengruppe']->getID();
        }
        $customerGroup = self::getDefault();
        if (isset($customerGroup->kKundengruppe) && $customerGroup->kKundengruppe > 0) {
            return (int)$customerGroup->kKundengruppe;
        }

        return 0;
    }

    /**
     * @param int $id
     * @return CustomerGroup|stdClass
     */
    public static function reset(int $id)
    {
        if (isset($_SESSION['Kundengruppe'])
            && $_SESSION['Kundengruppe'] instanceof self
            && $_SESSION['Kundengruppe']->getID() === $id
        ) {
            return $_SESSION['Kundengruppe'];
        }
        $item = new stdClass();
        if (!$id) {
            $id = self::getDefaultGroupID();
        }
        if ($id > 0) {
            $item = new self($id);
            if ($item->getID() > 0 && !isset($_SESSION['Kundengruppe'])) {
                $item->setMayViewPrices(1)->setMayViewCategories(1);
                $conf = Shop::getSettings([\CONF_GLOBAL]);
                if ((int)$conf['global']['global_sichtbarkeit'] === 2) {
                    $item->setMayViewPrices(0);
                }
                if ((int)$conf['global']['global_sichtbarkeit'] === 3) {
                    $item->setMayViewPrices(0)->setMayViewCategories(0);
                }
                $_SESSION['Kundengruppe'] = $item->initAttributes();
            }
        }

        return $item;
    }

    /**
     * @return $this
     */
    public function initAttributes(): self
    {
        if ($this->id > 0) {
            $this->Attribute = [];
            $attributes      = Shop::Container()->getDB()->selectAll(
                'tkundengruppenattribut',
                'kKundengruppe',
                (int)$this->id
            );
            foreach ($attributes as $attribute) {
                $this->Attribute[\mb_convert_case($attribute->cName, \MB_CASE_LOWER)] = $attribute->cWert;
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAttributes(): bool
    {
        return $this->Attribute !== null;
    }

    /**
     * @param string $attributeName
     * @return mixed|null
     */
    public function getAttribute($attributeName)
    {
        return $this->Attribute[$attributeName] ?? null;
    }

    /**
     * @param int $id
     * @return array
     * @deprecated since 4.06
     */
    public static function getAttributes(int $id): array
    {
        $attributes = [];
        if ($id > 0) {
            $attributes = Shop::Container()->getDB()->selectAll(
                'tkundengruppenattribut',
                'kKundengruppe',
                $id
            );
            foreach ($attributes as $Att) {
                $attributes[\mb_convert_case($Att->cName, \MB_CASE_LOWER)] = $Att->cWert;
            }
        }

        return $attributes;
    }

    /**
     * @param int $id
     * @return null|string
     */
    public static function getNameByID(int $id): ?string
    {
        $cgroup = new self();
        $cgroup->loadFromDB($id);

        return $cgroup->getName();
    }
}
