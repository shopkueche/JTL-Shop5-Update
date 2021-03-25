<?php
/**
 * Increase text fiels length for currencies
 *
 * @author fm
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190207074500
 */
class Migration_20190207074500 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Increase currency table text fields length';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `twaehrung` 
            CHANGE COLUMN `cName` `cName` VARCHAR(255) NULL DEFAULT NULL,
            CHANGE COLUMN `cNameHTML` `cNameHTML` VARCHAR(255) NULL DEFAULT NULL');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `twaehrung` 
            CHANGE COLUMN `cName` `cName` VARCHAR(20) NULL DEFAULT NULL,
            CHANGE COLUMN `cNameHTML` `cNameHTML` VARCHAR(20) NULL DEFAULT NULL');
    }
}
