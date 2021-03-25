<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class ListGroupItem
 * @package scc\components
 */
class ListGroupItem extends AbstractBlockComponent
{
    /**
     * ListGroupItem constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('listgroupitem.tpl');
        $this->setName('listgroupitem');

        $this->addParam(new ComponentProperty('tag', 'div'));
        $this->addParam(new ComponentProperty('variant'));
        $this->addParam(new ComponentProperty('href'));
        $this->addParam(new ComponentProperty('active', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('disabled', false, ComponentPropertyType::TYPE_BOOL));
    }
}
