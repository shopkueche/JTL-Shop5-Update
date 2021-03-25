<?php

namespace JTL\Session\Handler;

/**
 * Class JTLDefault
 * @package JTL\Session\Handler
 */
class JTLDefault extends \SessionHandler implements JTLHandlerInterface
{
    /**
     * @var array|null
     */
    protected $sessionData;

    /**
     * @inheritdoc
     */
    public function setSessionData(&$data): void
    {
        $this->sessionData = &$data;
    }

    /**
     * @inheritdoc
     */
    public function getSessionData(): ?array
    {
        return $this->sessionData;
    }

    /**
     * @inheritdoc
     */
    public function getAll(): ?array
    {
        return $this->getSessionData();
    }

    /**
     * @inheritdoc
     */
    public function get($key, $default = null)
    {
        $array = $this->sessionData;
        if ($key === null) {
            return $array;
        }
        if (isset($array[$key])) {
            return $array[$key];
        }
        foreach (\explode('.', $key) as $segment) {
            if (!\is_array($array) || !\array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * @inheritdoc
     */
    public function set($name, $value)
    {
        return self::array_set($this->sessionData, $name, $value);
    }

    /**
     * @inheritdoc
     */
    public static function array_set(&$array, $key, $value)
    {
        if ($key === null) {
            $array = $value;

            return $array;
        }
        $keys = \explode('.', $key);
        while (\count($keys) > 1) {
            $key = \array_shift($keys);
            if (!isset($array[$key]) || !\is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[\array_shift($keys)] = $value;

        return $array;
    }

    /**
     * @inheritdoc
     */
    public function put($key, $value = null): void
    {
        if (!\is_array($key)) {
            $key = [$key => $value];
        }
        foreach ($key as $arrayKey => $arrayValue) {
            $this->set($arrayKey, $arrayValue);
        }
    }

    /**
     * @inheritdoc
     */
    public function push($key, $value): void
    {
        $array   = $this->get($key, []);
        $array[] = $value;
        $this->put($key, $array);
    }
}
