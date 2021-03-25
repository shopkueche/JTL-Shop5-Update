<?php

/**
 * adds lang var for price flow title
 *
 * @author ms
 * @created Fri, 06 Dec 2019 14:33:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191206130000
 */
class Migration_20191206130000 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'adds lang var for price flow title';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'PriceFlowTitle', 'Preisverlauf der letzten %s Monate');
        $this->setLocalization('eng', 'productDetails', 'PriceFlowTitle', 'price flow of the last %s months');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('PriceFlowTitle');
    }
}
