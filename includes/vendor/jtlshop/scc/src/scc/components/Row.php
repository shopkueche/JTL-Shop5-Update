<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Row
 * @package scc\components
 */
class Row extends AbstractBlockComponent
{
    /**
     * Row constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('row.tpl');
        $this->setName('row');

        $this->addParam(new ComponentProperty('no-gutters', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('tag', 'div'));
        $this->addParam(new ComponentProperty('align-h'));
        $this->addParam(new ComponentProperty('align-v'));
        $this->addParam(new ComponentProperty('align-content'));
    }
}
