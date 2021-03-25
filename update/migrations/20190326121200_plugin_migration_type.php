<?php
/**
 * @author fm
 * @created Tue, 26 Mar 2019 12:12:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190326121200
 */
class Migration_20190326121200 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'change nVersion type';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tpluginmigration` CHANGE COLUMN `nVersion` `nVersion` VARCHAR(255) NOT NULL');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tpluginmigration` CHANGE COLUMN `nVersion` `nVersion` int(3) NOT NULL');
    }
}
