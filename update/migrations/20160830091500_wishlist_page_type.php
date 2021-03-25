<?php
/**
 * add new link type for wishlist
 *
 * @author fm
 * @created Tue, 30 Oct 2016 09:15:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160830091500
 */
class Migration_20160830091500 extends Migration implements IMigration
{
    protected $author = 'fm';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("INSERT INTO `tspezialseite` (`kPlugin`, `cName`, `cDateiname`, `nLinkart`, `nSort`) VALUES ('0', 'Wunschliste', 'wunschliste.php', '34', '34')");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("DELETE FROM `tspezialseite` WHERE `nLinkart` = '34'");
    }
}
