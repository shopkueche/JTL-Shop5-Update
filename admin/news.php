<?php

use JTL\Alert\Alert;
use JTL\ContentAuthor;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\News\Admin\Controller;
use JTL\News\Category;
use JTL\News\Comment;
use JTL\News\Item;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('CONTENT_NEWS_SYSTEM_VIEW', true, true);
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'news_inc.php';

$uploadDir      = PFAD_ROOT . PFAD_NEWSBILDER;
$uploadDirCat   = PFAD_ROOT . PFAD_NEWSKATEGORIEBILDER;
$newsCategories = [];
$db             = Shop::Container()->getDB();
$author         = ContentAuthor::getInstance();
$controller     = new Controller($db, $smarty, Shop::Container()->getCache());
$newsCategory   = new Category($db);
$languages      = LanguageHelper::getAllLanguages();
$adminID        = (int)$_SESSION['AdminAccount']->kAdminlogin;
$adminName      = $db->select('tadminlogin', 'kAdminlogin', $adminID)->cName;

if (mb_strlen(Request::verifyGPDataString('tab')) > 0) {
    $smarty->assign('files', []);

    switch (Request::verifyGPDataString('tab')) {
        case 'inaktiv':
            if (Request::verifyGPCDataInt('s1') > 1) {
                $smarty->assign('cBackPage', 'tab=inaktiv&s1=' . Request::verifyGPCDataInt('s1'))
                       ->assign('cSeite', Request::verifyGPCDataInt('s1'));
            }
            break;
        case 'aktiv':
            if (Request::verifyGPCDataInt('s2') > 1) {
                $smarty->assign('cBackPage', 'tab=aktiv&s2=' . Request::verifyGPCDataInt('s2'))
                       ->assign('cSeite', Request::verifyGPCDataInt('s2'));
            }
            break;
        case 'kategorien':
            if (Request::verifyGPCDataInt('s3') > 1) {
                $smarty->assign('cBackPage', 'tab=kategorien&s3=' . Request::verifyGPCDataInt('s3'))
                       ->assign('cSeite', Request::verifyGPCDataInt('s3'));
            }
            break;
    }
}
if (Request::verifyGPCDataInt('news') === 1 && Form::validateToken()) {
    if (Request::postInt('einstellungen') > 0) {
        $controller->setMsg(saveAdminSectionSettings(CONF_NEWS, $_POST, [CACHING_GROUP_OPTION, CACHING_GROUP_NEWS]));
        if (count($languages) > 0) {
            $db->query('TRUNCATE tnewsmonatspraefix', ReturnType::AFFECTED_ROWS);
            foreach ($languages as $lang) {
                $monthPrefix           = new stdClass();
                $monthPrefix->kSprache = $lang->getId();
                if (mb_strlen($_POST['praefix_' . $lang->getIso()]) > 0) {
                    $monthPrefix->cPraefix = htmlspecialchars(
                        $_POST['praefix_' . $lang->getIso()],
                        ENT_COMPAT | ENT_HTML401,
                        JTL_CHARSET
                    );
                } else {
                    $monthPrefix->cPraefix = $lang->getIso() === 'ger'
                        ? 'Newsuebersicht'
                        : 'Newsoverview';
                }
                $db->insert('tnewsmonatspraefix', $monthPrefix);
            }
        }
    } elseif ((isset($_POST['erstellen'], $_POST['news_erstellen']) && (int)$_POST['erstellen'] === 1)
        || Request::postInt('news_erstellen') === 1
    ) {
        $newsCategories = $controller->getAllNewsCategories();
        if (count($newsCategories) > 0) {
            $newsItem = new Item($db);
            $controller->setStep('news_erstellen');
            $smarty->assign('oNewsKategorie_arr', $newsCategories)
                   ->assign('oNews', $newsItem)
                   ->assign('oPossibleAuthors_arr', $author->getPossibleAuthors(['CONTENT_NEWS_SYSTEM_VIEW']));
        } else {
            $controller->setErrorMsg(__('errorNewsCatFirst'));
            $controller->setStep('news_uebersicht');
        }
    } elseif ((isset($_POST['erstellen'], $_POST['news_kategorie_erstellen']) && (int)$_POST['erstellen'] === 1)
        || Request::postInt('news_kategorie_erstellen') === 1
    ) {
        $controller->setStep('news_kategorie_erstellen');
    } elseif (Request::verifyGPCDataInt('nkedit') === 1 && Request::verifyGPCDataInt('kNews') > 0) {
        if (isset($_POST['newskommentarsavesubmit'])) {
            if ($controller->saveComment(Request::verifyGPCDataInt('kNewsKommentar'), $_POST)) {
                $controller->setStep('news_vorschau');
                $controller->setMsg(__('successNewsCommmentEdit'));

                if (Request::verifyGPCDataInt('nFZ') === 1) {
                    header('Location: freischalten.php');
                    exit();
                }
                $tab = Request::verifyGPDataString('tab');
                if ($tab === 'aktiv') {
                    $controller->newsRedirect(empty($tab) ? 'inaktiv' : $tab, $controller->getMsg(), [
                        'news'  => '1',
                        'nd'    => '1',
                        'kNews' => Request::verifyGPCDataInt('kNews'),
                        'token' => $_SESSION['jtl_token'],
                    ]);
                } else {
                    $controller->newsRedirect(empty($tab) ? 'inaktiv' : $tab, $controller->getMsg());
                }
            } else {
                $controller->setStep('news_kommentar_editieren');
                $controller->setErrorMsg(__('errorCheckInput'));
                $comment                 = new stdClass();
                $comment->kNewsKommentar = $_POST['kNewsKommentar'];
                $comment->kNews          = $_POST['kNews'];
                $comment->cName          = $_POST['cName'];
                $comment->cKommentar     = $_POST['cKommentar'];
                $smarty->assign('oNewsKommentar', $comment);
            }
        } else {
            $controller->setStep('news_kommentar_editieren');
            $comment = new Comment($db);
            $comment->load(Request::verifyGPCDataInt('kNewsKommentar'));
            $smarty->assign('oNewsKommentar', $comment);
            if (Request::verifyGPCDataInt('nFZ') === 1) {
                $smarty->assign('nFZ', 1);
            }
        }
    } elseif (Request::verifyGPCDataInt('nkanswer') === 1 && Request::verifyGPCDataInt('kNews') > 0) {
        $controller->setStep('news_kommentar_antwort_editieren');
        $comment         = new Comment($db);
        $parentCommentID = Request::verifyGPCDataInt('parentCommentID');
        if (empty($comment->loadByParentCommentID($parentCommentID))) {
            $comment->setID(0);
            $comment->setNewsID(Request::verifyGPCDataInt('kNews'));
            $comment->setCustomerID(0);
            $comment->setIsActive(1);
            $comment->setName($adminName);
            $comment->setMail('');
            $comment->setText('');
            $comment->setIsAdmin($adminID);
            $comment->setParentCommentID($parentCommentID);
        }
        $smarty->assign('oNewsKommentar', $comment);
    } elseif (Request::postInt('news_speichern') === 1) {
        $controller->createOrUpdateNewsItem($_POST, $languages, $author);
    } elseif (Request::postInt('news_loeschen') === 1) {
        if (GeneralObject::hasCount('kNews', $_POST)) {
            $controller->deleteNewsItems($_POST['kNews'], $author);
            $controller->setMsg(__('successNewsDelete'));
            $controller->newsRedirect('aktiv', $controller->getMsg());
        } else {
            $controller->setErrorMsg(__('errorAtLeastOneNews'));
        }
    } elseif (Request::postInt('news_kategorie_speichern') === 1) {
        $newsCategory = $controller->createOrUpdateCategory($_POST, $languages);
    } elseif (Request::postInt('news_kategorie_loeschen') === 1) {
        $controller->setStep('news_uebersicht');
        if (isset($_POST['kNewsKategorie'])) {
            $controller->deleteCategories($_POST['kNewsKategorie']);
            $controller->setMsg(__('successNewsCatDelete'));
            $controller->newsRedirect('kategorien', $controller->getMsg());
        } else {
            $controller->setErrorMsg(__('errorAtLeastOneNewsCat'));
        }
    } elseif (Request::getInt('newskategorie_editieren') === 1) {
        if (mb_strlen(Request::verifyGPDataString('delpic')) > 0) {
            if ($controller->deleteNewsImage(
                Request::verifyGPDataString('delpic'),
                Request::getInt('kNewsKategorie'),
                $uploadDirCat
            )) {
                $controller->setMsg(__('successNewsImageDelete'));
            } else {
                $controller->setErrorMsg(__('errorNewsImageDelete'));
            }
        }
        if (Request::getInt('kNewsKategorie') > 0) {
            $controller->setStep('news_kategorie_erstellen');
            $newsCategory->load(Request::getInt('kNewsKategorie'), false);
            if ($newsCategory->getID() > 0) {
                $smarty->assign('oNewsKategorie', $newsCategory)
                       ->assign('files', $controller->getCategoryImages($newsCategory->getID(), $uploadDirCat));
            } else {
                $controller->setStep('news_uebersicht');
                $controller->setErrorMsg(sprintf(__('errorNewsCatNotFound'), Request::getInt('kNewsKategorie')));
            }
        }
    } elseif (Request::postInt('newskommentar_freischalten') > 0 && !isset($_POST['kommentareloeschenSubmit'])) {
        $deleteIDs = Request::verifyGPDataIntegerArray('kNewsKommentar');
        if (count($deleteIDs) > 0) {
            foreach ($deleteIDs as $id) {
                $db->update('tnewskommentar', 'kNewsKommentar', $id, (object)['nAktiv' => 1]);
            }
            $controller->setMsg(__('successNewsCommentUnlock'));
            $tab = Request::verifyGPDataString('tab');
            $controller->newsRedirect(empty($tab) ? 'inaktiv' : $tab, $controller->getMsg());
        } else {
            $controller->setErrorMsg(__('errorAtLeastOneNewsComment'));
            $controller->newsRedirect('', $controller->getErrorMsg());
        }
    } elseif (isset(
        $_POST['newskommentar_freischalten'],
        $_POST['kNewsKommentar'],
        $_POST['kommentareloeschenSubmit']
    )) {
        $controller->deleteComments($_POST['kNewsKommentar']);
    }
    if (Request::getInt('news_editieren') === 1 || $controller->getContinueWith() > 0) {
        $newsCategories = $controller->getAllNewsCategories();
        $newsItemID     = $controller->getContinueWith() > 0
            ? $controller->getContinueWith()
            : Request::getInt('kNews');
        if (mb_strlen(Request::verifyGPDataString('delpic')) > 0) {
            if ($controller->deleteNewsImage(Request::verifyGPDataString('delpic'), $newsItemID, $uploadDir)) {
                $controller->setMsg(__('successNewsImageDelete'));
            } else {
                $controller->setErrorMsg(__('errorNewsImageDelete'));
            }
        }

        if ($newsItemID > 0 && count($newsCategories) > 0) {
            $smarty->assign('oNewsKategorie_arr', $controller->getAllNewsCategories())
                   ->assign('oAuthor', $author->getAuthor('NEWS', $newsItemID))
                   ->assign('oPossibleAuthors_arr', $author->getPossibleAuthors(['CONTENT_NEWS_SYSTEM_VIEW']));
            $controller->setStep('news_editieren');
            $newsItem = new Item($db);
            $newsItem->load($newsItemID);

            if ($newsItem->getID() > 0) {
                $smarty->assign('oNewsKategorie_arr', $controller->getAllNewsCategories())
                       ->assign('files', $controller->getNewsImages($newsItem->getID(), $uploadDir))
                       ->assign('oNews', $newsItem);
            }
        } else {
            $controller->setErrorMsg(__('errorNewsCatFirst'));
            $controller->setStep('news_uebersicht');
        }
    } elseif ($controller->getStep() === 'news_vorschau' || Request::verifyGPCDataInt('nd') === 1) {
        $controller->setStep('news_vorschau');
        $newsItemID = Request::verifyGPCDataInt('kNews');
        $newsItem   = new Item($db);
        $newsItem->load($newsItemID);

        if ($newsItem->getID() > 0) {
            if (Request::postInt('kommentare_loeschen') === 1 || isset($_POST['kommentareloeschenSubmit'])) {
                $controller->deleteComments($_POST['kNewsKommentar'] ?? [], $newsItem);
            }
            $smarty->assign('oNews', $newsItem)
                   ->assign('files', $controller->getNewsImages($newsItem->getID(), $uploadDir))
                   ->assign('comments', $newsItem->getComments()->getThreadedItems());
        }
    }
}
if ($controller->getStep() === 'news_uebersicht') {
    $newsItems = $controller->getAllNews();
    $comments  = $controller->getNonActivatedComments();
    $prefixes  = [];
    foreach ($languages as $i => $lang) {
        $item                = new stdClass();
        $item->kSprache      = $lang->getId();
        $item->cNameEnglisch = $lang->getNameEN();
        $item->cNameDeutsch  = $lang->getNameDE();
        $item->name          = $lang->getLocalizedName();
        $item->cISOSprache   = $lang->getIso();
        $monthPrefix         = $db->select(
            'tnewsmonatspraefix',
            'kSprache',
            (int)$lang->kSprache
        );
        $item->cPraefix      = $monthPrefix->cPraefix ?? null;
        $prefixes[$i]        = $item;
    }
    $newsCategories     = $controller->getAllNewsCategories();
    $commentPagination  = (new Pagination('kommentar'))
        ->setItemArray($comments)
        ->assemble();
    $itemPagination     = (new Pagination('news'))
        ->setItemArray($newsItems)
        ->assemble();
    $categoryPagination = (new Pagination('kats'))
        ->setItemArray($newsCategories)
        ->assemble();
    $smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_NEWS))
           ->assign('comments', $commentPagination->getPageItems())
           ->assign('oNews_arr', $itemPagination->getPageItems())
           ->assign('oNewsKategorie_arr', $categoryPagination->getPageItems())
           ->assign('oNewsMonatsPraefix_arr', $prefixes)
           ->assign('oPagiKommentar', $commentPagination)
           ->assign('oPagiNews', $itemPagination)
           ->assign('oPagiKats', $categoryPagination);
} elseif ($controller->getStep() === 'news_kategorie_erstellen') {
    $smarty->assign('oNewsKategorie_arr', $controller->getAllNewsCategories())
           ->assign('oNewsKategorie', $newsCategory);
}

if (!empty($_SESSION['news.cHinweis'])) {
    $controller->setMsg($controller->getMsg() . $_SESSION['news.cHinweis']);
    unset($_SESSION['news.cHinweis']);
}

$maxFileSize = getMaxFileSize(ini_get('upload_max_filesize'));
Shop::Container()->getAlertService()->addAlert(Alert::TYPE_NOTE, $controller->getMsg(), 'newsMessage');
Shop::Container()->getAlertService()->addAlert(Alert::TYPE_ERROR, $controller->getErrorMsg(), 'newsError');

$smarty->assign('customerGroups', CustomerGroup::getGroups())
       ->assign('step', $controller->getStep())
       ->assign('nMaxFileSize', $maxFileSize)
       ->display('news.tpl');
