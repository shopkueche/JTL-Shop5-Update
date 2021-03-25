<?php

/**
 * Add lang shipping info
 *
 * @author mh
 * @created Wed, 27 Nov 2019 10:48:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191127104800
 */
class Migration_20191127104800 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add lang shipping info';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization(
            'ger',
            'productDetails',
            'shippingInformation',
            "Die angegebenen Lieferzeiten gelten für den Versand innerhalb von %s. Die Lieferzeiten für den " .
            "Versand ins Ausland finden Sie in unseren <a href=\'%s\'>Versandinformationen</a>."
        );
        $this->setLocalization(
            'eng',
            'productDetails',
            'shippingInformation',
            "The indicated delivery times refer to shipments within %s. For information on the delivery times " .
            "for shipments to other countries, please see the  <a href=\'%s\'>Shipping information</a>."
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('shippingInformation');
    }
}
