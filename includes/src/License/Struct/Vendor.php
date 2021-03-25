<?php declare(strict_types=1);

namespace JTL\License\Struct;

use stdClass;

/**
 * Class Vendor
 * @package JTL\License
 */
class Vendor
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $href;

    /**
     * Vendor constructor.
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
        $this->setName($json->name);
        $this->setHref($json->href);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getHref(): string
    {
        return $this->href;
    }

    /**
     * @param string $href
     */
    public function setHref(string $href): void
    {
        $this->href = $href;
    }
}
