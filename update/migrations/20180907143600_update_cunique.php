<?php
/**
 * @author fm
 * @created Fri, 07 Sep 2018 14:36:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180907143600,
 */
class Migration_20180907143600 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'update cUnique fields';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `twarenkorbperspos` CHANGE COLUMN `cUnique` `cUnique` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL');
        $this->execute('ALTER TABLE `twarenkorbpos` CHANGE COLUMN `cUnique` `cUnique` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `twarenkorbperspos` CHANGE COLUMN `cUnique` `cUnique` VARCHAR(10) NOT NULL');
        $this->execute('ALTER TABLE `twarenkorbpos` CHANGE COLUMN `cUnique` `cUnique` VARCHAR(10) NOT NULL');
    }
}
