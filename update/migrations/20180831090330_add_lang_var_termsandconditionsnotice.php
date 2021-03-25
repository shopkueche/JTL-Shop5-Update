<?php
/**
 * add_lang_var_termsandconditionsnotice
 *
 * @author mh
 * @created Fri, 31 Aug 2018 09:03:30 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180831090330
 */
class Migration_20180831090330 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'add lang var termsAndConditionsNotice';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'termsAndConditionsNotice', 'Ich habe die <a href="%s" %s>AGB/Kundeninformationen</a> gelesen und erkläre mit dem Absenden der Bestellung mein Einverständnis.');
        $this->setLocalization('eng', 'checkout', 'termsAndConditionsNotice', 'I have read the <a href="%s" %s>General Terms and Conditions</a> and declare them being the basis of this contract.');

        $this->setLocalization('ger', 'checkout', 'cancellationPolicyNotice', 'Die <a href="%s" %s>Widerrufsbelehrung</a> habe ich zur Kenntnis genommen.');
        $this->setLocalization('eng', 'checkout', 'cancellationPolicyNotice', 'Please take note of our <a href="%s" %s>Instructions for cancellation.</a>');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('termsAndConditionsNotice');

        $this->setLocalization('ger', 'checkout', 'cancellationPolicyNotice', 'Bitte beachten Sie unsere #LINK_WRB#.');
        $this->setLocalization('eng', 'checkout', 'cancellationPolicyNotice', 'Please take note of our #LINK_WRB#.');
    }
}
