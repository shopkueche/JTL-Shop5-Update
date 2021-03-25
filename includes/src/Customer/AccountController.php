<?php declare(strict_types=1);

namespace JTL\Customer;

use Exception;
use JTL\Alert\Alert;
use JTL\Campaign;
use JTL\Cart\CartHelper;
use JTL\Cart\PersistentCart;
use JTL\Cart\PersistentCartItem;
use JTL\Catalog\ComparisonList;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\CheckBox;
use JTL\Checkout\Bestellung;
use JTL\Checkout\Kupon;
use JTL\Checkout\Lieferadresse;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Extensions\Config\Item;
use JTL\Extensions\Download\Download;
use JTL\Extensions\Upload\File;
use JTL\GeneralDataProtection\Journal;
use JTL\Helpers\Date;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Link\SpecialPageNotFoundException;
use JTL\Pagination\Pagination;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Services\JTL\LinkServiceInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use stdClass;
use function Functional\some;

/**
 * Class AccountController
 * @package JTL\Customer
 */
class AccountController
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var AlertServiceInterface
     */
    private $alertService;

    /**
     * @var LinkServiceInterface
     */
    private $linkService;

    /**
     * @var JTLSmarty
     */
    private $smarty;

    /**
     * AccountController constructor.
     * @param DbInterface           $db
     * @param AlertServiceInterface $alertService
     * @param LinkServiceInterface  $linkService
     * @param JTLSmarty             $smarty
     */
    public function __construct(
        DbInterface $db,
        AlertServiceInterface $alertService,
        LinkServiceInterface $linkService,
        JTLSmarty $smarty
    ) {
        $this->db           = $db;
        $this->alertService = $alertService;
        $this->linkService  = $linkService;
        $this->smarty       = $smarty;
        $this->config       = Shopsetting::getInstance()->getAll();
    }

    /**
     * @throws Exception
     */
    public function handleRequest(): void
    {
        Shop::setPageType(\PAGE_MEINKONTO);
        $customer   = Frontend::getCustomer();
        $customerID = $customer->getID();
        $step       = 'login';
        if ($customerID > 0) {
            Frontend::getInstance()->setCustomer($customer);
        }
        if (Request::verifyGPCDataInt('wlidmsg') > 0) {
            $this->alertService->addAlert(
                Alert::TYPE_NOTE,
                Wishlist::mapMessage(Request::verifyGPCDataInt('wlidmsg')),
                'wlidmsg'
            );
        }
        if (isset($_SESSION['JTL_REDIRECT']) || Request::verifyGPCDataInt('r') > 0) {
            $this->smarty->assign(
                'oRedirect',
                $_SESSION['JTL_REDIRECT'] ?? $this->getRedirect(Request::verifyGPCDataInt('r'))
            );
            \executeHook(\HOOK_JTL_PAGE_REDIRECT_DATEN);
        }
        unset($_SESSION['JTL_REDIRECT']);
        if (Request::getVar('updated_pw') === 'true') {
            $this->alertService->addAlert(
                Alert::TYPE_NOTE,
                Shop::Lang()->get('changepasswordSuccess', 'login'),
                'changepasswordSuccess'
            );
        }
        if (isset($_POST['email'], $_POST['passwort']) && Request::postInt('login') === 1) {
            $customerID = $this->login($_POST['email'], $_POST['passwort'])->getID();
        }
        if (isset($_GET['loggedout'])) {
            $this->alertService->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('loggedOut'), 'loggedOut');
        }
        if ($customerID > 0) {
            $step = $this->handleCustomerRequest($customerID);
        }
        $alertNote = $this->alertService->alertTypeExists(Alert::TYPE_NOTE);
        if (!$alertNote && $step === 'mein Konto' && $customerID > 0) {
            $this->alertService->addAlert(
                Alert::TYPE_INFO,
                Shop::Lang()->get('myAccountDesc', 'login'),
                'myAccountDesc',
                ['showInAlertListTemplate' => false]
            );
        }
        try {
            $link = $this->linkService->getSpecialPage(\LINKTYP_LOGIN);
        } catch (SpecialPageNotFoundException $e) {
            Shop::Container()->getLogService()->error($e->getMessage());
            $link = null;
        }
        $this->smarty->assign('alertNote', $alertNote)
                     ->assign('step', $step)
                     ->assign('Link', $link);
    }

    /**
     * @param int $customerID
     * @return string
     */
    private function handleCustomerRequest(int $customerID): string
    {
        Shop::setPageType(\PAGE_MEINKONTO);
        $ratings = [];
        $step    = 'mein Konto';
        $valid   = Form::validateToken();
        if (Request::verifyGPCDataInt('logout') === 1) {
            $this->logout();
        }
        if ($valid && ($uploadID = Request::verifyGPCDataInt('kUpload')) > 0) {
            $file = new File($uploadID);
            if ($file->validateOwner($customerID)) {
                File::send_file_to_browser(
                    \PFAD_UPLOADS . $file->cPfad,
                    'application/octet-stream',
                    $file->cName
                );
            }
        }
        if (Request::verifyGPCDataInt('del') === 1) {
            $openOrders = Frontend::getCustomer()->getOpenOrders();
            if (!empty($openOrders)) {
                if ($openOrders->ordersInCancellationTime > 0) {
                    $ordersInCancellationTime = \sprintf(
                        Shop::Lang()->get('customerOrdersInCancellationTime', 'account data'),
                        $openOrders->ordersInCancellationTime
                    );
                }
                $this->alertService->addAlert(
                    Alert::TYPE_DANGER,
                    \sprintf(
                        Shop::Lang()->get('customerOpenOrders', 'account data'),
                        $openOrders->openOrders,
                        $ordersInCancellationTime ?? ''
                    ),
                    'customerOrdersInCancellationTime'
                );
            }
            $step = 'account loeschen';
        }
        if (Request::verifyGPCDataInt('basket2Pers') === 1) {
            $this->setzeWarenkorbPersInWarenkorb($customerID);
            \header('Location: ' . $this->linkService->getStaticRoute('jtl.php'), true, 303);
            exit();
        }
        if ($valid && Request::verifyGPCDataInt('wllo') > 0) {
            $step = 'mein Konto';
            $this->alertService->addAlert(
                Alert::TYPE_NOTE,
                Wishlist::delete(Request::verifyGPCDataInt('wllo')),
                'wllo'
            );
        }
        if ($valid && Request::postInt('wls') > 0) {
            $step = 'mein Konto';
            $this->alertService->addAlert(
                Alert::TYPE_NOTE,
                Wishlist::setDefault(Request::verifyGPCDataInt('wls')),
                'wls'
            );
        }
        if ($valid && Request::postInt('wlh') > 0) {
            $step = 'mein Konto';
            $name = Text::htmlentities(Text::filterXSS($_POST['cWunschlisteName']));
            $this->alertService->addAlert(Alert::TYPE_NOTE, Wishlist::save($name), 'saveWL');
        }
        $wishlistID = Request::verifyGPCDataInt('wl');
        if ($wishlistID > 0) {
            $step = $this->modifyWishlist($customerID, $wishlistID);
        }
        if (Request::verifyGPCDataInt('editRechnungsadresse') > 0
            || Request::verifyGPCDataInt('editLieferadresse') > 0
        ) {
            $step = 'rechnungsdaten';
        }
        if (Request::getInt('pass') === 1) {
            $step = 'passwort aendern';
        }
        if ($valid && Request::postInt('edit') === 1) {
            $this->changeCustomerData();
        }
        if ($valid && Request::postInt('pass_aendern') > 0) {
            $step = $this->changePassword($customerID);
        }
        if (Request::verifyGPCDataInt('bestellungen') > 0) {
            $step = 'bestellungen';
        }
        if (Request::verifyGPCDataInt('wllist') > 0) {
            $step = 'wunschliste';
        }
        if (Request::verifyGPCDataInt('bewertungen') > 0) {
            $step = 'bewertungen';
        }
        if (Request::verifyGPCDataInt('bestellung') > 0) {
            $step = $this->viewOrder($customerID);
        }
        if ($valid && Request::postInt('del_acc') === 1) {
            $this->deleteAccount($customerID);
        }
        if ($step === 'mein Konto' || $step === 'bestellungen') {
            $this->viewOrders($customerID);
        }
        if ($step === 'mein Konto' || $step === 'wunschliste') {
            $this->smarty->assign('oWunschliste_arr', Wishlist::getWishlists());
        }
        if ($step === 'mein Konto') {
            $deliveryAddresses = [];
            $addressData       = $this->db->selectAll(
                'tlieferadresse',
                'kKunde',
                $customerID,
                'kLieferadresse'
            );
            foreach ($addressData as $item) {
                if ($item->kLieferadresse > 0) {
                    $deliveryAddresses[] = new Lieferadresse((int)$item->kLieferadresse);
                }
            }
            \executeHook(\HOOK_JTL_PAGE_MEINKKONTO, ['deliveryAddresses' => &$deliveryAddresses]);
            $this->smarty->assign('Lieferadressen', $deliveryAddresses)
                ->assign('compareList', new ComparisonList());
        }
        if ($step === 'rechnungsdaten') {
            $this->getCustomerFields();
        }
        if ($step === 'bewertungen') {
            $ratings = $this->db->queryPrepared(
                'SELECT tbewertung.kBewertung, fGuthabenBonus, nAktiv, kArtikel, cTitel, cText, 
                  tbewertung.dDatum, nSterne, cAntwort, dAntwortDatum
                  FROM tbewertung 
                  LEFT JOIN tbewertungguthabenbonus 
                      ON tbewertung.kBewertung = tbewertungguthabenbonus.kBewertung
                  WHERE tbewertung.kKunde = :customer',
                ['customer' => $customerID],
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
        $_SESSION['Kunde']->cGuthabenLocalized = Preise::getLocalizedPriceString($_SESSION['Kunde']->fGuthaben);
        $this->smarty->assign('Kunde', $_SESSION['Kunde'])
            ->assign('customerAttributes', $_SESSION['Kunde']->getCustomerAttributes())
            ->assign('bewertungen', $ratings)
            ->assign('BESTELLUNG_STATUS_BEZAHLT', \BESTELLUNG_STATUS_BEZAHLT)
            ->assign('BESTELLUNG_STATUS_VERSANDT', \BESTELLUNG_STATUS_VERSANDT)
            ->assign('BESTELLUNG_STATUS_OFFEN', \BESTELLUNG_STATUS_OFFEN)
            ->assign('nAnzeigeOrt', \CHECKBOX_ORT_KUNDENDATENEDITIEREN);

        return $step;
    }

    /**
     * @param string $userLogin
     * @param string $passLogin
     * @return Customer
     */
    public function login(string $userLogin, string $passLogin): Customer
    {
        $customer = new Customer();
        if (Form::validateToken() === false) {
            $this->alertService->addAlert(
                Alert::TYPE_NOTE,
                Shop::Lang()->get('csrfValidationFailed'),
                'csrfValidationFailed'
            );
            Shop::Container()->getLogService()->warning('CSRF-Warnung für Login: ' . $_POST['login']);

            return $customer;
        }
        $captchaState = $customer->verifyLoginCaptcha($_POST);
        if ($captchaState === true) {
            $returnCode = $customer->holLoginKunde($userLogin, $passLogin);
            $tries      = $customer->nLoginversuche;
        } else {
            $returnCode = Customer::ERROR_CAPTCHA;
            $tries      = $captchaState;
        }
        if ($customer->getID() > 0) {
            $this->initCustomer($customer);
        } elseif ($returnCode === Customer::ERROR_LOCKED) {
            $this->alertService->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('accountLocked'), 'accountLocked');
        } elseif ($returnCode === Customer::ERROR_INACTIVE) {
            $this->alertService->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('accountInactive'), 'accountInactive');
        } else {
            $this->checkLoginCaptcha($tries);
            $this->alertService->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('incorrectLogin'), 'incorrectLogin');
        }

        return $customer;
    }

    /**
     * @param int $tries
     */
    private function checkLoginCaptcha(int $tries): void
    {
        $maxAttempts = (int)$this->config['kunden']['kundenlogin_max_loginversuche'];
        if ($maxAttempts > 1 && $tries >= $maxAttempts) {
            $_SESSION['showLoginCaptcha'] = true;
        }
    }

    /**
     * @param Customer $customer
     * @throws Exception
     */
    public function initCustomer(Customer $customer): void
    {
        unset($_SESSION['showLoginCaptcha']);
        $coupons = $this->getCoupons();
        // create new session id to prevent session hijacking
        \session_regenerate_id();
        if (isset($_SESSION['oBesucher']->kBesucher) && $_SESSION['oBesucher']->kBesucher > 0) {
            $this->db->update(
                'tbesucher',
                'kBesucher',
                (int)$_SESSION['oBesucher']->kBesucher,
                (object)['kKunde' => $customer->getID()]
            );
        }
        if ($customer->cAktiv !== 'Y') {
            $this->alertService->addAlert(
                Alert::TYPE_NOTE,
                Shop::Lang()->get('loginNotActivated'),
                'loginNotActivated'
            );
            return;
        }
        $this->updateSession($customer->getID());
        $session = Frontend::getInstance();
        $session->setCustomer($customer);
        Wishlist::persistInSession();
        $persCartLoaded = $this->config['kaufabwicklung']['warenkorbpers_nutzen'] === 'Y'
            && $this->loadPersistentCart($customer);
        $this->pruefeWarenkorbArtikelSichtbarkeit($customer->getGroupID());
        \executeHook(\HOOK_JTL_PAGE_REDIRECT);
        CartHelper::checkAdditions();
        $this->checkURLRedirect();
        if (!$persCartLoaded && $this->config['kaufabwicklung']['warenkorbpers_nutzen'] === 'Y') {
            if ($this->config['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'Y') {
                $this->setzeWarenkorbPersInWarenkorb($customer->getID());
            } elseif ($this->config['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'P') {
                $persCart = new PersistentCart($customer->getID());
                if (\count($persCart->oWarenkorbPersPos_arr) > 0) {
                    $this->smarty->assign('nWarenkorb2PersMerge', 1);
                } else {
                    $this->setzeWarenkorbPersInWarenkorb($customer->getID());
                }
            }
        }
        $this->checkCoupons($coupons);
        $this->updateCustomerLanguage($customer->kSprache);
        Shop::Container()->getLinkService()->reset();
    }

    /**
     * @param int $languageID
     */
    private function updateCustomerLanguage(int $languageID): void
    {
        $isoLang = Shop::Lang()->getIsoFromLangID($languageID);
        if ((int)$_SESSION['kSprache'] !== $languageID && $isoLang !== null && !empty($isoLang->cISO)) {
            $_SESSION['kSprache']        = $languageID;
            $_SESSION['cISOSprache']     = $isoLang->cISO;
            $_SESSION['currentLanguage'] = LanguageHelper::getAllLanguages(1)[$languageID];
            Shop::setLanguage($languageID, $isoLang->cISO);
            Shop::Lang()->setzeSprache($isoLang->cISO);
        }
    }

    /**
     *
     */
    private function checkURLRedirect(): void
    {
        $url = Text::filterXSS(Request::verifyGPDataString('cURL'));
        if (\mb_strlen($url) > 0) {
            if (\mb_strpos($url, 'http') !== 0) {
                $url = Shop::getURL() . '/' . \ltrim($url, '/');
            }
            \header('Location: ' . $url, true, 301);
            exit();
        }
    }

    /**
     * @param int $customerID
     */
    private function updateSession(int $customerID): void
    {
        unset(
            $_SESSION['Zahlungsart'],
            $_SESSION['Versandart'],
            $_SESSION['Lieferadresse'],
            $_SESSION['ks'],
            $_SESSION['VersandKupon'],
            $_SESSION['NeukundenKupon'],
            $_SESSION['Kupon'],
            $_SESSION['oKategorie_arr_new']
        );
        if (isset($_SESSION['Kampagnenbesucher'])) {
            Campaign::setCampaignAction(\KAMPAGNE_DEF_LOGIN, $customerID, 1.0); // Login
        }
    }

    /**
     * @return array
     */
    private function getCoupons(): array
    {
        $coupons   = [];
        $coupons[] = !empty($_SESSION['VersandKupon']) ? $_SESSION['VersandKupon'] : null;
        $coupons[] = !empty($_SESSION['oVersandfreiKupon']) ? $_SESSION['oVersandfreiKupon'] : null;
        $coupons[] = !empty($_SESSION['NeukundenKupon']) ? $_SESSION['NeukundenKupon'] : null;
        $coupons[] = !empty($_SESSION['Kupon']) ? $_SESSION['Kupon'] : null;

        return $coupons;
    }

    /**
     * @param array $coupons
     */
    private function checkCoupons(array $coupons): void
    {
        foreach ($coupons as $coupon) {
            if (empty($coupon)) {
                continue;
            }
            $error      = Kupon::checkCoupon($coupon);
            $returnCode = \angabenKorrekt($error);
            \executeHook(\HOOK_WARENKORB_PAGE_KUPONANNEHMEN_PLAUSI, [
                'error'        => &$error,
                'nReturnValue' => &$returnCode
            ]);
            if ($returnCode) {
                if (isset($coupon->kKupon) && $coupon->kKupon > 0 && $coupon->cKuponTyp === Kupon::TYPE_STANDARD) {
                    Kupon::acceptCoupon($coupon);
                    \executeHook(\HOOK_WARENKORB_PAGE_KUPONANNEHMEN);
                } elseif (!empty($coupon->kKupon) && $coupon->cKuponTyp === Kupon::TYPE_SHIPPING) {
                    // Versandfrei Kupon
                    $_SESSION['oVersandfreiKupon'] = $coupon;
                    $this->smarty->assign(
                        'cVersandfreiKuponLieferlaender_arr',
                        \explode(';', $coupon->cLieferlaender)
                    );
                }
            } else {
                Frontend::getCart()->loescheSpezialPos(\C_WARENKORBPOS_TYP_KUPON);
                Kupon::mapCouponErrorMessage($error['ungueltig']);
            }
        }
    }

    /**
     * @param Customer $customer
     * @return bool
     */
    private function loadPersistentCart(Customer $customer): bool
    {
        $cart = Frontend::getCart();
        if (\count($cart->PositionenArr) > 0) {
            return false;
        }
        $persCart = new PersistentCart($customer->getID());
        $persCart->ueberpruefePositionen(true);
        if (\count($persCart->oWarenkorbPersPos_arr) === 0) {
            return false;
        }
        foreach ($persCart->oWarenkorbPersPos_arr as $item) {
            if (!empty($item->Artikel->bHasKonfig)) {
                continue;
            }
            // Gratisgeschenk in Warenkorb legen
            if ((int)$item->nPosTyp === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $productID = (int)$item->kArtikel;
                $present   = $this->db->queryPrepared(
                    'SELECT tartikelattribut.kArtikel, tartikel.fLagerbestand, 
                        tartikel.cLagerKleinerNull, tartikel.cLagerBeachten
                        FROM tartikelattribut
                        JOIN tartikel 
                            ON tartikel.kArtikel = tartikelattribut.kArtikel
                        WHERE tartikelattribut.kArtikel = :pid
                            AND tartikelattribut.cName = :atr
                            AND CAST(tartikelattribut.cWert AS DECIMAL) <= :sum',
                    [
                        'pid' => $productID,
                        'atr' => \FKT_ATTRIBUT_GRATISGESCHENK,
                        'sum' => $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true)
                    ],
                    ReturnType::SINGLE_OBJECT
                );
                if ((isset($present->kArtikel) && $present->kArtikel > 0)
                    && ($present->fLagerbestand > 0
                        || $present->cLagerKleinerNull === 'Y'
                        || $present->cLagerBeachten === 'N')
                ) {
                    \executeHook(\HOOK_WARENKORB_PAGE_GRATISGESCHENKEINFUEGEN);
                    $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_GRATISGESCHENK)
                        ->fuegeEin($productID, 1, [], \C_WARENKORBPOS_TYP_GRATISGESCHENK);
                }
                // Konfigitems ohne Artikelbezug
            } elseif ($item->kArtikel === 0 && !empty($item->kKonfigitem)) {
                $configItem = new Item($item->kKonfigitem);
                $cart->erstelleSpezialPos(
                    $configItem->getName(),
                    $item->fAnzahl,
                    $configItem->getPreis(),
                    $configItem->getSteuerklasse(),
                    \C_WARENKORBPOS_TYP_ARTIKEL,
                    false,
                    !Frontend::getCustomerGroup()->isMerchant(),
                    '',
                    $item->cUnique,
                    $item->kKonfigitem,
                    $item->kArtikel,
                    $item->cResponsibility
                );
            } else {
                CartHelper::addProductIDToCart(
                    $item->kArtikel,
                    $item->fAnzahl,
                    $item->oWarenkorbPersPosEigenschaft_arr,
                    1,
                    $item->cUnique,
                    $item->kKonfigitem,
                    null,
                    false,
                    $item->cResponsibility
                );
            }
        }
        $cart->setzePositionsPreise();

        return true;
    }

    /**
     * Prüfe ob Artikel im Warenkorb vorhanden sind, welche für den aktuellen Kunden nicht mehr sichtbar sein dürfen
     *
     * @param int $customerGroupID
     */
    private function pruefeWarenkorbArtikelSichtbarkeit(int $customerGroupID): void
    {
        $cart = Frontend::getCart();
        if ($customerGroupID <= 0 || empty($cart->PositionenArr)) {
            return;
        }
        foreach ($cart->PositionenArr as $i => $item) {
            if ($item->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL || !empty($item->cUnique)) {
                continue;
            }
            $visibility = $this->db->queryPrepared(
                'SELECT kArtikel
                FROM tartikelsichtbarkeit
                WHERE kArtikel = :pid
                    AND kKundengruppe = :cgid',
                ['pid' => (int)$item->kArtikel, 'cgid' => $customerGroupID],
                ReturnType::SINGLE_OBJECT
            );
            if (isset($visibility->kArtikel) && $visibility->kArtikel > 0 && (int)$item->kKonfigitem === 0) {
                unset($cart->PositionenArr[$i]);
            }
            $price = $this->db->queryPrepared(
                'SELECT tpreisdetail.fVKNetto
                FROM tpreis
                INNER JOIN tpreisdetail ON tpreisdetail.kPreis = tpreis.kPreis
                    AND tpreisdetail.nAnzahlAb = 0
                WHERE tpreis.kArtikel = :productID
                    AND tpreis.kKundengruppe = :customerGroup',
                ['productID' => (int)$item->kArtikel, 'customerGroup' => $customerGroupID],
                ReturnType::SINGLE_OBJECT
            );
            if (!isset($price->fVKNetto)) {
                unset($cart->PositionenArr[$i]);
            }
        }
    }

    /**
     * @param int $customerID
     * @return bool
     */
    public function setzeWarenkorbPersInWarenkorb(int $customerID): bool
    {
        if (!$customerID) {
            return false;
        }
        $cart = Frontend::getCart();
        foreach ($cart->PositionenArr as $item) {
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $productID = (int)$item->kArtikel;
                $present   = $this->db->queryPrepared(
                    'SELECT tartikelattribut.kArtikel, tartikel.fLagerbestand,
                       tartikel.cLagerKleinerNull, tartikel.cLagerBeachten
                    FROM tartikelattribut
                    JOIN tartikel 
                        ON tartikel.kArtikel = tartikelattribut.kArtikel
                    WHERE tartikelattribut.kArtikel = :pid
                        AND tartikelattribut.cName = :atr
                        AND CAST(tartikelattribut.cWert AS DECIMAL) <= :sum',
                    [
                        'pid' => $productID,
                        'atr' => \FKT_ATTRIBUT_GRATISGESCHENK,
                        'sum' => $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true)
                    ],
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($present->kArtikel) && $present->kArtikel > 0) {
                    PersistentCart::addToCheck($productID, 1, [], null, 0, \C_WARENKORBPOS_TYP_GRATISGESCHENK);
                }
            } else {
                PersistentCart::addToCheck(
                    $item->kArtikel,
                    $item->nAnzahl,
                    $item->WarenkorbPosEigenschaftArr,
                    $item->cUnique,
                    $item->kKonfigitem,
                    $item->nPosTyp,
                    $item->cResponsibility
                );
            }
        }
        $cart->PositionenArr = [];

        $persCart = new PersistentCart($customerID);
        /** @var PersistentCartItem $item */
        foreach ($persCart->oWarenkorbPersPos_arr as $item) {
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $productID = (int)$item->kArtikel;
                $present   = $this->db->queryPrepared(
                    'SELECT tartikelattribut.kArtikel, tartikel.fLagerbestand,
                       tartikel.cLagerKleinerNull, tartikel.cLagerBeachten
                    FROM tartikelattribut
                    JOIN tartikel 
                        ON tartikel.kArtikel = tartikelattribut.kArtikel
                    WHERE tartikelattribut.kArtikel = :pid
                        AND tartikelattribut.cName = :atr
                        AND CAST(tartikelattribut.cWert AS DECIMAL) <= :sum',
                    [
                        'pid' => $productID,
                        'atr' => \FKT_ATTRIBUT_GRATISGESCHENK,
                        'sum' => $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true)
                    ],
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($present->kArtikel) && $present->kArtikel > 0) {
                    if ($present->fLagerbestand <= 0
                        && $present->cLagerKleinerNull === 'N'
                        && $present->cLagerBeachten === 'Y'
                    ) {
                        break;
                    }
                    \executeHook(\HOOK_WARENKORB_PAGE_GRATISGESCHENKEINFUEGEN);
                    $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_GRATISGESCHENK)
                        ->fuegeEin($productID, 1, [], \C_WARENKORBPOS_TYP_GRATISGESCHENK);
                }
            } else {
                $tmpProduct = new Artikel();
                $tmpProduct->fuelleArtikel($item->kArtikel, (int)$item->kKonfigitem === 0
                    ? Artikel::getDefaultOptions()
                    : Artikel::getDefaultConfigOptions());
                if ((int)$tmpProduct->kArtikel > 0 && \count(CartHelper::addToCartCheck(
                    $tmpProduct,
                    $item->fAnzahl,
                    $item->oWarenkorbPersPosEigenschaft_arr
                )) === 0) {
                    CartHelper::addProductIDToCart(
                        $item->kArtikel,
                        $item->fAnzahl,
                        $item->oWarenkorbPersPosEigenschaft_arr,
                        1,
                        $item->cUnique,
                        $item->kKonfigitem,
                        null,
                        true,
                        $item->cResponsibility
                    );
                } else {
                    Shop::Container()->getAlertService()->addAlert(
                        Alert::TYPE_WARNING,
                        \sprintf(Shop::Lang()->get('cartPersRemoved', 'errorMessages'), $item->cArtikelName),
                        'cartPersRemoved' . $item->kArtikel,
                        ['saveInSession' => true]
                    );
                }
            }
        }

        return true;
    }

    /**
     * Redirect - Falls jemand eine Aktion durchführt die ein Kundenkonto beansprucht und der Gast nicht einloggt ist,
     * wird dieser hier her umgeleitet und es werden die passenden Parameter erstellt. Nach dem erfolgreichen einloggen,
     * wird die zuvor angestrebte Aktion durchgeführt.
     *
     * @param int $code
     * @return stdClass
     */
    private function getRedirect(int $code): stdClass
    {
        $redir = new stdClass();

        switch ($code) {
            case \R_LOGIN_WUNSCHLISTE:
                $redir->oParameter_arr   = [];
                $tmp                     = new stdClass();
                $tmp->Name               = 'a';
                $tmp->Wert               = Request::verifyGPCDataInt('a');
                $redir->oParameter_arr[] = $tmp;
                $tmp                     = new stdClass();
                $tmp->Name               = 'n';
                $tmp->Wert               = Request::verifyGPCDataInt('n');
                $redir->oParameter_arr[] = $tmp;
                $tmp                     = new stdClass();
                $tmp->Name               = 'Wunschliste';
                $tmp->Wert               = 1;
                $redir->oParameter_arr[] = $tmp;
                $redir->nRedirect        = \R_LOGIN_WUNSCHLISTE;
                $redir->cURL             = $this->linkService->getStaticRoute('wunschliste.php', false);
                $redir->cName            = Shop::Lang()->get('wishlist', 'redirect');
                break;
            case \R_LOGIN_BEWERTUNG:
                $redir->oParameter_arr   = [];
                $tmp                     = new stdClass();
                $tmp->Name               = 'a';
                $tmp->Wert               = Request::verifyGPCDataInt('a');
                $redir->oParameter_arr[] = $tmp;
                $tmp                     = new stdClass();
                $tmp->Name               = 'bfa';
                $tmp->Wert               = 1;
                $redir->oParameter_arr[] = $tmp;
                $redir->nRedirect        = \R_LOGIN_BEWERTUNG;
                $redir->cURL             = 'bewertung.php?a=' . Request::verifyGPCDataInt('a') . '&bfa=1&token=' .
                    $_SESSION['jtl_token'];
                $redir->cName            = Shop::Lang()->get('review', 'redirect');
                break;
            case \R_LOGIN_TAG:
                $redir->oParameter_arr   = [];
                $tmp                     = new stdClass();
                $tmp->Name               = 'a';
                $tmp->Wert               = Request::verifyGPCDataInt('a');
                $redir->oParameter_arr[] = $tmp;
                $redir->nRedirect        = \R_LOGIN_TAG;
                $redir->cURL             = '?a=' . Request::verifyGPCDataInt('a');
                $redir->cName            = Shop::Lang()->get('tag', 'redirect');
                break;
            case \R_LOGIN_NEWSCOMMENT:
                $redir->oParameter_arr   = [];
                $tmp                     = new stdClass();
                $tmp->Name               = 's';
                $tmp->Wert               = Request::verifyGPCDataInt('s');
                $redir->oParameter_arr[] = $tmp;
                $tmp                     = new stdClass();
                $tmp->Name               = 'n';
                $tmp->Wert               = Request::verifyGPCDataInt('n');
                $redir->oParameter_arr[] = $tmp;
                $redir->nRedirect        = \R_LOGIN_NEWSCOMMENT;
                $redir->cURL             = '?s=' . Request::verifyGPCDataInt('s') .
                    '&n=' . Request::verifyGPCDataInt('n');
                $redir->cName            = Shop::Lang()->get('news', 'redirect');
                break;
            default:
                break;
        }
        \executeHook(\HOOK_JTL_INC_SWITCH_REDIRECT, ['cRedirect' => &$code, 'oRedirect' => &$redir]);
        $_SESSION['JTL_REDIRECT'] = $redir;

        return $redir;
    }

    /**
     * @throws Exception
     */
    private function logout(): void
    {
        $languageID   = Shop::getLanguageID();
        $languageCode = Shop::getLanguageCode();
        $currency     = Frontend::getCurrency();
        unset($_SESSION['oKategorie_arr_new'], $_SESSION['Warenkorb']);

        $params = \session_get_cookie_params();
        \setcookie(
            \session_name(),
            '',
            \time() - 7000000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
        \session_destroy();
        new Frontend();
        \session_regenerate_id(true);

        $_SESSION['kSprache']    = $languageID;
        $_SESSION['cISOSprache'] = $languageCode;
        $_SESSION['Waehrung']    = $currency;
        Shop::setLanguage($languageID, $languageCode);

        \header('Location: ' . $this->linkService->getStaticRoute('jtl.php') . '?loggedout=1', true, 303);
        exit();
    }

    /**
     * @param int $customerID
     * @return string
     * @throws Exception
     */
    private function changePassword(int $customerID): string
    {
        $step = 'passwort aendern';
        if (!isset($_POST['altesPasswort'], $_POST['neuesPasswort1'])
            || !$_POST['altesPasswort']
            || !$_POST['neuesPasswort1']
        ) {
            $this->alertService->addAlert(
                Alert::TYPE_NOTE,
                Shop::Lang()->get('changepasswordFilloutForm', 'login'),
                'changepasswordFilloutForm'
            );
        }
        if ((isset($_POST['neuesPasswort1']) && !isset($_POST['neuesPasswort2']))
            || (isset($_POST['neuesPasswort2']) && !isset($_POST['neuesPasswort1']))
            || $_POST['neuesPasswort1'] !== $_POST['neuesPasswort2']
        ) {
            $this->alertService->addAlert(
                Alert::TYPE_ERROR,
                Shop::Lang()->get('changepasswordPassesNotEqual', 'login'),
                'changepasswordPassesNotEqual'
            );
        }
        $minLength = $this->config['kunden']['kundenregistrierung_passwortlaenge'];
        if (isset($_POST['neuesPasswort1']) && \mb_strlen($_POST['neuesPasswort1']) < $minLength) {
            $this->alertService->addAlert(
                Alert::TYPE_ERROR,
                Shop::Lang()->get('changepasswordPassTooShort', 'login') . ' ' .
                \lang_passwortlaenge($minLength),
                'changepasswordPassTooShort'
            );
        }
        if (isset($_POST['neuesPasswort1'], $_POST['neuesPasswort2'])
            && $_POST['neuesPasswort1'] === $_POST['neuesPasswort2']
            && \mb_strlen($_POST['neuesPasswort1']) >= $minLength
        ) {
            $customer = new Customer($customerID);
            $user     = $this->db->select(
                'tkunde',
                'kKunde',
                $customerID,
                null,
                null,
                null,
                null,
                false,
                'cPasswort, cMail'
            );
            if (isset($user->cPasswort, $user->cMail)) {
                $ok = $customer->checkCredentials($user->cMail, $_POST['altesPasswort']);
                if ($ok !== false) {
                    $customer->updatePassword($_POST['neuesPasswort1']);
                    $step = 'mein Konto';
                    $this->alertService->addAlert(
                        Alert::TYPE_NOTE,
                        Shop::Lang()->get('changepasswordSuccess', 'login'),
                        'changepasswordSuccess'
                    );
                } else {
                    $this->alertService->addAlert(
                        Alert::TYPE_ERROR,
                        Shop::Lang()->get('changepasswordWrongPass', 'login'),
                        'changepasswordWrongPass'
                    );
                }
            }
        }

        return $step;
    }

    /**
     * @param int $customerID
     * @return string
     */
    private function viewOrder(int $customerID): string
    {
        $order = new Bestellung(Request::verifyGPCDataInt('bestellung'), true);
        if ($order->kKunde === null || (int)$order->kKunde !== $customerID) {
            return 'login';
        }
        if (Request::verifyGPCDataInt('dl') > 0 && Download::checkLicense()) {
            $returnCode = Download::getFile(
                Request::verifyGPCDataInt('dl'),
                $customerID,
                $order->kBestellung
            );
            if ($returnCode !== 1) {
                $this->alertService->addAlert(
                    Alert::TYPE_ERROR,
                    Download::mapGetFileErrorCode($returnCode),
                    'downloadError'
                );
            }
        }
        $step                               = 'bestellung';
        $_SESSION['Kunde']->angezeigtesLand = LanguageHelper::getCountryCodeByCountryName($_SESSION['Kunde']->cLand);
        $this->smarty->assign('Bestellung', $order)
            ->assign('billingAddress', $order->oRechnungsadresse)
            ->assign('Lieferadresse', $order->Lieferadresse ?? null);
        if (isset($order->oEstimatedDelivery->longestMin, $order->oEstimatedDelivery->longestMax)) {
            $this->smarty->assign(
                'cEstimatedDeliveryEx',
                Date::dateAddWeekday(
                    $order->dErstellt,
                    $order->oEstimatedDelivery->longestMin
                )->format('d.m.Y')
                . ' - ' .
                Date::dateAddWeekday(
                    $order->dErstellt,
                    $order->oEstimatedDelivery->longestMax
                )->format('d.m.Y')
            );
        }

        return $step;
    }

    /**
     * @param int $customerID
     */
    private function viewOrders(int $customerID): void
    {
        $downloads = Download::getDownloads(['kKunde' => $customerID], Shop::getLanguageID());
        $this->smarty->assign('oDownload_arr', $downloads);
        if (Request::verifyGPCDataInt('dl') > 0 && Download::checkLicense()) {
            $returnCode = Download::getFile(
                Request::verifyGPCDataInt('dl'),
                $customerID,
                Request::verifyGPCDataInt('kBestellung')
            );
            if ($returnCode !== 1) {
                $this->alertService->addAlert(
                    Alert::TYPE_ERROR,
                    Download::mapGetFileErrorCode($returnCode),
                    'downloadError'
                );
            }
        }
        $orders     = $this->db->selectAll(
            'tbestellung',
            'kKunde',
            $customerID,
            '*, date_format(dErstellt,\'%d.%m.%Y\') AS dBestelldatum',
            'kBestellung DESC'
        );
        $currencies = [];
        foreach ($orders as $order) {
            $order->bDownload   = some($downloads, static function ($dl) use ($order) {
                return $dl->kBestellung === $order->kBestellung;
            });
            $order->kBestellung = (int)$order->kBestellung;
            $order->kWaehrung   = (int)$order->kWaehrung;
            $order->cStatus     = (int)$order->cStatus;
            if ($order->kWaehrung > 0) {
                if (isset($currencies[$order->kWaehrung])) {
                    $order->Waehrung = $currencies[$order->kWaehrung];
                } else {
                    $order->Waehrung               = $this->db->select(
                        'twaehrung',
                        'kWaehrung',
                        $order->kWaehrung
                    );
                    $currencies[$order->kWaehrung] = $order->Waehrung;
                }
                if (isset($order->fWaehrungsFaktor, $order->Waehrung->fFaktor) && $order->fWaehrungsFaktor !== 1) {
                    $order->Waehrung->fFaktor = $order->fWaehrungsFaktor;
                }
            }
            $order->cBestellwertLocalized = Preise::getLocalizedPriceString(
                $order->fGesamtsumme,
                $order->Waehrung
            );
            $order->Status                = \lang_bestellstatus($order->cStatus);
        }

        $orderPagination = (new Pagination('orders'))
            ->setItemArray($orders)
            ->setItemsPerPage(10)
            ->assemble();

        $this->smarty->assign('orderPagination', $orderPagination)
            ->assign('Bestellungen', $orders);
    }

    /**
     * @param int $customerID
     */
    private function deleteAccount(int $customerID): void
    {
        if (Form::validateToken() === true) {
            Frontend::getCustomer()->deleteAccount(
                Journal::ISSUER_TYPE_CUSTOMER,
                $customerID,
                false,
                true
            );

            \executeHook(\HOOK_JTL_PAGE_KUNDENACCOUNTLOESCHEN);
            \session_destroy();
            \header(
                'Location: ' . $this->linkService->getStaticRoute('registrieren.php') . '?accountDeleted=1',
                true,
                303
            );
            exit;
        }
        $this->alertService->addAlert(
            Alert::TYPE_NOTE,
            Shop::Lang()->get('csrfValidationFailed'),
            'csrfValidationFailed'
        );
        Shop::Container()->getLogService()->error('CSRF-Warnung fuer Account-Loeschung und kKunde ' . $customerID);
    }

    /**
     *
     */
    private function getCustomerFields(): void
    {
        $customer = $_SESSION['Kunde'];
        if (Request::postInt('edit') === 1) {
            $customer           = \getKundendaten($_POST, 0, 0);
            $customerAttributes = \getKundenattribute($_POST);
        } else {
            $customerAttributes = $customer->getCustomerAttributes();
        }

        $this->smarty->assign('Kunde', $customer)
            ->assign('customerAttributes', $customerAttributes)
            ->assign('laender', ShippingMethod::getPossibleShippingCountries(
                $customer->getGroupID(),
                false,
                true
            ))
            ->assign('oKundenfeld_arr', new CustomerFields(Shop::getLanguageID()));
    }

    /**
     * @param int $customerID
     * @param int $wishlistID
     * @return string
     */
    private function modifyWishlist(int $customerID, int $wishlistID): string
    {
        $step     = 'mein Konto';
        $wishlist = new Wishlist($wishlistID);
        if ($wishlist->kKunde !== $customerID) {
            return $step;
        }
        if (isset($_REQUEST['wlAction']) && Form::validateToken()) {
            $action = Request::verifyGPDataString('wlAction');
            if ($action === 'setPrivate') {
                Wishlist::setPrivate($wishlist->kWunschliste);
                $this->alertService->addAlert(
                    Alert::TYPE_NOTE,
                    Shop::Lang()->get('wishlistSetPrivate', 'messages'),
                    'wishlistSetPrivate'
                );
            } elseif ($action === 'setPublic') {
                Wishlist::setPublic($wishlist->kWunschliste);
                $this->alertService->addAlert(
                    Alert::TYPE_NOTE,
                    Shop::Lang()->get('wishlistSetPublic', 'messages'),
                    'wishlistSetPublic'
                );
            }
        }

        return $step;
    }

    /**
     *
     */
    private function changeCustomerData(): void
    {
        $postData = Text::filterXSS($_POST);
        $this->smarty->assign('cPost_arr', $postData);

        $missingData        = \checkKundenFormularArray($postData, 1, 0);
        $customerGroupID    = Frontend::getCustomerGroup()->getID();
        $checkBox           = new CheckBox();
        $missingData        = \array_merge(
            $missingData,
            $checkBox->validateCheckBox(\CHECKBOX_ORT_KUNDENDATENEDITIEREN, $customerGroupID, $postData, true)
        );
        $customerData       = \getKundendaten($postData, 0, 0);
        $customerAttributes = \getKundenattribute($postData);
        $returnCode         = \angabenKorrekt($missingData);

        \executeHook(\HOOK_JTL_PAGE_KUNDENDATEN_PLAUSI);

        if ($returnCode) {
            $customerData->cAbgeholt = 'N';
            $customerData->updateInDB();
            $checkBox->triggerSpecialFunction(
                \CHECKBOX_ORT_KUNDENDATENEDITIEREN,
                $customerGroupID,
                true,
                $postData,
                ['oKunde' => $customerData]
            )->checkLogging(\CHECKBOX_ORT_KUNDENDATENEDITIEREN, $customerGroupID, $postData, true);
            DataHistory::saveHistory($_SESSION['Kunde'], $customerData, DataHistory::QUELLE_MEINKONTO);
            $customerAttributes->save();
            $customerData->getCustomerAttributes()->load($customerData->getID());
            $_SESSION['Kunde'] = $customerData;
            $this->alertService->addAlert(
                Alert::TYPE_NOTE,
                Shop::Lang()->get('dataEditSuccessful', 'login'),
                'dataEditSuccessful'
            );
            Tax::setTaxRates();
            if (isset($_SESSION['Warenkorb']->kWarenkorb)
                && Frontend::getCart()->gibAnzahlArtikelExt([\C_WARENKORBPOS_TYP_ARTIKEL]) > 0
            ) {
                Frontend::getCart()->gibGesamtsummeWarenLocalized();
            }
        } else {
            $this->smarty->assign('fehlendeAngaben', $missingData);
        }
    }
}
