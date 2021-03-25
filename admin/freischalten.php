<?php

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('UNLOCK_CENTRAL_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'freischalten_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bewertung_inc.php';
setzeSprache();

$step                  = 'freischalten_uebersicht';
$ratingsSQL            = new stdClass();
$liveSearchSQL         = new stdClass();
$commentsSQL           = new stdClass();
$recipientsSQL         = new stdClass();
$ratingsSQL->cWhere    = '';
$liveSearchSQL->cWhere = '';
$liveSearchSQL->cOrder = ' dZuletztGesucht DESC ';
$commentsSQL->cWhere   = '';
$recipientsSQL->cWhere = '';
$recipientsSQL->cOrder = ' tnewsletterempfaenger.dEingetragen DESC';
$tab                   = Request::verifyGPDataString('tab');
$alertHelper           = Shop::Container()->getAlertService();

if (Request::verifyGPCDataInt('Suche') === 1) {
    $search = Shop::Container()->getDB()->escape(Text::filterXSS(Request::verifyGPDataString('cSuche')));

    if (mb_strlen($search) > 0) {
        switch (Request::verifyGPDataString('cSuchTyp')) {
            case 'Bewertung':
                $tab                = 'bewertungen';
                $ratingsSQL->cWhere = " AND (tbewertung.cName LIKE '%" . $search . "%'
                                            OR tbewertung.cTitel LIKE '%" . $search . "%'
                                            OR tartikel.cName LIKE '%" . $search . "%')";
                break;
            case 'Livesuche':
                $tab                   = 'livesearch';
                $liveSearchSQL->cWhere = " AND tsuchanfrage.cSuche LIKE '%" . $search . "%'";
                break;
            case 'Newskommentar':
                $tab                 = 'newscomments';
                $commentsSQL->cWhere = " AND (tnewskommentar.cKommentar LIKE '%" . $search . "%'
                                                OR tkunde.cVorname LIKE '%" . $search . "%'
                                                OR tkunde.cNachname LIKE '%" . $search . "%'
                                                OR t.title LIKE '%" . $search . "%')";
                break;
            case 'Newsletterempfaenger':
                $tab                   = 'newsletter';
                $recipientsSQL->cWhere = " AND (tnewsletterempfaenger.cVorname LIKE '%" . $search . "%'
                                                        OR tnewsletterempfaenger.cNachname LIKE '%" . $search . "%'
                                                        OR tnewsletterempfaenger.cEmail LIKE '%" . $search . "%')";
                break;
            default:
                break;
        }

        $smarty->assign('cSuche', $search)
               ->assign('cSuchTyp', Request::verifyGPDataString('cSuchTyp'));
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSearchTermMissing'), 'errorSearchTermMissing');
    }
}

if (Request::verifyGPCDataInt('nSort') > 0) {
    $smarty->assign('nSort', Request::verifyGPCDataInt('nSort'));

    switch (Request::verifyGPCDataInt('nSort')) {
        case 1:
            $liveSearchSQL->cOrder = ' tsuchanfrage.cSuche ASC ';
            break;
        case 11:
            $liveSearchSQL->cOrder = ' tsuchanfrage.cSuche DESC ';
            break;
        case 2:
            $liveSearchSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche DESC ';
            break;
        case 22:
            $liveSearchSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche ASC ';
            break;
        case 3:
            $liveSearchSQL->cOrder = ' tsuchanfrage.nAnzahlTreffer DESC ';
            break;
        case 33:
            $liveSearchSQL->cOrder = ' tsuchanfrage.nAnzahlTreffer ASC ';
            break;
        case 4:
            $recipientsSQL->cOrder = ' tnewsletterempfaenger.dEingetragen DESC ';
            break;
        case 44:
            $recipientsSQL->cOrder = ' tnewsletterempfaenger.dEingetragen ASC ';
            break;
        default:
            break;
    }
} else {
    $smarty->assign('nLivesucheSort', -1);
}

