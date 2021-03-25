<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Embed
 * @package scc\components
 */
class Embed extends AbstractBlockComponent
{
    /**
     * Embed constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('embed.tpl');
        $this->setName('embed');

        $this->addParam(new ComponentProperty('src'));
        $this->addParam(new ComponentProperty('type', 'iframe'));
        $this->addParam(new ComponentProperty('aspect', '16by9'));
        $this->addParam(new ComponentProperty('preload'));
        $this->addParam(new ComponentProperty('width'));
        $this->addParam(new ComponentProperty('height'));
        $this->addParam(new ComponentProperty('poster'));
        $this->addParam(new ComponentProperty('type'));
        $this->addParam(new ComponentProperty('allowfullscreen', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('controls', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('autoplay', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('autobuffer', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('loop', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('muted', false, ComponentPropertyType::TYPE_BOOL));
    }
}
