<?php
/**
 * update language vars for coupons in polls
 *
 * @author ms
 * @created Tue, 17 May 2016 15:22:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160517152200
 */
class Migration_20160517152200 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'messages', 'pollCoupon', 'Vielen Dank für die Teilnahme an unserer Umfrage. Für Ihre nächste Bestellung steht Ihnen der folgende Kuponcode zur Verfügung: %s.');
        $this->setLocalization('eng', 'messages', 'pollCoupon', 'Thank you for taking part in our poll. For your next order, feel free to use the following coupon code: %s.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'messages', 'pollCoupon', 'Vielen Dank für die Teilnahme an unserer Umfrage. Ihnen wurde der Kupon %s gutgeschrieben.');
        $this->setLocalization('eng', 'messages', 'pollCoupon', 'Your poll was successfully saved and the coupon %s was credited to you, thank you.');
    }
}
