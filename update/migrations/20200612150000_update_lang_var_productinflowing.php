<?php
/**
 * Update lang var productInflowing
 *
 * @author mh
 * @created Fr, 12 Jun 2020 15:00:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200612150000
 */
class Migration_20200612150000 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Update lang var productInflowing';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'productInflowing', 'Ware bestellt. %s %s voraussichtlich ab dem %s verfügbar.');
        $this->setLocalization('eng', 'productDetails', 'productInflowing', 'Goods ordered. %s %s expected on %s.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'productDetails', 'productInflowing', '%s bestellt, am %s erwartet');
        $this->setLocalization('eng', 'productDetails', 'productInflowing', '%s ordered, expected on %s');
    }
}
