<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180919103846
 */
class Migration_20180919103846 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'add anonymizing settings';

    /**
     * @inheritDoc
     */
    public function up()
    {
        // remove the old "IPs speichern" settings (teinstellungenconf::kEinstellungenConf=335,1133)
        $this->removeConfig('global_ips_speichern');
        $this->removeConfig('bestellabschluss_ip_speichern');

        // setting up the cron-job in the cron-table
        $cronDataProtection = $this->fetchArray("SELECT * FROM tcron WHERE cJobArt = 'dataprotection'");
        if (0 <= count($cronDataProtection)) {
            $this->execute("
                INSERT INTO tcron(kKey, cKey, cJobArt, nAlleXStd,cTabelle, cName, dStart, dStartZeit, dLetzterStart)
                    VALUES(50, '', 'dataprotection', 24, '', '', NOW(), '00:00:00', NOW())
            ");
        }

        // create the journal-table
        $this->execute("
            CREATE TABLE IF NOT EXISTS tanondatajournal(
                kAnonDatenHistory INT(11) NOT NULL AUTO_INCREMENT,
                cIssuer VARCHAR(255) DEFAULT '' COMMENT 'application(cron), user, admin',
                iIssuerId INT(11) DEFAULT NULL COMMENT 'id of the issuer (only for user or admin)',
                dEventTime DATETIME DEFAULT NULL COMMENT 'time of the event',
                PRIMARY KEY kAnonDatenHistory(kAnonDatenHistory),
                KEY kIssuer(iIssuerId)
            )
            ENGINE = InnoDB
            DEFAULT CHARSET = utf8
            COLLATE = utf8_unicode_ci
        ");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // remove the journal-table
        $this->execute('DROP TABLE tanondatajournal');

        // remove the cron-job from the cron-table and all possibly running job
        $this->execute("DELETE FROM tcron WHERE cJobArt = 'dataprotection'");
        $this->execute("DELETE FROM tjobqueue WHERE cJobArt = 'dataprotection'");

        // restore the old "IPs speichern" settings (teinstellungenconf::kEinstellungenConf=335,1133)
        $this->execute("
            INSERT INTO teinstellungenconf VALUES
                (335, 1, 'IP-Adresse bei Bestellung mitspeichern', 'Soll die IP-Adresse des Kunden in der Datenbank gespeichert werden, wenn er eine Bestellung abschliesst?', 'bestellabschluss_ip_speichern', 'selectbox', NULL, 554, 1, 0, 'Y'),
                (1133, 1 ,'IPs speichern', 'Sollen IPs von Benutzern bei z.b. Umfragen, Tags etc. als Floodschutz oder sonstigen Trackingm&ouml;glichkeiten gespeichert werden?' ,'global_ips_speichern' ,'selectbox', NULL, 552, 1, 0 , 'Y')
        ");
        $this->execute("
            INSERT INTO teinstellungenconfwerte VALUE
                ('335','Ja','Y','1'),
                ('335','Nein','N','2'),
                ('1133','Ja','Y','1'),
                ('1133','Nein','N','2')
        ");
    }
}
