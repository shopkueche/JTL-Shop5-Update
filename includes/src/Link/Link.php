<?php

namespace JTL\Link;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Language\LanguageHelper;
use JTL\Plugin\State;
use JTL\Shop;
use JTL\Shopsetting;
use stdClass;

/**
 * Class Link
 * @package JTL\Link
 */
final class Link extends AbstractLink
{
    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var int
     */
    protected $level = 0;

    /**
     * @var int
     */
    protected $parent = 0;

    /**
     * @var int
     */
    protected $linkGroupID = -1;

    /**
     * @var int
     */
    protected $pluginID = -1;

    /**
     * @var int
     */
    protected $linkType = -1;

    /**
     * @var int[]
     */
    protected $linkGroups = [];

    /**
     * @var string[]
     */
    protected $names = [];

    /**
     * @var string[]
     */
    protected $urls = [];

    /**
     * @var string[]
     */
    protected $seo = [];

    /**
     * @var string[]
     */
    protected $titles = [];

    /**
     * @var string[]
     */
    protected $contents = [];

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
     * @var int[]
     */
    protected $customerGroups = [];

    /**
     * @var int[]
     */
    protected $languageIDs = [];

    /**
     * @var int
     */
    protected $reference = 0;

    /**
     * @var int
     */
    protected $sort = 0;

    /**
     * @var bool
     */
    protected $ssl = false;

    /**
     * @var bool
     */
    protected $noFollow = false;

    /**
     * @var bool
     */
    protected $printButton = false;

    /**
     * @var bool
     */
    protected $isActive = false;

    /**
     * @var bool
     */
    protected $isEnabled = true;

    /**
     * @var bool
     */
    protected $isFluid = false;

    /**
     * @var bool
     */
    protected $isVisible = true;

    /**
     * @var bool
     */
    protected $visibleLoggedInOnly = false;

    /**
     * @var array
     */
    protected $languageCodes = [];

    /**
     * @var int
     */
    protected $redirectCode = 0;

    /**
     * @var string
     */
    protected $identifier = '';

    /**
     * @var bool
     */
    protected $pluginEnabled = true;

    /**
     * @var string
     */
    protected $fileName = '';

    /**
     * @var string
     */
    protected $handler = '';

    /**
     * @var string
     */
    protected $template = '';

    /**
     * @var string
     */
    protected $displayName = '';

    /**
     * @var Collection
     */
    protected $childLinks;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var int
     */
    private $currentLanguageID;

    /**
     * @var int
     */
    private $fallbackLanguageID;

    /**
     * Link constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db         = $db;
        $this->childLinks = new Collection();
        $this->initLanguageID();
    }

    /**
     *
     */
    public function __wakeup(): void
    {
        $this->initLanguageID();
    }

    /**
     *
     */
    private function initLanguageID(): void
    {
        $this->currentLanguageID = Shop::getLanguageID();
        if ($this->currentLanguageID === 0) {
            $this->currentLanguageID = (int)($_SESSION['kSprache'] ?? 1);
        }
        $this->fallbackLanguageID = LanguageHelper::getDefaultLanguage(true)->getId();
    }

    /**
     * @inheritdoc
     */
    public function load(int $id): LinkInterface
    {
        $this->id = $id;
        $link     = $this->db->queryPrepared(
            "SELECT tlink.*, loc.cISOSprache, tlink.cName AS displayName,
                loc.cName AS localizedName,  loc.cTitle AS localizedTitle,
                loc.cContent AS content, loc.cMetaDescription AS metaDescription,
                loc.cMetaKeywords AS metaKeywords, loc.cMetaTitle AS metaTitle,
                tseo.kSprache AS languageID, tseo.cSeo AS localizedUrl,
                pld.cDatei AS pluginFileName, tplugin.nStatus AS pluginState,
                pld.cDatei AS handler, pld.cTemplate AS template, pld.cFullscreenTemplate AS fullscreenTemplate,
                GROUP_CONCAT(assoc.linkGroupID) AS linkGroups
            FROM tlink
                JOIN tlinksprache loc
                    ON tlink.kLink = loc.kLink
                JOIN tsprache
                    ON tsprache.cISO = loc.cISOSprache
                JOIN tseo
                    ON tseo.cKey = 'kLink'
                    AND tseo.kKey = loc.kLink
                    AND tseo.kSprache = tsprache.kSprache
                LEFT JOIN tlinkgroupassociations assoc
                    ON assoc.linkID = loc.kLink
                LEFT JOIN tplugin
                    ON tplugin.kPlugin = tlink.kPlugin
                LEFT JOIN tpluginlinkdatei pld
                    ON tplugin.kPlugin = pld.kPlugin
                    AND tlink.kLink = pld.kLink
                WHERE tlink.kLink = :lid
                GROUP BY tseo.kSprache",
            ['lid' => $this->getRealID($id)],
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($link) === 0) {
            throw new InvalidArgumentException('Provided link id ' . $this->id . ' not found.');
        }

        return $this->map($link);
    }

