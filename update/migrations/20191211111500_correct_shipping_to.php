<?php
/**
 * Correct shipping to lang var
 *
 * @author mh
 * @created Wed, 11 Dec 2019 11:15:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191211111500
 */
class Migration_20191211111500 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Correct shipping to lang var';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'shippingTo', 'Versand nach');
        $this->setLocalization('eng', 'checkout', 'shippingTo', 'Destination');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {

    }
}
