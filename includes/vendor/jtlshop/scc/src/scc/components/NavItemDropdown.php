<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class NavItemDropdown
 * @package scc\components
 */
class NavItemDropdown extends AbstractBlockComponent
{
    /**
     * NavItemDropdown constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('navitemdropdown.tpl');
        $this->setName('navitemdropdown');

        $this->addParam(new ComponentProperty('text'));
        $this->addParam(new ComponentProperty('tag', 'li'));
        $this->addParam(new ComponentProperty('extra-toggle-classes'));
        $this->addParam(new ComponentProperty('extra-menu-classes'));
        $this->addParam(new ComponentProperty('offset', 0, ComponentPropertyType::TYPE_INT));
        $this->addParam(new ComponentProperty('router-data', null, ComponentPropertyType::TYPE_ARRAY));
        $this->addParam(new ComponentProperty('router-aria', null, ComponentPropertyType::TYPE_ARRAY));
        $this->addParam(new ComponentProperty('dropup', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('disabled', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('right', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('no-flip', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('no-caret', false, ComponentPropertyType::TYPE_BOOL));
    }
}
