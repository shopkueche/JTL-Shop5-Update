<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Badge
 * @package scc\components
 */
class Badge extends AbstractBlockComponent
{
    /**
     * Badge constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('badge.tpl');
        $this->setName('badge');

        $this->addParam(new ComponentProperty(
            'active',
            false,
            ComponentPropertyType::TYPE_BOOL
        ));
        $this->addParam(new ComponentProperty(
            'disabled',
            false,
            ComponentPropertyType::TYPE_BOOL
        ));
        $this->addParam(new ComponentProperty(
            'pill',
            false,
            ComponentPropertyType::TYPE_BOOL
        ));
        $this->addParam(new ComponentProperty('tag', 'span'));
        $this->addParam(new ComponentProperty('variant', 'secondary'));
    }
}
