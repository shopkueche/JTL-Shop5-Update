<?php
/**
 * Create index for tartikel.kStueckliste
 *
 * @author fp
 * @created Tue, 16 May 2017 15:30:34 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Update\MigrationHelper;

/**
 * Class Migration_20170516153034
 */
class Migration_20170516153034 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = /** @lang text */
        'Create index for tartikel.kStueckliste';

    /**
     * @inheritDoc
     */
    public function up()
    {
        MigrationHelper::createIndex('tartikel', ['kStueckliste'], 'idx_tartikel_kStueckliste');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        MigrationHelper::dropIndex('tartikel', 'idx_tartikel_kStueckliste');
    }
}
