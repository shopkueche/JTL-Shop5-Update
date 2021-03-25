<?php declare(strict_types=1);

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200427174400
 */
class Migration_20200427174400 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add template table rows';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'ALTER TABLE `ttemplate` 
                ADD COLUMN `exsID` VARCHAR(255) NULL DEFAULT NULL AFTER `preview`,
                ADD COLUMN `bootstrap` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `exsID`,
                ADD COLUMN `framework` VARCHAR(255) NULL DEFAULT NULL AFTER `bootstrap`');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `ttemplate` DROP COLUMN `exsID`, DROP COLUMN `bootstrap`, DROP COLUMN `framework`');
    }
}