    /**
     * @param int $id
     * @return int
     */
    private function getRealID(int $id): int
    {
        $reference = $this->db->queryPrepared(
            'SELECT `reference` FROM `tlink` WHERE kLink = :lid',
            ['lid' => $id],
            ReturnType::SINGLE_OBJECT
        );

        return (int)$reference->reference > 0
            ? (int)$reference->reference
            : $id;
    }

    public function deref(): void
    {
        $id     = $this->getID();
        $realID = $this->getRealID($id);
        if ($id !== $realID) {
            $this->setID($realID);
        }
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $res = [];
        foreach ($this->getLanguageIDs() as $languageID) {
            $languageCode          = $this->getLanguageCode($languageID);
            $data                  = new stdClass();
            $data->content         = $this->getContent($languageID);
            $data->url             = $this->getURL($languageID);
            $data->languageID      = $languageID;
            $data->languageCode    = $languageCode;
            $data->seo             = $this->getSEO($languageID);
            $data->id              = $this->getID();
            $data->title           = $this->getTitle($languageID);
            $data->metaDescription = $this->getMetaDescription($languageID);
            $data->metaTitle       = $this->getMetaTitle($languageID);
            $data->metaKeywords    = $this->getMetaKeyword($languageID);
            $res[$languageCode]    = $data;
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function map(array $localizedLinks): LinkInterface
    {
        \executeHook(\HOOK_LINK_PRE_MAP, ['data' => $localizedLinks]);
        foreach ($localizedLinks as $link) {
            $link = $this->sanitizeLinkData($link);
            $this->setIdentifier($link->cIdentifier ?? '');
            $this->setParent($link->kVaterLink);
            $this->setPluginID($link->kPlugin);
            $this->setPluginEnabled($link->enabled);
            $this->setLinkGroups(\array_unique(\array_map('\intval', \explode(',', $link->linkGroups))));
            $this->setLinkGroupID((int)$this->linkGroups[0]);
            $this->setLinkType($link->nLinkart);
            $this->setNoFollow($link->cNoFollow === 'Y');
            $this->setCustomerGroups(self::parseSSKAdvanced($link->cKundengruppen));
            $this->setVisibleLoggedInOnly($link->cSichtbarNachLogin === 'Y');
            $this->setPrintButton($link->cDruckButton === 'Y');
            $this->setSort($link->nSort);
            $this->setReference($link->reference);
            $this->setSSL((bool)$link->bSSL);
            $this->setIsFluid((bool)$link->bIsFluid);
            $this->setIsEnabled($this->checkActivationSetting((bool)$link->bIsActive));
            $this->setFileName($link->cDateiname ?? '');
            $this->setLanguageCode($link->cISOSprache, $link->languageID);
            $this->setContent($link->content ?? '', $link->languageID);
            $this->setMetaDescription($link->metaDescription ?? '', $link->languageID);
            $this->setMetaTitle($link->metaTitle ?? '', $link->languageID);
            $this->setMetaKeyword($link->metaKeywords ?? '', $link->languageID);
            $this->setDisplayName($link->displayName ?? '');
            $this->setName($link->localizedName ?? $link->cName, $link->languageID);
            $this->setTitle($link->localizedTitle ?? $link->cName, $link->languageID);
            $this->setLanguageID($link->languageID, $link->languageID);
            $this->setSEO($link->localizedUrl ?? '', $link->languageID);
            if ($this->getLinkType() === \LINKTYP_STARTSEITE && \EXPERIMENTAL_MULTILANG_SHOP === true) {
                $this->setURL(Shop::getURL(true, $link->languageID) . '/', $link->languageID);
            } else {
                $this->setURL(
                    $this->linkType === 2
                        ? $link->localizedUrl
                        : (Shop::getURL(true, $link->languageID) . '/' . $link->localizedUrl),
                    $link->languageID
                );
            }
            $this->setHandler($link->handler ?? '');
            $this->setTemplate($link->template ?? $link->fullscreenTemplate ?? '');
            if (($this->id === null || $this->id === 0) && isset($link->kLink)) {
                $this->setID((int)$link->kLink);
            }
        }
        $this->setChildLinks($this->buildChildLinks());
        \executeHook(\HOOK_LINK_MAPPED, ['link' => $this]);

        return $this;
    }

    /**
     * @param stdClass $link
     * @return stdClass
     */
    private function sanitizeLinkData(stdClass $link): stdClass
    {
        $link->languageID  = (int)$link->languageID;
        $link->kVaterLink  = (int)$link->kVaterLink;
        $link->kPlugin     = (int)$link->kPlugin;
        $link->bSSL        = (int)$link->bSSL;
        $link->nLinkart    = (int)$link->nLinkart;
        $link->nSort       = (int)$link->nSort;
        $link->reference   = (int)$link->reference;
        $link->enabled     = $link->pluginState === null || (int)$link->pluginState === State::ACTIVATED;
        $link->cISOSprache = $link->cISOSprache ?? Shop::getLanguageCode();
        if ($link->languageID === 0) {
            $link->languageID = $this->currentLanguageID;
        }
        if ($link->bSSL === 2) {
            $link->bSSL = 1;
        }

        return $link;
    }

    /**
     * @inheritdoc
     */
    public function checkVisibility(int $customerGroupID, int $customerID = 0): bool
    {
        $cVis   = $this->visibleLoggedInOnly === false || $customerID > 0;
        $cgVisi = \count($this->customerGroups) === 0 || \in_array($customerGroupID, $this->customerGroups, true);

        $this->isVisible = $cVis && $cgVisi && $this->isEnabled === true;

        return $this->isVisible;
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
    public function getParent(): int
    {
        return $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function setParent(int $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return int[]
     */
    public function getLinkGroups(): array
    {
        return $this->linkGroups;
    }

    /**
     * @inheritdoc
     */
    public function setLinkGroups(array $linkGroups): void
    {
        $this->linkGroups = $linkGroups;
    }

    /**
     * @inheritdoc
     */
    public function getLinkGroupID(): int
    {
        return $this->linkGroupID;
    }

    /**
     * @inheritdoc
     */
    public function setLinkGroupID(int $linkGroupID): void
    {
        $this->linkGroupID = $linkGroupID;
    }

    /**
     * @inheritdoc
     */
    public function getPluginID(): int
    {
        return $this->pluginID;
    }

    /**
     * @inheritdoc
     */
    public function setPluginID(int $pluginID): void
    {
        $this->pluginID = $pluginID;
    }

    /**
     * @inheritdoc
     */
    public function getLinkType(): int
    {
        return $this->linkType;
    }

    /**
     * @inheritdoc
     */
    public function setLinkType(int $linkType): void
    {
        $this->linkType = $linkType;
    }

    /**
     * @inheritdoc
     */
    public function getName(int $idx = null): string
    {
        return $this->names[$idx ?? $this->currentLanguageID]
            ?? $this->names[$this->fallbackLanguageID]
            ?? '';
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
        $this->names[$idx ?? $this->currentLanguageID] = $name;
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
    public function getSEOs(): array
    {
        return $this->seo;
    }

    /**
     * @inheritdoc
     */
    public function getSEO(int $idx = null): string
    {
        return $this->seo[$idx ?? $this->currentLanguageID]
            ?? $this->seo[$this->fallbackLanguageID]
            ?? '';
    }

    /**
     * @inheritdoc
     */
    public function setSEOs(array $seo): void
    {
        $this->seo = $seo;
    }

    /**
     * @inheritdoc
     */
    public function setSEO(string $url, int $idx = null): void
    {
        $this->seo[$idx ?? $this->currentLanguageID] = $url;
    }

    /**
     * @inheritdoc
     */
    public function getURL(int $idx = null): string
    {
        return $this->urls[$idx ?? $this->currentLanguageID]
            ?? '/?s=' . $this->getID()
            . '&lang=' . LanguageHelper::getIsoFromLangID($idx ?? $this->currentLanguageID)->cISO;
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
    public function setURL(string $url, int $idx = null): void
    {
        $this->urls[$idx ?? $this->currentLanguageID] = $url;
    }

    /**
     * @inheritdoc
     */
    public function setURLs(array $urls): void
    {
        $this->urls = $urls;
    }

    /**
     * @inheritdoc
     */
    public function getTitle(int $idx = null): string
    {
        return $this->titles[$idx ?? $this->currentLanguageID]
            ?? $this->titles[$this->fallbackLanguageID]
            ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getTitles(): array
    {
        return $this->titles;
    }

    /**
     * @inheritdoc
     */
    public function setTitle(string $title, int $idx = null): void
    {
        $this->titles[$idx ?? $this->currentLanguageID] = $title;
    }

    /**
     * @inheritdoc
     */
    public function setTitles(array $title): void
    {
        $this->titles = $title;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroups(): array
    {
        return $this->customerGroups;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroups(array $customerGroups): void
    {
        $this->customerGroups = $customerGroups;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageCode(int $idx = null): string
    {
        return $this->languageCodes[$idx ?? $this->currentLanguageID] ?? '';
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
    public function setLanguageCode(string $languageCode, int $idx = null): void
    {
        $this->languageCodes[$idx ?? $this->currentLanguageID] = $languageCode;
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
    public function getReference(): int
    {
        return $this->reference;
    }

    /**
     * @inheritdoc
     */
    public function setReference(int $reference): void
    {
        $this->reference = $reference;
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
    public function getSSL(): bool
    {
        return $this->ssl;
    }

    /**
     * @inheritdoc
     */
    public function setSSL(bool $ssl): void
    {
        $this->ssl = $ssl;
    }

    /**
     * @inheritdoc
     */
    public function getNoFollow(): bool
    {
        return $this->noFollow;
    }

    /**
     * @inheritdoc
     */
    public function setNoFollow(bool $noFollow): void
    {
        $this->noFollow = $noFollow;
    }

    /**
     * @inheritdoc
     */
    public function hasPrintButton(): bool
    {
        return $this->printButton;
    }

    /**
     * @return bool
     */
    public function getPrintButton(): bool
    {
        return $this->hasPrintButton();
    }

    /**
     * @inheritdoc
     */
    public function setPrintButton(bool $printButton): void
    {
        $this->printButton = $printButton;
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
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @inheritdoc
     */
    public function getIsEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @inheritdoc
     */
    public function setIsEnabled(bool $enabled): void
    {
        $this->isEnabled = $enabled;
    }

    /**
     * @inheritdoc
     */
    public function getIsFluid(): bool
    {
        return $this->isFluid;
    }

    /**
     * @inheritdoc
     */
    public function setIsFluid(bool $isFluid): void
    {
        $this->isFluid = $isFluid;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageID(int $idx = null): int
    {
        return $this->languageIDs[$idx ?? $this->currentLanguageID] ?? 0;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageID(int $languageID, int $idx = null): void
    {
        $this->languageIDs[$idx ?? $this->currentLanguageID] = $languageID;
    }

    /**
     * @return array|int[]
     */
    public function getLanguageIDs(): array
    {
        return $this->languageIDs;
    }

    /**
     * @param array $ids
     */
    public function setLanguageIDs(array $ids): void
    {
        $this->languageIDs = \array_map('\intval', $ids);
    }

    /**
     * @inheritdoc
     */
    public function getRedirectCode(): int
    {
        return $this->redirectCode;
    }

    /**
     * @inheritdoc
     */
    public function setRedirectCode(int $redirectCode): void
    {
        $this->redirectCode = $redirectCode;
    }

    /**
     * @inheritdoc
     */
    public function getVisibleLoggedInOnly(): bool
    {
        return $this->visibleLoggedInOnly;
    }

    /**
     * @inheritdoc
     */
    public function setVisibleLoggedInOnly(bool $visibleLoggedInOnly): void
    {
        $this->visibleLoggedInOnly = $visibleLoggedInOnly;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @inheritdoc
     */
    public function setIdentifier($identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @inheritdoc
     */
    public function getPluginEnabled(): bool
    {
        return $this->pluginEnabled;
    }

    /**
     * @inheritdoc
     */
    public function setPluginEnabled(bool $pluginEnabled): void
    {
        $this->pluginEnabled = $pluginEnabled;
    }

    /**
     * @inheritdoc
     */
    public function getChildLinks(): Collection
    {
        return $this->childLinks;
    }

    /**
     * @inheritdoc
     */
    public function setChildLinks($links): void
    {
        $this->childLinks = \is_array($links) ? \collect($links) : $links;
    }

    /**
     * @inheritdoc
     */
    public function addChildLink($link): void
    {
        $this->childLinks->push($link);
    }

    /**
     * @inheritdoc
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @inheritdoc
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * @inheritdoc
     */
    public function getContents(): array
    {
        return $this->contents;
    }

    /**
     * @inheritdoc
     */
    public function getContent(int $idx = null): string
    {
        return $this->contents[$idx ?? $this->currentLanguageID] ?? $this->contents[$this->fallbackLanguageID] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function setContent(string $content, int $idx = null): void
    {
        $this->contents[$idx ?? $this->currentLanguageID] = $content;
    }

    /**
     * @inheritdoc
     */
    public function setContents(array $contents): void
    {
        $this->contents = $contents;
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
        return $this->metaTitles[$idx ?? $this->currentLanguageID]
            ?? $this->metaTitles[$this->fallbackLanguageID]
            ?? '';
    }

    /**
     * @inheritdoc
     */
    public function setMetaTitle(string $metaTitle, int $idx = null): void
    {
        $this->metaTitles[$idx ?? $this->currentLanguageID] = $metaTitle;
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
        return $this->metaKeywords[$idx ?? $this->currentLanguageID]
            ?? $this->metaKeywords[$this->fallbackLanguageID]
            ?? '';
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
        $this->metaKeywords[$idx ?? $this->currentLanguageID] = $metaKeyword;
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
        return $this->metaDescriptions[$idx ?? $this->currentLanguageID]
            ?? $this->metaDescriptions[$this->fallbackLanguageID]
            ?? '';
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
        $this->metaDescriptions[$idx ?? $this->currentLanguageID] = $metaDescription;
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
    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    /**
     * @inheritdoc
     */
    public function setVisibility(bool $isVisible): void
    {
        $this->isVisible = $isVisible;
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
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @inheritdoc
     */
    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    /**
     * @inheritdoc
     */
    public function getHandler(): string
    {
        return $this->handler;
    }

    /**
     * @inheritdoc
     */
    public function setHandler(string $handler): void
    {
        $this->handler = $handler;
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
    public function buildChildLinks(): array
    {
        $links = [];
        foreach ($this->db->selectAll('tlink', 'kVaterLink', $this->getID(), 'kLink') as $id) {
            $links[] = (new self($this->db))->load((int)$id->kLink);
        }

        return $links;
    }

    /**
     * @return bool
     */
    public function hasDuplicateSpecialLink(): bool
    {
        $group = Shop::Container()->getLinkService()->getAllLinkGroups()->getLinkgroupByTemplate('specialpages');
        if ($group === null) {
            return false;
        }
        $duplicateLinks = $group->getLinks()->filter(function (LinkInterface $link) {
            return ($link->getPluginID() === 0
                && $link->getLinkType() === $this->getLinkType()
                && $link->getID() !== $this->getID()
                && (empty($this->getCustomerGroups())
                    || \in_array(-1, $this->getCustomerGroups(), true)
                    || empty($link->getCustomerGroups())
                    || \array_intersect($link->getCustomerGroups(), $this->getCustomerGroups())
                )
            );
        });

        return $duplicateLinks->isNotEmpty();
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

    /**
     * @param bool $isActive
     * @return bool
     */
    private function checkActivationSetting(bool $isActive): bool
    {
        if (!$isActive) {
            return false;
        }
        $conf = Shopsetting::getInstance()->getAll();

        switch ($this->getLinkType()) {
            case \LINKTYP_NEWSLETTER:
            case \LINKTYP_NEWSLETTERARCHIV:
                return $conf['newsletter']['newsletter_active'] === 'Y';
            default:
                return true;
        }
    }
}
