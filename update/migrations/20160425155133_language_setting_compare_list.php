<?php
/**
 * language setting compare list
 *
 * @author ms
 * @created Mon, 25 Apr 2016 15:51:33 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160425155133
 */
class Migration_20160425155133 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'compareListNoItems', 'Sie benötigen mindestens zwei Artikel, um vergleichen zu können.');
        $this->setLocalization('eng', 'global', 'compareListNoItems', 'You need at least two products in order to be able to compare.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'global', 'compareListNoItems', 'Sie haben noch keine Artikel auf Ihrer Vergleichsliste.');
        $this->setLocalization('eng', 'global', 'compareListNoItems', 'There are no items on you compare list yet.');
    }
}
