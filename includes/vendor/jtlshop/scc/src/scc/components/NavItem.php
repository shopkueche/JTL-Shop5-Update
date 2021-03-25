<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class NavItem
 * @package scc\components
 */
class NavItem extends AbstractBlockComponent
{
    /**
     * NavItem constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('navitem.tpl');
        $this->setName('navitem');

        $this->addParam(new ComponentProperty('href'));
        $this->addParam(new ComponentProperty('tag', 'li'));
        $this->addParam(new ComponentProperty('target', '_self'));
        $this->addParam(new ComponentProperty('active-class', 'active'));
        $this->addParam(new ComponentProperty('router-tag', 'a'));
        $this->addParam(new ComponentProperty('exact-active-class', 'active'));
        $this->addParam(new ComponentProperty('router-class'));
        $this->addParam(new ComponentProperty('router-data', null, ComponentPropertyType::TYPE_ARRAY));
        $this->addParam(new ComponentProperty('router-aria', null, ComponentPropertyType::TYPE_ARRAY));
        $this->addParam(new ComponentProperty('active', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('disabled', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('append', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('exact', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('replace', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('nofollow', false, ComponentPropertyType::TYPE_BOOL));
    }
}
