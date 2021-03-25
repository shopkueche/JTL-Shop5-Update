<?php

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Shop;
use function Functional\map;
use function Functional\reindex;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('SETTINGS_CONTACTFORM_VIEW', true, true);
$step        = 'uebersicht';
$alertHelper = Shop::Container()->getAlertService();
$db          = Shop::Container()->getDB();
$languages   = LanguageHelper::getAllLanguages();
if (Request::getInt('del') > 0 && Form::validateToken()) {
    $db->delete('tkontaktbetreff', 'kKontaktBetreff', Request::getInt('del'));
    $db->delete('tkontaktbetreffsprache', 'kKontaktBetreff', Request::getInt('del'));

    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSubjectDelete'), 'successSubjectDelete');
}

if (Request::postInt('content') === 1 && Form::validateToken()) {
    $db->delete('tspezialcontentsprache', 'nSpezialContent', SC_KONTAKTFORMULAR);
    foreach ($languages as $language) {
        $code                             = $language->getIso();
        $spezialContent1                  = new stdClass();
        $spezialContent2                  = new stdClass();
        $spezialContent3                  = new stdClass();
        $spezialContent1->nSpezialContent = SC_KONTAKTFORMULAR;
        $spezialContent2->nSpezialContent = SC_KONTAKTFORMULAR;
        $spezialContent3->nSpezialContent = SC_KONTAKTFORMULAR;
        $spezialContent1->cISOSprache     = $code;
        $spezialContent2->cISOSprache     = $code;
        $spezialContent3->cISOSprache     = $code;
        $spezialContent1->cTyp            = 'oben';
        $spezialContent2->cTyp            = 'unten';
        $spezialContent3->cTyp            = 'titel';
        $spezialContent1->cContent        = $_POST['cContentTop_' . $code];
        $spezialContent2->cContent        = $_POST['cContentBottom_' . $code];
        $spezialContent3->cContent        = htmlspecialchars(
            $_POST['cTitle_' . $code],
            ENT_COMPAT | ENT_HTML401,
            JTL_CHARSET
        );

        $db->insert('tspezialcontentsprache', $spezialContent1);
        $db->insert('tspezialcontentsprache', $spezialContent2);
        $db->insert('tspezialcontentsprache', $spezialContent3);
        unset($spezialContent1, $spezialContent2, $spezialContent3);
    }
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successContentSave'), 'successContentSave');
    $tab = 'content';
}

if (Request::postInt('betreff') === 1 && Form::validateToken()) {
    if ($_POST['cName'] && $_POST['cMail']) {
        $newSubject        = new stdClass();
        $newSubject->cName = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $newSubject->cMail = $_POST['cMail'];
        if (is_array($_POST['cKundengruppen'])) {
            $newSubject->cKundengruppen = implode(';', $_POST['cKundengruppen']) . ';';
        }
        if (GeneralObject::hasCount('cKundengruppen', $_POST) && in_array(0, $_POST['cKundengruppen'])) {
            $newSubject->cKundengruppen = 0;
        }
        $newSubject->nSort = Request::postInt('nSort');
        $subjectID         = 0;
        if (Request::postInt('kKontaktBetreff') === 0) {
            $subjectID = $db->insert('tkontaktbetreff', $newSubject);
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSubjectCreate'), 'successSubjectCreate');
        } else {
            $subjectID = Request::postInt('kKontaktBetreff');
            $db->update('tkontaktbetreff', 'kKontaktBetreff', $subjectID, $newSubject);
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                sprintf(__('successSubjectSave'), $newSubject->cName),
                'successSubjectSave'
            );
        }
        $localized                  = new stdClass();
        $localized->kKontaktBetreff = $subjectID;
        foreach ($languages as $language) {
            $code                   = $language->getIso();
            $localized->cISOSprache = $code;
            $localized->cName       = $newSubject->cName;
            if ($_POST['cName_' . $code]) {
                $localized->cName = htmlspecialchars(
                    $_POST['cName_' . $code],
                    ENT_COMPAT | ENT_HTML401,
                    JTL_CHARSET
                );
            }
            $db->delete(
                'tkontaktbetreffsprache',
                ['kKontaktBetreff', 'cISOSprache'],
                [$subjectID, $code]
            );
            $db->insert('tkontaktbetreffsprache', $localized);
        }
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSubjectSave'), 'errorSubjectSave');
        $step = 'betreff';
    }
    $tab = 'subjects';
}

if (Request::postInt('einstellungen') === 1) {
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(CONF_KONTAKTFORMULAR, $_POST),
        'saveSettings'
    );
    $tab = 'config';
}

if ((Request::getInt('kKontaktBetreff') > 0 || Request::getInt('neu') === 1) && Form::validateToken()) {
    $step = 'betreff';
}

if ($step === 'uebersicht') {
    $subjects = $db->query(
        'SELECT * FROM tkontaktbetreff ORDER BY nSort',
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($subjects as $subject) {
        $groups = '';
        if (!$subject->cKundengruppen) {
            $groups = __('allCustomerGroups');
        } else {
            foreach (explode(';', $subject->cKundengruppen) as $customerGroupID) {
                if (!is_numeric($customerGroupID)) {
                    continue;
                }
                $kndgrp  = $db->select('tkundengruppe', 'kKundengruppe', (int)$customerGroupID);
                $groups .= ' ' . $kndgrp->cName;
            }
        }
        $subject->Kundengruppen = $groups;
    }
    $specialContent = $db->selectAll(
        'tspezialcontentsprache',
        'nSpezialContent',
        SC_KONTAKTFORMULAR,
        '*',
        'cTyp'
    );
    $content        = [];
    foreach ($specialContent as $item) {
        $content[$item->cISOSprache . '_' . $item->cTyp] = $item->cContent;
    }
    $smarty->assign('Betreffs', $subjects)
        ->assign('Conf', getAdminSectionSettings(CONF_KONTAKTFORMULAR))
        ->assign('Content', $content);
}

if ($step === 'betreff') {
    $newSubject = null;
    if (Request::getInt('kKontaktBetreff') > 0) {
        $newSubject = $db->select(
            'tkontaktbetreff',
            'kKontaktBetreff',
            Request::getInt('kKontaktBetreff')
        );
    }

    $customerGroups = $db->query(
        'SELECT * FROM tkundengruppe ORDER BY cName',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $smarty->assign('Betreff', $newSubject)
        ->assign('kundengruppen', $customerGroups)
        ->assign('gesetzteKundengruppen', getGesetzteKundengruppen($newSubject))
        ->assign('Betreffname', ($newSubject !== null) ? getNames($newSubject->kKontaktBetreff) : null);
}
if (isset($tab)) {
    $smarty->assign('cTab', $tab);
}
$smarty->assign('step', $step)
    ->display('kontaktformular.tpl');

/**
 * @param object $link
 * @return array
 */
function getGesetzteKundengruppen($link)
{
    $ret = [];
    if (!isset($link->cKundengruppen) || !$link->cKundengruppen) {
        $ret[0] = true;

        return $ret;
    }
    foreach (array_filter(explode(';', $link->cKundengruppen)) as $customerGroupID) {
        $ret[$customerGroupID] = true;
    }

    return $ret;
}

/**
 * @param int $id
 * @return array
 */
function getNames(int $id)
{
    $data = Shop::Container()->getDB()->selectAll('tkontaktbetreffsprache', 'kKontaktBetreff', $id);

    return map(reindex($data, static function ($e) {
        return $e->cISOSprache;
    }), static function ($e) {
        return $e->cName;
    });
}
