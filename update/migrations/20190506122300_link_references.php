<?php
/**
 * syntax checks
 *
 * @author fm
 * @created Mon, 16 May 2019 12:23:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190506122300
 */
class Migration_20190506122300 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Link references';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tlink` ADD COLUMN `reference` INT(10) UNSIGNED NOT NULL DEFAULT 0');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tlink` DROP COLUMN `reference`');
    }
}
