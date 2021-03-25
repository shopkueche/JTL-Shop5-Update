<?php declare(strict_types=1);

/**
 * @author mh
 * @created Thu, 19 Mar 2020 19:12:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200319145600
 */
class Migration_20200319145600 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add consent lang';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("INSERT INTO tsprachsektion (cName) VALUES ('consent');");

        $this->setLocalization('ger', 'consent', 'howWeUseCookies', 'Wie wir Cookies & Co nutzen');
        $this->setLocalization('eng', 'consent', 'howWeUseCookies', 'How we use cookies and other data');
        $this->setLocalization('ger', 'consent', 'cookieSettings', 'Datenschutz-Einstellungen');
        $this->setLocalization('eng', 'consent', 'cookieSettings', 'Data privacy settings');
        $this->setLocalization('ger', 'consent', 'selectAll', 'Alle ab-/auswählen');
        $this->setLocalization('eng', 'consent', 'selectAll', 'Select/Deselect all');
        $this->setLocalization('ger', 'consent', 'apply', 'Übernehmen');
        $this->setLocalization('eng', 'consent', 'apply', 'Apply');
        $this->setLocalization('ger', 'consent', 'moreInformation', 'Weitere Informationen');
        $this->setLocalization('eng', 'consent', 'moreInformation', 'Further information');
        $this->setLocalization('ger', 'consent', 'description', 'Beschreibung');
        $this->setLocalization('eng', 'consent', 'description', 'Description');
        $this->setLocalization('ger', 'consent', 'company', 'Verarbeitende Firma');
        $this->setLocalization('eng', 'consent', 'company', 'Processing company');
        $this->setLocalization('ger', 'consent', 'terms', 'Nutzungsbedingungen');
        $this->setLocalization('eng', 'consent', 'terms', 'Terms of use');
        $this->setLocalization('ger', 'consent', 'link', 'Link');
        $this->setLocalization('eng', 'consent', 'link', 'Link');
        $this->setLocalization('ger', 'consent', 'dataProtection', 'Datenschutz-Einstellungen');
        $this->setLocalization('eng', 'consent', 'dataProtection', 'Data privacy settings');
        $this->setLocalization('ger', 'consent', 'consentOnce', 'Einmalig zustimmen');
        $this->setLocalization('eng', 'consent', 'consentOnce', 'Agree for one session');
        $this->setLocalization('ger', 'consent', 'consentAlways', 'Dauerhaft zustimmen');
        $this->setLocalization('eng', 'consent', 'consentAlways', 'Agree for all sessions');
        $this->setLocalization('ger', 'consent', 'consentAll', 'Alle akzeptieren');
        $this->setLocalization('eng', 'consent', 'consentAll', 'Accept all');
        $this->setLocalization('ger', 'consent', 'close', 'Schließen');
        $this->setLocalization('eng', 'consent', 'close', 'Close');
        $this->setLocalization('ger', 'consent', 'configure', 'Konfigurieren');
        $this->setLocalization('eng', 'consent', 'configure', 'Configuration');
        $this->setLocalization('ger', 'consent', 'consentDescription', 'Durch Klicken auf „Alle akzeptieren“ gestatten ' .
            'Sie den Einsatz folgender Dienste auf unserer Website: %s. Sie können die Einstellung jederzeit ändern ' .
            '(Fingerabdruck-Icon links unten). Weitere Details finden Sie unter <i>Konfigurieren</i> und in unserer ' .
            '<a href="%s" target="_blank">Datenschutzerklärung</a>.');
        $this->setLocalization('eng', 'consent', 'consentDescription', 'By selecting "Accept all", you give us permission ' .
            'to use the following services on our website: %s. You can change the settings at any time (fingerprint icon ' .
            'in the bottom left corner). For further details, please see Individual configuration and our ' .
            '<a href="%s" target="_blank">Privacy notice</a>.');
        $this->setLocalization('ger', 'consent', 'dataProtectionDescription', 'Sie möchten diesen Inhalt sehen? Aktivieren ' .
            'Sie den gewünschten Inhalt einmalig oder legen Sie eine dauerhafte Freigabe fest. Bei Zustimmung werden Daten ' .
            'beim genannten Drittanbieter abgerufen. Dabei werden unter Umständen Drittanbieter-Cookies auf Ihrem Endgerät gespeichert. ' .
            'Sie können diese Einstellungen jederzeit ändern (Fingerabdruck-Icon links unten). Weitere Details finden Sie ' .
            'in unserer <a href="%s" target="_blank">Datenschutzerklärung</a>.');
        $this->setLocalization('eng', 'consent', 'dataProtectionDescription', 'Would you like to see these contents? ' .
            'Activate the desired contents for one session only or allow the website to remember these settings. ' .
            'Once you have given your consent, the third-party data can be loaded. For this, third-party cookies ' .
            'might be stored on your device. You can change these settings at any time (fingerprint icon in the bottom left corner). ' .
            'For further details, please see the <a href="%s" target="_blank">Privacy notice</a>.');
        $this->setLocalization('ger', 'consent', 'cookieSettingsDescription', 'Einstellungen, die Sie hier vornehmen, ' .
            'werden auf Ihrem Endgerät im „Local Storage“ gespeichert und sind beim nächsten Besuch unseres Onlineshops wieder aktiv. ' .
            'Sie können diese Einstellungen jederzeit ändern (Fingerabdruck-Icon links unten).<br><br>' .
            'Informationen zur Cookie-Funktionsdauer sowie Details zu technisch notwendigen Cookies erhalten Sie in unserer ' .
            '<a href="%s" target="_blank">Datenschutzerklärung</a>.');
        $this->setLocalization('eng', 'consent', 'cookieSettingsDescription', 'The settings you specify here are stored ' .
            'in the "local storage" of your device. The settings will be remembered for the next time you visit our online shop. ' .
            'You can change these settings at any time (fingerprint icon in the bottom left corner).<br><br>' .
            'For more information on cookie lifetime and required essential cookies, please see the ' .
            '<a href="%s" target="_blank">Privacy notice</a>.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('howWeUseCookies', 'consent');
        $this->removeLocalization('cookieSettings', 'consent');
        $this->removeLocalization('selectAll', 'consent');
        $this->removeLocalization('apply', 'consent');
        $this->removeLocalization('moreInformation', 'consent');
        $this->removeLocalization('description', 'consent');
        $this->removeLocalization('company', 'consent');
        $this->removeLocalization('terms', 'consent');
        $this->removeLocalization('link', 'consent');
        $this->removeLocalization('dataProtection', 'consent');
        $this->removeLocalization('consentOnce', 'consent');
        $this->removeLocalization('consentAlways', 'consent');
        $this->removeLocalization('consentAll', 'consent');
        $this->removeLocalization('close', 'consent');
        $this->removeLocalization('configure', 'consent');
        $this->removeLocalization('consentDescription', 'consent');
        $this->removeLocalization('dataProtectionDescription', 'consent');
        $this->removeLocalization('cookieSettingsDescription', 'consent');

        $this->execute("DELETE FROM tsprachsektion WHERE cName = 'consent';");
    }
}
