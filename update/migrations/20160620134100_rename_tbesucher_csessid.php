<?php
/**
 * Rename tbesucher.cSessId to tbesucher.cSessID
 *
 * @author dr
 * @created Mon, 20 Jun 2016 13:41:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160620134100
 */
class Migration_20160620134100 extends Migration implements IMigration
{
    protected $author = 'dr';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tbesucher` CHANGE COLUMN `cSessId` `cSessID` VARCHAR(128)');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tbesucher` CHANGE COLUMN `cSessID` `cSessId` VARCHAR(128)');
    }
}
