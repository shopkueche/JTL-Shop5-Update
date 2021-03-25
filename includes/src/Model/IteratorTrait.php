<?php declare(strict_types=1);

namespace JTL\Model;

/**
 * Trait IteratorTrait
 * @package JTL\Model
 */
trait IteratorTrait
{
    /**
     * @var array
     * Stores keynames for iterator interface
     */
    protected $iteratorKeys;

    /**
     * Add a key to the internal iterator array - this will be used to iterate over all public propertys of this model.
     * Basically the DataModel will only iterate over database attributes. If a persistent class property is defined in
     * descendents, this property must be added if it should be used for iteration. A good place to use this function
     * is {@link onRegisterHandlers}.
     *
     * @param string $keyName - the property/key to add to the list of iteratorkeys
     */
    protected function addIteratorKey($keyName): void
    {
        if (!\in_array($keyName, $this->iteratorKeys, true)) {
            $this->iteratorKeys[] = $keyName;
            \reset($this->iteratorKeys);
        }
    }

    /**
     * @return mixed
     */
    public function current()
    {
        $key = \current($this->iteratorKeys);

        return $this->$key;
    }

    /**
     *
     */
    public function next(): void
    {
        \next($this->iteratorKeys);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return \current($this->iteratorKeys);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return \key($this->iteratorKeys) !== null;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        \reset($this->iteratorKeys);
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $result = [];
        foreach ($this->iteratorKeys as $key) {
            $result[$key] = $this->$key;
        }

        return $result;
    }
}
