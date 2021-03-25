<?php

namespace JTL\Cart;

use JTL\Alert\Alert;
use JTL\Campaign;
use JTL\Catalog\Currency;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\EigenschaftWert;
use JTL\Catalog\Product\Preise;
use JTL\Catalog\Product\VariationValue;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\Checkout\Bestellung;
use JTL\Checkout\Kupon;
use JTL\Checkout\Lieferadresse;
use JTL\Checkout\Rechnungsadresse;
use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Extensions\Config\Configurator;
use JTL\Extensions\Config\Item;
use JTL\Extensions\Upload\Upload;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Media\Image;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;
use function Functional\filter;
use function Functional\map;

/**
 * Class CartHelper
 * @package JTL\Cart
 */
class CartHelper
{
    public const NET = 0;

    public const GROSS = 1;

    /**
     * @return stdClass
     */
    protected function initCartInfo(): stdClass
    {
        return (object)[
            'type'      => $this->getCustomerGroup()->isMerchant() ? self::NET : self::GROSS,
            'currency'  => $this->getCurrency(),
            'article'   => [0, 0],
            'shipping'  => [0, 0],
            'discount'  => [0, 0],
            'surcharge' => [0, 0],
            'total'     => [0, 0],
            'items'     => [],
        ];
    }

    /**
     * @param stdClass $cartInfo
     */
    protected function calculateCredit(stdClass $cartInfo): void
    {
        if (isset($_SESSION['Bestellung'], $_SESSION['Bestellung']->GuthabenNutzen)
            && $_SESSION['Bestellung']->GuthabenNutzen === 1
        ) {
            $amountGross = $_SESSION['Bestellung']->fGuthabenGenutzt * -1;

            $cartInfo->discount[self::NET]   += $amountGross;
            $cartInfo->discount[self::GROSS] += $amountGross;
        }
        // positive discount
        $cartInfo->discount[self::NET]   *= -1;
        $cartInfo->discount[self::GROSS] *= -1;
    }

    /**
     * @param stdClass $cartInfo
     */
    protected function calculateTotal(stdClass $cartInfo): void
    {
        $cartInfo->total[self::NET]   = $cartInfo->article[self::NET] +
            $cartInfo->shipping[self::NET] -
            $cartInfo->discount[self::NET] +
            $cartInfo->surcharge[self::NET];
        $cartInfo->total[self::GROSS] = $cartInfo->article[self::GROSS] +
            $cartInfo->shipping[self::GROSS] -
            $cartInfo->discount[self::GROSS] +
            $cartInfo->surcharge[self::GROSS];
    }

    /**
     * @param int $decimals
     * @return stdClass
     */
    public function getTotal(int $decimals = 0): stdClass
    {
        $info = $this->initCartInfo();

        foreach ($this->getPositions() as $item) {
            $amountItem = $item->fPreisEinzelNetto;
            if ((!isset($item->Artikel->kVaterArtikel) || (int)$item->Artikel->kVaterArtikel === 0)
                && GeneralObject::isCountable('WarenkorbPosEigenschaftArr', $item)
            ) {
                foreach ($item->WarenkorbPosEigenschaftArr as $attr) {
                    if ((float)$attr->fAufpreis !== 0.0) {
                        $amountItem += $attr->fAufpreis;
                    }
                }
            }
            $amount      = $amountItem * $info->currency->getConversionFactor();
            $amountGross = Tax::getGross($amount, Tax::getSalesTax((int)$item->kSteuerklasse));

            switch ((int)$item->nPosTyp) {
                case \C_WARENKORBPOS_TYP_ARTIKEL:
                case \C_WARENKORBPOS_TYP_GRATISGESCHENK:
                    $data = (object)[
                        'name'     => '',
                        'quantity' => 1,
                        'amount'   => []
                    ];

                    if (\is_array($item->cName)) {
                        $langIso    = $_SESSION['cISOSprache'];
                        $data->name = $item->cName[$langIso];
                    } else {
                        $data->name = $item->cName;
                    }

                    $data->name   = \html_entity_decode($data->name);
                    $data->amount = [
                        self::NET   => $amount,
                        self::GROSS => $amountGross
                    ];

                    if ($item->Artikel->cTeilbar === 'Y' && (int)$item->nAnzahl !== $item->nAnzahl) {
                        $data->amount[self::NET]   *= (float)$item->nAnzahl;
                        $data->amount[self::GROSS] *= (float)$item->nAnzahl;

                        $data->name = \sprintf(
                            '%g %s %s',
                            (float)$item->nAnzahl,
                            $item->Artikel->cEinheit ?: 'x',
                            $data->name
                        );
                    } else {
                        $data->quantity = (int)$item->nAnzahl;
                    }

                    $info->article[self::NET]   += $data->amount[self::NET] * $data->quantity;
                    $info->article[self::GROSS] += $data->amount[self::GROSS] * $data->quantity;

                    $info->items[] = $data;
                    break;

                case \C_WARENKORBPOS_TYP_VERSANDPOS:
                case \C_WARENKORBPOS_TYP_VERSANDZUSCHLAG:
                case \C_WARENKORBPOS_TYP_VERPACKUNG:
                case \C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG:
                    $info->shipping[self::NET]   += $amount * $item->nAnzahl;
                    $info->shipping[self::GROSS] += $amountGross * $item->nAnzahl;
                    break;

                case \C_WARENKORBPOS_TYP_KUPON:
                case \C_WARENKORBPOS_TYP_GUTSCHEIN:
                case \C_WARENKORBPOS_TYP_NEUKUNDENKUPON:
                    $info->discount[self::NET]   += $amount * $item->nAnzahl;
                    $info->discount[self::GROSS] += $amountGross * $item->nAnzahl;
                    break;

                case \C_WARENKORBPOS_TYP_ZAHLUNGSART:
                    if ($amount >= 0) {
                        $info->surcharge[self::NET]   += $amount * $item->nAnzahl;
                        $info->surcharge[self::GROSS] += $amountGross * $item->nAnzahl;
                    } else {
                        $amount                      *= -1;
                        $info->discount[self::NET]   += $amount * $item->nAnzahl;
                        $info->discount[self::GROSS] += $amountGross * $item->nAnzahl;
                    }
                    break;

                case \C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR:
                    $info->surcharge[self::NET]   += $amount * $item->nAnzahl;
                    $info->surcharge[self::GROSS] += $amountGross * $item->nAnzahl;
                    break;
                default:
                    break;
            }
        }

        $this->calculateCredit($info);
        $this->calculateTotal($info);

        $formatter = static function ($prop) use ($decimals) {
            return [
                self::NET   => \number_format($prop[self::NET], $decimals, '.', ''),
                self::GROSS => \number_format($prop[self::GROSS], $decimals, '.', ''),
            ];
        };

        if ($decimals > 0) {
            $info->article   = $formatter($info->article);
            $info->shipping  = $formatter($info->shipping);
            $info->discount  = $formatter($info->discount);
            $info->surcharge = $formatter($info->surcharge);
            $info->total     = $formatter($info->total);

            foreach ($info->items as $item) {
                $item->amount = $formatter($item->amount);
            }
        }

        return $info;
    }

    /**
     * @return Cart|Bestellung|null
     */
    public function getObject()
    {
        return $_SESSION['Warenkorb'];
    }

    /**
     * @return Lieferadresse|Rechnungsadresse
     */
    public function getShippingAddress()
    {
        return $_SESSION['Lieferadresse'];
    }

    /**
     * @return Rechnungsadresse
     */
    public function getBillingAddress(): ?Rechnungsadresse
    {
        return $_SESSION['Rechnungsadresse'];
    }

    /**
     * @return CartItem[]
     */
    public function getPositions(): array
    {
        return Frontend::getCart()->PositionenArr;
    }

    /**
     * @return Customer
     */
    public function getCustomer(): ?Customer
    {
        return Frontend::getCustomer();
    }

    /**
     * @return CustomerGroup
     */
    public function getCustomerGroup(): CustomerGroup
    {
        return Frontend::getCustomerGroup();
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return Frontend::getCurrency();
    }

    /**
     * @return string
     */
    public function getCurrencyISO(): string
    {
        return $this->getCurrency()->getCode();
    }

    /**
     * @return string
     */
    public function getInvoiceID(): string
    {
        return '';
    }

    /**
     * @return int
     */
    public function getIdentifier(): int
    {
        return 0;
    }

    /**
     * @param CartItem       $item
     * @param VariationValue $variation
     */
    public static function setVariationPicture(CartItem $item, VariationValue $variation): void
    {
        if ($item->variationPicturesArr === null) {
            $item->variationPicturesArr = [];
        }
        $imageBaseURL       = Shop::getImageBaseURL();
        $image              = (object)[
            'isVariation'  => true,
            'cPfadMini'    => \str_replace($imageBaseURL, '', $variation->getImage(Image::SIZE_XS)),
            'cPfadKlein'   => \str_replace($imageBaseURL, '', $variation->getImage(Image::SIZE_SM)),
            'cPfadNormal'  => \str_replace($imageBaseURL, '', $variation->getImage(Image::SIZE_MD)),
            'cPfadGross'   => \str_replace($imageBaseURL, '', $variation->getImage(Image::SIZE_LG)),
            'cURLMini'     => $variation->getImage(Image::SIZE_XS),
            'cURLKlein'    => $variation->getImage(Image::SIZE_SM),
            'cURLNormal'   => $variation->getImage(Image::SIZE_MD),
            'cURLGross'    => $variation->getImage(Image::SIZE_LG),
            'nNr'          => \count($item->variationPicturesArr) + 1,
            'cAltAttribut' => \str_replace(['"', "'"], '', $item->Artikel->cName . ' - ' . $variation->cName),
        ];
        $image->galleryJSON = $item->Artikel->getArtikelImageJSON($image);

        $item->variationPicturesArr[] = $image;
    }

