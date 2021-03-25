<?php

use JTL\Alert\Alert;
use JTL\Backend\Revision;
use JTL\Boxes\Admin\BoxAdmin;
use JTL\Boxes\Type;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Link\LinkGroupInterface;
use JTL\Shop;
use function Functional\map;
use function Functional\reindex;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
/** @global \JTL\Backend\AdminAccount $oAccount */

$oAccount->permission('BOXES_VIEW', true, true);

$boxService  = Shop::Container()->getBoxService();
$alertHelper = Shop::Container()->getAlertService();
$boxAdmin    = new BoxAdmin(Shop::Container()->getDB());
$pageID      = Request::verifyGPCDataInt('page');
$linkID      = Request::verifyGPCDataInt('linkID');
$boxID       = Request::verifyGPCDataInt('item');
$ok          = false;

if (Request::postInt('einstellungen') > 0) {
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(CONF_BOXEN, $_POST),
        'saveSettings'
    );
} elseif (isset($_REQUEST['action']) && !isset($_REQUEST['revision-action']) && Form::validateToken()) {
    switch ($_REQUEST['action']) {
        case 'delete-invisible':
            if (!empty($_POST['kInvisibleBox']) && count($_POST['kInvisibleBox']) > 0) {
                $cnt = 0;
                foreach ($_POST['kInvisibleBox'] as $box) {
                    $ok = $boxAdmin->delete((int)$box);
                    if ($box) {
                        ++$cnt;
                    }
                }
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, $cnt . __('successBoxDelete'), 'successBoxDelete');
            }
            break;

        case 'new':
            $position    = $_REQUEST['position'];
            $containerID = $_REQUEST['container'] ?? 0;
            if ($boxID === 0) {
                // Neuer Container
                $ok = $boxAdmin->create(0, $pageID, $position);
                if ($ok) {
                    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successContainerCreate'), 'successContainerCreate');
                } else {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorContainerCreate'), 'errorContainerCreate');
                }
            } else {
                $ok = $boxAdmin->create($boxID, $pageID, $position, $containerID);
                if ($ok) {
                    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successBoxCreate'), 'successBoxCreate');
                } else {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorBoxCreate'), 'errorBoxCreate');
                }
            }
            break;

        case 'del':
            $ok = $boxAdmin->delete($boxID);
            if ($ok) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successBoxDelete'), 'successBoxDelete');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorBoxDelete'), 'errorBoxDelete');
            }
            break;

        case 'edit_mode':
            $oBox = $boxAdmin->getByID($boxID);
            // revisions need this as a different formatted array
            $revisionData = [];
            foreach ($oBox->oSprache_arr as $lang) {
                $revisionData[$lang->cISO] = $lang;
            }
            $links = Shop::Container()->getLinkService()->getAllLinkGroups()->filter(
                static function (LinkGroupInterface $e) {
                    return $e->isSpecial() === false;
                }
            );
            $smarty->assign('oEditBox', $oBox)
                ->assign('revisionData', $revisionData)
                ->assign('oLink_arr', $links);
            break;

        case 'edit':
            $cTitel = $_REQUEST['boxtitle'];
            $type   = $_REQUEST['typ'];
            if ($type === 'text') {
                $oldBox = $boxAdmin->getByID($boxID);
                if ($oldBox->supportsRevisions === true) {
                    $revision = new Revision(Shop::Container()->getDB());
                    $revision->addRevision('box', $boxID, true);
                }
                $ok = $boxAdmin->update($boxID, $cTitel);
                if ($ok) {
                    foreach ($_REQUEST['title'] as $cISO => $cTitel) {
                        $cInhalt = $_REQUEST['text'][$cISO];
                        $ok      = $boxAdmin->updateLanguage($boxID, $cISO, $cTitel, $cInhalt);
                        if (!$ok) {
                            break;
                        }
                    }
                }
            } elseif (($type === Type::LINK && $linkID > 0) || $type === Type::CATBOX) {
                $ok = $boxAdmin->update($boxID, $cTitel, $linkID);
                if ($ok) {
                    foreach ($_REQUEST['title'] as $cISO => $cTitel) {
                        $ok = $boxAdmin->updateLanguage($boxID, $cISO, $cTitel, '');
                        if (!$ok) {
                            break;
                        }
                    }
                }
            }

            if ($ok) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successBoxEdit'), 'successBoxEdit');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorBoxEdit'), 'errorBoxEdit');
            }
            break;

        case 'resort':
            $position = $_REQUEST['position'];
            $boxes    = array_map('\intval', $_REQUEST['box'] ?? []);
            $sort     = array_map('\intval', $_REQUEST['sort'] ?? []);
            $active   = array_map('\intval', $_REQUEST['aktiv'] ?? []);
            $ignore   = array_map('\intval', $_REQUEST['ignore'] ?? []);
            $boxCount = count($boxes);
            $show     = $_REQUEST['box_show'] ?? false;
            $ok       = $boxAdmin->setVisibility($pageID, $position, $show);
            foreach ($boxes as $i => $box) {
                $idx = 'box-filter-' . $box;
                $boxAdmin->sort($box, $pageID, $sort[$i], in_array($box, $active, true), in_array($box, $ignore, true));
                $boxAdmin->filterBoxVisibility((int)$box, $pageID, $_POST[$idx] ?? '');
            }
            // see jtlshop/jtl-shop/issues#544 && jtlshop/shop4#41
            if ($position !== 'left' || $pageID > 0) {
                $boxAdmin->setVisibility($pageID, $position, isset($_REQUEST['box_show']));
            }
            if ($ok) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successBoxRefresh'), 'successBoxRefresh');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorBoxesVisibilityEdit'), 'errorBoxesVisibilityEdit');
            }
            break;

        case 'activate':
            $bActive = (bool)$_REQUEST['value'];
            $ok      = $boxAdmin->activate($boxID, 0, $bActive);
            if ($ok) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successBoxEdit'), 'successBoxEdit');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorBoxEdit'), 'errorBoxEdit');
            }
            break;

        case 'container':
            $position = $_REQUEST['position'];
            $show     = (bool)$_GET['value'];
            $ok       = $boxAdmin->setVisibility(0, $position, $show);
            if ($ok) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successBoxEdit'), 'successBoxEdit');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorBoxEdit'), 'errorBoxEdit');
            }
            break;

        default:
            break;
    }
    $flushres = Shop::Container()->getCache()->flushTags([CACHING_GROUP_OBJECT, CACHING_GROUP_BOX, 'boxes']);
    Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);
}
$boxList       = $boxService->buildList($pageID, false);
$boxTemplates  = $boxAdmin->getTemplates($pageID);
$model         = Shop::Container()->getTemplateService()->getActiveTemplate();
$boxContainer  = $model->getBoxLayout();
$filterMapping = [];
if ($pageID === PAGE_ARTIKELLISTE) { //map category name
    $filterMapping = Shop::Container()->getDB()->query(
        'SELECT kKategorie AS id, cName AS name FROM tkategorie',
        ReturnType::ARRAY_OF_OBJECTS
    );
} elseif ($pageID === PAGE_ARTIKEL) { //map article name
    $filterMapping = Shop::Container()->getDB()->query(
        'SELECT kArtikel AS id, cName AS name FROM tartikel',
        ReturnType::ARRAY_OF_OBJECTS
    );
} elseif ($pageID === PAGE_HERSTELLER) { //map manufacturer name
    $filterMapping = Shop::Container()->getDB()->query(
        'SELECT kHersteller AS id, cName AS name FROM thersteller',
        ReturnType::ARRAY_OF_OBJECTS
    );
} elseif ($pageID === PAGE_EIGENE) { //map page name
    $filterMapping = Shop::Container()->getDB()->query(
        'SELECT kLink AS id, cName AS name FROM tlink',
        ReturnType::ARRAY_OF_OBJECTS
    );
}

