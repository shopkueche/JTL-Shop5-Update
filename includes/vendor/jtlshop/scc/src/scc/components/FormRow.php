<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;

/**
 * Class FormRow
 * @package scc\components
 */
class FormRow extends AbstractBlockComponent
{
    /**
     * FormRow constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('formrow.tpl');
        $this->setName('formrow');
        $this->addParam(new ComponentProperty('tag', 'div'));
    }
}