// Freischalten
if (Request::verifyGPCDataInt('freischalten') === 1 && Form::validateToken()) {
    // Bewertungen
    if (Request::verifyGPCDataInt('bewertungen') === 1) {
        if (isset($_POST['freischaltensubmit'])) {
            if (schalteBewertungFrei($_POST['kBewertung'])) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successRatingUnlock'), 'successRatingUnlock');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneRating'), 'errorAtLeastOneRating');
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (loescheBewertung($_POST['kBewertung'])) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successRatingDelete'), 'successRatingDelete');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneRating'), 'errorAtLeastOneRating');
            }
        }
    } elseif (Request::verifyGPCDataInt('suchanfragen') === 1) { // Suchanfragen
        // Mappen
        if (isset($_POST['submitMapping'])) {
            $mapping = Request::verifyGPDataString('cMapping');
            if (mb_strlen($mapping) > 0) {
                $res = 0;
                if (GeneralObject::hasCount('kSuchanfrage', $_POST)) {
                    $res = mappeLiveSuche($_POST['kSuchanfrage'], $mapping);

                    if ($res === 1) { // Alles O.K.
                        if (schalteSuchanfragenFrei($_POST['kSuchanfrage'])) {
                            $alertHelper->addAlert(
                                Alert::TYPE_SUCCESS,
                                sprintf(__('successLiveSearchMap'), $mapping),
                                'successLiveSearchMap'
                            );
                        } else {
                            $alertHelper->addAlert(
                                Alert::TYPE_ERROR,
                                __('errorLiveSearchMapNotUnlock'),
                                'errorLiveSearchMapNotUnlock'
                            );
                        }
                    } else {
                        switch ($res) {
                            case 2:
                                $searchError = __('errorMapUnknown');
                                break;
                            case 3:
                                $searchError = __('errorSearchNotFoundDB');
                                break;
                            case 4:
                                $searchError = __('errorMapDB');
                                break;
                            case 5:
                                $searchError = __('errorMapToNotExisting');
                                break;
                            case 6:
                                $searchError = __('errorMapSelf');
                                break;
                            default:
                                break;
                        }
                        $alertHelper->addAlert(Alert::TYPE_ERROR, $searchError, 'searchError');
                    }
                } else {
                    $alertHelper->addAlert(
                        Alert::TYPE_ERROR,
                        __('errorAtLeastOneLiveSearch'),
                        'errorAtLeastOneLiveSearch'
                    );
                }
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorMapNameMissing'), 'errorMapNameMissing');
            }
        }

        if (isset($_POST['freischaltensubmit'])) {
            if (schalteSuchanfragenFrei(Request::postVar('kSuchanfrage', []))) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSearchUnlock'), 'successSearchUnlock');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (loescheSuchanfragen(Request::postVar('kSuchanfrage', []))) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSearchDelete'), 'successSearchDelete');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
            }
        }
    } elseif (Request::verifyGPCDataInt('newskommentare') === 1 && Form::validateToken()) {
        if (isset($_POST['freischaltensubmit'])) {
            if (schalteNewskommentareFrei(Request::postVar('kNewsKommentar', []))) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successNewsCommentUnlock'), 'successNewsCommentUnlock');
            } else {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorAtLeastOneNewsComment'),
                    'errorAtLeastOneNewsComment'
                );
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (loescheNewskommentare(Request::postVar('kNewsKommentar', []))) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successNewsCommentDelete'), 'successNewsCommentDelete');
            } else {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorAtLeastOneNewsComment'),
                    'errorAtLeastOneNewsComment'
                );
            }
        }
    } elseif (Request::verifyGPCDataInt('newsletterempfaenger') === 1 && Form::validateToken()) {
        if (isset($_POST['freischaltensubmit'])) {
            if (schalteNewsletterempfaengerFrei(Request::postVar('kNewsletterEmpfaenger', []))) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successNewsletterUnlock'), 'successNewsletterUnlock');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneNewsletter'), 'errorAtLeastOneNewsletter');
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (loescheNewsletterempfaenger(Request::postVar('kNewsletterEmpfaenger', []))) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successNewsletterDelete'), 'successNewsletterDelete');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneNewsletter'), 'errorAtLeastOneNewsletter');
            }
        }
    }
}

if ($step === 'freischalten_uebersicht') {
    $pagiRatings    = (new Pagination('bewertungen'))
        ->setItemCount(gibMaxBewertungen())
        ->assemble();
    $pagiQueries    = (new Pagination('suchanfragen'))
        ->setItemCount(gibMaxSuchanfragen())
        ->assemble();
    $pagiComments   = (new Pagination('newskommentare'))
        ->setItemCount(gibMaxNewskommentare())
        ->assemble();
    $pagiRecipients = (new Pagination('newsletter'))
        ->setItemCount(gibMaxNewsletterEmpfaenger())
        ->assemble();

    $reviews      = gibBewertungFreischalten(' LIMIT ' . $pagiRatings->getLimitSQL(), $ratingsSQL);
    $queries      = gibSuchanfrageFreischalten(' LIMIT ' . $pagiQueries->getLimitSQL(), $liveSearchSQL);
    $newsComments = gibNewskommentarFreischalten(' LIMIT ' . $pagiComments->getLimitSQL(), $commentsSQL);
    $recipients   = gibNewsletterEmpfaengerFreischalten(' LIMIT ' . $pagiRecipients->getLimitSQL(), $recipientsSQL);
    $smarty->assign('ratings', $reviews)
           ->assign('searchQueries', $queries)
           ->assign('comments', $newsComments)
           ->assign('recipients', $recipients)
           ->assign('oPagiBewertungen', $pagiRatings)
           ->assign('oPagiSuchanfragen', $pagiQueries)
           ->assign('oPagiNewskommentare', $pagiComments)
           ->assign('oPagiNewsletterEmpfaenger', $pagiRecipients);
}

$smarty->assign('step', $step)
       ->assign('cTab', $tab)
       ->display('freischalten.tpl');
