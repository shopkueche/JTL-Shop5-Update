<?php

use JTL\Catalog\Product\Preise;
use JTL\Checkout\Kupon;
use JTL\Checkout\Lieferadresse;
use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Emailhistory;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\MailSmarty;

/**
 * @param string        $moduleID
 * @param stdClass      $data
 * @param null|stdClass $mail
 * @return null|bool|stdClass
 * @deprecated since 5.0.0
 */
function sendeMail($moduleID, $data, $mail = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $db       = Shop::Container()->getDB();
    $mailTPL  = null;
    $bodyHtml = '';
    if (!is_object($mail)) {
        $mail = new stdClass();
    }
    $config     = Shopsetting::getInstance()->getAll();
    $senderName = $config['emails']['email_master_absender_name'];
    $senderMail = $config['emails']['email_master_absender'];
    $sendCopy   = '';
    $smarty     = new MailSmarty($db);
    if (!isset($data->tkunde)) {
        $data->tkunde = new stdClass();
    }
    if (!isset($data->tkunde->kKundengruppe) || !$data->tkunde->kKundengruppe) {
        $data->tkunde->kKundengruppe = CustomerGroup::getDefaultGroupID();
    }
    $data->tfirma        = $db->query('SELECT * FROM tfirma', ReturnType::SINGLE_OBJECT);
    $data->tkundengruppe = $db->select(
        'tkundengruppe',
        'kKundengruppe',
        (int)$data->tkunde->kKundengruppe
    );
    if (isset($data->tkunde->kSprache) && $data->tkunde->kSprache > 0) {
        $groupLang = $db->select(
            'tkundengruppensprache',
            'kKundengruppe',
            (int)$data->tkunde->kKundengruppe,
            'kSprache',
            (int)$data->tkunde->kSprache
        );
        if (isset($groupLang->cName) && $groupLang->cName !== $data->tkundengruppe->cName) {
            $data->tkundengruppe->cName = $groupLang->cName;
        }
    }
    if (isset($_SESSION['currentLanguage']->kSprache)) {
        $lang = $_SESSION['currentLanguage'];
    } else {
        if (isset($data->tkunde->kSprache) && $data->tkunde->kSprache > 0) {
            $lang = $db->select('tsprache', 'kSprache', (int)$data->tkunde->kSprache);
        }
        if (isset($data->NewsletterEmpfaenger->kSprache) && $data->NewsletterEmpfaenger->kSprache > 0) {
            $lang = $db->select(
                'tsprache',
                'kSprache',
                $data->NewsletterEmpfaenger->kSprache
            );
        }
        if (empty($lang)) {
            $lang = isset($_SESSION['kSprache'])
                ? $db->select('tsprache', 'kSprache', $_SESSION['kSprache'])
                : $db->select('tsprache', 'cShopStandard', 'Y');
        }
    }
    $customer              = lokalisiereKunde($lang, $data->tkunde);
    $AGB                   = new stdClass();
    $WRB                   = new stdClass();
    $WRBForm               = new stdClass();
    $DSE                   = new stdClass();
    $oAGBWRB               = $db->select(
        'ttext',
        ['kSprache', 'kKundengruppe'],
        [(int)$lang->kSprache, (int)$data->tkunde->kKundengruppe]
    );
    $AGB->cContentText     = $oAGBWRB->cAGBContentText ?? '';
    $AGB->cContentHtml     = $oAGBWRB->cAGBContentHtml ?? '';
    $WRB->cContentText     = $oAGBWRB->cWRBContentText ?? '';
    $WRB->cContentHtml     = $oAGBWRB->cWRBContentHtml ?? '';
    $DSE->cContentText     = $oAGBWRB->cDSEContentText ?? '';
    $DSE->cContentHtml     = $oAGBWRB->cDSEContentHtml ?? '';
    $WRBForm->cContentHtml = $oAGBWRB->cWRBFormContentHtml ?? '';
    $WRBForm->cContentText = $oAGBWRB->cWRBFormContentText ?? '';

    $smarty->assign('int_lang', $lang)//assign the current language for includeMailTemplate()
           ->assign('Firma', $data->tfirma)
           ->assign('Kunde', $customer)
           ->assign('Einstellungen', $config)
           ->assign('Kundengruppe', $data->tkundengruppe)
           ->assign('NettoPreise', $data->tkundengruppe->nNettoPreise)
           ->assign('ShopLogoURL', Shop::getLogo(true))
           ->assign('ShopURL', Shop::getURL())
           ->assign('AGB', $AGB)
           ->assign('WRB', $WRB)
           ->assign('DSE', $DSE)
           ->assign('WRBForm', $WRBForm)
           ->assign('IP', Text::htmlentities(Text::filterXSS(Request::getRealIP())));

    $data = lokalisiereInhalt($data);
    // ModulId von einer Plugin Emailvorlage vorhanden?
    $table             = 'temailvorlage';
    $tableLocalization = 'temailvorlagesprache';
    $tableSetting      = 'temailvorlageeinstellungen';
    $where             = " cModulId = '" . $moduleID . "'";
    if (mb_strpos($moduleID, 'kPlugin') !== false) {
        [$cPlugin, $kPlugin, $cModulId] = explode('_', $moduleID);
        $tableSetting                   = 'tpluginemailvorlageeinstellungen';
        $where                          = ' kPlugin = ' . $kPlugin . " AND cModulId = '" . $cModulId . "'";
        $smarty->assign('oPluginMail', $data);
    }

    $mailTPL = $db->query(
        'SELECT *
            FROM ' . $table . '
            WHERE ' . $where,
        ReturnType::SINGLE_OBJECT
    );
    // Email aktiv?
    if (isset($mailTPL->cAktiv) && $mailTPL->cAktiv === 'N') {
        Shop::Container()->getLogService()->notice('Emailvorlage mit der ModulId ' . $moduleID . ' ist deaktiviert!');

        return false;
    }
    // Emailvorlageneinstellungen laden
    if (isset($mailTPL->kEmailvorlage) && $mailTPL->kEmailvorlage > 0) {
        $mailTPL->oEinstellung_arr = $db->selectAll(
            $tableSetting,
            'kEmailvorlage',
            $mailTPL->kEmailvorlage
        );
        if (GeneralObject::hasCount('oEinstellung_arr', $mailTPL)) {
            $mailTPL->oEinstellungAssoc_arr = [];
            foreach ($mailTPL->oEinstellung_arr as $conf) {
                $mailTPL->oEinstellungAssoc_arr[$conf->cKey] = $conf->cValue;
            }
        }
    }

    if (!isset($mailTPL->kEmailvorlage) || (int)$mailTPL->kEmailvorlage === 0) {
        Shop::Container()->getLogService()->error(
            'Keine Emailvorlage mit der ModulId ' . $moduleID .
            ' vorhanden oder diese Emailvorlage ist nicht aktiv!'
        );

        return false;
    }
    $mail->kEmailvorlage = $mailTPL->kEmailvorlage;
    $localization        = $db->select(
        $tableLocalization,
        ['kEmailvorlage', 'kSprache'],
        [(int)$mailTPL->kEmailvorlage, (int)$lang->kSprache]
    );
    $mailTPL->cBetreff   = injectSubject($data, $localization->cBetreff ?? null);
    if (isset($mailTPL->oEinstellungAssoc_arr['cEmailSenderName'])) {
        $senderName = $mailTPL->oEinstellungAssoc_arr['cEmailSenderName'];
    }
    if (isset($mailTPL->oEinstellungAssoc_arr['cEmailOut'])) {
        $senderMail = $mailTPL->oEinstellungAssoc_arr['cEmailOut'];
    }
    if (isset($mailTPL->oEinstellungAssoc_arr['cEmailCopyTo'])) {
        $sendCopy = $mailTPL->oEinstellungAssoc_arr['cEmailCopyTo'];
    }
    switch ($moduleID) {
        case MAILTEMPLATE_GUTSCHEIN:
            $smarty->assign('Gutschein', $data->tgutschein);
            break;

        case MAILTEMPLATE_BESTELLBESTAETIGUNG:
            $smarty->assign('Bestellung', $data->tbestellung)
                   ->assign('Verfuegbarkeit_arr', $data->cVerfuegbarkeit_arr ?? null);
            if (isset($data->tbestellung->Zahlungsart->cModulId)
                && mb_strlen($data->tbestellung->Zahlungsart->cModulId) > 0
            ) {
                $cModulId         = $data->tbestellung->Zahlungsart->cModulId;
                $oZahlungsartConf = $db->queryPrepared(
                    'SELECT tzahlungsartsprache.*
                        FROM tzahlungsartsprache
                        JOIN tzahlungsart
                            ON tzahlungsart.kZahlungsart = tzahlungsartsprache.kZahlungsart
                            AND tzahlungsart.cModulId = :module
                        WHERE tzahlungsartsprache.cISOSprache = :iso',
                    ['module' => $cModulId, 'iso' => $lang->cISO],
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($oZahlungsartConf->kZahlungsart) && $oZahlungsartConf->kZahlungsart > 0) {
                    $smarty->assign('Zahlungsart', $oZahlungsartConf);
                }
            }

            break;

        case MAILTEMPLATE_BESTELLUNG_AKTUALISIERT:
            $smarty->assign('Bestellung', $data->tbestellung);
            // Zahlungsart Einstellungen
            if (isset($data->tbestellung->Zahlungsart->cModulId)
                && mb_strlen($data->tbestellung->Zahlungsart->cModulId) > 0
            ) {
                $cModulId         = $data->tbestellung->Zahlungsart->cModulId;
                $oZahlungsartConf = $db->queryPrepared(
                    'SELECT tzahlungsartsprache.*
                        FROM tzahlungsartsprache
                        JOIN tzahlungsart
                            ON tzahlungsart.kZahlungsart = tzahlungsartsprache.kZahlungsart
                            AND tzahlungsart.cModulId = :module
                        WHERE tzahlungsartsprache.cISOSprache = :iso',
                    ['module' => $cModulId, 'iso' => $lang->cISO],
                    ReturnType::SINGLE_OBJECT
                );

                if (isset($oZahlungsartConf->kZahlungsart) && $oZahlungsartConf->kZahlungsart > 0) {
                    $smarty->assign('Zahlungsart', $oZahlungsartConf);
                }
            }

            break;

        case MAILTEMPLATE_PASSWORT_VERGESSEN:
            $smarty->assign('passwordResetLink', $data->passwordResetLink)
                   ->assign('Neues_Passwort', $data->neues_passwort);
            break;

        case MAILTEMPLATE_ADMINLOGIN_PASSWORT_VERGESSEN:
            $smarty->assign('passwordResetLink', $data->passwordResetLink);
            break;

        case MAILTEMPLATE_BESTELLUNG_BEZAHLT:
        case MAILTEMPLATE_BESTELLUNG_STORNO:
        case MAILTEMPLATE_BESTELLUNG_RESTORNO:
            $smarty->assign('Bestellung', $data->tbestellung);
            break;

        case MAILTEMPLATE_BESTELLUNG_TEILVERSANDT:
        case MAILTEMPLATE_BESTELLUNG_VERSANDT:
            $smarty->assign('Bestellung', $data->tbestellung);
            break;

        case MAILTEMPLATE_NEUKUNDENREGISTRIERUNG:
        case MAILTEMPLATE_ACCOUNTERSTELLUNG_DURCH_BETREIBER:
        case MAILTEMPLATE_KUNDENACCOUNT_GELOESCHT:
        case MAILTEMPLATE_KUNDENGRUPPE_ZUWEISEN:
            break;

        case MAILTEMPLATE_KUPON:
            $smarty->assign('Kupon', $data->tkupon)
                   ->assign('couponTypes', Kupon::getCouponTypes());
            break;

        case MAILTEMPLATE_KONTAKTFORMULAR:
            if (isset($config['kontakt']['kontakt_absender_name'])) {
                $senderName = $config['kontakt']['kontakt_absender_name'];
            }
            if (isset($config['kontakt']['kontakt_absender_mail'])) {
                $senderMail = $config['kontakt']['kontakt_absender_mail'];
            }
            $smarty->assign('Nachricht', $data->tnachricht);
            break;

        case MAILTEMPLATE_PRODUKTANFRAGE:
            if (isset($config['artikeldetails']['produktfrage_absender_name'])) {
                $senderName = $config['artikeldetails']['produktfrage_absender_name'];
            }
            if (isset($config['artikeldetails']['produktfrage_absender_mail'])) {
                $senderMail = $config['artikeldetails']['produktfrage_absender_mail'];
            }
            $smarty->assign('Nachricht', $data->tnachricht)
                   ->assign('Artikel', $data->tartikel);
            break;

        case MAILTEMPLATE_PRODUKT_WIEDER_VERFUEGBAR:
            $smarty->assign('Benachrichtigung', $data->tverfuegbarkeitsbenachrichtigung)
                   ->assign('Artikel', $data->tartikel);
            break;

        case MAILTEMPLATE_WUNSCHLISTE:
            $smarty->assign('Wunschliste', $data->twunschliste);
            break;

        case MAILTEMPLATE_BEWERTUNGERINNERUNG:
            $smarty->assign('Bestellung', $data->tbestellung);
            break;

        case MAILTEMPLATE_NEWSLETTERANMELDEN:
            $smarty->assign('NewsletterEmpfaenger', $data->NewsletterEmpfaenger);
            break;

        case MAILTEMPLATE_STATUSEMAIL:
            $data->mail->toName = $data->tfirma->cName . ' ' . $data->cIntervall;
            $mailTPL->cBetreff  = $data->tfirma->cName . ' ' . $data->cIntervall;
            $smarty->assign('oMailObjekt', $data);
            break;

        case MAILTEMPLATE_CHECKBOX_SHOPBETREIBER:
            $smarty->assign('oCheckBox', $data->oCheckBox)
                   ->assign('oKunde', $data->oKunde)
                   ->assign('cAnzeigeOrt', $data->cAnzeigeOrt)
                   ->assign('cAnzeigeOrt', $data->cAnzeigeOrt)
                   ->assign('oSprache', $lang);
            if (empty($data->oKunde->cVorname) && empty($data->oKunde->cNachname)) {
                $subjectLineCustomer = $data->oKunde->cMail;
            } else {
                $subjectLineCustomer = $data->oKunde->cVorname . ' ' . $data->oKunde->cNachname;
            }
            $mailTPL->cBetreff = $data->oCheckBox->cName .
                ' - ' . $subjectLineCustomer;
            break;
        case MAILTEMPLATE_BEWERTUNG_GUTHABEN:
            $waehrung = $db->select('twaehrung', 'cStandard', 'Y');

            $data->oBewertungGuthabenBonus->fGuthabenBonusLocalized = Preise::getLocalizedPriceString(
                $data->oBewertungGuthabenBonus->fGuthabenBonus,
                $waehrung,
                false
            );
            $smarty->assign('oKunde', $data->tkunde)
                   ->assign('oBewertungGuthabenBonus', $data->oBewertungGuthabenBonus);
            break;
    }

    $pluginBody = isset($mailTPL->kPlugin) && $mailTPL->kPlugin > 0 ? '_' . $mailTPL->kPlugin : '';

    executeHook(HOOK_MAILTOOLS_INC_SWITCH, [
        'mailsmarty'    => &$smarty,
        'mail'          => &$mail,
        'kEmailvorlage' => $mailTPL->kEmailvorlage,
        'kSprache'      => $lang->kSprache,
        'cPluginBody'   => $pluginBody,
        'Emailvorlage'  => $mailTPL
    ]);
    if ($mailTPL->cMailTyp === 'text/html' || $mailTPL->cMailTyp === 'html') {
        $bodyHtml = $smarty->fetch('db:html_' . $mailTPL->kEmailvorlage . '_' . $lang->kSprache . $pluginBody);
    }
    $bodyText = $smarty->fetch('db:text_' . $mailTPL->kEmailvorlage . '_' . $lang->kSprache . $pluginBody);
    // AKZ, AGB und WRB anhängen falls eingestellt
    if ((int)$mailTPL->nAKZ === 1) {
        $akzHtml = $smarty->fetch('db:html_core_jtl_anbieterkennzeichnung_' . $lang->kSprache . $pluginBody);
        $akzText = $smarty->fetch('db:text_core_jtl_anbieterkennzeichnung_' . $lang->kSprache . $pluginBody);

        if (mb_strlen($bodyHtml) > 0) {
            $bodyHtml .= '<br /><br />' . $akzHtml;
        }
        $bodyText .= "\n\n" . $akzText;
    }
    if ((int)$mailTPL->nWRB === 1) {
        $heading = Shop::Lang()->get('wrb');
        if (mb_strlen($bodyHtml) > 0) {
            $bodyHtml .= '<br /><br /><h3>' . $heading . '</h3>' . $WRB->cContentHtml;
        }
        $bodyText .= "\n\n" . $heading . "\n\n" . $WRB->cContentText;
    }
    if ((int)$mailTPL->nWRBForm === 1) {
        $heading = Shop::Lang()->get('wrbform');
        if (mb_strlen($bodyHtml) > 0) {
            $bodyHtml .= '<br /><br /><h3>' . $heading . '</h3>' . $WRBForm->cContentHtml;
        }
        $bodyText .= "\n\n" . $heading . "\n\n" . $WRBForm->cContentText;
    }
    if ((int)$mailTPL->nAGB === 1) {
        $heading = Shop::Lang()->get('agb');
        if (mb_strlen($bodyHtml) > 0) {
            $bodyHtml .= '<br /><br /><h3>' . $heading . '</h3>' . $AGB->cContentHtml;
        }
        $bodyText .= "\n\n" . $heading . "\n\n" . $AGB->cContentText;
    }
    if ((int)$mailTPL->nDSE === 1) {
        $heading = Shop::Lang()->get('dse');
        if (mb_strlen($bodyHtml) > 0) {
            $bodyHtml .= '<br /><br /><h3>' . $heading . '</h3>' . $DSE->cContentHtml;
        }
        $bodyText .= "\n\n" . $heading . "\n\n" . $DSE->cContentText;
    }
    if (isset($data->tkunde->cMail)) {
        $mail->toEmail = $data->tkunde->cMail;
        $mail->toName  = $data->tkunde->cVorname . ' ' . $data->tkunde->cNachname;
    } elseif (isset($data->NewsletterEmpfaenger->cEmail) && mb_strlen($data->NewsletterEmpfaenger->cEmail) > 0) {
        $mail->toEmail = $data->NewsletterEmpfaenger->cEmail;
    }
    //some mail servers seem to have problems with very long lines - wordwrap() if necessary
    $hasLongLines = false;
    foreach (preg_split('/((\r?\n)|(\r\n?))/', $bodyHtml) as $line) {
        if (mb_strlen($line) > 987) {
            $hasLongLines = true;
            break;
        }
    }
    if ($hasLongLines) {
        $bodyHtml = wordwrap($bodyHtml, 900);
    }
    $hasLongLines = false;
    foreach (preg_split('/((\r?\n)|(\r\n?))/', $bodyText) as $line) {
        if (mb_strlen($line) > 987) {
            $hasLongLines = true;
            break;
        }
    }
    if ($hasLongLines) {
        $bodyText = wordwrap($bodyText, 900);
    }

    $mail->fromEmail     = $senderMail;
    $mail->fromName      = $senderName;
    $mail->replyToEmail  = $senderMail;
    $mail->replyToName   = $senderName;
    $mail->subject       = Text::htmlentitydecode($mailTPL->cBetreff);
    $mail->bodyText      = $bodyText;
    $mail->bodyHtml      = $bodyHtml;
    $mail->lang          = $lang->cISO;
    $mail->methode       = $config['emails']['email_methode'];
    $mail->sendmail_pfad = $config['emails']['email_sendmail_pfad'];
    $mail->smtp_hostname = $config['emails']['email_smtp_hostname'];
    $mail->smtp_port     = $config['emails']['email_smtp_port'];
    $mail->smtp_auth     = $config['emails']['email_smtp_auth'];
    $mail->smtp_user     = $config['emails']['email_smtp_user'];
    $mail->smtp_pass     = $config['emails']['email_smtp_pass'];
    $mail->SMTPSecure    = $config['emails']['email_smtp_verschluesselung'];
    $mail->SMTPAutoTLS   = !empty($mail->SMTPSecure);

    $smarty->assign('absender_name', $senderName)
               ->assign('absender_mail', $senderMail);
    if (isset($data->mail->fromEmail)) {
        $mail->fromEmail = $data->mail->fromEmail;
    }
    if (isset($data->mail->fromName)) {
        $mail->fromName = $data->mail->fromName;
    }
    if (isset($data->mail->toEmail)) {
        $mail->toEmail = $data->mail->toEmail;
    }
    if (isset($data->mail->toName)) {
        $mail->toName = $data->mail->toName;
    }
    if (isset($data->mail->replyToEmail)) {
        $mail->replyToEmail = $data->mail->replyToEmail;
    }
    if (isset($data->mail->replyToName)) {
        $mail->replyToName = $data->mail->replyToName;
    }
    if (isset($localization->cPDFS) && mb_strlen($localization->cPDFS) > 0) {
        $mail->cPDFS_arr = getPDFAttachments($localization->cPDFS, $localization->cPDFNames);
    }
    executeHook(HOOK_MAILTOOLS_SENDEMAIL_ENDE, [
        'mailsmarty'    => &$smarty,
        'mail'          => &$mail,
        'kEmailvorlage' => $mailTPL->kEmailvorlage,
        'kSprache'      => $lang->kSprache,
        'cPluginBody'   => $pluginBody,
        'Emailvorlage'  => $mailTPL
    ]);

    verschickeMail($mail);

    if ($sendCopy) {
        $copyAddresses = Text::parseSSK($sendCopy);
        foreach ($copyAddresses as $copyAddress) {
            $mail->toEmail      = $copyAddress;
            $mail->toName       = $copyAddress;
            $mail->fromEmail    = $senderMail;
            $mail->fromName     = $senderName;
            $mail->replyToEmail = $data->tkunde->cMail;
            $mail->replyToName  = $data->tkunde->cVorname . ' ' . $data->tkunde->cNachname;
            verschickeMail($mail);
        }
    }
    if (isset($data->oKopie, $data->oKopie->cToMail) && mb_strlen($data->oKopie->cToMail) > 0) {
        $mail->toEmail      = $data->oKopie->cToMail;
        $mail->toName       = $data->oKopie->cToMail;
        $mail->fromEmail    = $senderMail;
        $mail->fromName     = $senderName;
        $mail->replyToEmail = $data->tkunde->cMail;
        $mail->replyToName  = $data->tkunde->cVorname . ' ' . $data->tkunde->cNachname;
        verschickeMail($mail);
    }

    return $mail;
}