$filterMapping = reindex($filterMapping, static function ($e) {
    return $e->id;
});
$filterMapping = map($filterMapping, static function ($e) {
    return $e->name;
});

$alertHelper->addAlert(
    Alert::TYPE_WARNING,
    __('warningNovaSidebar'),
    'warningNovaSidebar',
    ['dismissable' => false]
);

$smarty->assign('filterMapping', $filterMapping)
    ->assign('validPageTypes', $boxAdmin->getMappedValidPageTypes())
    ->assign('bBoxenAnzeigen', $boxAdmin->getVisibility($pageID))
    ->assign('oBoxenLeft_arr', $boxList['left'] ?? [])
    ->assign('oBoxenTop_arr', $boxList['top'] ?? [])
    ->assign('oBoxenBottom_arr', $boxList['bottom'] ?? [])
    ->assign('oBoxenRight_arr', $boxList['right'] ?? [])
    ->assign('oContainerTop_arr', $boxAdmin->getContainer('top'))
    ->assign('oContainerBottom_arr', $boxAdmin->getContainer('bottom'))
    ->assign('oVorlagen_arr', $boxTemplates)
    ->assign('oBoxenContainer', $boxContainer)
    ->assign('nPage', $pageID)
    ->assign('invisibleBoxes', $boxAdmin->getInvisibleBoxes())
    ->assign('oConfig_arr', getAdminSectionSettings(CONF_BOXEN))
    ->display('boxen.tpl');
