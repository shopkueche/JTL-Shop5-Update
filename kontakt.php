<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\CheckBox;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Session\Frontend;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'kontakt_inc.php';

Shop::setPageType(PAGE_KONTAKT);
$smarty         = Shop::Smarty();
$conf           = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_KONTAKTFORMULAR]);
$linkHelper     = Shop::Container()->getLinkService();
$kLink          = $linkHelper->getSpecialPageID(LINKTYP_KONTAKT);
$link           = $linkHelper->getPageLink($kLink);
$cCanonicalURL  = '';
$specialContent = new stdClass();
$alertHelper    = Shop::Container()->getAlertService();
$lang           = Shop::getLanguageCode();
if (Form::checkSubject()) {
    $step        = 'formular';
    $missingData = [];
    if (Request::postInt('kontakt') === 1 && Form::validateToken()) {
        $missingData     = Form::getMissingContactFormData();
        $customerGroupID = Frontend::getCustomerGroup()->getID();
        $checkBox        = new CheckBox();
        $missingData     = array_merge(
            $missingData,
            $checkBox->validateCheckBox(CHECKBOX_ORT_KONTAKT, $customerGroupID, $_POST, true)
        );
        $ok              = Form::eingabenKorrekt($missingData);
        $smarty->assign('cPost_arr', Text::filterXSS($_POST));
        executeHook(HOOK_KONTAKT_PAGE_PLAUSI);

        if ($ok) {
            $step = 'floodschutz';
            if (!Form::checkFloodProtection($conf['kontakt']['kontakt_sperre_minuten'])) {
                $msg = Form::baueKontaktFormularVorgaben();
                $checkBox->triggerSpecialFunction(
                    CHECKBOX_ORT_KONTAKT,
                    $customerGroupID,
                    true,
                    $_POST,
                    ['oKunde' => $msg, 'oNachricht' => $msg]
                )->checkLogging(CHECKBOX_ORT_KONTAKT, $customerGroupID, $_POST, true);
                Form::editMessage();
                $step = 'nachricht versendet';
            }
        }
    }

    $contents = Shop::Container()->getDB()->selectAll(
        'tspezialcontentsprache',
        ['nSpezialContent', 'cISOSprache'],
        [(int)SC_KONTAKTFORMULAR, $lang]
    );
    foreach ($contents as $content) {
        $specialContent->{$content->cTyp} = $content->cContent;
    }
    $subjects = Shop::Container()->getDB()->queryPrepared(
        "SELECT *
            FROM tkontaktbetreff
            WHERE (cKundengruppen = 0
            OR FIND_IN_SET(:customerGroupID, REPLACE(cKundengruppen, ';', ',')) > 0)
            ORDER BY nSort",
        ['customerGroupID' => Frontend::getCustomerGroup()->getID()],
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($subjects as $subject) {
        $localization             = Shop::Container()->getDB()->select(
            'tkontaktbetreffsprache',
            'kKontaktBetreff',
            (int)$subject->kKontaktBetreff,
            'cISOSprache',
            $lang
        );
        $subject->AngezeigterName = $localization->cName ?? $subject->cName;
    }
    if ($step === 'nachricht versendet') {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, Shop::Lang()->get('messageSent', 'contact'), 'messageSent');
    } elseif ($step === 'floodschutz') {
        $alertHelper->addAlert(
            Alert::TYPE_DANGER,
            Shop::Lang()->get('youSentUsAMessageShortTimeBefore', 'contact'),
            'youSentUsAMessageShortTimeBefore'
        );
    }
    $cCanonicalURL = $linkHelper->getStaticRoute('kontakt.php');

    $smarty->assign('step', $step)
           ->assign('code', false)
           ->assign('betreffs', $subjects)
           ->assign('Vorgaben', Form::baueKontaktFormularVorgaben($step === 'nachricht versendet'))
           ->assign('fehlendeAngaben', $missingData)
           ->assign('nAnzeigeOrt', CHECKBOX_ORT_KONTAKT);
} else {
    Shop::Container()->getLogService()->error('Kein Kontaktbetreff vorhanden! Bitte im Backend unter ' .
        'Einstellungen -> Kontaktformular -> Betreffs einen Betreff hinzuf&uuml;gen.');

    $alertHelper->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('noSubjectAvailable', 'contact'), 'noSubjectAvailable');
}

$smarty->assign('Link', $link)
       ->assign('Spezialcontent', $specialContent);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
executeHook(HOOK_KONTAKT_PAGE);
$smarty->display('contact/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
