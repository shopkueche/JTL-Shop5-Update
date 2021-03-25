<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;

/**
 * Class InputGroup
 * @package scc\components
 */
class InputGroup extends AbstractBlockComponent
{
    /**
     * InputGroup constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('inputgroup.tpl');
        $this->setName('inputgroup');
        $this->addParam(new ComponentProperty('size'));
        $this->addParam(new ComponentProperty('prepend'));
        $this->addParam(new ComponentProperty('append'));
        $this->addParam(new ComponentProperty('tag', 'div'));
    }
}
