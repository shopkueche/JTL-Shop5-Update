<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

/**
 * Class RadioGroup
 * @package scc\components
 */
class RadioGroup extends CheckboxGroup
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('radiogroup.tpl');
        $this->setName('radiogroup');
    }
}
