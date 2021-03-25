<?php declare(strict_types=1);

namespace JTL\License\Struct;

use stdClass;

/**
 * Class Releases
 * @package JTL\License\Struct
 */
class Releases
{
    /**
     * @var Release|null
     */
    private $latest;

    /**
     * @var Release|null
     */
    private $available;

    /**
     * Link constructor.
     * @param stdClass|null $json
     */
    public function __construct(?stdClass $json = null)
    {
        if ($json !== null) {
            $this->fromJSON($json);
        }
    }

    /**
     * @param stdClass $json
     */
    public function fromJSON(stdClass $json): void
    {
        $this->setAvailable($this->createRelease($json->available ?? null));
        $this->setLatest($this->createRelease($json->latest ?? null));
    }

    /**
     * @param stdClass|null $data
     * @return Release|null
     */
    private function createRelease(stdClass $data = null): ?Release
    {
        if ($data === null) {
            return null;
        }

        return new Release($data);
    }

    /**
     * @return Release|null
     */
    public function getLatest(): ?Release
    {
        return $this->latest;
    }

    /**
     * @param Release|null $latest
     */
    public function setLatest(?Release $latest): void
    {
        $this->latest = $latest;
    }

    /**
     * @return Release|null
     */
    public function getAvailable(): ?Release
    {
        return $this->available;
    }

    /**
     * @param Release|null $available
     */
    public function setAvailable(?Release $available): void
    {
        $this->available = $available;
    }
}
