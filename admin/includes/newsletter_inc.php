<?php

use JTL\Catalog\Product\Artikel;
use JTL\DB\ReturnType;
use JTL\Language\LanguageHelper;
use JTL\Shop;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use JTL\Smarty\MailSmarty;

/**
 * @param array $conf
 * @return JTLSmarty
 * @deprecated since 5.0.0
 */
function bereiteNewsletterVor($conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $db         = Shop::Container()->getDB();
    $mailSmarty = new MailSmarty($db, ContextType::NEWSLETTER);

    return $mailSmarty
        ->assign('Firma', $db->query(
            'SELECT *  FROM tfirma',
            ReturnType::SINGLE_OBJECT
        ))
       ->assign('URL_SHOP', Shop::getURL())
       ->assign('Einstellungen', $conf);
}

/**
 * @param JTLSmarty $mailSmarty
 * @param object    $newsletter
 * @param array     $conf
 * @param stdClass  $recipients
 * @param array     $products
 * @param array     $manufacturers
 * @param array     $categories
 * @param string    $campaign
 * @param string    $oKunde
 * @return string|bool
 * @deprecated since 5.0.0
 */
function versendeNewsletter(
    $mailSmarty,
    $newsletter,
    $conf,
    $recipients,
    $products = [],
    $manufacturers = [],
    $categories = [],
    $campaign = '',
    $oKunde = ''
) {
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
    $mailSmarty->assign('oNewsletter', $newsletter)
               ->assign('Emailempfaenger', $recipients)
               ->assign('Kunde', $oKunde)
               ->assign('Artikelliste', $products)
               ->assign('Herstellerliste', $manufacturers)
               ->assign('Kategorieliste', $categories)
               ->assign('Kampagne', $campaign)
               ->assign(
                   'cNewsletterURL',
                   Shop::getURL() .
                   '/newsletter.php?show=' .
                   ($newsletter->kNewsletter ?? '0')
               );
    $net      = 0;
    $bodyHtml = '';
    if (isset($oKunde->kKunde) && $oKunde->kKunde > 0) {
        $oKundengruppe = Shop::Container()->getDB()->query(
            'SELECT tkundengruppe.nNettoPreise
                FROM tkunde
                JOIN tkundengruppe
                    ON tkundengruppe.kKundengruppe = tkunde.kKundengruppe
                WHERE tkunde.kKunde = ' . (int)$oKunde->kKunde,
            ReturnType::SINGLE_OBJECT
        );
        if (isset($oKundengruppe->nNettoPreise)) {
            $net = $oKundengruppe->nNettoPreise;
        }
    }

    $mailSmarty->assign('NettoPreise', $net);

    $cPixel = '';
    if (isset($campaign->kKampagne) && $campaign->kKampagne > 0) {
        $cPixel = '<br /><img src="' . Shop::getURL() . '/' . PFAD_INCLUDES .
            'newslettertracker.php?kK=' . $campaign->kKampagne .
            '&kN=' . ($newsletter->kNewsletter ?? 0) . '&kNE=' .
            ($recipients->kNewsletterEmpfaenger ?? 0) . '" alt="Newsletter" />';
    }

    $type = 'VL';
    $nKey = $newsletter->kNewsletterVorlage ?? 0;
    if (isset($newsletter->kNewsletter) && $newsletter->kNewsletter > 0) {
        $type = 'NL';
        $nKey = $newsletter->kNewsletter;
    }
    if ($newsletter->cArt === 'text/html' || $newsletter->cArt === 'html') {
        try {
            $bodyHtml = $mailSmarty->fetch('db:' . $type . '_' . $nKey . '_html') . $cPixel;
        } catch (Exception $e) {
            Shop::Smarty()->assign('oSmartyError', $e->getMessage());

            return $e->getMessage();
        }
    }
    try {
        $bodyText = $mailSmarty->fetch('db:' . $type . '_' . $nKey . '_text');
    } catch (Exception $e) {
        Shop::Smarty()->assign('oSmartyError', $e->getMessage());

        return $e->getMessage();
    }
    $mail          = new stdClass();
    $mail->toEmail = $recipients->cEmail;
    $mail->toName  = ($recipients->cVorname ?? '') . ' ' . ($recipients->cNachname ?? '');
    if (isset($oKunde->kKunde) && $oKunde->kKunde > 0) {
        $mail->toName = ($oKunde->cVorname ?? '') . ' ' . ($oKunde->cNachname ?? '');
    }

    $mail->fromEmail     = $conf['newsletter']['newsletter_emailadresse'];
    $mail->fromName      = $conf['newsletter']['newsletter_emailabsender'];
    $mail->replyToEmail  = $conf['newsletter']['newsletter_emailadresse'];
    $mail->replyToName   = $conf['newsletter']['newsletter_emailabsender'];
    $mail->subject       = $newsletter->cBetreff;
    $mail->bodyText      = $bodyText;
    $mail->bodyHtml      = $bodyHtml;
    $mail->lang          = LanguageHelper::getIsoFromLangID((int)$newsletter->kSprache)->cISO;
    $mail->methode       = $conf['newsletter']['newsletter_emailmethode'];
    $mail->sendmail_pfad = $conf['newsletter']['newsletter_sendmailpfad'];
    $mail->smtp_hostname = $conf['newsletter']['newsletter_smtp_host'];
    $mail->smtp_port     = $conf['newsletter']['newsletter_smtp_port'];
    $mail->smtp_auth     = $conf['newsletter']['newsletter_smtp_authnutzen'];
    $mail->smtp_user     = $conf['newsletter']['newsletter_smtp_benutzer'];
    $mail->smtp_pass     = $conf['newsletter']['newsletter_smtp_pass'];
    $mail->SMTPSecure    = $conf['newsletter']['newsletter_smtp_verschluesselung'];
    verschickeMail($mail);

    return true;
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function gibStaticHtml()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return '';
}

