<?php
/**
 * Add lang var for wishlist/comparelist buttons
 *
 * @author mh
 * @created Fri, 14 June 2019 09:49:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190614094900
 */
class Migration_20190614094900 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add lang var for wishlist/comparelist buttons';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'onWishlist', 'Auf Wunschzettel');
        $this->setLocalization('eng', 'global', 'onWishlist', 'On wishlist');
        $this->setLocalization('ger', 'global', 'notOnWishlist', 'Nicht auf Wunschzettel');
        $this->setLocalization('eng', 'global', 'notOnWishlist', 'Not on wishlist');
        $this->setLocalization('ger', 'global', 'onComparelist', 'Auf Vergleichsliste');
        $this->setLocalization('eng', 'global', 'onComparelist', 'On comparelist');
        $this->setLocalization('ger', 'global', 'notOnComparelist', 'Nicht auf Vergleichsliste');
        $this->setLocalization('eng', 'global', 'notOnComparelist', 'Not on comparelist');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('onWishlist');
        $this->removeLocalization('notOnWishlist');
        $this->removeLocalization('onComparelist');
        $this->removeLocalization('notOnComparelist');
    }
}
