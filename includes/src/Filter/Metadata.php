<?php declare(strict_types=1);

namespace JTL\Filter;

use Illuminate\Support\Collection;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Category\MenuItem;
use JTL\Catalog\Hersteller;
use JTL\Catalog\Product\MerkmalWert;
use JTL\DB\ReturnType;
use JTL\Helpers\Category;
use JTL\Helpers\Text;
use JTL\MagicCompatibilityTrait;
use JTL\Shop;
use stdClass;
use function Functional\group;
use function Functional\map;
use function Functional\reduce_left;

/**
 * Class Metadata
 * @package JTL\Filter
 */
class Metadata implements MetadataInterface
{
    use MagicCompatibilityTrait;

    /**
     * @var ProductFilter
     */
    private $productFilter;

    /**
     * @var array
     */
    private $conf;

    /**
     * @var string
     */
    private $breadCrumb = '';

    /**
     * @var string
     */
    private $metaTitle = '';

    /**
     * @var string
     */
    private $metaDescription = '';

    /**
     * @var string
     */
    private $metaKeywords = '';

    /**
     * @var Kategorie
     */
    private $category;

    /**
     * @var Hersteller
     */
    private $manufacturer;

    /**
     * @var MerkmalWert
     */
    private $characteristicValue;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $imageURL = \BILD_KEIN_KATEGORIEBILD_VORHANDEN;

    /**
     * @var array
     */
    public static $mapping = [
        'cMetaTitle'       => 'MetaTitle',
        'cMetaDescription' => 'MetaDescription',
        'cMetaKeywords'    => 'MetaKeywords',
        'cName'            => 'Name',
        'oHersteller'      => 'Manufacturer',
        'cBildURL'         => 'ImageURL',
        'oMerkmalWert'     => 'CharacteristicValue',
        'oKategorie'       => 'Category',
        'cBrotNavi'        => 'BreadCrumb'
    ];

    /**
     * Metadata constructor.
     * @param ProductFilter $navigationsfilter
     */
    public function __construct(ProductFilter $navigationsfilter)
    {
        $this->productFilter = $navigationsfilter;
        $this->conf          = $navigationsfilter->getFilterConfig()->getConfig();
    }

    /**
     * @inheritdoc
     */
    public function getBreadCrumb(): string
    {
        return $this->breadCrumb;
    }

