<?php declare(strict_types=1);

namespace JTL\Link;

use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\MagicCompatibilityTrait;
use JTL\Shop;
use function Functional\flatten;
use function Functional\map;

/**
 * Class LinkGroup
 * @package JTL\Link
 */
final class LinkGroup implements LinkGroupInterface
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    public static $mapping = [
        'cLocalizedName' => 'Name',
        'Links'          => 'Links'
    ];

    /**
     * @var array
     */
    private $names = [];

    /**
     * @var string
     */
    private $groupName;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $template;

    /**
     * @var bool
     */
    private $isSpecial = true;

    /**
     * @var array
     */
    private $languageID = [];

    /**
     * @var array
     */
    private $languageCode = [];

    /**
     * @var Collection
     */
    private $links;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * LinkGroup constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db    = $db;
        $this->links = new Collection();
    }

    /**
     * @inheritdoc
     */
    public function load(int $id): LinkGroupInterface
    {
        $this->id       = $id;
        $groupLanguages = $this->db->queryPrepared(
            'SELECT l.*, g.cTemplatename AS template, g.cName AS groupName, lang.kSprache 
                FROM tlinkgruppe AS g 
                JOIN tlinkgruppesprache AS l
                    ON g.kLinkgruppe = l.kLinkgruppe
                JOIN tsprache AS lang
                    ON lang.cISO = l.cISOSprache
                WHERE g.kLinkgruppe = :lgid',
            ['lgid' => $this->id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($groupLanguages) === 0) {
            return $this;
        }

        return $this->map($groupLanguages);
    }

    /**
     * @inheritdoc
     */
    public function map(array $groupLanguages): LinkGroupInterface
    {
        foreach ($groupLanguages as $groupLanguage) {
            $langID                      = (int)$groupLanguage->kSprache;
            $this->languageID[]          = $langID;
            $this->names[$langID]        = $groupLanguage->cName;
            $this->languageCode[$langID] = $groupLanguage->cISOSprache;
            $this->template              = $groupLanguage->template;
            $this->groupName             = $groupLanguage->groupName;
        }
        $this->links = (new LinkList($this->db))->createLinks(map(flatten($this->db->queryPrepared(
            'SELECT kLink
                FROM tlink
                JOIN tlinkgroupassociations a 
                    ON tlink.kLink = a.linkID
                WHERE a.linkGroupID = :lgid
                ORDER BY tlink.nSort, tlink.cName',
            ['lgid' => $this->id],
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        )), static function ($e) {
            return (int)$e;
        }));
        \executeHook(\HOOK_LINKGROUP_MAPPED, ['group' => $this]);

        return $this;
    }

    /**
     * @return string
     */
    public function getGroupName(): string
    {
        return $this->groupName;
    }

    /**
     * @param string $groupName
     */
    public function setGroupName(string $groupName): void
    {
        $this->groupName = $groupName;
    }

    /**
     * @inheritdoc
     */
    public function getName(int $idx = null): string
    {
        return $this->names[$idx ?? Shop::getLanguageID()] ?? '';
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
    public function setNames(array $names): void
    {
        $this->names = $names;
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
    public function getLinks(): Collection
    {
        return $this->links;
    }

    /**
     * @inheritdoc
     */
    public function setLinks(Collection $links): void
    {
        $this->links = $links;
    }

    /**
     * @inheritdoc
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @inheritdoc
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /**
     * @inheritdoc
     */
    public function filterLinks(callable $func): Collection
    {
        $this->links = $this->links->filter($func);

        return $this->links;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageID(): array
    {
        return $this->languageID;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageID(array $languageID): void
    {
        $this->languageID = $languageID;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageCode(): array
    {
        return $this->languageCode;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageCode(array $languageCode): void
    {
        $this->languageCode = $languageCode;
    }

    /**
     * @inheritdoc
     */
    public function isSpecial(): bool
    {
        return $this->isSpecial;
    }

    /**
     * @inheritdoc
     */
    public function setIsSpecial(bool $isSpecial): void
    {
        $this->isSpecial = $isSpecial;
    }

    /**
     * @param int $langID
     * @return bool
     */
    public function isAvailableInLanguage(int $langID): bool
    {
        return \in_array($langID, $this->languageID, true);
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
