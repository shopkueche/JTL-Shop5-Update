<?php declare(strict_types=1);

namespace JTL\Link;

use Illuminate\Support\Collection;

/**
 * Interface LinkGroupListInterface
 * @package JTL\Link
 */
interface LinkGroupListInterface
{
    /**
     * @return $this
     */
    public function loadAll(): LinkGroupListInterface;

    /**
     * @return LinkGroupCollection
     */
    public function getLinkGroups(): LinkGroupCollection;

    /**
     * @param Collection $linkGroups
     */
    public function setLinkGroups(Collection $linkGroups): void;

    /**
     * @return LinkGroupCollection
     */
    public function getVisibleLinkGroups(): LinkGroupCollection;

    /**
     * @param LinkGroupCollection $linkGroups
     */
    public function setVisibleLinkGroups(LinkGroupCollection $linkGroups): void;

    /**
     * @param int $customerGroupID
     * @param int $customerID
     * @return $this
     */
    public function applyVisibilityFilter(int $customerGroupID, int $customerID): LinkGroupListInterface;

    /**
     * @param string $name
     * @param bool   $filtered
     * @return LinkGroupInterface|null
     */
    public function getLinkgroupByTemplate(string $name, $filtered = true): ?LinkGroupInterface;

    /**
     * @param int  $id
     * @param bool $filtered
     * @return LinkGroupInterface|null
     */
    public function getLinkgroupByID(int $id, $filtered = true): ?LinkGroupInterface;
}
