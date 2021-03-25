<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Modal
 * @package scc\components
 */
class Modal extends AbstractBlockComponent
{
    /**
     * Modal constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('modal.tpl');
        $this->setName('modal');

        $this->addParam(new ComponentProperty('footer'));
        $this->addParam(new ComponentProperty('centered', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('size', false, ComponentPropertyType::TYPE_STRING));
    }
}
