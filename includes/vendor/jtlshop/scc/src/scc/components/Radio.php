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
 * Class Radio
 * @package scc\components
 */
class Radio extends AbstractBlockComponent
{
    /**
     * Radio constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('radio.tpl');
        $this->setName('radio');

        $this->addParam(new ComponentProperty('disabled', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('checked', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('required', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('value'));
        $this->addParam(new ComponentProperty('button-variant'));
        $this->addParam(new ComponentProperty('name'));

        $this->setRenderer(new NestedBlockRenderer($this));
    }
}
