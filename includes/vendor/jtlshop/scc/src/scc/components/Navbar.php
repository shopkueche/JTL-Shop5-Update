<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Navbar
 * @package scc\components
 */
class Navbar extends AbstractBlockComponent
{
    /**
     * Navbar constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('navbar.tpl');
        $this->setName('navbar');

        $this->addParam(new ComponentProperty('tag', 'nav'));
        $this->addParam(new ComponentProperty('type', 'light'));
        $this->addParam(new ComponentProperty('variant'));
        $this->addParam(new ComponentProperty('toggle-breakpoint'));
        $this->addParam(new ComponentProperty('fixed'));
        $this->addParam(new ComponentProperty('toggleable', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('sticky', false, ComponentPropertyType::TYPE_BOOL));
    }
}
