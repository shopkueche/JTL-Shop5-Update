<?php declare(strict_types=1);

namespace JTL\Boxes\Items;

use JTL\Helpers\Manufacturer as ManufacturerHelper;

/**
 * Class Manufacturer
 *
 * @package JTL\Boxes\Items
 */
final class Manufacturer extends AbstractBox
{
    /**
     * @var array
     */
    private $manufacturerList;

    /**
     * Manufacturer constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->addMapping('manufacturers', 'Manufacturers');
        $this->setManufacturers(ManufacturerHelper::getInstance()->getManufacturers());
        $this->setShow(\count($this->manufacturerList) > 0);
    }

    /**
     * @return array
     */
    public function getManufacturers(): array
    {
        return $this->manufacturerList;
    }

    /**
     * @param array $manufacturers
     */
    public function setManufacturers(array $manufacturers): void
    {
        $this->manufacturerList = $manufacturers;
    }
}
