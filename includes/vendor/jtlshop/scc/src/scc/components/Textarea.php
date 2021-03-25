<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Textarea
 * @package scc\components
 */
class Textarea extends AbstractBlockComponent
{
    /**
     * Textarea constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('textarea.tpl');
        $this->setName('textarea');

        $this->addParam(new ComponentProperty('disabled', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('required', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('readonly', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('plaintext', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('no-resize', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('rows', null, ComponentPropertyType::TYPE_INT));
        $this->addParam(new ComponentProperty('max-rows', null, ComponentPropertyType::TYPE_INT));
        $this->addParam(new ComponentProperty('size'));
        $this->addParam(new ComponentProperty('value'));
        $this->addParam(new ComponentProperty('autocomplete'));
        $this->addParam(new ComponentProperty('placeholder'));
        $this->addParam(new ComponentProperty('name'));
        $this->addParam(new ComponentProperty('wrap', 'soft'));
    }
}
