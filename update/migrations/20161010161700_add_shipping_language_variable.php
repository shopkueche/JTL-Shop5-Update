<?php
/**
 * add shipping language variable
 *
 * @author msc
 * @created Thu, 10 Oct 2016 16:17:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161010161700
 */
class Migration_20161010161700 extends Migration implements IMigration
{
    protected $author = 'msc';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'productShippingDesc', 'Gesonderte Versandkosten');
        $this->setLocalization('eng', 'checkout', 'productShippingDesc', 'Separate shipping costs');
        $this->setLocalization('ger', 'global', 'shippingMethods', 'Versandarten');
        $this->setLocalization('eng', 'global', 'shippingMethods', 'Shipping methods');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'checkout', 'productShippingDesc', 'Für folgende Artikel gelten folgende Versandkosten');
        $this->setLocalization('eng', 'checkout', 'productShippingDesc', 'Shipping costs for the following products');
        $this->removeLocalization('shippingMethods');
    }
}