/**
 * @param string $cEmail
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeGlobaleEmailBlacklist($cEmail)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $blackList = Shop::Container()->getDB()->select('temailblacklist', 'cEmail', $cEmail);
    if (isset($blackList->cEmail) && mb_strlen($blackList->cEmail) > 0) {
        $block                = new stdClass();
        $block->cEmail        = $blackList->cEmail;
        $block->dLetzterBlock = 'NOW()';

        Shop::Container()->getDB()->insert('temailblacklistblock', $block);

        return true;
    }

    return false;
}

/**
 * @param object $mail
 * @deprecated since 5.0.0
 */
function verschickeMail($mail)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $sent          = false;
    $kEmailvorlage = null;
    $conf          = Shop::getSettings([CONF_EMAILBLACKLIST]);
    if ($conf['emailblacklist']['blacklist_benutzen'] === 'Y' && pruefeGlobaleEmailBlacklist($mail->toEmail)) {
        return;
    }
    if (isset($mail->kEmailvorlage)) {
        if ((int)$mail->kEmailvorlage > 0) {
            $kEmailvorlage = (int)$mail->kEmailvorlage;
        }
        unset($mail->kEmailvorlage);
    }
    $mail->bodyText  = Text::htmlentitydecode(str_replace('&euro;', 'EUR', $mail->bodyText), ENT_NOQUOTES);
    $mail->cFehler   = '';
    $GLOBALS['mail'] = $mail; // Plugin Work Around
    if (!$mail->methode) {
        SendNiceMailReply(
            $mail->fromName,
            $mail->fromEmail,
            $mail->fromEmail,
            $mail->toEmail,
            $mail->subject,
            $mail->bodyText,
            $mail->bodyHtml
        );
    } else {
        $phpmailer = new \PHPMailer\PHPMailer\PHPMailer();
        $lang      = ($mail->lang === 'DE' || $mail->lang === 'ger') ? 'de' : 'eng';
        $phpmailer->setLanguage($lang, PFAD_ROOT . PFAD_PHPMAILER . 'language/');
        $phpmailer->CharSet = JTL_CHARSET;
        $phpmailer->Timeout = SOCKET_TIMEOUT;
        $phpmailer->Sender  = $mail->fromEmail;
        $phpmailer->setFrom($mail->fromEmail, $mail->fromName);
        $phpmailer->addAddress($mail->toEmail, (!empty($mail->toName) ? $mail->toName : ''));
        $phpmailer->addReplyTo($mail->replyToEmail, $mail->replyToName);
        $phpmailer->Subject = $mail->subject;

        switch ($mail->methode) {
            case 'mail':
                $phpmailer->isMail();
                break;
            case 'sendmail':
                $phpmailer->isSendmail();
                $phpmailer->Sendmail = $mail->sendmail_pfad;
                break;
            case 'qmail':
                $phpmailer->isQmail();
                break;
            case 'smtp':
                $phpmailer->isSMTP();
                $phpmailer->Host          = $mail->smtp_hostname;
                $phpmailer->Port          = $mail->smtp_port;
                $phpmailer->SMTPKeepAlive = true;
                $phpmailer->SMTPAuth      = $mail->smtp_auth;
                $phpmailer->Username      = $mail->smtp_user;
                $phpmailer->Password      = $mail->smtp_pass;
                $phpmailer->SMTPSecure    = $mail->SMTPSecure;
                $phpmailer->SMTPAutoTLS   = $mail->SMTPAutoTLS
                    ?? (empty($mail->SMTPSecure)
                        ? false
                        : true);
                break;
        }
        if ($mail->bodyHtml) {
            $phpmailer->isHTML(true);
            $phpmailer->Body    = $mail->bodyHtml;
            $phpmailer->AltBody = $mail->bodyText;
        } else {
            $phpmailer->isHTML(false);
            $phpmailer->Body = $mail->bodyText;
        }

        if (isset($mail->cPDFS_arr) && count($mail->cPDFS_arr) > 0) {
            $uploadDir = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;
            foreach ($mail->cPDFS_arr as $i => $pdf) {
                $phpmailer->addAttachment(
                    $uploadDir . $pdf->fileName,
                    $pdf->publicName . '.pdf',
                    'base64',
                    'application/pdf'
                );
            }
        }
        if (isset($mail->oAttachment_arr) && count($mail->oAttachment_arr) > 0) {
            foreach ($mail->oAttachment_arr as $attachment) {
                if (empty($attachment->cEncoding)) {
                    $attachment->cEncoding = 'base64';
                }
                if (empty($attachment->cType)) {
                    $attachment->cType = 'application/octet-stream';
                }
                $phpmailer->addAttachment(
                    $attachment->cFilePath,
                    $attachment->cName,
                    $attachment->cEncoding,
                    $attachment->cType
                );
            }
        }

        $sent          = $phpmailer->send();
        $mail->cFehler = $phpmailer->ErrorInfo;
    }
    if ($sent) {
        $history = new Emailhistory();
        $history->setEmailvorlage($kEmailvorlage ?? 0)
                ->setSubject($mail->subject)
                ->setFromName($mail->fromName)
                ->setFromEmail($mail->fromEmail)
                ->setToName($mail->toName ?? '')
                ->setToEmail($mail->toEmail)
                ->setSent('NOW()')
                ->save();
    } else {
        Shop::Container()->getLogService()->error('Email konnte nicht versendet werden! Fehler: ' . $mail->cFehler);
    }

    executeHook(HOOK_MAILTOOLS_VERSCHICKEMAIL_GESENDET);
}

