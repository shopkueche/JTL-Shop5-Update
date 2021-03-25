<?php
/**
 * add setting "review reminder bound to newsletter"
 *
 * @author cr
 * @created Wed, 30 Jan 2019 13:08:22 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190130130822
 */
class Migration_20190130130822 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'add setting "review reminder bound to newsletter"';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "UPDATE teinstellungenconfwerte w
            JOIN teinstellungenconf c
                ON c.kEinstellungenConf = w.kEinstellungenConf
                AND c.cWertName = 'bewertungserinnerung_nutzen'
                AND w.cWert = 'N'
            SET w.nSort = 2"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte w
            JOIN teinstellungenconf c
                ON c.kEinstellungenConf = w.kEinstellungenConf
                AND c.cWertName = 'bewertungserinnerung_nutzen'
                AND w.cWert = 'Y'
            SET w.nSort = 3"
        );
        $this->execute("INSERT INTO teinstellungenconfwerte(
                kEinstellungenConf,
                cName,
                cWert,
                nSort)
            VALUES(
                (SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName = 'bewertungserinnerung_nutzen'),
                'An Newslettereinwilligung koppeln',
                'B',
                1)"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("DELETE w FROM teinstellungenconfwerte w JOIN teinstellungenconf c
            WHERE w.kEinstellungenConf = c.kEinstellungenConf
                AND c.cWertName = 'bewertungserinnerung_nutzen'
                AND w.cWert = 'B'"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte w
            JOIN teinstellungenconf c
                ON c.kEinstellungenConf = w.kEinstellungenConf
                AND c.cWertName = 'bewertungserinnerung_nutzen'
                AND w.cWert = 'N'
            SET w.nSort = 2"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte w
            JOIN teinstellungenconf c
                ON c.kEinstellungenConf = w.kEinstellungenConf
                AND c.cWertName = 'bewertungserinnerung_nutzen'
                AND w.cWert = 'Y'
            SET w.nSort = 1"
        );
    }
}
