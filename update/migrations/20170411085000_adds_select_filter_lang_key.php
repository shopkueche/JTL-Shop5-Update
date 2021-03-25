<?php
/**
 * add lang key choose filter
 *
 * @author ms
 * @created Tue, 11 Apr 2017 08:50:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170411085000
 */
class Migration_20170411085000 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'add lang key select filter';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'selectFilter', 'Beliebig');
        $this->setLocalization('eng', 'global', 'selectFilter', 'Any');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('selectFilter');
    }
}
