<?php
/**
 * Lang variables misc law fixes
 *
 * @author mh
 * @created Mon, 27 July 2020 15:01:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200727150100
 */
class Migration_20200727150100 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Lang variables misc law fixes';

    /**
     * @inheritDoc
     */
    public function up()
    {
        // SHOP-4259
        $this->setLocalization('ger', 'global', 'shippingTime', 'Lieferzeit');
        $this->setLocalization('eng', 'global', 'shippingTime', 'Delivery time');

        $this->setLocalization('ger', 'productDetails', 'shippingInfoIcon', 'Ausland');
        $this->setLocalization('eng', 'productDetails', 'shippingInfoIcon', 'Other countries');

        // SHOP-4261
        $this->setLocalization('ger', 'productDetails', 'suggestedPrice', 'Unverbindliche Preisempfehlung des Herstellers');
        $this->setLocalization('eng', 'productDetails', 'suggestedPrice', 'Manufacturers recommended retail price');
        $this->removeLocalization('suggestedPriceExpl', 'productDetails');

        // SHOP-4262
        $this->setLocalization('ger', 'checkout', 'termsNotice', 'Es gelten die <a href="%s" %s>Allgemeinen Geschäftsbedingungen</a>.');
        $this->setLocalization('eng', 'checkout', 'termsNotice', 'The <a href="%s" %s>General Terms and Conditions</a> apply.');
        $this->setLocalization('ger', 'checkout', 'termsCancelationNotice', 'Es gelten die <a href="%s" %s>Allgemeinen Geschäftsbedingungen</a>. Ich habe das <a href="%s" %s>Widerrufsrecht</a> zur Kenntnis genommen.');
        $this->setLocalization('eng', 'checkout', 'termsCancelationNotice', 'The <a href="%s" %s>General Terms and Conditions</a> apply. I have noted the <a href="%s" %s>withdrawal policy</a>.');

        // SHOP-4260
        $this->setLocalization(
            'ger',
            'productDetails',
            'shippingInformation',
            "Die angegebenen Lieferzeiten gelten für den Versand innerhalb von %s. Die Lieferzeiten für den " .
            "<a href=\'%s#othercountries\'>Versand ins Ausland</a> finden Sie in unseren <a href=\'%s\'>Versandinformationen</a>."
        );
        $this->setLocalization(
            'eng',
            'productDetails',
            'shippingInformation',
            "The indicated delivery times refer to shipments within %s. For information on the delivery times " .
            "for <a href=\'%s#othercountries\'>shipments to other countries</a>, please see the  <a href=\'%s\'>Shipping information</a>."
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // SHOP-4259
        $this->setLocalization('ger', 'global', 'shippingTime', 'Errechnete Lieferzeit');
        $this->setLocalization('eng', 'global', 'shippingTime', 'Calculated delivery time');

        $this->removeLocalization('shippingInfoIcon', 'productDetails');

        // SHOP-4261
        $this->setLocalization('ger', 'productDetails', 'suggestedPrice', 'UVP des Herstellers');
        $this->setLocalization('ger', 'productDetails', 'suggestedPriceExpl', '** Unverbindliche Preisempfehlung');
        $this->setLocalization('eng', 'productDetails', 'suggestedPrice', 'Manufacturers RRP');
        $this->setLocalization('eng', 'productDetails', 'suggestedPriceExpl', '** Recommended retail price');

        // SHOP-4262
        $this->removeLocalization('termsNotice', 'checkout');
        $this->removeLocalization('termsCancelationNotice', 'checkout');

        // SHOP-4260
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
}
