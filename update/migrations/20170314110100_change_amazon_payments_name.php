<?php
/**
 * Change "Amazon Payments" to "Amazon Pay"
 *
 * @author dr
 * @created Tue, 14 Mar 2017 11:01:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170314110100
 */
class Migration_20170314110100 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Change "Amazon Payments" to "Amazon Pay"';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("UPDATE tadminmenu SET cLinkname = 'Amazon Pay' WHERE cLinkname = 'Amazon Payments'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("UPDATE tadminmenu SET cLinkname = 'Amazon Payments' WHERE cLinkname = 'Amazon Pay'");
    }
}
