<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Cart\CartHelper;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\Helpers\Form;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Campaign;
use JTL\Pagination\Pagination;
use JTL\Session\Frontend;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'wunschliste_inc.php';

Shop::run();
$conf             = Shop::getSettings([CONF_GLOBAL, CONF_RSS]);
$params           = Shop::getParameters();
$cURLID           = Text::filterXSS(Request::verifyGPDataString('wlid'));
$kWunschliste     = (Request::verifyGPCDataInt('wl') > 0 && Request::verifyGPCDataInt('wlvm') === 0)
    ? Request::verifyGPCDataInt('wl') //one of multiple customer wishlists
    : ($params['kWunschliste'] //default wishlist from Shop class
        ?? $cURLID); //public link
$wishlistTargetID = Request::verifyGPCDataInt('kWunschlisteTarget');
$searchQuery      = Text::filterXSS(Request::verifyGPDataString('cSuche'));
$step             = null;
$wishlist         = null;
$action           = null;
$kWunschlistePos  = null;
$wishlists        = [];
$linkHelper       = Shop::Container()->getLinkService();
$customerID       = Frontend::getCustomer()->getID();
$alertHelper      = Shop::Container()->getAlertService();

if ($kWunschliste === 0 && $customerID > 0 && empty($_SESSION['Wunschliste']->kWunschliste)) {
    $_SESSION['Wunschliste'] = new Wishlist();
    $_SESSION['Wunschliste']->schreibeDB();
    $kWunschliste = (int)$_SESSION['Wunschliste']->kWunschliste;
}

