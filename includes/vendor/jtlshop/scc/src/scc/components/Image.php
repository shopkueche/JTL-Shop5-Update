<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Image
 * @package scc\components
 */
class Image extends AbstractFunctionComponent
{
    /**
     * Image constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('image.tpl');
        $this->setName('image');

        $this->addParam(new ComponentProperty('src'));
        $this->addParam(new ComponentProperty('srcset'));
        $this->addParam(new ComponentProperty('sizes'));
        $this->addParam(new ComponentProperty('alt', ''));
        $this->addParam(new ComponentProperty('width', null, ComponentPropertyType::TYPE_NUMERIC));
        $this->addParam(new ComponentProperty('height', null, ComponentPropertyType::TYPE_NUMERIC));
        $this->addParam(new ComponentProperty('block', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('fluid', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('fluid-grow', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('lazy', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('webp', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('rounded', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('thumbnail', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('left', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('right', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('center', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('blank', false, ComponentPropertyType::TYPE_BOOL));
    }
}
