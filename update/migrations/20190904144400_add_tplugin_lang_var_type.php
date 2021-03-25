<?php
/**
 * @author fm
 * @created Wed, 04 Sep 2019 14:44:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190904144400
 */
class Migration_20190904144400 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add input type for plugin language variables';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("ALTER TABLE `tpluginsprachvariable` ADD COLUMN `type` VARCHAR(255) NOT NULL DEFAULT 'text';");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tpluginsprachvariable` DROP COLUMN `type`;');
    }
}
