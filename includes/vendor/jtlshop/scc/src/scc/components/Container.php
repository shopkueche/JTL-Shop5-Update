<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Container
 * @package scc\components
 */
class Container extends AbstractBlockComponent
{
    /**
     * Container constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('container.tpl');
        $this->setName('container');

        $this->addParam(new ComponentProperty('fluid', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('tag', 'div'));
    }
}