/**
 * @param object $object
 * @param string $subject
 * @return mixed
 * @deprecated since 5.0.0
 */
function injectSubject($object, $subject)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $a     = [];
    $b     = [];
    $keys1 = array_keys(get_object_vars($object));
    if (!is_array($keys1)) {
        return $subject;
    }
    foreach ($keys1 as $obj) {
        if (is_object($object->$obj) && is_array(get_object_vars($object->$obj))) {
            $keys2 = array_keys(get_object_vars($object->$obj));
            if (is_array($keys2)) {
                foreach ($keys2 as $member) {
                    if ($member[0] !== 'k'
                        && !is_array($object->$obj->$member)
                        && !is_object($object->$obj->$member)
                    ) {
                        $a[] = '#'
                            . mb_convert_case(mb_substr($obj, 1), MB_CASE_LOWER)
                            . '.'
                            . mb_convert_case(mb_substr($member, 1), MB_CASE_LOWER)
                            . '#';
                        $b[] = $object->$obj->$member;
                    }
                }
            }
        }
    }

    return str_replace($a, $b, $subject);
}

/**
 * @param object $object
 * @return mixed
 * @deprecated since 5.0.0
 */
function lokalisiereInhalt($object)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (isset($object->tgutschein->fWert) && $object->tgutschein->fWert != 0) {
        $object->tgutschein->cLocalizedWert = Preise::getLocalizedPriceString($object->tgutschein->fWert, null, false);
    }

    return $object;
}

