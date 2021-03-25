<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentProperty;
use scc\ComponentPropertyType;

/**
 * Class Pagination
 * @package scc\components
 */
class Pagination extends AbstractBlockComponent
{
    /**
     * Pagination constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('pagination.tpl');
        $this->setName('pagination');

        $this->addParam(new ComponentProperty('value', 1, ComponentPropertyType::TYPE_INT));
        $this->addParam(new ComponentProperty('per-page', 20, ComponentPropertyType::TYPE_INT));
        $this->addParam(new ComponentProperty('total-rows', 20, ComponentPropertyType::TYPE_INT));
        $this->addParam(new ComponentProperty('disabled', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('hide-goto-end-buttons', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('hide-ellipsis', false, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('size', 'md'));
        $this->addParam(new ComponentProperty('align', 'left'));
        $this->addParam(new ComponentProperty('label-first-page', 'leftleft'));
        $this->addParam(new ComponentProperty('first-text', '&laquo;'));
        $this->addParam(new ComponentProperty('label-prev-page', 'Go to previous page'));
        $this->addParam(new ComponentProperty('prev-text', '&lsaquo;'));
        $this->addParam(new ComponentProperty('label-next-page', 'Go to next page'));
        $this->addParam(new ComponentProperty('next-text', '&rsaquo;'));
        $this->addParam(new ComponentProperty('label-last-page', 'Go to last page'));
        $this->addParam(new ComponentProperty('last-text', '&raquo;'));
        $this->addParam(new ComponentProperty('label-page', 'Go to page'));
        $this->addParam(new ComponentProperty('ellipsis-text', '&hellip;'));
    }
}
