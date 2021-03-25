<?php
/**
 * active status for link sites
 *
 * @author ms
 * @created Mon, 23 May 2016 16:19:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160523161900
 */
class Migration_20160523161900 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tlink` ADD COLUMN `bIsActive` TINYINT(1) NOT NULL DEFAULT 1;');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tlink` DROP COLUMN `bIsActive`;');
    }
}
