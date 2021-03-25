<?php

use Illuminate\Support\Collection;
use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Link\Admin\LinkAdmin;
use JTL\Link\Link;
use JTL\Link\LinkGroup;
use JTL\Link\LinkGroupList;
use JTL\Link\LinkInterface;
use JTL\PlausiCMS;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('CONTENT_PAGE_VIEW', true, true);
$step        = 'uebersicht';
$link        = null;
$uploadDir   = PFAD_ROOT . PFAD_BILDER . PFAD_LINKBILDER;
$clearCache  = false;
$continue    = false;
$db          = Shop::Container()->getDB();
$cache       = Shop::Container()->getCache();
$linkAdmin   = new LinkAdmin($db, $cache);
$alertHelper = Shop::Container()->getAlertService();
$action      = Request::verifyGPDataString('action');
$linkID      = Request::verifyGPCDataInt('kLink');
$linkGroupID = Request::verifyGPCDataInt('kLinkgruppe');
if ($action !== '' && Form::validateToken()) {
    switch ($action) {
        case 'add-link-to-linkgroup':
            $step = 'neuer Link';
            $link = new Link($db);
            $link->setLinkGroupID($linkGroupID);
            $link->setLinkGroups([$linkGroupID]);
            break;
        case 'remove-link-from-linkgroup':
            $res = $linkAdmin->removeLinkFromLinkGroup($linkID, $linkGroupID);
            if ($res > 0) {
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    __('successLinkFromLinkGroupDelete'),
                    'successLinkFromLinkGroupDelete'
                );
            } else {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorLinkFromLinkGroupDelete'),
                    'errorLinkFromLinkGroupDelete'
                );
            }
            unset($_POST['kLinkgruppe']);
            $step       = 'uebersicht';
            $clearCache = true;
            break;
        case 'delete-link':
            if ($linkAdmin->deleteLink($linkID) > 0) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successLinkDelete'), 'successLinkDelete');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkDelete'), 'errorLinkDelete');
            }
            $clearCache = true;
            $step       = 'uebersicht';
            $_POST      = [];
            break;
        case 'confirm-delete':
            if (Request::verifyGPCDataInt('confirmation') === 1) {
                $step = 'loesch_linkgruppe';
            } else {
                $step  = 'uebersicht';
                $_POST = [];
            }
            break;
        case 'delete-linkgroup':
            $step  = 'linkgruppe_loeschen_confirm';
            $group = new LinkGroup($db);
            $smarty->assign('linkGroup', $group->load($linkGroupID))
                   ->assign('affectedLinkNames', $linkAdmin->getPreDeletionLinks($linkGroupID));
            break;
        case 'edit-linkgroup':
        case 'create-linkgroup':
            $step      = 'neue Linkgruppe';
            $linkGroup = null;
            if ($linkGroupID > 0) {
                $linkGroup = new LinkGroup($db);
                $linkGroup = $linkGroup->load($linkGroupID);
            }
            $smarty->assign('linkGroup', $linkGroup);
            break;
        case 'save-linkgroup':
            $checks = new PlausiCMS();
            $checks->setPostVar($_POST);
            $checks->doPlausi('grp');
            if (count($checks->getPlausiVar()) === 0) {
                $linkGroupTemplateExists = Shop::Container()->getDB()->select(
                    'tlinkgruppe',
                    'cTemplatename',
                    $_POST['cTemplatename']
                );
                if ($linkGroupTemplateExists !== null && $linkGroupID !== (int)$linkGroupTemplateExists->kLinkgruppe) {
                    $step      = 'neue Linkgruppe';
                    $linkGroup = null;
                    if ($linkGroupID > 0) {
                        $linkGroup = new LinkGroup($db);
                        $linkGroup = $linkGroup->load($linkGroupID);
                    }
                    $alertHelper->addAlert(
                        Alert::TYPE_ERROR,
                        __('errorTemplateNameDuplicate'),
                        'errorTemplateNameDuplicate'
                    );
                    $smarty->assign('xPlausiVar_arr', $checks->getPlausiVar())
                           ->assign('xPostVar_arr', $checks->getPostVar())
                           ->assign('linkGroup', $linkGroup);
                } else {
                    if ($linkGroupID === 0) {
                        $linkAdmin->createOrUpdateLinkGroup(0, $_POST);
                        $alertHelper->addAlert(
                            Alert::TYPE_SUCCESS,
                            __('successLinkGroupCreate'),
                            'successLinkGroupCreate'
                        );
                    } else {
                        $linkgruppe = $linkAdmin->createOrUpdateLinkGroup($linkGroupID, $_POST);
                        $alertHelper->addAlert(
                            Alert::TYPE_SUCCESS,
                            sprintf(__('successLinkGroupEdit'), $linkgruppe->cName),
                            'successLinkGroupEdit'
                        );
                    }
                    $step = 'uebersicht';
                }

                $clearCache = true;
            } else {
                $step = 'neue Linkgruppe';
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
                $smarty->assign('xPlausiVar_arr', $checks->getPlausiVar())
                       ->assign('xPostVar_arr', $checks->getPostVar());
            }
            break;
        case 'move-to-linkgroup':
            $res = $linkAdmin->updateLinkGroup(
                $linkID,
                Request::postInt('kLinkgruppeAlt'),
                $linkGroupID
            );
            if ($res === LinkAdmin::ERROR_LINK_ALREADY_EXISTS) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkMoveDuplicate'), 'errorLinkMoveDuplicate');
            } elseif ($res === LinkAdmin::ERROR_LINK_NOT_FOUND) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkKeyNotFound'), 'errorLinkKeyNotFound');
            } elseif ($res === LinkAdmin::ERROR_LINK_GROUP_NOT_FOUND) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkGroupKeyNotFound'), 'errorLinkGroupKeyNotFound');
            } elseif ($res instanceof LinkInterface) {
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    sprintf(__('successLinkMove'), $res->getDisplayName()),
                    'successLinkMove'
                );
                $clearCache = true;
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorUnknownLong'), 'errorUnknownLong');
            }
            $step = 'uebersicht';
            break;
        case 'copy-to-linkgroup':
            $res = $linkAdmin->createReference($linkID, $linkGroupID);
            if ($res === LinkAdmin::ERROR_LINK_ALREADY_EXISTS) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkCopyDuplicate'), 'errorLinkCopyDuplicate');
            } elseif ($res === LinkAdmin::ERROR_LINK_NOT_FOUND) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkKeyNotFound'), 'errorLinkKeyNotFound');
            } elseif ($res === LinkAdmin::ERROR_LINK_GROUP_NOT_FOUND) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkGroupKeyNotFound'), 'errorLinkGroupKeyNotFound');
            } elseif ($res instanceof LinkInterface) {
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    sprintf(__('successLinkCopy'), $res->getDisplayName()),
                    'successLinkCopy'
                );
                $step       = 'uebersicht';
                $clearCache = true;
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorUnknownLong'), 'errorUnknownLong');
            }
            break;
        case 'change-parent':
            $parentID = (int)($_POST['kVaterLink'] ?? 0);
            if ($parentID >= 0 && ($link = $linkAdmin->updateParentID($linkID, $parentID)) !== false) {
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    sprintf(__('successLinkMove'), $link->cName),
                    'successLinkMove'
                );
                $step       = 'uebersicht';
                $clearCache = true;
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkMove'), 'errorLinkMove');
            }
            break;
        case 'edit-link':
            $step = 'edit-link';
            break;
        case 'create-or-update-link':
            $hasHTML = [];
            foreach (LanguageHelper::getAllLanguages() as $lang) {
                $hasHTML[] = 'cContent_' . $lang->getIso();
            }
            $checks = new PlausiCMS();
            $checks->setPostVar($_POST, $hasHTML, true);
            $checks->doPlausi('lnk');

            if (count($checks->getPlausiVar()) === 0) {
                $files = [];
                $link  = $linkAdmin->createOrUpdateLink($_POST);
                if (Request::postInt('kLink') === 0) {
                    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successLinkCreate'), 'successLinkCreate');
                } else {
                    $alertHelper->addAlert(
                        Alert::TYPE_SUCCESS,
                        sprintf(__('successLinkEdit'), $link->getDisplayName()),
                        'successLinkEdit'
                    );
                }
                $clearCache = true;
                $kLink      = $link->getID();
                $step       = 'uebersicht';
                $continue   = Request::postInt('continue') === 1;
                if ($continue) {
                    $step           = 'neuer Link';
                    $_POST['kLink'] = $kLink;
                }
                // Bilder hochladen
                if (!is_dir($uploadDir . $kLink)) {
                    mkdir($uploadDir . $kLink);
                }
                if (is_array($_FILES['Bilder']['name']) && count($_FILES['Bilder']['name']) > 0) {
                    $lastImage = $linkAdmin->getLastImageNumber($kLink);
                    $counter   = 0;
                    if ($lastImage > 0) {
                        $counter = $lastImage;
                    }
                    $imageCount = (count($_FILES['Bilder']['name']) + $counter);
                    for ($i = $counter; $i < $imageCount; ++$i) {
                        if (!empty($_FILES['Bilder']['size'][$i - $counter])
                            && $_FILES['Bilder']['error'][$i - $counter] === UPLOAD_ERR_OK
                        ) {
                            $type         = $_FILES['Bilder']['type'][$i - $counter];
                            $uploadedFile = $uploadDir . $kLink . '/Bild' . ($i + 1) . '.' .
                                mb_substr(
                                    $type,
                                    mb_strpos($type, '/') + 1,
                                    mb_strlen($type) - mb_strpos($type, '/') + 1
                                );
                            move_uploaded_file($_FILES['Bilder']['tmp_name'][$i - $counter], $uploadedFile);
                        }
                    }
                }
                $dirName = $uploadDir . $link->getID();
                if (is_dir($dirName)) {
                    $dirHandle = opendir($dirName);
                    $shopURL   = Shop::getURL() . '/';
                    while (($file = readdir($dirHandle)) !== false) {
                        if ($file === '.' || $file === '..') {
                            continue;
                        }
                        $newFile            = new stdClass();
                        $newFile->cName     = mb_substr($file, 0, mb_strpos($file, '.'));
                        $newFile->cNameFull = $file;
                        $newFile->cURL      = '<img class="link_image" src="' .
                            $shopURL . PFAD_BILDER . PFAD_LINKBILDER . $link->getID() . '/' . $file . '" />';
                        $newFile->nBild     = (int)mb_substr(
                            str_replace('Bild', '', $file),
                            0,
                            mb_strpos(str_replace('Bild', '', $file), '.')
                        );
                        $files[]            = $newFile;
                    }
                    usort($files, static function ($a, $b) {
                        return $a->nBild <=> $b->nBild;
                    });
                    $smarty->assign('cDatei_arr', $files);
                }
            } else {
                $step = 'neuer Link';
                $link = new Link($db);
                $link->setLinkGroupID(Request::postInt('kLinkgruppe'));
                $link->setLinkGroups([Request::postInt('kLinkgruppe')]);
                $checkVars = $checks->getPlausiVar();
                if (isset($checkVars['nSpezialseite'])) {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('isDuplicateSpecialLink'), 'isDuplicateSpecialLink');
                } else {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
                }
                $smarty->assign('xPlausiVar_arr', $checkVars)
                       ->assign('xPostVar_arr', $checks->getPostVar());
            }
            break;
        default:
            break;
    }
}

