<?php

namespace JTL;

/**
 * Trait MagicCompatibilityTrait
 *
 * allows a backwards compatable access to class properties
 * that are now hidden behind getters and setters via a simple list of mappings
 *
 */
trait MagicCompatibilityTrait
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param string $value
     * @return string|array|null
     */
    private static function getMapping($value)
    {
        return self::$mapping[$value] ?? null;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        \trigger_error(__CLASS__ . ': getter should be used to get ' . $name, \E_USER_DEPRECATED);
        if (\property_exists($this, $name)) {
            return $this->$name;
        }
        if (($mapped = self::getMapping($name)) !== null) {
            if (\is_array($mapped) && \count($mapped) === 2) {
                $method1 = $mapped[0];
                $method2 = 'get' . $mapped[1];

                return \call_user_func([$this->$method1(), $method2]);
            }
            $method = 'get' . $mapped;

            return $this->$method();
        }

        return $this->data[$name] ?? null;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return $this
     */
    public function __set($name, $value)
    {
        \trigger_error(__CLASS__ . ': setter should be used to set ' . $name, \E_USER_DEPRECATED);
        if (\property_exists($this, $name)) {
            $this->$name = $value;

            return $this;
        }
        if (($mapped = self::getMapping($name)) !== null) {
            if (\is_array($mapped) && \count($mapped) === 2) {
                $method1 = $mapped[0];
                $method2 = 'set' . $mapped[1];

                return \call_user_func([$this->$method1(), $method2], $value);
            }
            $method = 'set' . $mapped;

            return $this->$method($value);
        }
        \trigger_error(__CLASS__ . ': setter could not find property ' . $name, \E_USER_DEPRECATED);
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return \property_exists($this, $name) || self::getMapping($name) !== null;
    }
}
