<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class CarouselSlide
 * @package scc\components
 */
class CarouselSlide extends AbstractBlockComponent
{
    /**
     * CarouselSlide constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('carouselslide.tpl');
        $this->setName('carouselslide');

        $this->addParam(new ComponentProperty('active', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('img-blank', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('img-blank-color', 'transparent'));
        $this->addParam(new ComponentProperty('caption-text'));
        $this->addParam(new ComponentProperty('src'));
        $this->addParam(new ComponentProperty('img-src'));
        $this->addParam(new ComponentProperty('img-alt', ''));
        $this->addParam(new ComponentProperty('img-width'));
        $this->addParam(new ComponentProperty('img-height'));
        $this->addParam(new ComponentProperty('content-tag', 'div'));
        $this->addParam(new ComponentProperty('caption'));
        $this->addParam(new ComponentProperty('caption-tag', 'h3'));
        $this->addParam(new ComponentProperty('caption-text-tag', 'p'));
        $this->addParam(new ComponentProperty('background'));
    }
}
