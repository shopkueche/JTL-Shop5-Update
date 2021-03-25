<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;
use scc\renderers\TabsRenderer;

/**
 * Class Tabs
 * @package scc\components
 */
class Tabs extends AbstractBlockComponent
{
    /**
     * Tabs constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('tabs.tpl');
        $this->setName('tabs');
        $this->addParam(new ComponentProperty('tag', 'ul'));
        $this->addParam(new ComponentProperty('nav-class'));
        $this->addParam(new ComponentProperty('content-class'));
        $this->addParam(new ComponentProperty('pills', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('small', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('card', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('swipeable', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('tabs', null, ComponentPropertyType::TYPE_ARRAY));
        $this->setRenderer(new TabsRenderer($this));
    }
}
