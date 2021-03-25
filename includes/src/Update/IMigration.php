<?php

namespace JTL\Update;

use DateTime;

/**
 * Interface IMigration
 * @package JTL\Update
 */
interface IMigration
{
    /**
     * @var string
     */
    public const UP = 'up';

    /**
     * @var string
     */
    public const DOWN = 'down';

    /**
     * @return mixed|void
     */
    public function up();

    /**
     * @return mixed|void
     */
    public function down();

    /**
     * @return string|null
     */
    public function getId();

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string|null
     */
    public function getAuthor(): ?string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime;

    /**
     * @return bool
     */
    public function doDeleteData(): bool;

    /**
     * @param bool $deleteData
     */
    public function setDeleteData(bool $deleteData): void;
}
