<?php declare(strict_types=1);

namespace JTL\OPC;

use JTL\Shop;

/**
 * Class Blueprint
 * @package JTL\OPC
 */
class Blueprint implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var null|PortletInstance
     */
    protected $instance;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return PortletInstance|null
     */
    public function getInstance(): ?PortletInstance
    {
        return $this->instance;
    }

    /**
     * @param PortletInstance|null $instance
     * @return $this;
     */
    public function setInstance($instance): self
    {
        $this->instance = $instance;

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     * @throws \Exception
     */
    public function deserialize(array $data): self
    {
        $this->setName($data['name']);
        $instance = Shop::Container()->getOPC()->getPortletInstance($data['content']);
        $this->setInstance($instance);

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id'       => $this->getId(),
            'name'     => $this->getName(),
            'instance' => $this->instance->jsonSerialize(),
        ];
    }
}
