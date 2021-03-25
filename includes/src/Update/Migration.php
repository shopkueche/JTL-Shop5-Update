<?php

namespace JTL\Update;

use DateTime;
use JsonSerializable;
use JTL\DB\DbInterface;

/**
 * Class Migration
 * @package JTL\Update
 */
class Migration implements JsonSerializable
{
    use MigrationTrait,
        MigrationTableTrait;

    /**
     * @var string
     */
    protected $info;

    /**
     * @var DateTime
     */
    protected $executed;

    /**
     * @var string|null
     */
    protected $author;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var bool
     */
    protected $deleteData = true;

    /**
     * Migration constructor.
     *
     * @param DbInterface $db
     * @param null|string   $info
     * @param DateTime|null $executed
     */
    public function __construct(DbInterface $db, $info = null, DateTime $executed = null)
    {
        $this->setDB($db);
        $this->info     = \ucfirst(\strtolower($info));
        $this->executed = $executed;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return MigrationHelper::mapClassNameToId($this->getName());
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return \get_class($this);
    }

    /**
     * @return string|null
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description ?? $this->info ?? '';
    }

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return DateTime::createFromFormat('YmdHis', (string)$this->getId());
    }

    /**
     * @return DateTime|null
     */
    public function getExecuted(): ?DateTime
    {
        return $this->executed;
    }

    /**
     * @return bool
     */
    public function doDeleteData(): bool
    {
        return $this->deleteData;
    }

    /**
     * @param bool $deleteData
     */
    public function setDeleteData(bool $deleteData): void
    {
        $this->deleteData = $deleteData;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id'          => $this->getId(),
            'name'        => $this->getName(),
            'author'      => $this->getAuthor(),
            'description' => $this->getDescription(),
            'executed'    => $this->getExecuted(),
            'created'     => $this->getCreated()
        ];
    }
}
