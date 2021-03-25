<?php

namespace JTL\Customer;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JTL\DB\ReturnType;
use JTL\Shop;
use Traversable;
use function Functional\map;
use function Functional\select;

/**
 * Class CustomerFields
 * @package JTL\Customer
 */
class CustomerFields implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * @var CustomerFields[][]
     */
    private static $fields = [];

    /**
     * @var int
     */
    private $langID = 0;

    /**
     * CustomerFields constructor.
     * @param int $langID
     */
    public function __construct(int $langID = 0)
    {
        if ($langID === 0) {
            $langID = Shop::getLanguageID();
        }

        if ($langID > 0) {
            $this->load($langID);
        }
    }

    /**
     * @param int $langID
     * @return CustomerFields
     */
    public function load(int $langID): self
    {
        $this->langID = $langID;
        if (!isset(self::$fields[$langID])) {
            self::$fields[$langID] = Shop::Container()->getDB()->queryPrepared(
                'SELECT kKundenfeld, kSprache, cName, cWawi, cTyp, nSort, nPflicht, nEditierbar
                    FROM tkundenfeld
                    WHERE kSprache = :langID
                    ORDER BY nSort',
                ['langID' => $langID],
                ReturnType::COLLECTION
            )->mapInto(CustomerField::class)->keyBy(static function (CustomerField $field) {
                return $field->getID();
            })->toArray();
        }

        return $this;
    }

    /**
     * @return CustomerFields[]
     */
    public function getFields(): array
    {
        return self::$fields[$this->langID] ?? [];
    }

    /**
     * @return array
     */
    public function getNonEditableFields(): array
    {
        return map(select($this->getFields(), static function (CustomerField $e) {
            return !$e->isEditable();
        }), static function (CustomerField $e) {
            return $e->getID();
        });
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->getFields());
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return \array_key_exists($offset, $this->getFields());
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        $fields = $this->getFields();
        if (!isset($fields[$offset])) {
            return null;
        }

        if (!\is_a($fields[$offset], CustomerField::class)) {
            $fields[$offset] = new CustomerField($fields[$offset]);
        }

        return $fields[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        if (\is_a($value, CustomerField::class)) {
            self::$fields[$this->langID][$offset] = $value;
        } elseif (\is_object($value)) {
            self::$fields[$this->langID][$offset] = new CustomerField($value);
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
        unset(self::$fields[$this->langID][$offset]);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return \count($this->getFields());
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            'langID' => $this->langID,
            'fields' => $this->getFields(),
        ];
    }
}
