<?php

namespace JTL;

/**
 * Interface IExtensionPoint
 * @package JTL
 */
interface IExtensionPoint
{
    /**
     * @param int $id
     * @return mixed
     */
    public function init($id);
}
