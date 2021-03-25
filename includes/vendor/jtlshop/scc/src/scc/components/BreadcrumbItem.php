<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class BreadcrumbItem
 * @package scc\components
 */
class BreadcrumbItem extends AbstractBlockComponent
{
    /**
     * BreadcrumbItem constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('breadcrumbitem.tpl');
        $this->setName('breadcrumbitem');

        $this->addParam(new ComponentProperty('href'));
        $this->addParam(new ComponentProperty('tag', 'li'));
        $this->addParam(new ComponentProperty('target', '_self'));
        $this->addParam(new ComponentProperty('active-class', 'active'));
        $this->addParam(new ComponentProperty('router-tag', 'a'));
        $this->addParam(new ComponentProperty('router-tag-itemprop'));
        $this->addParam(new ComponentProperty('exact-active-class', 'active'));

        $prop = new ComponentProperty();
        $prop->setName('active');
        $prop->setType(ComponentPropertyType::TYPE_BOOL);
        $prop->setDefaultValue(false);
        $this->addParam($prop);

        $prop = new ComponentProperty();
        $prop->setName('disabled');
        $prop->setType(ComponentPropertyType::TYPE_BOOL);
        $prop->setDefaultValue(false);
        $this->addParam($prop);

        $prop = new ComponentProperty();
        $prop->setName('append');
        $prop->setType(ComponentPropertyType::TYPE_BOOL);
        $prop->setDefaultValue(false);
        $this->addParam($prop);

        $prop = new ComponentProperty();
        $prop->setName('exact');
        $prop->setType(ComponentPropertyType::TYPE_BOOL);
        $prop->setDefaultValue(false);
        $this->addParam($prop);

        $prop = new ComponentProperty();
        $prop->setName('replace');
        $prop->setType(ComponentPropertyType::TYPE_BOOL);
        $prop->setDefaultValue(false);
        $this->addParam($prop);

        $prop = new ComponentProperty();
        $prop->setName('nofollow');
        $prop->setType(ComponentPropertyType::TYPE_BOOL);
        $prop->setDefaultValue(false);
        $this->addParam($prop);
    }
}
