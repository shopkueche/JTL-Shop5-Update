<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace sccbs3;

use scc\Renderer;

/**
 * Class Bs3sccRenderer
 * @package sccbs3
 */
class Bs3sccRenderer extends Renderer
{
    /**
     * BS3sccRenderer constructor.
     * @param JTL\Smarty\JTLSmarty|\Smarty $smarty $smarty
     */
    public function __construct($smarty)
    {
        $this->smarty = $smarty;
        $this->smarty->addTemplateDir(__DIR__ . '/templates/', __NAMESPACE__);
        parent::__construct($smarty);
    }
}
