<?php
/**
 * Add customer-fields nSort
 *
 * @author cr
 * @created Thu, 23 Nov 2017 10:08:24 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20171123100824
 */
class Migration_20171123100824 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'Add customer-fields nSort';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('
            ALTER TABLE
                `tkundenfeldwert`
            ADD COLUMN
                `nSort` int(10) unsigned NOT NULL AFTER `cWert`
        ');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('
            ALTER TABLE
                `tkundenfeldwert`
            DROP COLUMN
                `nSort`;
        ');
    }
}
