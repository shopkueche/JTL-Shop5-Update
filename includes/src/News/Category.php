<?php declare(strict_types=1);

namespace JTL\News;

use DateTime;
use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\MagicCompatibilityTrait;
use JTL\Media\Image;
use JTL\Media\MultiSizeImage;
use JTL\Shop;
use stdClass;
use function Functional\flatten;
use function Functional\map;

/**
 * Class Category
 * @package JTL\News
 */
class Category implements CategoryInterface
{
    use MagicCompatibilityTrait,
        MultiSizeImage;

    /**
     * @var array
     */
    protected static $mapping = [
        'dLetzteAktualisierung_de' => 'DateLastModified',
        'nSort'                    => 'Sort',
        'nAktiv'                   => 'IsActive',
        'kNewsKategorie'           => 'ID',
        'cName'                    => 'Name',
        'nLevel'                   => 'Level',
        'children'                 => 'Children'
    ];

    /**
     * @var int
     */
    protected $id = -1;

    /**
     * @var int
     */
    protected $parentID = 0;

    /**
     * @var int
     */
    protected $lft = 0;

    /**
     * @var int
     */
    protected $rght = 0;

    /**
     * @var int
     */
    protected $level = 1;

    /**
     * @var int[]
     */
    protected $languageIDs = [];

    /**
     * @var string[]
     */
    protected $languageCodes = [];

    /**
     * @var string[]
     */
    protected $names = [];

    /**
     * @var array
     */
    protected $seo = [];

    /**
     * @var string[]
     */
    protected $descriptions = [];

    /**
     * @var string[]
     */
    protected $metaTitles = [];

    /**
     * @var string[]
     */
    protected $metaKeywords = [];

    /**
     * @var string[]
     */
    protected $metaDescriptions = [];

    /**
     * @var string[]
     */
    protected $previewImages = [];

    /**
     * @var string[]
     */
    protected $urls = [];

    /**
     * @var int
     */
    protected $sort = 0;

    /**
     * @var bool
     */
    protected $isActive = true;

    /**
     * @var DateTime
     */
    protected $dateLastModified;

    /**
     * @var Collection
     */
    protected $children;

