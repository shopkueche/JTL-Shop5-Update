<?php

namespace JTL\Catalog;

use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Product\Artikel;
use JTL\Filter\ProductFilter;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Link\Link;
use JTL\Link\LinkInterface;
use JTL\Services\JTL\LinkServiceInterface;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class Navigation
 * @package JTL\Catalog
 */
class Navigation
{
    /**
     * @var LanguageHelper
     */
    private $language;

    /**
     * @var int
     */
    private $pageType = \PAGE_UNBEKANNT;

    /**
     * @var LinkServiceInterface
     */
    private $linkService;

    /**
     * @var KategorieListe|null
     */
    private $categoryList;

    /**
     * @var string
     */
    private $baseURL;

    /**
     * @var Artikel|null
     */
    private $product;

    /**
     * @var Link|null
     */
    private $link;

    /**
     * @var string|null
     */
    private $linkURL;

    /**
     * @var ProductFilter|null
     */
    private $productFilter;

    /**
     * @var NavigationEntry|null
     */
    private $customNavigationEntry;

    /**
     * Navigation constructor.
     *
     * @param LanguageHelper       $language
     * @param LinkServiceInterface $linkService
     */
    public function __construct(LanguageHelper $language, LinkServiceInterface $linkService)
    {
        $this->language    = $language;
        $this->linkService = $linkService;
        $this->baseURL     = Shop::getURL() . '/';
    }

    /**
     * @return int
     */
    public function getPageType(): int
    {
        return $this->pageType;
    }

    /**
     * @param int $pageType
     */
    public function setPageType(int $pageType): void
    {
        $this->pageType = $pageType;
    }

    /**
     * @return KategorieListe|null
     */
    public function getCategoryList(): KategorieListe
    {
        return $this->categoryList;
    }

    /**
     * @param KategorieListe|null $categoryList
     */
    public function setCategoryList(KategorieListe $categoryList): void
    {
        $this->categoryList = $categoryList;
    }

    /**
     * @return string
     */
    public function getBaseURL(): string
    {
        return $this->baseURL;
    }

    /**
     * @param string $baseURL
     */
    public function setBaseURL(string $baseURL): void
    {
        $this->baseURL = $baseURL;
    }

    /**
     * @return Artikel|null
     */
    public function getProduct(): ?Artikel
    {
        return $this->product;
    }

    /**
     * @param Artikel|null $product
     */
    public function setProduct(Artikel $product): void
    {
        $this->product = $product;
    }

    /**
     * @return LinkInterface|null
     */
    public function getLink(): ?LinkInterface
    {
        return $this->link;
    }

    /**
     * @param LinkInterface|null $link
     */
    public function setLink(LinkInterface $link): void
    {
        $this->link = $link;
    }

    /**
     * @return string|null
     */
    public function getLinkURL(): ?string
    {
        return $this->linkURL;
    }

    /**
     * @param string $url
     */
    public function setLinkURL(string $url): void
    {
        $this->linkURL = $url;
    }

    /**
     * @return ProductFilter|null
     */
    public function getProductFilter(): ?ProductFilter
    {
        return $this->productFilter;
    }

    /**
     * @param ProductFilter|null $productFilter
     */
    public function setProductFilter(ProductFilter $productFilter): void
    {
        $this->productFilter = $productFilter;
    }

    /**
     * @return NavigationEntry|null
     */
    public function getCustomNavigationEntry(): ?NavigationEntry
    {
        return $this->customNavigationEntry;
    }

    /**
     * @param NavigationEntry|null $customNavigationEntry
     */
    public function setCustomNavigationEntry(NavigationEntry $customNavigationEntry): void
    {
        $this->customNavigationEntry = $customNavigationEntry;
    }

    /**
     * @return string
     */
    private function getProductFilterName(): string
    {
        if ($this->productFilter->hasCategory()) {
            return $this->productFilter->getCategory()->getName() ?? '';
        }
        if ($this->productFilter->hasManufacturer()) {
            return Shop::Lang()->get('productsFrom') . ' ' . $this->productFilter->getManufacturer()->getName();
        }
        if ($this->productFilter->hasCharacteristicValue()) {
            return Shop::Lang()->get('productsWith') . ' ' . $this->productFilter->getCharacteristicValue()->getName();
        }
        if ($this->productFilter->hasSearchSpecial()) {
            return $this->productFilter->getSearchSpecial()->getName() ?? '';
        }
        $name = '';
        if ($this->productFilter->hasSearch()) {
            $name = $this->productFilter->getSearch()->getName();
        } elseif ($this->productFilter->getSearchQuery()->isInitialized()) {
            $name = $this->productFilter->getSearchQuery()->getName();
        }
        if (!empty($this->productFilter->getSearch()->getName())
            || !empty($this->productFilter->getSearchQuery()->getName())
        ) {
            return Shop::Lang()->get('for') . ' ' . $name;
        }

        return '';
    }

