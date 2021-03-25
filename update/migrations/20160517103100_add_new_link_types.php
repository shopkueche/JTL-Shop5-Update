<?php
/**
 * add new link types
 *
 * @author fm
 * @created Mon, 17 May 2016 10:31:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160517103100
 */
class Migration_20160517103100 extends Migration implements IMigration
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
              VALUES ('0', 'Bestellvorgang', 'bestellvorgang.php', '32', '32')"
        );
        $this->execute(
            "INSERT INTO `tspezialseite`
              (`kPlugin`, `cName`, `cDateiname`, `nLinkart`, `nSort`)
              VALUES ('0', 'Bestellabschluss', 'bestellabschluss.php', '33', '33')"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("DELETE FROM `tspezialseite` WHERE `nLinkart` = '32' OR `nLinkart` = '33'");
    }
}
