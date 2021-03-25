<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;

/**
 * Class MediaBody
 * @package scc\components
 */
class MediaBody extends AbstractBlockComponent
{
    /**
     * MediaBody constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mediabody.tpl');
        $this->setName('mediabody');

        $this->addParam(new ComponentProperty('tag', 'div'));
    }
}
