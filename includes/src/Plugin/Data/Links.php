<?php declare(strict_types=1);

namespace JTL\Plugin\Data;

use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\Link\LinkList;
use stdClass;
use function Functional\map;

/**
 * Class Links
 * @package JTL\Plugin\Data
 */
class Links
{
    /**
     * @var Collection
     */
    private $links;

    /**
     * Links constructor.
     */
    public function __construct()
    {
        $this->links = new Collection();
    }

    /**
     * @param stdClass[]  $data
     * @param DbInterface $db
     * @return $this
     */
    public function load(array $data, DbInterface $db): self
    {
        $data        = map($data, static function ($e) {
            return (int)$e->kLink;
        });
        $links       = new LinkList($db);
        $this->links = $links->createLinks($data);

        return $this;
    }

    /**
     * @return array
     */
    public function getLinksCompat(): array
    {
        return $this->links->toArray();
    }

    /**
     * @return Collection
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    /**
     * @param Collection $links
     */
    public function setLinks(Collection $links): void
    {
        $this->links = $links;
    }
}
