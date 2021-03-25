<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;

/**
 * Class Breadcrumb
 * @package scc\components
 */
class Breadcrumb extends AbstractBlockComponent
{
    /**
     * Breadcrumb constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('breadcrumb.tpl');
        $this->setName('breadcrumb');
        $this->addParam(new ComponentProperty('tag', 'ol'));
    }
}
