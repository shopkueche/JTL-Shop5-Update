<?php
/**
 * fix datetime defaults
 *
 * @author fm
 * @created Tue, 28 Aug 2018 13:11:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180828131100
 */
class Migration_20180828131100 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Fix datetime defaults';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tlieferscheinposinfo` CHANGE COLUMN `dMHD` `dMHD` DATETIME DEFAULT NULL');
        $this->execute('ALTER TABLE `tjtllog` CHANGE COLUMN `cLog` `cLog` LONGTEXT NOT NULL');
        $this->execute('ALTER TABLE `tartikelwarenlager` CHANGE COLUMN `dZulaufDatum` `dZulaufDatum` DATETIME DEFAULT NULL');
        $this->execute('ALTER TABLE `tartikel` CHANGE COLUMN `dErscheinungsdatum` `dErscheinungsdatum` DATE DEFAULT NULL');
        $this->execute('ALTER TABLE `tartikel` CHANGE COLUMN `dErstellt` `dErstellt` DATE DEFAULT NULL');
        $this->execute('ALTER TABLE `tkunde` CHANGE COLUMN `dGeburtstag` `dGeburtstag` DATE DEFAULT NULL');
        $this->execute('ALTER TABLE `tkunde` CHANGE COLUMN `dErstellt` `dErstellt` DATE DEFAULT NULL');
        $this->execute('ALTER TABLE `tumfrage` CHANGE COLUMN `dGueltigBis` `dGueltigBis` DATETIME DEFAULT NULL');
        $this->execute('ALTER TABLE `tcron` CHANGE COLUMN `dLetzterStart` `dLetzterStart` DATETIME DEFAULT NULL');
        $this->execute('ALTER TABLE `tjobqueue` CHANGE COLUMN `dZuletztGelaufen` `dZuletztGelaufen` DATETIME DEFAULT NULL');
        $this->execute('ALTER TABLE `tlastjob` CHANGE COLUMN `dErstellt` `dErstellt` DATETIME DEFAULT NULL');
        $this->execute('ALTER TABLE `tnewsletterempfaenger` CHANGE COLUMN `dLetzterNewsletter` `dLetzterNewsletter` DATETIME DEFAULT NULL');
        $this->execute('ALTER TABLE `tnummern` CHANGE COLUMN `dAktualisiert` `dAktualisiert` DATETIME DEFAULT NULL');
        $this->execute("UPDATE `tzahlungsession` SET `dZeitBezahlt` = NOW() WHERE `dZeitBezahlt` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `tlieferscheinposinfo` SET `dMHD` = NULL WHERE `dMHD` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `tkupon` SET `dGueltigBis` = NULL WHERE `dGueltigBis` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `tkunde` SET `dVeraendert` = NOW() WHERE `dVeraendert` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `timagemap` SET `vDatum` = NULL WHERE `vDatum` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `timagemap` SET `bDatum` = NULL WHERE `bDatum` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `tbestellung` SET `dVersandDatum` = NULL WHERE `dVersandDatum` = '0000-00-00'");
        $this->execute("UPDATE `tbestellung` SET `dBewertungErinnerung` = NULL WHERE `dBewertungErinnerung` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `tbestellung` SET `dBezahltDatum` = NULL WHERE `dBezahltDatum` = '0000-00-00'");
        $this->execute("UPDATE `tartikelwarenlager` SET `dZulaufDatum` = NULL WHERE `dZulaufDatum` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `tnewsletterempfaengerhistory` SET `dEingetragen` = NULL WHERE `dEingetragen` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `tnewsletterempfaengerhistory` SET `dAusgetragen` = NULL WHERE `dAusgetragen` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `tkunde` SET `dGeburtstag` = NULL WHERE `dGeburtstag` = '0000-00-00'");
        $this->execute("UPDATE `tkunde` SET `dErstellt` = NULL WHERE `dErstellt` = '0000-00-00'");
        $this->execute("UPDATE `tartikelsonderpreis` SET `dEnde` = NULL WHERE `dEnde` = '0000-00-00'");
        $this->execute("UPDATE `tcron` SET `dLetzterStart` = NULL WHERE `dLetzterStart` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `tlastjob` SET `dErstellt` = NULL WHERE `dErstellt` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `tjobqueue` SET `dZuletztGelaufen` = NULL WHERE `dZuletztGelaufen` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `tcron` SET `dLetzterStart` = NULL WHERE `dLetzterStart` = '0000-00-00'");
        $this->execute("UPDATE `tumfrage` SET `dGueltigBis` = NULL WHERE `dGueltigBis` = '0000-00-00'");
        $this->execute("UPDATE `tnewsletterempfaenger` SET `dLetzterNewsletter` = NULL WHERE `dLetzterNewsletter` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `tnummern` SET `dAktualisiert` = NULL WHERE `dAktualisiert` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `tartikel` SET `dErscheinungsdatum` = NULL WHERE `dErscheinungsdatum` = '0000-00-00'");
        $this->execute("UPDATE `tartikel` SET `dErstellt` = NULL WHERE `dErstellt` = '0000-00-00'");
        $this->execute("UPDATE `tartikel` SET `dZulaufDatum` = NULL WHERE `dZulaufDatum` = '0000-00-00'");
        $this->execute("UPDATE `tartikel` SET `dMHD` = NULL WHERE `dMHD` = '0000-00-00'");
        $this->execute("UPDATE `tartikel` SET `dLetzteAktualisierung` = NULL WHERE `dLetzteAktualisierung` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `texportformat` SET `dZuletztErstellt` = NULL WHERE `dZuletztErstellt` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `texportformatqueuebearbeitet` SET `dStartZeit` = NULL WHERE `dStartZeit` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `texportformatqueuebearbeitet` SET `dZuletztGelaufen` = NULL WHERE `dZuletztGelaufen` = '0000-00-00 00:00:00'");
        $this->execute("UPDATE `tnewsletterempfaengerhistory` SET `dOptCode` = NULL WHERE `dOptCode` = '0000-00-00 00:00:00'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
