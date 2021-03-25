<?php

namespace JTL\Customer;

use JTL\DB\ReturnType;
use JTL\MagicCompatibilityTrait;
use JTL\Shop;
use stdClass;

/**
 * Class CustomerAttribute
 * @package JTL\Customer
 */
class CustomerAttribute
{
    use MagicCompatibilityTrait;

    /** @var int */
    private $id = 0;

    /** @var int */
    private $customerID = 0;

    /** @var int */
    private $customerFieldID = 0;

    /** @var string */
    private $label = '';

    /** @var string */
    private $name = '';

    /** @var string */
    private $value = '';

    /** @var int */
    private $order = 0;

    /** @var bool */
    private $editable = true;

    /** @var array */
    public static $mapping = [
        'kKundenAttribut' => 'ID',
        'kKunde'          => 'CustomerID',
        'kKundenfeld'     => 'CustomerFieldID',
        'cName'           => 'Label',
        'cWawi'           => 'Name',
        'cWert'           => 'Value',
        'nSort'           => 'Order',
        'nEditierbar'     => 'Editable',
    ];

    /**
     * CustomerAttribute constructor.
     * @param object|null $record
     */
    public function __construct(?object $record = null)
    {
        $this->setRecord($record);
    }

    /**
     * @param int $id
     * @return self
     */
    public static function load(int $id): self
    {
        $instance = new self();
        $instance->setRecord(Shop::Container()->getDB()->queryPrepared(
            'SELECT tkundenattribut.kKundenAttribut, tkundenattribut.kKunde, tkundenattribut.kKundenfeld,
                   tkundenfeld.cName, tkundenfeld.cWawi, tkundenattribut.cWert, tkundenfeld.nSort,
                   IF(COALESCE(tkundenattribut.cWert, \'\') = \'\', 1, tkundenfeld.nEditierbar) nEditierbar
                FROM tkundenattribut
                INNER JOIN tkundenfeld ON tkundenfeld.kKundenfeld = tkundenattribut.kKundenfeld
                WHERE tkundenattribut.kKundenAttribut = :id',
            [
                'id' => $id,
            ],
            ReturnType::SINGLE_OBJECT
        ));

        return $instance;
    }

    /**
     * @return self
     */
    public function save(): self
    {
        $record        = $this->getRecord();
        $record->cName = $record->cWawi;
        unset(
            $record->cWawi,
            $record->nSort,
            $record->nEditierbar
        );

        if ($record->kKundenAttribut === 0) {
            unset($record->kKundenAttribut);
        }
        $res = Shop::Container()->getDB()->upsert(
            'tkundenattribut',
            $record,
            ['kKundenAttribut', 'kKunde', 'kKundenfeld']
        );

        if ($res > 0) {
            $this->setID($res);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setID(?int $id): void
    {
        $this->id = $id ?? 0;
    }

    /**
     * @return int
     */
    public function getCustomerID(): int
    {
        return $this->customerID;
    }

    /**
     * @param int|null $customerID
     */
    public function setCustomerID(?int $customerID): void
    {
        $this->customerID = $customerID ?? 0;
    }

    /**
     * @return int
     */
    public function getCustomerFieldID(): int
    {
        return $this->customerFieldID;
    }

    /**
     * @param int $customerFieldID
     */
    public function setCustomerFieldID(int $customerFieldID): void
    {
        $this->customerFieldID = $customerFieldID;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value ?? '';
    }

    /**
     * @param string|null $value
     */
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder(int $order): void
    {
        $this->order = $order;
    }

    /**
     * @return bool
     */
    public function isEditable(): bool
    {
        return $this->editable;
    }

    /**
     * @return int
     */
    public function getEditable(): int
    {
        return $this->editable ? 1 : 0;
    }

    /**
     * @param int|bool $editable
     */
    public function setEditable($editable): void
    {
        $this->editable = $editable ? true : false;
    }

    /**
     * @param object|array|null $record
     * @return CustomerAttribute
     */
    public function setRecord($record): self
    {
        if (!\is_object($record) && !\is_array($record)) {
            $this->setID(0);
            $this->setCustomerFieldID(0);
            $this->setCustomerID(0);
            $this->setLabel('');
            $this->setName('');
            $this->setValue('');
            $this->setOrder(0);
            $this->setEditable(true);

            return $this;
        }

        foreach ($record as $item => $value) {
            if (($mapped = self::getMapping($item)) !== null) {
                $method = 'set' . $mapped;

                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * @return object
     */
    public function getRecord(): object
    {
        $result = new stdClass();

        foreach (self::$mapping as $item => $mapped) {
            $method        = 'get' . $mapped;
            $result->$item = $this->$method();
        }

        return $result;
    }
}
