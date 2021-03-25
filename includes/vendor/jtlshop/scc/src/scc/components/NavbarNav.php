<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class NavbarNav
 * @package scc\components
 */
class NavbarNav extends AbstractBlockComponent
{
    /**
     * NavbarNav constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('navbarnav.tpl');
        $this->setName('navbarnav');

        $this->addParam(new ComponentProperty('tag', 'ul'));
        $this->addParam(new ComponentProperty('fill', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('justified', false, ComponentPropertyType::TYPE_BOOL));
    }
}
