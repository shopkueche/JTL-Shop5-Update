<?php

use JTL\Alert\Alert;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('IMPORT_CUSTOMER_VIEW', true, true);

if (isset($_FILES['csv']['tmp_name'])
    && Request::postInt('kundenimport') === 1
    && $_FILES['csv']
    && Form::validateToken()
    && mb_strlen($_FILES['csv']['tmp_name']) > 0
) {
    $delimiter = getCsvDelimiter($_FILES['csv']['tmp_name']);
    $file      = fopen($_FILES['csv']['tmp_name'], 'r');
    if ($file !== false) {
        $format   = [
            'cPasswort',
            'cAnrede',
            'cTitel',
            'cVorname',
            'cNachname',
            'cFirma',
            'cStrasse',
            'cHausnummer',
            'cAdressZusatz',
            'cPLZ',
            'cOrt',
            'cBundesland',
            'cLand',
            'cTel',
            'cMobil',
            'cFax',
            'cMail',
            'cUSTID',
            'cWWW',
            'fGuthaben',
            'cNewsletter',
            'dGeburtstag',
            'fRabatt',
            'cHerkunft',
            'dErstellt',
            'cAktiv'
        ];
        $row      = 0;
        $fmt      = [];
        $formatId = -1;
        $notice   = '';
        while ($data = fgetcsv($file, 2000, $delimiter, '"')) {
            if ($row === 0) {
                $notice .= __('checkHead');
                $fmt     = checkformat($data, $format);
                if ($fmt === -1) {
                    $notice .= __('errorFormatNotFound');
                    break;
                }
                $notice .= '<br /><br />' . __('importPending') . '<br />';
            } else {
                $notice .= '<br />' . __('row') . $row . ': ' . processImport($fmt, $data);
            }

            $row++;
        }
        Shop::Container()->getAlertService()->addAlert(Alert::TYPE_NOTE, $notice, 'importNotice');
        fclose($file);
    }
}

$smarty->assign('kundengruppen', Shop::Container()->getDB()->query(
    'SELECT * FROM tkundengruppe ORDER BY cName',
    ReturnType::ARRAY_OF_OBJECTS
))
       ->assign('step', $step ?? null)
       ->display('kundenimport.tpl');

/**
 * @param array $data
 * @param array $format
 * @return array|int
 */
function checkformat($data, $format)
{
    $fmt = [];
    $cnt = count($data);
    for ($i = 0; $i < $cnt; $i++) {
        if (in_array($data[$i], $format, true)) {
            $fmt[$i] = $data[$i];
        } else {
            $fmt[$i] = '';
        }
    }

    if (Request::postInt('PasswortGenerieren') !== 1) {
        if (!in_array('cPasswort', $fmt, true) || !in_array('cMail', $fmt, true)) {
            return -1;
        }
    } elseif (!in_array('cMail', $fmt, true)) {
        return -1;
    }

    return $fmt;
}

/**
 * @param array $fmt
 * @param array $data
 * @return string
 */
function processImport($fmt, $data)
{
    $customer                = new Customer();
    $customer->kKundengruppe = Request::postInt('kKundengruppe');
    $customer->kSprache      = Request::postInt('kSprache');
    $customer->cAbgeholt     = 'Y';
    $customer->cSperre       = 'N';
    $customer->cAktiv        = 'Y';
    $customer->nRegistriert  = 1;
    $customer->dErstellt     = 'NOW()';
    $cnt                     = count($data);
    for ($i = 0; $i < $cnt; $i++) {
        if (!empty($fmt[$i])) {
            $customer->{$fmt[$i]} = $data[$i];
        }
    }
    if (Text::filterEmailAddress($customer->cMail) === false) {
        return sprintf(__('errorInvalidEmail'), $customer->cMail);
    }
    if (Request::postInt('PasswortGenerieren') !== 1
        && (!$customer->cPasswort || $customer->cPasswort === 'd41d8cd98f00b204e9800998ecf8427e')
    ) {
        return __('errorNoPassword');
    }
    if (!$customer->cNachname) {
        return __('errorNoSurname');
    }

    $old_mail = Shop::Container()->getDB()->select('tkunde', 'cMail', $customer->cMail);
    if (isset($old_mail->kKunde) && $old_mail->kKunde > 0) {
        return sprintf(__('errorEmailDuplicate'), $customer->cMail);
    }
    if ($customer->cAnrede === 'f' || mb_convert_case($customer->cAnrede, MB_CASE_LOWER) === 'frau') {
        $customer->cAnrede = 'w';
    }
    if ($customer->cAnrede === 'h' || mb_convert_case($customer->cAnrede, MB_CASE_LOWER) === 'herr') {
        $customer->cAnrede = 'm';
    }
    if ($customer->cNewsletter == 0 || $customer->cNewsletter == 'NULL') {
        $customer->cNewsletter = 'N';
    }
    if ($customer->cNewsletter == 1) {
        $customer->cNewsletter = 'Y';
    }

    if (empty($customer->cLand)) {
        if (isset($_SESSION['kundenimport']['cLand']) && mb_strlen($_SESSION['kundenimport']['cLand']) > 0) {
            $customer->cLand = $_SESSION['kundenimport']['cLand'];
        } else {
            $oRes = Shop::Container()->getDB()->query(
                "SELECT cWert AS cLand 
                    FROM teinstellungen 
                    WHERE cName = 'kundenregistrierung_standardland'",
                ReturnType::SINGLE_OBJECT
            );
            if (is_object($oRes) && isset($oRes->cLand) && mb_strlen($oRes->cLand) > 0) {
                $_SESSION['kundenimport']['cLand'] = $oRes->cLand;
                $customer->cLand                   = $oRes->cLand;
            }
        }
    }
    $password = '';
    if (Request::postInt('PasswortGenerieren') === 1) {
        $password            = Shop::Container()->getPasswordService()->generate(PASSWORD_DEFAULT_LENGTH);
        $customer->cPasswort = Shop::Container()->getPasswordService()->hash($password);
    }
    $tmp              = new stdClass();
    $tmp->cNachname   = $customer->cNachname;
    $tmp->cFirma      = $customer->cFirma;
    $tmp->cStrasse    = $customer->cStrasse;
    $tmp->cHausnummer = $customer->cHausnummer;
    if ($customer->insertInDB()) {
        if (Request::postInt('PasswortGenerieren') === 1) {
            $customer->cPasswortKlartext = $password;
            $customer->cNachname         = $tmp->cNachname;
            $customer->cFirma            = $tmp->cFirma;
            $customer->cStrasse          = $tmp->cStrasse;
            $customer->cHausnummer       = $tmp->cHausnummer;
            $obj                         = new stdClass();
            $obj->tkunde                 = $customer;
            $mailer                      = Shop::Container()->get(Mailer::class);
            $mail                        = new Mail();
            $mailer->send($mail->createFromTemplateID(MAILTEMPLATE_ACCOUNTERSTELLUNG_DURCH_BETREIBER, $obj));
        }

        return __('successImportRecord') . $customer->cVorname . ' ' . $customer->cNachname;
    }

    return __('errorImportRecord');
}
