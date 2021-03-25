<?php declare(strict_types=1);

namespace JTL\Media\Image;

/**
 * Class Dummy
 * @package JTL\Media\Image
 */
class Dummy extends AbstractImage
{
    /**
     * @param string $request
     * @return bool|mixed|void
     */
    public function handle(string $request)
    {
        return false;
    }
}
