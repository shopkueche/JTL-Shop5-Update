<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\components;

use scc\ComponentInterface;
use scc\ComponentProperty;
use scc\ComponentPropertyType;
use scc\ComponentPropertyInterface;
use scc\ComponentRendererInterface;

/**
 * Class AbstractBaseComponent
 * @package scc\components
 */
abstract class AbstractBaseComponent implements ComponentInterface
{
    /**
     * @var string|null
     */
    protected $content;

    /**
     * @var ComponentPropertyInterface[]
     */
    protected $params;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ComponentRendererInterface
     */
    protected $renderer;

    /**
     * @var string
     */
    protected $type;

    /**
     * AbstractBaseComponent constructor.
     */
    public function __construct()
    {
        $this->addParam(new ComponentProperty('class'));
        $this->addParam(new ComponentProperty('title'));
        $this->addParam(new ComponentProperty('id'));
        $this->addParam(new ComponentProperty('style'));
        $this->addParam(new ComponentProperty('itemprop'));
        $this->addParam(new ComponentProperty('itemtype'));
        $this->addParam(new ComponentProperty('itemid'));
        $this->addParam(new ComponentProperty('role'));
        $this->addParam(new ComponentProperty('itemscope', null, ComponentPropertyType::TYPE_BOOL));
        $this->addParam(new ComponentProperty('aria', null, ComponentPropertyType::TYPE_ARRAY));
        $this->addParam(new ComponentProperty('data', null, ComponentPropertyType::TYPE_ARRAY));
    }

    /**
     * @inheritdoc
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @inheritdoc
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * @inheritdoc
     */
    public function addParam(ComponentProperty $param): void
    {
        $this->params[$param->getName()] = $param;
    }

    /**
     * @inheritdoc
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @inheritdoc
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public function getRenderer(): ComponentRendererInterface
    {
        return $this->renderer;
    }

    /**
     * @inheritdoc
     */
    public function setRenderer(ComponentRendererInterface $renderer): void
    {
        $this->renderer = $renderer;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
