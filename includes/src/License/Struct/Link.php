<?php declare(strict_types=1);

namespace JTL\License\Struct;

use stdClass;

/**
 * Class Link
 * @package JTL\License
 */
class Link
{
    /**
     * @var string
     */
    private $href;

    /**
     * @var string
     */
    private $rel;

    /**
     * @var string
     */
    private $method = 'GET';

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
        $this->setHref($json->href);
        $this->setRel($json->rel);
        $this->setMethod($json->method ?? 'GET');
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

    /**
     * @return string
     */
    public function getRel(): string
    {
        return $this->rel;
    }

    /**
     * @param string $rel
     */
    public function setRel(string $rel): void
    {
        $this->rel = $rel;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }
}
