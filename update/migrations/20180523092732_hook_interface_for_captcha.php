<?php
/**
 * Hook interface for captcha
 *
 * @author fp
 * @created Wed, 23 May 2018 09:27:32 +0200
 */

use JTL\Shop;
use JTL\Update\IMigration;
use JTL\Update\Migration;

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'pluginverwaltung_inc.php';

/**
 * Class Migration_20180523092732
 */
class Migration_20180523092732 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Hook interface for captcha';

    private $settingsMap = [
        'global_google_recaptcha_public'  => 'jtl_google_recaptcha_sitekey',
        'global_google_recaptcha_private' => 'jtl_google_recaptcha_secretkey',
    ];

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeConfig('anti_spam_method');
        $this->removeConfig('global_google_recaptcha_public');
        $this->removeConfig('global_google_recaptcha_private');

        $this->setLocalization('ger', 'global', 'captcha_code_active', 'Spamschutz aktiv');
        $this->setLocalization('eng', 'global', 'captcha_code_active', 'Spam protection active');

        Shop::Container()->getCache()->flushTags(CACHING_GROUP_OPTION);
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setConfig(
            'anti_spam_method',
            'N',
            CONF_GLOBAL,
            'Spamschutz-Methode',
            'selectbox',
            520,
            (object)[
                'cBeschreibung' => 'Die Art des Spamschutzes',
                'inputOptions'  => [
                    'N' => 'keine',
                    '1' => 'Captcha Sicherheitsstufe 1 (im Evo reCaptcha)',
                    '2' => 'Captcha Sicherheitsstufe 2 (im Evo reCaptcha)',
                    '3' => 'Captcha Sicherheitsstufe 3 (im Evo reCaptcha)',
                    '4' => 'Rechenaufgabe (im Evo reCaptcha)',
                    '5' => 'unsichtbarer Sicherheitstoken (im Evo reCaptcha)',
                    '7' => 'Google reCaptcha',
                ]
            ],
            true
        );
        $this->setConfig(
            'global_google_recaptcha_public',
            '',
            CONF_GLOBAL,
            'Google reCAPTCHA Websiteschlüssel',
            'text',
            522,
            (object)[
                'cBeschreibung' => 'Sie müssen Ihre Domain auf https://www.google.com/recaptcha registrieren. ' .
                    'Anschließend erhalten Sie von Google Ihren Website- und Geheimen Schlüssel.',
            ]
        );
        $this->setConfig(
            'global_google_recaptcha_private',
            '',
            CONF_GLOBAL,
            'Google reCAPTCHA Geheimer Schlüssel',
            'text',
            523,
            (object)[
                'cBeschreibung' => 'Sie müssen Ihre Domain auf https://www.google.com/recaptcha registrieren. ' .
                    'Anschließend erhalten Sie von Google Ihren Website- und Geheimen Schlüssel.',
            ]
        );

        $this->removeLocalization('captcha_code_active');

        Shop::Container()->getCache()->flushTags(CACHING_GROUP_OPTION);
    }
}
