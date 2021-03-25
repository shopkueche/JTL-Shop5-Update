<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class ButtonToolbar
 * @package scc\components
 */
class ButtonToolbar extends AbstractBlockComponent
{

    /**
     * ButtonToolbar constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('buttontoolbar.tpl');
        $this->setName('buttontoolbar');

        $this->addParam(new ComponentProperty('justify', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('key-nav', false, ComponentPropertyType::TYPE_BOOL));
    }
}