/**
 * @param object            $lang
 * @param stdClass|Customer $customer
 * @return mixed
 * @deprecated since 5.0.0
 */
function lokalisiereKunde($lang, $customer)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (Shop::Lang()->gibISO() !== $lang->cISO) {
        Shop::Lang()->setzeSprache($lang->cISO);
    }
    if (isset($customer->cAnrede)) {
        if ($customer->cAnrede === 'w') {
            $customer->cAnredeLocalized = Shop::Lang()->get('salutationW');
        } elseif ($customer->cAnrede === 'm') {
            $customer->cAnredeLocalized = Shop::Lang()->get('salutationM');
        } else {
            $customer->cAnredeLocalized = Shop::Lang()->get('salutationGeneral');
        }
    }
    $customer = GeneralObject::deepCopy($customer);

    if (isset($_SESSION['Kunde'], $customer->cLand)) {
        $_SESSION['Kunde']->cLand = $customer->cLand;
    }

    return $customer;
}

/**
 * @param object        $lang
 * @param Lieferadresse $deliveryAddress
 * @return object
 * @deprecated since 5.0.0
 */
function lokalisiereLieferadresse($lang, $deliveryAddress)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $langRow = (mb_convert_case($lang->cISO, MB_CASE_LOWER) === 'ger') ? 'cDeutsch' : 'cEnglisch';
    $land    = Shop::Container()->getDB()->select(
        'tland',
        'cISO',
        $deliveryAddress->cLand,
        null,
        null,
        null,
        null,
        false,
        $langRow . ' AS cName, cISO'
    );
    if (!empty($land->cName)) {
        $deliveryAddress->cLand = $land->cName;
    }

    return $deliveryAddress;
}

