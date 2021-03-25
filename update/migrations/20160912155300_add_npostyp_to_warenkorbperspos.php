<?php
/**
 * @author ms
 * @created Mon, 12 Sep 2016 15:53:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160912155300
 */
class Migration_20160912155300 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `twarenkorbperspos` ADD COLUMN `nPosTyp` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `twarenkorbperspos` DROP COLUMN `nPosTyp`');
    }
}
