<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class CheckboxGroup
 * @package scc\components
 */
class CheckboxGroup extends AbstractBlockComponent
{
    /**
     * CheckboxGroup constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('checkboxgroup.tpl');
        $this->setName('checkboxgroup');

        $this->addParam(new ComponentProperty('required', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('disabled', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('plain', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('validated', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('stacked', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('buttons', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('name'));
        $this->addParam(new ComponentProperty('size'));
        $this->addParam(new ComponentProperty('value-field', 'value'));
        $this->addParam(new ComponentProperty('text-field', 'text'));
        $this->addParam(new ComponentProperty('disabled-field', 'disabled'));

        $this->addParam(new ComponentProperty('button-variant', 'secondary'));
    }
}
