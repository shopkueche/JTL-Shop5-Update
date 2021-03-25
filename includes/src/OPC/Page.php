<?php declare(strict_types=1);

namespace JTL\OPC;

use JTL\Helpers\GeneralObject;

/**
 * Class Page
 * @package JTL\OPC
 */
class Page implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $key = 0;

    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var bool
     */
    protected $isModifiable = true;

    /**
     * @var null|string
     */
    protected $publishFrom;

    /**
     * @var null|string
     */
    protected $publishTo;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var int
     */
    protected $revId = 0;

    /**
     * @var string
     */
    protected $url = '';

    /**
     * @var null|string
     */
    protected $lastModified;

    /**
     * @var string
     */
    protected $lockedBy = '';

    /**
     * @var null|string
     */
    protected $lockedAt;

    /**
     * @var null|AreaList
     */
    protected $areaList;

    /**
     * Page constructor.
     */
    public function __construct()
    {
        $this->areaList = new AreaList();
    }

    /**
     * @return int
     */
    public function getKey(): int
    {
        return $this->key;
    }

    /**
     * @param int $key
     * @return $this
     */
    public function setKey(int $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return bool
     */
    public function isModifiable(): bool
    {
        return $this->isModifiable;
    }

    /**
     * @param bool $isModifiable
     * @return Page
     */
    public function setIsModifiable(bool $isModifiable): Page
    {
        $this->isModifiable = $isModifiable;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPublishFrom(): ?string
    {
        return $this->publishFrom;
    }

    /**
     * @param null|string $publishFrom
     * @return Page
     */
    public function setPublishFrom($publishFrom): self
    {
        $this->publishFrom = $publishFrom;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPublishTo(): ?string
    {
        return $this->publishTo;
    }

    /**
     * @param null|string $publishTo
     * @return Page
     */
    public function setPublishTo($publishTo): self
    {
        $this->publishTo = $publishTo;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Page
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getRevId(): int
    {
        return $this->revId;
    }

    /**
     * @param int $revId
     * @return Page
     */
    public function setRevId(int $revId): self
    {
        $this->revId = $revId;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLastModified(): ?string
    {
        return $this->lastModified;
    }

    /**
     * @param null|string $lastModified
     * @return $this
     */
    public function setLastModified($lastModified): self
    {
        $this->lastModified = $lastModified;

        return $this;
    }

    /**
     * @return string
     */
    public function getLockedBy(): string
    {
        return $this->lockedBy;
    }

    /**
     * @param string $lockedBy
     * @return $this
     */
    public function setLockedBy(string $lockedBy): self
    {
        $this->lockedBy = $lockedBy;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLockedAt(): ?string
    {
        return $this->lockedAt;
    }

    /**
     * @param null|string $lockedAt
     * @return $this
     */
    public function setLockedAt($lockedAt): self
    {
        $this->lockedAt = $lockedAt;

        return $this;
    }

    /**
     * @return AreaList
     */
    public function getAreaList(): AreaList
    {
        return $this->areaList;
    }

    /**
     * @param AreaList $newList
     * @return $this
     */
    public function setAreaList(AreaList $newList): self
    {
        $this->areaList = $newList;

        return $this;
    }

    /**
     * @param int $publicDraftKey
     * @return int
     */
    public function getStatus(int $publicDraftKey)
    {
        $now   = \date('Y-m-d H:i:s');
        $start = $this->getPublishFrom();
        $end   = $this->getPublishTo();

        if (!empty($start) && $now >= $start && (empty($end) || $now < $end)) {
            if ($this->getKey() === $publicDraftKey || $publicDraftKey === 0) {
                return 0; // public
            }
            return 1; // planned
        }
        if (!empty($start) && $now < $start) {
            return 1; // planned
        }
        if (empty($start)) {
            return 2; // draft
        }
        if (!empty($start) && !empty($end) && $now > $end) {
            return 3; // backdate
        }

        return -1;
    }

    /**
     * @param bool $preview
     * @return array
     */
    public function getCssList(bool $preview = false): array
    {
        $list = [];

        foreach ($this->areaList->getAreas() as $area) {
            /** @noinspection AdditionOperationOnArraysInspection */
            $list = $list + $area->getCssList($preview);
        }

        return $list;
    }

    /**
     * @param string $json
     * @return Page
     * @throws \Exception
     */
    public function fromJson(string $json): self
    {
        $this->deserialize(\json_decode($json, true));

        return $this;
    }

    /**
     * @param array $data
     * @return Page
     * @throws \Exception
     */
    public function deserialize(array $data): self
    {
        $this->setKey($data['key'] ?? $this->getKey());
        $this->setId($data['id'] ?? $this->getId());
        $this->setPublishFrom($data['publishFrom'] ?? $this->getPublishFrom());
        $this->setPublishTo($data['publishTo'] ?? $this->getPublishTo());
        $this->setName($data['name'] ?? $this->getName());
        $this->setUrl($data['url'] ?? $this->getUrl());
        $this->setRevId($data['revId'] ?? $this->getRevId());

        if (GeneralObject::isCountable('areas', $data)) {
            $this->getAreaList()->deserialize($data['areas']);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'key'          => $this->getKey(),
            'id'           => $this->getId(),
            'publishFrom'  => $this->getPublishFrom(),
            'publishTo'    => $this->getPublishTo(),
            'name'         => $this->getName(),
            'revId'        => $this->getRevId(),
            'url'          => $this->getUrl(),
            'lastModified' => $this->getLastModified(),
            'lockedBy'     => $this->getLockedBy(),
            'lockedAt'     => $this->getLockedAt(),
            'areaList'     => $this->getAreaList()->jsonSerialize(),
        ];
    }
}
