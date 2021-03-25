<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;

/**
 * Class CardFooter
 * @package scc\components
 */
class CardFooter extends AbstractBlockComponent
{
    /**
     * CardFooter constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('cardfooter.tpl');
        $this->setName('cardfooter');
        $this->addParam(new ComponentProperty('footer'));
        $this->addParam(new ComponentProperty('footer-tag', 'div'));
        $this->addParam(new ComponentProperty('footer-bg-variant'));
        $this->addParam(new ComponentProperty('footer-border-variant'));
        $this->addParam(new ComponentProperty('footer-text-variant'));
        $this->addParam(new ComponentProperty('footer-class'));
    }
}
