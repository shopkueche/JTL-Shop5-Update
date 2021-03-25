<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Tab
 * @package scc\components
 */
class Tab extends AbstractBlockComponent
{
    /**
     * Tab constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('tab.tpl');
        $this->setName('tab');

        $this->addParam(new ComponentProperty('tag', 'li'));
        $this->addParam(new ComponentProperty('title-item-class'));
        $this->addParam(new ComponentProperty('title-link-class'));
        $this->addParam(new ComponentProperty('href', '#'));
        $this->addParam(new ComponentProperty('active', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('disabled', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('swipeable', false, ComponentPropertyType::TYPE_BOOL));
    }
}