Shop::setPageType(PAGE_WUNSCHLISTE);
if (!empty($_POST['addToCart'])) {
    $action          = 'addToCart';
    $kWunschlistePos = (int)$_POST['addToCart'];
} elseif (!empty($_POST['remove'])) {
    $action          = 'remove';
    $kWunschlistePos = (int)$_POST['remove'];
} elseif (isset($_POST['action'])) {
    $action = $_POST['action'];
}
if ($action !== null && Form::validateToken()) {
    if (isset($_POST['kWunschliste'])) {
        $kWunschliste = (int)$_POST['kWunschliste'];
        $wl           = Wishlist::instanceByID($kWunschliste)->filterPositions($searchQuery);
        switch ($action) {
            case 'addToCart':
                $wishlistPosition = Wishlist::getWishListPositionDataByID($kWunschlistePos);
                if (isset($wishlistPosition->kArtikel) && $wishlistPosition->kArtikel > 0
                    && (int)$wishlistPosition->kWunschliste === $wl->kWunschliste
                ) {
                    $attributeValues = Product::isVariChild($wishlistPosition->kArtikel)
                        ? Product::getVarCombiAttributeValues($wishlistPosition->kArtikel)
                        : Wishlist::getAttributesByID($kWunschliste, $wishlistPosition->kWunschlistePos);
                    if (!$wishlistPosition->bKonfig) {
                        CartHelper::addProductIDToCart(
                            $wishlistPosition->kArtikel,
                            $wishlistPosition->fAnzahl,
                            $attributeValues
                        );
                    }
                    $alertHelper->addAlert(
                        Alert::TYPE_NOTE,
                        Shop::Lang()->get('basketAdded', 'messages'),
                        'basketAdded'
                    );
                }
                break;

            case 'sendViaMail':
                if ($wl->cURLID !== '' && $wl->nOeffentlich && $wl->isSelfControlled()) {
                    $step = 'wunschliste anzeigen';
                    if (Request::postInt('send') === 1) {
                        if ($conf['global']['global_wunschliste_anzeigen'] === 'Y') {
                            $mails = explode(' ', Text::filterXSS($_POST['email']));
                            $alertHelper->addAlert(
                                Alert::TYPE_NOTE,
                                Wishlist::send($mails, $kWunschliste),
                                'sendWL'
                            );
                            $wishlist = Wishlist::buildPrice(Wishlist::instanceByID($kWunschliste));
                        }
                    } else {
                        $step = 'wunschliste versenden';
                        // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
                        $wishlist = Wishlist::buildPrice(Wishlist::instanceByID($kWunschliste));
                    }
                }
                break;

            case 'addAllToCart':
                if (count($wl->CWunschlistePos_arr) > 0) {
                    foreach ($wl->CWunschlistePos_arr as $wishlistPosition) {
                        $attributeValues = Product::isVariChild($wishlistPosition->kArtikel)
                            ? Product::getVarCombiAttributeValues($wishlistPosition->kArtikel)
                            : Wishlist::getAttributesByID($kWunschliste, (int)$wishlistPosition->kWunschlistePos);
                        if (!$wishlistPosition->Artikel->bHasKonfig && empty($wishlistPosition->bKonfig)
                            && isset($wishlistPosition->Artikel->inWarenkorbLegbar)
                            && $wishlistPosition->Artikel->inWarenkorbLegbar > 0
                        ) {
                            CartHelper::addProductIDToCart(
                                $wishlistPosition->kArtikel,
                                $wishlistPosition->fAnzahl,
                                $attributeValues
                            );
                        }
                    }
                    $alertHelper->addAlert(
                        Alert::TYPE_NOTE,
                        Shop::Lang()->get('basketAllAdded', 'messages'),
                        'basketAllAdded'
                    );
                }
                break;

            case 'remove':
                if ($kWunschlistePos > 0 && $wl->isSelfControlled()) {
                    $wl->entfernePos($kWunschlistePos);
                    $alertHelper->addAlert(
                        Alert::TYPE_NOTE,
                        Shop::Lang()->get('wishlistUpdate', 'messages'),
                        'wishlistUpdate'
                    );
                }
                break;

            case 'removeAll':
                if ($wl->isSelfControlled()) {
                    $wl->entferneAllePos();
                    if ((int)$_SESSION['Wunschliste']->kWunschliste === $wl->kWunschliste) {
                        $_SESSION['Wunschliste']->CWunschlistePos_arr = [];
                    }
                    $alertHelper->addAlert(
                        Alert::TYPE_NOTE,
                        Shop::Lang()->get('wishlistDelAll', 'messages'),
                        'wishlistDelAll'
                    );
                }
                break;

            case 'update':
                if ($wl->isSelfControlled()) {
                    $alertHelper->addAlert(
                        Alert::TYPE_NOTE,
                        Wishlist::update($kWunschliste),
                        'updateWL'
                    );
                    $wishlist                = Wishlist::buildPrice(Wishlist::instanceByID($kWunschliste));
                    $_SESSION['Wunschliste'] = $wishlist;
                }
                break;

            case 'setPublic':
                if ($wishlistTargetID !== 0 && Wishlist::instanceByID($wishlistTargetID)->isSelfControlled()) {
                    Wishlist::setPublic($wishlistTargetID);
                    $alertHelper->addAlert(
                        Alert::TYPE_NOTE,
                        Shop::Lang()->get('wishlistSetPublic', 'messages'),
                        'wishlistSetPublic'
                    );
                }
                break;

            case 'setPrivate':
                if ($wishlistTargetID !== 0 && Wishlist::instanceByID($wishlistTargetID)->isSelfControlled()) {
                    Wishlist::setPrivate($wishlistTargetID);
                    $alertHelper->addAlert(
                        Alert::TYPE_NOTE,
                        Shop::Lang()->get('wishlistSetPrivate', 'messages'),
                        'wishlistSetPrivate'
                    );
                }
                break;

            case 'createNew':
                $CWunschlisteName = Text::htmlentities(Text::filterXSS($_POST['cWunschlisteName']));
                $alertHelper->addAlert(
                    Alert::TYPE_NOTE,
                    Wishlist::save($CWunschlisteName),
                    'saveWL'
                );
                break;

            case 'delete':
                if ($wishlistTargetID !== 0 && Wishlist::instanceByID($wishlistTargetID)->isSelfControlled()) {
                    $alertHelper->addAlert(
                        Alert::TYPE_NOTE,
                        Wishlist::delete($wishlistTargetID),
                        'deleteWL'
                    );
                    if ($wishlistTargetID === $kWunschliste) {
                        // the currently active one was deleted, search for a new one
                        $newWishlist = Wishlist::getWishlists()->first();
                        if (isset($newWishlist->kWunschliste)) {
                            $kWunschliste = (int)$newWishlist->kWunschliste;
                            $alertHelper->addAlert(
                                Alert::TYPE_NOTE,
                                Wishlist::setDefault($kWunschliste),
                                'setDefaultWL'
                            );
                            $wishlist = new Wishlist($kWunschliste);
                        } elseif (empty($_SESSION['Wunschliste']->kWunschliste)) {
                            // the only existing wishlist was deleted, create a new one
                            $wishlist = new Wishlist();
                            $wishlist->schreibeDB();
                            $kWunschliste = $wishlist->kWunschliste;
                        }

                        $_SESSION['Wunschliste'] = $wishlist;
                    }
                }
                break;

            case 'setAsDefault':
                if ($wishlistTargetID !== 0 && Wishlist::instanceByID($wishlistTargetID)->isSelfControlled()) {
                    $alertHelper->addAlert(
                        Alert::TYPE_NOTE,
                        Wishlist::setDefault($wishlistTargetID),
                        'setDefaultWL'
                    );
                    $kWunschliste = $wishlistTargetID;
                }
                break;

            case 'search':
            default:
                $wishlist = $wl;
                break;
        }
    } elseif ($action === 'search' && $kWunschliste > 0) {
        $wishlist = Wishlist::instanceByID($kWunschliste)->filterPositions($searchQuery);
    }
}

