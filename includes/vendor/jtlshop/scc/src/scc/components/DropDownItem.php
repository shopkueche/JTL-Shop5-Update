<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class DropDownItem
 * @package scc\components
 */
class DropDownItem extends AbstractBlockComponent
{
    /**
     * DropDownItem constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('dropdownitem.tpl');
        $this->setName('dropdownitem');

        $this->addParam(new ComponentProperty('target', '_self'));
        $this->addParam(new ComponentProperty('variant'));
        $this->addParam(new ComponentProperty('href'));
        $this->addParam(new ComponentProperty('rel'));
        $this->addParam(new ComponentProperty('activeClass', 'active'));
        $this->addParam(new ComponentProperty('tag', 'a'));
        $this->addParam(new ComponentProperty('active', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('disabled', false, ComponentPropertyType::TYPE_BOOL));
    }
}
