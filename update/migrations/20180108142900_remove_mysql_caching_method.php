<?php
/**
 * Remove caching method "mysql"
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180108142900
 */
class Migration_20180108142900 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Remove caching method mysql';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("DELETE FROM `teinstellungenconfwerte` WHERE kEinstellungenConf = 1551 AND cWert = 'mysql'");
        $this->execute("UPDATE `teinstellungen` SET `cWert`='null' WHERE `cWert`='mysql' AND cName = 'caching_method'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("INSERT INTO `teinstellungenconfwerte` (kEinstellungenConf, cName, cWert, nSort) VALUES (1551, 'MySQL', 'mysql', 9)");
    }
}
