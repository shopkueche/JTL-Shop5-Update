<?php
/**
 * Add plugin hook priority
 *
 * @author fm
 * @created Wed, 29 Jun 2016 11:42:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160629114200
 */
class Migration_20160629114200 extends Migration implements IMigration
{
    protected $author = 'fm';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tpluginhook` ADD COLUMN `nPriority` INT(10) NULL DEFAULT 5');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tpluginhook` DROP COLUMN `nPriority`');
    }
}
