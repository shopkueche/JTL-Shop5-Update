<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class DropDown
 * @package scc\components
 */
class DropDown extends AbstractBlockComponent
{
    /**
     * DropDown constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('dropdown.tpl');
        $this->setName('dropdown');

        $this->addParam(new ComponentProperty('disabled', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('right', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('split', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('is-nav', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('dropup', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('no-caret', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('no-flip', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('offset', 0, ComponentPropertyType::TYPE_NUMERIC));
        $this->addParam(new ComponentProperty('variant', 'secondary'));
        $this->addParam(new ComponentProperty('toggleText', 'Toggle dropdown'));
        $this->addParam(new ComponentProperty('toggle-class'));
        $this->addParam(new ComponentProperty('size'));
        $this->addParam(new ComponentProperty('text', ''));
    }
}
