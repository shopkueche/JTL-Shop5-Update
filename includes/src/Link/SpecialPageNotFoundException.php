<?php declare(strict_types=1);

namespace JTL\Link;

use RuntimeException;

/**
 * Class SpecialPageNotFoundException
 * @package JTL\Link
 */
class SpecialPageNotFoundException extends RuntimeException
{
    /**
     * SpecialPageNotFoundException constructor.
     * @param int $linkType
     */
    public function __construct(int $linkType)
    {
        parent::__construct('Special page for link type ' . $linkType . ' could not be found.', 404);
    }
}
