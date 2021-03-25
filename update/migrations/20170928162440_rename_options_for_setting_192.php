<?php
/**
 * Rename options for setting 192
 *
 * @author fp
 * @created Thu, 28 Sep 2017 16:24:40 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170928162440
 */
class Migration_20170928162440 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Rename options for setting 192';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("UPDATE teinstellungenconfwerte SET cName = 'Automatischer Wechsel zu https' WHERE kEinstellungenConf = 192 AND cWert = 'P'");
        $this->execute("UPDATE teinstellungenconfwerte SET cName = 'Kein automatischer Wechsel' WHERE kEinstellungenConf = 192 AND cWert = 'N'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("UPDATE teinstellungenconfwerte SET cName = 'Permanentes SSL mit eigenem Zertifikat' WHERE kEinstellungenConf = 192 AND cWert = 'P'");
        $this->execute("UPDATE teinstellungenconfwerte SET cName = 'SSL-Verschl&uuml;sselung deaktivieren' WHERE kEinstellungenConf = 192 AND cWert = 'N'");
    }
}
