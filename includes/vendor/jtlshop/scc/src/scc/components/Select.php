<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Select
 * @package scc\components
 */
class Select extends AbstractBlockComponent
{
    /**
     * Select constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('select.tpl');
        $this->setName('select');

        $this->addParam(new ComponentProperty('disabled', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('required', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('plain', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('multiple', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('select-size', 0, ComponentPropertyType::TYPE_INT));
        $this->addParam(new ComponentProperty('size'));
        $this->addParam(new ComponentProperty('autocomplete'));
        $this->addParam(new ComponentProperty('name'));
    }
}