    /**
     * @return array
     */
    public function createNavigation(): array
    {
        $breadCrumb = [];
        $ele0       = new NavigationEntry();
        $ele0->setName($this->language->get('startpage', 'breadcrumb'));
        $ele0->setURL('/');
        $ele0->setURLFull($this->baseURL);

        $breadCrumb[] = $ele0;
        $ele          = new NavigationEntry();
        $ele->setHasChild(false);
        switch ($this->pageType) {
            case \PAGE_STARTSEITE:
                break;

            case \PAGE_ARTIKEL:
                if ($this->categoryList === null
                    || $this->product === null
                    || \count($this->categoryList->elemente) === 0
                ) {
                    break;
                }
                $elemCount = \count($this->categoryList->elemente) - 1;
                for ($i = $elemCount; $i >= 0; $i--) {
                    if (isset(
                        $this->categoryList->elemente[$i]->cKurzbezeichnung,
                        $this->categoryList->elemente[$i]->cURL
                    )) {
                        $ele = new NavigationEntry();
                        $ele->setName($this->categoryList->elemente[$i]->cKurzbezeichnung);
                        $ele->setURL($this->categoryList->elemente[$i]->cURL);
                        $ele->setURLFull($this->categoryList->elemente[$i]->cURLFull);
                        $breadCrumb[] = $ele;
                    }
                }
                $ele = new NavigationEntry();
                $ele->setName($this->product->cKurzbezeichnung);
                $ele->setURL($this->product->cURL);
                $ele->setURLFull($this->product->cURLFull);
                if ($this->product->isChild()) {
                    $parent = new Artikel();
                    $parent->fuelleArtikel($this->product->kVaterArtikel, Artikel::getDefaultOptions());
                    $ele->setName($parent->cKurzbezeichnung);
                    $ele->setURL($parent->cURL);
                    $ele->setURLFull($parent->cURLFull);
                    $ele->setHasChild(true);
                }
                $breadCrumb[] = $ele;
                break;

            case \PAGE_ARTIKELLISTE:
                $elemCount = \count($this->categoryList->elemente ?? []);
                for ($i = $elemCount - 1; $i >= 0; $i--) {
                    if (isset(
                        $this->categoryList->elemente[$i]->cKurzbezeichnung,
                        $this->categoryList->elemente[$i]->cURL
                    )) {
                        $ele = new NavigationEntry();
                        $ele->setName($this->categoryList->elemente[$i]->cKurzbezeichnung);
                        $ele->setURL($this->categoryList->elemente[$i]->cURL);
                        $ele->setURLFull($this->categoryList->elemente[$i]->cURLFull);
                        $breadCrumb[] = $ele;
                    }
                }
                if ($elemCount === 0 && $this->getProductFilter() !== null) {
                    $ele = new NavigationEntry();
                    $ele->setName($this->getProductFilterName());
                    $ele->setURL($this->productFilter->getFilterURL()->getURL());
                    $ele->setURLFull($this->productFilter->getFilterURL()->getURL());
                    $breadCrumb[] = $ele;
                }

                break;

            case \PAGE_WARENKORB:
                $url     = $this->linkService->getStaticRoute('warenkorb.php', false);
                $urlFull = $this->linkService->getStaticRoute('warenkorb.php');
                $ele->setName($this->language->get('basket', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case \PAGE_PASSWORTVERGESSEN:
                $url     = $this->linkService->getStaticRoute('pass.php', false);
                $urlFull = $this->linkService->getStaticRoute('pass.php');
                $ele->setName($this->language->get('forgotpassword', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case \PAGE_LOGIN:
            case \PAGE_MEINKONTO:
                $name    = Frontend::getCustomer()->getID() > 0
                    ? $this->language->get('account', 'breadcrumb')
                    : $this->language->get('login', 'breadcrumb');
                $url     = $this->linkService->getStaticRoute('jtl.php', false);
                $urlFull = $this->linkService->getStaticRoute('jtl.php');
                $ele->setName($name);
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;

                if (Request::verifyGPCDataInt('accountPage') !== 1) {
                    $childPages = [
                        'bestellungen'         => $this->language->get('myOrders'),
                        'editRechnungsadresse' => $this->language->get('myPersonalData'),
                        'wllist'               => $this->language->get('myWishlists'),
                        'del'                  => $this->language->get('deleteAccount', 'login'),
                        'bestellung'           => $this->language->get('bcOrder', 'breadcrumb'),
                        'wl'                   => $this->language->get('bcWishlist', 'breadcrumb'),
                        'pass'                 => $this->language->get('changePassword', 'login')
                    ];

                    foreach ($childPages as $childPage => $childPageLang) {
                        if (Request::verifyGPCDataInt($childPage) === 0) {
                            continue;
                        }
                        $url     = $this->linkService->getStaticRoute('jtl.php', false) . '?' . $childPage . '=1';
                        $urlFull = $this->linkService->getStaticRoute('jtl.php') . '?' . $childPage . '=1';
                        $ele     = new NavigationEntry();
                        $ele->setName($childPageLang);
                        $ele->setURL($url);
                        $ele->setURLFull($urlFull);
                        $breadCrumb[] = $ele;
                    }
                }

                break;

            case \PAGE_BESTELLVORGANG:
                $url     = $this->linkService->getStaticRoute('jtl.php', false);
                $urlFull = $this->linkService->getStaticRoute('jtl.php');
                $ele->setName($this->language->get('checkout', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case \PAGE_REGISTRIERUNG:
                $url     = $this->linkService->getStaticRoute('registrieren.php', false);
                $urlFull = $this->linkService->getStaticRoute('registrieren.php');
                $ele->setName($this->language->get('register', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case \PAGE_KONTAKT:
                $url     = $this->linkService->getStaticRoute('kontakt.php', false);
                $urlFull = $this->linkService->getStaticRoute('kontakt.php');
                $ele->setName($this->language->get('contact', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case \PAGE_WARTUNG:
                $url     = $this->linkService->getStaticRoute('wartung.php', false);
                $urlFull = $this->linkService->getStaticRoute('wartung.php');
                $ele->setName($this->language->get('maintainance', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case \PAGE_NEWSLETTER:
                if ($this->link !== null) {
                    $ele->setName($this->link->getName());
                    $ele->setURL($this->link->getURL());
                    $ele->setURLFull($this->link->getURL());
                    $breadCrumb[] = $ele;
                }
                break;

            case \PAGE_NEWSDETAIL:
            case \PAGE_NEWS:
                $url     = $this->linkService->getStaticRoute('news.php', false);
                $urlFull = $this->linkService->getStaticRoute('news.php');
                $ele->setName($this->language->get('news', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case \PAGE_NEWSKATEGORIE:
                $url     = $this->linkService->getStaticRoute('news.php', false);
                $urlFull = $this->linkService->getStaticRoute('news.php');
                $ele->setName($this->language->get('newskat', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case \PAGE_NEWSMONAT:
                $url     = $this->linkService->getStaticRoute('news.php', false);
                $urlFull = $this->linkService->getStaticRoute('news.php');
                $ele->setName($this->language->get('newsmonat', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;

                break;

            case \PAGE_VERGLEICHSLISTE:
                $url     = $this->linkService->getStaticRoute('vergleichsliste.php', false);
                $urlFull = $this->linkService->getStaticRoute('vergleichsliste.php');
                $ele->setName($this->language->get('compare'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case \PAGE_WUNSCHLISTE:
                $url     = $this->linkService->getStaticRoute('wunschliste.php', false);
                $urlFull = $this->linkService->getStaticRoute('wunschliste.php');
                $ele->setName($this->language->get('wishlist'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case \PAGE_BEWERTUNG:
                if ($this->product !== null) {
                    $ele = new NavigationEntry();
                    $ele->setName($this->product->cKurzbezeichnung);
                    $ele->setURL($this->product->cURL);
                    $ele->setURLFull($this->product->cURLFull);
                    if ($this->product->isChild()) {
                        $parent = new Artikel();
                        $parent->fuelleArtikel($this->product->kVaterArtikel, Artikel::getDefaultOptions());
                        $ele->setName($parent->cKurzbezeichnung);
                        $ele->setURL($parent->cURL);
                        $ele->setURLFull($parent->cURLFull);
                        $ele->setHasChild(true);
                    }
                    $breadCrumb[] = $ele;
                    $ele          = new NavigationEntry();
                    $ele->setName($this->language->get('bewertung', 'breadcrumb'));
                    $ele->setURL('bewertung.php?a=' . $this->product->kArtikel . '&bfa=1');
                    $ele->setURLFull($this->baseURL . 'bewertung.php?a=' . $this->product->kArtikel . '&bfa=1');
                    $breadCrumb[] = $ele;
                } else {
                    $ele = new NavigationEntry();
                    $ele->setName($this->language->get('bewertung', 'breadcrumb'));
                    $ele->setURL('');
                    $ele->setURLFull('');
                    $breadCrumb[] = $ele;
                }
                break;

            default:
                if ($this->link !== null && $this->link instanceof Link) {
                    $elems = $this->linkService->getParentLinks($this->link->getID())
                        ->map(static function (LinkInterface $l) {
                            $res = new NavigationEntry();
                            $res->setName($l->getName());
                            $res->setURL($l->getURL());
                            $res->setURLFull($l->getURL());

                            return $res;
                        })->reverse()->all();

                    $breadCrumb = \array_merge($breadCrumb, $elems);
                    $ele->setName($this->link->getName());
                    $ele->setURL($this->link->getURL());
                    $ele->setURLFull($this->link->getURL());
                    $breadCrumb[] = $ele;
                }
                break;
        }
        if ($this->customNavigationEntry !== null) {
            $breadCrumb[] = $this->customNavigationEntry;
        }
        \executeHook(\HOOK_TOOLSGLOBAL_INC_SWITCH_CREATENAVIGATION, ['navigation' => &$breadCrumb]);

        return $breadCrumb;
    }
}
