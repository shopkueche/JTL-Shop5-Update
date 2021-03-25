<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Nav
 * @package scc\components
 */
class Nav extends AbstractBlockComponent
{
    /**
     * Nav constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('nav.tpl');
        $this->setName('nav');

        $this->addParam(new ComponentProperty('tag', 'ul'));
        $this->addParam(new ComponentProperty('fill', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('justified', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('tabs', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('pills', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('vertical', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('is-nav-bar', false, ComponentPropertyType::TYPE_BOOL));
    }
}
