<?php
/**
 * Remove option for partial https encryption
 *
 * @author fm
 * @created Fri, 10 Mar 2017 15:38:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170310153800
 */
class Migration_20170310153800 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Remove partial https encryption option';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "UPDATE teinstellungen 
                SET cWert = 'P' 
                WHERE kEinstellungenSektion = 1 
                AND cName = 'kaufabwicklung_ssl_nutzen'
                AND cWert = 'Z'"
        );
        $this->execute(
            "DELETE 
                FROM teinstellungenconfwerte 
                WHERE kEinstellungenConf = 192 
                AND cWert = 'Z'"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "INSERT INTO 
                teinstellungenconfwerte (`kEinstellungenConf`, `cName`, `cWert`, `nSort`)
                VALUES (192, 'Teilverschlüsselung und automatischer Wechsel zwischen http und https', 'Z', 3)"
        );
    }
}
