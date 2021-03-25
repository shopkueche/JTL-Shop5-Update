<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;

/**
 * Class NavText
 * @package scc\components
 */
class NavText extends AbstractBlockComponent
{
    /**
     * NavText constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('navtext.tpl');
        $this->setName('navtext');
        $this->addParam(new ComponentProperty('tag', 'span'));
    }
}
