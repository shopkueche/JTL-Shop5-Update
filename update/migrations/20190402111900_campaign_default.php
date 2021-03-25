<?php
/**
 * @author fm
 * @created Tue, 02 Apr 2019 11:19:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190402111900
 */
class Migration_20190402111900 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'kKampgne default value';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'ALTER TABLE `timagemap` 
                CHANGE COLUMN `kKampagne` `kKampagne` INT(10) UNSIGNED NOT NULL DEFAULT 0'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `timagemap` CHANGE COLUMN `kKampagne` `kKampagne` INT(10) UNSIGNED NOT NULL');
    }
}
