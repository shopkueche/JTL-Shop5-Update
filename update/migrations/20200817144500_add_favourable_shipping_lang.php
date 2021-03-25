<?php
/**
 * Add favourable shipping lang
 *
 * @author mh
 * @created Mon, 27 July 2020 15:01:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200817144500
 */
class Migration_20200817144500 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add favourable shipping lang';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization(
            'ger',
            'basket',
            'shippingInformationSpecificSingle',
            'Zzgl. <a href="%1$s" class="shipment popup">Versandkosten</a>: %2$s bei Lieferung nach %3$s'
        );
        $this->setLocalization(
            'eng',
            'basket',
            'shippingInformationSpecificSingle',
            'Plus <a href="%1$s" class="shipment popup">shipping costs</a>: %2$s for delivery to %3$s'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('shippingInformationSpecificSingle', 'basket');
    }
}
