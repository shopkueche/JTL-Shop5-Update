<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Button
 * @package scc\components
 */
class Button extends AbstractBlockComponent
{
    /**
     * Button constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('button.tpl');
        $this->setName('button');

        $this->addParam(new ComponentProperty('type', 'button'));
        $this->addParam(new ComponentProperty('href'));
        $this->addParam(new ComponentProperty('target'));
        $this->addParam(new ComponentProperty('rel'));
        $this->addParam(new ComponentProperty('target'));
        $this->addParam(new ComponentProperty('name'));
        $this->addParam(new ComponentProperty('value'));
        $this->addParam(new ComponentProperty('size'));
        $this->addParam(new ComponentProperty('variant', 'secondary'));
        $this->addParam(new ComponentProperty('disabled', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('block', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('formnovalidate', false, ComponentPropertyType::TYPE_BOOL));
    }
}
