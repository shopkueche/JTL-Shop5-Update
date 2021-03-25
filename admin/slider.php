<?php

use JTL\Alert\Alert;
use JTL\Boxes\Admin\BoxAdmin;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Pagination\Pagination;
use JTL\Shop;
use JTL\Slide;
use JTL\Slider;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('SLIDER_VIEW', true, true);
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'slider_inc.php';
$alertHelper = Shop::Container()->getAlertService();
$db          = Shop::Container()->getDB();
$_kSlider    = 0;
$redirectUrl = Shop::getAdminURL() . '/slider.php';
$action      = isset($_REQUEST['action']) && Form::validateToken()
    ? $_REQUEST['action']
    : 'view';
$kSlider     = isset($_REQUEST['id'])
    ? (int)$_REQUEST['id']
    : 0;
switch ($action) {
    case 'slide_set':
        $aSlideKey = array_keys((array)$_REQUEST['aSlide']);
        $count     = count($aSlideKey);
        for ($i = 0; $i < $count; $i++) {
            $slide  = new Slide();
            $aSlide = $_REQUEST['aSlide'][$aSlideKey[$i]];
            if (mb_strpos((string)$aSlideKey[$i], 'neu') === false) {
                $slide->setID((int)$aSlideKey[$i]);
            }

            $slide->setSliderID($kSlider);
            $slide->setTitle(htmlspecialchars($aSlide['cTitel'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET));
            $slide->setImage($aSlide['cBild']);
            $slide->setThumbnail($aSlide['cThumbnail']);
            $slide->setText($aSlide['cText']);
            $slide->setLink($aSlide['cLink']);
            $slide->setSort((int)$aSlide['nSort']);
            if ((int)$aSlide['delete'] === 1) {
                $slide->delete();
            } else {
                $slide->save();
            }
        }
        break;
    default:
        $smarty->assign('disabled', '');
        if (!empty($_POST) && Form::validateToken()) {
            $slider   = new Slider($db);
            $_kSlider = (int)$_POST['kSlider'];
            $slider->load($kSlider, false);
            $slider->set((object)$_REQUEST);
            // extensionpoint
            $languageID      = (int)$_POST['kSprache'];
            $customerGroupID = $_POST['kKundengruppe'];
            $pageType        = (int)$_POST['nSeitenTyp'];
            $cKey            = $_POST['cKey'];
            $cKeyValue       = '';
            $cValue          = '';
            if ($pageType === PAGE_ARTIKEL) {
                $cKey      = 'kArtikel';
                $cKeyValue = 'article_key';
                $cValue    = $_POST[$cKeyValue];
            } elseif ($pageType === PAGE_ARTIKELLISTE) {
                $filter = [
                    'kMerkmalWert' => 'attribute_key',
                    'kKategorie'   => 'categories_key',
                    'kHersteller'  => 'manufacturer_key',
                    'cSuche'       => 'keycSuche'
                ];

                $cKeyValue = $filter[$cKey];
                $cValue    = $_POST[$cKeyValue];
            } elseif ($pageType === PAGE_EIGENE) {
                $cKey      = 'kLink';
                $cKeyValue = 'link_key';
                $cValue    = $_POST[$cKeyValue];
            }
            if (!empty($cKeyValue) && empty($cValue)) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    sprintf(__('errorKeyMissing'), $cKey),
                    'errorKeyMissing'
                );
            } else {
                if (empty($slider->getEffects())) {
                    $slider->setEffects('random');
                }
                if ($slider->save() === true) {
                    Shop::Container()->getDB()->delete(
                        'textensionpoint',
                        ['cClass', 'kInitial'],
                        ['slider', $slider->getID()]
                    );
                    $extension                = new stdClass();
                    $extension->kSprache      = $languageID;
                    $extension->kKundengruppe = $customerGroupID;
                    $extension->nSeite        = $pageType;
                    $extension->cKey          = $cKey;
                    $extension->cValue        = $cValue;
                    $extension->cClass        = 'slider';
                    $extension->kInitial      = $slider->getID();
                    Shop::Container()->getDB()->insert('textensionpoint', $extension);

                    $alertHelper->addAlert(
                        Alert::TYPE_SUCCESS,
                        __('successSliderSave'),
                        'successSliderSave',
                        ['saveInSession' => true]
                    );
                    header('Location: ' . $redirectUrl);
                    exit;
                }
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSliderSave'), 'errorSliderSave');
            }
        }
        break;
}
switch ($action) {
    case 'slides':
        $slider = new Slider($db);
        $slider->load($kSlider, false);
        $smarty->assign('oSlider', $slider);
        if (!is_object($slider)) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSliderNotFound'), 'errorSliderNotFound');
            $action = 'view';
        }
        break;

    case 'edit':
        if ($kSlider === 0 && $_kSlider > 0) {
            $kSlider = $_kSlider;
        }
        $slider = new Slider($db);
        $slider->load($kSlider, false);
        $smarty->assign('customerGroups', CustomerGroup::getGroups())
               ->assign('oExtension', holeExtension($kSlider));

        if ($slider->getEffects() !== 'random') {
            $effects = explode(';', $slider->getEffects());
            $options = '';
            foreach ($effects as $cKey => $cValue) {
                $options .= '<option value="' . $cValue . '">' . $cValue . '</option>';
            }
            $smarty->assign('cEffects', $options);
        } else {
            $smarty->assign('checked', 'checked="checked"')
                   ->assign('disabled', 'disabled="true"');
        }
        $smarty->assign('oSlider', $slider);

        if (!is_object($slider)) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSliderNotFound'), 'errorSliderNotFound');
            $action = 'view';
            break;
        }
        break;

    case 'new':
        $smarty->assign('checked', 'checked="checked"')
               ->assign('customerGroups', CustomerGroup::getGroups())
               ->assign('oSlider', new Slider($db));
        break;

    case 'delete':
        $slider = new Slider($db);
        $slider->load($kSlider, false);
        if ($slider->delete() === true) {
            header('Location: ' . $redirectUrl);
            exit;
        }
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSliderRemove'), 'errorSliderRemove');
        break;

    default:
        break;
}

$sliders    = $db->query('SELECT * FROM tslider', ReturnType::ARRAY_OF_OBJECTS);
$pagination = (new Pagination('sliders'))
    ->setRange(4)
    ->setItemArray($sliders)
    ->assemble();

$smarty->assign('action', $action)
       ->assign('kSlider', $kSlider)
       ->assign('validPageTypes', (new BoxAdmin($db))->getMappedValidPageTypes())
       ->assign('pagination', $pagination)
       ->assign('oSlider_arr', $pagination->getPageItems())
       ->display('slider.tpl');
