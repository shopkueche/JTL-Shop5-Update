<?php
/**
 * adding language variable for download-order-date
 *
 * @author cr
 * @created Mon, 10 Feb 2020 12:30:41 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200210123041
 */
class Migration_20200210123041 extends Migration implements IMigration
{
    protected $author = 'cr';
    protected $description = 'Add lang var download order date';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'downloadOrderDate', 'Bestellt am');
        $this->setLocalization('eng', 'global', 'downloadOrderDate', 'Ordered on');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('downloadOrderDate');
    }
}