    /**
     * @param Cart $warenkorb
     * @return int - since 5.0.0
     */
    public static function addVariationPictures(Cart $warenkorb): int
    {
        $count = 0;
        foreach ($warenkorb->PositionenArr as $item) {
            if (isset($item->variationPicturesArr) && \count($item->variationPicturesArr) > 0) {
                Product::addVariationPictures($item->Artikel, $item->variationPicturesArr);
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @former checkeWarenkorbEingang()
     * @return bool
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    public static function checkAdditions(): bool
    {
        $fAnzahl = 0;
        if (isset($_POST['anzahl'])) {
            $_POST['anzahl'] = \str_replace(',', '.', $_POST['anzahl']);
        }
        if (isset($_POST['anzahl']) && (float)$_POST['anzahl'] > 0) {
            $fAnzahl = (float)$_POST['anzahl'];
        } elseif (isset($_GET['anzahl']) && (float)$_GET['anzahl'] > 0) {
            $fAnzahl = (float)$_GET['anzahl'];
        }
        if (isset($_POST['n']) && (float)$_POST['n'] > 0) {
            $fAnzahl = (float)$_POST['n'];
        } elseif (isset($_GET['n']) && (float)$_GET['n'] > 0) {
            $fAnzahl = (float)$_GET['n'];
        }
        $productID = isset($_POST['a']) ? (int)$_POST['a'] : Request::verifyGPCDataInt('a');
        $conf      = Shop::getSettings([\CONF_GLOBAL, \CONF_VERGLEICHSLISTE]);
        \executeHook(\HOOK_TOOLS_GLOBAL_CHECKEWARENKORBEINGANG_ANFANG, [
            'kArtikel' => $productID,
            'fAnzahl'  => $fAnzahl
        ]);
        if ($productID > 0
            && (isset($_POST['Wunschliste']) || isset($_GET['Wunschliste']))
            && $conf['global']['global_wunschliste_anzeigen'] === 'Y'
        ) {
            return self::checkWishlist(
                $productID,
                $fAnzahl,
                $conf['global']['global_wunschliste_weiterleitung'] === 'Y'
            );
        }
        if (isset($_POST['Vergleichsliste']) && $productID > 0) {
            return self::checkCompareList($productID, (int)$conf['vergleichsliste']['vergleichsliste_anzahl']);
        }
        if ($productID > 0
            && !isset($_POST['Vergleichsliste'])
            && !isset($_POST['Wunschliste'])
            && Request::postInt('wke') === 1
        ) { //warenkorbeingang?
            return self::checkCart($productID, $fAnzahl);
        }

        return false;
    }

    /**
     * @param int $productID
     * @param int|float $count
     * @return bool
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    private static function checkCart(int $productID, $count): bool
    {
        // VariationsBox ist vorhanden => Prüfen ob Anzahl gesetzt wurde
        if (Request::postInt('variBox') === 1) {
            if (self::checkVariboxAmount($_POST['variBoxAnzahl'] ?? [])) {
                self::addVariboxToCart(
                    $_POST['variBoxAnzahl'],
                    $productID,
                    Product::isParent($productID),
                    isset($_POST['varimatrix'])
                );
            } else {
                \header('Location: ' . Shop::getURL() . '/?a=' . $productID . '&r=' . \R_EMPTY_VARIBOX, true, 303);
                exit;
            }

            return true;
        }
        if (Product::isParent($productID)) { // Varikombi
            $productID  = Product::getArticleForParent($productID);
            $attributes = Product::getSelectedPropertiesForVarCombiArticle($productID);
        } else {
            $attributes = Product::getSelectedPropertiesForArticle($productID);
        }
        $isConfigProduct = false;
        if (Configurator::checkLicense()) {
            if (!Configurator::validateKonfig($productID)) {
                $isConfigProduct = false;
            } else {
                $groups          = Configurator::getKonfig($productID);
                $isConfigProduct = GeneralObject::hasCount($groups);
            }
        }

        if (!$isConfigProduct) {
            return self::addProductIDToCart($productID, $count, $attributes);
        }
        $valid             = true;
        $errors            = [];
        $itemErrors        = [];
        $configItems       = [];
        $configGroups      = GeneralObject::isCountable('item', $_POST)
            ? $_POST['item']
            : [];
        $configGroupCounts = GeneralObject::isCountable('quantity', $_POST)
            ? $_POST['quantity']
            : [];
        $configItemCounts  = GeneralObject::isCountable('item_quantity', $_POST)
            ? $_POST['item_quantity']
            : false;
        $ignoreLimits      = isset($_POST['konfig_ignore_limits']);
        // Beim Bearbeiten die alten Positionen löschen
        if (isset($_POST['kEditKonfig'])) {
            self::deleteCartItem(Request::postInt('kEditKonfig'));
        }

        foreach ($configGroups as $itemList) {
            foreach ($itemList as $configItemID) {
                $configItemID = (int)$configItemID;
                // Falls ungültig, ignorieren
                if ($configItemID <= 0) {
                    continue;
                }
                $configItem          = new Item($configItemID);
                $configItem->fAnzahl = (float)($configItemCounts[$configItemID]
                    ?? $configGroupCounts[$configItem->getKonfiggruppe()] ?? $configItem->getInitial());
                if ($configItemCounts && isset($configItemCounts[$configItem->getKonfigitem()])) {
                    $configItem->fAnzahl = (float)$configItemCounts[$configItem->getKonfigitem()];
                }
                // Todo: Mindestbestellanzahl / Abnahmeinterval beachten
                if ($configItem->fAnzahl < 1) {
                    $configItem->fAnzahl = 1;
                }
                $count                 = \max($count, 1);
                $configItem->fAnzahlWK = $configItem->fAnzahl;
                if (!$configItem->ignoreMultiplier()) {
                    $configItem->fAnzahlWK *= $count;
                }
                $configItems[] = $configItem;
                // Alle Artikel können in den WK gelegt werden?
                if ($configItem->getPosTyp() === \KONFIG_ITEM_TYP_ARTIKEL) {
                    // Varikombi
                    /** @var Artikel $tmpProduct */
                    $configItem->oEigenschaftwerte_arr = [];
                    $tmpProduct                        = $configItem->getArtikel();

                    if ($tmpProduct !== null
                        && $tmpProduct->kVaterArtikel > 0
                        && isset($tmpProduct->kEigenschaftKombi)
                        && $tmpProduct->kEigenschaftKombi > 0
                    ) {
                        $configItem->oEigenschaftwerte_arr =
                            Product::getVarCombiAttributeValues($tmpProduct->kArtikel, false);
                    }
                    if ($tmpProduct->cTeilbar !== 'Y' && (int)$count !== $count) {
                        $count = \max((int)$count, 1);
                    }
                    $tmpProduct->isKonfigItem = true;
                    $redirectParam            = self::addToCartCheck(
                        $tmpProduct,
                        $configItem->fAnzahlWK,
                        $configItem->oEigenschaftwerte_arr
                    );
                    if (\count($redirectParam) > 0) {
                        $valid           = false;
                        $productMessages = Product::getProductMessages(
                            $redirectParam,
                            true,
                            $configItem->getArtikel(),
                            $configItem->fAnzahlWK,
                            $configItem->getKonfigitem()
                        );

                        $itemErrors[$configItem->getKonfigitem()] = $productMessages[0];
                    }
                }
            }
        }
        // Komplette Konfiguration validieren
        if (!$ignoreLimits && (($errors = Configurator::validateCart($productID, $configItems)) !== true)) {
            $valid = false;
        }
        // Alle Konfigurationsartikel können in den WK gelegt werden
        if ($valid) {
            // Eindeutige ID
            $cUnique = \uniqid('', true);
            // Hauptartikel in den WK legen
            self::addProductIDToCart($productID, $count, $attributes, 0, $cUnique);
            // Konfigartikel in den WK legen
            foreach ($configItems as $configItem) {
                $configItem->isKonfigItem = true;
                switch ($configItem->getPosTyp()) {
                    case \KONFIG_ITEM_TYP_ARTIKEL:
                        Frontend::getCart()->fuegeEin(
                            $configItem->getArtikelKey(),
                            $configItem->fAnzahlWK,
                            $configItem->oEigenschaftwerte_arr,
                            \C_WARENKORBPOS_TYP_ARTIKEL,
                            $cUnique,
                            $configItem->getKonfigitem()
                        );
                        break;

                    case \KONFIG_ITEM_TYP_SPEZIAL:
                        Frontend::getCart()->erstelleSpezialPos(
                            $configItem->getName(),
                            $configItem->fAnzahlWK,
                            $configItem->getPreis(),
                            $configItem->getSteuerklasse(),
                            \C_WARENKORBPOS_TYP_ARTIKEL,
                            false,
                            !Frontend::getCustomerGroup()->isMerchant(),
                            '',
                            $cUnique,
                            $configItem->getKonfigitem(),
                            $configItem->getArtikelKey()
                        );
                        break;
                }

                PersistentCart::addToCheck(
                    $configItem->getArtikelKey(),
                    $configItem->fAnzahlWK,
                    $configItem->oEigenschaftwerte_arr ?? [],
                    $cUnique,
                    $configItem->getKonfigitem()
                );
            }
            Frontend::getCart()->redirectTo();
        } else {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_ERROR,
                Shop::Lang()->get('configError', 'productDetails'),
                'configError',
                ['dismissable' => false]
            );
            Shop::Smarty()->assign('aKonfigerror_arr', $errors)
                ->assign('aKonfigitemerror_arr', $itemErrors);
        }

        $itemList = [];
        foreach ($configGroups as $item) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $itemList = \array_merge($itemList, $item);
        }
        Shop::Smarty()->assign('fAnzahl', $count)
            ->assign('nKonfigitem_arr', $itemList)
            ->assign('nKonfigitemAnzahl_arr', $configItemCounts)
            ->assign('nKonfiggruppeAnzahl_arr', $configGroupCounts);

        return $valid;
    }

    /**
     * @param int $productID
     * @param int $maxItems
     * @return bool
     */
    private static function checkCompareList(int $productID, int $maxItems): bool
    {
        $alertHelper = Shop::Container()->getAlertService();
        // Prüfen ob nicht schon die maximale Anzahl an Artikeln auf der Vergleichsliste ist
        $products = Frontend::get('Vergleichsliste')->oArtikel_arr ?? [];
        if ($maxItems <= \count($products)) {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_ERROR,
                Shop::Lang()->get('compareMaxlimit', 'errorMessages'),
                'compareMaxlimit',
                ['dismissable' => false]
            );

            return false;
        }
        $productExists = Shop::Container()->getDB()->select(
            'tartikel',
            'kArtikel',
            $productID,
            null,
            null,
            null,
            null,
            false,
            'kArtikel, cName'
        );
        if ($productExists === null) {
            return true;
        }
        $vis = Shop::Container()->getDB()->select(
            'tartikelsichtbarkeit',
            'kArtikel',
            $productID,
            'kKundengruppe',
            Frontend::getCustomerGroup()->getID(),
            null,
            null,
            false,
            'kArtikel'
        );
        if ($vis === null || !isset($vis->kArtikel) || !$vis->kArtikel) {
            // Prüfe auf Vater Artikel
            $variations = [];
            if (Product::isParent($productID)) {
                $productID  = Product::getArticleForParent($productID);
                $variations = Product::getSelectedPropertiesForVarCombiArticle($productID, 1);
            }
            $compareList = Frontend::getCompareList();
            if ($compareList->productExists($productID)) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    Shop::Lang()->get('comparelistProductexists', 'messages'),
                    'comparelistProductexists',
                    ['dismissable' => false]
                );
            } else {
                $compareList->addProduct($productID, $variations);
                $alertHelper->addAlert(
                    Alert::TYPE_NOTE,
                    Shop::Lang()->get('comparelistProductadded', 'messages'),
                    'comparelistProductadded'
                );
            }
        }

        return true;
    }

    /**
     * @param int       $productID
     * @param float|int $qty
     * @param bool      $redirect
     * @return bool
     */
    private static function checkWishlist(int $productID, $qty, $redirect): bool
    {
        $linkHelper = Shop::Container()->getLinkService();
        if (!isset($_POST['login']) && Frontend::getCustomer()->getID() < 1) {
            if ($qty <= 0) {
                $qty = 1;
            }
            \header('Location: ' . $linkHelper->getStaticRoute('jtl.php') .
                '?a=' . $productID .
                '&n=' . $qty .
                '&r=' . \R_LOGIN_WUNSCHLISTE, true, 302);
            exit;
        }

        if ($productID > 0 && Frontend::getCustomer()->getID() > 0) {
            $productExists = Shop::Container()->getDB()->select(
                'tartikel',
                'kArtikel',
                $productID,
                null,
                null,
                null,
                null,
                false,
                'kArtikel, cName'
            );
            if ($productExists !== null && $productExists->kArtikel > 0) {
                $vis = Shop::Container()->getDB()->select(
                    'tartikelsichtbarkeit',
                    'kArtikel',
                    $productID,
                    'kKundengruppe',
                    Frontend::getCustomerGroup()->getID(),
                    null,
                    null,
                    false,
                    'kArtikel'
                );
                if ($vis === null || !$vis->kArtikel) {
                    if (Product::isParent($productID)) {
                        // Falls die Wunschliste aus der Artikelübersicht ausgewählt wurde,
                        // muss zum Artikel weitergeleitet werden um Variationen zu wählen
                        if (Request::verifyGPCDataInt('overview') === 1) {
                            \header('Location: ' . Shop::getURL() . '/?a=' . $productID .
                                '&n=' . $qty .
                                '&r=' . \R_VARWAEHLEN, true, 303);
                            exit;
                        }

                        $productID  = Product::getArticleForParent($productID);
                        $attributes = $productID > 0
                            ? Product::getSelectedPropertiesForVarCombiArticle($productID)
                            : [];
                    } else {
                        $attributes = Product::getSelectedPropertiesForArticle($productID);
                    }
                    /** @noinspection NotOptimalIfConditionsInspection */
                    if ($productID > 0) {
                        if (empty($_SESSION['Wunschliste']->kWunschliste)) {
                            $_SESSION['Wunschliste'] = new Wishlist();
                            $_SESSION['Wunschliste']->schreibeDB();
                        }
                        $qty             = \max(1, $qty);
                        $kWunschlistePos = $_SESSION['Wunschliste']->fuegeEin(
                            $productID,
                            $productExists->cName,
                            $attributes,
                            $qty
                        );
                        if (isset($_SESSION['Kampagnenbesucher'])) {
                            Campaign::setCampaignAction(\KAMPAGNE_DEF_WUNSCHLISTE, $kWunschlistePos, $qty);
                        }

                        $obj           = new stdClass();
                        $obj->kArtikel = $productID;
                        \executeHook(\HOOK_TOOLS_GLOBAL_CHECKEWARENKORBEINGANG_WUNSCHLISTE, [
                            'kArtikel'         => &$productID,
                            'fAnzahl'          => &$qty,
                            'AktuellerArtikel' => &$obj
                        ]);

                        Shop::Container()->getAlertService()->addAlert(
                            Alert::TYPE_NOTE,
                            Shop::Lang()->get('wishlistProductadded', 'messages'),
                            'wishlistProductadded'
                        );
                        if ($redirect === true && !Request::isAjaxRequest()) {
                            \header('Location: ' . $linkHelper->getStaticRoute('wunschliste.php'), true, 302);
                            exit;
                        }
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param Artikel|object $product
     * @param int|float      $qty
     * @param array          $attributes
     * @param int            $accuracy
     * @param string|null    $token
     * @return array
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     * @former pruefeFuegeEinInWarenkorb()
     */
    public static function addToCartCheck($product, $qty, $attributes, int $accuracy = 2, ?string $token = null): array
    {
        $cart          = Frontend::getCart();
        $productID     = (int)$product->kArtikel; // relevant für die Berechnung von Artikelsummen im Warenkorb
        $redirectParam = [];
        $conf          = Shop::getSettings([\CONF_GLOBAL]);
        if ($product->fAbnahmeintervall > 0 && !self::isMultiple($qty, (float)$product->fAbnahmeintervall)) {
            $redirectParam[] = \R_ARTIKELABNAHMEINTERVALL;
        }
        if ((int)$qty !== $qty && $product->cTeilbar !== 'Y') {
            $qty = \max((int)$qty, 1);
        }
        if ($product->fMindestbestellmenge > $qty + $cart->gibAnzahlEinesArtikels($productID)) {
            $redirectParam[] = \R_MINDESTMENGE;
        }
        if ($product->cLagerBeachten === 'Y'
            && $product->cLagerVariation !== 'Y'
            && $product->cLagerKleinerNull !== 'Y'
        ) {
            foreach ($product->getAllDependentProducts(true) as $dependent) {
                /** @var Artikel $product */
                $depProduct = $dependent->product;
                if ($depProduct->fPackeinheit
                    * ($qty * $dependent->stockFactor +
                        Frontend::getCart()->getDependentAmount(
                            $depProduct->kArtikel,
                            true
                        )
                    ) > $depProduct->fLagerbestand
                ) {
                    $redirectParam[] = \R_LAGER;
                    break;
                }
            }
        }
        if (!Frontend::getCustomerGroup()->mayViewPrices() || !Frontend::getCustomerGroup()->mayViewCategories()) {
            $redirectParam[] = \R_LOGIN;
        }
        // kein vorbestellbares Produkt, aber mit Erscheinungsdatum in Zukunft
        if ($product->nErscheinendesProdukt && $conf['global']['global_erscheinende_kaeuflich'] === 'N') {
            $redirectParam[] = \R_VORBESTELLUNG;
        }
        // Die maximale Bestellmenge des Artikels wurde überschritten
        if (isset($product->FunktionsAttribute[\FKT_ATTRIBUT_MAXBESTELLMENGE])
            && $product->FunktionsAttribute[\FKT_ATTRIBUT_MAXBESTELLMENGE] > 0
            && ($qty > $product->FunktionsAttribute[\FKT_ATTRIBUT_MAXBESTELLMENGE]
                || ($cart->gibAnzahlEinesArtikels($productID) + $qty) >
                $product->FunktionsAttribute[\FKT_ATTRIBUT_MAXBESTELLMENGE])
        ) {
            $redirectParam[] = \R_MAXBESTELLMENGE;
        }
        // Der Artikel ist unverkäuflich
        if (isset($product->FunktionsAttribute[\FKT_ATTRIBUT_UNVERKAEUFLICH])
            && (int)$product->FunktionsAttribute[\FKT_ATTRIBUT_UNVERKAEUFLICH] === 1
        ) {
            $redirectParam[] = \R_UNVERKAEUFLICH;
        }
        // Preis auf Anfrage
        // verhindert, dass Konfigitems mit Preis=0 aus der Artikelkonfiguration fallen
        // wenn 'Preis auf Anfrage' eingestellt ist
        /** @noinspection MissingIssetImplementationInspection */
        if ($product->bHasKonfig === false
            && !empty($product->isKonfigItem)
            && $product->inWarenkorbLegbar === \INWKNICHTLEGBAR_PREISAUFANFRAGE
        ) {
            $product->inWarenkorbLegbar = 1;
        }
        /** @noinspection MissingIssetImplementationInspection */
        if (($product->bHasKonfig === false && empty($product->isKonfigItem))
            && (!isset($product->Preise->fVKNetto) || (float)$product->Preise->fVKNetto === 0)
            && $conf['global']['global_preis0'] === 'N'
        ) {
            $redirectParam[] = \R_AUFANFRAGE;
        }
        // fehlen zu einer Variation werte?
        foreach ($product->Variationen as $var) {
            if (\count($redirectParam) > 0) {
                break;
            }
            if ($var->cTyp === 'FREIFELD') {
                continue;
            }
            $exists = false;
            foreach ($attributes as $oEigenschaftwerte) {
                $oEigenschaftwerte->kEigenschaft = (int)$oEigenschaftwerte->kEigenschaft;
                if ($oEigenschaftwerte->kEigenschaft !== $var->kEigenschaft) {
                    continue;
                }
                if ($var->cTyp === 'PFLICHT-FREIFELD') {
                    if ($oEigenschaftwerte->cFreifeldWert !== '') {
                        $exists = true;
                    } else {
                        $redirectParam[] = \R_VARWAEHLEN;
                        break;
                    }
                } else {
                    $exists = true;
                    //schau, ob auch genug davon auf Lager
                    $attrValue = new EigenschaftWert($oEigenschaftwerte->kEigenschaftWert);
                    //ist der Eigenschaftwert überhaupt gültig?
                    if ($attrValue->kEigenschaft !== $oEigenschaftwerte->kEigenschaft) {
                        $redirectParam[] = \R_VARWAEHLEN;
                        break;
                    }
                    //schaue, ob genug auf Lager von jeder var
                    if ($product->cLagerBeachten === 'Y'
                        && $product->cLagerVariation === 'Y'
                        && $product->cLagerKleinerNull !== 'Y'
                    ) {
                        if ((float)$attrValue->fPackeinheit === 0) {
                            $attrValue->fPackeinheit = 1;
                        }
                        if ($attrValue->fPackeinheit *
                            ($qty +
                                $cart->gibAnzahlEinerVariation(
                                    $productID,
                                    $attrValue->kEigenschaftWert
                                )
                            ) > $attrValue->fLagerbestand
                        ) {
                            $redirectParam[] = \R_LAGERVAR;
                        }
                    }
                    break;
                }
            }
            if (!$exists) {
                $redirectParam[] = \R_VARWAEHLEN;
                break;
            }
        }
        if (!Form::validateToken($token)) {
            $redirectParam[] = \R_MISSING_TOKEN;
        }
        \executeHook(\HOOK_ADD_TO_CART_CHECK, [
            'product'       => $product,
            'quantity'      => $qty,
            'attributes'    => $attributes,
            'accuracy'      => $accuracy,
            'redirectParam' => &$redirectParam
        ]);

        return $redirectParam;
    }

    /**
     * @param array $amounts
     * @return bool
     * @former pruefeVariBoxAnzahl
     */
    public static function checkVariboxAmount(array $amounts): bool
    {
        foreach (\array_keys($amounts) as $cKeys) {
            if ((float)$amounts[$cKeys] > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param float         $total
     * @param Currency|null $currency
     * @return float
     * @since 5.0.0
     */
    public static function roundOptionalCurrency($total, Currency $currency = null)
    {
        $factor = ($currency ?? Frontend::getCurrency())->getConversionFactor();

        return self::roundOptional($total * $factor) / $factor;
    }

    /**
     * @param float $total
     * @return float
     * @since 5.0.0
     */
    public static function roundOptional($total)
    {
        $conf = Shop::getSettings([\CONF_KAUFABWICKLUNG]);

        if (isset($conf['kaufabwicklung']['bestellabschluss_runden5'])
            && (int)$conf['kaufabwicklung']['bestellabschluss_runden5'] === 1
        ) {
            return \round($total * 20) / 20;
        }

        return $total;
    }

    /**
     * @param Artikel $product
     * @param float   $qty
     * @return int|null
     * @former pruefeWarenkorbStueckliste()
     * @since 5.0.0
     */
    public static function checkCartPartComponent($product, $qty): ?int
    {
        $partList = Product::isStuecklisteKomponente($product->kArtikel, true);
        if (!(\is_object($product) && $product->cLagerBeachten === 'Y'
            && $product->cLagerKleinerNull !== 'Y'
            && ($product->kStueckliste > 0 || $partList))
        ) {
            return null;
        }
        $isComponent = false;
        $components  = null;
        if (isset($partList->kStueckliste)) {
            $isComponent = true;
        } else {
            $components = self::getPartComponent($product->kStueckliste, true);
        }
        foreach (Frontend::getCart()->PositionenArr as $item) {
            if ($item->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL) {
                continue;
            }
            if ($isComponent
                && isset($item->Artikel->kStueckliste)
                && $item->Artikel->kStueckliste > 0
                && ($item->nAnzahl * $partList->fAnzahl + $qty) > $product->fLagerbestand
            ) {
                return \R_LAGER;
            }
            if (!$isComponent && \count($components) > 0) {
                if (!empty($item->Artikel->kStueckliste)) {
                    $itemComponents = self::getPartComponent($item->Artikel->kStueckliste, true);
                    foreach ($itemComponents as $component) {
                        $desiredComponentQuantity = $qty * $components[$component->kArtikel]->fAnzahl;
                        $currentComponentStock    = $item->Artikel->fLagerbestand * $component->fAnzahl;
                        if ($desiredComponentQuantity > $currentComponentStock) {
                            return \R_LAGER;
                        }
                    }
                } elseif (isset($components[$item->kArtikel])
                    && (($item->nAnzahl * $components[$item->kArtikel]->fAnzahl) +
                        ($components[$item->kArtikel]->fAnzahl * $qty)) > $item->Artikel->fLagerbestand
                ) {
                    return \R_LAGER;
                }
            }
        }

        return null;
    }

    /**
     * @param int  $kStueckliste
     * @param bool $assoc
     * @return array
     * @former gibStuecklistenKomponente()
     * @since 5.0.0
     */
    public static function getPartComponent(int $kStueckliste, bool $assoc = false): array
    {
        if ($kStueckliste > 0) {
            $data = Shop::Container()->getDB()->selectAll('tstueckliste', 'kStueckliste', $kStueckliste);
            if (\count($data) > 0) {
                if ($assoc) {
                    $res = [];
                    foreach ($data as $item) {
                        $res[$item->kArtikel] = $item;
                    }

                    return $res;
                }

                return $data;
            }
        }

        return [];
    }

    /**
     * @param object $item
     * @param object $coupon
     * @return mixed
     * @former checkeKuponWKPos()
     * @since 5.0.0
     */
    public static function checkCouponCartItems($item, $coupon)
    {
        $item->nPosTyp = (int)$item->nPosTyp;
        if ($item->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL) {
            return $item;
        }
        $categoryQRY = '';
        $customerQRY = '';
        $categoryIDs = [];
        if ($item->Artikel->kArtikel > 0 && $item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
            $productID = (int)$item->Artikel->kArtikel;
            if (Product::isVariChild($productID)) {
                $productID = Product::getParent($productID);
            }
            $categories = Shop::Container()->getDB()->selectAll('tkategorieartikel', 'kArtikel', $productID);
            foreach ($categories as $category) {
                $category->kKategorie = (int)$category->kKategorie;
                if (!\in_array($category->kKategorie, $categoryIDs, true)) {
                    $categoryIDs[] = $category->kKategorie;
                }
            }
        }
        foreach ($categoryIDs as $id) {
            $categoryQRY .= " OR FIND_IN_SET('" . $id . "', REPLACE(cKategorien, ';', ',')) > 0";
        }
        if (Frontend::getCustomer()->isLoggedIn()) {
            $customerQRY = " OR FIND_IN_SET('" .
                Frontend::getCustomer()->getID() . "', REPLACE(cKunden, ';', ',')) > 0";
        }
        $couponsOK = Shop::Container()->getDB()->queryPrepared(
            "SELECT *
                FROM tkupon
                WHERE cAktiv = 'Y'
                    AND dGueltigAb <= NOW()
                    AND (dGueltigBis IS NULL OR dGueltigBis > NOW())
                    AND fMindestbestellwert <= :minAmount
                    AND (kKundengruppe = -1
                        OR kKundengruppe = 0
                        OR kKundengruppe = :cgID)
                    AND (nVerwendungen = 0
                        OR nVerwendungen > nVerwendungenBisher)
                    AND (cArtikel = '' OR FIND_IN_SET(:artNO, REPLACE(cArtikel, ';', ',')) > 0)
                    AND (cHersteller = '-1' OR FIND_IN_SET(:manuf, REPLACE(cHersteller, ';', ',')) > 0)
                    AND (cKategorien = '' OR cKategorien = '-1' " . $categoryQRY . ")
                    AND (cKunden = '' OR cKunden = '-1' " . $customerQRY . ')
                    AND kKupon = :couponID',
            [
                'minAmount' => Frontend::getCart()->gibGesamtsummeWaren(true, false),
                'cgID'      => Frontend::getCustomerGroup()->getID(),
                'artNO'     => \str_replace('%', '\%', $item->Artikel->cArtNr),
                'manuf'     => \str_replace('%', '\%', $item->Artikel->kHersteller),
                'couponID'  => (int)$coupon->kKupon
            ],
            ReturnType::SINGLE_OBJECT
        );
        if (isset($couponsOK->kKupon)
            && $couponsOK->kKupon > 0
            && $couponsOK->cWertTyp === 'prozent'
            && !Frontend::getCart()->posTypEnthalten(\C_WARENKORBPOS_TYP_KUPON)
        ) {
            $item->fPreisEinzelNetto -= ($item->fPreisEinzelNetto / 100) * $coupon->fWert;
            $item->fPreis            -= ($item->fPreis / 100) * $coupon->fWert;
            $item->cHinweis           = $coupon->cName .
                ' (' . \str_replace('.', ',', $coupon->fWert) .
                '% ' . Shop::Lang()->get('discount') . ')';

            if (\is_array($item->WarenkorbPosEigenschaftArr)) {
                foreach ($item->WarenkorbPosEigenschaftArr as $attribute) {
                    if (isset($attribute->fAufpreis) && (float)$attribute->fAufpreis > 0) {
                        $attribute->fAufpreis -= ((float)$attribute->fAufpreis / 100) * $coupon->fWert;
                    }
                }
            }
            foreach (Frontend::getCurrencies() as $currency) {
                $currencyName                                  = $currency->getName();
                $item->cGesamtpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                    Tax::getGross(
                        $item->fPreis * $item->nAnzahl,
                        Tax::getSalesTax($item->kSteuerklasse)
                    ),
                    $currency
                );
                $item->cGesamtpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                    $item->fPreis * $item->nAnzahl,
                    $currency
                );
                $item->cEinzelpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                    Tax::getGross($item->fPreis, Tax::getSalesTax($item->kSteuerklasse)),
                    $currency
                );
                $item->cEinzelpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                    $item->fPreis,
                    $currency
                );
            }
        }

        return $item;
    }

    /**
     * @param object $cartItem
     * @param object $coupon
     * @return mixed
     * @former checkSetPercentCouponWKPos()
     * @since 5.0.0
     */
    public static function checkSetPercentCouponWKPos($cartItem, $coupon)
    {
        $item              = new stdClass();
        $item->fPreis      = (float)0;
        $item->cName       = '';
        $cartItem->nPosTyp = (int)$cartItem->nPosTyp;
        if ($cartItem->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL) {
            return $item;
        }
        $categoryQRY = '';
        $customerQRY = '';
        $categoryIDs = [];
        if ($cartItem->Artikel->kArtikel > 0 && $cartItem->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
            $productID = (int)$cartItem->Artikel->kArtikel;
            if (Product::isVariChild($productID)) {
                $productID = Product::getParent($productID);
            }
            $categories = Shop::Container()->getDB()->selectAll(
                'tkategorieartikel',
                'kArtikel',
                $productID,
                'kKategorie'
            );
            foreach ($categories as $category) {
                $category->kKategorie = (int)$category->kKategorie;
                if (!\in_array($category->kKategorie, $categoryIDs, true)) {
                    $categoryIDs[] = $category->kKategorie;
                }
            }
        }
        foreach ($categoryIDs as $id) {
            $categoryQRY .= " OR FIND_IN_SET('" . $id . "', REPLACE(cKategorien, ';', ',')) > 0";
        }
        if (Frontend::getCustomer()->isLoggedIn()) {
            $customerQRY = " OR FIND_IN_SET('" .
                Frontend::getCustomer()->getID() . "', REPLACE(cKunden, ';', ',')) > 0";
        }
        $couponOK = Shop::Container()->getDB()->queryPrepared(
            "SELECT *
                FROM tkupon
                WHERE cAktiv = 'Y'
                    AND dGueltigAb <= NOW()
                    AND (dGueltigBis IS NULL OR dGueltigBis > NOW())
                    AND fMindestbestellwert <= :minAmount
                    AND (kKundengruppe = -1
                        OR kKundengruppe = 0
                        OR kKundengruppe = :cgID)
                    AND (nVerwendungen = 0 OR nVerwendungen > nVerwendungenBisher)
                    AND (cArtikel = '' OR FIND_IN_SET(:artNo, REPLACE(cArtikel, ';', ',')) > 0)
                    AND (cHersteller = '-1' OR FIND_IN_SET(:manuf, REPLACE(cHersteller, ';', ',')) > 0)
                    AND (cKategorien = '' OR cKategorien = '-1' " . $categoryQRY . ")
                    AND (cKunden = '' OR cKunden = '-1' " . $customerQRY . ')
                    AND kKupon = :couponID',
            [
                'minAmount' => Frontend::getCart()->gibGesamtsummeWaren(true, false),
                'cgID'      => Frontend::getCustomerGroup()->getID(),
                'artNo'     => \str_replace('%', '\%', $cartItem->Artikel->cArtNr),
                'manuf'     => \str_replace('%', '\%', $cartItem->Artikel->kHersteller),
                'couponID'  => $coupon->kKupon

            ],
            ReturnType::SINGLE_OBJECT
        );
        if (isset($couponOK->kKupon) && $couponOK->kKupon > 0 && $couponOK->cWertTyp === 'prozent') {
            $item->fPreis = $cartItem->fPreis *
                Frontend::getCurrency()->getConversionFactor() *
                $cartItem->nAnzahl *
                ((100 + Tax::getSalesTax($cartItem->kSteuerklasse)) / 100);
            $item->cName  = $cartItem->cName;
        }

        return $item;
    }

    /**
     * @param array $variBoxCounts
     * @param int $productID
     * @param bool $isParent
     * @param bool $isVariMatrix
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     * @former fuegeVariBoxInWK()
     * @since 5.0.0
     */
    public static function addVariboxToCart(
        array $variBoxCounts,
        int $productID,
        bool $isParent,
        bool $isVariMatrix = false
    ): void {
        if (\count($variBoxCounts) === 0) {
            return;
        }
        $parentID   = $productID;
        $attributes = [];
        unset($_SESSION['variBoxAnzahl_arr']);

        foreach (\array_keys($variBoxCounts) as $key) {
            if ((float)$variBoxCounts[$key] <= 0) {
                continue;
            }
            if ($isVariMatrix) {
                // varkombi matrix - all keys are IDs of a concrete child
                $productID                       = (int)$key;
                $properties                      = Product::getPropertiesForVarCombiArticle($productID, $parentID);
                $variKombi                       = new stdClass();
                $variKombi->fAnzahl              = (float)$variBoxCounts[$key];
                $variKombi->kEigenschaft_arr     = \array_keys($properties);
                $variKombi->kEigenschaftWert_arr = \array_values($properties);

                $_POST['eigenschaftwert']            = $properties;
                $_SESSION['variBoxAnzahl_arr'][$key] = $variKombi;
                $attributes[$key]                    = new stdClass();
                $attributes[$key]->kArtikel          = $productID;
                $attributes[$key]->oEigenschaft_arr  = \array_map(static function ($a) use ($properties) {
                    return (object)[
                        'kEigenschaft'     => $a,
                        'kEigenschaftWert' => $properties[$a],
                    ];
                }, $variKombi->kEigenschaft_arr);
            } elseif (\preg_match('/([0-9:]+)?_([0-9:]+)/', $key, $hits) && \count($hits) === 3) {
                if (empty($hits[1])) {
                    // 1-dimensional matrix - key is combination of property id and property value
                    unset($hits[1]);
                    $n = 1;
                } else {
                    // 2-dimensional matrix - key is set of combinations of property id and property value
                    $n = 2;
                }
                \array_shift($hits);

                $variKombi          = new stdClass();
                $variKombi->fAnzahl = (float)$variBoxCounts[$key];
                for ($i = 0; $i < $n; $i++) {
                    [$propertyID, $propertyValue]         = \explode(':', $hits[$i]);
                    $variKombi->{'cVariation' . $i}       = Text::filterXSS($hits[$i]);
                    $variKombi->{'kEigenschaft' . $i}     = (int)$propertyID;
                    $variKombi->{'kEigenschaftWert' . $i} = (int)$propertyValue;

                    $_POST['eigenschaftwert_' . Text::filterXSS($propertyID)] = (int)$propertyValue;
                }

                $_SESSION['variBoxAnzahl_arr'][$key] = $variKombi;
                $attributes[$key]                    = new stdClass();
                $attributes[$key]->oEigenschaft_arr  = [];
                $attributes[$key]->kArtikel          = 0;

                if ($isParent) {
                    $productID                          = Product::getArticleForParent($parentID);
                    $attributes[$key]->oEigenschaft_arr = Product::getSelectedPropertiesForVarCombiArticle($productID);
                } else {
                    $attributes[$key]->oEigenschaft_arr = Product::getSelectedPropertiesForArticle($productID);
                }
                $attributes[$key]->kArtikel = $productID;
            }
        }

        if (\count($attributes) === 0) {
            return;
        }
        $errors         = [];
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($attributes as $key => $attribute) {
            $redirects = self::addToCartCheck(
                (new Artikel())->fuelleArtikel($attribute->kArtikel, $defaultOptions),
                (float)$variBoxCounts[$key],
                $attribute->oEigenschaft_arr
            );

            $_SESSION['variBoxAnzahl_arr'][$key]->bError = false;
            if (\count($redirects) > 0) {
                foreach ($redirects as $redirect) {
                    $redirect = (int)$redirect;
                    if (!\in_array($redirect, $errors, true)) {
                        $errors[] = $redirect;
                    }
                }
                $_SESSION['variBoxAnzahl_arr'][$key]->bError = true;
            }
        }
        if (\count($errors) > 0) {
            \header('Location: ' . Shop::getURL() . '/?a=' . ($isParent ? $parentID : $productID) .
                '&r=' . \implode(',', $errors), true, 302);
            exit();
        }

        foreach ($attributes as $key => $attribute) {
            if (!$_SESSION['variBoxAnzahl_arr'][$key]->bError) {
                //#8224, #7482 -> do not call setzePositionsPreise() in loop @ Wanrekob::fuegeEin()
                self::addProductIDToCart(
                    $attribute->kArtikel,
                    (float)$variBoxCounts[$key],
                    $attribute->oEigenschaft_arr,
                    0,
                    false,
                    0,
                    null,
                    false
                );
            }
        }

        Frontend::getCart()->setzePositionsPreise();
        unset($_SESSION['variBoxAnzahl_arr']);
        Frontend::getCart()->redirectTo();
    }

    /**
     * @param int              $productID
     * @param int|string|float $qty
     * @param array            $attrValues
     * @param int              $redirect
     * @param string           $unique
     * @param int              $configItemID
     * @param stdClass|null    $options
     * @param bool             $setzePositionsPreise
     * @param string           $responsibility
     * @return bool
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     * @former fuegeEinInWarenkorb()
     * @since 5.0.0
     */
    public static function addProductIDToCart(
        int $productID,
        $qty,
        array $attrValues = [],
        $redirect = 0,
        $unique = '',
        int $configItemID = 0,
        $options = null,
        bool $setzePositionsPreise = true,
        string $responsibility = 'core'
    ): bool {
        if (!($qty > 0 && ($productID > 0 || ($productID === 0 && !empty($configItemID) && !empty($unique))))) {
            return false;
        }
        $product = new Artikel();
        $options = $options ?? Artikel::getDefaultOptions();
        $product->fuelleArtikel($productID, $options);
        if ((int)$qty !== $qty && $product->cTeilbar !== 'Y') {
            $qty = \max((int)$qty, 1);
        }
        $redirectParam = self::addToCartCheck($product, $qty, $attrValues);
        // verhindert, dass Konfigitems mit Preis=0 aus der Artikelkonfiguration fallen
        // wenn 'Preis auf Anfrage' eingestellt ist
        if (!empty($configItemID) && isset($redirectParam[0]) && $redirectParam[0] === \R_AUFANFRAGE) {
            unset($redirectParam[0]);
        }

        if (\count($redirectParam) > 0) {
            if (isset($_SESSION['variBoxAnzahl_arr'])) {
                return false;
            }
            if ($redirect === 0) {
                $con = (\mb_strpos($product->cURLFull, '?') === false) ? '?' : '&';
                if ($product->kEigenschaftKombi > 0) {
                    $url = empty($product->cURLFull)
                        ? (Shop::getURL() . '/?a=' . $product->kVaterArtikel .
                            '&a2=' . $product->kArtikel . '&')
                        : ($product->cURLFull . $con);
                    \header('Location: ' . $url . 'n=' . $qty . '&r=' . \implode(',', $redirectParam), true, 302);
                } else {
                    $url = empty($product->cURLFull)
                        ? (Shop::getURL() . '/?a=' . $product->kArtikel . '&')
                        : ($product->cURLFull . $con);
                    \header('Location: ' . $url . 'n=' . $qty . '&r=' . \implode(',', $redirectParam), true, 302);
                }
                exit;
            }

            return false;
        }
        Frontend::getCart()
            ->fuegeEin(
                $productID,
                $qty,
                $attrValues,
                1,
                $unique,
                $configItemID,
                false,
                $responsibility
            )
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSANDPOS)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZAHLUNGSART)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);

        Kupon::resetNewCustomerCoupon(false);
        if ($setzePositionsPreise) {
            Frontend::getCart()->setzePositionsPreise();
        }
        unset(
            $_SESSION['VersandKupon'],
            $_SESSION['Versandart'],
            $_SESSION['Zahlungsart']
        );
        // Wenn Kupon vorhanden und der cWertTyp prozentual ist, dann verwerfen und neu anlegen
        Kupon::reCheck();
        if (!isset($_POST['login']) && !isset($_REQUEST['basket2Pers'])) {
            PersistentCart::addToCheck($productID, $qty, $attrValues, $unique, $configItemID);
        }
        Shop::Smarty()
            ->assign('cartNote', Shop::Lang()->get('basketAdded', 'messages'))
            ->assign('bWarenkorbHinzugefuegt', true)
            ->assign('bWarenkorbAnzahl', $qty);
        if (isset($_SESSION['Kampagnenbesucher'])) {
            Campaign::setCampaignAction(\KAMPAGNE_DEF_WARENKORB, $productID, $qty);
        }
        Frontend::getCart()->redirectTo((bool)$redirect, $unique);

        return true;
    }

    /**
     * @param array $items
     * @param bool  $removeShippingCoupon
     * @former loescheWarenkorbPositionen()
     * @since 5.0.0
     */
    public static function deleteCartItems(array $items, $removeShippingCoupon = true): void
    {
        $cart    = Frontend::getCart();
        $uniques = [];
        foreach ($items as $item) {
            if (!isset($cart->PositionenArr[$item])) {
                return;
            }
            if ($cart->PositionenArr[$item]->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL
                && $cart->PositionenArr[$item]->nPosTyp !== \C_WARENKORBPOS_TYP_GRATISGESCHENK
            ) {
                return;
            }
            $unique = $cart->PositionenArr[$item]->cUnique;
            if (!empty($unique) && $cart->PositionenArr[$item]->kKonfigitem > 0) {
                return;
            }
            \executeHook(\HOOK_WARENKORB_LOESCHE_POSITION, [
                'nPos'     => $item,
                'position' => &$cart->PositionenArr[$item]
            ]);

            Upload::deleteArtikelUploads($cart->PositionenArr[$item]->kArtikel);

            $uniques[] = $unique;

            unset($cart->PositionenArr[$item]);
        }
        $cart->PositionenArr = \array_merge($cart->PositionenArr);
        foreach ($uniques as $unique) {
            if (empty($unique)) {
                continue;
            }
            $itemCount = \count($cart->PositionenArr);
            /** @noinspection ForeachInvariantsInspection */
            for ($i = 0; $i < $itemCount; $i++) {
                if (isset($cart->PositionenArr[$i]->cUnique) && $cart->PositionenArr[$i]->cUnique === $unique) {
                    unset($cart->PositionenArr[$i]);
                    $cart->PositionenArr = \array_merge($cart->PositionenArr);
                    $i                   = -1;
                }
            }
        }
        self::deleteAllSpecialItems($removeShippingCoupon);
        if (!$cart->posTypEnthalten(\C_WARENKORBPOS_TYP_ARTIKEL)) {
            unset($_SESSION['Kupon']);
            $_SESSION['Warenkorb'] = new Cart();
        }
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';
        \freeGiftStillValid();
        if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']->kKunde > 0) {
            (new PersistentCart($_SESSION['Kunde']->kKunde))->entferneAlles()->bauePersVonSession();
        }
    }

    /**
     * @param int $index
     * @former loescheWarenkorbPosition()
     * @since 5.0.0
     */
    public static function deleteCartItem(int $index): void
    {
        self::deleteCartItems([$index]);
    }

    /**
     * @former uebernehmeWarenkorbAenderungen()
     * @since 5.0.0
     */
    public static function applyCartChanges(): void
    {
        /** @var array('Warenkorb' => Warenkorb) $_SESSION */
        unset($_SESSION['cPlausi_arr'], $_SESSION['cPost_arr']);
        // Gratis Geschenk wurde hinzugefuegt
        if (isset($_POST['gratishinzufuegen'])) {
            return;
        }
        // wurden Positionen gelöscht?
        $drop = null;
        $post = false;
        $cart = Frontend::getCart();
        if (Request::postVar('dropPos') === 'assetToUse') {
            $_SESSION['Bestellung']->GuthabenNutzen   = false;
            $_SESSION['Bestellung']->fGuthabenGenutzt = 0;
            unset($_POST['dropPos']);
        }
        if (isset($_POST['dropPos'])) {
            $drop = (int)$_POST['dropPos'];
            $post = true;
        } elseif (isset($_GET['dropPos'])) {
            $drop = (int)$_GET['dropPos'];
        }
        if ($drop !== null) {
            self::deleteCartItem($drop);
            \freeGiftStillValid();
            if ($post) {
                \header(
                    'Location: ' . Shop::Container()->getLinkService()->getStaticRoute(
                        'warenkorb.php',
                        true,
                        true
                    ),
                    true,
                    303
                );
                exit();
            }

            return;
        }
        //wurde WK aktualisiert?
        if (empty($_POST['anzahl'])) {
            return;
        }
        $updated     = false;
        $freeGiftID  = 0;
        $cartNotices = $_SESSION['Warenkorbhinweise'] ?? [];
        foreach ($cart->PositionenArr as $i => $item) {
            $item->kArtikel = (int)$item->kArtikel;
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
                if ($item->kArtikel === 0) {
                    continue;
                }
                //stückzahlen verändert?
                if (isset($_POST['anzahl'][$i])) {
                    $product = new Artikel();
                    $valid   = true;
                    $product->fuelleArtikel($item->kArtikel, Artikel::getDefaultOptions());
                    $quantity = (float)\str_replace(',', '.', $_POST['anzahl'][$i]);
                    if ($product->cTeilbar !== 'Y' && (int)$quantity !== $quantity) {
                        $quantity = \max((int)$quantity, 1);
                    }
                    if ($product->fAbnahmeintervall > 0 && !self::isMultiple($quantity, $product->fAbnahmeintervall)) {
                        $valid         = false;
                        $cartNotices[] = Shop::Lang()->get('wkPurchaseintervall', 'messages');
                    }
                    if ($quantity + $cart->gibAnzahlEinesArtikels(
                        $item->kArtikel,
                        $i
                    ) < $item->Artikel->fMindestbestellmenge) {
                        $valid         = false;
                        $cartNotices[] = \lang_mindestbestellmenge(
                            $item->Artikel,
                            $quantity
                        );
                    }
                    if ($product->cLagerBeachten === 'Y'
                        && $product->cLagerVariation !== 'Y'
                        && $product->cLagerKleinerNull !== 'Y'
                    ) {
                        foreach ($product->getAllDependentProducts(true) as $dependent) {
                            /** @var Artikel $depProduct */
                            $depProduct = $dependent->product;
                            if ($depProduct->fPackeinheit * ($quantity * $dependent->stockFactor
                                    + Frontend::getCart()->getDependentAmount(
                                        $depProduct->kArtikel,
                                        true,
                                        [$i]
                                    )) > $depProduct->fLagerbestand
                            ) {
                                $valid = false;
                                break;
                            }
                        }

                        if (!$valid) {
                            $msg = Shop::Lang()->get('quantityNotAvailable', 'messages');
                            if (!isset($cartNotices) || !\in_array($msg, $cartNotices, true)) {
                                $cartNotices[] = $msg;
                            }
                            $_SESSION['Warenkorb']->PositionenArr[$i]->nAnzahl =
                                $_SESSION['Warenkorb']->getMaxAvailableAmount($i, $quantity);
                        }
                    }
                    // maximale Bestellmenge des Artikels beachten
                    if (isset($product->FunktionsAttribute[\FKT_ATTRIBUT_MAXBESTELLMENGE])
                        && $product->FunktionsAttribute[\FKT_ATTRIBUT_MAXBESTELLMENGE] > 0
                        && $quantity > $product->FunktionsAttribute[\FKT_ATTRIBUT_MAXBESTELLMENGE]
                    ) {
                        $valid         = false;
                        $cartNotices[] = Shop::Lang()->get('wkMaxorderlimit', 'messages');
                    }
                    if ($product->cLagerBeachten === 'Y'
                        && $product->cLagerVariation === 'Y'
                        && $product->cLagerKleinerNull !== 'Y'
                        && \is_array($item->WarenkorbPosEigenschaftArr)
                    ) {
                        foreach ($item->WarenkorbPosEigenschaftArr as $eWert) {
                            $value = new EigenschaftWert($eWert->kEigenschaftWert);
                            if ($value->fPackeinheit * ($quantity +
                                    $cart->gibAnzahlEinerVariation(
                                        $item->kArtikel,
                                        $eWert->kEigenschaftWert,
                                        $i
                                    )) > $value->fLagerbestand
                            ) {
                                $cartNotices[] = Shop::Lang()->get(
                                    'quantityNotAvailableVar',
                                    'messages'
                                );
                                $valid         = false;
                                break;
                            }
                        }
                    }

                    if ($valid) {
                        $item->nAnzahl = $quantity;
                        $item->fPreis  = $product->gibPreis(
                            $item->nAnzahl,
                            $item->WarenkorbPosEigenschaftArr
                        );
                        $item->setzeGesamtpreisLocalized();
                        $item->fGesamtgewicht = $item->gibGesamtgewicht();

                        $updated = true;
                    }
                }
                // Grundpreise bei Staffelpreisen
                if (isset($item->Artikel->fVPEWert) && $item->Artikel->fVPEWert > 0) {
                    $nLast = 0;
                    for ($j = 1; $j <= 5; $j++) {
                        $cStaffel = 'nAnzahl' . $j;
                        if (isset($item->Artikel->Preise->$cStaffel)
                            && $item->Artikel->Preise->$cStaffel > 0
                            && $item->Artikel->Preise->$cStaffel <= $item->nAnzahl
                        ) {
                            $nLast = $j;
                        }
                    }
                    if ($nLast > 0) {
                        $cStaffel = 'fPreis' . $nLast;
                        $item->Artikel->baueVPE($item->Artikel->Preise->$cStaffel);
                    } else {
                        $item->Artikel->baueVPE();
                    }
                }
            } elseif ($item->nPosTyp === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $freeGiftID = $item->kArtikel;
            }
        }
        $_SESSION['Warenkorbhinweise'] = $cartNotices;
        //positionen mit nAnzahl = 0 müssen gelöscht werden
        $cart->loescheNullPositionen();
        if (!$cart->posTypEnthalten(\C_WARENKORBPOS_TYP_ARTIKEL)) {
            $_SESSION['Warenkorb'] = new Cart();
            $cart                  = $_SESSION['Warenkorb'];
        }
        if ($updated) {
            $tmpCoupon = null;
            //existiert ein proz. Kupon, der auf die neu eingefügte Pos greift?
            if (isset($_SESSION['Kupon'])
                && $_SESSION['Kupon']->cWertTyp === 'prozent'
                && (int)$_SESSION['Kupon']->nGanzenWKRabattieren === 0
                && $cart->gibGesamtsummeWarenExt(
                    [\C_WARENKORBPOS_TYP_ARTIKEL],
                    true
                ) >= $_SESSION['Kupon']->fMindestbestellwert
            ) {
                $tmpCoupon = $_SESSION['Kupon'];
            }
            self::deleteAllSpecialItems();
            /** @noinspection MissingIssetImplementationInspection */
            if (isset($tmpCoupon->kKupon) && $tmpCoupon->kKupon > 0) {
                $_SESSION['Kupon'] = $tmpCoupon;
                foreach ($cart->PositionenArr as $i => $oWKPosition) {
                    $cart->PositionenArr[$i] = self::checkCouponCartItems(
                        $oWKPosition,
                        $_SESSION['Kupon']
                    );
                }
            }
            \plausiNeukundenKupon();
        }
        $cart->setzePositionsPreise();
        // Gesamtsumme Warenkorb < Gratisgeschenk && Gratisgeschenk in den Pos?
        if ($freeGiftID > 0) {
            // Prüfen, ob der Artikel wirklich ein Gratis Geschenk ist
            $gift = Shop::Container()->getDB()->query(
                'SELECT kArtikel
                    FROM tartikelattribut
                    WHERE kArtikel = ' . $freeGiftID . "
                        AND cName = '" . \FKT_ATTRIBUT_GRATISGESCHENK . "'
                        AND CAST(cWert AS DECIMAL) <= " .
                $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true),
                ReturnType::SINGLE_OBJECT
            );

            if (empty($gift->kArtikel)) {
                $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_GRATISGESCHENK);
            }
        }
        if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
            $persCart = new PersistentCart($_SESSION['Kunde']->kKunde);
            $persCart->entferneAlles()
                           ->bauePersVonSession();
        }
    }

    /**
     * @return string
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     * @former checkeSchnellkauf()
     * @since 5.0.0
     */
    public static function checkQuickBuy(): string
    {
        $msg = '';
        if (empty($_POST['ean']) || Request::postInt('schnellkauf') <= 0) {
            return $msg;
        }
        $msg = Shop::Lang()->get('eanNotExist') . ' ' .
            Text::htmlentities(Text::filterXSS($_POST['ean']));
        //gibts artikel mit dieser artnr?
        $productData = Shop::Container()->getDB()->select(
            'tartikel',
            'cArtNr',
            Text::htmlentities(Text::filterXSS($_POST['ean']))
        );
        if (empty($productData->kArtikel)) {
            $productData = Shop::Container()->getDB()->select(
                'tartikel',
                'cBarcode',
                Text::htmlentities(Text::filterXSS($_POST['ean']))
            );
        }
        if (isset($productData->kArtikel) && $productData->kArtikel > 0) {
            $product = (new Artikel())->fuelleArtikel($productData->kArtikel, Artikel::getDefaultOptions());
            if ($product !== null
                && (int)$product->kArtikel > 0
                && self::addProductIDToCart(
                    $productData->kArtikel,
                    1,
                    Product::getSelectedPropertiesForArticle($productData->kArtikel)
                )
            ) {
                $msg = $productData->cName . ' ' . Shop::Lang()->get('productAddedToCart');
            }
        }

        return $msg;
    }

    /**
     * @param bool $removeShippingCoupon
     * @former loescheAlleSpezialPos()
     * @since 5.0.0
     */
    public static function deleteAllSpecialItems($removeShippingCoupon = true): void
    {
        Frontend::getCart()
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZAHLUNGSART)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSANDPOS)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERPACKUNG)
                ->checkIfCouponIsStillValid();
        unset(
            $_SESSION['Versandart'],
            $_SESSION['Verpackung'],
            $_SESSION['Zahlungsart']
        );
        if ($removeShippingCoupon) {
            unset(
                $_SESSION['VersandKupon'],
                $_SESSION['oVersandfreiKupon']
            );
        }
        Kupon::resetNewCustomerCoupon();
        Kupon::reCheck();

        \executeHook(\HOOK_WARENKORB_LOESCHE_ALLE_SPEZIAL_POS);

        Frontend::getCart()->setzePositionsPreise();
    }

    /**
     * @return stdClass
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     * @former gibXSelling()
     * @since 5.0.0
     */
    public static function getXSelling(): stdClass
    {
        $xSelling  = new stdClass();
        $conf      = Shop::getSettings([\CONF_KAUFABWICKLUNG]);
        $cartItems = Frontend::getCart()->PositionenArr;
        if ($conf['kaufabwicklung']['warenkorb_xselling_anzeigen'] !== 'Y'
            || !\is_array($cartItems)
            || \count($cartItems) === 0
        ) {
            return $xSelling;
        }
        $productIDs = map(
            filter($cartItems, static function ($p) {
                return isset($p->Artikel->kArtikel);
            }),
            static function ($p) {
                return (int)$p->Artikel->kArtikel;
            }
        );
        if (\count($productIDs) > 0) {
            $productIDs = \implode(', ', $productIDs);
            $xsellData  = Shop::Container()->getDB()->query(
                'SELECT DISTINCT kXSellArtikel
                    FROM txsellkauf
                    WHERE kArtikel IN (' . $productIDs . ')
                        AND kXSellArtikel NOT IN (' . $productIDs .')
                    ORDER BY nAnzahl DESC
                    LIMIT ' . (int)$conf['kaufabwicklung']['warenkorb_xselling_anzahl'],
                ReturnType::ARRAY_OF_OBJECTS
            );
            if (\count($xsellData) > 0) {
                $xSelling->Kauf          = new stdClass();
                $xSelling->Kauf->Artikel = [];
                $defaultOptions          = Artikel::getDefaultOptions();
                foreach ($xsellData as $item) {
                    $product = (new Artikel())->fuelleArtikel((int)$item->kXSellArtikel, $defaultOptions);
                    if ($product !== null
                        && (int)$product->kArtikel > 0
                        && $product->aufLagerSichtbarkeit()) {
                        $xSelling->Kauf->Artikel[] = $product;
                    }
                }
            }
        }

        return $xSelling;
    }

    /**
     * @param array $conf
     * @return array
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     * @former gibGratisGeschenke()
     * @since 5.0.0
     */
    public static function getFreeGifts(array $conf): array
    {
        $gifts = [];
        if ($conf['sonstiges']['sonstiges_gratisgeschenk_nutzen'] === 'Y') {
            $sqlSort = ' ORDER BY CAST(tartikelattribut.cWert AS DECIMAL) DESC';
            if ($conf['sonstiges']['sonstiges_gratisgeschenk_sortierung'] === 'N') {
                $sqlSort = ' ORDER BY tartikel.cName';
            } elseif ($conf['sonstiges']['sonstiges_gratisgeschenk_sortierung'] === 'L') {
                $sqlSort = ' ORDER BY tartikel.fLagerbestand DESC';
            }
            $limit    = $conf['sonstiges']['sonstiges_gratisgeschenk_anzahl'] > 0 ?
                    ' LIMIT ' . $conf['sonstiges']['sonstiges_gratisgeschenk_anzahl'] : '';
            $giftsTmp = Shop::Container()->getDB()->query(
                "SELECT tartikel.kArtikel, tartikelattribut.cWert
                    FROM tartikel
                    JOIN tartikelattribut
                        ON tartikelattribut.kArtikel = tartikel.kArtikel
                    WHERE (tartikel.fLagerbestand > 0 ||
                          (tartikel.fLagerbestand <= 0 &&
                          (tartikel.cLagerBeachten = 'N' || tartikel.cLagerKleinerNull = 'Y')))
                        AND tartikelattribut.cName = '" . \FKT_ATTRIBUT_GRATISGESCHENK . "'
                        AND CAST(tartikelattribut.cWert AS DECIMAL) <= " .
                Frontend::getCart()->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true) .
                $sqlSort . $limit,
                ReturnType::ARRAY_OF_OBJECTS
            );

            foreach ($giftsTmp as $gift) {
                $product = (new Artikel())->fuelleArtikel((int)$gift->kArtikel, Artikel::getDefaultOptions());
                if ($product !== null
                    && (int)$product->kArtikel > 0
                    && ($product->kEigenschaftKombi > 0
                        || !\is_array($product->Variationen)
                        || \count($product->Variationen) === 0)
                ) {
                    $product->cBestellwert = Preise::getLocalizedPriceString((float)$gift->cWert);
                    $gifts[]               = $product;
                }
            }
        }

        return $gifts;
    }

    /**
     * Schaut nach ob eine Bestellmenge > Lagersbestand ist und falls dies erlaubt ist, gibt es einen Hinweis
     *
     * @param array $conf
     * @return string
     * @former pruefeBestellMengeUndLagerbestand()
     * @since 5.0.0
     */
    public static function checkOrderAmountAndStock(array $conf = []): string
    {
        $cart     = Frontend::getCart();
        $notice   = '';
        $name     = '';
        $exists   = false;
        $langCode = Shop::getLanguageCode();
        if (\is_array($cart->PositionenArr) && \count($cart->PositionenArr) > 0) {
            foreach ($cart->PositionenArr as $item) {
                if ($item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                    && isset($item->Artikel)
                    && $item->Artikel->cLagerBeachten === 'Y'
                    && $item->Artikel->cLagerKleinerNull === 'Y'
                    && $conf['global']['global_lieferverzoegerung_anzeigen'] === 'Y'
                    && $item->nAnzahl > $item->Artikel->fLagerbestand
                ) {
                    $exists = true;
                    $name  .= '<li>' . (\is_array($item->cName) ? $item->cName[$langCode] : $item->cName) . '</li>';
                }
            }
        }
        $cart->cEstimatedDelivery = $cart->getEstimatedDeliveryTime();
        if ($exists) {
            $notice = \sprintf(Shop::Lang()->get('orderExpandInventory', 'basket'), '<ul>' . $name . '</ul>');
        }

        return $notice;
    }

    /**
     * Nachschauen ob beim Konfigartikel alle Pflichtkomponenten vorhanden sind, andernfalls löschen
     * @former validiereWarenkorbKonfig()
     * @since 5.0.0
     */
    public static function validateCartConfig(): void
    {
        Configurator::postcheckCart($_SESSION['Warenkorb']);
    }

    /**
     * @param float|string $quantity
     * @param float|string $multiple
     * @return bool
     */
    public static function isMultiple(float $quantity, float $multiple): bool
    {
        $eps      = 1E-10;
        $residual = $quantity / $multiple;

        return \abs($residual - \round($residual)) < $eps;
    }
}
