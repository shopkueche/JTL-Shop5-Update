<?php

namespace JTL\Services\JTL\Validation;

/**
 * Interface ValueCarrierInterface
 * @package JTL\Services\JTL\Validation
 */
interface ValueCarrierInterface
{
    /**
     * @param mixed|null $default The default value that is returned, if the value is invalid or does not exist
     * @return mixed
     */
    public function getValue($default = null);

    /**
     * @param mixed $value
     * @return void
     */
    public function setValue($value): void;

    /**
     * Get the untransformed value (e.g. to redisplay the incorrect value to the user)
     *
     * @return mixed
     */
    public function getValueInsecure();
}