    /**
     * @var Collection|ItemListInterface
     */
    protected $items;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * Category constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db               = $db;
        $this->items            = new Collection();
        $this->children         = new Collection();
        $this->dateLastModified = \date_create();
        $this->setImageType(Image::TYPE_NEWSCATEGORY);
    }

    /**
     * @param int  $id
     * @param bool $activeOnly
     * @return CategoryInterface
     */
    public function load(int $id, bool $activeOnly = true): CategoryInterface
    {
        $this->id          = $id;
        $activeFilter      = $activeOnly ? ' AND tnewskategorie.nAktiv = 1 ' : '';
        $categoryLanguages = $this->db->queryPrepared(
            "SELECT tnewskategorie.*, t.*, tseo.cSeo
                FROM tnewskategorie
                JOIN tnewskategoriesprache t 
                    ON tnewskategorie.kNewsKategorie = t.kNewsKategorie
                JOIN tseo 
                    ON tseo.cKey = 'kNewsKategorie'
                    AND tseo.kSprache = t.languageID
                    AND tseo.kKey = :cid
                WHERE tnewskategorie.kNewsKategorie = :cid" . $activeFilter,
            ['cid' => $this->id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($categoryLanguages) === 0) {
            $this->setID(-1);

            return $this;
        }

        return $this->map($categoryLanguages, $activeOnly);
    }

    /**
     * @param array $categoryLanguages
     * @param bool  $activeOnly
     * @return $this|CategoryInterface
     */
    public function map(array $categoryLanguages, bool $activeOnly = true): CategoryInterface
    {
        foreach ($categoryLanguages as $groupLanguage) {
            $langID                          = (int)$groupLanguage->languageID;
            $this->languageIDs[]             = $langID;
            $this->names[$langID]            = $groupLanguage->name;
            $this->metaDescriptions[$langID] = $groupLanguage->metaDescription;
            $this->metaTitles[$langID]       = $groupLanguage->metaTitle;
            $this->descriptions[$langID]     = $groupLanguage->description;
            $this->sort                      = (int)$groupLanguage->nSort;
            $this->previewImages[$langID]    = $groupLanguage->cPreviewImage;
            $this->isActive                  = (bool)$groupLanguage->nAktiv;
            $this->dateLastModified          = \date_create($groupLanguage->dLetzteAktualisierung);
            $this->parentID                  = (int)($groupLanguage->kParent ?? 0);
            $this->level                     = (int)$groupLanguage->lvl;
            $this->lft                       = (int)$groupLanguage->lft;
            $this->rght                      = (int)$groupLanguage->rght;
            $this->seo[$langID]              = $groupLanguage->cSeo;
        }
        if (($preview = $this->getPreviewImage()) !== '') {
            $this->generateAllImageSizes(true, 1, \str_replace(\PFAD_NEWSKATEGORIEBILDER, '', $preview));
        }
        $this->items = (new ItemList($this->db))->createItems(map(flatten($this->db->queryPrepared(
            'SELECT tnewskategorienews.kNews
                FROM tnewskategorienews
                JOIN tnews
                    ON tnews.kNews = tnewskategorienews.kNews 
                WHERE kNewsKategorie = :cid' . ($activeOnly ? ' AND tnews.dGueltigVon <= NOW()' : ''),
            ['cid' => $this->id],
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        )), static function ($e) {
            return (int)$e;
        }));

        return $this;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function getMonthOverview(int $id): Category
    {
        $this->setID($id);
        $overview = $this->db->queryPrepared(
            'SELECT tnewsmonatsuebersicht.*, tseo.cSeo
                FROM tnewsmonatsuebersicht
                LEFT JOIN tseo
                    ON tseo.cKey = :cky
                    AND tseo.kKey = :oid
                WHERE tnewsmonatsuebersicht.kNewsMonatsUebersicht = :oid',
            [
                'cky' => 'kNewsMonatsUebersicht',
                'oid' => $id
            ],
            ReturnType::SINGLE_OBJECT
        );
        if ($overview === null) {
            return $this;
        }
        $this->urls[Shop::getLanguageID()] = Shop::getURL() . '/' . $overview->cSeo;
        $this->setMetaTitle(
            Shop::Lang()->get('newsArchiv') . ' - ' . $overview->nMonat . '/' . $overview->nJahr,
            Shop::getLanguageID()
        );

        $this->items = (new ItemList($this->db))->createItems(map(flatten($this->db->queryPrepared(
            'SELECT tnews.kNews
                FROM tnews
                JOIN tnewskategorienews 
                    ON tnewskategorienews.kNews = tnews.kNews 
                JOIN tnewskategorie 
                    ON tnewskategorie.kNewsKategorie = tnewskategorienews.kNewsKategorie
                    AND tnewskategorie.nAktiv = 1
                WHERE MONTH(tnews.dGueltigVon) = :mnth 
                    AND YEAR(tnews.dGueltigVon) = :yr',
            [
                'mnth' => (int)$overview->nMonat,
                'yr'   => (int)$overview->nJahr
            ],
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        )), static function ($e) {
            return (int)$e;
        }));

        return $this;
    }

    /**
     * @param stdClass|null $filterSQL
     * @return $this
     */
    public function getOverview(stdClass $filterSQL): Category
    {
        $this->setID(0);
        $this->items = (new ItemList($this->db))->createItems(map(flatten($this->db->queryPrepared(
            'SELECT tnews.kNews
                FROM tnews
                JOIN tnewssprache 
                    ON tnews.kNews = tnewssprache.kNews
                JOIN tnewskategorienews 
                    ON tnewskategorienews.kNews = tnews.kNews 
                JOIN tnewskategorie 
                    ON tnewskategorie.kNewsKategorie = tnewskategorienews.kNewsKategorie
            WHERE tnewskategorie.nAktiv = 1 AND tnews.dGueltigVon <= NOW() '
                . $filterSQL->cNewsKatSQL . $filterSQL->cDatumSQL,
            ['cid' => $this->id],
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        )), static function ($e) {
            return (int)$e;
        }));

        return $this;
    }

    /**
     * @return string
     */
    public function buildMetaKeywords(): string
    {
        return \implode(
            ',',
            \array_filter($this->items->slice(0, \min($this->items->count(), 6))->map(static function (Item $i) {
                return $i->getMetaKeyword();
            })->all())
        );
    }

    /**
     * @param int $customerGroupID
     * @param int $languageID
     * @return Collection
     */
    public function filterAndSortItems(int $customerGroupID = 0, $languageID = 0): Collection
    {
        switch ($_SESSION['NewsNaviFilter']->nSort) {
            case -1:
            case 1:
            default: // Datum absteigend
                $order = 'getDateValidFromNumeric';
                $dir   = 'desc';
                break;
            case 2: // Datum aufsteigend
                $order = 'getDateValidFromNumeric';
                $dir   = 'asc';
                break;
            case 3: // Name a ... z
                $order = 'getTitleUppercase';
                $dir   = 'asc';
                break;
            case 4: // Name z ... a
                $order = 'getTitleUppercase';
                $dir   = 'desc';
                break;
            case 5: // Anzahl Kommentare absteigend
                $order = 'getCommentCount';
                $dir   = 'desc';
                break;
            case 6: // Anzahl Kommentare aufsteigend
                $order = 'getCommentCount';
                $dir   = 'asc';
                break;
        }
        $cb = static function (Item $e) use ($order) {
            return $e->$order();
        };
        if ($customerGroupID > 0) {
            $this->items = $this->items->filter(static function (Item $i) use ($customerGroupID) {
                return $i->checkVisibility($customerGroupID);
            });
        }
        if ($languageID > 0) {
            $this->items = $this->items->filter(static function (Item $i) use ($languageID) {
                return $i->getTitle($languageID) !== '';
            });
        }

        return $dir === 'asc'
            ? $this->items->sortBy($cb)
            : $this->items->sortByDesc($cb);
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function setItems($items): void
    {
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function getName(int $idx = null): string
    {
        $idx = $idx ?? Shop::getLanguageID();

        return $this->names[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name, int $idx = null): void
    {
        $this->names[$idx ?? Shop::getLanguageID()] = $name;
    }

    /**
     * @inheritdoc
     */
    public function setNames(array $names): void
    {
        $this->names = $names;
    }

    /**
     * @inheritdoc
     */
    public function getMetaTitles(): array
    {
        return $this->metaTitles;
    }

    /**
     * @inheritdoc
     */
    public function getMetaTitle(int $idx = null): string
    {
        $idx = $idx ?? Shop::getLanguageID();

        return $this->metaTitles[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function setMetaTitle(string $metaTitle, int $idx = null): void
    {
        $this->metaTitles[$idx ?? Shop::getLanguageID()] = $metaTitle;
    }

    /**
     * @inheritdoc
     */
    public function setMetaTitles(array $metaTitles): void
    {
        $this->metaTitles = $metaTitles;
    }

    /**
     * @inheritdoc
     */
    public function getMetaKeyword(int $idx = null): string
    {
        $idx = $idx ?? Shop::getLanguageID();

        return $this->metaKeywords[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getMetaKeywords(): array
    {
        return $this->metaKeywords;
    }

    /**
     * @inheritdoc
     */
    public function setMetaKeyword(string $metaKeyword, int $idx = null): void
    {
        $this->metaKeywords[$idx ?? Shop::getLanguageID()] = $metaKeyword;
    }

    /**
     * @inheritdoc
     */
    public function setMetaKeywords(array $metaKeywords): void
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * @inheritdoc
     */
    public function getMetaDescription(int $idx = null): string
    {
        $idx = $idx ?? Shop::getLanguageID();

        return $this->metaDescriptions[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getMetaDescriptions(): array
    {
        return $this->metaDescriptions;
    }

    /**
     * @inheritdoc
     */
    public function setMetaDescription(string $metaDescription, int $idx = null): void
    {
        $this->metaDescriptions[$idx ?? Shop::getLanguageID()] = $metaDescription;
    }

    /**
     * @inheritdoc
     */
    public function setMetaDescriptions(array $metaDescriptions): void
    {
        $this->metaDescriptions = $metaDescriptions;
    }

    /**
     * @inheritdoc
     */
    public function getURL(int $idx = null): string
    {
        $idx = $idx ?? Shop::getLanguageID();

        // @todo: category or month overview?
//        return $this->urls[$idx] ?? '/?nm=' . $this->getID();
        return $this->urls[$idx] ?? '/?nk=' . $this->getID();
    }

    /**
     * @inheritdoc
     */
    public function getURLs(): array
    {
        return $this->urls;
    }

    /**
     * @inheritdoc
     */
    public function getSEO(int $idx = null): string
    {
        $idx = $idx ?? Shop::getLanguageID();

        return $this->seo[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getSEOs(): array
    {
        return $this->seo;
    }

    /**
     * @inheritdoc
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getParentID(): int
    {
        return $this->parentID;
    }

    /**
     * @inheritdoc
     */
    public function setParentID(int $parentID): void
    {
        $this->parentID = $parentID;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageID(int $idx = null): int
    {
        $idx = $idx ?? Shop::getLanguageID();

        return $this->languageIDs[$idx] ?? 0;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageIDs(): array
    {
        return $this->languageIDs;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageIDs(array $languageIDs): void
    {
        $this->languageIDs = $languageIDs;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageCode(int $idx = null): string
    {
        $idx = $idx ?? Shop::getLanguageID();

        return $this->languageCodes[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getLanguageCodes(): array
    {
        return $this->languageCodes;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageCodes(array $languageCodes): void
    {
        $this->languageCodes = $languageCodes;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(int $idx = null): string
    {
        $idx = $idx ?? Shop::getLanguageID();

        return $this->descriptions[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    /**
     * @inheritdoc
     */
    public function setDescriptions(array $descriptions): void
    {
        $this->descriptions = $descriptions;
    }

    /**
     * @inheritdoc
     */
    public function getPreviewImage(int $idx = null): string
    {
        $idx = $idx ?? Shop::getLanguageID();

        return $this->previewImages[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getPreviewImages(): array
    {
        return $this->previewImages;
    }

    /**
     * @inheritdoc
     */
    public function setPreviewImages(array $previewImages): void
    {
        $this->previewImages = $previewImages;
    }

    /**
     * @inheritdoc
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @inheritdoc
     */
    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @inheritdoc
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @inheritdoc
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @inheritdoc
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @inheritdoc
     */
    public function getDateLastModified(): DateTime
    {
        return $this->dateLastModified;
    }

    /**
     * @inheritdoc
     */
    public function setDateLastModified(DateTime $dateLastModified): void
    {
        $this->dateLastModified = $dateLastModified;
    }

    /**
     * @inheritdoc
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @inheritdoc
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    /**
     * @inheritdoc
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @inheritdoc
     */
    public function addChild(Category $child): void
    {
        $this->children->push($child);
    }

    /**
     * @inheritdoc
     */
    public function setChildren(Collection $children): void
    {
        $this->children = $children;
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
