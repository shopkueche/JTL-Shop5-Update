<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Progress
 * @package scc\components
 */
class Progress extends AbstractFunctionComponent
{
    /**
     * Progress constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('progress.tpl');
        $this->setName('progress');

        $this->addParam(new ComponentProperty('min', 0, ComponentPropertyType::TYPE_INT));
        $this->addParam(new ComponentProperty('max', 100, ComponentPropertyType::TYPE_INT));
        $this->addParam(new ComponentProperty('now', null, ComponentPropertyType::TYPE_INT));
        $this->addParam(new ComponentProperty('striped', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('animated', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('height'));
        $this->addParam(new ComponentProperty('type'));
    }
}
