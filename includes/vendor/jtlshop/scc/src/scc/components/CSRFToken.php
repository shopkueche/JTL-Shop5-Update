<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

/**
 * Class CSRFToken
 * @package scc\components
 */
class CSRFToken extends AbstractFunctionComponent
{
    /**
     * CSRFToken constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('csrf_token.tpl');
        $this->setName('csrf_token');
    }
}
