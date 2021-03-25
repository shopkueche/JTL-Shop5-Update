<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200407143100
 */
class Migration_20200407143100 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add extension store id to plugins';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tplugin` ADD COLUMN `exsID` VARCHAR(255) NULL DEFAULT NULL');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tplugin` DROP COLUMN `exsID`');
    }
}
