<?php
/**
 * Add language variable one-off
 *
 * @author msc
 * @created Tue, 01 Aug 2017 13:10:13 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170801131013
 */
class Migration_20170801131013 extends Migration implements IMigration
{
    protected $author      = 'msc';
    protected $description = 'Add language variable one-off';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'one-off', 'Einmalig enthalten');
        $this->setLocalization('eng', 'checkout', 'one-off', 'Included one-time');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('one-off');
    }
}
