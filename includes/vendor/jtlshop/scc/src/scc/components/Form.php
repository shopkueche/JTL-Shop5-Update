<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Form
 * @package scc\components
 */
class Form extends AbstractBlockComponent
{
    /**
     * Form constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('form.tpl');
        $this->setName('form');

        $this->addParam(new ComponentProperty('inline', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('novalidate', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('validated', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('addtoken', true, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('action', ''));
        $this->addParam(new ComponentProperty('method', 'POST'));
        $this->addParam(new ComponentProperty('target', '_self'));
        $this->addParam(new ComponentProperty('enctype'));
        $this->addParam(new ComponentProperty('slide', false, ComponentPropertyType::TYPE_BOOL));
    }
}