if ($step === 'loesch_linkgruppe' && $linkGroupID > 0) {
    $step = 'uebersicht';
    if ($linkAdmin->deleteLinkGroup($linkGroupID) > 0) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successLinkGroupDelete'), 'successLinkGroupDelete');
        $clearCache = true;
        $step       = 'uebersicht';
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkGroupDelete'), 'errorLinkGroupDelete');
    }
    $_POST = [];
} elseif ($step === 'edit-link') {
    $step = 'neuer Link';
    $link = (new Link($db))->load($linkID);
    $link->deref();
    $dirName = $uploadDir . $link->getID();
    $files   = [];
    if (Request::verifyGPCDataInt('delpic') === 1) {
        @unlink($dirName . '/' . Request::verifyGPDataString('cName'));
    }
    if (is_dir($dirName)) {
        $dirHandle = opendir($dirName);
        $shopURL   = Shop::getURL() . '/';
        while (($file = readdir($dirHandle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $newFile            = new stdClass();
            $newFile->cName     = mb_substr($file, 0, mb_strpos($file, '.'));
            $newFile->cNameFull = $file;
            $newFile->cURL      = '<img class="link_image" src="' .
                $shopURL . PFAD_BILDER . PFAD_LINKBILDER . $link->getID() . '/' . $file . '" />';
            $newFile->nBild     = (int)mb_substr(
                str_replace('Bild', '', $file),
                0,
                mb_strpos(str_replace('Bild', '', $file), '.')
            );
            $files[]            = $newFile;
        }
        usort($files, static function ($a, $b) {
            return $a->nBild <=> $b->nBild;
        });
        $smarty->assign('cDatei_arr', $files);
    }
}
if ($clearCache === true) {
    $linkAdmin->clearCache();
}
if ($step === 'uebersicht') {
    foreach ($linkAdmin->getDuplicateSpecialLinks()->groupBy(static function (LinkInterface $l) {
        return $l->getLinkType();
    }) as $specialLinks) {
        /** @var Collection $specialLinks */
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            sprintf(
                __('hasDuplicateSpecialLink'),
                ' ' . $specialLinks->map(static function (LinkInterface $l) {
                    return $l->getName();
                })->implode('/')
            ),
            'hasDuplicateSpecialLink-' . $specialLinks->first()->getLinkType()
        );
    }
    $smarty->assign('linkGroupCountByLinkID', $linkAdmin->getLinkGroupCountForLinkIDs())
           ->assign('linkgruppen', $linkAdmin->getLinkGroups());
}
if ($step === 'neuer Link') {
    $cgroups = $db->query('SELECT * FROM tkundengruppe ORDER BY cName', ReturnType::ARRAY_OF_OBJECTS);
    $lgl     = new LinkGroupList($db, Shop::Container()->getCache());
    $lgl->loadAll();
    $smarty->assign('specialPages', $linkAdmin->getSpecialPageTypes())
           ->assign('kundengruppen', $cgroups);
}
$smarty->assign('step', $step)
       ->assign('Link', $link)
       ->assign('kPlugin', Request::verifyGPCDataInt('kPlugin'))
       ->assign('linkAdmin', $linkAdmin)
       ->display('links.tpl');
