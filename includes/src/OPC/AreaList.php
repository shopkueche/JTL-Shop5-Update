<?php declare(strict_types=1);

namespace JTL\OPC;

/**
 * Class AreaList
 * @package JTL\OPC
 */
class AreaList implements \JsonSerializable
{
    /**
     * @var Area[]
     */
    protected $areas = [];

    /**
     * @return $this
     */
    public function clear(): self
    {
        $this->areas = [];

        return $this;
    }

    /**
     * @param Area $area
     * @return $this
     */
    public function putArea(Area $area): self
    {
        $this->areas[$area->getId()] = $area;

        return $this;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function hasArea($id): bool
    {
        return \array_key_exists($id, $this->areas);
    }

    /**
     * @param string $id
     * @return Area
     */
    public function getArea($id): Area
    {
        return $this->areas[$id];
    }

    /**
     * @return Area[]
     */
    public function getAreas(): array
    {
        return $this->areas;
    }

    /**
     * @return string[] the rendered HTML content of this page
     */
    public function getPreviewHtml(): array
    {
        $result = [];
        foreach ($this->areas as $id => $area) {
            $result[$id] = $area->getPreviewHtml();
        }

        return $result;
    }

    /**
     * @return array
     * @throws \Exception
     * @return string[] the rendered HTML content of this page
     */
    public function getFinalHtml(): array
    {
        $result = [];
        foreach ($this->areas as $id => $area) {
            $result[$id] = $area->getFinalHtml();
        }

        return $result;
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    public function deserialize(array $data): void
    {
        $this->clear();

        foreach ($data as $areaData) {
            $area = (new Area())->deserialize($areaData);
            $this->putArea($area);
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $res = [];
        foreach ($this->areas as $id => $area) {
            $res[$id] = $area->jsonSerialize();
        }

        return $res;
    }
}
