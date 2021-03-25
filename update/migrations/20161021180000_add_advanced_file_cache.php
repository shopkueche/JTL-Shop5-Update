<?php
/**
 * add new object cache method
 *
 * @author fm
 * @created Fri, 21 Oct 2016 18:00:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161021180000
 */
class Migration_20161021180000 extends Migration implements IMigration
{
    protected $author = 'fm';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort) VALUES (1551, 'Dateien (erweitert)', 'advancedfile', 9)");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("DELETE FROM teinstellungenconfwerte WHERE kEinstellungenConf = 1551 AND cWert = 'advancedfile'");
    }
}
