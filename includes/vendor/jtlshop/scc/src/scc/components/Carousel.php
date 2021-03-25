<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;
use scc\renderers\CarouselRenderer;

/**
 * Class Carousel
 * @package scc\components
 */
class Carousel extends AbstractBlockComponent
{
    /**
     * Carousel constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('carousel.tpl');
        $this->setName('carousel');

        $this->addParam(new ComponentProperty('label-prev', 'Previous slide'));
        $this->addParam(new ComponentProperty('label-next', 'Next slide'));
        $this->addParam(new ComponentProperty('label-goto-slide', 'Go to slide'));
        $this->addParam(new ComponentProperty('label-indicators', 'Select a slide to display'));
        $this->addParam(new ComponentProperty('interval', 5000, ComponentPropertyType::TYPE_INT));
        $this->addParam(new ComponentProperty('value', 0, ComponentPropertyType::TYPE_INT));
        $this->addParam(new ComponentProperty('indicators', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('disabled', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('controls', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('img-width', null, ComponentPropertyType::TYPE_NUMERIC));
        $this->addParam(new ComponentProperty('img-height', null, ComponentPropertyType::TYPE_NUMERIC));

        $this->setRenderer(new CarouselRenderer($this));
    }
}
