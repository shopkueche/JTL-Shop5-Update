<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;
use scc\renderers\NestedBlockRenderer;

/**
 * Class Checkbox
 * @package scc\components
 */
class Checkbox extends AbstractBlockComponent
{
    /**
     * Checkbox constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('checkbox.tpl');
        $this->setName('checkbox');

        $this->addParam(new ComponentProperty('checked', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('required', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('disabled', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('plain', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('button-variant'));
        $this->addParam(new ComponentProperty('name'));
        $this->addParam(new ComponentProperty('size'));
        $this->addParam(new ComponentProperty('value'));

        $this->setRenderer(new NestedBlockRenderer($this));
    }
}
