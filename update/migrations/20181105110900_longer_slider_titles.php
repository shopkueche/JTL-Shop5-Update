<?php
/**
 * @author fm
 * @created Mon, 05 Nov 2018 11:09:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20181105110900
 */
class Migration_20181105110900 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Longer slider/slide titles';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tslide` CHANGE COLUMN `cTitel` `cTitel` VARCHAR(255) NOT NULL');
        $this->execute('ALTER TABLE `tslider` CHANGE COLUMN `cName` `cName` VARCHAR(255) NOT NULL');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tslide` CHANGE COLUMN `cTitel` `cTitel` VARCHAR(45) NOT NULL');
        $this->execute('ALTER TABLE `tslider` CHANGE COLUMN `cName` `cName` VARCHAR(45) NOT NULL');
    }
}
