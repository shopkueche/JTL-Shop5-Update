<?php declare(strict_types=1);

namespace JTL\Boxes\Items;

use JTL\Boxes\Renderer\DefaultRenderer;
use JTL\Boxes\Type;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\ArtikelListe;
use JTL\Helpers\GeneralObject;
use JTL\MagicCompatibilityTrait;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use function Functional\false;
use function Functional\first;

/**
 * Class AbstractBox
 * @package JTL\Boxes\Items
 */
abstract class AbstractBox implements BoxInterface
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    private static $mapping = [
        'compatName'     => 'Name',
        'cName'          => 'Name',
        'anzeigen'       => 'ShowCompat',
        'kBox'           => 'ID',
        'kBoxvorlage'    => 'BaseType',
        'nSort'          => 'Sort',
        'eTyp'           => 'Type',
        'cTitel'         => 'Title',
        'cInhalt'        => 'Content',
        'nAnzeigen'      => 'ItemCount',
        'cURL'           => 'URL',
        'Artikel'        => 'Products',
        'oArtikel_arr'   => 'Products',
        'oContainer_arr' => 'Children',
        'bContainer'     => 'ContainerCheckCompat',
        'bAktiv'         => 'IsActive',
        'kContainer'     => 'ContainerID',
        'cFilter'        => 'Filter',
        'oPlugin'        => 'Plugin',
    ];

    /**
     * @var array
     */
    private static $validPageTypes = [
        \PAGE_UNBEKANNT,
        \PAGE_ARTIKEL,
        \PAGE_ARTIKELLISTE,
        \PAGE_WARENKORB,
        \PAGE_MEINKONTO,
        \PAGE_KONTAKT,
        \PAGE_NEWS,
        \PAGE_NEWSLETTER,
        \PAGE_LOGIN,
        \PAGE_REGISTRIERUNG,
        \PAGE_BESTELLVORGANG,
        \PAGE_BEWERTUNG,
        \PAGE_PASSWORTVERGESSEN,
        \PAGE_WARTUNG,
        \PAGE_WUNSCHLISTE,
        \PAGE_VERGLEICHSLISTE,
        \PAGE_STARTSEITE,
        \PAGE_VERSAND,
        \PAGE_AGB,
        \PAGE_DATENSCHUTZ,
        \PAGE_LIVESUCHE,
        \PAGE_HERSTELLER,
        \PAGE_SITEMAP,
        \PAGE_GRATISGESCHENK,
        \PAGE_WRB,
        \PAGE_PLUGIN,
        \PAGE_NEWSLETTERARCHIV,
        \PAGE_EIGENE,
        \PAGE_AUSWAHLASSISTENT,
        \PAGE_BESTELLABSCHLUSS,
        \PAGE_404,
        \PAGE_BESTELLSTATUS,
        \PAGE_NEWSMONAT,
        \PAGE_NEWSDETAIL,
        \PAGE_NEWSKATEGORIE
    ];

    /**
     * @var int
     */
    protected $itemCount = 0;

    /**
     * @var bool
     */
    protected $show;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $url = '';

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var string
     */
    protected $templateFile = '';

    /**
     * @var PluginInterface|null
     */
    protected $plugin;

    /**
     * @var PluginInterface|null
     */
    protected $extension;

    /**
     * @var int
     */
    protected $containerID = 0;

    /**
     * @var string
     */
    protected $position;

    /**
     * @var string|array
     */
    protected $title;

    /**
     * @var string|array
     */
    protected $content;

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var int
     */
    protected $baseType = 0;

    /**
     * @var int
     */
    protected $customID = 0;

    /**
     * @var int
     */
    protected $sort = 0;

    /**
     * @var bool
     */
    protected $isActive = true;

    /**
     * @var Artikel[]
     */
    protected $products;

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var string|null
     */
    protected $json;

    /**
     * @var array
     */
    protected $children = [];

    /**
     * @var string
     */
    protected $html = '';

    /**
     * @var string
     */
    protected $renderedContent = '';

    /**
     * @var bool
     */
    protected $supportsRevisions = false;

    /**
     * @var array
     */
    protected $filter;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $sortByPageID = [];

    /**
     * @inheritdoc
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getRenderer(): string
    {
        return DefaultRenderer::class;
    }

    /**
     * @param string $attrbute
     * @param string $method
     */
    public function addMapping(string $attrbute, string $method): void
    {
        self::$mapping[$attrbute] = $method;
    }

    /**
     * @inheritdoc
     */
    public function map(array $boxData): void
    {
        $data = first($boxData);
        if ($data->eTyp === null) {
            // containers do not have a lot of data..
            $data->eTyp      = Type::CONTAINER;
            $data->cTitel    = '';
            $data->cTemplate = 'box_container.tpl';
            $data->cName     = '';
        }
        $this->setID((int)$data->kBox);
        $this->setBaseType((int)$data->kBoxvorlage);
        $this->setCustomID((int)$data->kCustomID);
        $this->setContainerID((int)$data->kContainer);
        $this->setSort((int)$data->nSort);
        $this->setIsActive(true);
        if ($this->products === null) {
            $this->products = new ArtikelListe();
        }
        if (!empty($data->kSprache)) {
            $this->setTitle([]);
            $this->setContent([]);
        } else {
            $this->setTitle(!empty($data->cTitel) ? $data->cTitel : $data->cName);
            $this->setContent('');
        }
        $this->setPosition($data->ePosition);
        $this->setType($data->eTyp);

        if ($this->getType() !== Type::PLUGIN && \strpos($data->cTemplate, 'boxes/') !== 0) {
            $data->cTemplate = 'boxes/' . $data->cTemplate;
        }
        $this->setTemplateFile($data->cTemplate);
        $this->setName($data->cName);

        foreach (self::$validPageTypes as $pageType) {
            $this->filter[$pageType] = false;
        }
        foreach ($boxData as $box) {
            $pageIDs            = \array_map('\intval', \explode(',', $box->pageIDs));
            $sort               = \array_map('\intval', \explode(',', $box->sortBypageIDs ?? ''));
            $this->sortByPageID = \array_combine($pageIDs, $sort);
            if (!empty($box->cFilter)) {
                $this->filter[(int)$box->kSeite] = \array_map('\intval', \explode(',', $box->cFilter));
            } else {
                $pageVisibilities = \array_map('\intval', \explode(',', $box->pageVisibilities));
                $filter           = \array_combine($pageIDs, $pageVisibilities);
                foreach ($filter as $pageID => $visibility) {
                    $this->filter[$pageID] = (bool)$visibility;
                }
            }
            if (!empty($box->kSprache)) {
                if (!\is_array($this->content)) {
                    $this->content = [];
                }
                if (!\is_array($this->title)) {
                    $this->title = [];
                }
                $this->content[(int)$box->kSprache] = $box->cInhalt;
                $this->title[(int)$box->kSprache]   = $box->cTitel;
            }
        }
        \ksort($this->filter);

        if (false($this->filter)) {
            $this->setIsActive(false);
        }
        if (!\is_bool($this->show)) {
            // may be overridden in concrete classes' __construct
            $this->setShow($this->isActive());
        }
        $this->init();
    }

    /**
     * @param int $pageID
     * @return bool|array
     */
    public function isVisibleOnPage(int $pageID)
    {
        return $this->filter[$pageID] ?? false;
    }

    /**
     * @inheritdoc
     */
    public function isBoxVisible(int $pageType = \PAGE_UNBEKANNT, int $pageID = 0): bool
    {
        if ($this->show === false) {
            return false;
        }
        $vis = empty($this->filter) || (isset($this->filter[$pageType]) && $this->filter[$pageType] === true);

        if ($vis === false && $pageID > 0 && GeneralObject::isCountable($pageType, $this->filter)) {
            $vis = \in_array($pageID, $this->filter[$pageType], true);
        }

        return $vis;
    }

    /**
     * @return bool
     */
    public function show(): bool
    {
        return $this->show;
    }

    /**
     * @return bool
     */
    public function getShow(): bool
    {
        return $this->show;
    }

    /**
     * @param bool $show
     */
    public function setShow(bool $show): void
    {
        $this->show = $show;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getURL(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setURL(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTemplateFile(): string
    {
        return $this->templateFile;
    }

    /**
     * @param string $templateFile
     */
    public function setTemplateFile(string $templateFile): void
    {
        $this->templateFile = $templateFile;
    }

    /**
     * @return null|PluginInterface
     */
    public function getPlugin(): ?PluginInterface
    {
        return $this->plugin;
    }

    /**
     * @param null|PluginInterface $plugin
     */
    public function setPlugin(?PluginInterface $plugin): void
    {
        $this->plugin = $plugin;
    }

    /**
     * @return null|PluginInterface
     */
    public function getExtension(): ?PluginInterface
    {
        return $this->extension;
    }

    /**
     * @param null|PluginInterface $extension
     */
    public function setExtension(?PluginInterface $extension): void
    {
        $this->extension = $extension;
    }

    /**
     * @return int
     */
    public function getContainerID(): int
    {
        return $this->containerID;
    }

    /**
     * @param int $containerID
     */
    public function setContainerID(int $containerID): void
    {
        $this->containerID = $containerID;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition(string $position): void
    {
        $this->position = $position;
    }

    /**
     * @inheritdoc
     */
    public function getTitle($idx = null): string
    {
        if (\is_string($this->title)) {
            return $this->title;
        }
        $idx = $idx ?? Shop::getLanguageID();

        return $this->title[$idx] ?? '';
    }

    /**
     * @param string|array $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @inheritdoc
     */
    public function getContent($idx = null): string
    {
        if (\is_string($this->content)) {
            return $this->content;
        }
        $idx = $idx ?? Shop::getLanguageID();

        return $this->content[$idx] ?? '';
    }

    /**
     * @param string|array $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getBaseType(): int
    {
        return $this->baseType;
    }

    /**
     * @param int $type
     */
    public function setBaseType(int $type): void
    {
        $this->baseType = $type;
    }

    /**
     * @inheritdoc
     */
    public function getCustomID(): int
    {
        return $this->customID;
    }

    /**
     * @inheritdoc
     */
    public function setCustomID(int $id): void
    {
        $this->customID = $id;
    }

    /**
     * @inheritDoc
     */
    public function getSort(?int $pageID = null): int
    {
        return $pageID === null ? $this->sort : $this->sortByPageID[$pageID] ?? 0;
    }

    /**
     * @inheritDoc
     */
    public function setSort(int $sort, ?int $pageID = null): void
    {
        if ($pageID !== null) {
            $this->sortByPageID[$pageID] = $sort;
        } else {
            $this->sort = $sort;
        }
    }

    /**
     * @inheritDoc
     */
    public function getItemCount(): int
    {
        return $this->itemCount;
    }

    /**
     * @inheritDoc
     */
    public function setItemCount(int $count): void
    {
        $this->itemCount = $count;
    }

    /**
     * @inheritDoc
     */
    public function supportsRevisions(): bool
    {
        return $this->supportsRevisions;
    }

    /**
     * @inheritDoc
     */
    public function setSupportsRevisions(bool $supportsRevisions): void
    {
        $this->supportsRevisions = $supportsRevisions;
    }

    /**
     * @return bool
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
     * @return string
     */
    public function getShowCompat(): string
    {
        return $this->show === true ? 'Y' : 'N';
    }

    /**
     * @param string $show
     */
    public function setShowCompat(string $show): void
    {
        $this->show = $show === 'Y';
    }

    /**
     * @inheritdoc
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @inheritdoc
     */
    public function setProducts($products): void
    {
        $this->products = $products;
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
    public function getFilter(int $idx = null)
    {
        return $idx === null ? $this->filter : $this->filter[$idx] ?? true;
    }

    /**
     * @inheritdoc
     */
    public function setFilter(array $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @inheritdoc
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getJSON(): string
    {
        return $this->json;
    }

    /**
     * @inheritdoc
     */
    public function setJSON(string $json): void
    {
        $this->json = $json;
    }

    /**
     * @inheritdoc
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @inheritdoc
     */
    public function setChildren(array $chilren): void
    {
        $this->children = $chilren[$this->getID()] ?? [];
    }

    /**
     * @return string
     */
    public function getHTML(): string
    {
        return $this->html;
    }

    /**
     * @param string $html
     */
    public function setHTML(string $html): void
    {
        $this->html = $html;
    }

    /**
     * @inheritdoc
     */
    public function getRenderedContent(): string
    {
        return $this->renderedContent;
    }

    /**
     * @inheritdoc
     */
    public function setRenderedContent(string $renderedContent): void
    {
        $this->renderedContent = $renderedContent;
    }

    /**
     * special json string for sidebar clouds
     *
     * @param array  $cloud
     * @param string $nSpeed
     * @param string $nOpacity
     * @param bool   $color
     * @param bool   $cColorHover
     * @return string
     */
    public static function getJSONString(
        $cloud,
        $nSpeed = '1',
        $nOpacity = '0.2',
        $color = false,
        $cColorHover = false
    ): string {
        $iCur = 0;
        $iMax = 15;
        if (!\count($cloud)) {
            return '';
        }
        $tags                       = [];
        $tags['options']['speed']   = $nSpeed;
        $tags['options']['opacity'] = $nOpacity;
        $gibTagFarbe                = static function () {
            $cColor = '';
            $cCodes = ['00', '33', '66', '99', 'CC', 'FF'];
            for ($i = 0; $i < 3; $i++) {
                $cColor .= $cCodes[\rand(0, \count($cCodes) - 1)];
            }

            return '0x' . $cColor;
        };

        foreach ($cloud as $item) {
            if ($iCur++ >= $iMax) {
                break;
            }
            $name           = $item->cName ?? $item->cSuche;
            $randomColor    = (!$color || !$cColorHover) ? $gibTagFarbe() : '';
            $name           = \urlencode($name);
            $name           = \str_replace('+', ' ', $name); /* fix :) */
            $tags['tags'][] = [
                'name'  => $name,
                'url'   => $item->cURL,
                'size'  => (\count($cloud) <= 5) ? '100' : (string)($item->Klasse * 10), /* 10 bis 100 */
                'color' => $color ?: $randomColor,
                'hover' => $cColorHover ?: $randomColor
            ];
        }

        return \urlencode(\json_encode($tags));
    }

    /**
     * @return int
     */
    public function getContainerCheckCompat(): int
    {
        return $this->getBaseType() === \BOX_CONTAINER ? 1 : 0;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res           = \get_object_vars($this);
        $res['config'] = '*truncated*';

        return $res;
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
    }
}
