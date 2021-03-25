<?php declare(strict_types=1);

namespace JTL\OPC;

use JTL\Plugin\PluginInterface;
use JTL\Plugin\PluginLoader;
use JTL\Shop;

/**
 * Class Portlet
 * @package JTL\OPC
 */
class Portlet implements \JsonSerializable
{
    use PortletHtml;
    use PortletStyles;
    use PortletAnimations;

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var PluginInterface
     */
    protected $plugin;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $class = '';

    /**
     * @var string
     */
    protected $group = '';

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * Portlet constructor.
     * @param string $class
     * @param int    $id
     * @param int    $pluginId
     */
    final public function __construct(string $class, int $id, int $pluginId)
    {
        $this->class = $class;
        $this->id    = $id;

        if ($pluginId > 0) {
            $loader       = new PluginLoader(Shop::Container()->getDB(), Shop::Container()->getCache());
            $this->plugin = $loader->init($pluginId);
        }

        if ($this->plugin === null) {
            Shop::Container()->getGetText()->loadAdminLocale('portlets/' . $this->class);
        } else {
            Shop::Container()->getGetText()->loadPluginLocale('portlets/' . $this->class, $this->plugin);
        }
    }

    /**
     * @return array
     */
    final public function getDefaultProps(): array
    {
        $defProps = [];

        foreach ($this->getPropertyDesc() as $name => $propDesc) {
            $defProps[$name] = $propDesc['default'] ?? '';

            if (isset($propDesc['children'])) {
                foreach ($propDesc['children'] as $childName => $childPropDesc) {
                    $defProps[$childName] = $childPropDesc['default'] ?? '';
                }
            }

            if (isset($propDesc['childrenFor'])) {
                foreach ($propDesc['childrenFor'] as $optionalPropDescs) {
                    foreach ($optionalPropDescs as $childName => $childPropDesc) {
                        $defProps[$childName] = $childPropDesc['default'] ?? '';
                    }
                }
            }
        }

        return $defProps;
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getDeepPropertyDesc(): array
    {
        $deepDesc = [];

        foreach ($this->getPropertyDesc() as $name => $propDesc) {
            $deepDesc[$name] = $propDesc;

            if (isset($propDesc['children'])) {
                foreach ($propDesc['children'] as $childName => $childPropDesc) {
                    $deepDesc[$childName] = $childPropDesc;
                }
            }

            if (isset($propDesc['childrenFor'])) {
                foreach ($propDesc['childrenFor'] as $optionalPropDescs) {
                    foreach ($optionalPropDescs as $childName => $childPropDesc) {
                        $deepDesc[$childName] = $childPropDesc;
                    }
                }
            }
        }

        return $deepDesc;
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPluginId(): int
    {
        return $this->plugin === null ? 0 : $this->plugin->getID();
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return __($this->title);
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string $group
     * @return self
     */
    public function setGroup(string $group): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return PluginInterface|null
     */
    public function getPlugin(): ?PluginInterface
    {
        return $this->plugin;
    }

    /**
     * @param bool $active
     * @return Portlet
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id'           => $this->getId(),
            'pluginId'     => $this->getPluginId(),
            'title'        => $this->getTitle(),
            'class'        => $this->getClass(),
            'group'        => $this->getGroup(),
            'active'       => $this->isActive(),
            'defaultProps' => $this->getDefaultProps(),
        ];
    }
}
