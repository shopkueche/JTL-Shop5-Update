<?php declare(strict_types=1);

/**
 * @author mh
 * @created Tue, 19 May 2020 15:35:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200519153500
 */
class Migration_20200519153500 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Misc frontend lang fixes';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'basket', 'noShippingCostsReached', 'Ihre Bestellung ist mit %s versandkostenfrei nach %s lieferbar.');
        $this->setLocalization('eng', 'basket', 'noShippingCostsReached', 'Your order can be shipped for free with %s to %s.');
        $this->setLocalization('ger', 'basket', 'noShippingCostsAt', 'Noch %s und wir versenden kostenfrei mit %s nach %s.');
        $this->setLocalization('eng', 'basket', 'noShippingCostsAt', 'Another %s and your order will be eligible for free shipping with %s to %s.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'basket', 'noShippingCostsReached', 'Ihre Bestellung ist mit %s versandkostenfrei %s lieferbar.');
        $this->setLocalization('eng', 'basket', 'noShippingCostsReached', 'Your order can be shipped for free with %s %s.');
        $this->setLocalization('ger', 'basket', 'noShippingCostsAt', 'Noch %s und wir versenden kostenfrei mit %s %s');
        $this->setLocalization('eng', 'basket', 'noShippingCostsAt', 'Another %s and your order will be eligible for free shipping with %s %s');
    }
}
