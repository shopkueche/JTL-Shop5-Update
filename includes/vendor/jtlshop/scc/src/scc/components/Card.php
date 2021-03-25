<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Card
 * @package scc\components
 */
class Card extends AbstractBlockComponent
{
    /**
     * Card constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('card.tpl');
        $this->setName('card');

        $this->addParam(new ComponentProperty('img-src'));
        $this->addParam(new ComponentProperty('img-alt'));
        $this->addParam(new ComponentProperty('title-text'));
        $this->addParam(new ComponentProperty('title-tag', 'h4'));
        $this->addParam(new ComponentProperty('subtitle'));
        $this->addParam(new ComponentProperty('subtitle-tag', 'h6'));
        $this->addParam(new ComponentProperty('no-body', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('img-top', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('img-bottom', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('img-fluid', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('overlay', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('header'));
        $this->addParam(new ComponentProperty('header-tag', 'div'));
        $this->addParam(new ComponentProperty('footer'));
        $this->addParam(new ComponentProperty('footer-tag', 'div'));
        $this->addParam(new ComponentProperty('body-tag'));
        $this->addParam(new ComponentProperty('body-bg-variant'));
        $this->addParam(new ComponentProperty('body-border-variant'));
        $this->addParam(new ComponentProperty('body-text-variant'));
        $this->addParam(new ComponentProperty('body-class'));
        $this->addParam(new ComponentProperty('header-bg-variant'));
        $this->addParam(new ComponentProperty('header-border-variant'));
        $this->addParam(new ComponentProperty('header-text-variant'));
        $this->addParam(new ComponentProperty('header-class'));
        $this->addParam(new ComponentProperty('footer-bg-variant'));
        $this->addParam(new ComponentProperty('footer-border-variant'));
        $this->addParam(new ComponentProperty('footer-text-variant'));
        $this->addParam(new ComponentProperty('footer-class'));
        $this->addParam(new ComponentProperty('bg-variant'));
        $this->addParam(new ComponentProperty('border-variant'));
        $this->addParam(new ComponentProperty('text-variant'));
        $this->addParam(new ComponentProperty('align'));
    }
}
