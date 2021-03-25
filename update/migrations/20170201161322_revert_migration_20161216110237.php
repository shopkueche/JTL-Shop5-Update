<?php
/**
 * Revert Migration_20161216110237
 *
 * @author fp
 * @created Wed, 01 Feb 2017 16:13:22 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170201161322
 */
class Migration_20170201161322 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Revert Migration_20161216110237';

    /**
     * @inheritDoc
     */
    public function up()
    {
        // The up-function will only be executed if Migration_20161216110237 is installed.
        $migration = $this->fetchOne('SELECT kMigration FROM tmigration WHERE kMigration = 20161216110237');

        if (isset($migration) && (int)$migration->kMigration === 20161216110237) {
            $this->removeConfig('addDeliveryDayOnSaturday');

            $this->setLocalization('ger', 'global', 'deliverytimeEstimation', '#MINDELIVERYDAYS# - #MAXDELIVERYDAYS# Werktage');
            $this->setLocalization('eng', 'global', 'deliverytimeEstimation', '#MINDELIVERYDAYS# - #MAXDELIVERYDAYS# workdays');
            $this->setLocalization('ger', 'global', 'deliverytimeEstimationSimple', '#DELIVERYDAYS# Werktage');
            $this->setLocalization('eng', 'global', 'deliverytimeEstimationSimple', '#DELIVERYDAYS# workdays');
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // This migration will undo the changes from Migration_20161216110237 in beta installations.
        // There is absolutly no reason to downgrade because Migration_20161216110237 has been removed in final release of 4.05
    }
}
