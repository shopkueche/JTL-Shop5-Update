<?php
/**
 * add lang key redeemed coupons
 *
 * @author msc
 * @created Wed, 01 Feb 2017 15:36:18 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170201153618
 */
class Migration_20170201153618 extends Migration implements IMigration
{
    protected $author      = 'msc';
    protected $description = 'add lang key redeemed coupons';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'currentCoupon', 'Bereits eingelöster Kupon: ');
        $this->setLocalization('eng', 'checkout', 'currentCoupon', 'Redeemed coupon: ');
        $this->setLocalization('ger', 'checkout', 'discountForArticle', 'gültig für: ');
        $this->setLocalization('eng', 'checkout', 'discountForArticle', 'applied to: ');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('currentCoupon');
        $this->removeLocalization('discountForArticle');
    }
}
