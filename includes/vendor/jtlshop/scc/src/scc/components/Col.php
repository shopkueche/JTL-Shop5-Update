<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Col
 * @package scc\components
 */
class Col extends AbstractBlockComponent
{
    /**
     * Col constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('col.tpl');
        $this->setName('col');

        $this->addParam(new ComponentProperty('tag', 'div'));
        $this->addParam(new ComponentProperty('cols', null, ComponentPropertyType::TYPE_INT));
        $this->addParam(new ComponentProperty('offset', null, ComponentPropertyType::TYPE_INT));
        $this->addParam(new ComponentProperty('order', null, ComponentPropertyType::TYPE_INT));
        $this->addParam(new ComponentProperty('sm', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('md', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('lg', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('xl', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('col', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('offset-sm'));
        $this->addParam(new ComponentProperty('offset-md'));
        $this->addParam(new ComponentProperty('offset-lg'));
        $this->addParam(new ComponentProperty('offset-xl'));
        $this->addParam(new ComponentProperty('order-sm'));
        $this->addParam(new ComponentProperty('order-md'));
        $this->addParam(new ComponentProperty('order-lg'));
        $this->addParam(new ComponentProperty('order-xl'));
        $this->addParam(new ComponentProperty('align-self'));
    }
}
