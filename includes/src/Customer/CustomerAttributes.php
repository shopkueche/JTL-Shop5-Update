<?php

namespace JTL\Customer;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JTL\DB\ReturnType;
use JTL\Shop;
use Traversable;

/**
 * Class CustomerAttributes
 * @package JTL\Customer
 */
class CustomerAttributes implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * @var CustomerAttribute[]
     */
    private $attributes = [];

    /**
     * @var int
     */
    private $customerID = 0;

    /**
     * CustomerAttributes constructor.
     * @param int $customerID
     */
    public function __construct(int $customerID = 0)
    {
        if ($customerID > 0) {
            $this->load($customerID);
        } else {
            $this->create();
        }
    }

    /**
     * @param int $customerID
     * @return self
     */
    public function load(int $customerID): self
    {
        $this->attributes = [];
        $this->customerID = $customerID;

        foreach (Shop::Container()->getDB()->queryPrepared(
            'SELECT tkundenattribut.kKundenAttribut, COALESCE(tkundenattribut.kKunde, :customerID) kKunde,
                    tkundenfeld.kKundenfeld, tkundenfeld.cName, tkundenfeld.cWawi, tkundenattribut.cWert,
                    tkundenfeld.nSort,
                    IF(tkundenattribut.kKundenAttribut IS NULL
                        OR COALESCE(tkundenattribut.cWert, \'\') = \'\', 1, tkundenfeld.nEditierbar) nEditierbar
                FROM tkundenfeld
                LEFT JOIN tkundenattribut ON tkundenattribut.kKunde = :customerID
                    AND tkundenattribut.kKundenfeld = tkundenfeld.kKundenfeld
                WHERE tkundenfeld.kSprache = :langID
                ORDER BY tkundenfeld.nSort, tkundenfeld.cName',
            [
                'customerID' => $customerID,
                'langID'     => Shop::getLanguageID(),
            ],
            ReturnType::ARRAY_OF_OBJECTS
        ) as $customerAttribute) {
            $this->attributes[$customerAttribute->kKundenfeld] = new CustomerAttribute($customerAttribute);
        }

        return $this;
    }

    /**
     * @return self
     */
    public function save(): self
    {
        $nonEditables = (new CustomerFields())->getNonEditableFields();
        $usedIDs      = [];

        /** @var CustomerAttribute $attribute */
        foreach ($this as $attribute) {
            if ($attribute->isEditable()) {
                $attribute->save();
                $usedIDs[] = $attribute->getID();
            } else {
                $this->attributes[$attribute->getCustomerFieldID()] = CustomerAttribute::load($attribute->getID());
            }
        }

        Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tkundenattribut
                WHERE kKunde = :customerID' . (\count($nonEditables) > 0
                ? ' AND kKundenfeld NOT IN (' . \implode(', ', $nonEditables) . ')' : '') . (\count($usedIDs) > 0
                ? ' AND kKundenAttribut NOT IN (' . \implode(', ', $usedIDs) . ')' : ''),
            [
                'customerID' => $this->customerID,
            ],
            ReturnType::DEFAULT
        );

        return $this;
    }

    /**
     * @param CustomerAttributes $customerAttributes
     * @return self
     */
    public function assign(CustomerAttributes $customerAttributes): self
    {
        $this->attributes = [];

        /** @var CustomerAttribute $customerAttribute */
        foreach ($customerAttributes as $customerAttribute) {
            $record                                 = $customerAttribute->getRecord();
            $this->attributes[$record->kKundenfeld] = new CustomerAttribute($record);
        }

        return $this->sort();
    }

    /**
     * @return self
     */
    public function create(): self
    {
        $this->attributes = [];
        $customerFields   = new CustomerFields();

        /** @var CustomerField $customerField */
        foreach ($customerFields as $customerField) {
            $attribute = new CustomerAttribute();
            $attribute->setName($customerField->getName());
            $attribute->setCustomerFieldID($customerField->getID());
            $attribute->setEditable(true);
            $attribute->setLabel($customerField->getLabel());
            $attribute->setOrder($customerField->getOrder());

            $this->attributes[$customerField->getID()] = $attribute;
        }

        return $this->sort();
    }

    /**
     * @return int
     */
    public function getCustomerID(): int
    {
        return $this->customerID;
    }

    /**
     * @param int $customerID
     */
    public function setCustomerID(int $customerID): void
    {
        $this->customerID = $customerID;

        foreach ($this->attributes as $attribute) {
            $attribute->setCustomerID($customerID);
        }
    }

    /**
     * @return self
     */
    public function sort(): self
    {
        \uasort($this->attributes, static function (CustomerAttribute $lft, CustomerAttribute $rgt): int {
            if ($lft->getOrder() === $rgt->getOrder()) {
                return \strcmp($lft->getName(), $rgt->getName());
            }

            return $lft->getOrder() < $rgt->getOrder() ? -1 : 1;
        });

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->attributes);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return \array_key_exists($offset, $this->attributes);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        if (!isset($this->attributes[$offset])) {
            return null;
        }

        if (!\is_a($this->attributes[$offset], CustomerAttribute::class)) {
            $this->attributes[$offset] = new CustomerAttribute($this->attributes[$offset]);
        }

        return $this->attributes[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        if (\is_a($value, CustomerAttribute::class)) {
            $this->attributes[$offset] = $value;
        } elseif (\is_object($value)) {
            $this->attributes[$offset] = new CustomerAttribute($value);
        } else {
            throw new \InvalidArgumentException(
                self::class . '::' . __METHOD__ . ' - value must be an object, ' . \gettype($value) . ' given.'
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return \count($this->attributes);
    }
}
