<?php
/**
 * @author ms
 * @created Wed, 03 Apr 2019 16:18:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190403161800
 */
class Migration_20190403161800 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'removes vcard';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $upMigration = new Migration_20160713110643($this->db);
        $upMigration->down();
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $upMigration = new Migration_20160713110643($this->db);
        $upMigration->up();
    }
}
