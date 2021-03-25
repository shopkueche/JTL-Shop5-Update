<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Media
 * @package scc\components
 */
class Media extends AbstractBlockComponent
{
    /**
     * Media constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('media.tpl');
        $this->setName('media');

        $this->addParam(new ComponentProperty('tag', 'div'));
        $this->addParam(new ComponentProperty('vertical-align', 'top'));
        $this->addParam(new ComponentProperty('right-align', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('no-body', false, ComponentPropertyType::TYPE_BOOL));
    }
}