/**
 * @param string $pdfString
 * @param string $nameString
 * @return stdClass[]
 * @deprecated since 5.0.0
 */
function getPDFAttachments($pdfString, $nameString)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $result    = [];
    $pdfData   = Text::parseSSK(trim($pdfString, ";\t\n\r\0"));
    $names     = Text::parseSSK(trim($nameString, ";\t\n\r\0"));
    $uploadDir = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;
    foreach ($pdfData as $key => $pdfFile) {
        if (!empty($pdfFile) && file_exists($uploadDir . $pdfFile)) {
            $result[] = (object)[
                'fileName'   => $pdfFile,
                'publicName' => $names[$key] ?? $pdfFile,
            ];
        }
    }

    return $result;
}

/**
 * mail functions
 *
 * @param string $fromName
 * @param string $fromMail
 * @param string $replyTo
 * @param string $to
 * @param string $subject
 * @param string $text
 * @param string $html
 * @return bool
 * @deprecated since 5.0.0
 */
function SendNiceMailReply($fromName, $fromMail, $replyTo, $to, $subject, $text, $html = '')
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $eol = "\n";
    if (PHP_OS_FAMILY === 'Windows') {
        $eol = "\r\n";
    } elseif (PHP_OS_FAMILY === 'Darwin') {
        $eol = "\r";
    }

    $fromName = Text::unhtmlentities($fromName);
    $fromMail = Text::unhtmlentities($fromMail);
    $subject  = Text::unhtmlentities($subject);
    $text     = Text::unhtmlentities($text);

    $text = $text ?: 'Sorry, but you need an html mailer to read this mail.';

    if (empty($to)) {
        return false;
    }
    $mime_boundary = md5(time()) . '_jtlshop2';
    $headers       = '';
    if (mb_strpos($to, 'freenet')) {
        $headers .= 'From: ' . mb_convert_case($fromMail, MB_CASE_LOWER) . $eol;
    } else {
        $headers .= 'From: ' . $fromName . ' <' . mb_convert_case($fromMail, MB_CASE_LOWER) . '>' . $eol;
    }
    $headers .= 'Reply-To: ' . mb_convert_case($replyTo, MB_CASE_LOWER) . $eol;
    $headers .= 'MIME-Version: 1.0' . $eol;
    if (!$html) {
        $headers .= 'Content-Type: text/plain; charset=' . JTL_CHARSET . $eol;
        $headers .= 'Content-Transfer-Encoding: 8bit' . $eol . $eol;
    }
    $msg = $text;
    if ($html) {
        $msg      = '';
        $headers .= 'Content-Type: multipart/alternative; boundary=' . $mime_boundary . $eol;
        // text version
        $msg .= '--' . $mime_boundary . $eol;
        $msg .= 'Content-Type: text/plain; charset=' . JTL_CHARSET . $eol;
        $msg .= 'Content-Transfer-Encoding: 8bit' . $eol . $eol;
        $msg .= $text . $eol;
        // HTML version
        $msg .= '--' . $mime_boundary . $eol;
        $msg .= 'Content-Type: text/html; charset=' . JTL_CHARSET . $eol;
        $msg .= 'Content-Transfer-Encoding: 8bit' . $eol . $eol;
        $msg .= $html . $eol . $eol;
        $msg .= '--' . $mime_boundary . '--' . $eol . $eol;
    }
    mail($to, encode_iso88591($subject), $msg, $headers);

    return true;
}

/**
 * @param string $string
 * @return string
 * @deprecated since 5.0.0
 */
function encode_iso88591($string)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $text = '=?' . JTL_CHARSET . '?Q?';
    $max  = mb_strlen($string);
    for ($i = 0; $i < $max; $i++) {
        $val = mb_ord($string[$i]);
        if ($val > 127 || $val === 63) {
            $val   = dechex($val);
            $text .= '=' . $val;
        } else {
            $text .= $string[$i];
        }
    }
    $text .= '?=';

    return $text;
}
