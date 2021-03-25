<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Jumbotron
 * @package scc\components
 */
class Jumbotron extends AbstractBlockComponent
{
    /**
     * Jumbotron constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('jumbotron.tpl');
        $this->setName('jumbotron');

        $this->addParam(new ComponentProperty('fluid', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('container-fluid', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('header'));
        $this->addParam(new ComponentProperty('header-tag', 'h1'));
        $this->addParam(new ComponentProperty('lead'));
        $this->addParam(new ComponentProperty('lead-tag', 'p'));
        $this->addParam(new ComponentProperty('tag', 'div'));
        $this->addParam(new ComponentProperty('bg-variant'));
        $this->addParam(new ComponentProperty('border-variant'));
        $this->addParam(new ComponentProperty('text-variant'));
        $this->addParam(new ComponentProperty('header-level', 3, ComponentPropertyType::TYPE_NUMERIC));
    }
}
