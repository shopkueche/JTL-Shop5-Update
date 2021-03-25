<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

/**
 * Class Accordion
 * @package scc\components
 */
class Accordion extends AbstractBlockComponent
{
    /**
     * Accordion constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('accordion.tpl');
        $this->setName('accordion');
    }
}
