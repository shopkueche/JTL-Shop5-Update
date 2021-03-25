<?php
/**
 * New index for customer prices
 *
 * @author root
 * @created Mon, 22 Aug 2016 10:30:20 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Update\MigrationHelper;

/**
 * Class Migration_20160822103020
 */
class Migration_20160822103020 extends Migration implements IMigration
{
    protected $author = 'fp';

    /**
     * @inheritDoc
     */
    public function up()
    {
        MigrationHelper::createIndex('tpreis', ['kKunde'], 'idx_tpreis_kKunde');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        MigrationHelper::dropIndex('tpreis', 'idx_tpreis_kKunde');
    }
}