/**
 * @return array|null|stdClass
 * @deprecated since 5.0.0
 */
function speicherVorlage()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return null;
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function speicherVorlageStd(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function mappeFileTyp(): string
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return '.jpg';
}

/**
 * @param string $text
 * @return string
 * @deprecated since 5.0.0
 */
function br2nl(string $text): string
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return str_replace(['<br>', '<br />', '<br/>'], "\n", $text);
}

/**
 * @return mixed|string
 * @deprecated since 5.0.0
 */
function mappeVorlageStdVar()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return '';
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function pruefeVorlageStd(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function pruefeVorlage(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return null
 * @deprecated since 5.0.0
 */
function holeNewslettervorlageStd()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return null;
}

/**
 * @return stdClass
 * @deprecated since 5.0.0
 */
function explodecArtikel(): stdClass
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $productData               = new stdClass();
    $productData->kArtikel_arr = [];
    $productData->cArtNr_arr   = [];

    return $productData;
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function explodecKundengruppe(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function holeArtikelnummer(): string
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return '';
}

/**
 * @return stdClass
 * @deprecated since 5.0.0
 */
function getNewsletterEmpfaenger(): stdClass
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return new stdClass();
}

/**
 * @param string $time
 * @return stdClass
 * @deprecated since 5.0.0
 */
function baueZeitAusDB($time)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $res = new stdClass();

    if (mb_strlen($time) > 0) {
        [$dDatum, $dUhrzeit]     = explode(' ', $time);
        [$dJahr, $dMonat, $dTag] = explode('-', $dDatum);
        [$dStunde, $dMinute]     = explode(':', $dUhrzeit);

        $res->dZeit     = $dTag . '.' . $dMonat . '.' . $dJahr . ' ' . $dStunde . ':' . $dMinute;
        $res->cZeit_arr = [$dTag, $dMonat, $dJahr, $dStunde, $dMinute];
    }

    return $res;
}

/**
 * @return int
 * @deprecated since 5.0.0
 */
function holeAbonnentenAnzahl(): int
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function holeAbonnenten(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function loescheAbonnenten(): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function aktiviereAbonnenten(): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @return int
 * @deprecated since 5.0.0
 */
function gibAbonnent(): int
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function loescheAbonnent(): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function baueNewsletterVorschau(): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * Braucht ein String von Keys oder Nummern und gibt ein Array mit kKeys zurueck
 * Der String muss ';' separiert sein z.b. '1;2;3'
 *
 * @param string $keyName
 * @param bool   $productNo
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibAHKKeys($keyName, $productNo = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $res  = [];
    $keys = explode(';', $keyName);
    if (!is_array($keys) || count($keys) === 0) {
        return $res;
    }
    $res = array_filter($keys, static function ($e) {
        return mb_strlen($e) > 0;
    });
    if ($productNo) {
        $res = array_map(static function ($e) {
            return "'" . $e . "'";
        }, $res);
        if (count($res) > 0) {
            $artNoData = Shop::Container()->getDB()->query(
                'SELECT kArtikel
                FROM tartikel
                WHERE cArtNr IN (' . implode(',', $res) . ')
                    AND kEigenschaftKombi = 0',
                ReturnType::ARRAY_OF_OBJECTS
            );
            $res       = array_map(static function ($e) {
                return (int)$e->kArtikel;
            }, $artNoData);
        }
    } else {
        $res = array_map('\intval', $res);
    }

    return $res;
}

/**
 * @return Artikel[]
 * @deprecated since 5.0.0
 */
function gibArtikelObjekte(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return array
 */
function gibHerstellerObjekte()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibKategorieObjekte()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}
