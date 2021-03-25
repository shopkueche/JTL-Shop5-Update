<?php
/**
 * remove nglobal from tmerkmal
 *
 * @author mh
 * @created Tue, 11 June 2019 12:24:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190611122400
 */
class Migration_20190611122400 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove nGlobal from tmerkmal';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tmerkmal` DROP COLUMN `nGlobal`');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tmerkmal` ADD COLUMN `nGlobal` TINYINT(4) DEFAULT 0');
    }
}
