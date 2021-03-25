<?php
/**
 * Add lang var coupon success
 *
 * @author mh
 * @created Mon, 09 Mar 2020 14:52:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200309145200
 */
class Migration_20200309145200 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add lang var coupon success';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'couponSuccess', 'Der Coupon wurde freigeschaltet.');
        $this->setLocalization('eng', 'global', 'couponSuccess', 'Your coupon has been activated.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('couponSuccess');
    }
}
