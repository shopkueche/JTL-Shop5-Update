<?php
/**
 * add new special page type for compare list
 *
 * @author fm
 * @created Thu, 15 Sep 2016 17:52:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160915175200
 */
class Migration_20160915175200 extends Migration implements IMigration
{
    protected $author = 'fm';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "INSERT INTO `tspezialseite`
                (`kPlugin`, `cName`, `cDateiname`, `nLinkart`, `nSort`)
                 VALUES ('0', 'Vergleichsliste', 'vergleichsliste.php', '35', '35')"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("DELETE FROM `tspezialseite` WHERE `nLinkart` = '35'");
    }
}
