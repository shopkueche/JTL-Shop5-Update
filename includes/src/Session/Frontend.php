<?php

namespace JTL\Session;

use JTL\Campaign;
use JTL\Cart\Cart;
use JTL\Cart\PersistentCart;
use JTL\Catalog\ComparisonList;
use JTL\Catalog\Currency;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\Checkout\Lieferadresse;
use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Manufacturer;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Link\LinkGroupCollection;
use JTL\Shop;
use function Functional\first;

/**
 * Class Frontend
 * @package JTL\Session
 */
class Frontend extends AbstractSession
{
    private const DEFAULT_SESSION = 'JTLSHOP';

    /**
     * @var Frontend
     */
    protected static $instance;

    /**
     * @param bool   $start       - call session_start()?
     * @param bool   $force       - force new instance?
     * @param string $sessionName - if null, then default to current session name
     * @return Frontend
     * @throws \Exception
     */
    public static function getInstance(bool $start = true, $force = false, $sessionName = self::DEFAULT_SESSION): self
    {
        return ($force === true || self::$instance === null || self::$sessionName !== $sessionName)
            ? new self($start, $sessionName)
            : self::$instance;
    }

    /**
     * Frontend constructor.
     * @param bool   $start
     * @param string $sessionName
     * @throws \Exception
     */
    public function __construct(bool $start = true, string $sessionName = self::DEFAULT_SESSION)
    {
        parent::__construct($start, $sessionName);
        self::$instance = $this;
        $this->setStandardSessionVars();
        Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);

