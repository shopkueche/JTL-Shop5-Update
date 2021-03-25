<?php
/**
 * Lang var for price change during checkout.
 *
 * @author fp
 * @created Fri, 24 May 2019 12:23:22 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Migration
 */
class Migration_20190524122322 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Create lang var for price change during checkout.';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'priceHasChanged', 'Der Preis für den Artikel "%s" in Ihrem Warenkorb '
            . 'hat sich zwischenzeitlich geändert.  Bitte prüfen Sie die Warenkorbpositionen.');
        $this->setLocalization('eng', 'checkout', 'priceHasChanged', 'The price for the article "%s" in your basket has '
            . 'changed in the meantime. Please check your order items.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('priceHasChanged');
    }
}
