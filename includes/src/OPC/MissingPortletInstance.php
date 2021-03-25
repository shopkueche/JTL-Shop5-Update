<?php declare(strict_types=1);

namespace JTL\OPC;

use JTL\OPC\Portlets\MissingPortlet\MissingPortlet;

/**
 * Class MissingPortletInstance
 * @package JTL\OPC
 */
class MissingPortletInstance extends PortletInstance
{
    /**
     * @var string
     */
    protected $missingClass = '';

    /**
     * @param MissingPortlet $portlet
     * @param string         $missingClass
     */
    public function __construct(MissingPortlet $portlet, string $missingClass)
    {
        parent::__construct($portlet);
        $this->setMissingClass($missingClass);
    }

    /**
     * @return string
     */
    public function getMissingClass(): string
    {
        return $this->missingClass;
    }

    /**
     * @param string $missingClass
     * @return $this
     */
    public function setMissingClass(string $missingClass): self
    {
        $this->missingClass = $missingClass;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerializeShort()
    {
        $result                 = parent::jsonSerializeShort();
        $result['missingClass'] = $this->getMissingClass();

        return $result;
    }
}
