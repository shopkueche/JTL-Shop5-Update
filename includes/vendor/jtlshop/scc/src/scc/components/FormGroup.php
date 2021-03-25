<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class FormGroup
 * @package scc\components
 */
class FormGroup extends AbstractBlockComponent
{
    /**
     * FormGroup constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('formgroup.tpl');
        $this->setName('formgroup');

        $this->addParam(new ComponentProperty('breakpoint', 'sm'));
        $this->addParam(new ComponentProperty('label-text-align'));
        $this->addParam(new ComponentProperty('label'));
        $this->addParam(new ComponentProperty('label-for'));
        $this->addParam(new ComponentProperty('label-size'));
        $this->addParam(new ComponentProperty('label-class'));
        $this->addParam(new ComponentProperty('description'));
        $this->addParam(new ComponentProperty('label-sr-only', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('horizontal', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('label-cols', 3, ComponentPropertyType::TYPE_INT));
    }
}