if (Request::verifyGPCDataInt('wlidmsg') > 0) {
    $alertHelper->addAlert(
        Alert::TYPE_NOTE,
        Wishlist::mapMessage(Request::verifyGPCDataInt('wlidmsg')),
        'wlidmsg'
    );
}
if (Request::verifyGPCDataInt('error') === 1) {
    if (mb_strlen($cURLID) > 0) {
        $wl = Wishlist::instanceByURLID($cURLID);
        if ($wl->nOeffentlich !== 1) {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                sprintf(Shop::Lang()->get('nowlidWishlist', 'messages'), $cURLID),
                'nowlidWishlist',
                ['saveInSession' => true]
            );
        }
    } else {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            sprintf(Shop::Lang()->get('nowlidWishlist', 'messages'), $cURLID),
            'nowlidWishlist',
            ['saveInSession' => true]
        );
    }
} elseif (!$kWunschliste) {
    if ($customerID > 0) {
        $wishlist     = Wishlist::buildPrice(Wishlist::instanceByCustomerID($customerID));
        $kWunschliste = $wishlist->kWunschliste;
    }
    if (!$kWunschliste) {
        header('Location: ' . $linkHelper->getStaticRoute('jtl.php') . '&r=' . R_LOGIN_WUNSCHLISTE);
        exit;
    }
}
$link = ($params['kLink'] > 0) ? $linkHelper->getPageLink($params['kLink']) : null;
if ($wishlist === null) {
    $wishlist = Wishlist::buildPrice(Wishlist::instanceByID($kWunschliste)->filterPositions($searchQuery));
}
if ($customerID > 0) {
    $wishlists = Wishlist::getWishlists()->toArray();
} elseif ($wishlist->kWunschliste === 0) {
    header('Location: ' . $linkHelper->getStaticRoute('jtl.php') . '&r=' . R_LOGIN_WUNSCHLISTE);
    exit;
}

$pagination = (new Pagination())
    ->setItemArray($wishlist->CWunschlistePos_arr)
    ->setItemCount(count($wishlist->CWunschlistePos_arr))
    ->assemble();

Shop::Smarty()->assign('CWunschliste', $wishlist)
    ->assign('pagination', $pagination)
    ->assign('wishlistItems', $pagination->getPageItems())
    ->assign('oWunschliste_arr', $wishlists)
    ->assign('newWL', Request::verifyGPCDataInt('newWL'))
    ->assign('wlsearch', $searchQuery)
    ->assign('Link', $link)
    ->assign('hasItems', !empty($wishlist->CWunschlistePos_arr))
    ->assign('isCurrenctCustomer', isset($wishlist->kKunde) && (int)$wishlist->kKunde === $customerID)
    ->assign('cURLID', $cURLID)
    ->assign('step', $step);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

if (isset($wishlist->kWunschliste) && $wishlist->kWunschliste > 0) {
    $campaign = new Campaign(KAMPAGNE_INTERN_OEFFENTL_WUNSCHZETTEL);
    if (isset($campaign->kKampagne, $campaign->cWert)
        && mb_convert_case($campaign->cWert, MB_CASE_LOWER) ===
        strtolower(Request::verifyGPDataString($campaign->cParameter))
    ) {
        $event               = new stdClass();
        $event->kKampagne    = $campaign->kKampagne;
        $event->kKampagneDef = KAMPAGNE_DEF_HIT;
        $event->kKey         = $_SESSION['oBesucher']->kBesucher;
        $event->fWert        = 1.0;
        $event->cParamWert   = $campaign->cWert;
        $event->dErstellt    = 'NOW()';

        Shop::Container()->getDB()->insert('tkampagnevorgang', $event);
        $_SESSION['Kampagnenbesucher'] = $campaign;
    }
}

Shop::Smarty()->display('snippets/wishlist.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
