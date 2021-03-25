<?php

namespace JTL\Backend;

/**
 * Class NotificationEntry
 * @package Backend
 */
class NotificationEntry
{
    /**
     * None
     */
    public const TYPE_NONE = -1;

    /**
     * Information type
     */
    public const TYPE_INFO = 0;

    /**
     * Warning type
     */
    public const TYPE_WARNING = 1;

    /**
     * Error type
     */
    public const TYPE_DANGER = 2;

    /**
     * @var string
     */
    protected $pluginId;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var bool
     */
    protected $ignored = false;

    /**
     * NotificationEntry constructor.
     * @param int         $type
     * @param string      $title
     * @param string|null $description
     * @param string|null $url
     * @param string|null $hash
     */
    public function __construct(
        int $type,
        string $title,
        ?string $description = null,
        ?string $url = null,
        ?string $hash = null
    ) {
        $this->setType($type)
            ->setTitle($title)
            ->setDescription($description)
            ->setUrl($url)
            ->setHash($hash);
    }

    /**
     * @return string|null
     */
    public function getPluginId(): ?string
    {
        return $this->pluginId;
    }

    /**
     * @param string $pluginId
     * @return static
     */
    public function setPluginId(string $pluginId): self
    {
        $this->pluginId = $pluginId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return static
     */
    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return static
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return static
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasDescription(): bool
    {
        return $this->description !== null && \mb_strlen($this->description) > 0;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return static
     */
    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasUrl(): bool
    {
        return $this->url !== null && \mb_strlen($this->url) > 0;
    }

    /**
     * @return string|null
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * @param string|null $hash
     * @return static
     */
    public function setHash(?string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIgnored(): bool
    {
        return $this->ignored;
    }

    /**
     * @param bool $ignored
     * @return NotificationEntry
     */
    public function setIgnored(bool $ignored): self
    {
        $this->ignored = $ignored;

        return $this;
    }
}
