<?php

/**
 * add lang vars for increase decrease buttons
 *
 * @author ms
 * @created Thu, 14 Nov 2019 14:33:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191114143300
 */
class Migration_20191114143300 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'add lang vars for increase decrease buttons';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'aria', 'increaseQuantity', 'Menge erhöhen');
        $this->setLocalization('eng', 'aria', 'increaseQuantity', 'increase quantity');

        $this->setLocalization('ger', 'aria', 'decreaseQuantity', 'Menge verringern');
        $this->setLocalization('eng', 'aria', 'decreaseQuantity', 'decrease quantity');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('increaseQuantity');
        $this->removeLocalization('decreaseQuantity');
    }
}
