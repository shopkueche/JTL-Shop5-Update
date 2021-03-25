<?php declare(strict_types=1);

namespace JTL\News;

use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use function Functional\first;
use function Functional\group;
use function Functional\map;

/**
 * Class CommentList
 * @package JTL\News
 */
final class CommentList implements ItemListInterface
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var int
     */
    private $newsID;

    /**
     * @var Collection
     */
    private $items;

    /**
     * LinkList constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db    = $db;
        $this->items = new Collection();
    }

    /**
     * @inheritDoc
     */
    public function createItems(array $itemIDs, bool $activeOnly = true): Collection
    {
        $itemIDs = \array_map('\intval', $itemIDs);
        if (\count($itemIDs) === 0) {
            return $this->items;
        }
        $data  = $this->db->queryPrepared(
            'SELECT tnewskommentar.*, t.title
                FROM tnewskommentar
                JOIN tnewssprache t 
                    ON t.kNews = tnewskommentar.kNews
                WHERE kNewsKommentar IN (' . \implode(',', $itemIDs) . ')'
                . ($activeOnly ? ' AND nAktiv = 1 ' : '') . '
                GROUP BY tnewskommentar.kNewsKommentar
                ORDER BY tnewskommentar.dErstellt DESC',
            ['nid' => $this->newsID],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $items = map(group($data, static function ($e) {
            return (int)$e->kNewsKommentar;
        }), function ($e, $commentID) {
            $l = new Comment($this->db);
            $l->setID($commentID);
            $l->map($e);
            $l->setNewsTitle(first($e)->title);

            return $l;
        });
        foreach ($items as $item) {
            $this->items->push($item);
        }

        return $this->items;
    }

    /**
     * @param int $newsID
     * @return Collection
     */
    public function createItemsByNewsItem(int $newsID): Collection
    {
        $this->newsID = $newsID;
        $data         = $this->db->queryPrepared(
            'SELECT *
                FROM tnewskommentar
                WHERE kNews = :nid
                    AND nAktiv = 1
                    ORDER BY tnewskommentar.dErstellt DESC',
            ['nid' => $this->newsID],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $items        = map(group($data, static function ($e) {
            return (int)$e->kNewsKommentar;
        }), function ($e, $commentID) {
            $l = new Comment($this->db);
            $l->setID($commentID);
            $l->map($e);

            return $l;
        });
        foreach ($items as $item) {
            $this->items->push($item);
        }

        return $this->items;
    }

    /**
     * @param bool $active
     * @return Collection
     */
    public function filter(bool $active): Collection
    {
        return $this->items->filter(static function (Comment $e) use ($active) {
            return $e->isActive() === $active;
        });
    }

    /**
     * @inheritDoc
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @return Collection
     */
    public function getThreadedItems(): Collection
    {
        foreach ($this->items as $comment) {
            foreach ($this->items as $child) {
                if ($comment->getID() === $child->getParentCommentID()) {
                    $comment->setChildComment($child);
                }
            }
        }
        foreach ($this->items as $key => $comment) {
            if ($comment->getParentCommentID() > 0) {
                unset($this->items[$key]);
            }
        }

        return $this->items;
    }

    /**
     * @inheritDoc
     */
    public function setItems(Collection $items): void
    {
        $this->items = $items;
    }

    /**
     * @inheritDoc
     */
    public function addItem($item): void
    {
        $this->items->push($item);
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res       = \get_object_vars($this);
        $res['db'] = '*truncated*';

        return $res;
    }
}
