<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class NavbarToggle
 * @package scc\components
 */
class Clearfix extends AbstractFunctionComponent
{
    /**
     * NavbarToggle constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('clearfix.tpl');
        $this->setName('clearfix');
        $this->addParam(new ComponentProperty('visible-size', null, ComponentPropertyType::TYPE_STRING));
    }
}
