<?php
/**
 * Add boolean mode for fulltext search
 *
 * @author fp
 * @created Wed, 14 Mar 2018 13:37:43 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180314133743
 */
class Migration_20180314133743 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Add boolean mode for fulltext search';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "INSERT INTO teinstellungenconfwerte (
                SELECT teinstellungenconf.kEinstellungenConf, 'Volltextsuche (Boolean Mode)', 'B', 3
                FROM teinstellungenconf
                WHERE teinstellungenconf.cWertName = 'suche_fulltext'
            )"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "DELETE teinstellungenconfwerte 
                FROM teinstellungenconfwerte 
                INNER JOIN teinstellungenconf 
                    ON teinstellungenconf.kEinstellungenConf = teinstellungenconfwerte.kEinstellungenConf
                WHERE teinstellungenconf.cWertName = 'suche_fulltext'
                    AND teinstellungenconfwerte.cWert = 'B'"
        );
    }
}
