<?php
/**
 * Update tjtllog.nLevel to INT
 *
 * @author fm
 * @created Mon, 12 Mar 2018 15:41:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180312154100
 */
class Migration_20180312154100 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Update tjtllog.nLevel to INT';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tjtllog` CHANGE COLUMN `nLevel` `nLevel` INT UNSIGNED NOT NULL');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tjtllog` CHANGE COLUMN `nLevel` `nLevel` TINYINT UNSIGNED NOT NULL');
    }
}
