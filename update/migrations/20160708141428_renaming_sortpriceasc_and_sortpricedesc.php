<?php
/**
 * renaming_sortPriceAsc_and_sortPriceDesc
 *
 * @author msc
 * @created Fri, 08 Jul 2016 14:14:28 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160708141428
 */
class Migration_20160708141428 extends Migration implements IMigration
{
    protected $author = 'msc';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'sortPriceAsc', 'Preis aufsteigend');
        $this->setLocalization('eng', 'global', 'sortPriceAsc', 'Price ascending');
        $this->setLocalization('ger', 'global', 'sortPriceDesc', 'Preis absteigend');
        $this->setLocalization('eng', 'global', 'sortPriceDesc', 'Price descending');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'global', 'sortPriceAsc', 'Preis 1..9');
        $this->setLocalization('eng', 'global', 'sortPriceAsc', 'Price 1..9');
        $this->setLocalization('ger', 'global', 'sortPriceDesc', 'Preis 9..1');
        $this->setLocalization('eng', 'global', 'sortPriceDesc', 'Price 9..1');
    }
}