    /**
     * @inheritdoc
     */
    public function setBreadCrumb(string $breadCrumb): MetadataInterface
    {
        $this->breadCrumb = $breadCrumb;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    /**
     * @inheritdoc
     */
    public function setMetaTitle($metaTitle): MetadataInterface
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    /**
     * @inheritdoc
     */
    public function setMetaDescription($metaDescription): MetadataInterface
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMetaKeywords(): string
    {
        return $this->metaKeywords;
    }

    /**
     * @inheritdoc
     */
    public function setMetaKeywords($metaKeywords): MetadataInterface
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCategory(): ?Kategorie
    {
        return $this->category;
    }

    /**
     * @inheritdoc
     */
    public function setCategory(Kategorie $category): MetadataInterface
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getManufacturer(): ?Hersteller
    {
        return $this->manufacturer;
    }

    /**
     * @inheritdoc
     */
    public function setManufacturer(Hersteller $manufacturer): MetadataInterface
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCharacteristicValue(): ?MerkmalWert
    {
        return $this->characteristicValue;
    }

    /**
     * @inheritdoc
     */
    public function setCharacteristicValue(MerkmalWert $value): MetadataInterface
    {
        $this->characteristicValue = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name): MetadataInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getImageURL(): string
    {
        return $this->imageURL ?? \BILD_KEIN_KATEGORIEBILD_VORHANDEN;
    }

    /**
     * @inheritdoc
     */
    public function setImageURL(?string $imageURL): MetadataInterface
    {
        $this->imageURL = $imageURL;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasData(): bool
    {
        return !empty($this->imageURL) || !empty($this->name);
    }

    /**
     * @inheritdoc
     */
    public static function getGlobalMetaData(): array
    {
        return Shop::Container()->getCache()->get('jtl_glob_meta', static function ($cache, $id, &$content, &$tags) {
            $globalTmp = Shop::Container()->getDB()->query(
                'SELECT cName, kSprache, cWertName 
                    FROM tglobalemetaangaben ORDER BY kSprache',
                ReturnType::ARRAY_OF_OBJECTS
            );
            $content   = map(group($globalTmp, static function ($g) {
                return (int)$g->kSprache;
            }), static function ($item) {
                return reduce_left($item, static function ($value, $index, $collection, $reduction) {
                    $reduction->{$value->cName} = $value->cWertName;

                    return $reduction;
                }, new stdClass());
            });
            $tags      = [\CACHING_GROUP_CORE];

            return true;
        });
    }

    /**
     * @inheritdoc
     */
    public function getNavigationInfo(Kategorie $category = null, KategorieListe $list = null): MetadataInterface
    {
        if ($category !== null && $this->productFilter->hasCategory()) {
            $this->category = $category;
            if ($this->conf['navigationsfilter']['kategorie_bild_anzeigen'] === 'Y') {
                $this->setName($this->category->getName() ?? '');
            } elseif ($this->conf['navigationsfilter']['kategorie_bild_anzeigen'] === 'BT') {
                $this->setName($this->category->getName() ?? '');
                $this->setImageURL($this->category->getImage());
            } elseif ($this->conf['navigationsfilter']['kategorie_bild_anzeigen'] === 'B') {
                $this->setImageURL($category->getImage());
            }
        } elseif ($this->productFilter->hasManufacturer()) {
            $this->manufacturer = new Hersteller($this->productFilter->getManufacturer()->getValue());
            if ($this->conf['navigationsfilter']['hersteller_bild_anzeigen'] === 'Y') {
                $this->setName($this->manufacturer->getName() ?? '');
            } elseif ($this->conf['navigationsfilter']['hersteller_bild_anzeigen'] === 'BT') {
                $this->setName($this->manufacturer->getName() ?? '');
                $this->setImageURL($this->manufacturer->getImage());
            } elseif ($this->conf['navigationsfilter']['hersteller_bild_anzeigen'] === 'B') {
                $this->setImageURL($this->manufacturer->getImage());
            }
            if ($this->manufacturer !== null) {
                $this->setMetaTitle($this->manufacturer->cMetaTitle)
                     ->setMetaDescription($this->manufacturer->cMetaDescription)
                     ->setMetaKeywords($this->manufacturer->cMetaKeywords);
            }
        } elseif ($this->productFilter->hasCharacteristicValue()) {
            $this->characteristicValue = new MerkmalWert($this->productFilter->getCharacteristicValue()->getValue());
            if ($this->conf['navigationsfilter']['merkmalwert_bild_anzeigen'] === 'Y') {
                $this->setName($this->characteristicValue->cWert);
            } elseif ($this->conf['navigationsfilter']['merkmalwert_bild_anzeigen'] === 'BT') {
                $this->setName($this->characteristicValue->cWert)
                     ->setImageURL($this->characteristicValue->getImage());
            } elseif ($this->conf['navigationsfilter']['merkmalwert_bild_anzeigen'] === 'B') {
                $this->setImageURL($this->characteristicValue->getImage());
            }
            if ($this->characteristicValue !== null) {
                $this->setMetaTitle($this->characteristicValue->cMetaTitle)
                     ->setMetaDescription($this->characteristicValue->cMetaDescription)
                     ->setMetaKeywords($this->characteristicValue->cMetaKeywords);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function generateMetaDescription(
        array $products,
        SearchResultsInterface $searchResults,
        array $globalMeta,
        $category = null
    ): string {
        \executeHook(\HOOK_FILTER_INC_GIBNAVIMETADESCRIPTION);
        $maxLength = !empty($this->conf['metaangaben']['global_meta_maxlaenge_description'])
            ? (int)$this->conf['metaangaben']['global_meta_maxlaenge_description']
            : 0;
        if (!empty($this->metaDescription)) {
            return self::prepareMeta(
                \strip_tags($this->metaDescription),
                null,
                $maxLength
            );
        }
        // Kategorieattribut?
        $catDescription = '';
        $languageID     = $this->productFilter->getFilterConfig()->getLanguageID();
        if ($this->productFilter->hasCategory()) {
            $category = $category ?? new Kategorie($this->productFilter->getCategory()->getValue());
            if (!empty($category->cMetaDescription)) {
                // meta description via new method
                return self::prepareMeta(
                    \strip_tags($category->cMetaDescription),
                    null,
                    $maxLength
                );
            }
            if (!empty($category->categoryAttributes['meta_description']->cWert)) {
                // Hat die aktuelle Kategorie als Kategorieattribut eine Meta Description gesetzt?
                return self::prepareMeta(
                    \strip_tags($category->categoryAttributes['meta_description']->cWert),
                    null,
                    $maxLength
                );
            }
            // Hat die aktuelle Kategorie eine Beschreibung?
            if (!empty($category->cBeschreibung)) {
                $catDescription = \strip_tags(\str_replace(['<br>', '<br />'], [' ', ' '], $category->cBeschreibung));
            } elseif ($category->bUnterKategorien) {
                // Hat die aktuelle Kategorie Unterkategorien?
                $helper = Category::getInstance();
                $sub    = $helper->getCategoryById($category->kKategorie);
                if ($sub !== null && $sub->hasChildren()) {
                    $catNames       = map($sub->getChildren(), static function (MenuItem $e) {
                        return \strip_tags($e->getName());
                    });
                    $catDescription = \implode(', ', \array_filter($catNames));
                }
            }

            if (\mb_strlen($catDescription) > 1) {
                $catDescription  = \str_replace('"', '', $catDescription);
                $catDescription  = Text::htmlentitydecode($catDescription, \ENT_NOQUOTES);
                $metaDescription = !empty($globalMeta[$languageID]->Meta_Description_Praefix)
                    ? \trim(
                        \strip_tags($globalMeta[$languageID]->Meta_Description_Praefix) .
                        ' ' .
                        $catDescription
                    )
                    : \trim($catDescription);
                // Seitenzahl anhaengen ab Seite 2 (Doppelte Meta-Descriptions vermeiden, #5992)
                if ($searchResults->getOffsetStart() > 0
                    && $searchResults->getOffsetEnd() > 0
                    && $searchResults->getPages()->getCurrentPage() > 1
                ) {
                    $metaDescription .= ', ' . Shop::Lang()->get('products') . ' ' .
                        $searchResults->getOffsetStart() . ' - ' . $searchResults->getOffsetEnd();
                }

                return self::prepareMeta($metaDescription, null, $maxLength);
            }
        }
        // Keine eingestellten Metas vorhanden => generiere Standard Metas
        $metaDescription = '';
        if (\is_array($products) && \count($products) > 0) {
            \shuffle($products);
            $maxIdx      = \min(12, \count($products));
            $productName = '';
            for ($i = 0; $i < $maxIdx; ++$i) {
                $productName .= $i > 0
                    ? ' - ' . $products[$i]->cName
                    : $products[$i]->cName;
            }
            $productName = \str_replace('"', '', $productName);
            $productName = Text::htmlentitydecode($productName, \ENT_NOQUOTES);

            $metaDescription = !empty($globalMeta[$languageID]->Meta_Description_Praefix)
                ? $this->getMetaStart($searchResults) .
                ': ' .
                $globalMeta[$languageID]->Meta_Description_Praefix .
                ' ' . $productName
                : $this->getMetaStart($searchResults) . ': ' . $productName;
            // Seitenzahl anhaengen ab Seite 2 (Doppelte Meta-Descriptions vermeiden, #5992)
            if ($searchResults->getOffsetStart() > 0
                && $searchResults->getOffsetEnd() > 0
                && $searchResults->getPages()->getCurrentPage() > 1
            ) {
                $metaDescription .= ', ' . Shop::Lang()->get('products') . ' ' .
                    $searchResults->getOffsetStart() . ' - ' . $searchResults->getOffsetEnd();
            }
        }

        return self::prepareMeta(\strip_tags($metaDescription), null, $maxLength);
    }

    /**
     * @inheritdoc
     */
    public function generateMetaKeywords($products, Kategorie $category = null): string
    {
        \executeHook(\HOOK_FILTER_INC_GIBNAVIMETAKEYWORDS);
        if (!empty($this->metaKeywords)) {
            return \strip_tags($this->metaKeywords);
        }
        // Kategorieattribut?
        if ($this->productFilter->hasCategory()) {
            $category = $category ?? new Kategorie($this->productFilter->getCategory()->getValue());
            if (!empty($category->cMetaKeywords)) {
                // meta keywords via new method
                return \strip_tags($category->cMetaKeywords);
            }
            if (!empty($category->categoryAttributes['meta_keywords']->cWert)) {
                // Hat die aktuelle Kategorie als Kategorieattribut einen Meta Keywords gesetzt?
                return \strip_tags($category->categoryAttributes['meta_keywords']->cWert);
            }
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function generateMetaTitle($searchResults, $globalMeta, Kategorie $category = null): string
    {
        \executeHook(\HOOK_FILTER_INC_GIBNAVIMETATITLE);
        $languageID = $this->productFilter->getFilterConfig()->getLanguageID();
        $append     = $this->conf['metaangaben']['global_meta_title_anhaengen'] === 'Y';
        if (!empty($this->metaTitle)) {
            $metaTitle = \strip_tags($this->metaTitle);
            if ($append === true && !empty($globalMeta[$languageID]->Title)) {
                return $this->truncateMetaTitle(
                    $metaTitle . ' ' .
                    $globalMeta[$languageID]->Title
                );
            }

            return $this->truncateMetaTitle($metaTitle);
        }
        // Set Default Titles
        $metaTitle = $this->getMetaStart($searchResults);
        $metaTitle = \str_replace('"', "'", $metaTitle);
        $metaTitle = Text::htmlentitydecode($metaTitle, \ENT_NOQUOTES);
        if ($this->productFilter->hasCategory()) {
            $category = $category ?? new Kategorie($this->productFilter->getCategory()->getValue());
            if (!empty($category->cTitleTag)) {
                // meta title via new method
                $metaTitle = \strip_tags($category->cTitleTag);
                $metaTitle = \str_replace('"', "'", $metaTitle);
                $metaTitle = Text::htmlentitydecode($metaTitle, \ENT_NOQUOTES);
            } elseif (!empty($category->categoryAttributes['meta_title']->cWert)) {
                // Hat die aktuelle Kategorie als Kategorieattribut einen Meta Title gesetzt?
                $metaTitle = \strip_tags($category->categoryAttributes['meta_title']->cWert);
                $metaTitle = \str_replace('"', "'", $metaTitle);
                $metaTitle = Text::htmlentitydecode($metaTitle, \ENT_NOQUOTES);
            }
        }
        // Seitenzahl anhaengen ab Seite 2 (Doppelte Titles vermeiden, #5992)
        if ($searchResults->getPages()->getCurrentPage() > 1) {
            $metaTitle .= ', ' . Shop::Lang()->get('page') . ' ' .
                $searchResults->getPages()->getCurrentPage();
        }
        if ($append === true && !empty($globalMeta[$languageID]->Title)) {
            $metaTitle .= ' - ' . $globalMeta[$languageID]->Title;
        }
        // @todo: temp. fix to avoid destroyed header
        $metaTitle = \str_replace(['<', '>'], ['&lt;', '&gt;'], $metaTitle);

        return $this->truncateMetaTitle($metaTitle);
    }

    /**
     * Erstellt für die NaviMetas die gesetzten Mainwords + Filter und stellt diese vor jedem Meta an.
     *
     * @param SearchResultsInterface $searchResults
     * @return string
     */
    public function getMetaStart($searchResults): string
    {
        $parts = new Collection();
        // MerkmalWert
        if ($this->productFilter->hasCharacteristicValue()) {
            $parts->push($this->productFilter->getCharacteristicValue()->getName());
        } elseif ($this->productFilter->hasCategory()) { // Kategorie
            $parts->push($this->productFilter->getCategory()->getName());
        } elseif ($this->productFilter->hasManufacturer()) { // Hersteller
            $parts->push($this->productFilter->getManufacturer()->getName());
        } elseif ($this->productFilter->hasSearch()) { // Suchbegriff
            $parts->push($this->productFilter->getSearch()->getName());
        } elseif ($this->productFilter->hasSearchQuery()) { // Suchbegriff
            $parts->push($this->productFilter->getSearchQuery()->getName());
        } elseif ($this->productFilter->hasSearchSpecial()) { // Suchspecial
            $parts->push($this->productFilter->getSearchSpecial()->getName());
        }
        // Kategoriefilter
        if ($this->productFilter->hasCategoryFilter()) {
            $parts->push($this->productFilter->getCategoryFilter()->getName());
        }
        // Herstellerfilter
        if ($this->productFilter->hasManufacturerFilter()) {
            $parts->push($this->productFilter->getManufacturerFilter()->getName());
        }
        // Suchbegrifffilter
        $parts = $parts->merge(
            \collect($this->productFilter->getSearchFilter())
            ->map(static function (FilterInterface $filter) {
                return $filter->getName();
            })
            ->reject(static function ($name) {
                return $name === null;
            })
        );
        // Suchspecialfilter
        if ($this->productFilter->hasSearchSpecialFilter()) {
            switch ($this->productFilter->getSearchSpecialFilter()->getValue()) {
                case \SEARCHSPECIALS_BESTSELLER:
                    $parts->push(Shop::Lang()->get('bestsellers'));
                    break;

                case \SEARCHSPECIALS_SPECIALOFFERS:
                    $parts->push(Shop::Lang()->get('specialOffers'));
                    break;

                case \SEARCHSPECIALS_NEWPRODUCTS:
                    $parts->push(Shop::Lang()->get('newProducts'));
                    break;

                case \SEARCHSPECIALS_TOPOFFERS:
                    $parts->push(Shop::Lang()->get('topOffers'));
                    break;

                case \SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $parts->push(Shop::Lang()->get('upcomingProducts'));
                    break;

                case \SEARCHSPECIALS_TOPREVIEWS:
                    $parts->push(Shop::Lang()->get('topReviews'));
                    break;

                default:
                    break;
            }
        }
        // MerkmalWertfilter
        $parts = $parts->merge(
            \collect($this->productFilter->getCharacteristicFilter())
            ->map(static function (FilterInterface $filter) {
                return $filter->getName();
            })
            ->reject(static function ($name) {
                return $name === null;
            })
        );

        return $parts->implode(' ');
    }

    /**
     * @inheritdoc
     */
    public function truncateMetaTitle($cTitle): string
    {
        return ($length = (int)$this->conf['metaangaben']['global_meta_maxlaenge_title']) > 0
            ? \mb_substr($cTitle, 0, $length)
            : $cTitle;
    }

    /**
     * @inheritdoc
     */
    public function getHeader(): string
    {
        if ($this->productFilter->hasCategory()) {
            $this->breadCrumb = $this->productFilter->getCategory()->getName();

            return $this->breadCrumb ?? '';
        }
        if ($this->productFilter->hasManufacturer()) {
            $this->breadCrumb = $this->productFilter->getManufacturer()->getName();

            return Shop::Lang()->get('productsFrom') . ' ' . $this->breadCrumb;
        }
        if ($this->productFilter->hasCharacteristicValue()) {
            $this->breadCrumb = $this->productFilter->getCharacteristicValue()->getName();

            return Shop::Lang()->get('productsWith') . ' ' . $this->breadCrumb;
        }
        if ($this->productFilter->hasSearchSpecial()) {
            $this->breadCrumb = $this->productFilter->getSearchSpecial()->getName();

            return $this->breadCrumb ?? '';
        }
        if ($this->productFilter->hasSearch()) {
            $this->breadCrumb = $this->productFilter->getSearch()->getName();
        } elseif ($this->productFilter->getSearchQuery()->isInitialized()) {
            $this->breadCrumb = $this->productFilter->getSearchQuery()->getName();
        }
        if (!empty($this->productFilter->getSearch()->getName())
            || !empty($this->productFilter->getSearchQuery()->getName())
        ) {
            return Shop::Lang()->get('for') . ' ' . $this->breadCrumb;
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function getExtendedView(int $viewType = 0): stdClass
    {
        $conf = $this->conf['artikeluebersicht'];
        if (!isset($_SESSION['oErweiterteDarstellung'])) {
            $defaultViewType              = 0;
            $extendedView                 = new stdClass();
            $extendedView->cURL_arr       = [];
            $extendedView->nAnzahlArtikel = \ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;

            if ($this->productFilter->hasCategory()) {
                $category = new Kategorie($this->productFilter->getCategory()->getValue());
                if (!empty($category->categoryFunctionAttributes[\KAT_ATTRIBUT_DARSTELLUNG])) {
                    $defaultViewType = (int)$category->categoryFunctionAttributes[\KAT_ATTRIBUT_DARSTELLUNG];
                }
            }
            if ($viewType === 0 && (int)$conf['artikeluebersicht_erw_darstellung_stdansicht'] > 0) {
                $defaultViewType = (int)$conf['artikeluebersicht_erw_darstellung_stdansicht'];
            }
            if ($defaultViewType > 0) {
                switch ($defaultViewType) {
                    case \ERWDARSTELLUNG_ANSICHT_LISTE:
                        $extendedView->nDarstellung = \ERWDARSTELLUNG_ANSICHT_LISTE;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $extendedView->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$conf['artikeluebersicht_anzahl_darstellung1'] !== 0) {
                            $extendedView->nAnzahlArtikel = (int)$conf['artikeluebersicht_anzahl_darstellung1'];
                        }
                        break;
                    case \ERWDARSTELLUNG_ANSICHT_GALERIE:
                        $extendedView->nDarstellung = \ERWDARSTELLUNG_ANSICHT_GALERIE;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $extendedView->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$conf['artikeluebersicht_anzahl_darstellung2'] !== 0) {
                            $extendedView->nAnzahlArtikel = (int)$conf['artikeluebersicht_anzahl_darstellung2'];
                        }
                        break;
                    default: // when given invalid option from wawi attribute
                        $viewType = \ERWDARSTELLUNG_ANSICHT_LISTE;
                        if (isset($conf['artikeluebersicht_erw_darstellung_stdansicht'])
                            && (int)$conf['artikeluebersicht_erw_darstellung_stdansicht'] > 0
                        ) { // fallback to configured default
                            $viewType = (int)$conf['artikeluebersicht_erw_darstellung_stdansicht'];
                        }
                        $extendedView->nDarstellung = $viewType;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $extendedView->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$conf['artikeluebersicht_anzahl_darstellung1'] > 0) {
                            $extendedView->nAnzahlArtikel = (int)$conf['artikeluebersicht_anzahl_darstellung1'];
                        }
                        break;
                }
            } else {
                // Std ist Listendarstellung
                $extendedView->nDarstellung = \ERWDARSTELLUNG_ANSICHT_LISTE;
                if (isset($_SESSION['ArtikelProSeite'])) {
                    $extendedView->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                } elseif ((int)$conf['artikeluebersicht_anzahl_darstellung1'] !== 0) {
                    $extendedView->nAnzahlArtikel = (int)$conf['artikeluebersicht_anzahl_darstellung1'];
                }
            }
            $_SESSION['oErweiterteDarstellung'] = $extendedView;
        }
        $extendedView = $_SESSION['oErweiterteDarstellung'];
        if ($viewType > 0) {
            $extendedView->nDarstellung = $viewType;
            switch ($extendedView->nDarstellung) {
                case \ERWDARSTELLUNG_ANSICHT_LISTE:
                    $extendedView->nAnzahlArtikel = \ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                    if ((int)$conf['artikeluebersicht_anzahl_darstellung1'] > 0) {
                        $extendedView->nAnzahlArtikel = (int)$conf['artikeluebersicht_anzahl_darstellung1'];
                    }
                    break;
                case \ERWDARSTELLUNG_ANSICHT_GALERIE:
                default:
                    $extendedView->nAnzahlArtikel = \ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                    if ((int)$conf['artikeluebersicht_anzahl_darstellung2'] > 0) {
                        $extendedView->nAnzahlArtikel = (int)$conf['artikeluebersicht_anzahl_darstellung2'];
                    }
                    break;
            }

            if (isset($_SESSION['ArtikelProSeite'])) {
                $extendedView->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
            }
        }
        $naviURL  = $this->productFilter->getFilterURL()->getURL();
        $naviURL .= \mb_strpos($naviURL, '?') === false ? '?ed=' : '&amp;ed=';

        $extendedView->cURL_arr[\ERWDARSTELLUNG_ANSICHT_LISTE]   = $naviURL . \ERWDARSTELLUNG_ANSICHT_LISTE;
        $extendedView->cURL_arr[\ERWDARSTELLUNG_ANSICHT_GALERIE] = $naviURL . \ERWDARSTELLUNG_ANSICHT_GALERIE;

        return $extendedView;
    }

    /**
     * @inheritdoc
     */
    public function checkNoIndex(): bool
    {
        if (!isset($_SERVER['SCRIPT_NAME'])) {
            return false;
        }
        $noIndex = false;
        switch (\basename($_SERVER['SCRIPT_NAME'])) {
            case 'wartung.php':
            case 'navi.php':
            case 'bestellabschluss.php':
            case 'bestellvorgang.php':
            case 'jtl.php':
            case 'pass.php':
            case 'registrieren.php':
            case 'warenkorb.php':
            case 'wunschliste.php':
                $noIndex = true;
                break;
            default:
                break;
        }
        if ($this->productFilter->hasSearch()) {
            $noIndex = true;
        }
        if (!$noIndex) {
            $noIndex = $this->productFilter->getFilterCount() > 1
                || ($this->conf['global']['global_merkmalwert_url_indexierung'] === 'N'
                    && $this->productFilter->hasCharacteristicValue()
                    && $this->productFilter->getCharacteristicValue()->getValue() > 0);
        }

        return $noIndex;
    }

    /**
     * return trimmed description without (double) line breaks
     *
     * @param string $cDesc
     * @return string
     */
    public static function truncateMetaDescription(string $cDesc): string
    {
        $conf      = Shop::getSettings([\CONF_METAANGABEN]);
        $maxLength = !empty($conf['metaangaben']['global_meta_maxlaenge_description'])
            ? (int)$conf['metaangaben']['global_meta_maxlaenge_description']
            : 0;

        return self::prepareMeta($cDesc, null, $maxLength);
    }

    /**
     * @param string      $metaProposal the proposed meta text value.
     * @param string|null $metaSuffix append suffix to meta value that wont be shortened
     * @param int|null    $maxLength $metaProposal will be truncated to $maxlength - \mb_strlen($metaSuffix) characters
     * @return string truncated meta value with optional suffix (always appended if set)
     */
    public static function prepareMeta(string $metaProposal, ?string $metaSuffix = null, ?int $maxLength = null): string
    {
        $metaStr = \trim(\preg_replace('/\s\s+/', ' ', Text::htmlentitiesOnce($metaProposal)));

        return Text::htmlentitiesSubstr($metaStr, $maxLength ?? 0) . ($metaSuffix ?? '');
    }

    /**
     * @inheritdoc
     */
    public function __isset($name)
    {
        if (\property_exists($this, $name)) {
            return true;
        }
        $mapped = self::getMapping($name);
        if ($mapped === null) {
            return false;
        }
        $method = 'get' . $mapped;
        $result = $this->$method();

        return $result !== null;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res                  = \get_object_vars($this);
        $res['conf']          = '*truncated*';
        $res['productFilter'] = '*truncated*';

        return $res;
    }
}
