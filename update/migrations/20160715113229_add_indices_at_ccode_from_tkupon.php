<?php
/**
 * add_indices_at_cCode_from_tkupon
 *
 * @author msc
 * @created Fri, 15 Jul 2016 11:32:29 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160715113229
 */
class Migration_20160715113229 extends Migration implements IMigration
{
    protected $author = 'msc';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tkupon` ADD INDEX(`cCode`)');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE tkupon DROP INDEX cCode');
    }
}
