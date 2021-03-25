<?php declare(strict_types=1);

namespace JTL\OPC;

use Exception;
use JTL\Backend\Revision;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Shop;
use JTL\Update\Updater;
use stdClass;

/**
 * Class PageDB
 * @package JTL\OPC
 */
class PageDB
{
    /**
     * @var DbInterface
     */
    protected $shopDB;

    /**
     * PageDB constructor.
     * @param DbInterface $shopDB
     */
    public function __construct(DbInterface $shopDB)
    {
        $this->shopDB = $shopDB;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function shopHasPendingUpdates()
    {
        $updater = new Updater($this->shopDB);
        return $updater->hasPendingUpdates();
    }

    /**
     * @return int
     */
    public function getPageCount(): int
    {
        return (int)$this->shopDB->query(
            'SELECT COUNT(DISTINCT cPageId) AS count FROM topcpage',
            ReturnType::SINGLE_OBJECT
        )->count;
    }

    /**
     * @return array
     */
    public function getPages(): array
    {
        return $this->shopDB->query(
            'SELECT cPageId, cPageUrl FROM topcpage GROUP BY cPageId, cPageUrl',
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param string $id
     * @return array
     */
    public function getDraftRows($id): array
    {
        return $this->shopDB->selectAll('topcpage', 'cPageId', $id);
    }

    /**
     * @param string $id
     * @return int
     */
    public function getDraftCount($id): int
    {
        return (int)$this->shopDB->queryPrepared(
            'SELECT COUNT(kPage) AS count FROM topcpage WHERE cPageId = :id',
            ['id' => $id],
            ReturnType::SINGLE_OBJECT
        )->count;
    }

    /**
     * @param int $key
     * @return stdClass
     * @throws Exception
     */
    public function getDraftRow(int $key): stdClass
    {
        $draftRow = $this->shopDB->select('topcpage', 'kPage', $key);

        if (!\is_object($draftRow)) {
            throw new Exception('The OPC page draft could not be found in the database.');
        }

        return $draftRow;
    }

    /**
     * @param int $revId
     * @return object
     * @throws Exception
     */
    public function getRevisionRow(int $revId)
    {
        $revision    = new Revision($this->shopDB);
        $revisionRow = $revision->getRevision($revId);

        if (!\is_object($revisionRow)) {
            throw new Exception('The OPC page revision could not be found in the database.');
        }

        return \json_decode($revisionRow->content);
    }

    /**
     * @param string $id
     * @return null|stdClass
     */
    public function getPublicPageRow(string $id): ?stdClass
    {
        $publicRow = $this->shopDB->queryPrepared(
            'SELECT * FROM topcpage
                WHERE cPageId = :pageId
                    AND dPublishFrom IS NOT NULL
                    AND dPublishFrom <= NOW()
                    AND (dPublishTo > NOW() OR dPublishTo IS NULL)
                ORDER BY dPublishFrom DESC',
            ['pageId' => $id],
            ReturnType::SINGLE_OBJECT
        );

        return !\is_object($publicRow) ? null : $publicRow;
    }

    /**
     * @param string $id
     * @return Page[]
     * @throws Exception
     */
    public function getDrafts(string $id): array
    {
        $drafts = [];

        foreach ($this->getDraftRows($id) as $draftRow) {
            $drafts[] = $this->getPageFromRow($draftRow);
        }

        return $drafts;
    }

    /**
     * @param int $key
     * @return Page
     * @throws Exception
     */
    public function getDraft(int $key): Page
    {
        $draftRow = $this->getDraftRow($key);
        $seo      = $this->getPageSeo($draftRow->cPageId);

        if (!empty($seo)) {
            $draftRow->cPageUrl = $seo;
        }

        return $this->getPageFromRow($draftRow);
    }

    /**
     * @param int $revId
     * @return Page
     * @throws Exception
     */
    public function getRevision(int $revId): Page
    {
        $revisionRow = $this->getRevisionRow($revId);

        return $this->getPageFromRow($revisionRow);
    }

    /**
     * @param int $key
     * @return array
     */
    public function getRevisionList(int $key): array
    {
        $revision = new Revision($this->shopDB);

        return $revision->getRevisions('opcpage', $key);
    }

    /**
     * @param string $id
     * @return Page|null
     * @throws Exception
     */
    public function getPublicPage(string $id): ?Page
    {
        $publicRow = $this->getPublicPageRow($id);
        $page      = null;

        if (\is_object($publicRow)) {
            $page = $this->getPageFromRow($publicRow);
        }

        Shop::fire('shop.OPC.PageDB.getPublicPage', [
            'id'   => $id,
            'page' => &$page
        ]);

        return $page;
    }

    /**
     * @param string $pageId
     * @return string|null
     */
    public function getPageSeo(string $pageId): ?string
    {
        $pageIdObj = \json_decode($pageId);

        if (empty($pageIdObj)) {
            return null;
        }

        switch ($pageIdObj->type) {
            case 'product':
                $cKey = 'kArtikel';
                break;
            case 'category':
                $cKey = 'kKategorie';
                break;
            case 'manufacturer':
                $cKey = 'kHersteller';
                break;
            case 'link':
                $cKey = 'kLink';
                break;
            case 'attrib':
                $cKey = 'kMerkmalWert';
                break;
            case 'special':
                $cKey = 'suchspecial';
                break;
            case 'news':
                $cKey = 'kNews';
                break;
            case 'newscat':
                $cKey = 'kNewsKategorie';
                break;
            default:
                $cKey = null;
                break;
        }

        if (empty($cKey)) {
            return null;
        }

        $seo = $this->shopDB->queryPrepared(
            'SELECT cSeo FROM tseo WHERE cKey = :ckey AND kKey = :key AND kSprache = :lang',
            ['ckey' => $cKey, 'key' => $pageIdObj->id, 'lang' => $pageIdObj->lang],
            ReturnType::SINGLE_OBJECT
        );

        if (empty($seo)) {
            return null;
        }

        if (!empty($pageIdObj->attribs)) {
            $attribSeos = $this->shopDB->queryPrepared(
                "SELECT cSeo FROM tseo WHERE cKey = 'kMerkmalWert'
                     AND kKey IN (" . \implode(',', $pageIdObj->attribs) . ')
                     AND kSprache = :lang',
                ['lang' => $pageIdObj->lang],
                ReturnType::ARRAY_OF_OBJECTS
            );

            if (\count($attribSeos) !== \count($pageIdObj->attribs)) {
                return null;
            }
        }

        if (!empty($pageIdObj->manufacturerFilter)) {
            $manufacturerSeo = $this->shopDB->queryPrepared(
                "SELECT cSeo FROM tseo WHERE cKey = 'kHersteller'
                     AND kKey = :kKey
                     AND kSprache = :lang",
                ['kKey' => $pageIdObj->manufacturerFilter, 'lang' => $pageIdObj->lang],
                ReturnType::SINGLE_OBJECT
            );

            if (empty($manufacturerSeo)) {
                return null;
            }
        }

        $result = '/' . $seo->cSeo;

        if (!empty($attribSeos)) {
            foreach ($attribSeos as $seo) {
                $result .= '__' . $seo->cSeo;
            }
        }

        if (!empty($manufacturerSeo)) {
            $result .= '::' . $manufacturerSeo->cSeo;
        }

        return $result;
    }

    /**
     * @param Page $page
     * @return $this
     * @throws Exception
     */
    public function saveDraft(Page $page): self
    {
        if ($page->getUrl() === ''
            || $page->getLastModified() === ''
            || $page->getLockedAt() === ''
            || $page->getId() === ''
        ) {
            throw new Exception('The OPC page data to be saved is incomplete or invalid.');
        }

        Shop::fire('shop.OPC.PageDB.saveDraft:afterValidate', [
            'page' => &$page
        ]);

        $page->setLastModified(\date('Y-m-d H:i:s'));

        $pageDB = (object)[
            'cPageId'       => $page->getId(),
            'dPublishFrom'  => $page->getPublishFrom() ?? '_DBNULL_',
            'dPublishTo'    => $page->getPublishTo() ?? '_DBNULL_',
            'cName'         => $page->getName(),
            'cPageUrl'      => $page->getUrl(),
            'cAreasJson'    => \json_encode($page->getAreaList()),
            'dLastModified' => $page->getLastModified() ?? '_DBNULL_',
            'cLockedBy'     => $page->getLockedBy(),
            'dLockedAt'     => $page->getLockedAt() ?? '_DBNULL_',
        ];

        if ($page->getKey() > 0) {
            $dbPage       = $this->shopDB->select('topcpage', 'kPage', $page->getKey());
            $oldAreasJson = $dbPage->cAreasJson;
            $newAreasJson = $pageDB->cAreasJson;

            if ($oldAreasJson !== $newAreasJson) {
                $revision = new Revision($this->shopDB);
                $revision->addRevision('opcpage', (int)$dbPage->kPage);
            }

            if ($this->shopDB->update('topcpage', 'kPage', $page->getKey(), $pageDB) === -1) {
                throw new Exception('The OPC page could not be updated in the DB.');
            }
        } else {
            $key = $this->shopDB->insert('topcpage', $pageDB);

            if ($key === 0) {
                throw new Exception('The OPC page could not be inserted into the DB.');
            }

            $page->setKey($key);
        }

        return $this;
    }

    /**
     * @param Page $page existing page draft
     * @return $this
     * @throws Exception
     */
    public function saveDraftLockStatus(Page $page): self
    {
        $pageDB = (object)[
            'cLockedBy' => $page->getLockedBy(),
            'dLockedAt' => $page->getLockedAt() ?? '_DBNULL_',
        ];

        if ($this->shopDB->update('topcpage', 'kPage', $page->getKey(), $pageDB) === -1) {
            throw new Exception('The OPC page could not be updated in the DB.');
        }

        return $this;
    }

    /**
     * @param Page $page existing page draft
     * @return $this
     * @throws Exception
     */
    public function saveDraftPublicationStatus(Page $page): self
    {
        $pageDB = (object)[
            'dPublishFrom' => $page->getPublishFrom() ?? '_DBNULL_',
            'dPublishTo'   => $page->getPublishTo() ?? '_DBNULL_',
            'cName'        => $page->getName(),
        ];

        if ($this->shopDB->update('topcpage', 'kPage', $page->getKey(), $pageDB) === -1) {
            throw new Exception('The OPC page publication status could not be updated in the DB.');
        }

        return $this;
    }

    /**
     * @param int    $draftKey
     * @param string $draftName
     * @return PageDB
     * @throws Exception
     */
    public function saveDraftName(int $draftKey, string $draftName): self
    {
        $pageDB = (object)[
            'cName' => $draftName,
        ];

        if ($this->shopDB->update('topcpage', 'kPage', $draftKey, $pageDB) === -1) {
            throw new Exception('The OPC draft name could not be updated in the DB.');
        }

        return $this;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function deletePage(string $id): self
    {
        $this->shopDB->delete('topcpage', 'cPageId', $id);

        return $this;
    }

    /**
     * @param int $key
     * @return $this
     */
    public function deleteDraft(int $key): self
    {
        $this->shopDB->delete('topcpage', 'kPage', $key);

        return $this;
    }

    /**
     * @param $row
     * @return Page
     * @throws Exception
     */
    protected function getPageFromRow($row): Page
    {
        $page = (new Page())
            ->setKey((int)$row->kPage)
            ->setId($row->cPageId)
            ->setPublishFrom($row->dPublishFrom)
            ->setPublishTo($row->dPublishTo)
            ->setName($row->cName)
            ->setUrl($row->cPageUrl)
            ->setLastModified($row->dLastModified)
            ->setLockedBy($row->cLockedBy)
            ->setLockedAt($row->dLockedAt);

        $areaData = \json_decode($row->cAreasJson, true);

        if ($areaData !== null) {
            $page->getAreaList()->deserialize($areaData);
        }

        Shop::fire('shop.OPC.PageDB.getPageRow', [
            'row'  => &$row,
            'page' => &$page
        ]);

        return $page;
    }
}
