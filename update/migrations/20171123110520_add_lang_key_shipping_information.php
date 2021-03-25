<?php
/**
 * add_lang_key_shipping_information
 *
 * @author msc
 * @created Thu, 23 Nov 2017 11:05:20 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20171123110520
 */
class Migration_20171123110520 extends Migration implements IMigration
{
    protected $author      = 'msc';
    protected $description = 'add_lang_key_shipping_information';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'basket', 'shippingInformationSpecific', 'Zzgl. <a href="%1$s" class="shipment popup">Versandkosten</a> ab %2$s bei Lieferung nach %3$s');
        $this->setLocalization('eng', 'basket', 'shippingInformationSpecific', 'Plus <a href="%1$s" class="shipment popup">shipping costs</a> starting from %2$s for delivery to %3$s');
        $this->setLocalization('ger', 'basket', 'shippingInformation', 'Zzgl. <a href="%1$s" class="shipment popup">Versandkosten</a>');
        $this->setLocalization('eng', 'basket', 'shippingInformation', 'Plus <a href="%1$s" class="shipment popup">shipping costs</a>');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('shippingInformationSpecific');
        $this->removeLocalization('shippingInformation');
    }
}