        \executeHook(\HOOK_CORE_SESSION_CONSTRUCTOR);
    }

    /**
     * setzt Sessionvariablen beim ersten Sessionaufbau oder wenn globale Daten aktualisiert werden müssen
     *
     * @return $this
     * @throws \Exception
     */
    public function setStandardSessionVars(): self
    {
        LanguageHelper::getInstance()->autoload();
        $_SESSION['FremdParameter'] = [];
        $_SESSION['Warenkorb']      = $_SESSION['Warenkorb'] ?? new Cart();

        $updateGlobals  = $this->checkGlobals();
        $updateLanguage = $this->checkLanguageUpdate();
        $updateGlobals  = $updateLanguage || $updateGlobals || $this->checkSessionUpdate();
        $lang           = $_GET['lang'] ?? '';
        $checked        = false;
        if (isset($_SESSION['kSprache'])) {
            self::checkReset($lang);
            $checked = true;
        }
        if ($updateGlobals) {
            $this->updateGlobals();
            if ($updateLanguage && isset($_SESSION['Kunde'])) {
                // Kundensprache ändern, wenn im eingeloggten Zustand die Sprache geändert wird
                /** @var array('Kunde' => \Kunde) $_SESSION */
                $_SESSION['Kunde']->kSprache = $_SESSION['kSprache'];
                $_SESSION['Kunde']->updateInDB();
            }
        }
        if (!$checked) {
            self::checkReset($lang);
        }
        $this->checkWishlistDeletes()->checkComparelistDeletes();
        // Kampagnen in die Session laden
        Campaign::getAvailable();
        if (!isset($_SESSION['cISOSprache'])) {
            \session_destroy();
            die('<h1>Ihr Shop wurde installiert. Lesen Sie in unserem Guide ' .
                '<a href="https://jtl-url.de/3dw4f">' .
                'mehr zu ersten Schritten mit JTL-Shop, der Grundkonfiguration ' .
                'und dem erstem Abgleich mit JTL-Wawi</a>.</h1>');
        }
        $this->checkCustomerUpdate();
        $this->initLanguageURLs();

        return $this;
    }

    /**
     * @return bool
     */
    private function checkLanguageUpdate(): bool
    {
        return isset($_GET['lang']) && (!isset($_SESSION['cISOSprache']) || $_GET['lang'] !== $_SESSION['cISOSprache']);
    }

    /**
     * wurde kunde über wawi aktualisiert?
     *
     * @return bool
     */
    private function checkCustomerUpdate(): bool
    {
        if (empty($_SESSION['Kunde']->kKunde) || isset($_SESSION['kundendaten_aktualisiert'])) {
            return false;
        }
        $data = Shop::Container()->getDB()->queryPrepared(
            'SELECT kKunde
                FROM tkunde
                WHERE kKunde = :cid
                    AND DATE_SUB(NOW(), INTERVAL 3 HOUR) < dVeraendert',
            ['cid' => (int)$_SESSION['Kunde']->kKunde],
            ReturnType::SINGLE_OBJECT
        );
        if (isset($data->kKunde) && $data->kKunde > 0) {
            Shop::setLanguage(
                $_SESSION['kSprache'] ?? $_SESSION['Kunde']->kSprache ?? 0,
                $_SESSION['cISOSprache'] ?? null
            );
            $this->setCustomer(new Customer((int)$_SESSION['Kunde']->kKunde));
            $_SESSION['kundendaten_aktualisiert'] = 1;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function checkSessionUpdate(): bool
    {
        return ((isset($_SESSION['Kundengruppe']) && \get_class($_SESSION['Kundengruppe']) === 'stdClass')
            || (isset($_SESSION['Waehrung']) && \get_class($_SESSION['Waehrung']) === 'stdClass')
            || (isset($_SESSION['Sprachen']) && \get_class($_SESSION['Sprachen'][0]) === 'stdClass'));
    }

    /**
     * @return bool
     */
    private function checkGlobals(): bool
    {
        $doUpdate = true;
        if (isset($_SESSION['Globals_TS'])) {
            $doUpdate = false;
            $last     = Shop::Container()->getDB()->queryPrepared(
                'SELECT dLetzteAenderung 
                    FROM tglobals 
                    WHERE dLetzteAenderung > :ts',
                ['ts' => $_SESSION['Globals_TS']],
                ReturnType::SINGLE_OBJECT
            );
            if (isset($last->dLetzteAenderung)) {
                $_SESSION['Globals_TS'] = $last->dLetzteAenderung;
                $doUpdate               = true;
            }
        } else {
            $_SESSION['Globals_TS'] = Shop::Container()->getDB()->query(
                'SELECT dLetzteAenderung  FROM tglobals',
                ReturnType::SINGLE_OBJECT
            )->dLetzteAenderung;
        }

        return $doUpdate || !isset($_SESSION['cISOSprache'], $_SESSION['kSprache'], $_SESSION['Kundengruppe']);
    }

    /**
     * @throws \Exception
     */
    private function updateGlobals(): void
    {
        unset($_SESSION['oKategorie_arr_new']);
        $_SESSION['ks']       = [];
        $_SESSION['Sprachen'] = LanguageHelper::getInstance()->gibInstallierteSprachen();
        Currency::setCurrencies(true);

        if (!isset($_SESSION['jtl_token'])) {
            $_SESSION['jtl_token'] = Shop::Container()->getCryptoService()->randomString(32);
        }
        $defaultLang = '';
        $allowed     = [];
        foreach ($_SESSION['Sprachen'] as $language) {
            $iso = Text::convertISO2ISO639($language->getCode());
            $language->setIso639($iso);
            $allowed[] = $iso;
            if ($language->isShopDefault()) {
                $defaultLang = $iso;
            }
        }
        if (!isset($_SESSION['kSprache'])) {
            $default = Text::convertISO6392ISO($this->getBrowserLanguage($allowed, $defaultLang));
            foreach ($_SESSION['Sprachen'] as $lang) {
                if ($lang->cISO === $default || (empty($default) && $lang->cShopStandard === 'Y')) {
                    $_SESSION['kSprache']    = $lang->kSprache;
                    $_SESSION['cISOSprache'] = \trim($lang->cISO);
                    Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
                    $_SESSION['currentLanguage'] = clone $lang;
                    break;
                }
            }
        }
        if (isset($_SESSION['Waehrung'])) {
            if (\get_class($_SESSION['Waehrung']) === 'stdClass') {
                $_SESSION['Waehrung'] = new Currency($_SESSION['Waehrung']->kWaehrung);
            }
            foreach ($_SESSION['Waehrungen'] as $currency) {
                /** @var Currency $currency */
                if ($currency->getCode() === $_SESSION['Waehrung']->getCode()) {
                    $_SESSION['Waehrung']      = $currency;
                    $_SESSION['cWaehrungName'] = $currency->getName();
                }
            }
        } else {
            foreach ($_SESSION['Waehrungen'] as $currency) {
                /** @var Currency $currency */
                if ($currency->isDefault()) {
                    $_SESSION['Waehrung']      = $currency;
                    $_SESSION['cWaehrungName'] = $currency->getName();
                }
            }
        }
        // EXPERIMENTAL_MULTILANG_SHOP
        foreach ($_SESSION['Sprachen'] as $lang) {
            if (isset($_SERVER['HTTP_HOST']) && \defined('URL_SHOP_' . \mb_convert_case($lang->cISO, \MB_CASE_UPPER))) {
                $shopLangURL = \constant('URL_SHOP_' . \mb_convert_case($lang->cISO, \MB_CASE_UPPER));
                if (\mb_strpos($shopLangURL, ($_SERVER['HTTP_HOST'] ?? ' ')) !== false) {
                    $_SESSION['kSprache']    = $lang->kSprache;
                    $_SESSION['cISOSprache'] = \trim($lang->cISO);
                    Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
                    break;
                }
            }
        }
        // EXPERIMENTAL_MULTILANG_SHOP END
        if (!isset($_SESSION['Kunde']->kKunde, $_SESSION['Kundengruppe']->kKundengruppe)
            || \get_class($_SESSION['Kundengruppe']) === 'stdClass'
        ) {
            $_SESSION['Kundengruppe'] = (new CustomerGroup())
                ->setLanguageID((int)$_SESSION['kSprache'])
                ->loadDefaultGroup();
        }
        if (!$_SESSION['Kundengruppe']->hasAttributes()) {
            $_SESSION['Kundengruppe']->initAttributes();
        }
        if (\PHP_SAPI !== 'cli' && Shop::Container()->getCache()->isCacheGroupActive(\CACHING_GROUP_CORE) === false) {
            $_SESSION['Linkgruppen'] = Shop::Container()->getLinkService()->getLinkGroups();
            $_SESSION['Hersteller']  = Manufacturer::getInstance()->getManufacturers();
        }
        Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
        self::getCart()->loescheDeaktiviertePositionen();
        Tax::setTaxRates();
        Shop::Lang()->reset();
    }

    /**
     * @return $this
     */
    private function checkWishlistDeletes(): self
    {
        $index = Request::verifyGPCDataInt('wlplo');
        if ($index !== 0) {
            $wl = new Wishlist();
            $wl->entfernePos($index);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function checkComparelistDeletes(): self
    {
        $listID = Request::verifyGPCDataInt('vlplo');
        if ($listID !== 0 && GeneralObject::isCountable('oArtikel_arr', $_SESSION['Vergleichsliste'])) {
            // Wunschliste Position aus der Session löschen
            foreach ($_SESSION['Vergleichsliste']->oArtikel_arr as $i => $product) {
                if ((int)$product->kArtikel === $listID) {
                    unset($_SESSION['Vergleichsliste']->oArtikel_arr[$i]);
                }
            }
            // Ist nach dem Löschen des Artikels aus der Vergleichslite kein weiterer Artikel vorhanden?
            if (\count($_SESSION['Vergleichsliste']->oArtikel_arr) === 0) {
                unset($_SESSION['Vergleichsliste']);
            } else {
                // Positionen Array in der Wunschliste neu nummerieren
                $_SESSION['Vergleichsliste']->oArtikel_arr = \array_merge($_SESSION['Vergleichsliste']->oArtikel_arr);
            }
            if (!isset($_SERVER['REQUEST_URI']) || \mb_strpos($_SERVER['REQUEST_URI'], 'index.php') !== false) {
                \http_response_code(301);
                \header('Location: ' . Shop::getURL() . '/');
                exit;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function cleanUp(): self
    {
        if (isset($_SESSION['Kunde']->nRegistriert) && (int)$_SESSION['Kunde']->nRegistriert === 0) {
            unset($_SESSION['Kunde']);
        }

        unset(
            $_SESSION['Zahlungsart'],
            $_SESSION['Warenkorb'],
            $_SESSION['Versandart'],
            $_SESSION['Lieferadresse'],
            $_SESSION['VersandKupon'],
            $_SESSION['NeukundenKupon'],
            $_SESSION['Kupon'],
            $_SESSION['GuthabenLocalized'],
            $_SESSION['Bestellung'],
            $_SESSION['Warenkorb'],
            $_SESSION['IP'],
            $_SESSION['kommentar']
        );
        $_SESSION['Warenkorb'] = new Cart();
        // WarenkorbPers loeschen
        $oWarenkorbPers = new PersistentCart($_SESSION['Kunde']->kKunde ?? 0);
        $oWarenkorbPers->entferneAlles();

        return $this;
    }

    /**
     * @return Lieferadresse
     */
    public static function getDeliveryAddress(): Lieferadresse
    {
        return $_SESSION['Lieferadresse'] ?? new Lieferadresse();
    }

    /**
     * @param Lieferadresse $address
     */
    public static function setDeliveryAddress(Lieferadresse $address): void
    {
        $_SESSION['Lieferadresse'] = $address;
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer): self
    {
        $customer->angezeigtesLand = LanguageHelper::getCountryCodeByCountryName($customer->cLand);
        $_SESSION['Kunde']         = $customer;
        $_SESSION['Kundengruppe']  = new CustomerGroup((int)$customer->kKundengruppe);
        $_SESSION['Kundengruppe']->setMayViewCategories(1)
            ->setMayViewPrices(1)
            ->initAttributes();
        self::getCart()->setzePositionsPreise();
        Tax::setTaxRates();
        self::setSpecialLinks();

        return $this;
    }

    /**
     * @return Customer
     */
    public static function getCustomer(): Customer
    {
        return $_SESSION['Kunde'] ?? new Customer();
    }

    /**
     * @return Customer
     * @deprecated since 5.0.0
     */
    public static function customer(): Customer
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCustomer();
    }

    /**
     * @return CustomerGroup
     */
    public static function getCustomerGroup(): CustomerGroup
    {
        return $_SESSION['Kundengruppe'] ?? (new CustomerGroup())->loadDefaultGroup();
    }

    /**
     * @return CustomerGroup
     * @deprecated since 5.0.0
     */
    public static function customerGroup(): CustomerGroup
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCustomerGroup();
    }

    /**
     * @return LanguageHelper
     */
    public function getLanguage(): LanguageHelper
    {
        $lang                    = LanguageHelper::getInstance();
        $lang->kSprache          = (int)$_SESSION['kSprache'];
        $lang->currentLanguageID = (int)$_SESSION['kSprache'];
        $lang->cISOSprache       = $_SESSION['cISOSprache'];

        return $lang;
    }

    /**
     * @return LanguageHelper
     * @deprecated since 5.0.0
     */
    public function language(): LanguageHelper
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return $this->getLanguage();
    }

    /**
     * @return LanguageModel[]
     */
    public static function getLanguages(): array
    {
        return $_SESSION['Sprachen'] ?? [];
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public static function languages(): array
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getLanguages();
    }

    /**
     * @return array
     */
    public function getPaymentMethods(): array
    {
        return $_SESSION['Zahlungsarten'] ?? [];
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public function payments(): array
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return $this->getPaymentMethods();
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public function deliveryCountries(): array
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return [];
    }

    /**
     * @return Currency
     */
    public static function getCurrency(): Currency
    {
        return $_SESSION['Waehrung'] ?? (new Currency())->getDefault();
    }

    /**
     * @return Currency
     * @deprecated since 5.0.0
     */
    public static function currency(): Currency
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCurrency();
    }

    /**
     * @return Cart
     */
    public static function getCart(): Cart
    {
        return $_SESSION['Warenkorb'] ?? new Cart();
    }

    /**
     * @return Cart
     * @deprecated since 5.0.0
     */
    public static function cart(): Cart
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCart();
    }

    /**
     * @return Currency[]
     */
    public static function getCurrencies(): array
    {
        return $_SESSION['Waehrungen'] ?? [];
    }

    /**
     * @return Currency[]
     * @deprecated since 5.0.0
     */
    public static function currencies(): array
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCurrencies();
    }

    /**
     * @return Cart
     * @deprecated since 5.0.0
     */
    public function basket(): Cart
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return $_SESSION['Warenkorb'] ?? new Cart();
    }

    /**
     * @return Wishlist
     */
    public static function getWishList(): Wishlist
    {
        return $_SESSION['Wunschliste'] ?? new Wishlist();
    }

    /**
     * @return Wishlist
     * @deprecated since 5.0.0
     */
    public static function wishList(): Wishlist
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getWishList();
    }

    /**
     * @return ComparisonList
     */
    public static function getCompareList(): ComparisonList
    {
        return $_SESSION['Vergleichsliste'] ?? new ComparisonList();
    }

    /**
     * @return ComparisonList
     * @deprecated since 5.0.0
     */
    public static function compareList(): ComparisonList
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCompareList();
    }

    /**
     * @param string $langISO
     * @former checkeSpracheWaehrung()
     * @since 5.0.0
     */
    public static function checkReset($langISO = ''): void
    {
        if ($langISO !== '') {
            if ($langISO !== Shop::getLanguageCode()) {
                $_SESSION['oKategorie_arr']     = [];
                $_SESSION['oKategorie_arr_new'] = [];
            }
            $lang = first(LanguageHelper::getAllLanguages(), static function ($l) use ($langISO) {
                return $l->cISO === $langISO;
            });
            if ($lang === null) {
                self::urlFallback();
            }
            $_SESSION['cISOSprache'] = $lang->cISO;
            $_SESSION['kSprache']    = (int)$lang->kSprache;
            Shop::setLanguage($lang->kSprache, $lang->cISO);
            unset($_SESSION['Suche']);
            self::setSpecialLinks();
            if (isset($_SESSION['Wunschliste'])) {
                self::getWishList()->umgebungsWechsel();
            }
            if (isset($_SESSION['Vergleichsliste'])) {
                self::getCompareList()->umgebungsWechsel();
            }
            $_SESSION['currentLanguage'] = clone $lang;
            unset($_SESSION['currentLanguage']->cURL);
        }

        $currencyCode = Request::verifyGPDataString('curr');
        if ($currencyCode) {
            $cart     = self::getCart();
            $currency = first(self::getCurrencies(), static function (Currency $c) use ($currencyCode) {
                return $c->getCode() === $currencyCode;
            });
            if ($currency !== null) {
                $_SESSION['Waehrung']      = $currency;
                $_SESSION['cWaehrungName'] = $currency->getName();
                if (isset($_SESSION['Wunschliste'])) {
                    self::getWishList()->umgebungsWechsel();
                }
                if (isset($_SESSION['Vergleichsliste'])) {
                    self::getCompareList()->umgebungsWechsel();
                }
                if ($cart !== null && \count($cart->PositionenArr) > 0) {
                    $cart->setzePositionsPreise();
                }
            }
        }
        LanguageHelper::getInstance()->autoload();
    }

    private static function urlFallback(): void
    {
        $productID             = Request::verifyGPCDataInt('a');
        $categoryID            = Request::verifyGPCDataInt('k');
        $pageID                = Request::verifyGPCDataInt('s');
        $childProductID        = Request::verifyGPCDataInt('a2');
        $manufacturerID        = Request::verifyGPCDataInt('h');
        $searchQueryID         = Request::verifyGPCDataInt('l');
        $kMerkmalWert          = Request::verifyGPCDataInt('m');
        $kSuchspecial          = Request::verifyGPCDataInt('q');
        $kNews                 = Request::verifyGPCDataInt('n');
        $kNewsMonatsUebersicht = Request::verifyGPCDataInt('nm');
        $kNewsKategorie        = Request::verifyGPCDataInt('nk');
        $key                   = 'kArtikel';
        $val                   = 0;
        \http_response_code(301);
        if ($productID > 0) {
            $key = 'kArtikel';
            $val = $productID;
        } elseif ($categoryID > 0) {
            $key = 'kKategorie';
            $val = $categoryID;
        } elseif ($pageID > 0) {
            $key = 'kLink';
            $val = $pageID;
        } elseif ($childProductID > 0) {
            $key = 'kArtikel';
            $val = $childProductID;
        } elseif ($manufacturerID > 0) {
            $key = 'kHersteller';
            $val = $manufacturerID;
        } elseif ($searchQueryID > 0) {
            $key = 'kSuchanfrage';
            $val = $searchQueryID;
        } elseif ($kMerkmalWert > 0) {
            $key = 'kMerkmalWert';
            $val = $kMerkmalWert;
        } elseif ($kSuchspecial > 0) {
            $key = 'kSuchspecial';
            $val = $kSuchspecial;
        } elseif ($kNews > 0) {
            $key = 'kNews';
            $val = $kNews;
        } elseif ($kNewsMonatsUebersicht > 0) {
            $key = 'kNewsMonatsUebersicht';
            $val = $kNewsMonatsUebersicht;
        } elseif ($kNewsKategorie > 0) {
            $key = 'kNewsKategorie';
            $val = $kNewsKategorie;
        }
        $dbRes = Shop::Container()->getDB()->select(
            'tseo',
            'cKey',
            $key,
            'kKey',
            $val,
            'kSprache',
            Shop::getLanguageID()
        );
        $seo   = $dbRes->cSeo ?? '';
        \header('Location: ' . Shop::getURL() . '/' . $seo, true, 301);
        exit;
    }

    /**
     * @return LinkGroupCollection
     * @former setzeLinks()
     * @since 5.0.0
     */
    public static function setSpecialLinks(): LinkGroupCollection
    {
        $linkGroups                    = Shop::Container()->getLinkService()->getLinkGroups();
        $_SESSION['Link_Datenschutz']  = $linkGroups->Link_Datenschutz;
        $_SESSION['Link_AGB']          = $linkGroups->Link_AGB;
        $_SESSION['Link_Versandseite'] = $linkGroups->Link_Versandseite;

        return $linkGroups;
    }
}
