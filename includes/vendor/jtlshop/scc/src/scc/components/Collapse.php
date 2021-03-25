<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Collapse
 * @package scc\components
 */
class Collapse extends AbstractBlockComponent
{
    /**
     * Collapse constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('collapse.tpl');
        $this->setName('collapse');

        $this->addParam(new ComponentProperty('accordion'));
        $this->addParam(new ComponentProperty('tag', 'div'));
        $this->addParam(new ComponentProperty('visible', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('is-nav', false, ComponentPropertyType::TYPE_BOOL));
    }
}
