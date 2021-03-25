<?php
/**
 * adds paymentNotNecessary language variable
 *
 * @author ms
 * @created Wed, 10 May 2017 14:53:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170510145300
 */
class Migration_20170510145300 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'adds paymentNotNecessary language variable';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'paymentNotNecessary', 'Keine Zahlung notwendig');
        $this->setLocalization('eng', 'checkout', 'paymentNotNecessary', 'Payment not necessary');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('paymentNotNecessary');
    }
}
