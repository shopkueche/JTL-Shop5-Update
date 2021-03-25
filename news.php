<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\URL;
use JTL\News\Category;
use JTL\News\Controller;
use JTL\News\Item;
use JTL\News\ViewType;
use JTL\Pagination\Pagination;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'news_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'seite_inc.php';

$NaviFilter       = Shop::run();
$params           = Shop::getParameters();
$db               = Shop::Container()->getDB();
$service          = Shop::Container()->getNewsService();
$pagination       = new Pagination();
$breadCrumbName   = null;
$breadCrumbURL    = null;
$cMetaTitle       = '';
$cMetaDescription = '';
$cMetaKeywords    = '';
$conf             = Shopsetting::getInstance()->getAll();
$customerGroupID  = Frontend::getCustomerGroup()->getID();
$linkService      = Shop::Container()->getLinkService();
$link             = $linkService->getPageLink($linkService->getSpecialPageID(LINKTYP_NEWS));
$controller       = new Controller($db, $conf, $smarty);
$alertHelper      = Shop::Container()->getAlertService();
$smarty           = Shop::Smarty();

switch ($controller->getPageType($params)) {
    case ViewType::NEWS_DETAIL:
        Shop::setPageType(PAGE_NEWSDETAIL);
        $pagination = new Pagination('comments');
        $newsItemID = $params['kNews'];
        $newsItem   = new Item($db);
        $newsItem->load($newsItemID);
        $newsItem->checkVisibility(Frontend::getCustomer()->getGroupID());

        $cMetaTitle       = $newsItem->getMetaTitle();
        $cMetaDescription = $newsItem->getMetaDescription();
        $cMetaKeywords    = $newsItem->getMetaKeyword();
        if ((int)($_POST['kommentar_einfuegen'] ?? 0) > 0 && Form::validateToken()) {
            $result = $controller->addComment($newsItemID, $_POST);
        }

        $controller->displayItem($newsItem, $pagination);

        $breadCrumbName = $newsItem->getTitle() ?? Shop::Lang()->get('news', 'breadcrumb');
        $breadCrumbURL  = URL::buildURL($newsItem, URLART_NEWS);

        executeHook(HOOK_NEWS_PAGE_DETAILANSICHT, [
            'newsItem'   => $newsItem,
            'pagination' => $pagination
        ]);
        break;
    case ViewType::NEWS_CATEGORY:
        Shop::setPageType(PAGE_NEWSKATEGORIE);
        $newsCategoryID = (int)$params['kNewsKategorie'];
        $overview       = $controller->displayOverview($pagination, $newsCategoryID, 0, $customerGroupID);
        $cCanonicalURL  = $overview->getURL();
        $breadCrumbURL  = $cCanonicalURL;
        $breadCrumbName = $overview->getName();
        $newsCategory   = new Category($db);
        $newsCategory->load($newsCategoryID);

        $cMetaTitle       = $newsCategory->getMetaTitle();
        $cMetaDescription = $newsCategory->getMetaDescription();
        $cMetaKeywords    = $newsCategory->getMetaKeyword();
        $smarty->assign('robotsContent', 'noindex, follow');
        break;
    case ViewType::NEWS_OVERVIEW:
        Shop::setPageType(PAGE_NEWS);
        $newsCategoryID = 0;
        $overview       = $controller->displayOverview($pagination, $newsCategoryID, 0, $customerGroupID);
        break;
    case ViewType::NEWS_MONTH_OVERVIEW:
        Shop::setPageType(PAGE_NEWSMONAT);
        $id             = (int)$params['kNewsMonatsUebersicht'];
        $overview       = $controller->displayOverview($pagination, 0, $id, $customerGroupID);
        $cCanonicalURL  = $overview->getURL();
        $breadCrumbURL  = $cCanonicalURL;
        $cMetaTitle     = $overview->getMetaTitle();
        $breadCrumbName = !empty($overview->getName()) ? $overview->getName() : $cMetaTitle;
        $smarty->assign('robotsContent', 'noindex, follow');
        break;
    case ViewType::NEWS_DISABLED:
    default:
        Shop::$is404 = true;
        Shop::$kLink = 0;
        Shop::$kNews = 0;

        return;
}

$cMetaTitle = JTL\Filter\Metadata::prepareMeta(
    $cMetaTitle,
    null,
    (int)$conf['metaangaben']['global_meta_maxlaenge_title']
);

if ($controller->getErrorMsg() !== '') {
    $alertHelper->addAlert(Alert::TYPE_ERROR, $controller->getErrorMsg(), 'newsError');
}
if ($controller->getNoticeMsg() !== '') {
    $alertHelper->addAlert(Alert::TYPE_NOTE, $controller->getNoticeMsg(), 'newsNote');
}

$smarty->assign('oPagination', $pagination)
    ->assign('code_news', false);

require_once PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
$smarty->display('blog/index.tpl');
require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
