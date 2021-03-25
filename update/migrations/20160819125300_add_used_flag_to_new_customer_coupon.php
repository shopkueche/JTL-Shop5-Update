<?php
/**
 * used flag for new customer coupons
 *
 * @author ms
 * @created Fri, 19 Aug 2016 12:53:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160819125300
 */
class Migration_20160819125300 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("ALTER TABLE `tkuponneukunde` ADD COLUMN `cVerwendet` VARCHAR(1) NOT NULL DEFAULT 'N';");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->dropColumn('tkuponneukunde', 'cVerwendet');
    }
}
