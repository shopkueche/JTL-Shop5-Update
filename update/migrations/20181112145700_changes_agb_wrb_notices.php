<?php
/**
 * changes agb wrb notices, adds delete from compare list var
 *
 * @author ms
 * @created Mon, 12 Nov 2018 14:57:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20181112145700
 */
class Migration_20181112145700 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'changes agb wrb notices, adds delete from compare list var';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'termsAndConditionsNotice', 'Ich habe die <a href="%s" %s>AGB/Kundeninformationen</a> gelesen und erkläre mit dem Absenden der Bestellung mein Einverständnis.');
        $this->setLocalization('eng', 'checkout', 'termsAndConditionsNotice', 'I have read the <a href="%s" %s>General Terms and Conditions</a> and declare them being the basis of this contract.');

        $this->setLocalization('ger', 'checkout', 'cancellationPolicyNotice', 'Die <a href="%s" %s>Widerrufsbelehrung</a> habe ich zur Kenntnis genommen.');
        $this->setLocalization('eng', 'checkout', 'cancellationPolicyNotice', 'Please take note of our <a href="%s" %s>Instructions for cancellation.</a>');

        $this->setLocalization('ger', 'comparelist', 'removeFromCompareList', 'Artikel von der Vergleichsliste entfernen');
        $this->setLocalization('eng', 'comparelist', 'removeFromCompareList', 'remove product from compare list');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'checkout', 'termsAndConditionsNotice', 'Ich habe die <a href="#URL_AGB#" #ATTRIBUTES#>AGB/Kundeninformationen</a> gelesen und erkläre mit dem Absenden der Bestellung mein Einverständnis.');
        $this->setLocalization('eng', 'checkout', 'termsAndConditionsNotice', 'I have read the <a href="#URL_AGB#" #ATTRIBUTES#>General Terms and Conditions</a> and declare them being the basis of this contract.');

        $this->setLocalization('ger', 'checkout', 'cancellationPolicyNotice', 'Die <a href="#URL_WRB#" #ATTRIBUTES#>Widerrufsbelehrung</a> habe ich zur Kenntnis genommen.');
        $this->setLocalization('eng', 'checkout', 'cancellationPolicyNotice', 'Please take note of our <a href="#URL_WRB#" #ATTRIBUTES#>Instructions for cancellation.</a>');

        $this->removeLocalization('removeFromCompareList');
    }
}
