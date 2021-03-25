<?php declare(strict_types=1);

namespace JTL\News;

use Illuminate\Support\Collection;

/**
 * Interface ItemListInterface
 * @package JTL\News
 */
interface ItemListInterface
{
    /**
     * @param int[] $itemIDs
     * @param bool  $activeOnly
     * @return Collection
     */
    public function createItems(array $itemIDs, bool $activeOnly = true): Collection;

    /**
     * @return Collection
     */
    public function getItems(): Collection;

    /**
     * @param Collection $items
     */
    public function setItems(Collection $items): void;

    /**
     * @param mixed $item
     */
    public function addItem($item): void;
}
