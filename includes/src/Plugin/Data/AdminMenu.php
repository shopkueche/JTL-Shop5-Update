<?php declare(strict_types=1);

namespace JTL\Plugin\Data;

use Illuminate\Support\Collection;
use stdClass;

/**
 * Class AdminMenu
 * @package JTL\Plugin\Data
 */
class AdminMenu
{
    /**
     * @var Collection
     */
    private $items;

    /**
     * AdminMenu constructor.
     */
    public function __construct()
    {
        $this->items = new Collection();
    }

    /**
     * @return Collection
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @param Collection $items
     */
    public function setItems(Collection $items): void
    {
        $this->items = $items;
    }

    /**
     * @param stdClass $item
     */
    public function addItem($item): void
    {
        $this->items->push($item);
    }

    /**
     * @param int $menuID
     */
    public function removeItem(int $menuID): void
    {
        $this->items = $this->items->reject(static function ($value, $key) use ($menuID) {
            return $value->kPluginAdminMenu === $menuID;
        });
    }
}
