<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Table
 * @package scc\components
 */
class Table extends AbstractFunctionComponent
{
    /**
     * Table constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('table.tpl');
        $this->setName('table');

        $this->addParam(new ComponentProperty('title-item-class'));
        $this->addParam(new ComponentProperty('title-link-class'));
        $this->addParam(new ComponentProperty('href', '#'));
        $this->addParam(new ComponentProperty('caption', null, ComponentPropertyType::TYPE_STRING));
        $this->addParam(new ComponentProperty('responsive', null, ComponentPropertyType::TYPE_STRING));
        $this->addParam(new ComponentProperty('fields', [], ComponentPropertyType::TYPE_ARRAY));
        $this->addParam(new ComponentProperty('items', [], ComponentPropertyType::TYPE_ARRAY));
        $this->addParam(new ComponentProperty('striped', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('bordered', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('borderless', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('outlined', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('small', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('hover', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('fixed', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('dark', false, ComponentPropertyType::TYPE_BOOL));
    }
}
