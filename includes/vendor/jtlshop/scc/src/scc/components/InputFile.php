<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class InputFile
 * @package scc\components
 */
class InputFile extends AbstractFunctionComponent
{
    /**
     * InputFile constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('inputfile.tpl');
        $this->setName('inputfile');

        $this->addParam(new ComponentProperty('name'));
        $this->addParam(new ComponentProperty('placeholder'));
        $this->addParam(new ComponentProperty('accept'));
        $this->addParam(new ComponentProperty('required', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('disabled', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('plain', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('capture', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('multiple', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('no-traverse', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('no-drop', false, ComponentPropertyType::TYPE_BOOL));
    }
}
