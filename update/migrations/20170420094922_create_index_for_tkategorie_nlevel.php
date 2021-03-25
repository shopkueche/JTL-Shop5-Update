<?php
/**
 * Create index for tkategorie.nLevel
 *
 * @author fp
 * @created Thu, 20 Apr 2017 09:49:22 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Update\MigrationHelper;

/**
 * Class Migration_20170420094922
 */
class Migration_20170420094922 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = /** @lang text */
        'Create index for tkategorie.nLevel';

    /**
     * @inheritDoc
     */
    public function up()
    {
        MigrationHelper::createIndex('tkategorie', ['nLevel'], 'idx_tkategorie_nLevel');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        MigrationHelper::dropIndex('tkategorie', 'idx_tkategorie_nLevel');
    }
}
