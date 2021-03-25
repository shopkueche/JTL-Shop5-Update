<?php

/**
 * adds lang var to wishlist section
 *
 * @author ms
 * @created Mon, 07 Jan 2020 13:13:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200107131300
 */
class Migration_20200107131300 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'adds lang var to wishlist section';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'wishlist', 'addCurrentProductsToCart', 'aktuelle Artikel in den Warenkorb');
        $this->setLocalization('eng', 'wishlist', 'addCurrentProductsToCart', 'add current products to cart');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('addCurrentProductsToCart');
    }
}
