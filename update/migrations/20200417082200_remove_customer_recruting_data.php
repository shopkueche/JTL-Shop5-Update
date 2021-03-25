<?php
/**
 * Remove customer recruting data
 *
 * @author mh
 * @created Fri, 17 Apr 2020 08:22:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200417082200
 */
class Migration_20200417082200 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove customer recruting data';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeConfig('kwk_nutzen');
        $this->removeConfig('kwk_neukundenguthaben');
        $this->removeConfig('kwk_bestandskundenguthaben');
        $this->removeConfig('kwk_kundengruppen');
        $this->removeConfig('configgroup_116_customer_recruit_customer');

        $this->removeLocalization('kwkEmail', 'login');
        $this->removeLocalization('kwkName', 'login');
        $this->removeLocalization('kwkAlreadyreg', 'errorMessages');
        $this->removeLocalization('kwkWrongdata', 'errorMessages');
        $this->removeLocalization('kwkAdd', 'messages');
        $this->removeLocalization('kwkFirstName', 'login');
        $this->removeLocalization('kwkLastName', 'login');
        $this->removeLocalization('kwkSend', 'login');

        $this->execute("DELETE FROM `tadminrecht` WHERE `cRecht`='MODULE_CAC_VIEW';");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setConfig(
            'kwk_nutzen',
            'N',
            CONF_KUNDENWERBENKUNDEN,
            'Kunden werben Kunden nutzen?',
            'selectbox',
            20,
            (object)[
                'cBeschreibung' => 'Möchten Sie das Kunden werben Kunden Modul im Shop nutzen?',
                'inputOptions'  => [
                    'J'   => 'Ja',
                    'N'   => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'kwk_neukundenguthaben',
            '0',
            CONF_KUNDENWERBENKUNDEN,
            'Neukunde Willkommensguthaben',
            'kommazahl',
            30,
            (object)[
                'cBeschreibung' => 'Wieviel Guthaben möchten Sie Neukunden geben?'
            ]
        );
        $this->setConfig(
            'kwk_bestandskundenguthaben',
            '0',
            CONF_KUNDENWERBENKUNDEN,
            'Bestandskunde Bonusguthaben',
            'kommazahl',
            30,
            (object)[
                'cBeschreibung' => 'Wieviel Guthaben möchten Sie dem Bestandskunden der geworben hat, gutschreiben?'
            ]
        );
        $this->setConfig(
            'kwk_kundengruppen',
            '1',
            CONF_KUNDENWERBENKUNDEN,
            'Standard Kundengruppe',
            'selectkdngrp',
            50,
            (object)[
                'cBeschreibung' => 'Welche Kundengruppe soll Neukunden zugewiesen werden?'
            ]
        );
        $this->setConfig(
            'configgroup_116_customer_recruit_customer',
            'Kunden werben Kunden Einstellungen',
            CONF_KUNDENWERBENKUNDEN,
            'Kunden werben Kunden Einstellungen',
            null,
            10,
            (object)['cConf' => 'N']
        );

        $this->setLocalization('ger', 'login', 'kwkEmail', 'E-Mail-Adresse');
        $this->setLocalization('ger', 'login', 'kwkName', 'Werben Sie einen Freund!');
        $this->setLocalization('ger', 'login', 'kwkFirstName', 'Vorname');
        $this->setLocalization('ger', 'login', 'kwkLastName', 'Nachname');
        $this->setLocalization('ger', 'login', 'kwkSend', 'Verschicken');
        $this->setLocalization('ger', 'errorMessages', 'kwkAlreadyreg', 'Die E-Mail-Adresse %s wird bereits verwendet.');
        $this->setLocalization('ger', 'messages', 'kwkAdd', 'Ihre Einladung wurde erfolgreich an %s gesendet.');
        $this->setLocalization('ger', 'errorMessages', 'kwkWrongdata', 'Bitte geben Sie gültige Daten ein.');
        $this->setLocalization('eng', 'login', 'kwkEmail', 'Email address');
        $this->setLocalization('eng', 'login', 'kwkName', 'Tell a friend!');
        $this->setLocalization('eng', 'login', 'kwkFirstName', 'First name');
        $this->setLocalization('eng', 'login', 'kwkLastName', 'Last name');
        $this->setLocalization('eng', 'login', 'kwkSend', 'Send');
        $this->setLocalization('eng', 'errorMessages', 'kwkAlreadyreg', 'The email address %s is already in use.');
        $this->setLocalization('eng', 'messages', 'kwkAdd', 'Your invitation has been successfully sent to %s.');
        $this->setLocalization('eng', 'errorMessages', 'kwkWrongdata', 'Please enter a valid date.');

        $this->execute("INSERT INTO `tadminrecht` (`cRecht`, `cBeschreibung`) VALUES ('MODULE_CAC_VIEW', 'Kunden werben Kunden');");
    }
}
