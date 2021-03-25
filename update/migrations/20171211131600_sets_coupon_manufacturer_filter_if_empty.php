<?php
/** missing migration for manufacturer filter. sets coupon manufacturer filter if empty*/

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20171211131600
 */
class Migration_20171211131600 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("UPDATE tkupon SET cHersteller = '-1' WHERE cHersteller = '';");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
