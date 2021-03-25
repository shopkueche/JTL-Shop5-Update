<?php
/** add a manufacturer column to tkupon to enable manufacturer specific coupons*/

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161108112500
 */
class Migration_20161108112500 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE tkupon ADD COLUMN cHersteller TEXT NOT NULL AFTER cArtikel;');

        $this->setLocalization('ger', 'global', 'couponErr12', 'Der Kupon ist für den aktuellen Warenkorb ungültig (gilt nur für bestimmte Hersteller).');
        $this->setLocalization('eng', 'global', 'couponErr12', 'This coupon is invalid for your cart (valid only for specific manufacturers).');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->dropColumn('tkupon', 'cHersteller');
        $this->removeLocalization('couponErr12');
    }
}
