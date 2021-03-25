<?php
/**
 * Change setting for restriction to only delivery countries
 *
 * @author fp
 * @created Thu, 15 Nov 2018 13:13:59 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20181115131359
 */
class Migration_20181115131359 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Change setting for restriction to only delivery countries';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "UPDATE teinstellungenconf
                SET cBeschreibung = 'Damit gibt es bei der Lieferadresse nur Länder zur Auswahl, für die min. eine Versandart definiert ist.'
                WHERE cWertName = 'kundenregistrierung_nur_lieferlaender'"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "UPDATE teinstellungenconf
                SET cBeschreibung = 'Damit gibt es bei der Rechnungsadresse und Lieferadresse nur Länder zur Auswahl, für die min. eine Versandart definiert ist.'
                WHERE cWertName = 'kundenregistrierung_nur_lieferlaender'"
        );
    }
}
